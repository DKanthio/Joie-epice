<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Check if the user is logged in and has the administrator role
        if (!$this->getUser() || !in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            throw $this->createAccessDeniedException('Accès refusé. Veuillez vous connecter en tant qu\'administrateur.');
        }

        // Handle adding a product
        if ($request->isMethod('POST') && $request->request->has('ajouter')) {
            $product = new Product();
            $product->setName($request->request->get('name'));
            $product->setPrice($request->request->get('price'));
            $product->setImage($request->request->get('image'));
            $product->setDescription($request->request->get('description'));
            $product->setStock(2); 
            $entityManager->persist($product);
            $entityManager->flush();
            return $this->redirectToRoute('admin_dashboard');
        }

        // Handle deleting a product
        if ($request->isMethod('POST') && $request->request->has('supprimer')) {
            $productId = $request->request->get('productId');
            $product = $entityManager->getRepository(Product::class)->find($productId);
            if ($product) {
                $entityManager->remove($product);
                $entityManager->flush();
            }
            return $this->redirectToRoute('admin_dashboard');
        }

        // Handle updating a product
        if ($request->isMethod('POST') && $request->request->has('modifier')) {
            $productId = $request->request->get('productId');
            $product = $entityManager->getRepository(Product::class)->find($productId);
            if ($product) {
                $product->setName($request->request->get('name'));
                $product->setPrice($request->request->get('price'));
                $product->setImage($request->request->get('image'));
                $product->setDescription($request->request->get('description'));
                $entityManager->flush();
            }
            return $this->redirectToRoute('admin_dashboard');
        }

        // Fetching products
        $products = $entityManager->getRepository(Product::class)->findAll();
        
        return $this->render('admin/dashboard.html.twig', [
            'products' => $products,
        ]);
    }
}
