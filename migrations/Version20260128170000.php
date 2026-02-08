<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260128170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Intervention: table intervention_ligne (pièces livrées) et colonne stock_applique.';
    }

    public function up(Schema $schema): void
    {
        $isMySql = $this->connection->getDatabasePlatform()->getName() === 'mysql';

        if ($isMySql) {
            $this->addSql('CREATE TABLE intervention_ligne (id INT AUTO_INCREMENT NOT NULL, intervention_id INT NOT NULL, piece_id INT NOT NULL, quantite INT NOT NULL, INDEX IDX_INTERVENTION (intervention_id), INDEX IDX_PIECE (piece_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
            $this->addSql('ALTER TABLE intervention_ligne ADD CONSTRAINT FK_INTERVENTION FOREIGN KEY (intervention_id) REFERENCES intervention (id) ON DELETE CASCADE');
            $this->addSql('ALTER TABLE intervention_ligne ADD CONSTRAINT FK_PIECE FOREIGN KEY (piece_id) REFERENCES piece (id) ON DELETE CASCADE');
            $this->addSql('ALTER TABLE intervention ADD stock_applique TINYINT(1) DEFAULT 0 NOT NULL');
        } else {
            $this->addSql('CREATE TABLE intervention_ligne (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, intervention_id INTEGER NOT NULL, piece_id INTEGER NOT NULL, quantite INTEGER NOT NULL, CONSTRAINT FK_INTERVENTION FOREIGN KEY (intervention_id) REFERENCES intervention (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_PIECE FOREIGN KEY (piece_id) REFERENCES piece (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('CREATE INDEX IDX_INTERVENTION ON intervention_ligne (intervention_id)');
            $this->addSql('CREATE INDEX IDX_PIECE ON intervention_ligne (piece_id)');
            $this->addSql('ALTER TABLE intervention ADD stock_applique BOOLEAN DEFAULT 0 NOT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE intervention_ligne');
        $this->addSql('ALTER TABLE intervention DROP COLUMN stock_applique');
    }
}
