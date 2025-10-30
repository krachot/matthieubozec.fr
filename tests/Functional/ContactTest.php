<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Contact\Enum\BudgetEnum;
use App\Contact\Enum\PrestationTypeEnum;
use App\Tests\Support\DatabaseResetTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Mailer\Test\InteractsWithMailer;
use Zenstruck\Mailer\Test\TestEmail;

class ContactTest extends WebTestCase
{
    use HasBrowser;
    use InteractsWithMailer;
    use DatabaseResetTrait;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->resetDatabase();
    }

    public function testDisplayContactForm(): void
    {
        $this->browser()
            ->get('/contact')
            ->assertStatus(200)
            ->assertSee('form');
    }

    public function testSubmitValidContactFormSendsMailAndStoresEntry(): void
    {
        $fakeEmail = sprintf('bob-%s@exemple.com', uniqid('', true));

        $this->browser()
            ->get('/contact')
            ->fillField('contact_message[name]', 'Bob')
            ->fillField('contact_message[email]', $fakeEmail)
            ->selectField('contact_message[prestationType]', PrestationTypeEnum::APPLICATION_WEB->value)
            ->selectField('contact_message[budget]', BudgetEnum::P2->value)
            ->fillField('contact_message[message]', 'Pouvez-vous me rappeler ?')
            ->click('button[type=submit]')
            ->followRedirects()
            ->assertSeeElement('[role="status"]')
        ;

        $this->mailer()
            ->assertSentEmailCount(1)
            ->assertEmailSentTo('bonjour@matthieubozec.fr', function (TestEmail $email) use ($fakeEmail) {
                $email
                    ->assertSubject('Formulaire de contact - Bob')
                    ->assertHtmlContains($fakeEmail)
                ;
            })
        ;

        $conn = self::getContainer()->get('doctrine.dbal.default_connection');
        /** @var int $count */
        $count = $conn->fetchOne('SELECT COUNT(*) FROM contact_messages');
        $this->assertSame(1, $count);
    }

    public function testSubmitInvalidContactFormShowErrors(): void
    {
        $this->browser()
            ->get('/contact')
            ->click('button[type=submit]')
            ->followRedirects()
            ->assertSeeElement('[role="alert"]')
        ;

        $this->mailer()
            ->assertSentEmailCount(0)
        ;

        $conn = self::getContainer()->get('doctrine.dbal.default_connection');
        /** @var int $count */
        $count = $conn->fetchOne('SELECT COUNT(*) FROM contact_messages');
        $this->assertSame(0, $count);
    }
}
