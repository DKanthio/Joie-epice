<?php

namespace App\Tests\Controller;

use App\Entity\Product;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CartControllerTest extends WebTestCase
{
    public function testAddToCartWithUnauthenticatedUser(): void
{
    $client = static::createClient();
    $entityManager = $client->getContainer()->get('doctrine')->getManager();

    $product = $entityManager->getRepository(Product::class)->createQueryBuilder('p')
        ->where('p.stock > 0')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
    $this->assertNotNull($product, 'Aucun produit avec stock trouvé');

    $client->request('GET', '/add-to-cart/' . $product->getId());
    $this->assertResponseRedirects('/login');
}

    public function testAddToCartWithAuthenticatedUser(): void
{
    $client = static::createClient();
    $entityManager = $client->getContainer()->get('doctrine')->getManager();

    $user = new User();
    $user->setUsername('testusername');
    $user->setEmail('username@example.com');
    $user->setPassword(password_hash('password', PASSWORD_BCRYPT));
    $user->setRoles(['ROLE_USER']);
    $entityManager->persist($user);
    $entityManager->flush();

    $client->loginUser($user);

    $product = $entityManager->getRepository(Product::class)->createQueryBuilder('p')
        ->where('p.stock > 0')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
    $this->assertNotNull($product, 'Aucun produit avec stock trouvé');

    $client->request('GET', '/add-to-cart/' . $product->getId());
    $this->assertResponseRedirects('/cart');
}

}
