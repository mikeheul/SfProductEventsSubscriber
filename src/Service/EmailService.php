<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendProductNotification(
        string $subject, 
        string $message, 
        string $to = 'admin@shop.com',
        string $pdfPath = null)
    {
        $email = (new Email())
            ->from('admin@shop.com')
            ->to($to)
            ->subject($subject)
            ->text($message);

        if ($pdfPath && file_exists($pdfPath)) {
            $email->attachFromPath($pdfPath);
        }

        $this->mailer->send($email);
    }
}
