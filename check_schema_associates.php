<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Core\App;

// Initialize the app (assuming it handles environment loading etc)
$app = new App(__DIR__);

try {
    $db = App::database();
    $columns = $db->fetchAll("DESCRIBE associates");
    echo "Table: associates\n";
    foreach ($columns as $col) {
        echo $col['Field'] . " - " . $col['Type'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
