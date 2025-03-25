<?php

namespace App\EventListener;

use App\Event\ProductRemovedEvent;
use Psr\Log\LoggerInterface;

class ProductRemovedListener
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onProductRemoved(ProductRemovedEvent $event)
    {
        $product = $event->getProduct();
        $this->logger->info('Produit supprimé : ' . $product->getName());
    }
}