<?php

declare(strict_types=1);

namespace App\Contact\Repository;

use App\Contact\ContactRepositoryInterface;
use App\Contact\Model\ContactMessage;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Types;

final readonly class DbalContactRepository implements ContactRepositoryInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    /**
     * @throws Exception
     */
    public function save(ContactMessage $message): void
    {
        $this->connection->insert('contact_messages', [
            'name' => $message->name,
            'email' => $message->email,
            'prestation_type' => $message->prestationType?->value,
            'budget' => $message->budget?->value,
            'message' => $message->message,
            'created_at' => $message->createdAt,
        ], [
            'name' => Types::STRING,
            'email' => Types::STRING,
            'prestation_type' => Types::STRING,
            'budget' => Types::STRING,
            'message' => Types::TEXT,
            'created_at' => Types::DATETIME_IMMUTABLE,
        ]);
    }
}
