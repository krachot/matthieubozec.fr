<?php

declare(strict_types=1);

namespace App\Page\PageRenderer;

use App\Page\Page;
use App\Page\PageRendererInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Psr\Link\EvolvableLinkProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\WebLink\GenericLinkProvider;
use Symfony\Component\WebLink\Link;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class TwigPageRenderer implements PageRendererInterface
{
    public function __construct(
        private readonly Environment $twig,
        private readonly CacheManager $cache,
    ) {
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function render(Page $page, Request $request): Response
    {
        if (($featuredImage = $page->get('featuredImage')) && \is_string($featuredImage)) {
            /** @var EvolvableLinkProviderInterface $linkProvider */
            $linkProvider = $request->attributes->get('_links', new GenericLinkProvider());

            foreach ([380, 600, 1024] as $size) {
                $urlImagePath = parse_url($featuredImage, PHP_URL_PATH);
                if (!\is_string($urlImagePath)) {
                    continue;
                }


                $imagePath = $this->cache->getBrowserPath($urlImagePath, 'image_'.$size);
                $linkProvider = $linkProvider->withLink(
                    new Link('preload', $imagePath)
                        ->withAttribute('as', 'image')
                        ->withAttribute('type', 'image/webp')
                        ->withAttribute('media', sprintf('(max-width: %svw)', $size))
                );
            }

            $request->attributes->set('_links', $linkProvider);
        }

        $response = new Response($this->twig->render((string) $page, ['page' => $page]));
        $response->setLastModified($page->lastModified());

        return $response;
    }
}
