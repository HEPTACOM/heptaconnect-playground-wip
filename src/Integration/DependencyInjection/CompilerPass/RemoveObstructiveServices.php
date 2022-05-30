<?php

declare(strict_types=1);

namespace Heptacom\HeptaConnect\Integration\DependencyInjection\CompilerPass;

use Shopware\Core\Framework\Update\Services\CreateCustomAppsDir;
use Shopware\Core\Framework\Update\Services\UpdateHtaccess;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RemoveObstructiveServices implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->removeDefinition(CreateCustomAppsDir::class);
        $container->removeDefinition(UpdateHtaccess::class);
    }
}
