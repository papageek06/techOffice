# Schéma de la Base de Données

**⚠️ IMPORTANT : Ce document doit être tenu à jour à chaque modification de la structure de la base de données.**

**Dernière mise à jour :** 2026-01-28

---

## Vue d'ensemble

Cette base de données gère :
- Les clients et leurs sites
- Les imprimantes et leurs modèles/fabricants
- Les relevés de compteurs et états des consommables
- Les interventions techniques
- Les utilisateurs et leur authentification
- Les demandes de congé
- La vérification d'appareil (device check) avec OTP
- Les contrats clients et facturation par période
- La gestion des stocks multi-emplacements

---

## Tables

### 1. `user`

Table des utilisateurs de l'application.

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | Identifiant unique |
| `email` | VARCHAR(180) | UNIQUE, NOT NULL | Email (identifiant de connexion) |
| `roles` | JSON | NOT NULL | Rôles de l'utilisateur (ROLE_USER, ROLE_ADMIN, ROLE_COMPTABLE) |
| `password` | VARCHAR(255) | NOT NULL | Mot de passe hashé |
| `nom` | VARCHAR(255) | NULL | Nom de l'utilisateur |
| `phone_number` | VARCHAR(20) | NULL | Numéro de téléphone (pour OTP SMS) |

**Index :**
- `UNIQ_IDENTIFIER_EMAIL` sur `email` (unique)

**Relations :**
- `OneToMany` → `intervention` (via `utilisateur_id`)
- `OneToMany` → `demande_conge` (via `utilisateur_id`)
- `OneToMany` → `user_device` (via `user_id`)
- `OneToMany` → `login_challenge` (via `user_id`)

---

### 2. `client`

Table des clients (entreprises).

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | Identifiant unique |
| `nom` | VARCHAR(255) | NOT NULL | Nom du client |
| `actif` | BOOLEAN | NOT NULL, DEFAULT TRUE | Statut actif/inactif |

**Relations :**
- `OneToMany` → `site` (via `client_id`)

---

### 3. `site`

Table des sites (localisations) des clients.

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | Identifiant unique |
| `client_id` | INT | FOREIGN KEY, NOT NULL | Référence vers `client.id` |
| `nom_site` | VARCHAR(255) | NOT NULL | Nom du site |
| `principal` | BOOLEAN | NOT NULL, DEFAULT FALSE | Site principal du client |
| `actif` | BOOLEAN | NOT NULL, DEFAULT TRUE | Statut actif/inactif |

**Relations :**
- `ManyToOne` → `client` (via `client_id`)
- `OneToMany` → `imprimante` (via `site_id`)

**Contraintes :**
- `ON DELETE CASCADE` : Si un client est supprimé, ses sites sont supprimés

---

### 4. `fabricant`

Table des fabricants d'imprimantes.

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | Identifiant unique |
| `nom_fabricant` | VARCHAR(150) | UNIQUE, NOT NULL | Nom du fabricant |

**Relations :**
- `OneToMany` → `modele` (via `fabricant_id`)

**Index :**
- Unique sur `nom_fabricant`

---

### 5. `modele`

Table des modèles d'imprimantes.

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | Identifiant unique |
| `fabricant_id` | INT | FOREIGN KEY, NOT NULL | Référence vers `fabricant.id` |
| `reference_modele` | VARCHAR(150) | NOT NULL | Référence du modèle |
| `couleur` | BOOLEAN | NOT NULL, DEFAULT FALSE | Imprimante couleur ou noir/blanc |

**Relations :**
- `ManyToOne` → `fabricant` (via `fabricant_id`)
- `OneToMany` → `imprimante` (via `modele_id`)

**Index :**
- `uniq_modele_fabricant_ref` : Unique sur (`fabricant_id`, `reference_modele`)

---

### 6. `imprimante`

