# Synchronisation des contacts Microsoft 365 (Graph API)

Implémentation Symfony 6/7 pour synchroniser manuellement les contacts partagés Microsoft 365 dans la BDD (UPSERT via Microsoft Graph).

---

## Prérequis et TODOs

### 1. Application Azure / Microsoft Entra

- [ ] Créer une application dans [Azure Portal](https://portal.azure.com) → **Microsoft Entra ID** → **Inscriptions d’applications** → **Nouvelle inscription**.
- [ ] **Type de prise en charge** : Comptes dans cet annuaire organisationnel uniquement (single tenant) ou multitenant selon besoin.
- [ ] Noter l’**ID d’application (client)** et l’**ID de l’annuaire (tenant)**.

### 2. Secret client

- [ ] **Certificats et secrets** → **Nouveau secret client** → noter la **Valeur** (elle ne sera plus affichée ensuite).

### 3. URI de redirection

- [ ] **Authentification** → **URI de redirection** → **Ajouter un URI** :
  - En local : `http://localhost:8000/m365/callback` (ou le domaine utilisé).
  - En prod : `https://votredomaine.com/m365/callback`.

### 4. Permissions Microsoft Graph (déléguées)

- [ ] **Autorisations d’API** → **Ajouter une autorisation** → **Microsoft Graph** → **Autorisations déléguées**.
- [ ] Ajouter : **Contacts.Read.Shared** (lire les contacts partagés).
- [ ] Optionnel : **offline_access** (pour `refresh_token`) — souvent inclus avec “Consentement admin”.
- [ ] **Accorder le consentement d’administrateur** pour le tenant.

### 5. Variables d’environnement

Créer ou compléter le fichier `.env` (ou `.env.local`) :

```env
M365_TENANT_ID=votre-tenant-id
M365_CLIENT_ID=votre-client-id
M365_CLIENT_SECRET=votre-client-secret
M365_REDIRECT_URI=http://localhost:8000/m365/callback

# Optionnel : ID du dossier contacts à synchroniser (sinon premier dossier disponible)
# M365_SHARED_FOLDER_ID=
```

---

## Endpoints Microsoft Graph utilisés

| Méthode | Endpoint | Description |
|--------|----------|-------------|
| GET | `/me/contactFolders` | Liste des dossiers de contacts de l’utilisateur connecté (dont dossiers partagés auxquels il a accès). |
| GET | `/me/contactFolders/{folderId}/contacts?$top=100` | Liste des contacts d’un dossier. Pagination via `@odata.nextLink`. |
| GET | (idem + `$filter=lastModifiedDateTime ge {ISO8601}`) | Sync incrémentale : uniquement les contacts modifiés depuis une date. |

### Découverte du dossier partagé

- **Sans `M365_SHARED_FOLDER_ID`** : le code appelle `GET /me/contactFolders` et utilise le **premier** dossier retourné (souvent le dossier “Contacts” par défaut).
- **Dossiers partagés** : avec la permission **Contacts.Read.Shared**, les dossiers partagés par d’autres utilisateurs apparaissent dans `/me/contactFolders`. Vous pouvez :
  - soit figer l’ID du dossier souhaité dans `M365_SHARED_FOLDER_ID`,
  - soit le sauvegarder dans `SyncState.meta['sharedContactFolderId']` après une première synchro (le service le réutilise ensuite).

Référence : [Graph - contactFolder](https://learn.microsoft.com/en-us/graph/api/resources/contactfolder), [list contactFolders](https://learn.microsoft.com/en-us/graph/api/user-list-contactfolders), [list contacts](https://learn.microsoft.com/en-us/graph/api/contactfolder-list-contacts).

---

## Routes

| Route | Méthode | Description |
|-------|--------|-------------|
| `/m365/login` | GET | Redirection vers Microsoft (OAuth2). |
| `/m365/callback` | GET | Récupère `code`, échange contre `access_token` + `refresh_token`, stocke en BDD (`oauth_token`), redirige vers `/admin/contacts`. |
| `/admin/contacts` | GET | Page admin : état de connexion M365, dernière synchro, nombre de contacts, bouton « Synchroniser ». |
| `/admin/contacts/sync` | POST | Déclenche la synchro (CSRF requis). |

---

## Modèle de données

- **oauth_token** : `user_id`, `provider` ('m365'), `access_token`, `refresh_token`, `expires_at`, `created_at`, `updated_at`. Unicité `(user_id, provider)`.
- **sync_state** : `user_id`, `provider` ('m365_contacts_shared'), `last_sync_at`, `meta` (JSON, ex. `lastSyncContactsCount`, `sharedContactFolderId`). Unicité `(user_id, provider)`.
- **contact** : `user_id`, `source` ('m365'), `source_id` (ID Graph), `display_name`, `given_name`, `surname`, `email1`, `email2`, `phone_mobile`, `phone_business`, `company_name`, `job_title`, `address` (JSON), `last_modified_at`, `created_at`, `updated_at`. Unicité `(user_id, source, source_id)`.

---

## Logique de synchronisation

1. Récupération d’un `access_token` valide (refresh automatique si expiré ou proche de l’expiration).
2. Résolution du dossier de contacts : `M365_SHARED_FOLDER_ID` ou `SyncState.meta['sharedContactFolderId']` ou premier dossier de `GET /me/contactFolders`.
3. Appel `GET /me/contactFolders/{folderId}/contacts` avec option `$filter=lastModifiedDateTime ge {last_sync_at}` si disponible (sync incrémentale).
4. Pour chaque contact Graph : UPSERT en BDD sur `(user_id, source, source_id)` ; mise à jour de `last_modified_at` depuis `lastModifiedDateTime`.
5. Mise à jour de `SyncState` : `last_sync_at`, `meta.lastSyncContactsCount`, `meta.sharedContactFolderId`.

---

## Commande console

```bash
php bin/console app:m365:sync-contacts-shared
```

Synchronise tous les utilisateurs ayant un token M365. Option :

```bash
php bin/console app:m365:sync-contacts-shared --user=email@example.com
```

Utile pour un cron après que les utilisateurs se soient connectés une première fois via le bouton « Synchroniser » (ou `/m365/login`).

---

## Sécurité

- Les routes `/m365/*` et `/admin/contacts` sont protégées par `ROLE_USER` (contrôleurs). Pour restreindre à l’admin, utiliser `#[IsGranted('ROLE_ADMIN')]` sur `AdminContactsController` et `OAuthController` si besoin.
- Le CSRF est requis sur le formulaire POST « Synchroniser ».

---

## Fichiers créés (arborescence)

```
config/packages/m365.yaml
config/services.yaml                    # bindings M365
migrations/Version20260129120000.php    # oauth_token, sync_state, contact
docs/M365_CONTACTS_SYNC.md             # ce fichier
src/
  Command/M365SyncContactsCommand.php
  Controller/M365/
    OAuthController.php
    AdminContactsController.php
  Entity/
    OAuthToken.php
    SyncState.php
    Contact.php
  Repository/
    OAuthTokenRepository.php
    SyncStateRepository.php
    ContactRepository.php
  Service/M365/
    MicrosoftOAuth2Service.php
    MicrosoftGraphClient.php
    M365ContactSyncService.php
templates/admin/contacts/index.html.twig
```

---

## Appliquer la migration

```bash
php bin/console doctrine:migrations:migrate
```

Puis configurer les variables d’environnement et tester en allant sur `/admin/contacts`, en cliquant sur « Connecter Microsoft 365 », puis « Synchroniser ».
