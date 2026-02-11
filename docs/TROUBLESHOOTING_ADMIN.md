# Dépannage - Problème de connexion Super Admin

## Problème : Impossible de se connecter avec le super admin défini dans `.env.local`

### Causes possibles

1. **La commande n'a pas été exécutée**
   - Les variables dans `.env.local` servent uniquement à créer le compte
   - Vous devez exécuter la commande pour créer/mettre à jour le compte

2. **Les variables d'environnement ne sont pas lues correctement**
   - Vérifiez que le fichier `.env.local` existe bien
   - Vérifiez qu'il n'y a pas d'espaces autour du `=`
   - Vérifiez qu'il n'y a pas de guillemets inutiles

3. **Le compte existe mais avec un mauvais mot de passe**
   - Le mot de passe peut avoir été modifié
   - Le hash du mot de passe peut être incorrect

## Solutions

### 1. Vérifier le fichier `.env.local`

Assurez-vous que votre fichier `.env.local` contient bien :

```bash
SUPER_ADMIN_EMAIL=votre-email@exemple.com
SUPER_ADMIN_PASSWORD=votre_mot_de_passe
```

**Important :**
- Pas d'espaces autour du `=`
- Pas de guillemets sauf si nécessaire (pour les mots de passe avec espaces)
- Pas de caractères spéciaux non échappés

### 2. Exécuter la commande de création

```bash
php bin/console app:user:create-admin
```

Cette commande va :
- Lire les variables `SUPER_ADMIN_EMAIL` et `SUPER_ADMIN_PASSWORD` depuis `.env.local`
- Créer le compte s'il n'existe pas
- Mettre à jour le mot de passe et le rôle si le compte existe déjà

### 3. Vérifier que le compte a été créé

```bash
php bin/console doctrine:query:sql "SELECT email, roles FROM user WHERE email = 'votre-email@exemple.com'"
```

### 4. Créer le compte manuellement (si la commande ne fonctionne pas)

Si la commande ne lit pas les variables d'environnement, vous pouvez créer le compte manuellement :

```bash
php bin/console app:user:create-admin votre-email@exemple.com votre_mot_de_passe
```

### 5. Vérifier les logs

Si le problème persiste, vérifiez les logs :

```bash
tail -f var/log/prod.log
```

### 6. Réinitialiser le mot de passe d'un compte existant

Si le compte existe mais que vous ne vous souvenez plus du mot de passe :

```bash
# Option 1 : Utiliser la commande avec les nouveaux identifiants
php bin/console app:user:create-admin votre-email@exemple.com nouveau_mot_de_passe

# Option 2 : Modifier directement en base de données (non recommandé)
# Utilisez plutôt la commande ci-dessus
```

## Vérifications supplémentaires

### Vérifier que le fichier `.env.local` est bien chargé

```bash
php bin/console debug:container --env-vars | grep SUPER_ADMIN
```

### Vérifier la connexion à la base de données

```bash
php bin/console doctrine:schema:validate
```

### Vider le cache

```bash
php bin/console cache:clear --env=prod
```

## Exemple de fichier `.env.local` correct

```bash
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=votre_secret_genere
DATABASE_URL="mysql://user:password@host:3306/dbname?serverVersion=8.0.32&charset=utf8mb4"
SUPER_ADMIN_EMAIL=admin@exemple.com
SUPER_ADMIN_PASSWORD=MonMotDePasse123!
MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
```

## Si rien ne fonctionne

1. Vérifiez que vous êtes bien en production (`APP_ENV=prod`)
2. Vérifiez que le fichier `.env.local` est bien à la racine du projet
3. Vérifiez les permissions du fichier `.env.local`
4. Essayez de créer le compte manuellement avec la commande complète
5. Vérifiez les logs d'erreur PHP et Symfony
