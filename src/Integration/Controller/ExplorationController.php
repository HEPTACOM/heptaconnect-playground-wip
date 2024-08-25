<?php

declare(strict_types=1);

namespace HeptaConnect\Production\Integration\Controller;

use Heptacom\HeptaConnect\Core\Exploration\Contract\ExploreServiceInterface;
use Heptacom\HeptaConnect\Dataset\Base\Contract\DatasetEntityContract;
use Heptacom\HeptaConnect\Portal\Base\StorageKey\Contract\PortalNodeKeyInterface;
use Heptacom\HeptaConnect\Storage\Base\Contract\StorageKeyGeneratorContract;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ExplorationController extends AbstractController
{
    public function __construct(
        private StorageKeyGeneratorContract $storageKeyGenerator,
        private ExploreServiceInterface $exploreService,
    ) {
    }

    /**
     * @Route(
     *      "/api/heptaconnect/flow/{portalNodeId}/explorer/{entityType}",
     *      name="api.heptaconnect.exploration",
     *      requirements={"entityType"=".+"},
     *      defaults={"auth_required"=false}
     *  )
     */
    public function __invoke(string $portalNodeId, string $entityType): Response
    {
        /** @var PortalNodeKeyInterface $portalNodeKey */
        $portalNodeKey = $this->storageKeyGenerator->deserialize($portalNodeId);

        if (!\is_a($entityType, DatasetEntityContract::class, true)) {
            throw new \Exception('The provided type is not a DatasetEntityContract: ' . $entityType);
        }

        $this->exploreService->dispatchExploreJob($portalNodeKey, [$entityType]);

        $this->addFlash('success', 'Exploration job is dispatched');

        return $this->redirectToRoute('ui.heptaconnect.home');
    }
}
