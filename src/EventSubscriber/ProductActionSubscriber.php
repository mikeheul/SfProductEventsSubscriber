<?php

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use App\Service\EmailService;
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
    private EmailService $emailService;

    public function __construct(MailerInterface $mailer, LoggerInterface $logger, EntityManagerInterface $entityManager, EmailService $emailService)
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->emailService = $emailService;
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
        $this->handleProductEvent($event->getProduct(), 'added', 'ajouté');
    }

    public function onProductUpdated(ProductUpdatedEvent $event)
    {
        $this->handleProductEvent($event->getProduct(), 'updated', 'mis à jour');
    }

    public function onProductRemoved(ProductRemovedEvent $event)
    {
        $this->handleProductEvent($event->getProduct(), 'removed', 'supprimé');
    }

    private function handleProductEvent($product, string $eventType, string $action)
    {
        $eventKey = "product.$eventType";
        $message = "Le produit \"{$product->getName()}\" a été $action en BDD.\n";
        $message .= "Son prix est de {$product->getPrice()} €.\n";
        $message .= "Description : {$product->getDescription()}.\n";
        $message .= "Date de création : {$product->getCreatedAt()->format('d/m/Y H:i:s')}.";

        $this->logger->info($message);

        $productEventLog = new ProductEventLog($eventKey, $message);
        $this->entityManager->persist($productEventLog);
        $this->entityManager->flush();

        $this->emailService->sendProductNotification("Produit $action", $message);
    }
}