Table des imprimantes installées.

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | Identifiant unique |
| `site_id` | INT | FOREIGN KEY, NOT NULL | Référence vers `site.id` |
| `modele_id` | INT | FOREIGN KEY, NOT NULL | Référence vers `modele.id` |
| `numero_serie` | VARCHAR(80) | UNIQUE, NOT NULL | Numéro de série |
| `date_installation` | DATE | NULL | Date d'installation |
| `adresse_ip` | VARCHAR(45) | NULL | Adresse IP de l'imprimante |
| `emplacement` | VARCHAR(255) | NULL | Emplacement physique |
| `suivie_par_service` | BOOLEAN | NOT NULL, DEFAULT TRUE | Suivi par le service (managed) |
| `statut` | VARCHAR(255) | NOT NULL, DEFAULT 'actif' | Statut (Enum: StatutImprimante) |
| `notes` | TEXT | NULL | Notes diverses |
| `dual_scan` | BOOLEAN | NOT NULL, DEFAULT FALSE | Scanner recto-verso (dual scan) |

**Relations :**
- `ManyToOne` → `site` (via `site_id`)
- `ManyToOne` → `modele` (via `modele_id`)
- `OneToMany` → `intervention` (via `imprimante_id`)
- `OneToMany` → `releve_compteur` (via `imprimante_id`)
- `OneToMany` → `etat_consommable` (via `imprimante_id`)

**Index :**
- `uniq_imprimante_numero_serie` : Unique sur `numero_serie`

**Contraintes :**
- `ON DELETE CASCADE` : Si un site est supprimé, ses imprimantes sont supprimées

---

### 7. `releve_compteur`

Table des relevés de compteurs d'impression.

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | Identifiant unique |
| `imprimante_id` | INT | FOREIGN KEY, NOT NULL | Référence vers `imprimante.id` |
| `date_releve` | DATETIME | NOT NULL | Date du relevé (LAST_SCAN_DATE) |
| `compteur_noir` | INT | NULL | Compteur noir |
| `compteur_couleur` | INT | NULL | Compteur couleur |
| `compteur_fax` | INT | NULL | Compteur fax |
| `source` | VARCHAR(30) | NOT NULL, DEFAULT 'manuel' | Source du relevé ('csv', 'manuel') |
| `date_reception_rapport` | DATETIME | NULL | Date de réception du rapport CSV (READING_DATE) |

**Relations :**
- `ManyToOne` → `imprimante` (via `imprimante_id`)

**Contraintes :**
- `ON DELETE CASCADE` : Si une imprimante est supprimée, ses relevés sont supprimés

**Note :** Les relevés sont identifiés de manière unique par (`imprimante_id`, `date_releve`) pour éviter les doublons lors de l'import CSV.

---

### 8. `etat_consommable`

Table des états des consommables (toners/encre).

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | Identifiant unique |
| `imprimante_id` | INT | FOREIGN KEY, NOT NULL | Référence vers `imprimante.id` |
| `date_capture` | DATETIME | NOT NULL | Date de capture (LAST_SCAN_DATE) |
| `noir_pourcent` | INT | NULL | Pourcentage noir (0-100, 0 = Low) |
| `cyan_pourcent` | INT | NULL | Pourcentage cyan (0-100, 0 = Low) |
| `magenta_pourcent` | INT | NULL | Pourcentage magenta (0-100, 0 = Low) |
| `jaune_pourcent` | INT | NULL | Pourcentage jaune (0-100, 0 = Low) |
| `bac_recuperation` | INT | NULL | État du bac de récupération |
| `date_epuisement_noir` | DATE | NULL | Date prévisionnelle d'épuisement noir |
| `date_epuisement_cyan` | DATE | NULL | Date prévisionnelle d'épuisement cyan |
| `date_epuisement_magenta` | DATE | NULL | Date prévisionnelle d'épuisement magenta |
| `date_epuisement_jaune` | DATE | NULL | Date prévisionnelle d'épuisement jaune |
| `date_reception_rapport` | DATETIME | NULL | Date de réception du rapport CSV (READING_DATE) |

