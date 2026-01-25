<?php

namespace App\EventListener;

use App\Entity\LoginChallenge;
use App\Entity\User;
use App\Entity\UserDevice;
use App\Repository\LoginChallengeRepository;
use App\Repository\UserDeviceRepository;
use App\Service\DeviceIdManager;
use App\Service\EmailSenderInterface;
use App\Service\OtpGenerator;
use App\Service\SmsSenderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

/**
 * Listener qui intercepte les connexions réussies pour vérifier l'appareil
 * Si l'appareil n'est pas reconnu, génère un OTP et redirige vers la validation
 */
#[AsEventListener(event: LoginSuccessEvent::class, method: 'onLoginSuccess')]
class DeviceCheckListener
{
    public function __construct(
        private DeviceIdManager $deviceIdManager,
        private UserDeviceRepository $userDeviceRepository,
        private LoginChallengeRepository $loginChallengeRepository,
        private OtpGenerator $otpGenerator,
        private SmsSenderInterface $smsSender,
        private EmailSenderInterface $emailSender,
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    /**
     * Vérifie l'appareil après une connexion réussie
     */
    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        
        if (!$user instanceof User) {
            return; // Pas un User de notre application
        }

        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }

        // Récupérer ou créer le device_id
        $deviceId = $this->deviceIdManager->getOrCreateDeviceId($request);

        // Vérifier si l'appareil est déjà de confiance
        $trustedDevice = $this->userDeviceRepository->findTrustedDevice($user, $deviceId);

        if ($trustedDevice && $trustedDevice->isValid()) {
            // Appareil reconnu et valide : mettre à jour la date de dernière utilisation
            $trustedDevice->markAsUsed();
            $this->entityManager->flush();
            
            // Ajouter le cookie si nécessaire
            $response = $event->getResponse();
            if ($response) {
                $this->deviceIdManager->setDeviceCookie($response, $deviceId);
            }
            
            return; // Accès autorisé, pas besoin d'OTP
        }

        // Appareil non reconnu : créer un challenge OTP
        $this->createOtpChallenge($user, $deviceId, $request);

        // Rediriger vers la page de validation et définir le cookie device_id
        $redirectResponse = new RedirectResponse($this->urlGenerator->generate('app_device_check'));
        $this->deviceIdManager->setDeviceCookie($redirectResponse, $deviceId);
        $event->setResponse($redirectResponse);
    }

    /**
     * Crée un challenge OTP pour valider l'appareil
     */
    private function createOtpChallenge(User $user, string $deviceId, $request): void
    {
        // Supprimer les anciens challenges pour cet appareil
        $this->loginChallengeRepository->removeChallengesForDevice($user, $deviceId);

        // Générer un nouveau code OTP
        $otpCode = $this->otpGenerator->generateOtp();
        $otpHash = $this->otpGenerator->hashOtp($otpCode);

        // Créer le challenge
        $challenge = new LoginChallenge();
        $challenge->setUser($user);
        $challenge->setDeviceId($deviceId);
        $challenge->setOtpHash($otpHash);

        $this->entityManager->persist($challenge);
        $this->entityManager->flush();

        // Envoyer le code OTP par SMS et/ou email
        $phoneNumber = $this->getUserPhoneNumber($user);
        $email = $user->getEmail();
        $userName = $user->getNom() ?? $user->getEmail();
        
        // Envoyer par SMS si numéro disponible
        if ($phoneNumber) {
            $this->smsSender->sendOtp($phoneNumber, $otpCode);
        }
        
        // Toujours envoyer par email en complément (ou en remplacement si pas de SMS)
        if ($email) {
            $this->emailSender->sendOtp($email, $otpCode, $userName);
        }
        
        // En développement, logger aussi le code pour faciliter les tests
        if (!$phoneNumber && !$email) {
            error_log(sprintf(
                '[DEV] Code OTP pour utilisateur ID %d (device: %s): %s',
                $user->getId(),
                $deviceId,
                $otpCode
            ));
        }
    }

    /**
     * Récupère le numéro de téléphone de l'utilisateur
     */
    private function getUserPhoneNumber(User $user): ?string
    {
        return $user->getPhoneNumber();
    }
}
