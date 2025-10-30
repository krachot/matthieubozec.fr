<?php

namespace App\Page;

interface PageRepositoryInterface
{
    /**
     * @return list<Page>
     */
    public function all(): array;

    public function find(string $key): ?Page;

    public function findByPermalink(string $permalink): ?Page;
}
