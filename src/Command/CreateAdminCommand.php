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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Creates a new admin user with email, password, and username',
)]
class CreateAdminCommand extends Command
{
    private $entityManager;
    private $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email of the admin')
            ->addArgument('password', InputArgument::REQUIRED, 'Password for the admin')
            ->addArgument('username', InputArgument::REQUIRED, 'Username for the admin'); // Ajout de l'argument username
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Récupérez les arguments passés dans la commande
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $username = $input->getArgument('username'); // Nouveau champ pour l'username

        // Créez un nouvel utilisateur avec les rôles administratifs
        $user = new User();
        $user->setEmail($email);
        $user->setUsername($username); // Définissez l'username
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        // Persistez l'utilisateur dans la base de données
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success("L'utilisateur administrateur avec l'email $email, le username $username a été créé avec succès.");

        return Command::SUCCESS;
    }
}