**Relations :**
- `ManyToOne` → `imprimante` (via `imprimante_id`)

**Contraintes :**
- `ON DELETE CASCADE` : Si une imprimante est supprimée, ses états consommables sont supprimés

**Note :** Les états sont identifiés de manière unique par (`imprimante_id`, `date_capture`) pour éviter les doublons lors de l'import CSV.

---

### 9. `intervention`

Table des interventions techniques.

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | Identifiant unique |
| `imprimante_id` | INT | FOREIGN KEY, NOT NULL | Référence vers `imprimante.id` |
| `utilisateur_id` | INT | FOREIGN KEY, NOT NULL | Référence vers `user.id` (technicien) |
| `date_creation` | DATETIME | NOT NULL | Date de création |
| `date_intervention` | DATETIME | NULL | Date réelle de l'intervention |
| `type_intervention` | VARCHAR(255) | NOT NULL | Type (Enum: TypeIntervention) |
| `statut` | VARCHAR(255) | NOT NULL | Statut (Enum: StatutIntervention) |
| `description` | TEXT | NOT NULL | Description de l'intervention |
| `temps_facturable_minutes` | INT | NOT NULL, DEFAULT 0 | Temps facturable en minutes |
| `temps_reel_minutes` | INT | NULL | Temps réel en minutes |
| `facturable` | BOOLEAN | NULL | null = non validé par l'admin, true = à facturer, false = ne pas facturer (visible uniquement aux admins) |
| `stock_applique` | BOOLEAN | NOT NULL, DEFAULT FALSE | True une fois les mouvements de stock appliqués à la clôture |

**Relations :**
- `ManyToOne` → `imprimante` (via `imprimante_id`)
- `ManyToOne` → `user` (via `utilisateur_id`)
- `OneToMany` → `intervention_ligne` (via `intervention_id`)

**Contraintes :**
- `ON DELETE CASCADE` : Si une imprimante est supprimée, ses interventions sont supprimées

---

### 10. `intervention_ligne`

Lignes d'une intervention (pièces livrées ou installées). Utilisées pour appliquer les mouvements de stock à la clôture (débit entreprise, crédit client pour toners/bacs uniquement).

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | Identifiant unique |
| `intervention_id` | INT | FOREIGN KEY, NOT NULL | Référence vers `intervention.id` |
| `piece_id` | INT | FOREIGN KEY, NOT NULL | Référence vers `piece.id` |
| `quantite` | INT | NOT NULL | Quantité livrée/installée |

**Relations :**
- `ManyToOne` → `intervention` (via `intervention_id`)
- `ManyToOne` → `piece` (via `piece_id`)

**Contraintes :**
- `ON DELETE CASCADE` : Si une intervention est supprimée, ses lignes sont supprimées
- `ON DELETE CASCADE` : Si une pièce est supprimée, les lignes associées sont supprimées

---

### 11. `demande_conge`

Table des demandes de congé (numérotation conservée après insertion de `intervention_ligne`).

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | Identifiant unique |
| `utilisateur_id` | INT | FOREIGN KEY, NOT NULL | Référence vers `user.id` |
| `date_debut` | DATE | NOT NULL | Date de début |
| `date_fin` | DATE | NOT NULL | Date de fin |
| `type_conge` | VARCHAR(255) | NOT NULL | Type (Enum: TypeConge) |
| `statut` | VARCHAR(255) | NOT NULL | Statut (Enum: StatutDemandeConge) |
| `date_demande` | DATETIME | NOT NULL | Date de la demande |
| `commentaire` | TEXT | NULL | Commentaire |

**Relations :**
- `ManyToOne` → `user` (via `utilisateur_id`)

**Contraintes :**
- `ON DELETE CASCADE` : Si un utilisateur est supprimé, ses demandes de congé sont supprimées

---

### 11. `user_device`

