<?php

declare(strict_types=1);

namespace HeptaConnect\Production\Portal\PlaygroundDumperPortal\Ui\ProductList;

use Heptacom\HeptaConnect\Package\WebFrontend\Components\Page\Contract\UiHandlerContract;
use Heptacom\HeptaConnect\Portal\Base\Portal\Contract\PortalStorageInterface;
use Heptacom\HeptaConnect\Portal\Base\Web\Http\Contract\HttpHandleContextInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ProductListUiHandler extends UiHandlerContract
{
    public function __construct(
        private PortalStorageInterface $portalStorage,
    ) {
    }

    public function isProtected(ServerRequestInterface $request): bool
    {
        return false;
    }

    protected function supports(): string
    {
        return 'products/list';
    }

    protected function get(
        ServerRequestInterface $request,
        ResponseInterface $response,
        HttpHandleContextInterface $context,
    ): ResponseInterface {
        $products = [];

        foreach ($this->portalStorage->list() as $key => $product) {
            if (\str_starts_with($key, 'dump/product/')) {
                $products[] = $product;
            }
        }

        $products = \array_column($products, null, 'number');

        $page = new ProductListPage($products);

        return $this->render($page);
    }
}
