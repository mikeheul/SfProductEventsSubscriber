<?php

namespace App\EventSubscriber;

use App\Entity\ProductEventLog;
use App\Event\ProductAddedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductEventLogSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductAddedEvent::NAME => 'onProductAdded',
        ];
    }

    public function onProductAdded(ProductAddedEvent $event)
    {
        // Récupérer le produit
        $product = $event->getProduct();

        // Créer un log d'événement
        $productEventLog = new ProductEventLog(
            'product.added',  // ou "Product Added" etc
            'Le produit "' . $product->getName() . '" a été ajouté en BDD.'
        );

        // Persister l'événement dans la base de données
        $this->entityManager->persist($productEventLog);
        $this->entityManager->flush();
    }
}
