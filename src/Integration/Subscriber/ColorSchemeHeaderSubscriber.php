<?php

declare(strict_types=1);

namespace HeptaConnect\Production\Integration\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

final class ColorSchemeHeaderSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Environment $twig,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if ($event->getRequestType() !== HttpKernelInterface::MAIN_REQUEST) {
            return;
        }

        $preferredColorScheme = $event->getRequest()->headers->get('Sec-CH-Prefers-Color-Scheme');
        $preferredColorScheme = \strtolower((string) $preferredColorScheme);

        if (!\in_array($preferredColorScheme, ['light', 'dark'], true)) {
            $preferredColorScheme = 'light';
        }

        $this->twig->addGlobal('colorScheme', $preferredColorScheme);
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $event->getResponse()->headers->set('Accept-CH', 'Sec-CH-Prefers-Color-Scheme');
        $event->getResponse()->headers->set('Vary', 'Sec-CH-Prefers-Color-Scheme');
        $event->getResponse()->headers->set('Critical-CH', 'Sec-CH-Prefers-Color-Scheme');
    }
}
