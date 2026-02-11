@echo off
REM Script pour corriger le problème DebugBundle en production (Windows)

echo 🔧 Correction du problème DebugBundle en production...

REM 1. Réinstaller les dépendances sans dev
echo 📦 Réinstallation des dépendances (sans dev)...
composer install --no-dev --optimize-autoloader --no-interaction

REM 2. Vider le cache de production
echo 🗑️  Vidage du cache de production...
php bin/console cache:clear --env=prod --no-debug

REM 3. Réchauffer le cache
echo 🔥 Réchauffage du cache...
php bin/console cache:warmup --env=prod --no-debug

echo ✅ Terminé ! Le DebugBundle ne devrait plus être chargé en production.
