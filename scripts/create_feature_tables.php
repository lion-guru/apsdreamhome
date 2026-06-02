<?php
/**
 * Create additional tables for new features
 */

$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");

echo "=== Creating Additional Tables ===\n\n";

// 1. Plot Development Costs Table
echo "1. Creating plot_development_costs table...\n";
$sql1 = "CREATE TABLE IF NOT EXISTS plot_development_costs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    colony_id INT UNSIGNED NOT NULL,
    cost_type ENUM('land', 'development', 'amenities', 'legal', 'misc') NOT NULL,
    description VARCHAR(255),
    amount DECIMAL(12,2) NOT NULL,
    per_sqft_rate DECIMAL(10,2),
    total_area_sqft DECIMAL(12,2),
    vendor_name VARCHAR(200),
    invoice_number VARCHAR(50),
    invoice_date DATE,
    payment_status ENUM('pending', 'partial', 'completed') DEFAULT 'pending',
    paid_amount DECIMAL(12,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_colony_id (colony_id),
    INDEX idx_cost_type (cost_type),
    INDEX idx_payment_status (payment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

try {
    $pdo->exec($sql1);
    echo "   ✅ plot_development_costs created\n";
} catch (Exception $e) {
    echo "   ⚠️  " . substr($e->getMessage(), 0, 80) . "\n";
}

// 2. Automation Triggers Table
echo "2. Creating automation_triggers table...\n";
$sql2 = "CREATE TABLE IF NOT EXISTS automation_triggers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    trigger_name VARCHAR(100) NOT NULL,
    trigger_type ENUM('lead_score', 'lead_status', 'payment', 'property', 'schedule', 'custom') NOT NULL,
    conditions JSON NOT NULL,
    actions JSON NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    last_triggered_at TIMESTAMP,
    trigger_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_trigger_type (trigger_type),
    INDEX idx_is_active (is_active),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

try {
    $pdo->exec($sql2);
    echo "   ✅ automation_triggers created\n";
} catch (Exception $e) {
    echo "   ⚠️  " . substr($e->getMessage(), 0, 80) . "\n";
}

// 3. Lead Status History Table
echo "3. Creating lead_status_history table...\n";
$sql3 = "CREATE TABLE IF NOT EXISTS lead_status_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id INT UNSIGNED NOT NULL,
    old_status VARCHAR(50),
    new_status VARCHAR(50),
    changed_by BIGINT UNSIGNED,
    notes TEXT,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_lead_id (lead_id),
    INDEX idx_changed_at (changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

try {
    $pdo->exec($sql3);
    echo "   ✅ lead_status_history created\n";
} catch (Exception $e) {
    echo "   ⚠️  " . substr($e->getMessage(), 0, 80) . "\n";
}

// 4. Lead Pipeline Table
echo "4. Creating lead_pipeline table...\n";
$sql4 = "CREATE TABLE IF NOT EXISTS lead_pipeline (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id INT UNSIGNED NOT NULL UNIQUE,
    current_stage_id INT UNSIGNED,
    assigned_to BIGINT UNSIGNED,
    assigned_at TIMESTAMP,
    entered_stage_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    deal_value DECIMAL(12,2),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_lead_id (lead_id),
    INDEX idx_assigned_to (assigned_to),
    INDEX idx_priority (priority),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

try {
    $pdo->exec($sql4);
    echo "   ✅ lead_pipeline created\n";
} catch (Exception $e) {
    echo "   ⚠️  " . substr($e->getMessage(), 0, 80) . "\n";
}

// 5. MLM Points Transactions Table
echo "5. Creating mlm_points_transactions table...\n";
$sql5 = "CREATE TABLE IF NOT EXISTS mlm_points_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    points INT NOT NULL,
    type ENUM('credit', 'debit') NOT NULL,
    reference_type VARCHAR(50),
    reference_id BIGINT UNSIGNED,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_type (type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

try {
    $pdo->exec($sql5);
    echo "   ✅ mlm_points_transactions created\n";
} catch (Exception $e) {
    echo "   ⚠️  " . substr($e->getMessage(), 0, 80) . "\n";
}

// 6. Campaign Members Table
echo "6. Creating campaign_members table...\n";
$sql6 = "CREATE TABLE IF NOT EXISTS campaign_members (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT UNSIGNED NOT NULL,
    lead_id INT UNSIGNED,
    user_id BIGINT UNSIGNED,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_campaign_id (campaign_id),
    INDEX idx_lead_id (lead_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

try {
    $pdo->exec($sql6);
    echo "   ✅ campaign_members created\n";
} catch (Exception $e) {
    echo "   ⚠️  " . substr($e->getMessage(), 0, 80) . "\n";
}

// 7. Plot Allocations Table
echo "7. Creating plot_allocations table...\n";
$sql7 = "CREATE TABLE IF NOT EXISTS plot_allocations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plot_id INT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NOT NULL,
    allocation_date DATE NOT NULL,
    agreement_number VARCHAR(50),
    agreement_date DATE,
    registry_number VARCHAR(50),
    registry_date DATE,
    registry_amount DECIMAL(12,2),
    plot_rate DECIMAL(10,2) NOT NULL,
    total_plot_value DECIMAL(12,2) NOT NULL,
    development_charges DECIMAL(12,2) DEFAULT 0,
    total_amount DECIMAL(12,2) NOT NULL,
    amount_paid DECIMAL(12,2) DEFAULT 0,
    amount_pending DECIMAL(12,2),
    possession_date DATE,
    possession_status ENUM('not_due', 'due', 'given', 'delayed') DEFAULT 'not_due',
    status ENUM('booked', 'agreement_pending', 'agreement_done', 'registry_pending', 'registry_done', 'completed') DEFAULT 'booked',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_plot_id (plot_id),
    INDEX idx_customer_id (customer_id),
    INDEX idx_status (status),
    INDEX idx_possession_status (possession_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

try {
    $pdo->exec($sql7);
    echo "   ✅ plot_allocations created\n";
} catch (Exception $e) {
    echo "   ⚠️  " . substr($e->getMessage(), 0, 80) . "\n";
}

// 8. Add columns to users table if not exists
echo "8. Adding columns to users table...\n";
$alterQueries = [
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS mlm_points INT DEFAULT 0 AFTER mlm_target",
    "ALTER TABLE leads ADD COLUMN IF NOT EXISTS lead_category VARCHAR(20) DEFAULT 'cold' AFTER status",
];

foreach ($alterQueries as $sql) {
    try {
        $pdo->exec($sql);
        echo "   ✅ Column added\n";
    } catch (Exception $e) {
        echo "   - " . substr($e->getMessage(), 0, 60) . "\n";
    }
}

echo "\n=== Setup Complete ===\n";

// Show new table counts
$newTables = [
    'plot_development_costs',
    'automation_triggers',
    'lead_status_history',
    'lead_pipeline',
    'mlm_points_transactions',
    'campaign_members',
    'plot_allocations'
];

echo "\nNew tables status:\n";
foreach ($newTables as $table) {
    try {
        $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        echo "  ✅ $table: $count rows\n";
    } catch (Exception $e) {
        echo "  ❌ $table: Error\n";
    }
}
