# Schéma de la Base de Données

**⚠️ IMPORTANT : Ce document doit être tenu à jour à chaque modification de la structure de la base de données.**

**Dernière mise à jour :** 2026-01-25

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
| `suivie_par_service` | BOOLEAN | NOT NULL, DEFAULT TRUE | Suivi par le service (MANAGED) |
| `statut` | VARCHAR(255) | NOT NULL, DEFAULT 'actif' | Statut (Enum: StatutImprimante) |
| `notes` | TEXT | NULL | Notes diverses |
| `dual_scan` | BOOLEAN | NOT NULL, DEFAULT FALSE | Scanner recto-verso automatique (A) |

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
| `facturable` | BOOLEAN | NOT NULL, DEFAULT TRUE | Intervention facturable ou non |

**Relations :**
- `ManyToOne` → `imprimante` (via `imprimante_id`)
- `ManyToOne` → `user` (via `utilisateur_id`)

**Contraintes :**
- `ON DELETE CASCADE` : Si une imprimante est supprimée, ses interventions sont supprimées

---

### 10. `demande_conge`

Table des demandes de congé.

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

## Enums

### StatutImprimante
- `ACTIF`
- `INACTIF`
- `EN_PANNE`
- `EN_REPARATION`

### StatutIntervention
- `OUVERTE`
- `EN_COURS`
- `FERMEE`
- `ANNULEE`

### TypeIntervention
- `SUR_SITE`
- `A_DISTANCE`
- `PREVENTIVE`
- `CORRECTIVE`

### StatutDemandeConge
- `DEMANDEE`
- `APPROUVEE`
- `REFUSEE`
- `ANNULEE`

### TypeConge
- `PAYE`
- `SANS_SOLDE`
- `MALADIE`
- `MATERNITE`
- `PATERNITE`

---

## Relations entre tables

```
client (1) ──< (N) site (1) ──< (N) imprimante (1) ──< (N) intervention
                                                      └──< (N) releve_compteur
                                                      └──< (N) etat_consommable

fabricant (1) ──< (N) modele (1) ──< (N) imprimante

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
