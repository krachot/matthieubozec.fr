<?php

declare(strict_types=1);

namespace App\Infrastructure\Twig\Extension;

use App\Page\PageRepositoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Attribute\AsTwigFunction;

readonly class PageExtension
{
    public function __construct(
        private PageRepositoryInterface $pageRepository,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @param array<string, mixed> $parameters
     */
    #[AsTwigFunction(name: 'page_path')]
    public function pagePath(string $pageKey, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): ?string
    {
        if (!$page = $this->pageRepository->find($pageKey)) {
            return null;
        }

        return $this->urlGenerator->generate('page', array_merge(['permalink' => $page->permalink()], $parameters), $referenceType);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    #[AsTwigFunction(name: 'page_url')]
    public function pageUrl(string $pageKey, array $parameters = []): ?string
    {
        if (!$page = $this->pageRepository->find($pageKey)) {
            return null;
        }

        return $this->urlGenerator->generate('page', array_merge(['permalink' => $page->permalink()], $parameters), UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
