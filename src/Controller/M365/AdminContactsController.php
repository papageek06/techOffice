<?php

declare(strict_types=1);

namespace App\Controller\M365;

use App\Entity\User;
use App\Repository\ContactRepository;
use App\Repository\OAuthTokenRepository;
use App\Repository\SyncStateRepository;
use App\Service\M365\M365ContactSyncService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/contacts')]
#[IsGranted('ROLE_USER')]
final class AdminContactsController extends AbstractController
{
    public function __construct(
        private readonly OAuthTokenRepository $oauthTokenRepository,
        private readonly SyncStateRepository $syncStateRepository,
        private readonly ContactRepository $contactRepository,
        private readonly M365ContactSyncService $syncService
    ) {
    }

    #[Route('', name: 'app_admin_contacts_index', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $token = $this->oauthTokenRepository->findForUserAndProvider($user, 'm365');
        $syncState = $this->syncStateRepository->findForUserAndProvider($user, SyncStateRepository::PROVIDER_M365_CONTACTS_SHARED);
        $contacts = $this->contactRepository->findByUserAndSource($user, ContactRepository::SOURCE_M365);
        $contactsCount = $this->contactRepository->countByUserAndSource($user, ContactRepository::SOURCE_M365);

        return $this->render('admin/contacts/index.html.twig', [
            'm365_connected' => $token !== null,
            'last_sync_at' => $syncState?->getLastSyncAt(),
            'sync_meta' => $syncState?->getMeta(),
            'contacts_count' => $contactsCount,
            'contacts' => $contacts,
        ]);
    }

    #[Route('/sync', name: 'app_admin_contacts_sync', methods: ['POST'])]
    public function sync(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        if (!$this->isCsrfTokenValid('admin_contacts_sync', (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_admin_contacts_index');
        }

        try {
            $result = $this->syncService->sync($user);
            $this->addFlash('success', sprintf(
                'Synchronisation terminée: %d contact(s) importé(s) ou mis à jour.',
                $result['contacts_upserted']
            ));
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Erreur lors de la synchronisation: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_admin_contacts_index');
    }
}
