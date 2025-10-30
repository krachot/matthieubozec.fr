<?php

declare(strict_types=1);

namespace App\Infrastructure\Twig\Loader;

use App\Page\PageRepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Error\LoaderError;
use Twig\Loader\LoaderInterface;
use Twig\Source;

readonly class PageTemplateLoader implements LoaderInterface
{
    public function __construct(
        private PageRepositoryInterface $repository,
        #[Autowire(value: '%kernel.project_dir%/templates/page')]
        private string $basePath,
    ) {
    }

    public function getSourceContext(string $name): Source
    {
        $page = $this->repository->find($name);
        if (!$page) {
            throw new LoaderError(sprintf('Page template "%s" not found.', $name));
        }

        $pageContent = $page->content();
        $layoutToExtends = sprintf('layout/%s.html.twig', $page->layout());
        $twigSource = sprintf("{%% extends \"%s\" %%}\n\n%s", $layoutToExtends, $pageContent);

        return new Source($twigSource, $name, $page->templatePath());
    }

    public function getCacheKey(string $name): string
    {
        $page = $this->repository->find($name);
        if (!$page) {
            throw new LoaderError(sprintf('Page template "%s" not found.', $name));
        }

        $len = \strlen($this->basePath);
        if (0 === strncmp($this->basePath, $page->templatePath(), $len)) {
            return substr($page->templatePath(), $len);
        }

        return $page->templatePath();
    }

    public function isFresh(string $name, int $time): bool
    {
        $page = $this->repository->find($name);
        if (!$page) {
            return false;
        }

        return new \SplFileInfo($page->templatePath())->getMTime() <= $time;
    }

    public function exists(string $name): bool
    {
        return (bool) $this->repository->find($name);
    }
}
