<?php

declare(strict_types=1);

namespace App\Page\Navigation;

final class NavigationNode
{
    public function __construct(
        public string $key,
        public ?string $url,
        public string $title,
        public int $order,
        public ?string $parentKey,
        public ?string $icon = null,
        /** @var list<NavigationNode> */
        public array $children = [],
    ) {
    }
}
