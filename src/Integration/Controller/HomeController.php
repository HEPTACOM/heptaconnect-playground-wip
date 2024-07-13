<?php

declare(strict_types=1);

namespace HeptaConnect\Production\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class HomeController extends AbstractController
{
    public function __construct(
        private string $heptaconnectVersion,
    ) {
    }

    /**
     * @Route("/")
     */
    public function __invoke(): Response
    {
        return $this->render('@Integration/home/index.html.twig', [
            'version' => $this->heptaconnectVersion,
        ]);
    }
}
