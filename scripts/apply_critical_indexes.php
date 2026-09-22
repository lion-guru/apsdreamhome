<?php
/**
 * APS DREAM HOME — CRITICAL DATABASE INDEXING MIGRATION
 * Adds missing performance indexes on core transactional & MLM tables
 */

require_once __DIR__ . '/../config/bootstrap.php';

$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=apsdreamhome', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

echo "=================================================================\n";
echo "   APPLYING CRITICAL HIGH-TRAFFIC DATABASE PERFORMANCE INDEXES\n";
echo "=================================================================\n\n";

$indexes = [
    ['users', 'sponsor_id', 'idx_users_sponsor_id'],
    ['site_visits', 'agent_id', 'idx_site_visits_agent_id'],
    ['site_visits', 'property_id', 'idx_site_visits_property_id'],
    ['site_visits', 'colony_id', 'idx_site_visits_colony_id'],
    ['bookings', 'sales_manager_id', 'idx_bookings_sales_manager_id'],
    ['payments', 'gateway_transaction_id', 'idx_payments_gateway_txn_id'],
    ['payments', 'reference_id', 'idx_payments_reference_id'],
    ['leads', 'source_id', 'idx_leads_source_id'],
    ['mlm_commissions', 'user_id', 'idx_mlm_commissions_user_id'],
    ['mlm_commissions', 'booking_id', 'idx_mlm_commissions_booking_id'],
];

foreach ($indexes as $item) {
    list($table, $column, $indexName) = $item;
    
    // Check if table and column exist
    $colCheck = $pdo->query("
        SELECT COUNT(*) FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = 'apsdreamhome' AND TABLE_NAME = '$table' AND COLUMN_NAME = '$column'
    ")->fetchColumn();
    
    if (!$colCheck) {
        echo "[-] Skipping $table.$column (column does not exist)\n";
        continue;
    }
    
    // Check if index already exists
    $idxCheck = $pdo->query("
        SELECT COUNT(*) FROM information_schema.STATISTICS 
        WHERE TABLE_SCHEMA = 'apsdreamhome' AND TABLE_NAME = '$table' AND INDEX_NAME = '$indexName'
    ")->fetchColumn();
    
    if ($idxCheck > 0) {
        echo "[=] Already exists: Index '$indexName' on `$table`(`$column`)\n";
        continue;
    }
    
    try {
        $pdo->exec("CREATE INDEX `$indexName` ON `$table`(`$column`)");
        echo "[+] Successfully created index: `$indexName` on `$table`(`$column`)\n";
    } catch (\Exception $e) {
        echo "[!] Error creating index on $table.$column: " . $e->getMessage() . "\n";
    }
}

echo "\n=================================================================\n";
echo "                   INDEX MIGRATION COMPLETE\n";
echo "=================================================================\n";
