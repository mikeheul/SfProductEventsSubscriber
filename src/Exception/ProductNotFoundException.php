<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductNotFoundException extends NotFoundHttpException
{
    public function __construct($message = "Produit non trouvé", \Throwable $previous = null)
    {
        parent::__construct($message, $previous);
    }
}
