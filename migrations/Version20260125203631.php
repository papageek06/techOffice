<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260125203631 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE piece (id INT AUTO_INCREMENT NOT NULL, reference VARCHAR(150) NOT NULL, designation VARCHAR(255) NOT NULL, type_piece VARCHAR(255) NOT NULL, couleur VARCHAR(10) DEFAULT NULL, actif TINYINT DEFAULT 1 NOT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX uniq_piece_reference (reference), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE piece_modele (id INT AUTO_INCREMENT NOT NULL, role VARCHAR(255) NOT NULL, notes LONGTEXT DEFAULT NULL, piece_id INT NOT NULL, modele_id INT NOT NULL, INDEX IDX_CA708E09C40FCFA8 (piece_id), INDEX idx_modele_id (modele_id), UNIQUE INDEX uniq_piece_modele_role (piece_id, modele_id, role), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE stock_item (id INT AUTO_INCREMENT NOT NULL, quantite INT DEFAULT 0 NOT NULL, seuil_alerte INT DEFAULT NULL, updated_at DATETIME NOT NULL, stock_location_id INT NOT NULL, piece_id INT NOT NULL, INDEX idx_piece_id (piece_id), INDEX idx_stock_location_id (stock_location_id), UNIQUE INDEX uniq_stock_piece (stock_location_id, piece_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE stock_location (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, nom_stock VARCHAR(255) NOT NULL, actif TINYINT DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, site_id INT NOT NULL, INDEX IDX_1158DD89F6BD1646 (site_id), INDEX idx_type (type), UNIQUE INDEX uniq_site_nom_stock (site_id, nom_stock), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE piece_modele ADD CONSTRAINT FK_CA708E09C40FCFA8 FOREIGN KEY (piece_id) REFERENCES piece (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE piece_modele ADD CONSTRAINT FK_CA708E09AC14B70A FOREIGN KEY (modele_id) REFERENCES modele (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE stock_item ADD CONSTRAINT FK_6017DDAD98387BA FOREIGN KEY (stock_location_id) REFERENCES stock_location (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE stock_item ADD CONSTRAINT FK_6017DDAC40FCFA8 FOREIGN KEY (piece_id) REFERENCES piece (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE stock_location ADD CONSTRAINT FK_1158DD89F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE piece_modele DROP FOREIGN KEY FK_CA708E09C40FCFA8');
        $this->addSql('ALTER TABLE piece_modele DROP FOREIGN KEY FK_CA708E09AC14B70A');
        $this->addSql('ALTER TABLE stock_item DROP FOREIGN KEY FK_6017DDAD98387BA');
        $this->addSql('ALTER TABLE stock_item DROP FOREIGN KEY FK_6017DDAC40FCFA8');
        $this->addSql('ALTER TABLE stock_location DROP FOREIGN KEY FK_1158DD89F6BD1646');
        $this->addSql('DROP TABLE piece');
        $this->addSql('DROP TABLE piece_modele');
        $this->addSql('DROP TABLE stock_item');
        $this->addSql('DROP TABLE stock_location');
    }
}
