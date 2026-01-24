<?php

namespace App\Service;

/**
 * Service pour générer et valider les codes OTP (One-Time Password)
 * Utilise password_hash/password_verify pour stocker uniquement le hash
 */
class OtpGenerator
{
    private const OTP_LENGTH = 6;
    private const OTP_VALIDITY = 600; // 10 minutes en secondes

    /**
     * Génère un code OTP de 6 chiffres
     * 
     * @return string Code OTP (ex: "123456")
     */
    public function generateOtp(): string
    {
        $otp = '';
        for ($i = 0; $i < self::OTP_LENGTH; $i++) {
            $otp .= random_int(0, 9);
        }
        
        return $otp;
    }

    /**
     * Hash un code OTP pour le stockage sécurisé
     * 
     * @param string $otp Code OTP en clair
     * @return string Hash du code OTP
     */
    public function hashOtp(string $otp): string
    {
        return password_hash($otp, PASSWORD_BCRYPT);
    }

    /**
     * Vérifie si un code OTP correspond au hash stocké
     * 
     * @param string $otp Code OTP saisi par l'utilisateur
     * @param string $hash Hash stocké en base de données
     * @return bool True si le code est valide
     */
    public function verifyOtp(string $otp, string $hash): bool
    {
        return password_verify($otp, $hash);
    }

    /**
     * Retourne la durée de validité d'un OTP en secondes
     * 
     * @return int Durée en secondes
     */
    public function getValidityDuration(): int
    {
        return self::OTP_VALIDITY;
    }
}
