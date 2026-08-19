<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');

echo "=== Database Integrity Check ===\n\n";

// 1. Count tables
$stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'apsdreamhome'");
$tableCount = $stmt->fetchColumn();
echo "1. Total Tables: $tableCount\n";

// 2. Check key tables
$keyTables = ['users', 'properties', 'bookings', 'site_visits', 'mlm_profiles', 'mlm_referrals', 'mlm_payouts', 'api_tokens', 'payments', 'colonies', 'plots', 'notifications'];
echo "\n2. Key Tables:\n";
foreach ($keyTables as $table) {
    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute([$table]);
    $exists = $stmt->fetch() !== false;
    if ($exists) {
        $stmt2 = $pdo->query("SELECT COUNT(*) FROM `$table`");
        $count = $stmt2->fetchColumn();
        echo "   $table: EXISTS ($count rows)\n";
    } else {
        echo "   $table: MISSING!\n";
    }
}

// 3. Check foreign keys
echo "\n3. Foreign Key Constraints:\n";
$stmt = $pdo->query("
    SELECT 
        TABLE_NAME,
        COLUMN_NAME,
        REFERENCED_TABLE_NAME,
        REFERENCED_COLUMN_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = 'apsdreamhome'
    AND REFERENCED_TABLE_NAME IS NOT NULL
    LIMIT 20
");
$fks = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (empty($fks)) {
    echo "   No foreign keys found (using application-level integrity)\n";
} else {
    foreach ($fks as $fk) {
        echo "   {$fk['TABLE_NAME']}.{$fk['COLUMN_NAME']} -> {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}\n";
    }
}

// 4. Check for orphaned records
echo "\n4. Data Integrity:\n";
$checks = [
    ['Bookings without valid customer', "SELECT COUNT(*) FROM bookings b LEFT JOIN users u ON b.customer_id = u.id WHERE u.id IS NULL AND b.customer_id > 0"],
    ['Site visits without valid user', "SELECT COUNT(*) FROM site_visits sv LEFT JOIN users u ON sv.user_id = u.id WHERE u.id IS NULL AND sv.user_id > 0"],
    ['Properties without valid type', "SELECT COUNT(*) FROM properties p LEFT JOIN property_types pt ON p.property_type_id = pt.id WHERE pt.id IS NULL AND p.property_type_id > 0"],
];
foreach ($checks as [$label, $sql]) {
    try {
        $stmt = $pdo->query($sql);
        $count = $stmt->fetchColumn();
        $status = $count == 0 ? "OK" : "WARNING";
        echo "   $status: $label ($count orphaned)\n";
    } catch (\Exception $e) {
        echo "   SKIP: $label (table/column not found)\n";
    }
}

echo "\n=== Database Integrity Check Complete ===\n";
