<?php

declare(strict_types=1);

namespace App\Page\Repository;

use App\Page\Page;
use App\Page\PageRepositoryInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;

#[AsAlias]
#[AsDecorator(decorates: PageRepository::class)]
readonly class CachedPageRepository implements PageRepositoryInterface
{
    private const string CACHE_KEY = 'pages';

    public function __construct(
        #[AutowireDecorated]
        private PageRepositoryInterface $decorated,
        private CacheItemPoolInterface $pool,
        #[Autowire('%kernel.debug%')]
        private bool $debug = false,
    ) {
    }

    /**
     * @return list<Page>
     */
    public function all(): array
    {
        $cache = $this->pool->getItem(self::CACHE_KEY);

        if ($this->debug) {
            $cache->expiresAfter(-1);
        }

        if (!$cache->isHit()) {
            $cache->set($this->decorated->all());
            $this->pool->save($cache);
        }

        /** @var list<Page> $pages */
        $pages = $cache->get();

        return $pages;
    }

    public function find(string $key): ?Page
    {
        foreach ($this->all() as $page) {
            if ($page->key() === $key) {
                return $page;
            }
        }

        return null;
    }

    public function findByPermalink(string $permalink): ?Page
    {
        foreach ($this->all() as $page) {
            if ($page->permalink() === $permalink) {
                return $page;
            }
        }

        return null;
    }
}
