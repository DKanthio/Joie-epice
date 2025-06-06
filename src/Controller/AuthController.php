<?php


namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/mot-de-passe-oublie", name="forgot_password")
     */
    public function forgotPassword(Request $request): Response
    {
        // Check if the password reset form has been submitted
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email'); 
            // Check if the email exists in the database
            $user = $this->entityManager->getRepository(User::class)->findOneByEmail($email);
    

            if ($user) {
                
                return $this->redirectToRoute('confirm_reset_password', ['email' => $email]);
            } else {
                
                $this->addFlash('error', 'Adresse e-mail non valide.');
            }
        }
       
    
        
        return $this->render('auth/forgot_password.html.twig', [
           
        ]);
    }

    /**
     * @Route("/confirmer-reset-mot-de-passe/{email}", name="confirm_reset_password")
     */
    public function confirmResetPassword(Request $request, string $email, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        // Check if the password reset confirmation form has been submitted
        if ($request->isMethod('POST')) {
            $password = $request->request->get('password');

            // Update the password in the database
            $entityManager = $this->entityManager;
            $user = $entityManager->getRepository(User::class)->findOneByEmail($email);
             
            if ($user) {
                // Hash the new password
                $hashedPassword = $userPasswordHasher->hashPassword($user, $password);
                $user->setPassword($hashedPassword);

                // Update the user entity in the database
                $entityManager->persist($user);
                $entityManager->flush();

                
                return $this->redirectToRoute('login');
            } else {
                // If the user is not found, display an error message
                $this->addFlash('error', 'Utilisateur non trouvé.');
            }
        }
       
    
        // Displays the new password input form
        return $this->render('auth/confirm_reset_password.html.twig', [
            'email' => $email,
            
        ]);
    }
}

