<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminControllerTest extends WebTestCase
{
    private $client;
    private $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = $this->client->getContainer()->get('doctrine')->getManager();
    }

    private function createAdminUser(): User
    {
        $uniqueId = uniqid('admin_', true);

        $user = new User();
        $user->setEmail($uniqueId . '@example.com');
        $user->setUsername($uniqueId);
        $user->setPassword(password_hash('adminpass', PASSWORD_BCRYPT));
        $user->setRoles(['ROLE_ADMIN']);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    private function createIsolatedProduct(): Product
    {
        $product = new Product();
        $product->setName('Produit test');
        $product->setPrice(9.99);
        $product->setDescription('This is a test product.');
        $product->setImage('image.jpg');

        $this->em->persist($product);
        $this->em->flush();

        return $product;
    }

    public function testAccessDeniedForUnauthenticated(): void
    {
        $this->client->request('GET', '/admin');
        $this->assertResponseRedirects('/login');
    }

    public function testAdminDashboardLoads(): void
    {
        $admin = $this->createAdminUser();
        $this->client->loginUser($admin);

        $crawler = $this->client->request('GET', '/admin');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('body');
    }

     public function testDeleteExistingProduct(): void
{
    $admin = $this->createAdminUser();
    $product = $this->createIsolatedProduct();
    $productId = $product->getId(); 

    $this->assertNotNull($productId, 'L\'ID du produit ne doit pas être null');

    $this->client->loginUser($admin);

    $this->client->request('POST', '/admin', [
        'supprimer' => true,
        'productId' => $productId,
    ]);

    $this->assertResponseRedirects('/admin');

    $this->em->clear(); 

    
    $deleted = $this->em->getRepository(Product::class)->find($productId);

    $this->assertNull($deleted, 'Le produit n’a pas été supprimé');
}


    public function testEditExistingProduct(): void
    {
        $admin = $this->createAdminUser();
        $product = $this->createIsolatedProduct();

        $this->client->loginUser($admin);

        $this->client->request('POST', '/admin', [
            'modifier' => true,
            'productId' => $product->getId(),
            'name' => 'Produit modifié',
            'price' => 42,
            'image' => 'modif.jpg',
            'description' => 'This is a test product.',
        ]);

        $this->assertResponseRedirects('/admin');

        $this->em->clear();
        $updated = $this->em->getRepository(Product::class)->find($product->getId());

        $this->assertSame('Produit modifié', $updated->getName());
        $this->assertSame(42.0, $updated->getPrice());
    }
}
