<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Get all tables
$stmt = $pdo->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'apsdreamhome' AND TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME");
$allTables = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $allTables[] = $row['TABLE_NAME'];
}
echo "Total tables: " . count($allTables) . "\n\n";

// Check which have tenant_id
echo "Tables WITH tenant_id column:\n";
$withTenant = [];
foreach ($allTables as $t) {
    $c = $pdo->query("SHOW COLUMNS FROM `$t` LIKE 'tenant_id'");
    if ($c->rowCount() > 0) {
        $withTenant[] = $t;
        echo "  $t\n";
    }
}

echo "\nTables WITHOUT tenant_id (relevant for controllers):\n";
$withoutTenant = array_diff($allTables, $withTenant);
// Only show tables that are likely tenant-scoped (not laravel/system/cache tables)
$skipPatterns = ['cache', 'sessions', 'migrations', 'failed_jobs', 'personal_access_tokens', 'password_reset'];
foreach ($withoutTenant as $t) {
    $skip = false;
    foreach ($skipPatterns as $p) {
        if (strpos($t, $p) !== false) { $skip = true; break; }
    }
    if (!$skip) {
        echo "  $t\n";
    }
}
