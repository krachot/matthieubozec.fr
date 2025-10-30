<?php

declare(strict_types=1);

namespace App\Tests\Integration\Page\Repository;

use App\Page\Loader\FilesystemPageLoader;
use App\Page\Page;
use App\Page\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class PageRepositoryTest extends KernelTestCase
{
    private string $fixturesDir;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->fixturesDir = self::getContainer()
                ->getParameter('kernel.project_dir').'/tests/Fixtures/templates';
    }

    public function testRepositoryLoadsAllPagesFromFixtures(): void
    {
        $loader = new FilesystemPageLoader($this->fixturesDir);
        $repo = new PageRepository($loader);

        $pages = $repo->all();

        $this->assertCount(5, $pages);
        $this->assertContainsOnlyInstancesOf(Page::class, $pages);

        $keys = array_map(fn (Page $p) => $p->key(), $pages);
        $this->assertContains('home', $keys);
        $this->assertContains('about', $keys);
        $this->assertContains('blog_index', $keys);
        $this->assertContains('blog_post_a', $keys);
        $this->assertContains('blog_post_b', $keys);
    }

    public function testFindByKeyAndPermalinkWorkWithFixtures(): void
    {
        $loader = new FilesystemPageLoader($this->fixturesDir);
        $repo = new PageRepository($loader);

        $about = $repo->find('about');
        $this->assertInstanceOf(Page::class, $about);
        $this->assertSame('À propos', $about->get('title'));

        $postA = $repo->findByPermalink('blog/post-a');
        $this->assertInstanceOf(Page::class, $postA);
        $this->assertSame('Article A', $postA->get('title'));

        $home = $repo->findByPermalink('/');
        $this->assertInstanceOf(Page::class, $home);
        $this->assertSame('Accueil', $home->get('title'));

        $this->assertNull($repo->find('does-not-exist'));
        $this->assertNull($repo->findByPermalink('/nope/'));
    }
}
