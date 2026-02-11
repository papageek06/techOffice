#!/bin/bash
# Script pour corriger le problème DebugBundle en production

echo "🔧 Correction du problème DebugBundle en production..."

# 1. Vérifier que APP_ENV=prod
if [ -z "$APP_ENV" ] || [ "$APP_ENV" != "prod" ]; then
    echo "⚠️  APP_ENV n'est pas défini à 'prod'. Vérifiez votre .env.local ou variables d'environnement."
fi

# 2. Réinstaller les dépendances sans dev
echo "📦 Réinstallation des dépendances (sans dev)..."
composer install --no-dev --optimize-autoloader --no-interaction

# 3. Vider le cache de production
echo "🗑️  Vidage du cache de production..."
php bin/console cache:clear --env=prod --no-debug

# 4. Réchauffer le cache
echo "🔥 Réchauffage du cache..."
php bin/console cache:warmup --env=prod --no-debug

echo "✅ Terminé ! Le DebugBundle ne devrait plus être chargé en production."
