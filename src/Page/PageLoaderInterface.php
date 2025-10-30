<?php

namespace App\Page;

interface PageLoaderInterface
{
    /**
     * @return list<Page>
     */
    public function load(): array;
}
