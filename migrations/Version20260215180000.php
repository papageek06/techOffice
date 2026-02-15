<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260215180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout colonne tags (JSON) sur inbound_alert pour payload fetch.js';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE inbound_alert ADD tags JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE inbound_alert DROP tags');
    }
}