Table des appareils de confiance pour la vérification d'appareil.

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | Identifiant unique |
| `user_id` | INT | FOREIGN KEY, NOT NULL | Référence vers `user.id` |
| `device_id` | VARCHAR(36) | NOT NULL | UUID de l'appareil (cookie) |
| `device_name` | VARCHAR(255) | NULL | Nom/description de l'appareil |
| `created_at` | DATETIME | NOT NULL | Date de création |
| `last_used_at` | DATETIME | NOT NULL | Date de dernière utilisation |
| `expires_at` | DATETIME | NULL | Date d'expiration (NULL = permanent) |
| `ip_address` | VARCHAR(45) | NULL | Adresse IP lors de la validation |
| `user_agent` | TEXT | NULL | User-Agent lors de la validation |

**Relations :**
- `ManyToOne` → `user` (via `user_id`)

**Index :**
- `uniq_user_device` : Unique sur (`user_id`, `device_id`)
- `idx_device_id` : Index sur `device_id`

**Contraintes :**
- `ON DELETE CASCADE` : Si un utilisateur est supprimé, ses appareils sont supprimés

**Note :** Un utilisateur peut avoir plusieurs appareils de confiance. Si `expires_at` est NULL, l'appareil est de confiance permanente.

---

### 12. `login_challenge`

Table des défis OTP pour la validation d'appareil.

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | Identifiant unique |
| `user_id` | INT | FOREIGN KEY, NOT NULL | Référence vers `user.id` |
| `device_id` | VARCHAR(36) | NOT NULL | UUID de l'appareil à valider |
| `otp_hash` | VARCHAR(255) | NOT NULL | Hash du code OTP (BCRYPT) |
| `created_at` | DATETIME | NOT NULL | Date de création |
| `expires_at` | DATETIME | NOT NULL | Date d'expiration (10 minutes) |
| `attempts` | INT | NOT NULL, DEFAULT 0 | Nombre de tentatives (max 5) |

**Relations :**
- `ManyToOne` → `user` (via `user_id`)

**Index :**
- `idx_user_device` : Index sur (`user_id`, `device_id`)
- `idx_expires_at` : Index sur `expires_at`

**Contraintes :**
- `ON DELETE CASCADE` : Si un utilisateur est supprimé, ses challenges sont supprimés

**Note :** 
- Le code OTP n'est **jamais** stocké en clair, uniquement le hash.
- Un challenge expire après 10 minutes.
- Maximum 5 tentatives par challenge.
- Les challenges expirés doivent être nettoyés périodiquement.

---

### 13. `contrat`

Table des contrats clients.

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | Identifiant unique |
| `client_id` | INT | FOREIGN KEY, NOT NULL | Référence vers `client.id` |
| `reference` | VARCHAR(100) | NOT NULL | Référence du contrat |
| `type_contrat` | VARCHAR(255) | NOT NULL | Type (Enum: TypeContrat) |
| `date_debut` | DATE | NOT NULL | Date de début du contrat |
| `date_fin` | DATE | NULL | Date de fin du contrat (NULL = en cours) |
| `statut` | VARCHAR(255) | NOT NULL, DEFAULT 'BROUILLON' | Statut (Enum: StatutContrat) |
| `notes` | TEXT | NULL | Notes diverses |
| `created_at` | DATETIME | NOT NULL | Date de création |
| `updated_at` | DATETIME | NOT NULL | Date de mise à jour |

**Relations :**
- `ManyToOne` → `client` (via `client_id`)
- `OneToMany` → `contrat_ligne` (via `contrat_id`)

**Contraintes :**
- `ON DELETE CASCADE` : Si un client est supprimé, ses contrats sont supprimés

---

### 14. `contrat_ligne`

