<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class NotificationService
{
    private $mailer;
    private string $mailerFrom;
    private string $mailerTo;

    public function __construct(MailerInterface $mailer, ParameterBagInterface $params)
    {
        $this->mailer = $mailer;
        $this->mailerFrom = $params->get('mailer_from');
        $this->mailerTo = $params->get('mailer_to');
    }

    public function sendEmail(string $subject, string $message): void
    {
        $emailMessage = (new Email())
            ->from($this->mailerFrom) 
            ->to($this->mailerTo)
            ->subject($subject)
            ->text($message);
        try {
            $this->mailer->send($emailMessage);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to send notification: ' . $e->getMessage());
        }
    }
}
