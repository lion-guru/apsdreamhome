<?php
/**
 * Scheduler & File Management Migration
 * Creates tables for Task Scheduler and File Manager
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Database\Database;

echo "🚀 Creating Scheduler & File Management Tables...\n\n";

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Scheduled tasks
    echo "⏰ Creating scheduled_tasks table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS scheduled_tasks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT NULL,
        command VARCHAR(255) NOT NULL,
        schedule VARCHAR(50) NOT NULL,
        timezone VARCHAR(50) DEFAULT 'Asia/Kolkata',
        is_active TINYINT(1) DEFAULT 1,
        last_run_at TIMESTAMP NULL,
        next_run_at TIMESTAMP NULL,
        last_status ENUM('success', 'failed', 'running') NULL,
        last_output TEXT NULL,
        last_error TEXT NULL,
        run_count INT DEFAULT 0,
        fail_count INT DEFAULT 0,
        timeout_seconds INT DEFAULT 300,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_name (name),
        INDEX idx_active (is_active),
        INDEX idx_next_run (next_run_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Task execution logs
    echo "📝 Creating task_execution_logs table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS task_execution_logs (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        task_id INT NOT NULL,
        started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        finished_at TIMESTAMP NULL,
        status ENUM('success', 'failed', 'timeout', 'killed') NOT NULL,
        output TEXT NULL,
        error TEXT NULL,
        execution_time_seconds INT NULL,
        memory_usage_mb INT NULL,
        INDEX idx_task (task_id),
        INDEX idx_status (status),
        INDEX idx_started (started_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Task dependencies
    echo "🔗 Creating task_dependencies table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS task_dependencies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        task_id INT NOT NULL,
        depends_on_task_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_dep (task_id, depends_on_task_id),
        INDEX idx_task (task_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Files table
    echo "📁 Creating files table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS files (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        uuid VARCHAR(36) NOT NULL UNIQUE,
        original_name VARCHAR(255) NOT NULL,
        file_name VARCHAR(255) NOT NULL,
        file_path VARCHAR(500) NOT NULL,
        file_type VARCHAR(50) NOT NULL,
        file_category ENUM('property', 'user', 'document', 'payment', 'general') DEFAULT 'general',
        mime_type VARCHAR(100) NULL,
        extension VARCHAR(10) NULL,
        size_bytes BIGINT NOT NULL,
        checksum VARCHAR(64) NULL,
        uploaded_by INT NULL,
        uploaded_by_type ENUM('customer', 'associate', 'agent', 'admin') NULL,
        entity_type VARCHAR(50) NULL,
        entity_id INT NULL,
        is_public TINYINT(1) DEFAULT 0,
        is_versioned TINYINT(1) DEFAULT 0,
        version_number INT DEFAULT 1,
        parent_file_id BIGINT NULL,
        metadata JSON NULL,
        description TEXT NULL,
        tags JSON NULL,
        download_count INT DEFAULT 0,
        last_downloaded_at TIMESTAMP NULL,
        expires_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_uuid (uuid),
        INDEX idx_category (file_category),
        INDEX idx_entity (entity_type, entity_id),
        INDEX idx_uploaded (uploaded_by, uploaded_by_type),
        INDEX idx_type (file_type),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // File versions
    echo "📂 Creating file_versions table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS file_versions (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        file_id BIGINT NOT NULL,
        version_number INT NOT NULL,
        file_name VARCHAR(255) NOT NULL,
        file_path VARCHAR(500) NOT NULL,
        size_bytes BIGINT NOT NULL,
        checksum VARCHAR(64) NULL,
        created_by INT NULL,
        change_notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_version (file_id, version_number),
        INDEX idx_file (file_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // File shares
    echo "🔗 Creating file_shares table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS file_shares (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        file_id BIGINT NOT NULL,
        shared_by INT NOT NULL,
        shared_with_email VARCHAR(255) NULL,
        shared_with_user_id INT NULL,
        share_token VARCHAR(64) NOT NULL,
        permissions ENUM('view', 'download', 'edit') DEFAULT 'view',
        password_hash VARCHAR(255) NULL,
        expires_at TIMESTAMP NULL,
        access_count INT DEFAULT 0,
        last_accessed_at TIMESTAMP NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_token (share_token),
        INDEX idx_file (file_id),
        INDEX idx_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // File access logs
    echo "📊 Creating file_access_logs table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS file_access_logs (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        file_id BIGINT NOT NULL,
        user_id INT NULL,
        user_type VARCHAR(20) NULL,
        action ENUM('view', 'download', 'upload', 'delete', 'share', 'version') NOT NULL,
        ip_address VARCHAR(45) NULL,
        user_agent TEXT NULL,
        success TINYINT(1) DEFAULT 1,
        error_message TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_file (file_id),
        INDEX idx_action (action),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // File tags
    echo "🏷️ Creating file_tags table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS file_tags (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE,
        color VARCHAR(7) DEFAULT '#6c757d',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // File-tag relations
    echo "🔗 Creating file_tag_relations table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS file_tag_relations (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        file_id BIGINT NOT NULL,
        tag_id INT NOT NULL,
        UNIQUE KEY unique_relation (file_id, tag_id),
        INDEX idx_file (file_id),
        INDEX idx_tag (tag_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Seed default scheduled tasks
    echo "\n🌱 Seeding default scheduled tasks...\n";
    $tasks = [
        ['backup_database', 'Database Backup', 'App\\Jobs\\BackupDatabaseJob', '0 2 * * *', 3600],
        ['cleanup_old_data', 'Cleanup Old Data', 'App\\Jobs\\CleanupJob', '0 3 * * 0', 1800],
        ['send_emi_reminders', 'Send EMI Reminders', 'App\\Jobs\\SendEmiRemindersJob', '0 9 * * *', 1800],
        ['process_property_alerts', 'Process Property Alerts', 'App\\Jobs\\ProcessPropertyAlertsJob', '0 */6 * * *', 3600],
        ['generate_daily_report', 'Generate Daily Report', 'App\\Jobs\\GenerateDailyReportJob', '0 8 * * *', 1800],
        ['cleanup_expired_tokens', 'Cleanup Expired Tokens', 'App\\Jobs\\CleanupTokensJob', '0 4 * * *', 600],
        ['update_search_index', 'Update Search Index', 'App\\Jobs\\UpdateSearchIndexJob', '0 1 * * *', 3600],
        ['send_followup_emails', 'Send Follow-up Emails', 'App\\Jobs\\SendFollowupEmailsJob', '0 10,15 * * *', 1800]
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO scheduled_tasks 
        (name, description, command, schedule, timeout_seconds)
        VALUES (?, ?, ?, ?, ?)");
    
    foreach ($tasks as $task) {
        $stmt->execute([$task[0], $task[1] . ' - Daily scheduled task', $task[2], $task[3], $task[4]]);
    }
    
    echo "\n✅ Scheduler & File Management tables created successfully!\n";
    echo "📊 Summary:\n";
    echo "   - scheduled_tasks (8 default tasks)\n";
    echo "   - task_execution_logs\n";
    echo "   - task_dependencies\n";
    echo "   - files\n";
    echo "   - file_versions\n";
    echo "   - file_shares\n";
    echo "   - file_access_logs\n";
    echo "   - file_tags\n";
    echo "   - file_tag_relations\n";
    echo "\n🎉 Total: 9 new tables!\n";
    
} catch (\Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
