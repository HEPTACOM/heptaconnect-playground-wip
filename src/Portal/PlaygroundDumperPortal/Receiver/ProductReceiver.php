<?php

declare(strict_types=1);

namespace HeptaConnect\Production\Portal\PlaygroundDumperPortal\Receiver;

use Heptacom\HeptaConnect\Dataset\Base\Contract\DatasetEntityContract;
use Heptacom\HeptaConnect\Dataset\Ecommerce\Media\Media;
use Heptacom\HeptaConnect\Dataset\Ecommerce\Product\Product;
use Heptacom\HeptaConnect\Portal\Base\File\FileReferenceResolverContract;
use Heptacom\HeptaConnect\Portal\Base\Portal\Contract\PortalStorageInterface;
use Heptacom\HeptaConnect\Portal\Base\Reception\Contract\ReceiveContextInterface;
use Heptacom\HeptaConnect\Portal\Base\Reception\Contract\ReceiverContract;

final class ProductReceiver extends ReceiverContract
{
    public function __construct(
        private FileReferenceResolverContract $fileReferenceResolver,
        private PortalStorageInterface $portalStorage,
    ) {
    }

    public function supports(): string
    {
        return Product::class;
    }

    /**
     * @param Product $entity
     */
    protected function run(DatasetEntityContract $entity, ReceiveContextInterface $context): void
    {
        $primaryKey = $entity->getNumber();
        $entity->setPrimaryKey($primaryKey);

        $images = $entity->getMedias()->map(function (Media $media): array {
            $fileReference = $this->fileReferenceResolver->resolve($media->getFile());

            return [
                'mimeType' => $media->getMimeType(),
                'filename' => $media->getFilename(),
                'publicUrl' => $fileReference->getPublicUrl(),
            ];
        });

        $data = [
            'number' => $primaryKey,
            'name' => $entity->getName()->getFallback(),
            'gtin' => $entity->getGtin(),
            'updatedAt' => \date_create()->format('Y-m-d H:i:s.v'),
            'price' => $entity->getPrices()->first()?->getGross(),
            'images' => [...$images],
        ];

        $this->portalStorage->set('dump/product/' . $primaryKey, $data);
    }
}
