<?php

declare(strict_types=1);

namespace App\Page\Navigation;

use App\Page\Page;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class NavigationBuilder
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @param list<Page> $pages
     *
     * @return list<NavigationNode>
     */
    public function build(array $pages): array
    {
        /** @var array<string, NavigationNode> $lookup */
        $lookup = [];
        /** @var list<NavigationNode> $roots */
        $roots = [];

        foreach ($pages as $page) {
            /** @var array{'label'?: string|null, "order"?: mixed|null, "parent"?: string|null, "icon"?: string|null}|null $nav */
            $nav = $page->get('navigation', []);
            if (!\is_array($nav)) {
                continue;
            }

            $label = $nav['label'] ?? null;
            if (!\is_string($label) || '' === $label) {
                continue;
            }

            $orderRaw = $nav['order'] ?? null;
            $order = \is_int($orderRaw) || \is_string($orderRaw) && ctype_digit($orderRaw)
                ? (int) $orderRaw
                : PHP_INT_MAX;

            $parentKey = $nav['parent'] ?? null;
            $parentKey = \is_string($parentKey) && '' !== $parentKey ? $parentKey : null;

            $key = $page->key();
            $permalink = $page->permalink();

            $url = $this->urlGenerator->generate(
                'page',
                ['permalink' => $permalink],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $icon = $nav['icon'] ?? null;

            $lookup[$key] = new NavigationNode(
                key: $key,
                url: $url,
                title: $label,
                order: $order,
                parentKey: $parentKey,
                icon: $icon,
                children: [],
            );
        }

        // Lier la hiérarchie
        foreach ($lookup as $key => $node) {
            if (null !== $node->parentKey && isset($lookup[$node->parentKey])) {
                $lookup[$node->parentKey]->children[] = $node;
            } else {
                $roots[] = $node;
            }
        }

        // Trier récursivement
        $this->sortRecursive($roots);

        return $roots;
    }

    /**
     * @param list<NavigationNode> $items
     */
    private function sortRecursive(array &$items): void
    {
        \usort($items, static fn (NavigationNode $a, NavigationNode $b): int => $a->order <=> $b->order);

        foreach ($items as $item) {
            if ([] !== $item->children) {
                $this->sortRecursive($item->children);
            }
        }
    }
}
