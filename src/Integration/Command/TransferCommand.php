<?php

declare(strict_types=1);

namespace HeptaConnect\Production\Integration\Command;

use Heptacom\HeptaConnect\Core\Emission\Contract\EmitContextFactoryInterface;
use Heptacom\HeptaConnect\Core\Emission\Contract\EmitterStackBuilderFactoryInterface;
use Heptacom\HeptaConnect\Core\Reception\Contract\ReceiveServiceInterface;
use Heptacom\HeptaConnect\Dataset\Base\Contract\DatasetEntityContract;
use Heptacom\HeptaConnect\Dataset\Base\DatasetEntityCollection;
use Heptacom\HeptaConnect\Dataset\Base\TypedDatasetEntityCollection;
use Heptacom\HeptaConnect\Portal\Base\StorageKey\Contract\PortalNodeKeyInterface;
use Heptacom\HeptaConnect\Portal\Base\Support\Contract\DeepObjectIteratorContract;
use Heptacom\HeptaConnect\Storage\Base\Action\Identity\Map\IdentityMapPayload;
use Heptacom\HeptaConnect\Storage\Base\Action\Identity\Reflect\IdentityReflectPayload;
use Heptacom\HeptaConnect\Storage\Base\Action\Route\Get\RouteGetCriteria;
use Heptacom\HeptaConnect\Storage\Base\Action\Route\Get\RouteGetResult;
use Heptacom\HeptaConnect\Storage\Base\Action\Route\Listing\ReceptionRouteListCriteria;
use Heptacom\HeptaConnect\Storage\Base\Action\Route\Listing\ReceptionRouteListResult;
use Heptacom\HeptaConnect\Storage\Base\Contract\Action\Identity\IdentityMapActionInterface;
use Heptacom\HeptaConnect\Storage\Base\Contract\Action\Identity\IdentityReflectActionInterface;
use Heptacom\HeptaConnect\Storage\Base\Contract\Action\Route\ReceptionRouteListActionInterface;
use Heptacom\HeptaConnect\Storage\Base\Contract\Action\Route\RouteGetActionInterface;
use Heptacom\HeptaConnect\Storage\Base\Contract\StorageKeyGeneratorContract;
use Heptacom\HeptaConnect\Storage\Base\RouteKeyCollection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\ErrorHandler\ErrorRenderer\CliErrorRenderer;

final class TransferCommand extends Command
{
    protected static $defaultName = 'heptaconnect:transfer';

    public function __construct(
        private StorageKeyGeneratorContract $storageKeyGenerator,
        private EmitterStackBuilderFactoryInterface $emitterStackBuilderFactory,
        private EmitContextFactoryInterface $emitContextFactory,
        private ReceptionRouteListActionInterface $receptionRouteListAction,
        private RouteGetActionInterface $routeGetAction,
        private ReceiveServiceInterface $receiveService,
        private IdentityMapActionInterface $identityMapAction,
        private IdentityReflectActionInterface $identityReflectAction,
        private DeepObjectIteratorContract $objectIterator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('portal-node', InputArgument::REQUIRED);
        $this->addArgument('type', InputArgument::REQUIRED);
        $this->addArgument('external-ids', InputArgument::REQUIRED | InputArgument::IS_ARRAY);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $sourcePortalNodeKey = $this->getPortalNodeKey($input);
        $entityType = $this->getType($input);
        $externalIds = $this->getExternalIds($input);

        $routes = $this->getRoutes($sourcePortalNodeKey, $entityType);

        $context = $this->emitContextFactory->createContext($sourcePortalNodeKey);

        $stack = $this->emitterStackBuilderFactory
            ->createEmitterStackBuilder($sourcePortalNodeKey, $entityType)
            ->pushSource()
            ->pushDecorators()
            ->build();

        try {
            $entities = new DatasetEntityCollection($stack->next($externalIds, $context));

            foreach ($routes as $route) {
                $entityClones = new DatasetEntityCollection($entities->map('\DeepCopy\deep_copy'));

                $targetPortalNodeKey = $route->getTargetPortalNodeKey();
                $this->reflect($entityClones, $sourcePortalNodeKey, $targetPortalNodeKey);

                $this->receiveService->receive(
                    new TypedDatasetEntityCollection($entityType, $entityClones),
                    $targetPortalNodeKey
                );
            }
        } catch (\Throwable $exception) {
            $io->write((new CliErrorRenderer())->render($exception)->getAsString());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function getPortalNodeKey(InputInterface $input): PortalNodeKeyInterface
    {
        $portalNodeKey = $this->storageKeyGenerator->deserialize($input->getArgument('portal-node'));

        if (!$portalNodeKey instanceof PortalNodeKeyInterface) {
            throw new \Exception('Unknown portal-node');
        }

        return $portalNodeKey;
    }

    private function getType(InputInterface $input): string
    {
        $type = \trim((string) $input->getArgument('type'), ' "\'\\');

        if (!\class_exists($type)) {
            throw new \Exception('Unknown type');
        }

        return $type;
    }

    /**
     * @return string[]
     */
    private function getExternalIds(InputInterface $input): array
    {
        return \array_map(
            static fn ($externalId): string => \trim((string) $externalId),
            (array) $input->getArgument('external-ids')
        );
    }

    /**
     * @return RouteGetResult[]
     */
    private function getRoutes(PortalNodeKeyInterface $sourcePortalNodeKey, string $entityType): array
    {
        /** @var ReceptionRouteListResult[] $receptionRoutes */
        $receptionRoutes = \iterable_to_array($this->receptionRouteListAction->list(
            new ReceptionRouteListCriteria($sourcePortalNodeKey, $entityType)
        ));

        if ($receptionRoutes === []) {
            $messageTemplate = \implode(\PHP_EOL, [
                'Message is not routed. Add a route and re-explore this entity.',
                'source portal: %s',
                'data type: %s',
            ]);

            throw new \Exception(\sprintf(
                $messageTemplate,
                $this->storageKeyGenerator->serialize($sourcePortalNodeKey),
                $entityType
            ));
        }

        $routeKeys = new RouteKeyCollection(
            \array_map(static fn (ReceptionRouteListResult $result) => $result->getRouteKey(), $receptionRoutes)
        );

        /** @var RouteGetResult[] $routes */
        $routes = \iterable_to_array($this->routeGetAction->get(new RouteGetCriteria($routeKeys)));

        return $routes;
    }

    private function reflect(
        DatasetEntityCollection $entities,
        PortalNodeKeyInterface $sourcePortalNodeKey,
        PortalNodeKeyInterface $targetPortalNodeKey
    ): void {
        /** @var array<DatasetEntityContract|object> $allEntities */
        $allEntities = $this->objectIterator->iterate($entities);
        $filteredEntityObjects = new DatasetEntityCollection($allEntities);

        $mappedEntities = $this->identityMapAction
            ->map(new IdentityMapPayload($sourcePortalNodeKey, $filteredEntityObjects))
            ->getMappedDatasetEntityCollection();

        $this->identityReflectAction->reflect(
            new IdentityReflectPayload($targetPortalNodeKey, $mappedEntities)
        );
    }
}
