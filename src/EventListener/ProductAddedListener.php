<?php

namespace App\EventListener;

use App\Event\ProductAddedEvent;
use Psr\Log\LoggerInterface;

class ProductAddedListener
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onProductAdded(ProductAddedEvent $event)
    {
        $product = $event->getProduct();
        $this->logger->info('Un nouveau produit a été ajouté : ' . $product->getName());
    }
}