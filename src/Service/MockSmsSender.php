<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

/**
 * Implémentation mock de SmsSenderInterface pour le développement
 * Log le SMS au lieu de l'envoyer réellement
 */
class MockSmsSender implements SmsSenderInterface
{
    public function __construct(
        private ?LoggerInterface $logger = null
    ) {
    }

    /**
     * Envoie un SMS (mock) - log le message au lieu de l'envoyer
     * 
     * @param string $phoneNumber Numéro de téléphone
     * @param string $otpCode Code OTP
     * @return bool Toujours true en mode mock
     */
    public function sendOtp(string $phoneNumber, string $otpCode): bool
    {
        $message = sprintf(
            '[MOCK SMS] Envoi du code OTP %s au numéro %s',
            $otpCode,
            $phoneNumber
        );

        if ($this->logger) {
            $this->logger->info($message);
        } else {
            // Fallback si pas de logger : afficher dans error_log
            error_log($message);
        }

        // En production, remplacer par un vrai service SMS
        // Exemple avec Twilio, Nexmo, etc.
        
        return true;
    }
}
