<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/bootstrap.php';

use App\Core\Database;

echo "Dropping api_keys table...
";

try {
    $db = Database::getInstance();
    $db->exec("DROP TABLE IF EXISTS api_keys");
    echo "api_keys table dropped successfully.
";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "
";
    exit(1);
}
