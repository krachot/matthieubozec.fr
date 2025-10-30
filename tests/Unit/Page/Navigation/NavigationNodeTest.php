<?php

declare(strict_types=1);

namespace App\Tests\Unit\Page\Navigation;

use App\Page\Navigation\NavigationNode;
use PHPUnit\Framework\TestCase;

final class NavigationNodeTest extends TestCase
{
    public function testItStoresProvidedValues(): void
    {
        $node = new NavigationNode(
            key: 'home',
            url: '/home',
            title: 'Accueil',
            order: 1,
            parentKey: null
        );

        $this->assertSame('home', $node->key);
        $this->assertSame('/home', $node->url);
        $this->assertSame('Accueil', $node->title);
        $this->assertSame(1, $node->order);
        $this->assertNull($node->parentKey);
        $this->assertSame([], $node->children);
    }

    public function testItCanContainChildNodes(): void
    {
        $child1 = new NavigationNode(
            key: 'about',
            url: '/about',
            title: 'À propos',
            order: 1,
            parentKey: 'home'
        );

        $child2 = new NavigationNode(
            key: 'contact',
            url: '/contact',
            title: 'Contact',
            order: 2,
            parentKey: 'home'
        );

        $parent = new NavigationNode(
            key: 'home',
            url: '/home',
            title: 'Accueil',
            order: 1,
            parentKey: null,
            children: [$child1, $child2]
        );

        $this->assertCount(2, $parent->children);
        $this->assertSame('about', $parent->children[0]->key);
        $this->assertSame('contact', $parent->children[1]->key);
        $this->assertSame('home', $parent->children[0]->parentKey);
    }
}
