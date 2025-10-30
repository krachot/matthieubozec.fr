<?php

declare(strict_types=1);

namespace App\Infrastructure\Twig\Extension;

use App\Page\Navigation\Navigation;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;

class NavigationExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly Navigation $navigation,
    ) {
    }

    /**
     * @return list<TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('breadcrumb', [$this->navigation, 'getBreadcrumb']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getGlobals(): array
    {
        return [
            'navigation' => $this->navigation->getNavigation(),
            'flatten_navigation' => $this->navigation->getFlatNavigation(),
        ];
    }
}
