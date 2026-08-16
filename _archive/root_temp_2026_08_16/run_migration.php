<?php
require_once 'vendor/autoload.php';
require_once 'app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance()->getConnection();

// Read the migration file
$sql = file_get_contents('database/migrations/2026_08_15_create_legal_documents_table.php');

// Extract the up() method content
if (preg_match('/public function up\(\): void\s*\{(.*?)\}\s*public function down/s', $sql, $matches)) {
    $upMethod = $matches[1];
    // Extract SQL statements
    if (preg_match_all('/Schema::create\([^)]+\);/s', $upMethod, $sqlMatches)) {
        foreach ($sqlMatches[0] as $statement) {
            try {
                // This won't work directly - Schema is a Laravel class
                echo "Found Schema::create statement\n";
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage() . "\n";
            }
        }
    }
} else {
    echo "Could not parse migration file\n";
}

// Let's just run the SQL directly
$sql = "
CREATE TABLE IF NOT EXISTS legal_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(255) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(50) NOT NULL,
    document_type VARCHAR(50) NOT NULL,
    content LONGTEXT NOT NULL,
    summary TEXT NULL,
    version VARCHAR(20) DEFAULT '1.0',
    status ENUM('draft','published','archived') DEFAULT 'draft',
    is_mandatory BOOLEAN DEFAULT FALSE,
    applies_to_roles JSON NULL,
    metadata JSON NULL,
    created_by INT NULL,
    updated_by INT NULL,
    published_at TIMESTAMP NULL,
    effective_from TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_slug (slug),
    INDEX idx_category (category),
    INDEX idx_document_type (document_type),
    INDEX idx_status (status),
    INDEX idx_published_at (published_at),
    INDEX idx_effective_from (effective_from),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS legal_document_versions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    legal_document_id INT NOT NULL,
    version VARCHAR(20) NOT NULL,
    content LONGTEXT NOT NULL,
    change_summary TEXT NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_document_version (legal_document_id, version),
    FOREIGN KEY (legal_document_id) REFERENCES legal_documents(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS legal_document_acceptances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    legal_document_id INT NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    user_type VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    version VARCHAR(20) NULL,
    accepted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_document_user_acceptance (legal_document_id, user_id, user_type),
    INDEX idx_user (user_id, user_type),
    FOREIGN KEY (legal_document_id) REFERENCES legal_documents(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

try {
    foreach (explode(';', $sql) as $stmt) {
        $stmt = trim($stmt);
        if ($stmt) {
            $db->exec($stmt);
            echo "Executed: " . substr($stmt, 0, 50) . "...\n";
        }
    }
    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}