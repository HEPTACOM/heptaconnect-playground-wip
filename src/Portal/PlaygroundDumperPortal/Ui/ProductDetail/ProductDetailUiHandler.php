<?php

declare(strict_types=1);

namespace HeptaConnect\Production\Portal\PlaygroundDumperPortal\Ui\ProductDetail;

use Heptacom\HeptaConnect\Package\WebFrontend\Components\Page\Contract\UiHandlerContract;
use Heptacom\HeptaConnect\Portal\Base\Portal\Contract\PortalStorageInterface;
use Heptacom\HeptaConnect\Portal\Base\Web\Http\Contract\HttpHandleContextInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ProductDetailUiHandler extends UiHandlerContract
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
        return 'product/detail';
    }

    protected function get(
        ServerRequestInterface $request,
        ResponseInterface $response,
        HttpHandleContextInterface $context,
    ): ResponseInterface {
        $number = $request->getQueryParams()['number'] ?? null;

        if (!\is_string($number)) {
            return $response->withStatus(404);
        }

        $product = $this->portalStorage->get('dump/product/' . $number);

        if (!\is_array($product)) {
            return $response->withStatus(404);
        }

        $page = new ProductDetailPage($product);

        return $this->render($page);
    }
}
