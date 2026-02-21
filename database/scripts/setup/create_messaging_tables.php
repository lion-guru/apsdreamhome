<?php
/**
 * Script to create in-app messaging system tables
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

    // Create conversations table
    $sql = "CREATE TABLE IF NOT EXISTS `conversations` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `conversation_type` ENUM('direct','group','support','announcement') DEFAULT 'direct',
        `title` VARCHAR(255) NULL,
        `description` TEXT NULL,
        `created_by` INT NOT NULL,
        `created_by_type` ENUM('customer','employee','associate','admin') DEFAULT 'customer',
        `is_active` TINYINT(1) DEFAULT 1,
        `is_archived` TINYINT(1) DEFAULT 0,
        `last_message_at` TIMESTAMP NULL,
        `last_message_preview` VARCHAR(500) NULL,
        `metadata` JSON NULL COMMENT 'Additional conversation metadata',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_conversation_type` (`conversation_type`),
        INDEX `idx_conversation_active` (`is_active`),
        INDEX `idx_conversation_archived` (`is_archived`),
        INDEX `idx_conversation_last_message` (`last_message_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Conversations table created successfully!\n";
    }

    // Create conversation participants table
    $sql = "CREATE TABLE IF NOT EXISTS `conversation_participants` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `conversation_id` INT NOT NULL,
        `user_id` INT NOT NULL,
        `user_type` ENUM('customer','employee','associate','admin') DEFAULT 'customer',
        `role` ENUM('owner','admin','member') DEFAULT 'member',
        `is_active` TINYINT(1) DEFAULT 1,
        `joined_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `last_seen_at` TIMESTAMP NULL,
        `unread_count` INT DEFAULT 0,
        `is_muted` TINYINT(1) DEFAULT 0,
        `muted_until` TIMESTAMP NULL,

        FOREIGN KEY (`conversation_id`) REFERENCES `conversations`(`id`) ON DELETE CASCADE,
        UNIQUE KEY `unique_participant` (`conversation_id`, `user_id`, `user_type`),
        INDEX `idx_participant_conversation` (`conversation_id`),
        INDEX `idx_participant_user` (`user_id`, `user_type`),
        INDEX `idx_participant_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Conversation participants table created successfully!\n";
    }

    // Create messages table
    $sql = "CREATE TABLE IF NOT EXISTS `messages` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `conversation_id` INT NOT NULL,
        `sender_id` INT NOT NULL,
        `sender_type` ENUM('customer','employee','associate','admin') DEFAULT 'customer',
        `message_type` ENUM('text','image','file','location','contact','property','invoice','system') DEFAULT 'text',
        `content` TEXT NULL,
        `metadata` JSON NULL COMMENT 'Message metadata (file info, location, etc.)',
        `reply_to_message_id` INT NULL,
        `is_edited` TINYINT(1) DEFAULT 0,
        `edited_at` TIMESTAMP NULL,
        `is_deleted` TINYINT(1) DEFAULT 0,
        `deleted_at` TIMESTAMP NULL,
        `deleted_by` INT NULL,
        `sent_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `delivered_at` TIMESTAMP NULL,
        `read_at` TIMESTAMP NULL,

        FOREIGN KEY (`conversation_id`) REFERENCES `conversations`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`reply_to_message_id`) REFERENCES `messages`(`id`) ON DELETE SET NULL,
        INDEX `idx_message_conversation` (`conversation_id`),
        INDEX `idx_message_sender` (`sender_id`, `sender_type`),
        INDEX `idx_message_type` (`message_type`),
        INDEX `idx_message_sent` (`sent_at`),
        INDEX `idx_message_deleted` (`is_deleted`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Messages table created successfully!\n";
    }

    // Create message attachments table
    $sql = "CREATE TABLE IF NOT EXISTS `message_attachments` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `message_id` INT NOT NULL,
        `file_name` VARCHAR(255) NOT NULL,
        `file_path` VARCHAR(500) NOT NULL,
        `file_size` INT NOT NULL,
        `mime_type` VARCHAR(100) NOT NULL,
        `file_type` ENUM('image','document','video','audio','other') DEFAULT 'other',
        `thumbnail_path` VARCHAR(500) NULL,
        `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (`message_id`) REFERENCES `messages`(`id`) ON DELETE CASCADE,
        INDEX `idx_attachment_message` (`message_id`),
        INDEX `idx_attachment_type` (`file_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Message attachments table created successfully!\n";
    }

    // Create message reactions table
    $sql = "CREATE TABLE IF NOT EXISTS `message_reactions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `message_id` INT NOT NULL,
        `user_id` INT NOT NULL,
        `user_type` ENUM('customer','employee','associate','admin') DEFAULT 'customer',
        `reaction_type` VARCHAR(50) NOT NULL COMMENT 'emoji or reaction type',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (`message_id`) REFERENCES `messages`(`id`) ON DELETE CASCADE,
        UNIQUE KEY `unique_reaction` (`message_id`, `user_id`, `user_type`, `reaction_type`),
        INDEX `idx_reaction_message` (`message_id`),
        INDEX `idx_reaction_user` (`user_id`, `user_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Message reactions table created successfully!\n";
    }

    // Create typing indicators table
    $sql = "CREATE TABLE IF NOT EXISTS `typing_indicators` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `conversation_id` INT NOT NULL,
        `user_id` INT NOT NULL,
        `user_type` ENUM('customer','employee','associate','admin') DEFAULT 'customer',
        `started_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        FOREIGN KEY (`conversation_id`) REFERENCES `conversations`(`id`) ON DELETE CASCADE,
        UNIQUE KEY `unique_typing` (`conversation_id`, `user_id`, `user_type`),
        INDEX `idx_typing_conversation` (`conversation_id`),
        INDEX `idx_typing_updated` (`last_updated`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Typing indicators table created successfully!\n";
    }

    // Create message templates table
    $sql = "CREATE TABLE IF NOT EXISTS `message_templates` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `template_key` VARCHAR(100) NOT NULL UNIQUE,
        `name` VARCHAR(255) NOT NULL,
        `category` ENUM('greeting','support','sales','announcement','reminder') DEFAULT 'support',
        `content` TEXT NOT NULL,
        `variables` JSON NULL COMMENT 'Available variables in template',
        `is_active` TINYINT(1) DEFAULT 1,
        `created_by` INT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_template_key` (`template_key`),
        INDEX `idx_template_category` (`category`),
        INDEX `idx_template_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Message templates table created successfully!\n";
    }

    // Create quick replies table
    $sql = "CREATE TABLE IF NOT EXISTS `quick_replies` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `user_type` ENUM('customer','employee','associate','admin') DEFAULT 'customer',
        `title` VARCHAR(100) NOT NULL,
        `content` TEXT NOT NULL,
        `category` VARCHAR(50) NULL,
        `usage_count` INT DEFAULT 0,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_quick_reply_user` (`user_id`, `user_type`),
        INDEX `idx_quick_reply_category` (`category`),
        INDEX `idx_quick_reply_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Quick replies table created successfully!\n";
    }

    // Insert default message templates
    $defaultTemplates = [
        ['greeting_customer', 'Customer Greeting', 'greeting', 'Hello {customer_name}! Welcome to APS Dream Home. How can I help you find your perfect property today?', '["customer_name"]'],
        ['property_inquiry_response', 'Property Inquiry Response', 'sales', 'Thank you for your interest in {property_title}. This property is located in {location} and is priced at ₹{price}. Would you like to schedule a viewing?', '["property_title","location","price"]'],
        ['support_acknowledgment', 'Support Acknowledgment', 'support', 'Hi {customer_name}, thank you for reaching out. We have received your message and our support team will get back to you within 24 hours.', '["customer_name"]'],
        ['payment_reminder', 'Payment Reminder', 'reminder', 'Dear {customer_name}, this is a friendly reminder that your payment of ₹{amount} for {description} is due on {due_date}.', '["customer_name","amount","description","due_date"]'],
        ['booking_confirmation', 'Booking Confirmation', 'sales', 'Great news {customer_name}! Your property viewing for {property_title} has been confirmed for {date} at {time}. Our representative will meet you at the location.', '["customer_name","property_title","date","time"]']
    ];

    $insertTemplateSql = "INSERT IGNORE INTO `message_templates` (`template_key`, `name`, `category`, `content`, `variables`) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertTemplateSql);

    foreach ($defaultTemplates as $template) {
        $stmt->execute($template);
    }

    echo "✅ Default message templates inserted successfully!\n";

    echo "\n🎉 In-app messaging system database setup completed!\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
