<?php

declare(strict_types=1);

namespace App\Contact;

use App\Contact\Model\ContactMessage;

interface ContactRepositoryInterface
{
    public function save(ContactMessage $message): void;
}
