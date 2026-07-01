<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 1. Tables without PKs
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$noPk = [];
foreach ($tables as $t) {
    $pk = $pdo->query("SHOW KEYS FROM `$t` WHERE Key_name = 'PRIMARY'")->fetch();
    if (!$pk) $noPk[] = $t;
}
echo "=== TABLES WITHOUT PRIMARY KEY: " . count($noPk) . " ===\n";
if (!empty($noPk)) foreach ($noPk as $t) echo "  MISSING PK: $t\n";

// 2. Critical table row counts
echo "\n=== CRITICAL TABLE ROW COUNTS ===\n";
$critical = ['users', 'leads', 'plot_bookings', 'colonies', 'mlm_commission_ledger', 'associates', 'mlm_network_tree', 'admin_menu_items', 'lead_activities', 'site_visits', 'crm_tasks', 'booking_payment_schedules', 'wallet_points', 'mlm_settings', 'mlm_rank_benefits', 'property_views', 'inquiries', 'whatsapp_lead_shares'];
foreach ($critical as $t) {
    try {
        $cnt = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        echo "  $t: $cnt\n";
    } catch (Throwable $e) {
        echo "  $t: ERROR - " . $e->getMessage() . "\n";
    }
}

// 3. Orphaned foreign keys (check FK constraints)
echo "\n=== FK CONSTRAINTS ===\n";
try {
    $fks = $pdo->query("
        SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = 'apsdreamhome' AND REFERENCED_TABLE_NAME IS NOT NULL
        ORDER BY TABLE_NAME, CONSTRAINT_NAME
    ")->fetchAll(PDO::FETCH_ASSOC);
    echo "  Total FK constraints: " . count($fks) . "\n";
    foreach ($fks as $fk) {
        echo "  {$fk['TABLE_NAME']}.{$fk['COLUMN_NAME']} -> {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}\n";
    }
} catch (Throwable $e) {
    echo "  Error: " . $e->getMessage() . "\n";
}

// 4. Check for duplicate column names across critical tables
echo "\n=== DUPLICATE LEAD TABLES CHECK ===\n";
try {
    $leadTables = $pdo->query("SHOW TABLES LIKE '%lead%'")->fetchAll(PDO::FETCH_COLUMN);
    echo "  Tables with 'lead' in name: " . count($leadTables) . "\n";
    foreach ($leadTables as $lt) echo "    $lt\n";
    
    $bookingTables = $pdo->query("SHOW TABLES LIKE '%booking%'")->fetchAll(PDO::FETCH_COLUMN);
    echo "  Tables with 'booking' in name: " . count($bookingTables) . "\n";
    foreach ($bookingTables as $bt) echo "    $bt\n";
} catch (Throwable $e) {}

// 5. Check for tables that may be orphaned (no FK references)
echo "\n=== UNUSED/ORPHANED TABLE CHECK ===\n";
try {
    $allTables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    $referencedTables = $pdo->query("
        SELECT DISTINCT REFERENCED_TABLE_NAME 
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = 'apsdreamhome' AND REFERENCED_TABLE_NAME IS NOT NULL
    ")->fetchAll(PDO::FETCH_COLUMN);
    $orphaned = array_diff($allTables, $referencedTables);
    echo "  Tables not referenced by FK: " . count($orphaned) . " (this is normal for root tables)\n";
} catch (Throwable $e) {}
