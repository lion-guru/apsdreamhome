<?php
/**
 * Performance Index Audit
 * Identifies missing indexes on hot-path tables
 */
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Hot tables (large or frequently queried)
$hotTables = [
    'leads' => ['status', 'assigned_to', 'created_at', 'source'],
    'properties' => ['status', 'city_id', 'price', 'listing_type'],
    'user_properties' => ['status', 'user_id', 'property_type'],
    'plots' => ['colony_id', 'status', 'plot_number', 'block'],
    'bookings' => ['customer_id', 'plot_id', 'status', 'booking_date'],
    'payments' => ['booking_id', 'status', 'payment_date', 'customer_id'],
    'commissions' => ['user_id', 'status', 'created_at'],
    'payouts' => ['user_id', 'status', 'requested_at'],
    'inquiries' => ['property_id', 'user_id', 'status', 'created_at'],
    'site_visits' => ['property_id', 'user_id', 'visit_date', 'status'],
    'ai_call_sessions' => ['user_id', 'agent_id', 'status', 'started_at'],
    'mlm_commission_ledger' => ['beneficiary_user_id', 'source_user_id', 'created_at'],
    'users' => ['role', 'email', 'phone', 'created_at'],
    'projects' => ['status', 'city_id', 'launch_date'],
    'associates' => ['user_id', 'sponsor_id', 'level'],
    'mlm_profiles' => ['user_id', 'sponsor_user_id'],
    'wallet_points' => ['user_id'],
    'workflow_instances' => ['status', 'entity_type', 'entity_id'],
    'notifications' => ['user_id', 'is_read', 'created_at'],
    'notification_queue' => ['status', 'scheduled_at'],
    'email_queue' => ['status', 'scheduled_at'],
    'sms_queue' => ['status', 'scheduled_at'],
    'whatsapp_messages' => ['status', 'scheduled_at'],
    'audit_log' => ['user_id', 'entity_type', 'created_at'],
    'admin_activity_log' => ['admin_id', 'created_at'],
    'visitor_page_views' => ['session_id', 'page_url', 'created_at'],
    'admin_menu_items' => ['section', 'order_index', 'is_active'],
    'pipeline_stages' => ['pipeline_id', 'order_index'],
    'tasks' => ['assigned_to', 'status', 'due_date'],
    'support_tickets' => ['user_id', 'assigned_to', 'status', 'created_at'],
    'companies' => ['status'],
    'builders' => ['status'],
    'colonies' => ['district_id', 'status'],
    'districts' => ['state_id'],
    'cities' => ['district_id', 'name'],
    'states' => ['country_id'],
    'pincodes' => ['pincode', 'city_id'],
    'bank_branches' => ['bank_id', 'ifsc'],
];

echo "=== PERFORMANCE INDEX AUDIT ===\n\n";
$missing = [];
$existing = [];

foreach ($hotTables as $table => $columns) {
    // Check table exists
    $exists = $pdo->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='apsdreamhome' AND TABLE_NAME='$table'")->fetchColumn();
    if (!$exists) continue;

    // Get existing indexes
    $stmt = $pdo->prepare("SHOW INDEX FROM `$table`");
    $stmt->execute();
    $indexed = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $idx) {
        $indexed[] = $idx['Column_name'];
    }

    foreach ($columns as $col) {
        if (in_array($col, $indexed)) {
            $existing[] = "$table.$col";
        } else {
            // Check if column exists
            $check = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$col'")->fetch();
            if ($check) {
                $missing[] = "$table.$col";
            }
        }
    }
}

echo "Existing indexes: " . count($existing) . "\n";
echo "Missing indexes: " . count($missing) . "\n\n";

if (count($missing) > 0) {
    echo "MISSING (in order of priority):\n";
    foreach ($missing as $m) echo "  - $m\n";
    echo "\nTo apply: php scripts/apply_performance_indexes.php\n";
}?>