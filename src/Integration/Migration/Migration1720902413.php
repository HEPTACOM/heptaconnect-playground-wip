<?php

declare(strict_types=1);

namespace HeptaConnect\Production\Integration\Migration;

use Heptacom\HeptaConnect\Dataset\Ecommerce\Product\Product;
use HeptaConnect\Production\Integration\Component\Migration\AbstractMigration;
use HeptaConnect\Production\Integration\Component\Migration\MigrationHelper;
use HeptaConnect\Production\Portal\PlaygroundDumperPortal\PlaygroundDumperPortal;
use NiemandOnline\HeptaConnect\Portal\Amiibo\AmiiboPortal;

/**
 * @generated via `bin/console database:create-migration`
 */
final class Migration1720902413 extends AbstractMigration
{
    protected function up(MigrationHelper $migrationHelper): void
    {
        $migrationHelper->addPortalNode(AmiiboPortal::class, 'amiibo');
        $migrationHelper->addPortalNode(PlaygroundDumperPortal::class, 'dumper');
        $migrationHelper->addRoute('amiibo', 'dumper', Product::class);
    }
}
