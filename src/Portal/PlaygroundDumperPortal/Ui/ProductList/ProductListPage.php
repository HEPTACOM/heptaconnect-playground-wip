<?php

declare(strict_types=1);

namespace HeptaConnect\Production\Portal\PlaygroundDumperPortal\Ui\ProductList;

use Heptacom\HeptaConnect\Package\WebFrontend\Components\Page\Contract\AbstractPage;

final class ProductListPage extends AbstractPage
{
    public function __construct(
        private array $products,
    ) {
    }

    public function getTemplate(): string
    {
        return '@PlaygroundDumperPortal/products/list/index.html.twig';
    }

    public function getProducts(): array
    {
        return $this->products;
    }
}
