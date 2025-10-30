<?php

declare(strict_types=1);

namespace App\Tests\Integration\Page\Loader;

use App\Page\Loader\FilesystemPageLoader;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FilesystemPageLoaderTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testLoad(): void
    {
        /** @var FilesystemPageLoader $loader */
        $loader = self::getContainer()->get(FilesystemPageLoader::class);

        $pages = $loader->load();
        $this->assertNotEmpty($pages, 'Expected at least one Page to be loaded.');

        $byKey = [];
        foreach ($pages as $page) {
            $byKey[$page->key()] = $page;
        }

        $keys = [
            'index',
            'about',
            'services',
            'services_applications',
            'services_integration',
            'services_laravel',
            'services_react',
            'services_symfony',
            'services_integration',
            'services_wordpress',
            'services_prestashop',
            'services_site',
            'services_maintenance',
        ];

        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $byKey);
        }
    }
}
