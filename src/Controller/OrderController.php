<?php

namespace App\Controller;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/order/history', name: 'order_history')]
    public function history(): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('login');
        }

        // Check if the user is an administrator
        if ($this->isGranted('ROLE_ADMIN')) {
            // If the user is an admin, fetch all orders
            $orders = $this->entityManager->getRepository(Order::class)->findAll();
        } else {
            // Otherwise, fetch only the orders of the logged-in user
            $orders = $this->entityManager->getRepository(Order::class)->findBy(['user' => $user]);
        }

        return $this->render('order/history.html.twig', [
            'orders' => $orders,
        ]);
    }
}
