<?php

declare(strict_types=1);

namespace App\Contact;

use App\Contact\Model\ContactMessage;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;

class ContactNotification extends Notification implements EmailNotificationInterface
{
    public function __construct(
        private ContactMessage $message,
    ) {
        parent::__construct(sprintf('Formulaire de contact - %s', $this->message->name));
    }

    public function asEmailMessage(EmailRecipientInterface $recipient, ?string $transport = null): ?EmailMessage
    {
        $message = EmailMessage::fromNotification($this, $recipient);
        /** @var TemplatedEmail $templatedEmail */
        $templatedEmail = $message->getMessage();
        $templatedEmail
            ->htmlTemplate('emails/contact_notification.html.twig')
            ->context(['message' => $this->message])
        ;

        return $message;
    }
}
