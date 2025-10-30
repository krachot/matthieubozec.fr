<?php

declare(strict_types=1);

namespace App\Contact\Model;

use App\Contact\Enum\BudgetEnum;
use App\Contact\Enum\PrestationTypeEnum;
use App\Infrastructure\Validator\Honeypot;
use App\Infrastructure\Validator\RateLimit;
use Symfony\Component\Validator\Constraints as Assert;

class ContactMessage
{
    public function __construct(
        #[Assert\NotBlank(message: 'Veuillez saisir votre nom')]
        public ?string $name = null,
        #[Assert\NotBlank(message: 'Veuillez saisir votre adresse email')]
        #[Assert\Email]
        #[RateLimit(message: 'Vous avez atteint la limite d\'envois. Réessayez plus tard.')]
        public ?string $email = null,
        #[Assert\Type(PrestationTypeEnum::class)]
        #[Assert\NotBlank(message: 'Veuillez saisir un type de prestation')]
        public ?PrestationTypeEnum $prestationType = null,
        #[Assert\Type(BudgetEnum::class)]
        #[Assert\NotBlank(message: 'Veuillez saisir un budget indicatif')]
        public ?BudgetEnum $budget = null,
        #[Assert\NotBlank(message: 'Veuillez saisir votre message')]
        public ?string $message = null,
        #[Honeypot(message: 'Bot détecté. Votre message n\'a pas été envoyé.')]
        public ?string $nickname = null,
        public readonly \DateTimeImmutable $createdAt = new \DateTimeImmutable(timezone: new \DateTimeZone('UTC')),
    ) {
    }
}
