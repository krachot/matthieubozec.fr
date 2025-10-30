<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Page\Page;
use App\Page\PageRendererInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Attribute\Route;

class PageController
{
    #[Route(
        path: '{permalink}',
        name: 'page',
        requirements: ['permalink' => '.*'],
        methods: ['GET'],
        priority: -1,
    )]
    public function __invoke(
        Request $request,
        #[ValueResolver('page')]
        Page $page,
        PageRendererInterface $pageRenderer,
    ): Response {
        return $pageRenderer->render($page, $request);
    }
}
