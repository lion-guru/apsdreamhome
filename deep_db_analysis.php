<?php
/**
 * Deep Database Schema Analysis
 * Check ALL existing tables and their structure
 */

require __DIR__ . '/config/bootstrap.php';

$pdo = \App\Core\Database::getInstance()->getConnection();

echo "===========================================\n";
echo "  DEEP DATABASE ANALYSIS - APS DREAM HOME\n";
echo "===========================================\n\n";

// Get all tables
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
sort($tables);

echo "TOTAL TABLES: " . count($tables) . "\n\n";

// Categories
$categories = [
    'user' => [],
    'lead' => [],
    'customer' => [],
    'property' => [],
    'plot' => [],
    'project' => [],
    'booking' => [],
    'sale' => [],
    'payment' => [],
    'commission' => [],
    'mlm' => [],
    'associate' => [],
    'agent' => [],
    'employee' => [],
    'visit' => [],
    'task' => [],
    'ticket' => [],
    'wallet' => [],
    'payout' => [],
    'referral' => [],
    'network' => [],
    'gallery' => [],
    'content' => [],
    'setting' => [],
    'log' => [],
    'admin' => [],
    'other' => []
];

foreach ($tables as $table) {
    $found = false;
    foreach (array_keys($categories) as $cat) {
        if (strpos($table, $cat) !== false) {
            $categories[$cat][] = $table;
            $found = true;
            break;
        }
    }
    if (!$found) $categories['other'][] = $table;
}

foreach ($categories as $cat => $list) {
    if (!empty($list)) {
        echo "\n📁 " . strtoupper($cat) . " (" . count($list) . " tables)\n";
        echo str_repeat('-', 50) . "\n";
        foreach ($list as $t) {
            // Get row count
            $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
            echo sprintf("  %-35s %6d rows\n", $t, $count);
        }
    }
}

// Check specific critical tables
echo "\n\n🔍 CRITICAL TABLES CHECK:\n";
echo str_repeat('=', 50) . "\n";

$criticalTables = [
    'users', 'leads', 'customers', 'properties', 'plots', 'projects', 'bookings',
    'sales', 'payments', 'commissions', 'commission_rules', 'payouts',
    'wallet_transactions', 'wallet_points', 'visits', 'site_visits',
    'network_tree', 'mlm_networks', 'referrals', 'referral_rewards',
    'property_images', 'gallery', 'lead_scoring', 'lead_scores',
    'lead_engagement', 'lead_engagement_metrics', 'tasks', 'support_tickets'
];

foreach ($criticalTables as $table) {
    $exists = in_array($table, $tables);
    if ($exists) {
        $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        echo "✓ $table EXISTS ($count rows)\n";
        
        // Get columns
        $columns = $pdo->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_COLUMN);
        echo "  Columns: " . implode(', ', array_slice($columns, 0, 5)) . "...\n";
    } else {
        echo "✗ $table NOT FOUND\n";
    }
}

echo "\n\n✅ ANALYSIS COMPLETE\n";
