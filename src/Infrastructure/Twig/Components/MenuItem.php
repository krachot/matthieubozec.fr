<?php

declare(strict_types=1);

namespace App\Infrastructure\Twig\Components;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsTwigComponent(name: 'Menu:MenuItem', template: 'components/Menu/MenuItem.html.twig')]
class MenuItem
{
    public ?string $link = null;
    public ?string $icon = null;
    public ?bool $isCurrentPage = null;

    private ?Request $request = null;

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    #[PostMount]
    public function postMount(): void
    {
        if (!$this->link) {
            return;
        }

        if (null !== $this->isCurrentPage) {
            return;
        }

        $this->isCurrentPage = $this->checkIfIsCurrentPage();
    }

    private function checkIfIsCurrentPage(): bool
    {
        if (!\is_string($this->link)) {
            return false;
        }

        $menuItempathInfo = parse_url($this->link, PHP_URL_PATH);
        if (!\is_string($menuItempathInfo)) {
            return false;
        }

        if (true === ($this->getMainRequest()?->getPathInfo() === parse_url($menuItempathInfo, PHP_URL_PATH))) {
            return true;
        }

        if ('' === trim($menuItempathInfo, '/')) {
            return false;
        }

        return str_starts_with((string) $this->getMainRequest()?->getPathInfo(), $menuItempathInfo);
    }

    private function getMainRequest(): ?Request
    {
        if (!$this->request) {
            $this->request = $this->requestStack->getMainRequest();
        }

        return $this->request;
    }
}
