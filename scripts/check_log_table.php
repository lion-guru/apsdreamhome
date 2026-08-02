<?php
require __DIR__ . '/../config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance()->getConnection();

$tables = ['user_activity_log'];
foreach ($tables as $t) {
    $r = $db->query("SELECT COUNT(*) as c FROM $t");
    $row = $r->fetch();
    echo "$t: " . $row['c'] . " rows\n";

    $cols = $db->query("SHOW COLUMNS FROM $t");
    $hasTid = false;
    while ($c = $cols->fetch()) {
        if ($c['Field'] === 'tenant_id') $hasTid = true;
    }
    echo "  has tenant_id: " . ($hasTid ? 'YES' : 'NO') . "\n";
}
