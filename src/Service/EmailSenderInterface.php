<?php

namespace App\Service;

/**
 * Interface pour l'envoi d'emails
 * Permet d'implémenter différents systèmes d'envoi (Symfony Mailer, SwiftMailer, etc.)
 */
interface EmailSenderInterface
{
    /**
     * Envoie un email avec le code OTP
     * 
     * @param string $email Adresse email du destinataire
     * @param string $otpCode Code OTP à envoyer
     * @param string $userName Nom de l'utilisateur (optionnel)
     * @return bool True si l'envoi a réussi
     * @throws \Exception En cas d'erreur d'envoi
     */
    public function sendOtp(string $email, string $otpCode, ?string $userName = null): bool;
}
