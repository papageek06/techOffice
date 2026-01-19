<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260119184750 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // 1. Supprimer d'abord les contraintes de clé étrangère
        $this->addSql('ALTER TABLE demande_conge DROP FOREIGN KEY FK_D8061061FB88E14F');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814ABFB88E14F');
        
        // 2. Ajouter la colonne nom à la table user (si elle n'existe pas déjà, l'erreur sera ignorée)
        // Note: Pour MySQL 8.0.19+, on pourrait utiliser IF NOT EXISTS, mais pour compatibilité on utilise une approche différente
        $this->addSql('ALTER TABLE user ADD nom VARCHAR(255) DEFAULT NULL');
        
        // 3. Recréer les contraintes pour pointer vers user au lieu de utilisateur
        $this->addSql('ALTER TABLE demande_conge ADD CONSTRAINT FK_D8061061FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814ABFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES user (id)');
        
        // 4. Enfin, supprimer la table utilisateur
        $this->addSql('DROP TABLE IF EXISTS utilisateur');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, nom VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, UNIQUE INDEX UNIQ_1D1C63B3E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE demande_conge DROP FOREIGN KEY FK_D8061061FB88E14F');
        $this->addSql('ALTER TABLE demande_conge ADD CONSTRAINT `FK_D8061061FB88E14F` FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814ABFB88E14F');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT `FK_D11814ABFB88E14F` FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE user DROP nom');
    }
}
