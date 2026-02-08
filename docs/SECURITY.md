# Sécurité – TechOffice

## Mesures en place

### XSS (Cross-Site Scripting)

- **Twig** : pas de `|raw` sur des données utilisateur. Twig échappe par défaut tout affichage `{{ ... }}`.
- **JavaScript / innerHTML** : tout contenu dynamique injecté côté client (réponses AJAX, `error.message`, données API) est échappé avant d’être mis dans le DOM :
  - Fonction globale `window.escapeHtml()` (définie dans `base.html.twig`) utilisée partout où du HTML est construit en JS à partir de données (contrat show/edit, piece show, messages d’erreur).
  - En cas d’absence de `escapeHtml`, repli sur un échappement manuel des caractères `& < > " '`.
- **Règle** : ne jamais concaténer une chaîne non fiable (utilisateur, API, `error.message`) dans une chaîne HTML puis l’assigner à `innerHTML` sans échappement.

### CSRF (Cross-Site Request Forgery)

- **Formulaires Symfony** : protection CSRF activée (`config/packages/csrf.yaml`), token inclus dans les formulaires.
- **Actions sensibles** : les suppressions et actions d’état (validation, facturation, etc.) vérifient le token via `isCsrfTokenValid()`.
- **Requêtes AJAX POST** : la route `POST /piece/{id}/add-modele` exige un token CSRF (`piece_add_modele`) envoyé dans le body ; le contrôleur rejette en 403 si le token est absent ou invalide.

### Authentification et autorisation

- **Security** : login par formulaire, contrôle d’accès par rôles (`ROLE_ADMIN`, `ROLE_COMPTABLE`, etc.).
- **API webhook** : `/api/inbound/printaudit/webhook` protégé par un secret (header `X-Webhook-Token` ou Bearer), pas de session.

### Entrées utilisateur (backend)

- **Formulaires** : utilisation des types de formulaire Symfony et de la validation (contraintes sur les entités/formulaires quand elles sont en place).
- **Requêtes** : les paramètres sont typés (`getInt()`, etc.) et les entités récupérées via le routeur/Doctrine (ex. `Piece $piece`) pour limiter les injections.
- **Webhook** : le corps de la requête est stocké brut puis traité en asynchrone ; pas d’exécution directe de contenu entrant.

### XMLHttpRequest / fetch

- **GET** : utilisés pour la recherche et le chargement de fragments (sites, clients, détails période). Les réponses HTML sont rendues par Twig (échappement côté serveur) ; les réponses JSON utilisées pour construire du HTML sont échappées côté client avec `escapeHtml()`.
- **POST** : les requêtes qui modifient des données (ex. add-modele) envoient le token CSRF et sont validées côté serveur.
- **Headers** : `X-Requested-With: XMLHttpRequest` utilisé pour la détection de requêtes AJAX ; la protection CSRF ne repose pas uniquement sur ce header.

## Bonnes pratiques pour le développement

1. **Ne jamais utiliser `|raw`** en Twig sur des données utilisateur ou provenant de la base.
2. **Tout contenu dynamique injecté en JS** (dans `innerHTML`, `document.write`, ou construction de nœuds à partir de chaînes) doit passer par `escapeHtml()` ou un équivalent.
3. **Toute action POST (formulaire ou fetch/XHR)** qui modifie des données doit inclure et vérifier un token CSRF.
4. **Ne pas exposer de secrets** dans le front (tokens, mots de passe) ; le token CSRF est conçu pour être dans la page et renvoyé avec la requête, pas pour être partagé avec un autre site.
5. **Validation côté serveur** : toujours valider et filtrer les entrées ; ne pas se fier uniquement au JavaScript côté client.

## En cas d’incident

- Vérifier les logs (`var/log/`) et les réponses d’erreur (sans exposer de stack trace en production).
- Révoquer ou régénérer les secrets (APP_SECRET, PRINTAUDIT_WEBHOOK_TOKEN) si une fuite est suspectée.
- Contacter l’équipe en charge de la sécurité applicative.
