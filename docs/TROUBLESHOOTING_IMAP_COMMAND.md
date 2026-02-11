# Dépannage - Commande app:mail:import non trouvée

## Problème

La commande `app:mail:import` n'est pas reconnue par Symfony.

## Solutions

### 1. Vider le cache de production

```bash
php bin/console cache:clear --env=prod --no-debug
```

### 2. Vérifier que le fichier existe

```bash
ls -la src/Command/ImportMailCommand.php
```

### 3. Vérifier l'autoload

```bash
composer dump-autoload
```

### 4. Vérifier que la commande est bien enregistrée

```bash
php bin/console list app:mail
```

Si la commande apparaît, elle est bien enregistrée.

### 5. Vérifier les erreurs de configuration

```bash
php bin/console debug:container App\\Command\\ImportMailCommand
```

### 6. Vérifier les logs

```bash
tail -f var/log/prod.log
```

## Si rien ne fonctionne

1. Vérifiez que tous les fichiers sont bien présents sur le serveur :
   - `src/Command/ImportMailCommand.php`
   - `src/Service/ImapMailboxClient.php`
   - `src/Service/InboundMailImporter.php`
   - `src/Entity/InboundEmail.php`
   - `src/Entity/InboundAttachment.php`

2. Vérifiez les permissions :
   ```bash
   chmod -R 755 src/
   ```

3. Réinstallez les dépendances :
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