Table des lignes de contrat (une ligne par site).

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | Identifiant unique |
| `contrat_id` | INT | FOREIGN KEY, NOT NULL | Référence vers `contrat.id` |
| `site_id` | INT | FOREIGN KEY, NOT NULL | Référence vers `site.id` |
| `libelle` | VARCHAR(255) | NOT NULL | Libellé de la ligne |
| `periodicite` | VARCHAR(255) | NOT NULL | Périodicité (Enum: Periodicite) |
| `prochaine_facturation` | DATE | NOT NULL | Date de prochaine facturation |
| `prix_fixe` | DECIMAL(10,2) | NULL | Prix fixe (abonnement) |
| `prix_page_noir` | DECIMAL(10,4) | NULL | Prix par page noire |
| `prix_page_couleur` | DECIMAL(10,4) | NULL | Prix par page couleur |
| `pages_incluses_noir` | INT | NULL | Nombre de pages noires incluses |
| `pages_incluses_couleur` | INT | NULL | Nombre de pages couleur incluses |
| `actif` | BOOLEAN | NOT NULL, DEFAULT TRUE | Ligne active ou non |
| `created_at` | DATETIME | NOT NULL | Date de création |
| `updated_at` | DATETIME | NOT NULL | Date de mise à jour |

**Relations :**
- `ManyToOne` → `contrat` (via `contrat_id`)
- `ManyToOne` → `site` (via `site_id`)
- `OneToMany` → `affectation_materiel` (via `contrat_ligne_id`)
- `OneToMany` → `facturation_periode` (via `contrat_ligne_id`)

**Index :**
- `idx_prochaine_facturation` : Index sur `prochaine_facturation`

**Contraintes :**
- `ON DELETE CASCADE` : Si un contrat est supprimé, ses lignes sont supprimées
- `ON DELETE CASCADE` : Si un site est supprimé, les lignes de contrat associées sont supprimées

---

### 15. `affectation_materiel`

Table des affectations d'imprimantes aux lignes de contrat.

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | Identifiant unique |
| `contrat_ligne_id` | INT | FOREIGN KEY, NOT NULL | Référence vers `contrat_ligne.id` |
| `imprimante_id` | INT | FOREIGN KEY, NOT NULL | Référence vers `imprimante.id` |
| `date_debut` | DATETIME | NOT NULL | Date de début de l'affectation |
| `date_fin` | DATETIME | NULL | Date de fin de l'affectation (NULL = active) |
| `type_affectation` | VARCHAR(255) | NOT NULL, DEFAULT 'PRINCIPALE' | Type (Enum: TypeAffectation) |
| `reason` | TEXT | NULL | Raison du changement |
| `created_at` | DATETIME | NOT NULL | Date de création |
| `updated_at` | DATETIME | NOT NULL | Date de mise à jour |

**Relations :**
- `ManyToOne` → `contrat_ligne` (via `contrat_ligne_id`)
- `ManyToOne` → `imprimante` (via `imprimante_id`)
- `OneToMany` → `facturation_compteur` (via `affectation_materiel_id`)

**Index :**
- `idx_contrat_ligne` : Index sur `contrat_ligne_id`
- `idx_imprimante` : Index sur `imprimante_id`

**Contraintes :**
- `ON DELETE CASCADE` : Si une ligne de contrat est supprimée, ses affectations sont supprimées
- `ON DELETE CASCADE` : Si une imprimante est supprimée, ses affectations sont supprimées

**Note :** 
- Une seule affectation active (`date_fin` NULL) par `contrat_ligne_id` à la fois.
- Lors d'un changement de machine, l'affectation précédente est automatiquement clôturée.

---

### 16. `facturation_periode`

Table des périodes de facturation pour les lignes de contrat.

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | Identifiant unique |
| `contrat_ligne_id` | INT | FOREIGN KEY, NOT NULL | Référence vers `contrat_ligne.id` |
| `date_debut` | DATE | NOT NULL | Date de début de la période |
| `date_fin` | DATE | NOT NULL | Date de fin de la période |
| `statut` | VARCHAR(255) | NOT NULL, DEFAULT 'BROUILLON' | Statut (Enum: StatutFacturation) |
| `created_at` | DATETIME | NOT NULL | Date de création |

