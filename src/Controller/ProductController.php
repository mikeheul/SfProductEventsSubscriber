<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Event\ProductAddedEvent;
use App\Event\ProductRemovedEvent;
use App\Event\ProductUpdatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_product')]
    public function index(): Response
    {
        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
        ]);
    }

    #[Route('/add-product', name: 'product_add')]
    public function addProduct(EntityManagerInterface $entityManager, EventDispatcherInterface $dispatcher): Response
    {
        $productNames = [
            'Télévision', 'Smartphone', 'Ordinateur portable', 'Casque audio', 'Montre connectée', 
            'Appareil photo', 'Tablette', 'Clavier mécanique', 'Souris sans fil', 'Écouteurs Bluetooth'
        ];
        
        $productPrices = [19.99, 49.99, 99.99, 199.99, 299.99, 399.99, 499.99, 799.99];
        
        $randomName = $productNames[array_rand($productNames)];
        $randomPrice = $productPrices[array_rand($productPrices)];
        
        $product = new Product();
        $product->setName($randomName);
        $product->setDescription('Description du produit');
        $product->setPrice($randomPrice);
        
        $entityManager->persist($product);
        $entityManager->flush();
        
        $dispatcher->dispatch(new ProductAddedEvent($product), ProductAddedEvent::NAME);
        
        return new Response('Produit ajouté et log enregistré !');
    }
    
    
    #[Route('/edit-product/{id}', name: 'product_edit')]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager, EventDispatcherInterface $dispatcher)
    {
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $dispatcher->dispatch(new ProductUpdatedEvent($product), ProductUpdatedEvent::NAME);
            return $this->redirectToRoute('app_product');
        }

        return $this->render('product/edit.html.twig', [
            'formEditProduct' => $form,
        ]);
        
        // return new Response('Produit modifié et log enregistré !');
    }

    #[Route('/remove-product/{id}', name: 'product_remove')]
    public function removeProduct(int $id, EntityManagerInterface $entityManager, EventDispatcherInterface $dispatcher): Response
    {
        $product = $entityManager->getRepository(Product::class)->find($id);
        
        if (!$product) {
            return new Response('Produit non trouvé', 404);
        }
        
        $entityManager->remove($product);
        $entityManager->flush();

        $dispatcher->dispatch(new ProductRemovedEvent($product), ProductRemovedEvent::NAME);

        return new Response('Produit supprimé et log enregistré !');
    }
}
