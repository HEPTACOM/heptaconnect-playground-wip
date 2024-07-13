<?php

declare(strict_types=1);

namespace HeptaConnect\Production\DevOps\Kernel;

use Composer\InstalledVersions;

final class Kernel extends \Shopware\Core\Kernel
{
    public function getCacheDir(): string
    {
        return \sprintf(
            '%s/storage/data/var/cache/%s_h%s',
            $this->getProjectDir(),
            $this->getEnvironment(),
            $this->getCacheHash()
        );
    }

    public function getLogDir(): string
    {
        return $this->getProjectDir() . '/storage/logs';
    }

    protected function getKernelParameters(): array
    {
        if (InstalledVersions::isInstalled('heptacom/heptaconnect-framework')) {
            $version = InstalledVersions::getVersion('heptacom/heptaconnect-framework');
        } elseif (InstalledVersions::isInstalled('heptacom/heptaconnect-core')) {
            $version = InstalledVersions::getVersion('heptacom/heptaconnect-core');
        } else {
            $version = 'UNKNOWN';
        }

        return [
            ...parent::getKernelParameters(),
            'kernel.heptaconnect_version' => $version,
        ];
    }
}
