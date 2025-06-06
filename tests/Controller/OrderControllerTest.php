<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OrderControllerTest extends WebTestCase
{
    public function testRedirectIfNotLoggedIn(): void
    {
        $client = static::createClient();
        $client->request('GET', '/order/history');

        $this->assertResponseRedirects('/login');
    }

    public function testUserOrderHistory(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $entityManager = $container->get('doctrine')->getManager();

        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'username@example.com']);
        if (!$user) {
            $this->markTestSkipped('Aucun utilisateur trouvé avec l\'email username@example.com');
        }

        $client->loginUser($user);
        $crawler = $client->request('GET', '/order/history');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Historique des Commandes');
    }

    public function testAdminOrderHistory(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $entityManager = $container->get('doctrine')->getManager();

        $admin = $entityManager->getRepository(User::class)->findOneBy(['email' => 'admin_6829cd40e9c188.42418103@example.com']);
        if (!$admin) {
            $this->markTestSkipped('Aucun administrateur trouvé avec cet email.');
        }

        $client->loginUser($admin);
        $crawler = $client->request('GET', '/order/history');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Historique des Commandes');
        $this->assertSelectorExists('th', 'Nom d\'Utilisateur'); 
    }
}
