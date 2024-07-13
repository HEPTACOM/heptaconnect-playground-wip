<?php

declare(strict_types=1);

namespace HeptaConnect\Production\Integration\Core\Web;

use Heptacom\HeptaConnect\Bridge\ShopwarePlatform\Web\Http\HttpHostProviderContract;
use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;

final class HttpHostProviderDecorator extends HttpHostProviderContract
{
    private UriFactoryInterface $uriFactory;

    public function __construct(
        SystemConfigService $systemConfigService,
        private HttpHostProviderContract $hostProvider,
        private string $appUrl,
    ) {
        parent::__construct($systemConfigService);
        $this->uriFactory = Psr17FactoryDiscovery::findUriFactory();
    }

    public function get(): UriInterface
    {
        if ($this->appUrl === '') {
            return $this->hostProvider->get();
        }

        $baseUrl = $this->appUrl;

        if (\strpos($baseUrl, '//') === false) {
            $baseUrl = '//' . $baseUrl;
        }

        $uri = $this->uriFactory->createUri();

        $urlComponents = \parse_url($baseUrl);

        if (!\is_array($urlComponents)) {
            return $uri;
        }

        $uri = $uri->withScheme($urlComponents['scheme'] ?? 'http');

        if (isset($urlComponents['host'])) {
            $uri = $uri->withHost($urlComponents['host']);
        }

        if (isset($urlComponents['port'])) {
            $uri = $uri->withPort($urlComponents['port']);
        }

        if (isset($urlComponents['path'])) {
            $uri = $uri->withPath($urlComponents['path']);
        }

        return $uri;
    }
}
