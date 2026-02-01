<?php

declare(strict_types=1);

namespace App\Service\M365;

use App\Entity\Contact;
use App\Entity\User;
use App\Repository\ContactRepository;
use App\Repository\SyncStateRepository;
use Psr\Log\LoggerInterface;

/**
 * Syncs M365 shared/default contact folder into local Contact table.
 * UPSERT by source='m365' + sourceId (Graph contact id).
 */
class M365ContactSyncService
{
    public function __construct(
        private readonly MicrosoftOAuth2Service $oauth2Service,
        private readonly MicrosoftGraphClient $graphClient,
        private readonly ContactRepository $contactRepository,
        private readonly SyncStateRepository $syncStateRepository,
        private readonly ?string $sharedFolderId = null,
        private readonly ?LoggerInterface $logger = null
    ) {
    }

    public function sync(User $user): array
    {
        $accessToken = $this->oauth2Service->getValidAccessToken($user);
        if ($accessToken === null) {
            throw new \RuntimeException('User has not connected Microsoft 365. Please connect first via /m365/login.');
        }

        $syncState = $this->syncStateRepository->getOrCreateForUserAndProvider(
            $user,
            SyncStateRepository::PROVIDER_M365_CONTACTS_SHARED
        );

        $folderId = $this->resolveFolderId($accessToken, $syncState->getMeta());
        $modifiedSince = $syncState->getLastSyncAt();

        $upserted = 0;
        $now = new \DateTimeImmutable();

        foreach ($this->graphClient->getContacts($accessToken, $folderId, $modifiedSince) as $graphContact) {
            $this->upsertContactFromGraph($user, $graphContact);
            $upserted++;
        }

        $syncState->setLastSyncAt($now);
        $meta = $syncState->getMeta() ?? [];
        $meta['lastSyncContactsCount'] = $upserted;
        $meta['sharedContactFolderId'] = $folderId;
        $syncState->setMeta($meta);
        $this->syncStateRepository->save($syncState, true);

        $this->logger?->info('M365 contact sync completed', [
            'user_id' => $user->getId(),
            'upserted' => $upserted,
        ]);

        return [
            'last_sync_at' => $now,
            'contacts_upserted' => $upserted,
            'folder_id' => $folderId,
        ];
    }

    private function resolveFolderId(string $accessToken, ?array $meta): string
    {
        if ($this->sharedFolderId !== null && $this->sharedFolderId !== '') {
            return $this->sharedFolderId;
        }
        if (isset($meta['sharedContactFolderId']) && \is_string($meta['sharedContactFolderId'])) {
            return $meta['sharedContactFolderId'];
        }
        $folders = $this->graphClient->getContactFolders($accessToken);
        if (\count($folders) === 0) {
            throw new \RuntimeException('No contact folder found. Create a contact folder in Outlook or use M365_SHARED_FOLDER_ID.');
        }
        $first = $folders[0];
        return $first['id'];
    }

    private function upsertContactFromGraph(User $user, array $graph): void
    {
        $sourceId = (string) ($graph['id'] ?? '');
        if ($sourceId === '') {
            return;
        }

        $contact = $this->contactRepository->findForUserBySourceAndSourceId($user, ContactRepository::SOURCE_M365, $sourceId);
        if ($contact === null) {
            $contact = new Contact();
            $contact->setUser($user);
            $contact->setSource(ContactRepository::SOURCE_M365);
            $contact->setSourceId($sourceId);
        }

        $contact->setDisplayName($graph['displayName'] ?? null);
        $contact->setGivenName($graph['givenName'] ?? null);
        $contact->setSurname($graph['surname'] ?? null);
        $contact->setCompanyName($graph['companyName'] ?? null);
        $contact->setJobTitle($graph['jobTitle'] ?? null);

        $emails = $graph['emailAddresses'] ?? [];
        $contact->setEmail1(isset($emails[0]) ? ($emails[0]['address'] ?? null) : null);
        $contact->setEmail2(isset($emails[1]) ? ($emails[1]['address'] ?? null) : null);

        $phones = $graph['businessPhones'] ?? [];
        $contact->setPhoneBusiness(isset($phones[0]) ? $phones[0] : null);
        $mobiles = $graph['mobilePhone'] ?? null;
        $contact->setPhoneMobile(\is_string($mobiles) ? $mobiles : null);

        $addr = $graph['homeAddress'] ?? $graph['businessAddress'] ?? [];
        $address = [];
        if (isset($addr['street'])) {
            $address['street'] = $addr['street'];
        }
        if (isset($addr['city'])) {
            $address['city'] = $addr['city'];
        }
        if (isset($addr['postalCode'])) {
            $address['postalCode'] = $addr['postalCode'];
        }
        if (isset($addr['countryOrRegion'])) {
            $address['country'] = $addr['countryOrRegion'];
        }
        $contact->setAddress(\count($address) > 0 ? $address : null);

        $lastMod = $graph['lastModifiedDateTime'] ?? null;
        if (\is_string($lastMod)) {
            try {
                $contact->setLastModifiedAt(new \DateTimeImmutable($lastMod));
            } catch (\Exception) {
            }
        }

        $this->contactRepository->save($contact, true);
    }
}
