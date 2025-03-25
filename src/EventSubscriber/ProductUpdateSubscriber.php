<?php

namespace App\EventSubscriber;

use App\Event\ProductUpdatedEvent;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;

class ProductUpdateSubscriber implements EventSubscriberInterface
{
    private MailerInterface $mailer;
    private LoggerInterface $logger;

    public function __construct(MailerInterface $mailer, LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductUpdatedEvent::NAME => 'onProductUpdated',
        ];
    }

    public function onProductUpdated(ProductUpdatedEvent $event)
    {
        $product = $event->getProduct();

        // Log de mise à jour
        $this->logger->info('Produit mis à jour : ' . $product->getName());

        // Envoi d'un e-mail
        $email = (new Email())
            ->from('admin@shop.com')
            ->to('admin@shop.com')
            ->subject('Produit mis à jour')
            ->text('Le produit "' . $product->getName() . '" a été mis à jour.');

        $this->mailer->send($email);
    }
}
