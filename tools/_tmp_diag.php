<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::FETCH_ASSOC,
]);

$failures = [
    ['employees', 'user_id', 'users', 'id'],
    ['mlm_profiles', 'user_id', 'users', 'id'],
    ['mlm_commission_ledger', 'beneficiary_user_id', 'users', 'id'],
    ['mlm_commission_ledger', 'source_user_id', 'users', 'id'],
    ['colonies', 'district_id', 'districts', 'id'],
    ['plots', 'colony_id', 'colonies', 'id'],
    ['property_images', 'property_id', 'user_properties', 'id'],
];

foreach ($failures as $f) {
    list($child, $childCol, $parent, $parentCol) = $f;
    echo "=== $child.$childCol -> $parent.$parentCol ===\n";

    // Check child column type
    $cType = $pdo->query("SHOW COLUMNS FROM `$child` WHERE Field = '$childCol'")->fetch();
    echo "  Child col: {$cType['Field']} {$cType['Type']} {$cType['Null']}\n";
    
    // Check parent column type
    $pType = $pdo->query("SHOW COLUMNS FROM `$parent` WHERE Field = '$parentCol'")->fetch();
    echo "  Parent col: {$pType['Field']} {$pType['Type']} {$pType['Null']} {$pType['Key']}\n";

    // Check for orphaned values in child
    $orphans = $pdo->query("SELECT COUNT(*) as cnt FROM `$child` c WHERE c.`$childCol` IS NOT NULL AND c.`$childCol` NOT IN (SELECT `$parentCol` FROM `$parent`)")->fetch();
    echo "  Orphaned child rows: {$orphans['cnt']}\n";
    if ($orphans['cnt'] > 0) {
        $sample = $pdo->query("SELECT c.`$childCol` FROM `$child` c WHERE c.`$childCol` IS NOT NULL AND c.`$childCol` NOT IN (SELECT `$parentCol` FROM `$parent`) LIMIT 5")->fetchAll(PDO::FETCH_COLUMN);
        echo "  Sample orphan values: " . json_encode($sample) . "\n";
    }

    // Check parent index
    echo "\n";
}
