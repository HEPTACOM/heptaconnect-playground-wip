<?php

declare(strict_types=1);

namespace HeptaConnect\Production\Portal\PlaygroundDumperPortal\Ui\ProductDetail;

use Heptacom\HeptaConnect\Package\WebFrontend\Components\Page\Contract\AbstractPage;

final class ProductDetailPage extends AbstractPage
{
    public function __construct(
        private array $product,
    ) {
    }

    public function getTemplate(): string
    {
        return '@PlaygroundDumperPortal/products/detail/index.html.twig';
    }

    public function getProduct(): array
    {
        return $this->product;
    }
}
