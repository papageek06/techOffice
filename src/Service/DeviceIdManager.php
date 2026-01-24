<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Service pour gérer l'identification des appareils via cookie
 * Génère et valide les UUID d'appareils stockés dans des cookies sécurisés
 */
class DeviceIdManager
{
    private const COOKIE_NAME = 'device_id';
    private const COOKIE_LIFETIME = 31536000; // 1 an en secondes

    /**
     * Récupère ou crée un device_id depuis/vers le cookie
     * 
     * @param Request $request La requête HTTP
     * @return string UUID v4 de l'appareil
     */
    public function getOrCreateDeviceId(Request $request): string
    {
        $deviceId = $request->cookies->get(self::COOKIE_NAME);

        if ($deviceId && $this->isValidUuid($deviceId)) {
            return $deviceId;
        }

        // Générer un nouvel UUID v4
        return $this->generateUuid();
    }

    /**
     * Crée un cookie sécurisé pour le device_id
     * 
     * @param string $deviceId UUID de l'appareil
     * @return Cookie Le cookie configuré
     */
    public function createDeviceCookie(string $deviceId): Cookie
    {
        return Cookie::create(
            self::COOKIE_NAME,
            $deviceId,
            time() + self::COOKIE_LIFETIME,
            '/',
            null,
            true,  // Secure (HTTPS uniquement)
            true,  // HttpOnly (pas accessible en JavaScript)
            false, // Raw
            'Lax'  // SameSite
        );
    }

    /**
     * Ajoute le cookie device_id à la réponse
     * 
     * @param Response $response La réponse HTTP
     * @param string $deviceId UUID de l'appareil
     */
    public function setDeviceCookie(Response $response, string $deviceId): void
    {
        $cookie = $this->createDeviceCookie($deviceId);
        $response->headers->setCookie($cookie);
    }

    /**
     * Génère un UUID v4
     * 
     * @return string UUID v4
     */
    private function generateUuid(): string
    {
        $data = random_bytes(16);
        
        // Version 4 UUID
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // Version 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // Variant RFC 4122
        
        return sprintf(
            '%08s-%04s-%04s-%04s-%12s',
            bin2hex(substr($data, 0, 4)),
            bin2hex(substr($data, 4, 2)),
            bin2hex(substr($data, 6, 2)),
            bin2hex(substr($data, 8, 2)),
            bin2hex(substr($data, 10, 6))
        );
    }

    /**
     * Valide qu'une chaîne est un UUID v4 valide
     * 
     * @param string $uuid La chaîne à valider
     * @return bool True si valide
     */
    private function isValidUuid(string $uuid): bool
    {
        return (bool) preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $uuid
        );
    }
}
