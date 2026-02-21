<?php
/**
 * Script to create push notifications system tables
 */

// Database configuration
$host = 'localhost';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    echo "Connected to database successfully.\n";

    // Function to execute SQL queries
    function executeQuery($pdo, $sql) {
        try {
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute();
            if ($result) {
                echo "Query executed successfully\n";
                return true;
            } else {
                echo "Error executing query\n";
                return false;
            }
        } catch (Exception $e) {
            echo "Exception: " . $e->getMessage() . "\n";
            return false;
        }
    }

    // Create push notification templates table
    $sql = "CREATE TABLE IF NOT EXISTS `notification_templates` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `template_key` VARCHAR(100) NOT NULL UNIQUE,
        `name` VARCHAR(255) NOT NULL,
        `title` VARCHAR(255) NOT NULL,
        `message` TEXT NOT NULL,
        `category` ENUM('system','marketing','transactional','reminder','alert') DEFAULT 'system',
        `priority` ENUM('low','normal','high','urgent') DEFAULT 'normal',
        `icon` VARCHAR(100) NULL,
        `action_url` VARCHAR(500) NULL,
        `action_text` VARCHAR(50) NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_by` INT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_template_key` (`template_key`),
        INDEX `idx_template_category` (`category`),
        INDEX `idx_template_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Notification templates table created successfully!\n";
    }

    // Create user notification preferences table
    $sql = "CREATE TABLE IF NOT EXISTS `user_notification_preferences` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `user_type` ENUM('customer','employee','associate','admin') DEFAULT 'customer',
        `notification_type` VARCHAR(100) NOT NULL,
        `email_enabled` TINYINT(1) DEFAULT 1,
        `push_enabled` TINYINT(1) DEFAULT 1,
        `sms_enabled` TINYINT(1) DEFAULT 0,
        `whatsapp_enabled` TINYINT(1) DEFAULT 0,
        `frequency` ENUM('immediate','hourly','daily','weekly','never') DEFAULT 'immediate',
        `quiet_hours_start` TIME NULL,
        `quiet_hours_end` TIME NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        UNIQUE KEY `unique_user_notification` (`user_id`, `user_type`, `notification_type`),
        INDEX `idx_user_prefs` (`user_id`, `user_type`),
        INDEX `idx_notification_type` (`notification_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ User notification preferences table created successfully!\n";
    }

    // Create push subscriptions table (for web push)
    $sql = "CREATE TABLE IF NOT EXISTS `push_subscriptions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `user_type` ENUM('customer','employee','associate','admin') DEFAULT 'customer',
        `endpoint` VARCHAR(500) NOT NULL,
        `public_key` VARCHAR(100) NOT NULL,
        `auth_token` VARCHAR(50) NOT NULL,
        `user_agent` TEXT NULL,
        `ip_address` VARCHAR(45) NULL,
        `device_type` ENUM('desktop','mobile','tablet','unknown') DEFAULT 'unknown',
        `browser` VARCHAR(50) NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `last_used` TIMESTAMP NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        UNIQUE KEY `unique_subscription` (`user_id`, `user_type`, `endpoint`(100)),
        INDEX `idx_subscription_user` (`user_id`, `user_type`),
        INDEX `idx_subscription_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Push subscriptions table created successfully!\n";
    }

    // Create notification queue table
    $sql = "CREATE TABLE IF NOT EXISTS `notification_queue` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `notification_id` INT NULL,
        `user_id` INT NOT NULL,
        `user_type` ENUM('customer','employee','associate','admin') DEFAULT 'customer',
        `channel` ENUM('email','push','sms','whatsapp','in_app') NOT NULL,
        `template_key` VARCHAR(100) NULL,
        `title` VARCHAR(255) NOT NULL,
        `message` TEXT NOT NULL,
        `data` JSON NULL COMMENT 'Additional notification data',
        `priority` ENUM('low','normal','high','urgent') DEFAULT 'normal',
        `status` ENUM('queued','processing','sent','delivered','failed','cancelled') DEFAULT 'queued',
        `scheduled_at` TIMESTAMP NULL,
        `sent_at` TIMESTAMP NULL,
        `delivered_at` TIMESTAMP NULL,
        `error_message` TEXT NULL,
        `retry_count` INT DEFAULT 0,
        `max_retries` INT DEFAULT 3,
        `created_by` INT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_queue_user` (`user_id`, `user_type`),
        INDEX `idx_queue_channel` (`channel`),
        INDEX `idx_queue_status` (`status`),
        INDEX `idx_queue_scheduled` (`scheduled_at`),
        INDEX `idx_queue_priority` (`priority`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Notification queue table created successfully!\n";
    }

    // Create notification history table
    $sql = "CREATE TABLE IF NOT EXISTS `notification_history` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `user_type` ENUM('customer','employee','associate','admin') DEFAULT 'customer',
        `notification_type` VARCHAR(100) NOT NULL,
        `channel` ENUM('email','push','sms','whatsapp','in_app') NOT NULL,
        `title` VARCHAR(255) NOT NULL,
        `message` TEXT NOT NULL,
        `status` ENUM('sent','delivered','failed','read') DEFAULT 'sent',
        `reference_type` VARCHAR(50) NULL COMMENT 'e.g., invoice, property, lead',
        `reference_id` INT NULL,
        `sent_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `delivered_at` TIMESTAMP NULL,
        `read_at` TIMESTAMP NULL,
        `clicked_at` TIMESTAMP NULL,
        `device_info` JSON NULL,
        `ip_address` VARCHAR(45) NULL,

        INDEX `idx_history_user` (`user_id`, `user_type`),
        INDEX `idx_history_type` (`notification_type`),
        INDEX `idx_history_channel` (`channel`),
        INDEX `idx_history_reference` (`reference_type`, `reference_id`),
        INDEX `idx_history_sent` (`sent_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Notification history table created successfully!\n";
    }

    // Create notification campaigns table
    $sql = "CREATE TABLE IF NOT EXISTS `notification_campaigns` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `campaign_name` VARCHAR(255) NOT NULL,
        `campaign_type` ENUM('marketing','transactional','reminder','announcement') DEFAULT 'marketing',
        `target_audience` JSON NOT NULL COMMENT 'Criteria for target users',
        `template_key` VARCHAR(100) NOT NULL,
        `scheduled_at` TIMESTAMP NULL,
        `status` ENUM('draft','scheduled','running','completed','cancelled') DEFAULT 'draft',
        `total_recipients` INT DEFAULT 0,
        `sent_count` INT DEFAULT 0,
        `delivered_count` INT DEFAULT 0,
        `failed_count` INT DEFAULT 0,
        `opened_count` INT DEFAULT 0,
        `clicked_count` INT DEFAULT 0,
        `channels` JSON NOT NULL COMMENT 'Array of channels to use',
        `budget_limit` DECIMAL(10,2) NULL,
        `cost_per_notification` DECIMAL(5,2) NULL,
        `created_by` INT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_campaign_status` (`status`),
        INDEX `idx_campaign_type` (`campaign_type`),
        INDEX `idx_campaign_scheduled` (`scheduled_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Notification campaigns table created successfully!\n";
    }

    // Insert default notification templates
    $defaultTemplates = [
        ['welcome_customer', 'Welcome New Customer', 'Welcome to APS Dream Home!', 'Dear {name}, welcome to APS Dream Home! We\'re excited to help you find your dream property.', 'marketing', 'normal', 'fas fa-user-plus'],
        ['property_inquiry', 'Property Inquiry Response', 'New Property Inquiry', 'You have a new inquiry for property {property_title} from {customer_name}.', 'transactional', 'high', 'fas fa-home'],
        ['payment_received', 'Payment Received', 'Payment Confirmation', 'Dear {name}, we have received your payment of ₹{amount} for {description}.', 'transactional', 'normal', 'fas fa-rupee-sign'],
        ['invoice_overdue', 'Invoice Overdue Reminder', 'Payment Reminder', 'Dear {name}, your invoice {invoice_number} is overdue. Please make payment at your earliest convenience.', 'reminder', 'high', 'fas fa-exclamation-triangle'],
        ['shift_reminder', 'Shift Reminder', 'Upcoming Shift', 'Dear {name}, your shift starts at {start_time} tomorrow. Please be on time.', 'reminder', 'normal', 'fas fa-clock'],
        ['lead_assigned', 'Lead Assigned', 'New Lead Assigned', 'A new lead has been assigned to you: {customer_name} - {contact_number}', 'alert', 'high', 'fas fa-user-tag'],
        ['property_featured', 'Property Featured', 'Property Featured', 'Congratulations! Your property {property_title} has been featured on our platform.', 'marketing', 'normal', 'fas fa-star'],
        ['price_drop_alert', 'Price Drop Alert', 'Price Reduced!', 'Great news! The price of {property_title} has been reduced to ₹{new_price}.', 'marketing', 'normal', 'fas fa-tags']
    ];

    $insertSql = "INSERT IGNORE INTO `notification_templates` (`template_key`, `name`, `title`, `message`, `category`, `priority`, `icon`) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertSql);

    foreach ($defaultTemplates as $template) {
        $stmt->execute($template);
    }

    echo "✅ Default notification templates inserted successfully!\n";

    // Insert default notification preferences for different user types
    $defaultPreferences = [
        [1, 'customer', 'welcome', 1, 1, 0, 1, 'immediate'],
        [1, 'customer', 'property_updates', 1, 1, 0, 1, 'daily'],
        [1, 'customer', 'payment_reminders', 1, 1, 1, 1, 'immediate'],
        [1, 'employee', 'shift_reminders', 1, 1, 1, 1, 'daily'],
        [1, 'employee', 'leave_approvals', 1, 1, 0, 1, 'immediate'],
        [1, 'associate', 'commission_updates', 1, 1, 1, 1, 'weekly'],
        [1, 'associate', 'lead_assignments', 1, 1, 1, 1, 'immediate']
    ];

    $prefSql = "INSERT IGNORE INTO `user_notification_preferences` (`user_id`, `user_type`, `notification_type`, `email_enabled`, `push_enabled`, `sms_enabled`, `whatsapp_enabled`, `frequency`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($prefSql);

    foreach ($defaultPreferences as $pref) {
        $stmt->execute($pref);
    }

    echo "✅ Default notification preferences inserted successfully!\n";

    echo "\n🎉 Push notifications system database setup completed!\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
