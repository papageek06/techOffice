-- Création manuelle des tables inbound_alert (à lancer sur le serveur si la migration a échoué en post-check)
-- Base : profeszoffice

CREATE TABLE IF NOT EXISTS inbound_alert (
    id INT AUTO_INCREMENT NOT NULL,
    message_id VARCHAR(255) DEFAULT NULL,
    subject VARCHAR(500) DEFAULT NULL,
    from_email VARCHAR(255) DEFAULT NULL,
    received_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
    severity VARCHAR(20) DEFAULT NULL,
    body LONGTEXT DEFAULT NULL,
    status VARCHAR(30) NOT NULL,
    error_message LONGTEXT DEFAULT NULL,
    created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
    processed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
    UNIQUE INDEX uniq_inbound_alert_message_id (message_id),
    INDEX idx_inbound_alert_status (status),
    INDEX idx_inbound_alert_received_at (received_at),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS inbound_alert_attachment (
    id INT AUTO_INCREMENT NOT NULL,
    inbound_alert_id INT NOT NULL,
    original_name VARCHAR(500) NOT NULL,
    mime_type VARCHAR(100) DEFAULT NULL,
    size INT DEFAULT NULL,
    sha256 VARCHAR(64) NOT NULL,
    stored_path VARCHAR(500) NOT NULL,
    stored_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
    UNIQUE INDEX uniq_inbound_alert_attachment_sha256 (sha256),
    INDEX idx_inbound_alert_attachment_alert (inbound_alert_id),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;

-- Clé étrangère (ignorer l'erreur si elle existe déjà)
ALTER TABLE inbound_alert_attachment ADD CONSTRAINT FK_inbound_alert_attachment_alert FOREIGN KEY (inbound_alert_id) REFERENCES inbound_alert (id) ON DELETE CASCADE;
