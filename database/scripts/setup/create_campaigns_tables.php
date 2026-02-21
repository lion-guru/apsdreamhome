<?php
/**
 * Script to create automated follow-ups and drip campaigns system tables
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

    // Create campaigns table
    $sql = "CREATE TABLE IF NOT EXISTS `campaigns` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `campaign_name` VARCHAR(255) NOT NULL,
        `campaign_type` ENUM('drip','followup','nurture','reengagement','announcement') DEFAULT 'drip',
        `campaign_description` TEXT NULL,
        `target_audience` JSON NOT NULL COMMENT 'Criteria for target leads/customers',
        `status` ENUM('draft','active','paused','completed','cancelled') DEFAULT 'draft',
        `schedule_type` ENUM('immediate','scheduled','event_triggered','behavior_triggered') DEFAULT 'scheduled',
        `start_date` DATETIME NULL,
        `end_date` DATETIME NULL,
        `timezone` VARCHAR(50) DEFAULT 'Asia/Kolkata',
        `total_recipients` INT DEFAULT 0,
        `sent_count` INT DEFAULT 0,
        `open_count` INT DEFAULT 0,
        `click_count` INT DEFAULT 0,
        `conversion_count` INT DEFAULT 0,
        `unsubscribe_count` INT DEFAULT 0,
        `bounce_count` INT DEFAULT 0,
        `budget_limit` DECIMAL(10,2) NULL,
        `cost_per_send` DECIMAL(5,2) NULL,
        `created_by` INT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_campaign_type` (`campaign_type`),
        INDEX `idx_campaign_status` (`status`),
        INDEX `idx_campaign_schedule` (`schedule_type`),
        INDEX `idx_campaign_dates` (`start_date`, `end_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Campaigns table created successfully!\n";
    }

    // Create campaign sequences table
    $sql = "CREATE TABLE IF NOT EXISTS `campaign_sequences` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `campaign_id` INT NOT NULL,
        `sequence_name` VARCHAR(255) NOT NULL,
        `sequence_order` INT DEFAULT 0,
        `delay_days` INT DEFAULT 0,
        `delay_hours` INT DEFAULT 0,
        `delay_minutes` INT DEFAULT 0,
        `trigger_event` VARCHAR(100) NULL COMMENT 'Event that triggers this sequence',
        `condition_rules` JSON NULL COMMENT 'Conditions that must be met',
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (`campaign_id`) REFERENCES `campaigns`(`id`) ON DELETE CASCADE,
        INDEX `idx_sequence_campaign` (`campaign_id`),
        INDEX `idx_sequence_order` (`sequence_order`),
        INDEX `idx_sequence_trigger` (`trigger_event`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Campaign sequences table created successfully!\n";
    }

    // Create sequence messages table
    $sql = "CREATE TABLE IF NOT EXISTS `sequence_messages` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `sequence_id` INT NOT NULL,
        `message_name` VARCHAR(255) NOT NULL,
        `channels` JSON NOT NULL COMMENT 'Array of channels: email, sms, whatsapp, push',
        `subject` VARCHAR(255) NULL,
        `content` LONGTEXT NOT NULL,
        `template_id` INT NULL,
        `attachments` JSON NULL COMMENT 'Array of attachment file paths',
        `priority` ENUM('low','normal','high','urgent') DEFAULT 'normal',
        `track_opens` TINYINT(1) DEFAULT 1,
        `track_clicks` TINYINT(1) DEFAULT 1,
        `a_b_test_enabled` TINYINT(1) DEFAULT 0,
        `a_b_test_percentage` DECIMAL(5,2) DEFAULT 50.00,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        FOREIGN KEY (`sequence_id`) REFERENCES `campaign_sequences`(`id`) ON DELETE CASCADE,
        INDEX `idx_message_sequence` (`sequence_id`),
        INDEX `idx_message_template` (`template_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Sequence messages table created successfully!\n";
    }

    // Create campaign recipients table
    $sql = "CREATE TABLE IF NOT EXISTS `campaign_recipients` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `campaign_id` INT NOT NULL,
        `lead_id` INT NULL,
        `customer_id` INT NULL,
        `user_type` ENUM('lead','customer','employee','associate') DEFAULT 'lead',
        `email` VARCHAR(255) NULL,
        `phone` VARCHAR(20) NULL,
        `status` ENUM('pending','sent','delivered','opened','clicked','converted','bounced','unsubscribed','complained') DEFAULT 'pending',
        `sent_at` DATETIME NULL,
        `delivered_at` DATETIME NULL,
        `opened_at` DATETIME NULL,
        `clicked_at` DATETIME NULL,
        `converted_at` DATETIME NULL,
        `unsubscribed_at` DATETIME NULL,
        `bounce_reason` TEXT NULL,
        `metadata` JSON NULL COMMENT 'Additional recipient data',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        FOREIGN KEY (`campaign_id`) REFERENCES `campaigns`(`id`) ON DELETE CASCADE,
        INDEX `idx_recipient_campaign` (`campaign_id`),
        INDEX `idx_recipient_lead` (`lead_id`),
        INDEX `idx_recipient_customer` (`customer_id`),
        INDEX `idx_recipient_status` (`status`),
        INDEX `idx_recipient_email` (`email`),
        INDEX `idx_recipient_phone` (`phone`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Campaign recipients table created successfully!\n";
    }

    // Create message templates table
    $sql = "CREATE TABLE IF NOT EXISTS `message_templates` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `template_name` VARCHAR(255) NOT NULL,
        `template_type` ENUM('email','sms','whatsapp','push') NOT NULL,
        `category` ENUM('welcome','followup','nurture','promotion','reminder','announcement') DEFAULT 'followup',
        `subject_template` VARCHAR(255) NULL,
        `content_template` LONGTEXT NOT NULL,
        `variables` JSON NULL COMMENT 'Available template variables',
        `thumbnail` VARCHAR(500) NULL,
        `is_default` TINYINT(1) DEFAULT 0,
        `is_active` TINYINT(1) DEFAULT 1,
        `usage_count` INT DEFAULT 0,
        `created_by` INT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_template_type` (`template_type`),
        INDEX `idx_template_category` (`category`),
        INDEX `idx_template_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Message templates table created successfully!\n";
    }

    // Create campaign analytics table
    $sql = "CREATE TABLE IF NOT EXISTS `campaign_analytics` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `campaign_id` INT NOT NULL,
        `date` DATE NOT NULL,
        `sent_count` INT DEFAULT 0,
        `delivered_count` INT DEFAULT 0,
        `open_count` INT DEFAULT 0,
        `click_count` INT DEFAULT 0,
        `bounce_count` INT DEFAULT 0,
        `unsubscribe_count` INT DEFAULT 0,
        `complaint_count` INT DEFAULT 0,
        `conversion_count` INT DEFAULT 0,
        `revenue_generated` DECIMAL(15,2) DEFAULT 0,
        `cost_incurred` DECIMAL(10,2) DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (`campaign_id`) REFERENCES `campaigns`(`id`) ON DELETE CASCADE,
        UNIQUE KEY `unique_campaign_date` (`campaign_id`, `date`),
        INDEX `idx_analytics_campaign` (`campaign_id`),
        INDEX `idx_analytics_date` (`date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Campaign analytics table created successfully!\n";
    }

    // Create automated triggers table
    $sql = "CREATE TABLE IF NOT EXISTS `automated_triggers` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `trigger_name` VARCHAR(255) NOT NULL,
        `trigger_type` ENUM('lead_score_change','behavior_event','time_based','property_view','inquiry_submit','meeting_book','payment_receive') NOT NULL,
        `conditions` JSON NOT NULL COMMENT 'Trigger conditions',
        `actions` JSON NOT NULL COMMENT 'Actions to perform when triggered',
        `is_active` TINYINT(1) DEFAULT 1,
        `priority` INT DEFAULT 0,
        `cooldown_hours` INT DEFAULT 24 COMMENT 'Hours to wait before triggering again',
        `max_executions` INT NULL COMMENT 'Maximum times this trigger can execute',
        `executed_count` INT DEFAULT 0,
        `created_by` INT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_trigger_type` (`trigger_type`),
        INDEX `idx_trigger_active` (`is_active`),
        INDEX `idx_trigger_priority` (`priority`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Automated triggers table created successfully!\n";
    }

    // Insert default message templates
    $defaultTemplates = [
        [
            'Welcome Email - New Lead',
            'email',
            'welcome',
            'Welcome to APS Dream Home - Your Property Journey Begins!',
            '<h2>Welcome to APS Dream Home!</h2><p>Dear {lead_name},</p><p>Thank you for your interest in our properties. We\'re excited to help you find your dream home.</p><p>Our team of experts is here to assist you every step of the way.</p><p>Best regards,<br>APS Dream Home Team</p>',
            '["lead_name","property_interest","contact_number"]',
            null,
            1,
            1,
            0
        ],
        [
            'Follow-up SMS',
            'sms',
            'followup',
            null,
            'Hi {lead_name}! We noticed you were interested in our {property_type} properties. Ready to schedule a viewing? Call us at {contact_number}',
            '["lead_name","property_type","contact_number"]',
            null,
            1,
            1,
            0
        ],
        [
            'Property Reminder',
            'email',
            'reminder',
            'Don\'t Forget About Your Dream Property!',
            '<p>Hi {lead_name},</p><p>We wanted to follow up on your interest in our {property_title}. Properties like this don\'t stay available for long!</p><p>Would you like to schedule a viewing or learn more about financing options?</p><p>Best,<br>Your Property Consultant</p>',
            '["lead_name","property_title","property_location"]',
            null,
            1,
            1,
            0
        ],
        [
            'Meeting Confirmation',
            'whatsapp',
            'reminder',
            null,
            'Hi {lead_name}! This is a reminder about your property viewing scheduled for {meeting_date} at {meeting_time}. We\'re looking forward to showing you {property_title} at {property_location}. Please confirm your attendance.',
            '["lead_name","meeting_date","meeting_time","property_title","property_location"]',
            null,
            1,
            1,
            0
        ]
    ];

    $insertTemplateSql = "INSERT IGNORE INTO `message_templates` (`template_name`, `template_type`, `category`, `subject_template`, `content_template`, `variables`, `thumbnail`, `is_default`, `is_active`, `usage_count`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertTemplateSql);

    foreach ($defaultTemplates as $template) {
        $stmt->execute($template);
    }

    echo "✅ Default message templates inserted successfully!\n";

    // Insert default automated triggers
    $defaultTriggers = [
        [
            'High Score Lead Follow-up',
            'lead_score_change',
            '{"score_threshold": 70, "grade": "A"}',
            '{"send_email": true, "email_template": "Welcome Email - New Lead", "assign_to_agent": true, "create_task": true}',
            1,
            1,
            24,
            null,
            0
        ],
        [
            'Property Inquiry Follow-up',
            'behavior_event',
            '{"event_type": "inquiry_submit", "wait_hours": 2}',
            '{"send_email": true, "email_template": "Follow-up Email", "schedule_call": true}',
            1,
            2,
            24,
            null,
            0
        ],
        [
            'Meeting Reminder',
            'time_based',
            '{"event_type": "meeting_scheduled", "remind_hours_before": 24}',
            '{"send_sms": true, "sms_template": "Meeting Confirmation"}',
            1,
            3,
            24,
            null,
            0
        ]
    ];

    $insertTriggerSql = "INSERT IGNORE INTO `automated_triggers` (`trigger_name`, `trigger_type`, `conditions`, `actions`, `is_active`, `priority`, `cooldown_hours`, `max_executions`, `executed_count`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertTriggerSql);

    foreach ($defaultTriggers as $trigger) {
        $stmt->execute($trigger);
    }

    echo "✅ Default automated triggers inserted successfully!\n";

    echo "\n🎉 Automated follow-ups and drip campaigns system database setup completed!\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
