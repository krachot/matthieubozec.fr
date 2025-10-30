<?php

declare(strict_types=1);

namespace App\Page\Repository;

use App\Page\Page;
use App\Page\PageLoaderInterface;
use App\Page\PageRepositoryInterface;

class PageRepository implements PageRepositoryInterface
{
    public function __construct(
        private readonly PageLoaderInterface $pageLoader,
        /** @var list<Page> */
        private array $pages = [],
        private bool $loaded = false,
    ) {
    }

    /**
     * @return list<Page>
     */
    public function all(): array
    {
        if (!$this->loaded) {
            $this->doLoadPages();
        }

        return $this->pages;
    }

    public function find(string $key): ?Page
    {
        if (!$this->loaded) {
            $this->doLoadPages();
        }

        foreach ($this->pages as $page) {
            if ($page->key() === $key) {
                return $page;
            }
        }

        return null;
    }

    public function findByPermalink(string $permalink): ?Page
    {
        if (!$this->loaded) {
            $this->doLoadPages();
        }

        $permalink = trim($permalink, '/');

        foreach ($this->pages as $page) {
            if ($page->permalink() === $permalink) {
                return $page;
            }
        }

        return null;
    }

    private function doLoadPages(): void
    {
        if (true === $this->loaded) {
            return;
        }

        $this->pages = $this->pageLoader->load();
        $this->loaded = true;
    }
}
