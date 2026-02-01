# Contraintes et logique métier

**Dernière mise à jour :** 2026-01-28

Ce document liste les contraintes (validation, unicité, règles) et la logique métier du site techOffice.

---

## 1. Contraintes d’intégrité (base de données)

### Unicité
- **user** : `email` unique
- **fabricant** : `nom_fabricant` unique
- **modele** : (`fabricant_id`, `reference_modele`) unique
- **imprimante** : `numero_serie` unique
- **user_device** : (`user_id`, `device_id`) unique
- **stock_location** : (`site_id`, `nom_stock`) unique
- **piece** : `reference` unique
- **stock_item** : (`stock_location_id`, `piece_id`) unique
- **piece_modele** : (`piece_id`, `modele_id`, `role`) unique

### Clés étrangères et cascade
- Suppression en cascade (ON DELETE CASCADE) : site → imprimantes, releves, etats ; client → sites, contrats ; contrat → lignes ; contrat_ligne → affectations, periodes ; facturation_periode → facturation_compteur ; intervention → intervention_ligne ; user → interventions, demandes_conge, user_device, login_challenge ; piece → stock_item, piece_modele ; etc. (voir `docs/DATABASE_SCHEMA.md`).

### Règles métier liées aux données
- **Affectation matériel** : une seule affectation active (`date_fin` NULL) par `contrat_ligne_id` à la fois.
- **Relevés / états** : identifiés par (`imprimante_id`, `date_releve`) ou (`imprimante_id`, `date_capture`) pour éviter les doublons à l’import CSV.

---

## 2. Logique métier par domaine

### 2.1 Interventions

| Règle | Description |
|-------|-------------|
| **Clôture** | Lors du passage au statut **TERMINEE**, un listener (`InterventionStockListener`) : (1) fixe `date_intervention = date_creation` ; (2) appelle `InterventionClotureStockService::applyStockLivraison()`. |
| **Stock à la clôture** | Si l’intervention a des lignes (pièces livrées/installées) : **débit** du stock entreprise pour toutes les pièces ; **crédit** du stock du site client **uniquement** pour les pièces dont le rôle est TONER_* ou BAC_RECUP (livrées en stock). Les autres pièces (drum, fuser, etc.) sont considérées installées, pas créditées au stock client. |
| **Condition d’application stock** | `applyStockLivraison` ne fait rien si : statut ≠ TERMINEE, ou `stock_applique` déjà true, ou pas de lignes, ou aucun stock entreprise trouvé. |
| **Lignes d’intervention** | Table `intervention_ligne` : pièce + quantité. Utilisées pour livraison toner / pièces installées ; les mouvements sont appliqués une seule fois à la clôture. |

| **Facturable** | Colonne `facturable` nullable : **null** = non validé par l'admin (défaut), **true** = à facturer, **false** = ne pas facturer. Champ visible et modifiable uniquement par **ROLE_ADMIN** (formulaires et vues). |

**Services :** `InterventionClotureStockService`, `InterventionStockListener`.

---

### 2.2 Affectation matériel

| Règle | Description |
|-------|-------------|
| **Une affectation active par ligne** | Une seule affectation avec `date_fin` NULL par `contrat_ligne_id`. |
| **Changement de machine** | Lors de la création d’une nouvelle affectation (`AffectationMaterielManager::createAffectation`), l’affectation active existante est clôturée : `date_fin = date_debut_nouvelle - 1 jour`. |
| **Clôture manuelle** | `closeAffectation()` fixe `date_fin` et optionnellement une raison ; impossible de clôturer deux fois. |

**Service :** `AffectationMaterielManager`.

---

### 2.3 Stocks

| Règle | Description |
|-------|-------------|
| **Stocks CLIENT** | Un stock de type **CLIENT** ne peut contenir que des pièces de type **TONER** ou **BAC_RECUP** (`StockValidationService::canAddPieceToStock`). |
| **Stocks ENTREPRISE** | Tous les types de pièces sont autorisés. |
| **Validation** | `StockValidationService::validateStockItem()` lève une exception si une pièce non autorisée est ajoutée à un stock CLIENT. |

**Services :** `StockValidationService`, `StockLocatorService`, `InterventionClotureStockService`.

