<?php
/**
 * Migration: Recreate crm_routing_rules table with correct schema
 * Matches LeadRoutingService column expectations
 */

function run_migration() {
    $pdo = getDBConnection();
    
    // Drop old table if it has wrong columns
    $pdo->exec("DROP TABLE IF EXISTS crm_routing_rules");
    
    $sql = "CREATE TABLE IF NOT EXISTS crm_routing_rules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        source_pattern VARCHAR(255) DEFAULT '*',
        city_pattern VARCHAR(255) DEFAULT '*',
        min_budget DECIMAL(15,2) DEFAULT 0,
        max_budget DECIMAL(15,2) DEFAULT 0,
        target_department_id INT UNSIGNED,
        target_user_id INT UNSIGNED,
        priority INT DEFAULT 100,
        is_active TINYINT(1) DEFAULT 1,
        created_by INT UNSIGNED,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_source (source_pattern),
        INDEX idx_city (city_pattern),
        INDEX idx_budget (min_budget, max_budget),
        INDEX idx_active_priority (is_active, priority ASC)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $pdo->exec($sql);
    echo "✅ Created crm_routing_rules table (correct schema)\n";
    
    // Create routing log table
    $logSql = "CREATE TABLE IF NOT EXISTS lead_routing_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        lead_id INT UNSIGNED NOT NULL,
        rule_id INT UNSIGNED,
        target_department_id INT UNSIGNED,
        target_user_id INT UNSIGNED,
        routed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_lead (lead_id),
        INDEX idx_rule (rule_id),
        INDEX idx_date (routed_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $pdo->exec($logSql);
    echo "✅ Created lead_routing_log table\n";
    
    // Seed some default rules
    $rules = [
        ['High Budget Leads', '*', '*', 5000000, 0, null, null, 10],
        ['Website Leads', 'website', '*', 0, 0, null, null, 20],
        ['Referral Leads', 'referral', '*', 0, 0, null, null, 20],
        ['Social Media Leads', 'social%', '*', 0, 0, null, null, 30],
        ['Default Catch-All', '*', '*', 0, 0, null, null, 999],
    ];
    
    $insert = $pdo->prepare("INSERT INTO crm_routing_rules (name, source_pattern, city_pattern, min_budget, max_budget, target_department_id, target_user_id, priority) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($rules as $r) {
        $insert->execute($r);
    }
    echo "✅ Seeded " . count($rules) . " default routing rules\n";
}

function getDBConnection() {
    $host = '127.0.0.1';
    $port = 3307;
    $db = 'apsdreamhome';
    $user = 'root';
    $pass = '';
    
    return new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
}

if (php_sapi_name() === 'cli') {
    run_migration();
}
