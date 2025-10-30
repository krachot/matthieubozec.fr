<?php

declare(strict_types=1);

namespace App\Page;

readonly class Page
{
    public function __construct(
        private string $key,
        private string $templatePath,
        private string $templateContent,
        private TemplateData $templateData,
        private ?\DateTimeImmutable $lastModified = null,
    ) {
    }

    public function __toString(): string
    {
        return $this->key();
    }

    public function key(): string
    {
        return $this->key;
    }

    public function layout(): string
    {
        return $this->templateData->getString('layout', 'default');
    }

    public function permalink(): string
    {
        if ($value = $this->templateData->getString('permalink')) {
            return $value;
        }

        return '';
    }

    /**
     * @return array<string|int, mixed>
     */
    public function data(): array
    {
        return $this->templateData->all();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->templateData->get($key, $default);
    }

    public function content(): string
    {
        return $this->templateContent;
    }

    public function templatePath(): string
    {
        return $this->templatePath;
    }

    public function lastModified(): ?\DateTimeImmutable
    {
        return $this->lastModified;
    }
}
