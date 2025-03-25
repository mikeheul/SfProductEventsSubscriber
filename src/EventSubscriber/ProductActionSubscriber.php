<?php

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use App\Event\ProductAddedEvent;
use Symfony\Component\Mime\Email;
use App\Event\ProductRemovedEvent;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductActionSubscriber implements EventSubscriberInterface
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
            ProductAddedEvent::NAME => 'onProductAdded',
            ProductRemovedEvent::NAME => 'onProductRemoved',
        ];
    }

    public function onProductAdded(ProductAddedEvent $event)
    {
        // Récupérer le produit
        $product = $event->getProduct();

        // Log l'ajout du produit
        $this->logger->info('Un nouveau produit a été ajouté : ' . $product->getName());

        // Envoyer l'e-mail
        $email = (new Email())
            ->from('admin@shop.com')
            ->to('admin@shop.com')
            ->subject('Nouveau produit ajouté')
            ->text('Le produit "' . $product->getName() . '" a été ajouté.');

        $this->mailer->send($email);
    }

    public function onProductRemoved(ProductRemovedEvent $event)
    {
        // Récupérer le produit
        $product = $event->getProduct();

        // Log la suppression du produit
        $this->logger->info('Produit supprimé : ' . $product->getName());

        // Envoyer l'e-mail
        $email = (new Email())
            ->from('admin@shop.com')
            ->to('admin@shop.com')
            ->subject('Produit supprimé')
            ->text('Le produit "' . $product->getName() . '" a été supprimé.');

        $this->mailer->send($email);
    }
}