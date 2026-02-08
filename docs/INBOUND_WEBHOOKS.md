# Inbound Alerts / Webhooks PrintAudit

## Configuration

- **Variable d'environnement** : `PRINTAUDIT_WEBHOOK_TOKEN` (dans `.env` ou `.env.local`). Si vide, le webhook répond 401.
- **Route** : `POST /api/inbound/printaudit/webhook`
- **Authentification** : header `X-Webhook-Token: <token>` ou `Authorization: Bearer <token>`.

## Exemples cURL

```bash
# Définir le token (remplacer par la valeur réelle)
export TOKEN="votre-secret"

# Envoi JSON
curl -s -X POST "http://localhost:8000/api/inbound/printaudit/webhook" \
  -H "X-Webhook-Token: $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"alert":"toner_low","deviceId":"PRN-001","serialNumber":"ABC123","message":"Toner noir faible"}'

# Réponse attendue (nouveau) : {"ok":true,"id":1}
# Réponse attendue (doublon) : {"ok":true,"id":1,"duplicate":true}

# Envoi avec Bearer
curl -s -X POST "http://localhost:8000/api/inbound/printaudit/webhook" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"event":"offline","ip":"192.168.1.10"}'

# Sans token (doit retourner 401)
curl -s -X POST "http://localhost:8000/api/inbound/printaudit/webhook" \
  -H "Content-Type: application/json" \
  -d '{}'
```

## Traitement asynchrone

Après persistance, un message Messenger `ProcessInboundEventMessage` est envoyé. Le worker doit tourner :

```bash
php bin/console messenger:consume async -v
```

## Republier les événements en erreur

```bash
php bin/console app:inbound-events:reprocess --status=failed --provider=printaudit_fm
```

## Admin

- Liste (HTML) : `/admin/inbound-events` (nécessite `ROLE_ADMIN`)
- Détail (HTML) : `/admin/inbound-events/{id}`
- Détail (JSON) : `/admin/inbound-events/{id}?format=json`
