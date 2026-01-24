<?php

namespace App\Service;

/**
 * Interface pour l'envoi de SMS
 * Permet d'implémenter différents fournisseurs (Twilio, Nexmo, etc.)
 */
interface SmsSenderInterface
{
    /**
     * Envoie un SMS avec le code OTP
     * 
     * @param string $phoneNumber Numéro de téléphone (format international recommandé)
     * @param string $otpCode Code OTP à envoyer
     * @return bool True si l'envoi a réussi
     * @throws \Exception En cas d'erreur d'envoi
     */
    public function sendOtp(string $phoneNumber, string $otpCode): bool;
}
