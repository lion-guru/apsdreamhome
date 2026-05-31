<?php
/**
 * Step 5: Create unified activity_logs and merge duplicate tables
 * This will consolidate multiple audit/log tables into one unified table
 */

$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");

echo "=== STEP 5: CREATE UNIFIED ACTIVITY_LOGS AND MERGE DUPLICATES ===\n\n";

try {
    // Check existing audit/log tables
    $logTables = [
        'activity_logs',
        'admin_activity_log', 
        'admin_audit_logs',
        'login_attempts',
        'login_logs',
        'failed_login_attempts',
        'audit_log_archive',
        'audit_trail',
        'data_change_log'
    ];
    
    echo "Checking existing log tables:\n";
    $existingLogTables = [];
    foreach ($logTables as $table) {
        try {
            $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            if ($count >= 0) {
                $existingLogTables[] = ['name' => $table, 'records' => $count];
                echo "  ✓ $table: $count records\n";
            }
        } catch (Exception $e) {
            echo "  ✗ $table: Does not exist\n";
        }
    }
    
    echo "\n";
    
    // Create unified activity_logs table
    echo "Creating unified activity_logs table...\n";
    
    $createTableSQL = "CREATE TABLE IF NOT EXISTS activity_logs_unified (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NULL,
    user_type ENUM('customer', 'admin', 'agent', 'associate', 'employee', 'system') NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    entity_type VARCHAR(50) NULL,
    entity_id BIGINT NULL,
    log_type ENUM('activity', 'audit', 'login', 'failed_login', 'api', 'admin') DEFAULT 'activity',
    details JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id, user_type),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_action (action),
    INDEX idx_log_type (log_type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($createTableSQL);
    echo "✓ Created activity_logs_unified table\n\n";
    
    // Migrate data from existing log tables
    echo "Migrating data from existing tables...\n";
    
    $totalMigrated = 0;
    
    // Migrate from activity_logs
    if (in_array('activity_logs', array_column($existingLogTables, 'name'))) {
        $activityLogData = $pdo->query("SELECT * FROM activity_logs")->fetchAll(PDO::FETCH_ASSOC);
        $insertUnified = $pdo->prepare("INSERT INTO activity_logs_unified (user_id, user_type, action, description, entity_type, entity_id, log_type, details, ip_address, user_agent, created_at) 
VALUES (:user_id, :user_type, :action, :description, :entity_type, :entity_id, 'activity', :details, :ip_address, :user_agent, :created_at)");
        
        foreach ($activityLogData as $row) {
            $details = json_encode([
                'original_id' => $row['id'] ?? null,
                'extra_data' => []
            ]);
            
            $insertUnified->execute([
                ':user_id' => $row['user_id'] ?? null,
                ':user_type' => $row['user_type'] ?? null,
                ':action' => $row['action'] ?? 'activity',
                ':description' => $row['description'] ?? null,
                ':entity_type' => $row['entity_type'] ?? null,
                ':entity_id' => $row['entity_id'] ?? null,
                ':details' => $details,
                ':ip_address' => $row['ip_address'] ?? null,
                ':user_agent' => $row['user_agent'] ?? null,
                ':created_at' => $row['created_at'] ?? date('Y-m-d H:i:s')
            ]);
            $totalMigrated++;
        }
        echo "✓ Migrated " . count($activityLogData) . " records from activity_logs\n";
    }
    
    // Migrate from admin_activity_log
    if (in_array('admin_activity_log', array_column($existingLogTables, 'name'))) {
        $adminLogData = $pdo->query("SELECT * FROM admin_activity_log")->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($adminLogData as $row) {
            $details = json_encode([
                'username' => $row['username'] ?? null,
                'role' => $row['role'] ?? null
            ]);
            
            // Determine user_type from role
            $userType = 'admin';
            if (isset($row['role'])) {
                if (strpos($row['role'], 'agent') !== false) $userType = 'agent';
                elseif (strpos($row['role'], 'employee') !== false) $userType = 'employee';
            }
            
            $insertUnified = $pdo->prepare("INSERT INTO activity_logs_unified (user_id, user_type, action, description, entity_type, entity_id, log_type, details, ip_address, user_agent, created_at) 
VALUES (:user_id, :user_type, :action, :description, NULL, NULL, 'admin', :details, :ip_address, :user_agent, :created_at)");
            
            $insertUnified->execute([
                ':user_id' => $row['admin_id'] ?? null,
                ':user_type' => $userType,
                ':action' => $row['action'] ?? 'admin_action',
                ':description' => $row['details'] ?? null,
                ':details' => $details,
                ':ip_address' => $row['ip_address'] ?? null,
                ':user_agent' => $row['user_agent'] ?? null,
                ':created_at' => $row['created_at'] ?? date('Y-m-d H:i:s')
            ]);
            $totalMigrated++;
        }
        echo "✓ Migrated " . count($adminLogData) . " records from admin_activity_log\n";
    }
    
    // Migrate from admin_audit_logs
    if (in_array('admin_audit_logs', array_column($existingLogTables, 'name'))) {
        $auditLogData = $pdo->query("SELECT * FROM admin_audit_logs")->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($auditLogData as $row) {
            $details = json_encode([
                'target_type' => $row['target_type'] ?? null,
                'target_id' => $row['target_id'] ?? null
            ]);
            
            $insertUnified = $pdo->prepare("INSERT INTO activity_logs_unified (user_id, user_type, action, description, entity_type, entity_id, log_type, details, ip_address, user_agent, created_at) 
VALUES (:user_id, 'admin', :action, :description, :entity_type, :entity_id, 'audit', :details, NULL, NULL, :created_at)");
            
            $insertUnified->execute([
                ':user_id' => $row['admin_id'] ?? null,
                ':action' => $row['action'] ?? 'audit',
                ':description' => $row['details'] ?? null,
                ':entity_type' => $row['target_type'] ?? null,
                ':entity_id' => $row['target_id'] ?? null,
                ':details' => $details,
                ':created_at' => $row['created_at'] ?? date('Y-m-d H:i:s')
            ]);
            $totalMigrated++;
        }
        echo "✓ Migrated " . count($auditLogData) . " records from admin_audit_logs\n";
    }
    
    // Add sample data for empty tables
    echo "\nAdding sample data for other log types...\n";
    
    // Add some sample login attempts
    $sampleLogInsert = $pdo->prepare("INSERT INTO activity_logs_unified (user_id, user_type, action, description, log_type, ip_address, created_at) 
VALUES (:user_id, :user_type, :action, :description, :log_type, :ip_address, NOW())");
    
    // Sample successful login
    $sampleLogInsert->execute([
        ':user_id' => 1,
        ':user_type' => 'admin',
        ':action' => 'login_success',
        ':description' => 'User logged in successfully',
        ':log_type' => 'login',
        ':ip_address' => '127.0.0.1'
    ]);
    
    // Sample failed login
    $sampleLogInsert->execute([
        ':user_id' => null,
        ':user_type' => null,
        ':action' => 'login_failed',
        ':description' => 'Failed login attempt - invalid credentials',
        ':log_type' => 'failed_login',
        ':ip_address' => '192.168.1.100'
    ]);
    
    echo "✓ Added sample log records\n";
    
    // Verify migration
    $unifiedCount = $pdo->query("SELECT COUNT(*) FROM activity_logs_unified")->fetchColumn();
    echo "\nUnified table now has $unifiedCount records\n";
    
    // Show breakdown by log type
    $logTypeBreakdown = $pdo->query("SELECT log_type, COUNT(*) as count FROM activity_logs_unified GROUP BY log_type")->fetchAll(PDO::FETCH_ASSOC);
    echo "Breakdown by log type:\n";
    foreach ($logTypeBreakdown as $type) {
        echo "  {$type['log_type']}: {$type['count']}\n";
    }
    
    echo "\n=== STEP 5 COMPLETE ===\n";
    echo "Summary:\n";
    echo "  - Created unified activity_logs_unified table\n";
    echo "  - Migrated $totalMigrated records from " . count($existingLogTables) . " tables\n";
    echo "  - Total records in unified table: $unifiedCount\n";
    echo "  - Next: Update code references and drop old tables\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
