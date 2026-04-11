<?php
// Check specific critical tables
$host = '127.0.0.1';
$port = '3307';
$dbname = 'apsdreamhome';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $criticalTables = [
        'visits', 'site_visits', 'field_visits',
        'sales', 'property_sales',
        'lead_scoring', 'lead_scores', 'lead_scoring_history',
        'wallet_transactions', 'wallet_points', 'wallets',
        'commission_rules', 'commissions', 'commission_records',
        'payouts', 'payout_requests',
        'network_tree', 'mlm_networks', 'network_trees',
        'referrals', 'referral_rewards', 'referral_tracking',
        'property_images', 'property_gallery', 'property_photos',
        'lead_engagement_metrics', 'engagement_metrics'
    ];
    
    echo "<pre>🔍 CHECKING CRITICAL TABLES:\n";
    echo str_repeat('=', 60) . "\n\n";
    
    foreach ($criticalTables as $table) {
        try {
            $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            $columns = $pdo->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_COLUMN);
            echo "✅ $table - EXISTS ($count rows)\n";
            echo "   Columns: " . implode(', ', array_slice($columns, 0, 5)) . (count($columns) > 5 ? '...' : '') . "\n\n";
        } catch (PDOException $e) {
            echo "❌ $table - NOT FOUND\n\n";
        }
    }
    
    echo "</pre>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
