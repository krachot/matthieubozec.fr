<?php

declare(strict_types=1);

namespace App\Page\Navigation;

use App\Page\PageRepositoryInterface;

final class Navigation
{
    /** @var list<NavigationNode>|null */
    private ?array $navigation = null;

    public function __construct(
        private readonly PageRepositoryInterface $pageRepository,
        private readonly NavigationBuilder $navigationBuilder,
    ) {
    }

    /**
     * Retourne les objets NavigationNode hiérarchiques.
     *
     * @return list<NavigationNode>
     */
    public function getNavigation(): array
    {
        if (null === $this->navigation) {
            /** @var list<NavigationNode> $nodes */
            $nodes = $this->navigationBuilder->build($this->pageRepository->all());
            $this->navigation = $nodes;
        }

        return $this->navigation;
    }

    /**
     * Retourne la navigation complète à plat (flatten), dans l’ordre hiérarchique et ordonné.
     *
     * @return list<NavigationNode>
     */
    public function getFlatNavigation(): array
    {
        $tree = $this->getNavigation();
        $flat = [];

        $this->flattenRecursive($tree, $flat);

        return $flat;
    }

    /**
     * Fonction récursive qui parcourt l’arborescence dans l’ordre et ajoute chaque élément à $flat.
     *
     * @param list<NavigationNode> $nodes
     * @param list<NavigationNode> $flat
     */
    private function flattenRecursive(array $nodes, array &$flat): void
    {
        foreach ($nodes as $node) {
            $flat[] = $node;

            if ([] !== $node->children) {
                $this->flattenRecursive($node->children, $flat);
            }
        }
    }

    /**
     * @return list<NavigationNode>
     */
    public function getBreadcrumb(string $currentKey): array
    {
        $tree = $this->getNavigation();

        $current = $this->findNodeByKey($tree, $currentKey);
        if (null === $current) {
            $home = $this->findNodeByKey($tree, 'index');

            return $home ? [$home] : [];
        }

        $trail = [];
        $node = $current;
        while (null !== $node) {
            array_unshift($trail, $node);
            $node = $node->parentKey ? $this->findNodeByKey($tree, $node->parentKey) : null;
        }

        $home = $this->findNodeByKey($tree, 'index');
        if (null !== $home && $trail[0]->key !== $home->key) {
            array_unshift($trail, $home);
        }

        return $trail;
    }

    /**
     * @param list<NavigationNode> $nodes
     */
    private function findNodeByKey(array $nodes, string $key): ?NavigationNode
    {
        foreach ($nodes as $node) {
            if ($node->key === $key) {
                return $node;
            }

            if ([] !== $node->children) {
                $found = $this->findNodeByKey($node->children, $key);
                if (null !== $found) {
                    return $found;
                }
            }
        }

        return null;
    }
}
