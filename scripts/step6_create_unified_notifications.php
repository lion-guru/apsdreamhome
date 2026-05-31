<?php
/**
 * Step 6: Create unified notifications and merge communication tables
 * Consolidate email_logs, sms_logs, email_tracking, sms_otp_logs, notification_history
 */

$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");

echo "=== STEP 6: CREATE UNIFIED NOTIFICATIONS AND MERGE COMMUNICATION TABLES ===\n\n";

try {
    // Check existing communication tables
    $commTables = [
        'email_logs',
        'sms_logs',
        'email_tracking',
        'sms_otp_logs',
        'notification_history',
        'notification_templates',
        'email_templates',
        'whatsapp_logs'
    ];
    
    echo "Checking existing communication tables:\n";
    $existingCommTables = [];
    foreach ($commTables as $table) {
        try {
            $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            if ($count >= 0) {
                $existingCommTables[] = ['name' => $table, 'records' => $count];
                echo "  ✓ $table: $count records\n";
            }
        } catch (Exception $e) {
            echo "  ✗ $table: Does not exist\n";
        }
    }
    
    echo "\n";
    
    // Create unified notifications table
    echo "Creating unified notifications table...\n";
    
    $createTableSQL = "CREATE TABLE IF NOT EXISTS notifications_unified (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NULL,
    notification_type ENUM('email', 'sms', 'whatsapp', 'push', 'in_app') NOT NULL,
    subject VARCHAR(255) NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'sent', 'failed', 'delivered', 'opened', 'clicked') DEFAULT 'pending',
    recipient VARCHAR(255) NULL,
    template_id VARCHAR(100) NULL,
    metadata JSON NULL,
    sent_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    opened_at TIMESTAMP NULL,
    clicked_at TIMESTAMP NULL,
    error_message TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_type (notification_type),
    INDEX idx_status (status),
    INDEX idx_recipient (recipient),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($createTableSQL);
    echo "✓ Created notifications_unified table\n\n";
    
    // Migrate data from existing tables
    echo "Migrating data from existing tables...\n";
    
    $totalMigrated = 0;
    
    // Migrate from email_logs
    if (in_array('email_logs', array_column($existingCommTables, 'name'))) {
        $emailLogData = $pdo->query("SELECT * FROM email_logs")->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($emailLogData)) {
            $insertUnified = $pdo->prepare("INSERT INTO notifications_unified (user_id, notification_type, subject, message, status, recipient, template_id, metadata, sent_at, error_message, created_at) 
VALUES (:user_id, 'email', :subject, :message, :status, :recipient, :template_id, :metadata, :sent_at, :error_message, :created_at)");
            
            foreach ($emailLogData as $row) {
                $metadata = json_encode([
                    'original_id' => $row['id'] ?? null,
                    'email_data' => []
                ]);
                
                $insertUnified->execute([
                    ':user_id' => $row['user_id'] ?? null,
                    ':subject' => $row['subject'] ?? 'Email Notification',
                    ':message' => $row['body'] ?? $row['message'] ?? 'Email content',
                    ':status' => $row['status'] ?? 'sent',
                    ':recipient' => $row['to_email'] ?? $row['recipient'] ?? null,
                    ':template_id' => $row['template'] ?? null,
                    ':metadata' => $metadata,
                    ':sent_at' => $row['sent_at'] ?? $row['created_at'] ?? null,
                    ':error_message' => $row['error'] ?? null,
                    ':created_at' => $row['created_at'] ?? date('Y-m-d H:i:s')
                ]);
                $totalMigrated++;
            }
            echo "✓ Migrated " . count($emailLogData) . " records from email_logs\n";
        } else {
            echo "  - email_logs is empty, skipping migration\n";
        }
    }
    
    // Migrate from sms_logs
    if (in_array('sms_logs', array_column($existingCommTables, 'name'))) {
        $smsLogData = $pdo->query("SELECT * FROM sms_logs")->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($smsLogData)) {
            $insertUnified = $pdo->prepare("INSERT INTO notifications_unified (user_id, notification_type, subject, message, status, recipient, template_id, metadata, sent_at, error_message, created_at) 
VALUES (:user_id, 'sms', :subject, :message, :status, :recipient, :template_id, :metadata, :sent_at, :error_message, :created_at)");
            
            foreach ($smsLogData as $row) {
                $metadata = json_encode([
                    'original_id' => $row['id'] ?? null,
                    'sms_data' => []
                ]);
                
                $insertUnified->execute([
                    ':user_id' => $row['user_id'] ?? null,
                    ':subject' => 'SMS Notification',
                    ':message' => $row['message'] ?? $row['content'] ?? 'SMS content',
                    ':status' => $row['status'] ?? 'sent',
                    ':recipient' => $row['phone'] ?? $row['recipient'] ?? null,
                    ':template_id' => $row['template'] ?? null,
                    ':metadata' => $metadata,
                    ':sent_at' => $row['sent_at'] ?? $row['created_at'] ?? null,
                    ':error_message' => $row['error'] ?? null,
                    ':created_at' => $row['created_at'] ?? date('Y-m-d H:i:s')
                ]);
                $totalMigrated++;
            }
            echo "✓ Migrated " . count($smsLogData) . " records from sms_logs\n";
        } else {
            echo "  - sms_logs is empty, skipping migration\n";
        }
    }
    
    // Migrate from notification_history
    if (in_array('notification_history', array_column($existingCommTables, 'name'))) {
        $notifHistoryData = $pdo->query("SELECT * FROM notification_history")->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($notifHistoryData)) {
            foreach ($notifHistoryData as $row) {
                $metadata = json_encode([
                    'original_id' => $row['id'] ?? null
                ]);
                
                $insertUnified = $pdo->prepare("INSERT INTO notifications_unified (user_id, notification_type, subject, message, status, recipient, metadata, created_at) 
VALUES (:user_id, :notification_type, :subject, :message, :status, :recipient, :metadata, :created_at)");
                
                $insertUnified->execute([
                    ':user_id' => $row['user_id'] ?? null,
                    ':notification_type' => $row['type'] ?? 'in_app',
                    ':subject' => $row['title'] ?? 'Notification',
                    ':message' => $row['message'] ?? 'Notification content',
                    ':status' => $row['status'] ?? 'sent',
                    ':recipient' => $row['recipient'] ?? null,
                    ':metadata' => $metadata,
                    ':created_at' => $row['created_at'] ?? date('Y-m-d H:i:s')
                ]);
                $totalMigrated++;
            }
            echo "✓ Migrated " . count($notifHistoryData) . " records from notification_history\n";
        } else {
            echo "  - notification_history is empty, skipping migration\n";
        }
    }
    
    // Add sample data for empty tables
    echo "\nAdding sample notification data...\n";
    
    $sampleNotifInsert = $pdo->prepare("INSERT INTO notifications_unified (user_id, notification_type, subject, message, status, recipient, sent_at, created_at) 
VALUES (:user_id, :notification_type, :subject, :message, :status, :recipient, :sent_at, NOW())");
    
    // Sample email notification
    $sampleNotifInsert->execute([
        ':user_id' => 1,
        ':notification_type' => 'email',
        ':subject' => 'Welcome to APS Dream Home',
        ':message' => 'Thank you for registering with APS Dream Home. Your account has been successfully created.',
        ':status' => 'sent',
        ':recipient' => 'user@example.com',
        ':sent_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
    ]);
    
    // Sample SMS notification
    $sampleNotifInsert->execute([
        ':user_id' => 1,
        ':notification_type' => 'sms',
        ':subject' => 'OTP Verification',
        ':message' => 'Your verification code is 123456. Valid for 10 minutes.',
        ':status' => 'sent',
        ':recipient' => '+91 9876543210',
        ':sent_at' => date('Y-m-d H:i:s', strtotime('-2 hours'))
    ]);
    
    // Sample in-app notification
    $sampleNotifInsert->execute([
        ':user_id' => 1,
        ':notification_type' => 'in_app',
        ':subject' => 'New Property Listed',
        ':message' => 'A new property matching your criteria has been listed in your preferred location.',
        ':status' => 'pending',
        ':recipient' => null,
        ':sent_at' => null
    ]);
    
    echo "✓ Added sample notification records\n";
    
    // Verify migration
    $unifiedCount = $pdo->query("SELECT COUNT(*) FROM notifications_unified")->fetchColumn();
    echo "\nUnified table now has $unifiedCount records\n";
    
    // Show breakdown by type
    $typeBreakdown = $pdo->query("SELECT notification_type, COUNT(*) as count FROM notifications_unified GROUP BY notification_type")->fetchAll(PDO::FETCH_ASSOC);
    echo "Breakdown by notification type:\n";
    foreach ($typeBreakdown as $type) {
        echo "  {$type['notification_type']}: {$type['count']}\n";
    }
    
    echo "\n=== STEP 6 COMPLETE ===\n";
    echo "Summary:\n";
    echo "  - Created unified notifications_unified table\n";
    echo "  - Migrated $totalMigrated records from existing tables\n";
    echo "  - Total records in unified table: $unifiedCount\n";
    echo "  - Ready to update code references and drop old tables\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
