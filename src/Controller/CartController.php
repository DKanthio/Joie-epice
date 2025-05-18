<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/add-to-cart/{productId}', name: 'add_to_cart')]
    public function addToCart(int $productId, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find($productId);
    
        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }
    
        // Check available stock
        if ($product->getStock() <= 0) {
            $this->addFlash('error', 'Product out of stock');
            return $this->redirectToRoute('cart');
        }
    
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('login');
        }
    
        $cartItem = $this->entityManager
            ->getRepository(Cart::class)
            ->findOneBy(['user' => $user, 'product' => $product]);
    
        if ($cartItem) {
            $cartItem->setQuantity($cartItem->getQuantity() + 1);
        } else {
            $cartItem = new Cart();
            $cartItem->setUser($user);
            $cartItem->setProduct($product);
            $cartItem->setQuantity(1);
        }
    
        // Deduct from stock
        $product->setStock($product->getStock() - 1);
    
        $this->entityManager->persist($cartItem);
        $this->entityManager->flush();
    
        return $this->redirectToRoute('cart');
    }
    
    #[Route('/cart', name: 'cart')]
    public function viewCart(Request $request): Response
    {
        $user = $this->getUser();
    
        if (!$user) {
            return $this->redirectToRoute('login');
        }
    
        $cartItems = $this->entityManager
            ->getRepository(Cart::class)
            ->findBy(['user' => $user]);
    
        $total = array_reduce($cartItems, function($carry, Cart $cart) {
            return $carry + ($cart->getProduct()->getPrice() * $cart->getQuantity());
        }, 0);
    
        // Save the total in the session
        $request->getSession()->set('total', $total);
    
        return $this->render('home/cart.html.twig', [
            'cart' => $cartItems,
            'total' => $total
        ]);
    }

    #[Route('/remove-from-cart/{id}', name: 'remove_from_cart')]
    public function removeFromCart(int $id): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('login');
        }
    
        $cartItem = $this->entityManager
            ->getRepository(Cart::class)
            ->find($id);
    
        if ($cartItem && $cartItem->getUser() === $user) {
            // Retrieve the product and restore the quantity
            $product = $cartItem->getProduct();
            $product->setStock($product->getStock() + $cartItem->getQuantity());
    
            $this->entityManager->remove($cartItem);
            $this->entityManager->flush();
        }
    
        return $this->redirectToRoute('cart');
    }
    
    #[Route('/increase-quantity/{id}', name: 'increase_quantity')]
    public function increaseQuantity(int $id): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('login');
        }
    
        $cartItem = $this->entityManager
            ->getRepository(Cart::class)
            ->find($id);
    
        if ($cartItem && $cartItem->getUser() === $user) {
            $product = $cartItem->getProduct();
    
            // Check available stock
            if ($product->getStock() > 0) {
                $cartItem->setQuantity($cartItem->getQuantity() + 1);
                // Deduct from stock
                $product->setStock($product->getStock() - 1);
                $this->entityManager->flush();
            } else {
                $this->addFlash('error', 'This product is out of stock');
            }
        }
    
        return $this->redirectToRoute('cart');
    }
    
    #[Route('/decrease-quantity/{id}', name: 'decrease_quantity')]
    public function decreaseQuantity(int $id): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('login');
        }
    
        $cartItem = $this->entityManager
            ->getRepository(Cart::class)
            ->find($id);
    
        if ($cartItem && $cartItem->getUser() === $user) {
            $product = $cartItem->getProduct();
    
            // Check if quantity is greater than 1
            if ($cartItem->getQuantity() > 1) {
                $cartItem->setQuantity($cartItem->getQuantity() - 1);
                // Restore stock
                $product->setStock($product->getStock() + 1);
            } else {
                // Remove the cart item and restore stock
                $product->setStock($product->getStock() + 1);
                $this->entityManager->remove($cartItem);
            }
            $this->entityManager->flush();
        }
    
        return $this->redirectToRoute('cart');
    }
    
}
