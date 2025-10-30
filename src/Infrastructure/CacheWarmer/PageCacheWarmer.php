<?php

declare(strict_types=1);

namespace App\Infrastructure\CacheWarmer;

use App\Page\PageRepositoryInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class PageCacheWarmer implements CacheWarmerInterface
{
    public function __construct(
        private PageRepositoryInterface $pageRepository,
    ) {
    }

    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        $this->pageRepository->all();

        return [];
    }

    public function isOptional(): bool
    {
        return false;
    }
}
