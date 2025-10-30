<?php

declare(strict_types=1);

namespace App\Tests\Unit\Page\Navigation;

use App\Page\Navigation\Navigation;
use App\Page\Navigation\NavigationBuilder;
use App\Page\Navigation\NavigationNode;
use App\Page\PageRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class NavigationTest extends TestCase
{
    public function testGetNavigationCachesResult(): void
    {
        $repository = $this->createMock(PageRepositoryInterface::class);
        $builder = $this->createMock(NavigationBuilder::class);

        $node = new NavigationNode('home', '/home', 'Accueil', 1, null);
        $builder->expects($this->once())
            ->method('build')
            ->with($repository->all() ?? [])
            ->willReturn([$node]);

        $repository->expects($this->once())
            ->method('all')
            ->willReturn([]);

        $nav = new Navigation($repository, $builder);

        // 1er appel => build exécuté
        $result1 = $nav->getNavigation();
        $this->assertSame([$node], $result1);

        // 2e appel => cache utilisé (builder non rappelé)
        $result2 = $nav->getNavigation();
        $this->assertSame($result1, $result2);
    }

    public function testGetFlatNavigationFlattensTree(): void
    {
        // Structure : home -> about -> team
        $team = new NavigationNode('team', '/team', 'Team', 3, 'about');
        $about = new NavigationNode('about', '/about', 'About', 2, 'home', null, [$team]);
        $home = new NavigationNode('home', '/home', 'Home', 1, null, null, [$about]);

        $builder = $this->createMock(NavigationBuilder::class);
        $builder->method('build')->willReturn([$home]);

        $repo = $this->createMock(PageRepositoryInterface::class);
        $repo->method('all')->willReturn([]);

        $nav = new Navigation($repo, $builder);

        $flat = $nav->getFlatNavigation();

        $this->assertCount(3, $flat);
        $this->assertSame(['home', 'about', 'team'], array_map(fn ($n) => $n->key, $flat));
    }

    public function testGetBreadcrumbForNestedNode(): void
    {
        // index -> blog -> post1
        $post1 = new NavigationNode('post1', '/post1', 'Post 1', 2, 'blog');
        $blog = new NavigationNode('blog', '/blog', 'Blog', 1, 'index', null, [$post1]);
        $index = new NavigationNode('index', '/', 'Home', 0, null, null, [$blog]);

        $builder = $this->createMock(NavigationBuilder::class);
        $builder->method('build')->willReturn([$index]);

        $repo = $this->createMock(PageRepositoryInterface::class);
        $repo->method('all')->willReturn([]);

        $nav = new Navigation($repo, $builder);

        // Breadcrumb pour post1
        $trail = $nav->getBreadcrumb('post1');

        $this->assertSame(['index', 'blog', 'post1'], array_map(fn ($n) => $n->key, $trail));
    }

    public function testGetBreadcrumbForUnknownNodeReturnsHome(): void
    {
        $index = new NavigationNode('index', '/', 'Home', 0, null);
        $builder = $this->createMock(NavigationBuilder::class);
        $builder->method('build')->willReturn([$index]);

        $repo = $this->createMock(PageRepositoryInterface::class);
        $repo->method('all')->willReturn([]);

        $nav = new Navigation($repo, $builder);

        $trail = $nav->getBreadcrumb('unknown');

        $this->assertCount(1, $trail);
        $this->assertSame('index', $trail[0]->key);
    }

    public function testGetBreadcrumbWithoutHomeReturnsEmpty(): void
    {
        $blog = new NavigationNode('blog', '/blog', 'Blog', 1, null);
        $builder = $this->createMock(NavigationBuilder::class);
        $builder->method('build')->willReturn([$blog]);

        $repo = $this->createMock(PageRepositoryInterface::class);
        $repo->method('all')->willReturn([]);

        $nav = new Navigation($repo, $builder);

        $trail = $nav->getBreadcrumb('unknown');
        $this->assertSame([], $trail);
    }
}
