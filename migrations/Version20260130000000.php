<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour les entités InboundEmail et InboundAttachment
 */
final class Version20260130000000 extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return false;
    }

    public function getDescription(): string
    {
        return 'Création des tables inbound_email et inbound_attachment pour l\'import IMAP';
    }

    public function up(Schema $schema): void
    {
        // Table inbound_email
        $this->addSql('CREATE TABLE inbound_email (
            id INT AUTO_INCREMENT NOT NULL,
            message_id VARCHAR(255) NOT NULL,
            subject VARCHAR(500) DEFAULT NULL,
            from_email VARCHAR(255) DEFAULT NULL,
            received_at DATETIME DEFAULT NULL,
            processed_at DATETIME DEFAULT NULL,
            status VARCHAR(20) NOT NULL,
            error_message LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL,
            UNIQUE INDEX UNIQ_message_id (message_id),
            INDEX idx_status (status),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Table inbound_attachment
        $this->addSql('CREATE TABLE inbound_attachment (
            id INT AUTO_INCREMENT NOT NULL,
            inbound_email_id INT NOT NULL,
            filename_original VARCHAR(500) NOT NULL,
            mime_type VARCHAR(100) DEFAULT NULL,
            size INT DEFAULT NULL,
            sha256 VARCHAR(64) NOT NULL,
            stored_path VARCHAR(500) NOT NULL,
            stored_at DATETIME NOT NULL,
            UNIQUE INDEX UNIQ_sha256 (sha256),
            INDEX idx_inbound_email_id (inbound_email_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Foreign key
        $this->addSql('ALTER TABLE inbound_attachment ADD CONSTRAINT FK_inbound_email FOREIGN KEY (inbound_email_id) REFERENCES inbound_email (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE inbound_attachment DROP FOREIGN KEY FK_inbound_email');
        $this->addSql('DROP TABLE inbound_attachment');
        $this->addSql('DROP TABLE inbound_email');
    }
}