---

### 2.4 Facturation (contrats, périodes, compteurs)

| Règle | Description |
|-------|-------------|
| **Périodes** | Générées par `BillingPeriodGenerator` selon la **périodicité** de la ligne de contrat (MENSUEL, TRIMESTRIEL, SEMESTRIEL, ANNUEL) et la date de **prochaine facturation**. Jusqu’à 4 périodes à l’avance ou date de référence + 1 an. |
| **Compteurs** | Pour chaque période, les `FacturationCompteur` sont résolus par `CounterSnapshotResolver` à partir des relevés (`releve_compteur`) : compteur début = relevé le plus proche avant/à la date de début ; compteur fin = relevé le plus proche avant/à la date de fin. Une affectation sans relevé suffisant ne génère pas de facturation_compteur. |
| **Calcul du montant** | `BillingCalculator::calculateForPeriod` : prix fixe + (pages noires facturables × prix_page_noir) + (pages couleur facturables × prix_page_couleur), avec déduction des pages incluses (noir/couleur) de la ligne de contrat. |

**Services :** `BillingPeriodGenerator`, `CounterSnapshotResolver`, `BillingCalculator`, `BillingEstimationService`.

---

### 2.5 Sécurité et appareils (device check)

| Règle | Description |
|-------|-------------|
| **Appareil inconnu** | Au login, si l’appareil n’est pas dans `user_device`, un OTP est généré et envoyé par SMS ; l’utilisateur est redirigé vers la validation. |
| **OTP** | Code à 6 chiffres ; stocké uniquement en hash (BCRYPT) dans `login_challenge` ; expiration 10 minutes ; maximum 5 tentatives. |
| **Appareil de confiance** | Après validation OTP, l’appareil peut être enregistré (confiance temporaire ou permanente). |

**Documentation détaillée :** `docs/DEVICE_CHECK_SYSTEM.md`.  
**Services :** `DeviceIdManager`, `OtpGenerator`, `SmsSenderInterface` / `MockSmsSender`.

---

## 3. Enums (valeurs autorisées)

Les valeurs réelles sont définies dans `src/Enum/`. Résumé :

| Enum | Valeurs |
|------|---------|
| **StatutImprimante** | actif, pret, assurance, hs, decheterie |
| **TypeIntervention** | sur_site, distance, atelier, livraison_toner |
| **StatutIntervention** | ouverte, en_cours, terminee, annulee |
| **TypeConge** | paye, sans_solde, maladie |
| **StatutDemandeConge** | demandee, acceptee, refusee, annulee |
| **TypeContrat** | MAINTENANCE, LOCATION, VENTE, PRET |
| **StatutContrat** | BROUILLON, ACTIF, SUSPENDU, RESILIE, TERMINE |
| **Periodicite** | MENSUEL, TRIMESTRIEL, SEMESTRIEL, ANNUEL |
| **TypeAffectation** | PRINCIPALE, REMPLACEMENT_TEMP, REMPLACEMENT_DEF, PRET |
| **StatutFacturation** | BROUILLON, VALIDE, FACTURE |
| **SourceCompteur** | MANUEL, SNMP, SCAN |
| **StockLocationType** | ENTREPRISE, CLIENT |
| **PieceType** | TONER, BAC_RECUP, DRUM, FUSER, MAINTENANCE_KIT, AUTRE |
| **PieceRoleModele** | TONER_K, TONER_C, TONER_M, TONER_Y, BAC_RECUP, DRUM, FUSER, AUTRE |

---

## 4. Contraintes de validation (formulaires)

Les contraintes sont gérées dans les types de formulaire (`src/Form/`) et éventuellement les entités (attributs `#[Assert\...]` si présents). Pour les champs enum, les formulaires utilisent `ChoiceType` avec `choice_label` pour éviter la conversion en chaîne côté Twig.

---

## 5. Commandes et scripts utiles

- **Migrations :** `php bin/console doctrine:migrations:migrate`
- **Validation schéma :** `php bin/console doctrine:schema:validate`
- **Génération périodes de facturation :** via `BillingPeriodGenerator` (commande ou appel service selon l’implémentation)

---

**À mettre à jour** à chaque ajout de contrainte ou de règle métier significative.
