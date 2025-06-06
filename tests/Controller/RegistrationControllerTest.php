<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\UserRepository;

class RegistrationControllerTest extends WebTestCase
{
    public function testDisplayRegisterForm(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorTextContains('h1', "S'inscrire");
    }

    public function testSubmitValidRegistrationForm(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $userRepository = $container->get(UserRepository::class);

        // Clean up the test user if it already exists to avoid unique constraint conflicts
        $existingUser = $userRepository->findOneBy(['username' => 'testuser']);
        if ($existingUser) {
            $em = $container->get('doctrine')->getManager();
            $em->remove($existingUser);
            $em->flush();
        }

        $crawler = $client->request('GET', '/register');

        $form = $crawler->selectButton('Créer un compte')->form([
            'registration_form[username]' => 'testuser',
            'registration_form[email]' => 'testuser@example.com',
            'registration_form[plainPassword]' => 'Password123',
            'registration_form[confirmPassword]' => 'Password123',
        ]);

        $client->submit($form);

        // Check that the response is a redirect (HTTP status 302)
        $this->assertResponseRedirects();

        // Follow the redirect
        $client->followRedirect();

        // Assert that a success message is displayed
        $this->assertSelectorTextContains('body', "Votre inscription s'est bien effectuée");

        // Check that the user was created in the database
        $user = $userRepository->findOneBy(['username' => 'testuser']);
        $this->assertNotNull($user, 'The user "testuser" should exist in the database after registration.');
        $this->assertSame('testuser@example.com', $user->getEmail());
    }
}
