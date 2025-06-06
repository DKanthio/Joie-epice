<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Creates a new admin user with email, password, and username',
)]
class CreateAdminCommand extends Command
{
    private $entityManager;
    private $passwordHasher;
    private $mailer;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, MailerInterface $mailer)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->mailer = $mailer;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email of the admin')
            ->addArgument('password', InputArgument::REQUIRED, 'Password for the admin')
            ->addArgument('username', InputArgument::REQUIRED, 'Username for the admin'); 
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $username = $input->getArgument('username'); 

        $user = new User();
        $user->setEmail($email);
        $user->setUsername($username);
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

       
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $emailMessage = (new TemplatedEmail())
            ->from(new Address('diraicha25@gmail.com', 'Aicha'))
            ->to($email)
            ->subject('Création de votre compte administrateur')
            ->context([
                'username' => $username,
                'user_email' => $email,
            ]);
        $this->mailer->send($emailMessage);

        $io->success("L'utilisateur administrateur avec l'email $email, le username $username a été créé avec succès.");

        return Command::SUCCESS;
    }
}
