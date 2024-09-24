<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


/**
 * Class NotificationService
 *
 * This service handles sending email notifications.
 */
class NotificationService
{
    private $mailer;
    private string $mailerFrom;
    private string $mailerTo;

    /**
     * NotificationService constructor.
     *
     * @param MailerInterface $mailer The mailer service used to send emails.
     * @param ParameterBagInterface $params The parameter bag containing configuration parameters.
     */
    public function __construct(MailerInterface $mailer, ParameterBagInterface $params)
    {
        $this->mailer = $mailer;
        $this->mailerFrom = $params->get('mailer_from');
        $this->mailerTo = $params->get('mailer_to');
    }

    /**
     * Sends an email notification.
     *
     * @param string $subject The subject of the email.
     * @param string $message The body message of the email.
     *
     * @throws \RuntimeException If the email fails to send.
     */
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
