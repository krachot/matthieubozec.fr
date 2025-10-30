<?php

declare(strict_types=1);

namespace App\Contact;

use App\Contact\Model\ContactMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Notifier\Notifier;
use Symfony\Component\Notifier\NotifierInterface;

final readonly class ContactSubmitter
{
    public function __construct(
        private ContactRepositoryInterface $contactRepository,
        /** @var Notifier $notifier */
        private NotifierInterface $notifier,
        private LoggerInterface $logger,
    ) {
    }

    public function submit(ContactMessage $message): void
    {
        try {
            $this->contactRepository->save($message);
            $this->notifier->send(new ContactNotification($message), ...$this->notifier->getAdminRecipients());

            $this->logger->info('[ContactSubmitter] message saved');
        } catch (\Throwable $exception) {
            $this->logger->error('[ContactSubmitter] submit error', [$exception]);
        }
    }
}
