<?php
require_once 'vendor/autoload.php';
require_once 'app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance()->getConnection();

// Add missing columns to legal_documents table
$alterStatements = [
    "ALTER TABLE legal_documents ADD COLUMN IF NOT EXISTS slug VARCHAR(255) NOT NULL UNIQUE AFTER title",
    "ALTER TABLE legal_documents ADD COLUMN IF NOT EXISTS document_type VARCHAR(50) NOT NULL DEFAULT 'terms' AFTER category",
    "ALTER TABLE legal_documents ADD COLUMN IF NOT EXISTS summary TEXT NULL AFTER content",
    "ALTER TABLE legal_documents ADD COLUMN IF NOT EXISTS version VARCHAR(20) DEFAULT '1.0' AFTER document_type",
    "ALTER TABLE legal_documents ADD COLUMN IF NOT EXISTS is_mandatory BOOLEAN DEFAULT FALSE AFTER version",
    "ALTER TABLE legal_documents ADD COLUMN IF NOT EXISTS applies_to_roles JSON NULL AFTER is_mandatory",
    "ALTER TABLE legal_documents ADD COLUMN IF NOT EXISTS metadata JSON NULL AFTER applies_to_roles",
    "ALTER TABLE legal_documents ADD COLUMN IF NOT EXISTS published_at TIMESTAMP NULL AFTER metadata",
    "ALTER TABLE legal_documents ADD COLUMN IF NOT EXISTS effective_from TIMESTAMP NULL AFTER published_at",
    "ALTER TABLE legal_documents ADD COLUMN IF NOT EXISTS expires_at TIMESTAMP NULL AFTER effective_from",
    "ALTER TABLE legal_documents ADD COLUMN IF NOT EXISTS created_by INT NULL AFTER expires_at",
    "ALTER TABLE legal_documents ADD COLUMN IF NOT EXISTS updated_by INT NULL AFTER created_by",
    "ALTER TABLE legal_documents ADD COLUMN IF NOT EXISTS published_at TIMESTAMP NULL AFTER status",
    "ALTER TABLE legal_documents ADD COLUMN IF NOT EXISTS effective_from TIMESTAMP NULL AFTER published_at",
    "ALTER TABLE legal_documents ADD COLUMN IF NOT EXISTS expires_at TIMESTAMP NULL AFTER effective_from",
    "ALTER TABLE legal_documents ADD COLUMN IF NOT EXISTS slug VARCHAR(255) NOT NULL UNIQUE AFTER title",
    "ALTER TABLE legal_documents ADD COLUMN IF NOT EXISTS summary TEXT NULL AFTER content",
    "ALTER TABLE legal_documents ADD COLUMN IF NOT EXISTS version VARCHAR(20) DEFAULT '1.0' AFTER document_type",
];

foreach ($alterStatements as $stmt) {
    try {
        $db->exec($stmt);
        echo "OK: " . substr($stmt, 0, 60) . "...\n";
    } catch (Exception $e) {
        echo "SKIP (may already exist): " . substr($stmt, 0, 60) . "...\n";
        echo "  Error: " . $e->getMessage() . "\n";
    }
}

echo "Done adding columns!\n";