<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Repository\LoginChallengeRepository;
use App\Repository\UserDeviceRepository;
use App\Service\DeviceIdManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Subscriber qui vérifie si l'appareil est de confiance sur chaque requête
 * Redirige vers la page de validation si l'appareil n'est pas validé
 */
class DeviceCheckSubscriber implements EventSubscriberInterface
{
    /**
     * Routes exemptées de la vérification d'appareil
     */
    private const EXEMPTED_ROUTES = [
        'app_login',
        'app_logout',
        'app_device_check',
    ];

    public function __construct(
        private DeviceIdManager $deviceIdManager,
        private UserDeviceRepository $userDeviceRepository,
        private LoginChallengeRepository $loginChallengeRepository,
        private UrlGeneratorInterface $urlGenerator,
        private Security $security
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }

    /**
     * Vérifie l'appareil sur chaque requête
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        // Ignorer les routes exemptées
        if (in_array($route, self::EXEMPTED_ROUTES, true)) {
            return;
        }

        // Vérifier si l'utilisateur est connecté
        $token = $this->security->getToken();
        if (!$token) {
            return; // Pas de token, laisser passer
        }
        
        $user = $token->getUser();
        if (!$user instanceof User) {
            return; // Pas connecté, laisser passer
        }

        // Récupérer le device_id
        $deviceId = $this->deviceIdManager->getOrCreateDeviceId($request);

        // Vérifier si l'appareil est de confiance
        $trustedDevice = $this->userDeviceRepository->findTrustedDevice($user, $deviceId);
        
        if ($trustedDevice && $trustedDevice->isValid()) {
            // Appareil de confiance, autoriser l'accès
            return;
        }

        // Vérifier s'il y a un challenge actif
        $activeChallenge = $this->loginChallengeRepository->findActiveChallenge($user, $deviceId);
        
        if (!$activeChallenge) {
            // Pas de challenge actif : créer un nouveau challenge
            // Cela devrait normalement être fait par DeviceCheckListener après login
            // Mais si l'utilisateur accède directement, on redirige vers login
            $event->setResponse(
                new RedirectResponse($this->urlGenerator->generate('app_logout'))
            );
            return;
        }

        // Challenge actif mais pas sur la page de validation : rediriger
        if ($route !== 'app_device_check') {
            $event->setResponse(
                new RedirectResponse($this->urlGenerator->generate('app_device_check'))
            );
        }
    }
}
