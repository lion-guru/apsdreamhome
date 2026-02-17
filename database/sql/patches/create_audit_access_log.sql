CREATE TABLE IF NOT EXISTS audit_access_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_user VARCHAR(128) NOT NULL,
    action VARCHAR(32) NOT NULL,
    details TEXT,
    ip_address VARCHAR(64),
    accessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
