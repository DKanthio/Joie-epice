<?php

namespace App\Security;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class EmailVerifier
{
    public function __construct(
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private MailerInterface $mailer,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Sends the email confirmation.
     */
    public function sendEmailConfirmation(string $verifyEmailRouteName, UserInterface $user, TemplatedEmail $email): void
    {
        // Generate the email verification signature
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            $verifyEmailRouteName,
            $user->getId(),
            $user->getUsername(),
            ['id' => $user->getId()]
        );
        $expiresInHours = 1;
        // Add verification URL and expiration details to the email context
        $context = $email->getContext();
        $context['signedUrl'] = $signatureComponents->getSignedUrl();
        $context['expiresAtMessage'] = "Ce lien expire dans " . ($expiresInHours === 1 ? "1 heure" : "$expiresInHours heures");
        $email->context($context);

        // Send the email
        $this->mailer->send($email);
    }

    /**
     * Handles the email confirmation.
     * 
     * @throws VerifyEmailExceptionInterface
     */
    public function handleEmailConfirmation(Request $request, UserInterface $user): void
    {
        // Validate the email confirmation
        $this->verifyEmailHelper->validateEmailConfirmation($request->getUri(), $user->getId(), $user->getUsername());

        // Mark the user as verified
        $user->setIsVerified(true);

        // Persist the changes
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
