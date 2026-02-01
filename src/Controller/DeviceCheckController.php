<?php

namespace App\Controller;

use App\Entity\LoginChallenge;
use App\Entity\UserDevice;
use App\Form\DeviceCheckType;
use App\Repository\LoginChallengeRepository;
use App\Repository\UserDeviceRepository;
use App\Service\DeviceIdManager;
use App\Service\OtpGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Contrôleur pour la validation du code OTP et l'enregistrement de l'appareil
 */
#[Route('/security/device-check', name: 'app_device_check')]
#[IsGranted('ROLE_USER')]
class DeviceCheckController extends AbstractController
{
    public function __construct(
        private DeviceIdManager $deviceIdManager,
        private LoginChallengeRepository $loginChallengeRepository,
        private UserDeviceRepository $userDeviceRepository,
        private OtpGenerator $otpGenerator,
        private EntityManagerInterface $entityManager,
        #[Autowire('%kernel.environment%')]
        private string $kernelEnvironment
    ) {
    }

    /**
     * Affiche le formulaire de validation OTP
     */
    #[Route('', name: '', methods: ['GET', 'POST'])]
    public function check(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Récupérer le device_id
        $deviceId = $this->deviceIdManager->getOrCreateDeviceId($request);

        // Vérifier si l'appareil est déjà de confiance
        $trustedDevice = $this->userDeviceRepository->findTrustedDevice($user, $deviceId);
        if ($trustedDevice && $trustedDevice->isValid()) {
            // Appareil déjà validé, rediriger vers la page d'accueil
            return $this->redirectToRoute('app_home');
        }

        // Récupérer le challenge actif
        $challenge = $this->loginChallengeRepository->findActiveChallenge($user, $deviceId);
        
        if (!$challenge) {
            // Pas de challenge actif, rediriger vers le login
            $this->addFlash('error', 'Aucun code de vérification en attente. Veuillez vous reconnecter.');
            return $this->redirectToRoute('app_login');
        }

        $devOtpDisplay = null;
        if ($this->kernelEnvironment === 'dev' && $request->getSession()->has('dev_otp_display')) {
            $devOtpDisplay = $request->getSession()->get('dev_otp_display');
        }

        $form = $this->createForm(DeviceCheckType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $otp = $form->get('otp')->getData();
            $trustDuration = $form->get('trustDuration')->getData();

            // Vérifier le code OTP
            if (!$this->otpGenerator->verifyOtp($otp, $challenge->getOtpHash())) {
                // Code incorrect
                $challenge->incrementAttempts();
                $this->entityManager->flush();

                $remainingAttempts = $challenge->getRemainingAttempts();
                
                if ($remainingAttempts > 0) {
                    $this->addFlash('error', sprintf(
                        'Code incorrect. Il vous reste %d tentative(s).',
                        $remainingAttempts
                    ));
                } else {
                    $this->addFlash('error', 'Trop de tentatives incorrectes. Veuillez vous reconnecter.');
                    // Supprimer le challenge
                    $this->entityManager->remove($challenge);
                    $this->entityManager->flush();
                    
                    return $this->redirectToRoute('app_logout');
                }

                return $this->render('security/device_check.html.twig', [
                    'form' => $form,
                    'challenge' => $challenge,
                    'dev_otp_display' => $devOtpDisplay,
                ]);
            }

            // Code OTP valide : enregistrer l'appareil comme de confiance
            $this->registerTrustedDevice($user, $deviceId, $trustDuration, $request);

            // Supprimer le challenge utilisé
            $this->entityManager->remove($challenge);
            $this->entityManager->flush();

            // En dev : retirer l'OTP affiché en session
            if ($this->kernelEnvironment === 'dev') {
                $request->getSession()->remove('dev_otp_display');
            }

            $this->addFlash('success', 'Appareil enregistré avec succès. Vous êtes maintenant connecté.');

            // Rediriger vers la page d'accueil
            $response = $this->redirectToRoute('app_home');
            $this->deviceIdManager->setDeviceCookie($response, $deviceId);
            
            return $response;
        }

        return $this->render('security/device_check.html.twig', [
            'form' => $form,
            'challenge' => $challenge,
            'dev_otp_display' => $devOtpDisplay,
        ]);
    }

    /**
     * Enregistre l'appareil comme de confiance
     */
    private function registerTrustedDevice($user, string $deviceId, string $trustDuration, Request $request): void
    {
        // Supprimer les anciens appareils expirés pour cet utilisateur
        $this->userDeviceRepository->removeExpiredDevices($user);

        // Créer ou mettre à jour l'appareil
        $device = $this->userDeviceRepository->findTrustedDevice($user, $deviceId);
        
        if (!$device) {
            $device = new UserDevice();
            $device->setUser($user);
            $device->setDeviceId($deviceId);
            $device->setIpAddress($request->getClientIp());
            $device->setUserAgent($request->headers->get('User-Agent'));
            $this->entityManager->persist($device);
        }

        // Définir la date d'expiration selon le choix
        if ($trustDuration === 'permanent') {
            $device->setExpiresAt(null); // Confiance permanente
        } else {
            // 3 heures
            $device->setExpiresAt((new \DateTimeImmutable())->modify('+3 hours'));
        }

        $device->markAsUsed();
        $this->entityManager->flush();
    }
}
