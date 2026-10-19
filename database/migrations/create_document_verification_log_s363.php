<?php
/**
 * Migration: Create Document Verification Log Table
 * Session: 364
 * Idempotent: Safe to re-run, checks before creating
 */
require_once __DIR__ . '/../../vendor/autoload.php';

try {
    $db = \App\Core\Database\Database::getInstance();

    // Document Verification Log table
    $db->execute("
        CREATE TABLE IF NOT EXISTS document_verification_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            document_id INT NOT NULL,
            action ENUM('verified', 'rejected', 'requested') NOT NULL,
            verified_by BIGINT UNSIGNED NOT NULL,
            comments TEXT,
            verified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY idx_dvl_doc (document_id),
            KEY idx_dvl_verifier (verified_by),
            KEY idx_dvl_tenant (tenant_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "document_verification_log: CREATED/EXISTS\n";

    // Add foreign key if not exists (optional, skip if fails)
    try {
        $db->execute("
            ALTER TABLE document_verification_log 
            ADD CONSTRAINT fk_dvl_document FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE
        ");
        echo "FK document_verification_log.document_id -> documents.id: ADDED\n";
    } catch (\Throwable $e) {
        echo "FK may already exist: " . $e->getMessage() . "\n";
    }

    echo "\n=== DOCUMENT VERIFICATION LOG MIGRATION COMPLETED ===\n";

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}