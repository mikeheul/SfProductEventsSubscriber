<?php

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use App\Entity\ProductEventLog;
use App\Event\ProductAddedEvent;
use Symfony\Component\Mime\Email;
use App\Event\ProductRemovedEvent;
use App\Event\ProductUpdatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductActionSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;
    private MailerInterface $mailer;
    private LoggerInterface $logger;

    public function __construct(MailerInterface $mailer, LoggerInterface $logger, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductAddedEvent::NAME => 'onProductAdded',
            ProductUpdatedEvent::NAME => 'onProductUpdated',
            ProductRemovedEvent::NAME => 'onProductRemoved',
        ];
    }

    public function onProductAdded(ProductAddedEvent $event)
    {
        // Récupérer le produit
        $product = $event->getProduct();

        // Log l'ajout du produit
        $this->logger->info('Un nouveau produit a été ajouté : ' . $product->getName());

        $productEventLog = new ProductEventLog(
            'product.added',
            'Le produit "' . $product->getName() . '" a été modifié en BDD.'
        );

        $this->entityManager->persist($productEventLog);
        $this->entityManager->flush();

        // Envoyer l'e-mail
        $email = (new Email())
            ->from('admin@shop.com')
            ->to('admin@shop.com')
            ->subject('Nouveau produit ajouté')
            ->text('Le produit "' . $product->getName() . '" a été ajouté.');

        $this->mailer->send($email);
    }

    public function onProductUpdated(ProductUpdatedEvent $event)
    {
        $product = $event->getProduct();

        // Log de mise à jour
        $this->logger->info('Produit mis à jour : ' . $product->getName());

        $productEventLog = new ProductEventLog(
            'product.updated',
            'Le produit "' . $product->getName() . '" a été modifié en BDD.'
        );

        $this->entityManager->persist($productEventLog);
        $this->entityManager->flush();

        // Envoi d'un e-mail
        $email = (new Email())
            ->from('admin@shop.com')
            ->to('admin@shop.com')
            ->subject('Produit mis à jour')
            ->text('Le produit "' . $product->getName() . '" a été mis à jour.');

        $this->mailer->send($email);
    }

    public function onProductRemoved(ProductRemovedEvent $event)
    {
        // Récupérer le produit
        $product = $event->getProduct();

        // Log la suppression du produit
        $this->logger->info('Produit supprimé : ' . $product->getName());

        $productEventLog = new ProductEventLog(
            'product.removed',
            'Le produit "' . $product->getName() . '" a été supprimé de la BDD.'
        );

        $this->entityManager->persist($productEventLog);
        $this->entityManager->flush();

        // Envoyer l'e-mail
        $email = (new Email())
            ->from('admin@shop.com')
            ->to('admin@shop.com')
            ->subject('Produit supprimé')
            ->text('Le produit "' . $product->getName() . '" a été supprimé.');

        $this->mailer->send($email);
    }
}
