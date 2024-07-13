<?php

declare(strict_types=1);

namespace HeptaConnect\Production\Portal\PlaygroundDumperPortal;

use Heptacom\HeptaConnect\Package\WebFrontend\Components\Template\Contract\ThemeInterface;
use Heptacom\HeptaConnect\Package\WebFrontend\Components\Template\Feature\CacheFeature;
use Heptacom\HeptaConnect\Package\WebFrontend\Components\Template\Utility\ThemePackageTrait;
use Heptacom\HeptaConnect\Package\WebFrontend\WebFrontendPackage;
use Heptacom\HeptaConnect\Portal\Base\Portal\Contract\PortalContract;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class PlaygroundDumperPortal extends PortalContract implements ThemeInterface
{
    use ThemePackageTrait;

    public function getAdditionalPackages(): iterable
    {
        yield new WebFrontendPackage();
    }

    public function buildContainer(ContainerBuilder $containerBuilder): void
    {
        parent::buildContainer($containerBuilder);

        $containerBuilder->loadFromExtension(CacheFeature::getName(), [
            'enabled' => false,
        ]);
    }
}
