<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    public function testHomePageDisplaysProducts(): void
    {
        $client = static::createClient();

       
        $client->request('GET', '/');

        // Check that the HTTP response is 200 OK
        $this->assertResponseIsSuccessful();

       
        $this->assertSelectorExists('.card');
    }
}
