# Système de Vérification d'Appareil avec OTP par SMS

## Vue d'ensemble

Ce système ajoute une couche de sécurité supplémentaire lors de la connexion en vérifiant si l'appareil utilisé est reconnu. Si l'appareil est inconnu, un code OTP est envoyé par SMS pour valider l'appareil.

## Architecture

### Entités

1. **UserDevice** (`src/Entity/UserDevice.php`)
   - Stocke les appareils de confiance pour chaque utilisateur
   - Contient : device_id (UUID), dates de création/utilisation, expiration, IP, User-Agent
   - Supporte la confiance temporaire (3h) ou permanente

2. **LoginChallenge** (`src/Entity/LoginChallenge.php`)
   - Stocke les défis OTP en cours
   - Contient uniquement le **hash** du code OTP (jamais le code en clair)
   - Expire après 10 minutes
   - Maximum 5 tentatives

### Services

1. **DeviceIdManager** (`src/Service/DeviceIdManager.php`)
   - Gère le cookie `device_id` (UUID v4)
   - Cookie sécurisé : HttpOnly, Secure, SameSite=Lax
   - Génère un nouvel UUID si absent

2. **OtpGenerator** (`src/Service/OtpGenerator.php`)
   - Génère des codes OTP à 6 chiffres
   - Hash les codes avec `password_hash()` (BCRYPT)
   - Vérifie les codes avec `password_verify()`

3. **SmsSenderInterface** (`src/Service/SmsSenderInterface.php`)
   - Interface pour l'envoi de SMS
   - Implémentation mock : `MockSmsSender` (log les SMS en développement)

### Listeners/Subscribers

1. **DeviceCheckListener** (`src/EventListener/DeviceCheckListener.php`)
   - Intercepte `LoginSuccessEvent`
   - Vérifie si l'appareil est reconnu
   - Si non reconnu : génère OTP, envoie SMS, redirige vers validation

2. **DeviceCheckSubscriber** (`src/EventSubscriber/DeviceCheckSubscriber.php`)
   - Vérifie l'appareil sur chaque requête
   - Redirige vers `/security/device-check` si appareil non validé
   - Exempte certaines routes (login, logout, device-check)

### Contrôleur

**DeviceCheckController** (`src/Controller/DeviceCheckController.php`)
- Route : `/security/device-check`
- Affiche le formulaire de validation OTP
- Valide le code et enregistre l'appareil
- Gère les tentatives (max 5)

## Flux de connexion

1. **Utilisateur se connecte** (email + mot de passe)
2. **DeviceCheckListener** intercepte le succès :
   - Récupère/crée le `device_id` depuis le cookie
   - Vérifie si l'appareil est dans `UserDevice` et valide
   - Si oui → connexion normale
   - Si non → génère OTP, envoie SMS, redirige vers `/security/device-check`
3. **DeviceCheckSubscriber** vérifie sur chaque requête :
   - Si appareil non validé → redirige vers `/security/device-check`
4. **Utilisateur saisit le code OTP** sur `/security/device-check`
5. **Validation** :
   - Code correct → enregistre dans `UserDevice`, finalise la connexion
   - Code incorrect → incrémente les tentatives (max 5)

## Configuration

### Services (`config/services.yaml`)

```yaml
App\Service\SmsSenderInterface:
    class: App\Service\MockSmsSender
```

En production, remplacer `MockSmsSender` par une implémentation réelle (Twilio, Nexmo, etc.).

### Sécurité (`config/packages/security.yaml`)

La route `/security/device-check` est accessible aux utilisateurs connectés (`ROLE_USER`).

## Migration

Exécuter la migration pour créer les tables :

```bash
php bin/console doctrine:migrations:migrate
```

## Utilisation

### Ajouter un numéro de téléphone à un utilisateur

Le champ `phoneNumber` a été ajouté à l'entité `User`. Il est disponible dans le formulaire de création/édition d'utilisateur.

### En production

1. **Implémenter un vrai service SMS** :
   - Créer une classe implémentant `SmsSenderInterface`
   - Utiliser Twilio, Nexmo, ou un autre fournisseur
   - Mettre à jour `config/services.yaml` pour utiliser cette implémentation

2. **Configurer les cookies sécurisés** :
   - S'assurer que l'application est en HTTPS
   - Le cookie `device_id` est déjà configuré avec `Secure: true`

3. **Nettoyer les challenges expirés** :
   - Créer une commande cron pour exécuter :
   ```php
   $loginChallengeRepository->removeExpiredChallenges();
   ```

## Sécurité

- ✅ Codes OTP jamais stockés en clair (uniquement hash)
- ✅ Cookie device_id sécurisé (HttpOnly, Secure, SameSite)
- ✅ OTP expire après 10 minutes
- ✅ Maximum 5 tentatives par challenge
- ✅ Appareils expirés automatiquement supprimés
- ✅ Pas de fingerprint matériel (respect de la vie privée)

## Tests

En développement, les codes OTP sont loggés dans les logs PHP :
```
[DEV] Code OTP pour user@example.com (device: xxx): 123456
```

Vérifier les logs pour obtenir le code lors des tests.
