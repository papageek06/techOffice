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

---

## Erreur « Connection refused » vers ssl0.ovh.net:993 (OVH)

### Symptôme

```
Échec de connexion IMAP: Can't connect to ssl0.ovh.net,993: Connection refused
```

La commande tourne bien, mais la connexion TCP vers le serveur IMAP est refusée.

### Cause

Sur l’hébergement mutualisé OVH, les **connexions sortantes** (depuis le serveur web vers l’extérieur) peuvent être **restreintes ou bloquées** par le pare-feu. Le port 993 (IMAPS) vers `ssl0.ovh.net` n’est alors pas accessible depuis le cluster web.

### Pistes de solution

1. **Contacter OVH**  
   Demander si les connexions sortantes vers le port **993 (IMAP)** sont autorisées depuis votre hébergement, et s’il existe un **serveur IMAP « interne »** à utiliser quand l’application tourne chez OVH (ex. hostname différent, ou règle firewall à faire ouvrir).

2. **Tester depuis une autre machine**  
   Lancer la même commande en local (ou sur un VPS/serveur où le port 993 sortant est autorisé) avec les mêmes variables d’env pour vérifier que la config IMAP est correcte.

3. **Exécuter l’import ailleurs**  
   Si OVH ne peut pas ouvrir l’accès sortant IMAP depuis l’hébergement web, faire tourner `app:mail:import` sur une machine qui a accès à Internet (cron local, autre serveur, script planifié), puis synchroniser les pièces jointes / données si besoin.

4. **Vérifier le serveur IMAP**  
   S’assurer que la boîte `alert@professionaldev.fr` est bien sur les serveurs OVH (ssl0.ovh.net) et que le compte est activé. Tester la connexion avec un client mail (Thunderbird, etc.) depuis ton PC pour confirmer que host/port/identifiants sont bons.
