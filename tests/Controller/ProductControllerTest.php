<?php

namespace App\Tests\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductControllerTest extends WebTestCase
{
    public function testProductDetailsPageWithValidId()
    {
        $client = static::createClient();
        $container = static::getContainer();

        $entityManager = $container->get('doctrine')->getManager();

        // Retrieve a product from the database
        $product = $entityManager->getRepository(Product::class)->findOneBy([]);

        $this->assertNotNull($product, 'No product found in the database');

        // Send a request using a real product ID
        $client->request('GET', '/product/' . $product->getId());

        // Check that the response is successful
        $this->assertResponseIsSuccessful();

        // Check that the product name is displayed
        $this->assertSelectorTextContains('h2', $product->getName());

        // Check that the product price is displayed
        $this->assertSelectorTextContains('h3', (string)$product->getPrice());

        // Check that the "add to cart" button is present
        $this->assertSelectorExists('a.btn-primary');
    }

    public function testProductDetailsPageWithInvalidId()
    {
        $client = static::createClient();

        
        $client->request('GET', '/product/999999'); 
       
        $this->assertResponseStatusCodeSame(404);
    }
}
