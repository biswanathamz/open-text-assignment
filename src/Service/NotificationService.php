<?php


namespace App\Service;

use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Notification\EmailNotification;
use Symfony\Component\Notifier\Recipient\Recipient;

class NotificationService
{
    private $notifier;

    public function __construct(NotifierInterface $notifier)
    {
        $this->notifier = $notifier;
    }

    public function notifyUser(string $email, string $message): void
    {
        $notification = new EmailNotification('Scan Notification', $message);
        $recipient = new Recipient($email);
        $this->notifier->send($notification, $recipient);
    }
}