**Relations :**
- `ManyToOne` → `contrat_ligne` (via `contrat_ligne_id`)
- `OneToMany` → `facturation_compteur` (via `facturation_periode_id`)

**Index :**
- `idx_contrat_ligne` : Index sur `contrat_ligne_id`
- `idx_statut` : Index sur `statut`

**Contraintes :**
- `ON DELETE CASCADE` : Si une ligne de contrat est supprimée, ses périodes sont supprimées

**Note :** Les périodes sont générées automatiquement selon la périodicité de la ligne de contrat.

---

### 17. `facturation_compteur`

Table des compteurs de début et fin pour chaque affectation dans une période de facturation.

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | Identifiant unique |
| `facturation_periode_id` | INT | FOREIGN KEY, NOT NULL | Référence vers `facturation_periode.id` |
| `affectation_materiel_id` | INT | FOREIGN KEY, NOT NULL | Référence vers `affectation_materiel.id` |
| `compteur_debut_noir` | INT | NOT NULL | Compteur noir au début de la période |
| `compteur_fin_noir` | INT | NOT NULL | Compteur noir à la fin de la période |
| `compteur_debut_couleur` | INT | NULL | Compteur couleur au début de la période |
| `compteur_fin_couleur` | INT | NULL | Compteur couleur à la fin de la période |
| `source_debut` | VARCHAR(255) | NOT NULL | Source du compteur début (Enum: SourceCompteur) |
| `source_fin` | VARCHAR(255) | NOT NULL | Source du compteur fin (Enum: SourceCompteur) |

**Relations :**
- `ManyToOne` → `facturation_periode` (via `facturation_periode_id`)
- `ManyToOne` → `affectation_materiel` (via `affectation_materiel_id`)

**Index :**
- `idx_facturation_periode` : Index sur `facturation_periode_id`
- `idx_affectation_materiel` : Index sur `affectation_materiel_id`

**Contraintes :**
- `ON DELETE CASCADE` : Si une période est supprimée, ses compteurs sont supprimés
- `ON DELETE CASCADE` : Si une affectation est supprimée, ses compteurs sont supprimés

**Note :** 
- Les compteurs sont résolus automatiquement depuis `releve_compteur` lors de la création d'une période.
- Le nombre de pages consommées est calculé : `compteur_fin - compteur_debut`.

---

### 18. `stock_location`

Table des emplacements de stock (ateliers, dépôts, stocks clients).

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | Identifiant unique |
| `site_id` | INT | FOREIGN KEY, NOT NULL | Référence vers `site.id` |
| `type` | VARCHAR(255) | NOT NULL | Type (Enum: StockLocationType) |
| `nom_stock` | VARCHAR(255) | NOT NULL | Nom de l'emplacement de stock |
| `actif` | BOOLEAN | NOT NULL, DEFAULT TRUE | Emplacement actif ou non |
| `created_at` | DATETIME | NOT NULL | Date de création |
| `updated_at` | DATETIME | NOT NULL | Date de mise à jour |

**Relations :**
- `ManyToOne` → `site` (via `site_id`)
- `OneToMany` → `stock_item` (via `stock_location_id`)

**Index :**
- `idx_type` : Index sur `type`
- `uniq_site_nom_stock` : Unique sur (`site_id`, `nom_stock`)

**Note :** Plusieurs stocks peuvent exister sur un même site (ex: "Atelier principal" et "Dépôt secondaire").

---

### 19. `piece`

Table du catalogue des pièces consommables (toners, bacs, tambours, etc.).

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | Identifiant unique |
| `reference` | VARCHAR(150) | UNIQUE, NOT NULL | Référence de la pièce |
| `designation` | VARCHAR(255) | NOT NULL | Désignation de la pièce |
| `type_piece` | VARCHAR(255) | NOT NULL | Type (Enum: PieceType) |
| `couleur` | VARCHAR(10) | NULL | Couleur (K, C, M, Y pour les toners) |
| `actif` | BOOLEAN | NOT NULL, DEFAULT TRUE | Pièce active ou non |
| `notes` | TEXT | NULL | Notes diverses |
| `created_at` | DATETIME | NOT NULL | Date de création |
| `updated_at` | DATETIME | NOT NULL | Date de mise à jour |

