# API Inbound – Rapports CSV et alertes mail

API REST pour recevoir en production des rapports CSV (PrintAudit) et des alertes mail (texte + pièces jointes) sans utiliser IMAP. Conçue pour OVH mutualisé où l’accès IMAP sortant est bloqué : un script externe POST vers Symfony.

## Endpoints

| Méthode | URL | Description |
|--------|-----|-------------|
| POST | `/api/inbound/printaudit/report` | Envoi d’un fichier CSV (rapport) |
| POST | `/api/inbound/mail/alert` | Envoi d’une alerte textuelle avec ou sans pièces jointes |

## Sécurité

- **Authentification** : header `X-Inbound-Token` doit contenir le token configuré.
- **Variable d’environnement** : `INBOUND_TOKEN` (en prod, définir dans `.env.local` ou dans l’hébergeur).
- Si token manquant ou incorrect : réponse **401** JSON `{"ok":false,"error":"Unauthorized"}`.

## 1) POST /api/inbound/printaudit/report

- **Content-Type** : `multipart/form-data`
- **Champs** :
  - `csv_file` (obligatoire) : fichier CSV
  - `siteId`, `printerSerial`, `reportType` (optionnels, meta pour usage futur)
- **Réponse** : `{"ok": true, "imported": 42, "errors": []}`

### Exemple cURL (rapport CSV)

```bash
curl -X POST "https://votredomaine.com/api/inbound/printaudit/report" \
  -H "X-Inbound-Token: VOTRE_TOKEN_SECRET" \
  -F "csv_file=@/chemin/vers/rapport.csv"
```

Avec meta optionnelle :

```bash
curl -X POST "https://votredomaine.com/api/inbound/printaudit/report" \
  -H "X-Inbound-Token: VOTRE_TOKEN_SECRET" \
  -F "csv_file=@rapport.csv" \
  -F "siteId=1" \
  -F "printerSerial=ABC123" \
  -F "reportType=monthly"
```

## 2) POST /api/inbound/mail/alert

Deux modes acceptés.

### Mode JSON (alerte sans pièce jointe)

- **Content-Type** : `application/json`
- **Corps** :
  - `messageId` (string, optionnel)
  - `subject` (string)
  - `from` ou `fromEmail` (string)
  - `receivedAt` (string ISO 8601, optionnel)
  - `body` (string, texte brut)
  - `severity` (string, optionnel : `info`, `warning`, `critical`, défaut `info`)
  - `tags` (array, optionnel)

**Réponse** : `{"ok": true, "alertId": 123, "attachmentsSaved": 0}`

### Exemple cURL (alerte JSON)

```bash
curl -X POST "https://votredomaine.com/api/inbound/mail/alert" \
  -H "X-Inbound-Token: VOTRE_TOKEN_SECRET" \
  -H "Content-Type: application/json" \
  -d '{
    "messageId": "<msg-123@serveur>",
    "subject": "Alerte toner faible",
    "from": "alertes@example.com",
    "receivedAt": "2026-01-28T14:30:00+00:00",
    "body": "Imprimante HP Room 101 : niveau toner noir < 15%.",
    "severity": "warning",
    "tags": ["printaudit", "toner"]
  }'
```

### Mode multipart (alerte + pièces jointes)

- **Content-Type** : `multipart/form-data`
- **Champs** :
  - `payload` : chaîne JSON (mêmes champs que ci-dessus)
  - `attachments[]` : un ou plusieurs fichiers (CSV, PDF, TXT, PNG, JPG, etc.)

**Réponse** : `{"ok": true, "alertId": 124, "attachmentsSaved": 2}`

### Exemple cURL (alerte + pièces jointes)

```bash
# Fichier payload.json contenant le JSON de l’alerte
curl -X POST "https://votredomaine.com/api/inbound/mail/alert" \
  -H "X-Inbound-Token: VOTRE_TOKEN_SECRET" \
  -F "payload=@payload.json;type=application/json" \
  -F "attachments[]=@rapport.csv" \
  -F "attachments[]=@document.pdf"
```

## Pièces jointes

- **Dossier de stockage** : `var/inbound/YYYY/MM/` (nom sécurisé + uniqid).
- **Extensions autorisées** : csv, pdf, txt, png, jpg, jpeg.
- **Extensions refusées** : php, phtml, exe, js, etc.
- **Taille max** : variable d’environnement `INBOUND_MAX_FILE_SIZE` (ex. `10M`).
- **Déduplication** : hash SHA256 en base ; doublons possibles selon contrainte unique.

## Traitement asynchrone

- Chaque alerte enregistrée déclenche un message **ProcessInboundAlertMessage** (Messenger).
- **Handler** : si une pièce jointe est un CSV, il appelle `ImportCsvService` sur ce fichier ; sinon l’alerte est simplement marquée comme traitée. En cas d’erreur : statut `ERROR` et `errorMessage` renseignés.

## Logging

- **Canal Monolog** : `inbound`.
- Log à chaque réception : IP, route, subject, nombre de pièces jointes.
- Log en **warning** si token invalide, payload invalide ou extension de fichier refusée.

## Déploiement OVH (mutualisé)

1. **Variables d’environnement**  
   Dans `.env.local` (ou panel OVH) :
   - `INBOUND_TOKEN` : token secret fort (générer avec `openssl rand -hex 32`).
   - Optionnel : `INBOUND_MAX_FILE_SIZE=10M`.

2. **Permissions**  
   Le répertoire `var/` doit être writable par le serveur web (création de `var/inbound/YYYY/MM/` et logs) :
   ```bash
   chmod -R 755 var
   # ou selon config OVH (souvent 750 / 775)
   ```

3. **Cache**  
   Après déploiement :
   ```bash
   php bin/console cache:clear --env=prod
   ```

4. **Migrations**  
   Exécuter les migrations pour créer les tables `inbound_alert` et `inbound_alert_attachment` :
   ```bash
   php bin/console doctrine:migrations:migrate --no-interaction
   ```

5. **Messenger**  
   Si le transport est `doctrine` (file d’attente en BDD), lancer le worker en cron ou en tâche planifiée :
   ```bash
   php bin/console messenger:consume async -l 100
   ```
   Ou via cron toutes les minutes :  
   `* * * * * cd /path/to/project && php bin/console messenger:consume async --limit=50`

## Fichiers créés / modifiés (résumé)

- **Entités** : `InboundAlert`, `InboundAlertAttachment`
- **Repositories** : `InboundAlertRepository`, `InboundAlertAttachmentRepository`
- **Services** : `InboundFileStorageService`, `CsvUploadService`
- **Controller** : `App\Controller\Api\InboundApiController` (report + alert)
- **Message / Handler** : `ProcessInboundAlertMessage`, `ProcessInboundAlertHandler`
- **Config** : `services.yaml`, `monolog.yaml` (canal `inbound`), `messenger.yaml` (routing)
- **Migration** : `Version20260128180000`
- **Import** : `ImportController` utilise `CsvUploadService` pour mutualiser la validation CSV

## Tests rapides (local)

```bash
# Token (à mettre dans .env.local : INBOUND_TOKEN=...)
export TOKEN="votre_token"

# Rapport CSV
curl -X POST "http://localhost:8000/api/inbound/printaudit/report" \
  -H "X-Inbound-Token: $TOKEN" \
  -F "csv_file=@docs/export.csv"

# Alerte JSON
curl -X POST "http://localhost:8000/api/inbound/mail/alert" \
  -H "X-Inbound-Token: $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"subject":"Test","from":"a@b.com","body":"Message test","severity":"info"}'
```
