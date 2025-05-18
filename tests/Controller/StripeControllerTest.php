<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class StripeControllerTest extends WebTestCase
{
    public function testIndexPageShowsStripeForm()
    {
        $client = static::createClient();

       
        $session = $client->getContainer()->get('session.factory')->createSession();
        $session->set('total', 50);
        $session->save();

        
        $client->getCookieJar()->set(new \Symfony\Component\BrowserKit\Cookie($session->getName(), $session->getId()));

        
        $crawler = $client->request('GET', '/stripe');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#card-element');
        $this->assertSelectorTextContains('button', 'PAY 50€');
    }

    public function testCreateChargeRedirectsAfterPayment()
    {
        $client = static::createClient();

        
        $session = $client->getContainer()->get('session.factory')->createSession();
        $session->set('total', 10);
        $session->save();

      
        $client->getCookieJar()->set(new \Symfony\Component\BrowserKit\Cookie($session->getName(), $session->getId()));

        
        $client->request('POST', '/stripe/create-charge', [
            'stripeToken' => 'tok_visa',
        ]);

        
        $this->assertResponseRedirects('/stripe');

       
        $client->followRedirect();

        
        $this->assertSelectorTextContains('div', 'Payment Successful');
    }
}