**Relations :**
- `OneToMany` → `stock_item` (via `piece_id`)
- `OneToMany` → `piece_modele` (via `piece_id`)

**Index :**
- `uniq_piece_reference` : Unique sur `reference`

---

### 20. `stock_item`

Table des quantités de pièces dans chaque emplacement de stock.

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | Identifiant unique |
| `stock_location_id` | INT | FOREIGN KEY, NOT NULL | Référence vers `stock_location.id` |
| `piece_id` | INT | FOREIGN KEY, NOT NULL | Référence vers `piece.id` |
| `quantite` | INT | NOT NULL, DEFAULT 0 | Quantité en stock |
| `seuil_alerte` | INT | NULL | Seuil d'alerte (quantité minimale) |
| `updated_at` | DATETIME | NOT NULL | Date de mise à jour |

**Relations :**
- `ManyToOne` → `stock_location` (via `stock_location_id`)
- `ManyToOne` → `piece` (via `piece_id`)

**Index :**
- `idx_stock_location_id` : Index sur `stock_location_id`
- `idx_piece_id` : Index sur `piece_id`
- `uniq_stock_piece` : Unique sur (`stock_location_id`, `piece_id`)

**Contraintes :**
- `ON DELETE CASCADE` : Si un emplacement de stock est supprimé, ses items sont supprimés
- `ON DELETE CASCADE` : Si une pièce est supprimée, ses items de stock sont supprimés

**Note :** Une seule entrée par combinaison (`stock_location_id`, `piece_id`).

---

### 21. `piece_modele`

Table de compatibilité entre les pièces et les modèles d'imprimantes (table pivot avec rôle).

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | Identifiant unique |
| `piece_id` | INT | FOREIGN KEY, NOT NULL | Référence vers `piece.id` |
| `modele_id` | INT | FOREIGN KEY, NOT NULL | Référence vers `modele.id` |
| `role` | VARCHAR(255) | NOT NULL | Rôle de la pièce (Enum: PieceRoleModele) |
| `notes` | TEXT | NULL | Notes diverses |

**Relations :**
- `ManyToOne` → `piece` (via `piece_id`)
- `ManyToOne` → `modele` (via `modele_id`)

**Index :**
- `idx_modele_id` : Index sur `modele_id`
- `uniq_piece_modele_role` : Unique sur (`piece_id`, `modele_id`, `role`)

**Contraintes :**
- `ON DELETE CASCADE` : Si une pièce est supprimée, ses compatibilités sont supprimées
- `ON DELETE CASCADE` : Si un modèle est supprimé, ses compatibilités sont supprimées

**Note :** Le rôle indique la fonction de la pièce pour ce modèle (ex: TONER_K pour le toner noir).

---

## Enums

### StatutImprimante
- `actif`, `pret`, `assurance`, `hs`, `decheterie`

### StatutIntervention
- `ouverte`, `en_cours`, `terminee`, `annulee`

### TypeIntervention
- `sur_site`, `distance`, `atelier`, `livraison_toner`

### StatutDemandeConge
- `demandee`, `acceptee`, `refusee`, `annulee`

### TypeConge
- `paye`, `sans_solde`, `maladie`

### TypeContrat
- `MAINTENANCE`
- `LOCATION`
- `VENTE`
- `PRET`

### StatutContrat
- `BROUILLON`
- `ACTIF`
- `SUSPENDU`
- `RESILIE`
- `TERMINE`

### Periodicite
- `MENSUEL`
- `TRIMESTRIEL`
- `SEMESTRIEL`
- `ANNUEL`

### TypeAffectation
- `PRINCIPALE`
- `REMPLACEMENT_TEMP`
- `REMPLACEMENT_DEF`
- `PRET`

