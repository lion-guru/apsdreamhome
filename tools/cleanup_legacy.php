<?php
// tools/cleanup_legacy.php

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/bootstrap.php';

use App\Core\Database;

echo "Starting Legacy Cleanup...\n";
echo "----------------------------------------\n";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // 1. Delete mlm_agents (Confirmed empty)
    echo "Dropping table 'mlm_agents'...\n";
    $conn->exec("DROP TABLE IF EXISTS mlm_agents");
    echo "✔ Table 'mlm_agents' deleted successfully.\n";

    // 2. Check mlm_associates
    echo "Checking 'mlm_associates'...\n";
    $stmt = $conn->query("SELECT COUNT(*) FROM mlm_associates");
    $count = $stmt->fetchColumn();
    if ($count <= 1) {
        echo "Dropping table 'mlm_associates' ($count records)...\n";
        $conn->exec("DROP TABLE IF EXISTS mlm_associates");
        echo "✔ Table 'mlm_associates' deleted successfully.\n";
    } else {
        echo "⚠ Table 'mlm_associates' has $count records. Keeping for manual review.\n";
    }

} catch (Exception $e) {
    echo "Error during cleanup: " . $e->getMessage() . "\n";
}

echo "\nCleanup Complete.\n";
