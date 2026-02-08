# Déploiement TechOffice (production)

Le projet utilise **MySQL/MariaDB uniquement**. SQLite n’est pas supporté en production ni pour les tests.

## 1. Prérequis

- **PHP** 8.2+ (extensions : pdo_mysql, json, mbstring, openssl, intl, zip, xml, ctype)
- **MySQL** 8.0+ ou **MariaDB** 10.11+
- **Composer** 2
- **Node/npm** si vous compilez les assets (Webpack Encore)

## 2. Variables d’environnement (production)

À définir sur le serveur ou dans `.env.local` (non versionné) :

| Variable | Obligatoire | Description |
|----------|-------------|-------------|
| `APP_ENV` | Oui | `prod` |
| `APP_DEBUG` | Oui | `0` (désactivé en prod) |
| `APP_SECRET` | Oui | Clé secrète (ex. générée avec `openssl rand -hex 32`) |
| `DATABASE_URL` | Oui | `mysql://user:password@host:3306/techoffice?serverVersion=8.0.32&charset=utf8mb4` (adapter `serverVersion` si MariaDB) |
| `PRINTAUDIT_WEBHOOK_TOKEN` | Si webhooks | Token pour `POST /api/inbound/printaudit/webhook` |
| `MESSENGER_TRANSPORT_DSN` | Oui | Ex. `doctrine://default?auto_setup=0` ou Redis/AMQP pour async |
| `MAILER_DSN` | Selon besoin | DSN du mailer (prod) |
| `DEFAULT_URI` | Recommandé | URL publique du site (ex. `https://techoffice.example.com`) |

**Exemple `.env.local` (à ne pas committer) :**

```bash
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=votre_secret_genere
DATABASE_URL="mysql://user:pass@db:3306/techoffice?serverVersion=8.0.32&charset=utf8mb4"
PRINTAUDIT_WEBHOOK_TOKEN=secret_webhook
DEFAULT_URI=https://votredomaine.com
```

## 3. Déploiement (checklist)

1. **Cloner / récupérer le code** (sans `vendor/`, sans `.env.local`).

2. **Installer les dépendances PHP (prod, sans dev) :**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. **Compiler les assets (si besoin) :**
   ```bash
   npm ci
   npm run build
   ```

4. **Configurer l’environnement** : définir les variables ci‑dessus (`.env.local` ou env du serveur).

5. **Base de données :**
   - Créer la base MySQL et l’utilisateur.
   - Exécuter les migrations :
   ```bash
   php bin/console doctrine:migrations:migrate --no-interaction
   ```

6. **Vider et réchauffer le cache :**
   ```bash
   php bin/console cache:clear --env=prod
   ```

7. **Permissions :** répertoires `var/` et `var/cache/`, `var/log/` en écriture pour l’utilisateur du serveur web.

8. **Worker Messenger (si vous utilisez les tâches async, ex. webhooks inbound) :**
   ```bash
   php bin/console messenger:consume async -v
   ```
   À lancer en arrière‑plan (systemd, supervisord, etc.).

## 4. Sécurité

- Ne jamais committer `.env.local` ni de secrets.
- En prod : `APP_DEBUG=0` obligatoire (évite les fuites d’infos).
- `APP_SECRET` doit être fort et unique par environnement.
- Protéger `/admin` (contrôle d’accès `ROLE_ADMIN` déjà en place).
- Webhook PrintAudit : définir `PRINTAUDIT_WEBHOOK_TOKEN` en prod pour accepter les alertes.

## 5. Vérifications post-déploiement

- Accès à la page d’accueil et au login.
- Connexion à la base (liste des sites, imprimantes, etc.).
- Si utilisé : envoi d’un POST de test vers `/api/inbound/printaudit/webhook` avec le token configuré.
- Vérifier les logs dans `var/log/prod.log` (ou stderr selon la config Monolog).

## 6. Serveur OVH (PHP 8.2)

Le projet est compatible **PHP 8.2** (ex. hébergement OVH en php/8.2). Le `composer.lock` a été généré pour cette version :

- **doctrine/doctrine-bundle** : ^2.18 (compatible PHP 8.1+)
- **doctrine/doctrine-migrations-bundle** : ^3.3 (compatible PHP 7.2+)
- **symfony/cache** : 7.3.* (évite le conflit avec `ext-redis` &lt; 6.1 sur OVH)
- **phpunit** (dev) : ^11.0 (compatible PHP 8.2)

Sur le serveur, exécuter :

```bash
composer install --no-dev --optimize-autoloader
```

Ne pas lancer `composer update` en production. Si OVH propose PHP 8.3 ou 8.4, vous pourrez à terme remonter les contraintes (doctrine-bundle 3.x, etc.) en régénérant le lock en local avec cette version.

## 7. Tests (environnement test)

Les tests utilisent **MySQL** avec un suffixe de base (ex. `techoffice_test`). Dans `.env.test`, `DATABASE_URL` doit pointer vers une base MySQL ; Doctrine ajoute le suffixe via `dbname_suffix`. Créer une base dédiée aux tests ou un utilisateur avec droits de création de base si besoin.

```bash
php bin/console doctrine:database:create --env=test  # si la base n’existe pas
php bin/console doctrine:migrations:migrate --env=test --no-interaction
php bin/phpunit
```
