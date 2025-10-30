<?php

declare(strict_types=1);

namespace App\Tests\Unit\Page\Navigation;

use App\Page\Navigation\NavigationBuilder;
use App\Page\Navigation\NavigationNode;
use App\Page\Page;
use App\Page\TemplateData;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class NavigationBuilderTest extends TestCase
{
    public function testBuildGeneratesHierarchicalNavigation(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->method('generate')
            ->willReturnCallback(fn (string $route, array $params) => $params['permalink']);

        $builder = new NavigationBuilder($urlGenerator);

        $pages = [
            $this->makePage('home', '/home', ['label' => 'Accueil',  'order' => 1]),
            $this->makePage('about', '/about', ['label' => 'À propos', 'order' => 3]),
            $this->makePage('blog', '/blog', ['label' => 'Blog',     'order' => 2]),
            $this->makePage('post1', '/blog/post1', ['label' => 'Article 1', 'parent' => 'blog', 'order' => 2]),
            $this->makePage('post0', '/blog/post0', ['label' => 'Article 0', 'parent' => 'blog', 'order' => 1]),
        ];

        $tree = $builder->build($pages);

        // 3 racines triées : home(1), blog(2), about(3)
        $this->assertCount(3, $tree);
        $this->assertSame(['home', 'blog', 'about'], array_map(fn (NavigationNode $n) => $n->key, $tree));

        // URL générées depuis le permalink contenu dans TemplateData
        $this->assertSame('/home', $tree[0]->url);
        $this->assertSame('/blog', $tree[1]->url);

        // Enfants de "blog" triés par order
        $blog = $tree[1];
        $this->assertCount(2, $blog->children);
        $this->assertSame(['post0', 'post1'], array_map(fn (NavigationNode $n) => $n->key, $blog->children));
        $this->assertSame('blog', $blog->children[0]->parentKey);
        $this->assertSame([], $blog->children[0]->children);
    }

    public function testPagesWithoutValidNavigationAreIgnored(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->method('generate')
            ->willReturnCallback(fn (string $route, array $params) => $params['permalink']);

        $builder = new NavigationBuilder($urlGenerator);

        $pages = [
            // navigation non-array -> ignorée
            $this->makePage('x', '/x', 'invalid'),
            // navigation sans label -> ignorée
            $this->makePage('y', '/y', ['order' => 1]),
            // navigation valide -> gardée
            $this->makePage('z', '/z', ['label' => 'OK']),
        ];

        $tree = $builder->build($pages);

        $this->assertCount(1, $tree);
        $this->assertSame('z', $tree[0]->key);
        $this->assertSame('OK', $tree[0]->title);
        $this->assertSame('/z', $tree[0]->url);
    }

    /** ---------------- Helpers ---------------- */
    private function makePage(string $key, string $permalink, array|string $navigation /* @phpstan-ignore missingType.iterableValue */): Page
    {
        $data = [
            'permalink' => '/'.ltrim($permalink, '/'),
            'navigation' => $navigation,
        ];

        return new Page(
            key: $key,
            templatePath: '/fake/path/'.$key.'.twig',
            templateContent: '<div>'.$key.'</div>',
            templateData: new TemplateData($data),
            lastModified: new \DateTimeImmutable()
        );
    }
}
