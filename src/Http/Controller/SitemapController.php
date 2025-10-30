<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Page\PageRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

readonly class SitemapController
{
    public function __construct(
        private Environment $twig,
    ) {
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    #[Route(path: '/sitemap.xml', name: 'sitemap', format: 'xml')]
    public function __invoke(
        PageRepositoryInterface $pageRepository,
    ): Response {
        return new Response($this->twig->render('sitemap/sitemap.xml.twig', [
            'pages' => $pageRepository->all(),
        ]));
    }
}
