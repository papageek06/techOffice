<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260119183253 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, actif TINYINT DEFAULT 1 NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE demande_conge (id INT AUTO_INCREMENT NOT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, type_conge VARCHAR(255) NOT NULL, statut VARCHAR(255) NOT NULL, date_demande DATETIME NOT NULL, commentaire LONGTEXT DEFAULT NULL, utilisateur_id INT NOT NULL, INDEX IDX_D8061061FB88E14F (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE etat_consommable (id INT AUTO_INCREMENT NOT NULL, date_capture DATETIME NOT NULL, noir_pourcent INT DEFAULT NULL, cyan_pourcent INT DEFAULT NULL, magenta_pourcent INT DEFAULT NULL, jaune_pourcent INT DEFAULT NULL, bac_recuperation INT DEFAULT NULL, imprimante_id INT NOT NULL, INDEX IDX_CD38D79A1CA0A76 (imprimante_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE fabricant (id INT AUTO_INCREMENT NOT NULL, nom_fabricant VARCHAR(150) NOT NULL, UNIQUE INDEX UNIQ_D740A26943B1D328 (nom_fabricant), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE imprimante (id INT AUTO_INCREMENT NOT NULL, numero_serie VARCHAR(80) NOT NULL, date_installation DATE DEFAULT NULL, adresse_ip VARCHAR(45) DEFAULT NULL, emplacement VARCHAR(255) DEFAULT NULL, suivie_par_service TINYINT DEFAULT 1 NOT NULL, statut VARCHAR(255) DEFAULT \'actif\' NOT NULL, notes LONGTEXT DEFAULT NULL, site_id INT NOT NULL, modele_id INT NOT NULL, INDEX IDX_4DF2C3AAF6BD1646 (site_id), INDEX IDX_4DF2C3AAAC14B70A (modele_id), UNIQUE INDEX uniq_imprimante_numero_serie (numero_serie), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE intervention (id INT AUTO_INCREMENT NOT NULL, date_creation DATETIME NOT NULL, date_intervention DATETIME DEFAULT NULL, type_intervention VARCHAR(255) NOT NULL, statut VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, temps_facturable_minutes INT NOT NULL, temps_reel_minutes INT DEFAULT NULL, facturable TINYINT DEFAULT 1 NOT NULL, imprimante_id INT NOT NULL, utilisateur_id INT NOT NULL, INDEX IDX_D11814AB1CA0A76 (imprimante_id), INDEX IDX_D11814ABFB88E14F (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE modele (id INT AUTO_INCREMENT NOT NULL, reference_modele VARCHAR(150) NOT NULL, couleur TINYINT DEFAULT 0 NOT NULL, fabricant_id INT NOT NULL, INDEX IDX_10028558CBAAAAB3 (fabricant_id), UNIQUE INDEX uniq_modele_fabricant_ref (fabricant_id, reference_modele), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE releve_compteur (id INT AUTO_INCREMENT NOT NULL, date_releve DATETIME NOT NULL, compteur_noir INT DEFAULT NULL, compteur_couleur INT DEFAULT NULL, source VARCHAR(30) DEFAULT \'manuel\' NOT NULL, imprimante_id INT NOT NULL, INDEX IDX_F15DECBC1CA0A76 (imprimante_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE site (id INT AUTO_INCREMENT NOT NULL, nom_site VARCHAR(255) NOT NULL, principal TINYINT DEFAULT 0 NOT NULL, actif TINYINT DEFAULT 1 NOT NULL, client_id INT NOT NULL, INDEX IDX_694309E419EB6921 (client_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, nom VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_1D1C63B3E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE demande_conge ADD CONSTRAINT FK_D8061061FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE etat_consommable ADD CONSTRAINT FK_CD38D79A1CA0A76 FOREIGN KEY (imprimante_id) REFERENCES imprimante (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE imprimante ADD CONSTRAINT FK_4DF2C3AAF6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
        $this->addSql('ALTER TABLE imprimante ADD CONSTRAINT FK_4DF2C3AAAC14B70A FOREIGN KEY (modele_id) REFERENCES modele (id)');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814AB1CA0A76 FOREIGN KEY (imprimante_id) REFERENCES imprimante (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814ABFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE modele ADD CONSTRAINT FK_10028558CBAAAAB3 FOREIGN KEY (fabricant_id) REFERENCES fabricant (id)');
        $this->addSql('ALTER TABLE releve_compteur ADD CONSTRAINT FK_F15DECBC1CA0A76 FOREIGN KEY (imprimante_id) REFERENCES imprimante (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE site ADD CONSTRAINT FK_694309E419EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE demande_conge DROP FOREIGN KEY FK_D8061061FB88E14F');
        $this->addSql('ALTER TABLE etat_consommable DROP FOREIGN KEY FK_CD38D79A1CA0A76');
        $this->addSql('ALTER TABLE imprimante DROP FOREIGN KEY FK_4DF2C3AAF6BD1646');
        $this->addSql('ALTER TABLE imprimante DROP FOREIGN KEY FK_4DF2C3AAAC14B70A');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814AB1CA0A76');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814ABFB88E14F');
        $this->addSql('ALTER TABLE modele DROP FOREIGN KEY FK_10028558CBAAAAB3');
        $this->addSql('ALTER TABLE releve_compteur DROP FOREIGN KEY FK_F15DECBC1CA0A76');
        $this->addSql('ALTER TABLE site DROP FOREIGN KEY FK_694309E419EB6921');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE demande_conge');
        $this->addSql('DROP TABLE etat_consommable');
        $this->addSql('DROP TABLE fabricant');
        $this->addSql('DROP TABLE imprimante');
        $this->addSql('DROP TABLE intervention');
        $this->addSql('DROP TABLE modele');
        $this->addSql('DROP TABLE releve_compteur');
        $this->addSql('DROP TABLE site');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
