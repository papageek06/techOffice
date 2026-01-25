<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

/**
 * Implémentation mock de EmailSenderInterface pour le développement
 * Log l'email au lieu de l'envoyer réellement
 */
class MockEmailSender implements EmailSenderInterface
{
    public function __construct(
        private ?LoggerInterface $logger = null
    ) {
    }

    /**
     * Envoie un email (mock) - log le message au lieu de l'envoyer
     * 
     * @param string $email Adresse email
     * @param string $otpCode Code OTP
     * @param string|null $userName Nom de l'utilisateur
     * @return bool Toujours true en mode mock
     */
    public function sendOtp(string $email, string $otpCode, ?string $userName = null): bool
    {
        $message = sprintf(
            '[MOCK EMAIL] Envoi du code OTP %s à l\'adresse %s%s',
            $otpCode,
            $email,
            $userName ? sprintf(' (Utilisateur: %s)', $userName) : ''
        );

        if ($this->logger) {
            $this->logger->info($message);
        } else {
            // Fallback si pas de logger : afficher dans error_log
            error_log($message);
        }

        // En production, utiliser Symfony Mailer ou un autre service
        // Exemple avec Symfony Mailer:
        // $email = (new Email())
        //     ->from('noreply@example.com')
        //     ->to($email)
        //     ->subject('Code de vérification')
        //     ->html($this->renderEmailTemplate($otpCode, $userName));
        // $this->mailer->send($email);
        
        return true;
    }
}