### StatutFacturation
- `BROUILLON`
- `VALIDE`
- `FACTURE`

### SourceCompteur
- `MANUEL`
- `SNMP`
- `SCAN`

### StockLocationType
- `ENTREPRISE`
- `CLIENT`

### PieceType
- `TONER`
- `BAC_RECUP`
- `DRUM`
- `FUSER`
- `MAINTENANCE_KIT`
- `AUTRE`

### PieceRoleModele
- `TONER_K` (Toner Noir)
- `TONER_C` (Toner Cyan)
- `TONER_M` (Toner Magenta)
- `TONER_Y` (Toner Jaune)
- `BAC_RECUP` (Bac de récupération)
- `DRUM` (Tambour)
- `FUSER` (Unité de fusion)
- `AUTRE` (Autre pièce)

---

## Relations entre tables

```
client (1) ──< (N) site (1) ──< (N) imprimante (1) ──< (N) intervention (1) ──< (N) intervention_ligne
      │              │                    │              └──< (N) releve_compteur
      │              │                    │              └──< (N) etat_consommable
      │              │                    │              └──< (N) affectation_materiel
      │              └──< (N) stock_location (1) ──< (N) stock_item
      │
      └──< (N) contrat (1) ──< (N) contrat_ligne (1) ──< (N) affectation_materiel
                                                      └──< (N) facturation_periode (1) ──< (N) facturation_compteur

fabricant (1) ──< (N) modele (1) ──< (N) imprimante
                              └──< (N) piece_modele

piece (1) ──< (N) stock_item
      └──< (N) piece_modele
      └──< (N) intervention_ligne

user (1) ──< (N) intervention
      └──< (N) demande_conge
      └──< (N) user_device
      └──< (N) login_challenge
```

---

## Notes importantes

1. **Import CSV** : Les relevés et états consommables sont identifiés de manière unique par (`imprimante_id`, `date_releve`/`date_capture`) pour éviter les doublons lors de l'import.

2. **Sécurité** : 
   - Les mots de passe sont hashés (algorithme configuré dans `security.yaml`)
   - Les codes OTP sont hashés avec BCRYPT (jamais stockés en clair)
   - Les cookies `device_id` sont sécurisés (HttpOnly, Secure, SameSite=Lax)

3. **Nettoyage** : 
   - Les challenges expirés doivent être nettoyés périodiquement
   - Les appareils expirés peuvent être supprimés automatiquement

4. **Cascade** : La plupart des relations utilisent `ON DELETE CASCADE` pour maintenir l'intégrité référentielle.

5. **Contrats et Facturation** :
   - Les périodes de facturation sont générées automatiquement selon la périodicité des lignes de contrat.
   - Les compteurs de facturation sont résolus depuis `releve_compteur` lors de la création d'une période.
   - Une seule affectation active (`date_fin` NULL) par ligne de contrat à la fois.
   - Lors d'un changement de machine, l'affectation précédente est automatiquement clôturée.

6. **Gestion des stocks** :
   - Les emplacements de stock peuvent être de type ENTREPRISE (atelier, dépôt) ou CLIENT (sur site client).
   - Plusieurs stocks peuvent exister sur un même site (contrainte unique sur `site_id` + `nom_stock`).
   - Les pièces sont liées aux modèles via `piece_modele` avec un rôle spécifique (TONER_K, TONER_C, etc.).
   - Les seuils d'alerte permettent de surveiller les stocks faibles.

---

## Commandes utiles

### Générer une migration
```bash
php bin/console make:migration
```

### Appliquer les migrations
```bash
php bin/console doctrine:migrations:migrate
```

### Voir le schéma actuel
```bash
php bin/console doctrine:schema:validate
```

### Générer le schéma SQL
```bash
php bin/console doctrine:schema:update --dump-sql
```

---

**⚠️ Rappel : Mettre à jour ce document à chaque modification de la structure de la base de données !**
