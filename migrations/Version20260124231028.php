<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260124231028 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE login_challenge (id INT AUTO_INCREMENT NOT NULL, device_id VARCHAR(36) NOT NULL, otp_hash VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, attempts INT DEFAULT 0 NOT NULL, user_id INT NOT NULL, INDEX IDX_B797A3A4A76ED395 (user_id), INDEX idx_user_device (user_id, device_id), INDEX idx_expires_at (expires_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_device (id INT AUTO_INCREMENT NOT NULL, device_id VARCHAR(36) NOT NULL, device_name VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, last_used_at DATETIME NOT NULL, expires_at DATETIME DEFAULT NULL, ip_address VARCHAR(45) DEFAULT NULL, user_agent LONGTEXT DEFAULT NULL, user_id INT NOT NULL, INDEX IDX_6C7DADB3A76ED395 (user_id), INDEX idx_device_id (device_id), UNIQUE INDEX uniq_user_device (user_id, device_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE login_challenge ADD CONSTRAINT FK_B797A3A4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_device ADD CONSTRAINT FK_6C7DADB3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD phone_number VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE login_challenge DROP FOREIGN KEY FK_B797A3A4A76ED395');
        $this->addSql('ALTER TABLE user_device DROP FOREIGN KEY FK_6C7DADB3A76ED395');
        $this->addSql('DROP TABLE login_challenge');
        $this->addSql('DROP TABLE user_device');
        $this->addSql('ALTER TABLE user DROP phone_number');
    }
}
