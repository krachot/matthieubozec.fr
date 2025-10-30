<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Page\PageRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Zenstruck\Browser\Test\HasBrowser;

class PageTest extends WebTestCase
{
    use HasBrowser;

    private PageRepositoryInterface $pageRepository;
    private UrlGeneratorInterface $urlGenerator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->pageRepository = self::getContainer()->get(PageRepositoryInterface::class);
        $this->urlGenerator = self::getContainer()->get(UrlGeneratorInterface::class);
    }

    public function testPage(): void
    {
        $browser = $this->browser();

        foreach ($this->pageRepository->all() as $page) {
            $browser
                ->visit($this->urlGenerator->generate('page', ['permalink' => $page->permalink()]))
                ->assertStatus(200)
            ;

            $pageTitle = $page->get('title');
            if (\is_string($pageTitle)) {
                $browser->assertSee($pageTitle);
            }
        }
    }
}
