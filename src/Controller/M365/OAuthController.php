<?php

declare(strict_types=1);

namespace App\Controller\M365;

use App\Service\M365\MicrosoftOAuth2Service;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/m365')]
#[IsGranted('ROLE_USER')]
final class OAuthController extends AbstractController
{
    public function __construct(
        private readonly MicrosoftOAuth2Service $oauth2Service
    ) {
    }

    #[Route('/login', name: 'app_m365_login', methods: ['GET'])]
    public function login(Request $request): Response
    {
        $state = bin2hex(random_bytes(16));
        $request->getSession()->set('m365_oauth_state', $state);
        $url = $this->oauth2Service->getAuthorizationUrl($state);
        return $this->redirect($url);
    }

    #[Route('/callback', name: 'app_m365_callback', methods: ['GET'])]
    public function callback(Request $request): Response
    {
        $session = $request->getSession();
        $state = $session->get('m365_oauth_state');
        if ($state === null || $request->query->get('state') !== $state) {
            $this->addFlash('error', 'OAuth state invalid. Please try again.');
            return $this->redirectToRoute('app_admin_contacts_index');
        }
        $session->remove('m365_oauth_state');

        $code = $request->query->get('code');
        if (!\is_string($code) || $code === '') {
            $error = $request->query->get('error_description', $request->query->get('error', 'No code received.'));
            $this->addFlash('error', 'Microsoft 365: ' . $error);
            return $this->redirectToRoute('app_admin_contacts_index');
        }

        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            return $this->redirectToRoute('app_login');
        }

        try {
            $this->oauth2Service->getTokenFromCode($user, $code);
            $this->addFlash('success', 'Microsoft 365 connecté. Vous pouvez synchroniser les contacts.');
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Erreur lors de la connexion: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_admin_contacts_index');
    }
}
