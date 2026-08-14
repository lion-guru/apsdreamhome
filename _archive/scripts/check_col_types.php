<?php
require_once __DIR__ . '/../app/Core/ConfigService.php';
require_once __DIR__ . '/../app/Core/Database/Database.php';
$db = \App\Core\Database\Database::getInstance();

$tables = ['land_deals', 'land_leads', 'colonies', 'users', 'colony_development_costs', 'colony_layouts', 'land_documents'];
foreach ($tables as $t) {
    echo "=== $t ===\n";
    $cols = $db->fetchAll("SHOW COLUMNS FROM $t");
    foreach ($cols as $c) {
        if (strpos($c['Field'], '_id') !== false || $c['Field'] === 'id') {
            echo "  {$c['Field']}: {$c['Type']} {$c['Null']} {$c['Key']}\n";
        }
    }
}?>