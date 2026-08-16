<?php
require_once 'vendor/autoload.php';
require_once 'app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance()->getConnection();

// Add deleted_at column for soft deletes
try {
    $db->exec("ALTER TABLE legal_documents ADD COLUMN deleted_at TIMESTAMP NULL AFTER created_at");
    echo "Added deleted_at column\n";
} catch (Exception $e) {
    echo "deleted_at: " . $e->getMessage() . "\n";
}

// Also check legal_document_versions
try {
    $db->exec("ALTER TABLE legal_document_versions ADD COLUMN deleted_at TIMESTAMP NULL AFTER created_at");
    echo "Added deleted_at to versions\n";
} catch (Exception $e) {
    echo "versions deleted_at: " . $e->getMessage() . "\n";
}

// legal_document_acceptances
try {
    $db->exec("ALTER TABLE legal_document_acceptances ADD COLUMN deleted_at TIMESTAMP NULL AFTER accepted_at");
    echo "Added deleted_at to acceptances\n";
} catch (Exception $e) {
    echo "acceptances deleted_at: " . $e->getMessage() . "\n";
}

echo "Done!\n";