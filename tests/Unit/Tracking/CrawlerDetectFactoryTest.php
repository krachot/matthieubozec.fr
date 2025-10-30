<?php

declare(strict_types=1);

namespace App\Tests\Unit\Tracking;

use App\Tracking\CrawlerDetectFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class CrawlerDetectFactoryTest extends TestCase
{
    private CrawlerDetectFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new CrawlerDetectFactory();
    }

    public function testCreateWithoutRequest(): void
    {
        $this->expectNotToPerformAssertions();

        // Act & Assert - Should not throw exception
        $this->factory->create();
    }

    public function testCreateWithNullRequest(): void
    {
        $this->expectNotToPerformAssertions();

        // Act & Assert - Should not throw exception
        $this->factory->create(null);
    }

    public function testCreateWithRequest(): void
    {
        $this->expectNotToPerformAssertions();

        // Arrange
        $request = Request::create(
            '/test-page',
            'GET',
            [],
            [],
            [],
            [
                'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
                'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.5',
                'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
                'HTTP_CONNECTION' => 'keep-alive',
                'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
            ]
        );

        // Act & Assert - Should not throw exception
        $this->factory->create($request);
    }
}
