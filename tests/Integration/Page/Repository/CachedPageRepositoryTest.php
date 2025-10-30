<?php

declare(strict_types=1);

namespace App\Tests\Integration\Page\Repository;

use App\Page\Loader\FilesystemPageLoader;
use App\Page\Page;
use App\Page\Repository\CachedPageRepository;
use App\Page\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

final class CachedPageRepositoryTest extends KernelTestCase
{
    private string $fixturesDir;
    private string $cacheDir;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->fixturesDir = self::getContainer()
                ->getParameter('kernel.project_dir').'/tests/Fixtures/templates';

        $this->cacheDir = self::getContainer()
                ->getParameter('kernel.cache_dir').'/page_cache';

        // Nettoyage du cache avant chaque test
        $cache = new FilesystemAdapter('', 0, $this->cacheDir);
        $cache->clear();
    }

    public function testRepositoryCachesPagesAfterFirstLoad(): void
    {
        $loader = new FilesystemPageLoader($this->fixturesDir);
        $innerRepo = new PageRepository($loader);

        $cache = new FilesystemAdapter('', 0, $this->cacheDir);

        // Cached repo réel
        $repo = new CachedPageRepository($innerRepo, $cache, debug: false);

        // 1er appel → charge depuis le décoré
        $pages1 = $repo->all();
        $this->assertNotEmpty($pages1);
        $this->assertContainsOnlyInstancesOf(Page::class, $pages1);

        // 2e appel → doit provenir du cache (pas de nouveau chargement)
        // On recrée un nouveau repo pour s'assurer que le cache est persistant
        $repo2 = new CachedPageRepository($innerRepo, $cache, debug: false);
        $pages2 = $repo2->all();

        $this->assertEquals($pages1, $pages2);
        $this->assertGreaterThanOrEqual(5, count($pages2));

        // Vérifie qu'une page est trouvable via le cache
        $about = $repo2->find('about');
        $this->assertInstanceOf(Page::class, $about);
        $this->assertSame('À propos', $about->get('title'));

        // Vérifie que le cache contient bien la clé
        $item = $cache->getItem('pages');
        $this->assertTrue($item->isHit(), 'Les pages doivent être stockées en cache');
    }

    public function testFindByPermalinkUsesCachedData(): void
    {
        $loader = new FilesystemPageLoader($this->fixturesDir);
        $innerRepo = new PageRepository($loader);
        $cache = new FilesystemAdapter('', 0, $this->cacheDir);

        $repo = new CachedPageRepository($innerRepo, $cache, debug: false);

        $page = $repo->findByPermalink('blog/post-a');
        $this->assertInstanceOf(Page::class, $page);
        $this->assertSame('Article A', $page->get('title'));

        $this->assertNull($repo->findByPermalink('unknown'));
    }
}
