<?php

declare(strict_types=1);

namespace App\Page;

final readonly class TemplateData
{
    public function __construct(
        /** @var array<string|int, mixed> */
        private array $data = [],
    ) {
    }

    /**
     * @return array<string|int, mixed>
     */
    public function all(): array
    {
        return $this->data;
    }

    public function getString(string $key, string $default = ''): string
    {
        $value = $this->get($key, $default);
        if (!\is_scalar($value) && !$value instanceof \Stringable) {
            throw new \RuntimeException(\sprintf('Parameter value "%s" cannot be converted to "string".', $key));
        }

        return (string) $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (!str_contains($key, '.')) {
            return $this->data[$key] ?? $default;
        }

        $array = $this->data;
        foreach (explode('.', $key) as $segment) {
            if (\is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }
}
