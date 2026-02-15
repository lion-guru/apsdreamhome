CREATE TABLE IF NOT EXISTS upload_audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(64) NOT NULL,
    entity_id INT NOT NULL,
    entity_table VARCHAR(64) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    drive_file_id VARCHAR(128),
    uploader VARCHAR(128) NOT NULL,
    slack_status VARCHAR(32),
    telegram_status VARCHAR(32),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
