<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthControllerTest extends WebTestCase
{
    private $client;
    private $em;
    private $passwordHasher;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = $this->client->getContainer();

        $this->em = $container->get('doctrine')->getManager();
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);

        
        $connection = $this->em->getConnection();
        $platform = $connection->getDatabasePlatform();

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        $connection->executeStatement($platform->getTruncateTableSQL('user', true));
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function createTestUser(string $email = null): User
    {
        if (!$email) {
            $email = uniqid('testuser_', true) . '@example.com';
        }

        $user = new User();
        $user->setUsername($email); 
        $user->setEmail($email);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'testpass'));
        $user->setRoles(['ROLE_USER']);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function testForgotPasswordWithExistingEmail()
    {
        $user = $this->createTestUser();

        $crawler = $this->client->request('POST', '/mot-de-passe-oublie', [
            'email' => $user->getEmail(),
        ]);

        
        $this->assertResponseRedirects('/confirmer-reset-mot-de-passe/' . $user->getEmail());
    }

    public function testForgotPasswordWithNonExistingEmail()
    {
        $this->client->request('POST', '/mot-de-passe-oublie', [
            'email' => 'inconnu@example.com',
        ]);

        $response = $this->client->getResponse();

       
        $this->assertTrue(
            $response->isServerError(),
            'La requête avec un email inconnu doit renvoyer une erreur serveur 500.'
        );

     
    }

    public function testConfirmResetPasswordUpdatesPassword()
    {
        $user = $this->createTestUser();

        $newPassword = 'newpassword123';

       
        $crawler = $this->client->request('GET', '/confirmer-reset-mot-de-passe/' . $user->getEmail());
        $this->assertResponseIsSuccessful();

        // POST submission of the new password
        $this->client->request('POST', '/confirmer-reset-mot-de-passe/' . $user->getEmail(), [
            'password' => $newPassword,
        ]);

        $this->assertResponseRedirects('/login');

       
        $userFromDb = $this->em->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
        $this->assertNotNull($userFromDb, "L'utilisateur doit exister en base après la mise à jour");

        // Verify that the password has been successfully changed
        $this->assertTrue(
            $this->passwordHasher->isPasswordValid($userFromDb, $newPassword),
            'Le mot de passe doit être mis à jour dans la base'
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
        $this->em = null; 
    }
}
