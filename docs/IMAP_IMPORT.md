# Import automatique d'emails IMAP

Ce système permet d'importer automatiquement les emails reçus sur une boîte IMAP OVH, de télécharger les pièces jointes et de les stocker en base de données.

## Configuration

### 1. Variables d'environnement

Ajoutez les variables suivantes dans votre fichier `.env.local` :

```bash
# Configuration IMAP OVH
MAIL_IMAP_HOST=ssl0.ovh.net
MAIL_IMAP_PORT=993
MAIL_IMAP_USER=alert@professionaldev.fr
MAIL_IMAP_PASSWORD=votre_mot_de_passe_imap
MAIL_IMAP_MAILBOX=INBOX

# Répertoire de stockage des pièces jointes (optionnel)
# Par défaut: var/mail_attachments
MAIL_ATTACHMENTS_DIR=%kernel.project_dir%/var/mail_attachments
```

**⚠️ Important :** Ne jamais committer le fichier `.env.local` contenant les mots de passe.

### 2. Extension PHP IMAP

Assurez-vous que l'extension PHP IMAP est installée et activée :

```bash
# Vérifier si l'extension est installée
php -m | grep imap

# Si non installée, installer selon votre système
# Ubuntu/Debian:
sudo apt-get install php-imap
sudo phpenmod imap

# RedHat/CentOS:
sudo yum install php-imap

# macOS (Homebrew):
brew install php-imap
```

### 3. Migration de la base de données

Exécutez la migration pour créer les tables nécessaires :

```bash
php bin/console doctrine:migrations:migrate
```

## Utilisation

### Commande d'import

La commande principale pour importer les emails :

```bash
# Import standard (50 emails max)
php bin/console app:mail:import

# Limiter le nombre d'emails
php bin/console app:mail:import --limit=10

# Mode simulation (dry-run) - ne sauvegarde rien
php bin/console app:mail:import --dry-run
```

### Configuration Cron

Pour automatiser l'import toutes les 5 minutes, ajoutez cette ligne dans votre crontab :

```bash
# Éditer le crontab
crontab -e

# Ajouter cette ligne (remplacer /path/to/project par le chemin réel)
*/5 * * * * cd /path/to/project && php bin/console app:mail:import --env=prod >> /path/to/project/var/log/mail_import.log 2>&1
```

**Exemple avec chemin complet :**

```bash
*/5 * * * * /usr/bin/php /var/www/techOffice/bin/console app:mail:import --env=prod >> /var/www/techOffice/var/log/mail_import.log 2>&1
```

### Vérification des logs

Les logs sont enregistrés dans le canal standard de Symfony (généralement `var/log/prod.log`).

Pour suivre les imports en temps réel :

```bash
tail -f var/log/prod.log | grep -i "imap\|mail\|attachment"
```

## Fonctionnalités

### Gestion des doublons

- Les emails sont identifiés par leur `Message-ID` unique
- Les pièces jointes sont identifiées par leur hash SHA256
- Si un email ou une pièce jointe existe déjà, il est ignoré (pas de doublon)

### Stockage des pièces jointes

- Les fichiers sont stockés dans `var/mail_attachments/YYYY/MM/`
- Les noms de fichiers sont sécurisés (caractères spéciaux remplacés)
- Chaque fichier est identifié par un hash SHA256 unique

### Statut des emails

- **NEW** : Email importé mais pas encore traité
- **PROCESSED** : Email traité avec succès
- **ERROR** : Erreur lors du traitement (consulter `error_message`)

### Marquage des emails

- Les emails sont marqués comme lus (SEEN) dans IMAP uniquement après un traitement réussi
- En cas d'erreur, l'email reste non lu pour être retraité au prochain import

## Sécurité

### Verrouillage

La commande utilise un verrou fichier pour éviter les exécutions simultanées :
- Fichier de verrou : `var/lock/mail-import.lock`
- Timeout automatique si une instance est bloquée

### Permissions

Assurez-vous que les répertoires suivants sont accessibles en écriture :

```bash
chmod -R 755 var/mail_attachments
chmod -R 755 var/lock
```

## Dépannage

### Erreur de connexion IMAP

1. Vérifiez les identifiants dans `.env.local`
2. Vérifiez que l'extension PHP IMAP est installée
3. Testez la connexion manuellement :
   ```bash
   php -r "var_dump(imap_open('{ssl0.ovh.net:993/imap/ssl}INBOX', 'user@domain.com', 'password'));"
   ```

### Aucun email importé

1. Vérifiez qu'il y a bien des emails non lus dans la boîte
2. Vérifiez les logs pour voir les erreurs éventuelles
3. Utilisez `--dry-run` pour voir ce qui serait importé

### Erreurs de permissions

```bash
# Donner les permissions nécessaires
chown -R www-data:www-data var/mail_attachments
chmod -R 755 var/mail_attachments
```

### Pièces jointes non sauvegardées

1. Vérifiez l'espace disque disponible
2. Vérifiez les permissions du répertoire `var/mail_attachments`
3. Consultez les logs pour les erreurs spécifiques

## Structure de la base de données

### Table `inbound_email`

| Colonne | Type | Description |
|---------|------|-------------|
| id | INT | Identifiant unique |
| message_id | VARCHAR(255) | Message-ID unique de l'email |
| subject | VARCHAR(500) | Sujet de l'email |
| from_email | VARCHAR(255) | Expéditeur |
| received_at | DATETIME | Date de réception |
| processed_at | DATETIME | Date de traitement |
| status | VARCHAR(20) | Statut (NEW/PROCESSED/ERROR) |
| error_message | TEXT | Message d'erreur si échec |
| created_at | DATETIME | Date de création |

### Table `inbound_attachment`

| Colonne | Type | Description |
|---------|------|-------------|
| id | INT | Identifiant unique |
| inbound_email_id | INT | Référence à l'email |
| filename_original | VARCHAR(500) | Nom original du fichier |
| mime_type | VARCHAR(100) | Type MIME |
| size | INT | Taille en octets |
| sha256 | VARCHAR(64) | Hash SHA256 (unique) |
| stored_path | VARCHAR(500) | Chemin de stockage |
| stored_at | DATETIME | Date de stockage |

## Exemples d'utilisation

### Import manuel

```bash
# Importer les 10 premiers emails non lus
php bin/console app:mail:import --limit=10

# Tester sans sauvegarder
php bin/console app:mail:import --dry-run
```

### Requêtes SQL utiles

```sql
-- Voir les emails importés aujourd'hui
SELECT * FROM inbound_email WHERE DATE(created_at) = CURDATE();

-- Compter les pièces jointes par type
SELECT mime_type, COUNT(*) as count 
FROM inbound_attachment 
GROUP BY mime_type;

-- Voir les emails en erreur
SELECT * FROM inbound_email WHERE status = 'ERROR';
```

## Support

Pour toute question ou problème, consultez les logs dans `var/log/prod.log` ou contactez l'équipe de développement.
