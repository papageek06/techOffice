# Déploiement TechOffice (production)

Le projet utilise **MySQL/MariaDB uniquement**.

## 1. Prérequis

- **PHP** 8.2+ (extensions : pdo_mysql, json, mbstring, openssl, intl, zip, xml, ctype)
- **MySQL** 8.0+ ou **MariaDB** 10.11+
- **Composer** 2
- **Node/npm** si vous compilez les assets (Webpack Encore)

## 2. Variables d’environnement (production)



**Exemple `.env.local` (à ne pas committer) :**

```bash
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=votre_secret_genere
DATABASE_URL="mysql://user:pass@db:3306/techoffice?serverVersion=8.0.32&charset=utf8mb4"
SUPER_ADMIN_EMAIL=admin@votredomaine.com
SUPER_ADMIN_PASSWORD=votre_mot_de_passe_admin
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

6. **Super admin (recommandé en prod) :** dans `.env.local`, définir `SUPER_ADMIN_EMAIL` et `SUPER_ADMIN_PASSWORD`, puis exécuter une fois :
   ```bash
   php bin/console app:user:create-admin
   ```
   Le compte sera créé ou mis à jour avec les identifiants définis dans l’env.

7. **Vider et réchauffer le cache :**
   ```bash
   php bin/console cache:clear --env=prod
   ```

8. **Racine web (IMPORTANT) :** le **document root** du site doit pointer sur le dossier **`public/`** du projet (et non sur la racine du dépôt). Sinon toutes les URLs (/login, /admin, etc.) donnent « URL not found ».  
   - Ex. : si le code est dans `~/techOffice`, la racine web doit être `~/techOffice/public`.  
   - **Apache** : le fichier `public/.htaccess` envoie les requêtes vers `index.php`. Si ça ne marche pas, vérifier que `AllowOverride All` est actif (sinon contacter l’hébergeur).  
   - **Nginx** (certains hébergements OVH) : `.htaccess` est ignoré. Il faut dans la config du vhost (ou via le panel OVH si proposé) :
   ```nginx
   root /chemin/vers/techOffice/public;
   location / {
       try_files $uri /index.php$is_args$args;
   }
   location ~ ^/index\.php(/|$) {
       fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;  # ou 127.0.0.1:9000
       include fastcgi_params;
       fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
       internal;
   }
   ```
   - **Test rapide** : ouvrir `https://votredomaine.com/index.php/login`. Si ça affiche le login, le problème vient uniquement de la réécriture (Apache/Nginx).

9. **Permissions :** répertoires `var/` et `var/cache/`, `var/log/` en écriture pour l’utilisateur du serveur web.

10. **Worker Messenger (si vous utilisez les tâches async, ex. webhooks inbound) :**
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
