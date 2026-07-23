<?php
require_once 'config/bootstrap.php';
require_once 'app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

// DigiLocker integration table
$pdo->exec("
CREATE TABLE IF NOT EXISTS digilocker_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED DEFAULT NULL,
    session_id VARCHAR(100) NOT NULL UNIQUE,
    access_token TEXT DEFAULT NULL,
    refresh_token TEXT DEFAULT NULL,
    expires_at DATETIME DEFAULT NULL,
    state VARCHAR(100) NOT NULL,
    scopes JSON DEFAULT NULL,
    user_data JSON DEFAULT NULL,
    status ENUM('pending', 'authorized', 'expired', 'revoked') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_user (user_id),
    KEY idx_session (session_id),
    KEY idx_state (state)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "digilocker_sessions table created/verified\n";

// DigiLocker config
$pdo->exec("
CREATE TABLE IF NOT EXISTS digilocker_config (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id VARCHAR(100) NOT NULL,
    client_secret VARCHAR(255) NOT NULL,
    redirect_uri VARCHAR(500) NOT NULL,
    auth_url VARCHAR(255) DEFAULT 'https://digilocker.meripehchaan.gov.in/public/oauth2/1/authorize',
    token_url VARCHAR(255) DEFAULT 'https://digilocker.meripehchaan.gov.in/public/oauth2/1/token',
    api_base_url VARCHAR(255) DEFAULT 'https://digilocker.meripehchaan.gov.in/public/oauth2/1',
    scopes JSON DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    is_sandbox TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_client_id (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "digilocker_config table created/verified\n";

// Insert default config (sandbox)
$stmt = $pdo->prepare("
    INSERT INTO digilocker_config (client_id, client_secret, redirect_uri, scopes, is_sandbox)
    VALUES (?, ?, ?, ?, 1)
    ON DUPLICATE KEY UPDATE 
        client_secret = VALUES(client_secret),
        redirect_uri = VALUES(redirect_uri),
        scopes = VALUES(scopes),
        is_sandbox = VALUES(is_sandbox),
        updated_at = CURRENT_TIMESTAMP
");
$stmt->execute([
    'your_client_id_here',
    'your_client_secret_here',
    'http://localhost/apsdreamhome/callback/digilocker',
    json_encode(['aadhaar', 'pan', 'drivinglicense', 'vehicle_registration', 'marksheet']),
]);

echo "Default DigiLocker config inserted\n";

echo "\n=== DigiLocker tables created successfully ===\n";