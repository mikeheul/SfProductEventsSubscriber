<?php

namespace App\EventListener;

use App\Exception\ProductNotFoundException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProductExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        // Vérifier si l'exception est liée à un produit non trouvé
        if ($exception instanceof ProductNotFoundException) {
            $response = new JsonResponse([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], JsonResponse::HTTP_NOT_FOUND); 

            // Remplacer la réponse par la réponse JSON
            $event->setResponse($response);
        }
    }
}
