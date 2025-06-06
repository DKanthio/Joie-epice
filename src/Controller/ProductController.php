<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Product;

class ProductController extends AbstractController
{
    
    private $entityManager;
  

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
      
    }

    /**
     * @Route("/product/{id}", name="product_details")
     */
    public function productDetails($id)
    {
        $product = $this->entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id ' . $id
            );
        }

        return $this->render('home/product_details.html.twig', [
            'product' => $product
        ]);
    }
}
