<?php

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use App\Service\EmailService;
use App\Entity\ProductEventLog;
use App\Event\ProductAddedEvent;
use App\Event\ProductRemovedEvent;
use App\Event\ProductUpdatedEvent;
use App\Service\PdfGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductActionSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;
    private EmailService $emailService;
    private PdfGeneratorService $pdfGenerator;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        EmailService $emailService,
        PdfGeneratorService $pdfGenerator
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->emailService = $emailService;
        $this->pdfGenerator = $pdfGenerator;
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
        // Génération du PDF
        $pdfPath = $this->pdfGenerator->generateProductPdf($product);
        $this->logger->info("PDF généré : " . $pdfPath);

        // Création du message de log
        $message = sprintf(
            "Le produit \"%s\" a été %s en BDD.\nPrix : %.2f €\nDescription : %s\nDate de création : %s.",
            $product->getName(),
            $action,
            $product->getPrice(),
            $product->getDescription(),
            $product->getCreatedAt()->format('d/m/Y H:i:s')
        );

        // Log de l'événement
        $this->logger->info($message);

        // Enregistrement en base de données
        $productEventLog = new ProductEventLog("product.$eventType", $message);
        $this->entityManager->persist($productEventLog);
        $this->entityManager->flush();

        // Envoi de l'e-mail avec le PDF
        $subject = "Produit $action : " . $product->getName();
        $this->emailService->sendProductNotification(
            $subject,
            $message,
            'admin@shop.com',
            $pdfPath
        );
    }
}
