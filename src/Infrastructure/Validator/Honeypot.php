<?php

declare(strict_types=1);

namespace App\Infrastructure\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Honeypot extends Constraint
{
    public string $message = 'Activité suspicieuse détectée.';

    public function __construct(
        ?string $message = null,
        mixed $options = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct($options, $groups, $payload);

        if ($message) {
            $this->message = $message;
        }
    }
}
