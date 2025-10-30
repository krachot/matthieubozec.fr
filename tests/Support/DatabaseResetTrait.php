<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Infrastructure\Database\ContactMessagesTableInitializer;
use Doctrine\DBAL\Connection;

trait DatabaseResetTrait
{
    protected function resetDatabase(): void
    {
        $container = static::getContainer();

        $initializer = $container->get(ContactMessagesTableInitializer::class);
        $initializer->initialize();

        /** @var Connection $conn */
        $conn = $container->get('doctrine.dbal.default_connection');
        $conn->executeStatement('DELETE FROM contact_messages');
    }
}
