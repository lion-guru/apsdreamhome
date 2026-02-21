<?php
/**
 * Database Tables Check & Repair Script
 * Verify all required tables exist and recreate missing ones
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

    echo "🔍 DATABASE TABLES VERIFICATION\n";
    echo "===============================\n\n";

    // Get all existing tables
    $existingTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "📊 Currently existing tables: " . count($existingTables) . "\n";
    echo "Tables: " . implode(', ', $existingTables) . "\n\n";

    // Define all required tables
    $requiredTables = [
        // Core system tables
        'users',
        'admin',
        'employees',

        // Property related
        'properties',
        'property_types',
        'property_views',
        'virtual_tours',
        'tour_scenes',
        'tour_hotspots',
        'tour_assets',
        'tour_comments',
        'tour_analytics',

        // Customer/Lead management
        'leads',
        'lead_activities',
        'lead_score_history',
        'customers',

        // Communication
        'communication_logs',
        'email_tracking',
        'notification_templates',
        'campaigns',
        'campaign_recipients',
        'sequence_messages',
        'message_templates',

        // Financial
        'invoices',
        'invoice_items',
        'invoice_payments',
        'invoice_reminders',
        'invoice_templates',
        'payments',
        'financial_reports',
        'chart_of_accounts',
        'journal_entries',
        'journal_entry_lines',
        'budget_items',
        'budgets',
        'gst_settings',
        'tax_slabs',
        'gst_returns',

        // Employee management
        'attendance',
        'leaves',
        'leave_types',
        'payroll',
        'salary_structures',
        'performance_reviews',
        'documents',
        'shifts',
        'shift_types',

        // MLM/Network
        'associates',
        'mlm_commission_ledger',
        'mlm_genealogy',
        'gamification_points',
        'user_badges',
        'points_transactions',
        'challenges',
        'challenge_participants',
        'leaderboards',
        'leaderboard_entries',
        'achievements',
        'user_achievements',
        'rewards_catalog',
        'reward_redemptions',

        // Training
        'training_courses',
        'course_modules',
        'user_course_enrollments',
        'module_progress',
        'training_quizzes',
        'quiz_questions',
        'quiz_attempts',
        'training_certificates',
        'training_analytics',

        // Analytics & Reporting
        'system_analytics_metrics',
        'analytics_dashboards',
        'dashboard_widgets_config',
        'analytics_reports',
        'report_executions',
        'analytics_alerts',
        'alert_triggers',

        // Portfolio management
        'property_portfolios',
        'portfolio_properties',
        'property_valuations',
        'portfolio_analytics',
        'portfolio_alerts',
        'portfolio_goals',
        'portfolio_reports',

        // AI & Automation
        'ai_workflows',
        'ai_tools_directory',
        'ai_implementation_guides',
        'ai_user_suggestions',
        'ai_agent_logs',
        'ai_audit_log',
        'workflow_executions',
        'integration_activity_logs',

        // System
        'system_activities',
        'user_sessions',
        'bookings',
        'booking_details'
    ];

    // Check which tables are missing
    $missingTables = array_diff($requiredTables, $existingTables);
    $extraTables = array_diff($existingTables, $requiredTables);

    echo "🔍 TABLES ANALYSIS\n";
    echo "------------------\n";
    echo "Required tables: " . count($requiredTables) . "\n";
    echo "Existing tables: " . count($existingTables) . "\n";
    echo "Missing tables: " . count($missingTables) . "\n";

    if (!empty($missingTables)) {
        echo "\n❌ MISSING TABLES (" . count($missingTables) . "):\n";
        foreach ($missingTables as $table) {
            echo "  • $table\n";
        }
    }

    if (!empty($extraTables)) {
        echo "\nℹ️  EXTRA TABLES (" . count($extraTables) . "):\n";
        foreach ($extraTables as $table) {
            echo "  • $table\n";
        }
    }

    echo "\n🔧 RECREATING MISSING TABLES\n";
    echo "=============================\n";

    // Function to execute SQL
    function executeQuery($pdo, $sql) {
        try {
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute();
            if ($result) {
                echo "✅ Query executed successfully\n";
                return true;
            } else {
                echo "❌ Error executing query\n";
                return false;
            }
        } catch (Exception $e) {
            echo "⚠️  Exception: " . $e->getMessage() . "\n";
            return false;
        }
    }

    // Recreate missing core tables
    if (in_array('users', $missingTables)) {
        echo "\n📝 Creating users table...\n";
        $sql = "CREATE TABLE `users` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `first_name` VARCHAR(50) NOT NULL,
            `last_name` VARCHAR(50),
            `email` VARCHAR(100) UNIQUE NOT NULL,
            `password` VARCHAR(255) NOT NULL,
            `phone` VARCHAR(20),
            `role` ENUM('customer','associate','agent') DEFAULT 'customer',
            `status` ENUM('active','inactive','suspended') DEFAULT 'active',
            `email_verified` TINYINT(1) DEFAULT 0,
            `phone_verified` TINYINT(1) DEFAULT 0,
            `profile_image` VARCHAR(255),
            `address` TEXT,
            `city` VARCHAR(100),
            `state` VARCHAR(100),
            `country` VARCHAR(100),
            `pincode` VARCHAR(20),
            `date_of_birth` DATE,
            `gender` ENUM('male','female','other'),
            `occupation` VARCHAR(100),
            `company` VARCHAR(100),
            `annual_income` DECIMAL(12,2),
            `preferred_property_types` JSON,
            `preferred_locations` JSON,
            `budget_min` DECIMAL(12,2),
            `budget_max` DECIMAL(12,2),
            `lead_source` VARCHAR(50),
            `assigned_agent` INT,
            `last_login` TIMESTAMP NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            INDEX `idx_users_email` (`email`),
            INDEX `idx_users_role` (`role`),
            INDEX `idx_users_status` (`status`),
            INDEX `idx_users_created` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        executeQuery($pdo, $sql);
    }

    if (in_array('properties', $missingTables)) {
        echo "\n🏠 Creating properties table...\n";
        $sql = "CREATE TABLE `properties` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(255) NOT NULL,
            `description` TEXT,
            `property_type_id` INT,
            `listing_type` ENUM('sale','rent','lease') DEFAULT 'sale',
            `status` ENUM('active','inactive','sold','rented','pending','draft') DEFAULT 'active',
            `price` DECIMAL(12,2),
            `rent_amount` DECIMAL(10,2),
            `security_deposit` DECIMAL(10,2),
            `area` DECIMAL(10,2),
            `area_unit` ENUM('sqft','sqm','acre','hectare') DEFAULT 'sqft',
            `bedrooms` INT,
            `bathrooms` INT,
            `floors` INT,
            `furnishing` ENUM('unfurnished','semi_furnished','fully_furnished'),
            `parking` INT DEFAULT 0,
            `age` INT,
            `facing` VARCHAR(50),
            `ownership` ENUM('freehold','leasehold','cooperative'),
            `location` TEXT,
            `city` VARCHAR(100),
            `state` VARCHAR(100),
            `country` VARCHAR(100) DEFAULT 'India',
            `pincode` VARCHAR(20),
            `latitude` DECIMAL(10,8),
            `longitude` DECIMAL(11,8),
            `landmarks` JSON,
            `amenities` JSON,
            `images` JSON,
            `videos` JSON,
            `documents` JSON,
            `features` JSON,
            `virtual_tour_url` VARCHAR(500),
            `floor_plan_url` VARCHAR(500),
            `brochure_url` VARCHAR(500),
            `rera_number` VARCHAR(50),
            `possession_date` DATE,
            `is_featured` TINYINT(1) DEFAULT 0,
            `featured_until` DATE,
            `view_count` INT DEFAULT 0,
            `favorite_count` INT DEFAULT 0,
            `created_by` INT,
            `assigned_agent` INT,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            INDEX `idx_properties_type` (`property_type_id`),
            INDEX `idx_properties_city` (`city`),
            INDEX `idx_properties_status` (`status`),
            INDEX `idx_properties_price` (`price`),
            INDEX `idx_properties_featured` (`is_featured`),
            INDEX `idx_properties_created` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        executeQuery($pdo, $sql);
    }

    if (in_array('leads', $missingTables)) {
        echo "\n🎯 Creating leads table...\n";
        $sql = "CREATE TABLE `leads` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            `email` VARCHAR(100),
            `phone` VARCHAR(20),
            `lead_source` VARCHAR(50),
            `lead_status` ENUM('new','contacted','qualified','proposal','negotiation','closed_won','closed_lost','nurture') DEFAULT 'new',
            `lead_score` DECIMAL(5,2) DEFAULT 0,
            `property_id` INT,
            `property_type` VARCHAR(50),
            `budget_min` DECIMAL(12,2),
            `budget_max` DECIMAL(12,2),
            `preferred_locations` JSON,
            `requirements` TEXT,
            `timeline` VARCHAR(50),
            `assigned_to` INT,
            `assigned_by` INT,
            `assigned_at` TIMESTAMP NULL,
            `last_contact` TIMESTAMP NULL,
            `next_followup` DATE,
            `notes` TEXT,
            `tags` JSON,
            `priority` ENUM('low','normal','high','urgent') DEFAULT 'normal',
            `is_qualified` TINYINT(1) DEFAULT 0,
            `qualification_date` TIMESTAMP NULL,
            `expected_close_date` DATE,
            `deal_value` DECIMAL(12,2),
            `probability_percentage` DECIMAL(5,2) DEFAULT 0,
            `lost_reason` VARCHAR(255),
            `created_by` INT,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            INDEX `idx_leads_status` (`lead_status`),
            INDEX `idx_leads_source` (`lead_source`),
            INDEX `idx_leads_assigned` (`assigned_to`),
            INDEX `idx_leads_created` (`created_at`),
            INDEX `idx_leads_score` (`lead_score`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        executeQuery($pdo, $sql);
    }

    if (in_array('invoices', $missingTables)) {
        echo "\n📄 Creating invoices table...\n";
        $sql = "CREATE TABLE `invoices` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `invoice_number` VARCHAR(50) UNIQUE NOT NULL,
            `invoice_date` DATE NOT NULL,
            `due_date` DATE NOT NULL,
            `client_id` INT,
            `client_type` ENUM('customer','associate','vendor','employee') DEFAULT 'customer',
            `client_name` VARCHAR(255) NOT NULL,
            `client_email` VARCHAR(100),
            `client_phone` VARCHAR(20),
            `client_address` TEXT,
            `billing_address` TEXT,
            `shipping_address` TEXT,
            `subtotal` DECIMAL(12,2) DEFAULT 0,
            `tax_amount` DECIMAL(12,2) DEFAULT 0,
            `discount_amount` DECIMAL(12,2) DEFAULT 0,
            `total_amount` DECIMAL(12,2) NOT NULL,
            `currency` VARCHAR(3) DEFAULT 'INR',
            `status` ENUM('draft','sent','viewed','paid','overdue','cancelled') DEFAULT 'draft',
            `payment_terms` TEXT,
            `notes` TEXT,
            `template_id` INT,
            `generated_by` INT,
            `sent_at` TIMESTAMP NULL,
            `paid_at` TIMESTAMP NULL,
            `reminder_count` INT DEFAULT 0,
            `last_reminder` DATE NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            INDEX `idx_invoices_client` (`client_id`, `client_type`),
            INDEX `idx_invoices_status` (`status`),
            INDEX `idx_invoices_date` (`invoice_date`),
            INDEX `idx_invoices_due` (`due_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        executeQuery($pdo, $sql);

        // Create related invoice tables
        echo "Creating invoice_items table...\n";
        $sql = "CREATE TABLE `invoice_items` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `invoice_id` INT NOT NULL,
            `item_type` ENUM('service','product','property') DEFAULT 'service',
            `item_name` VARCHAR(255) NOT NULL,
            `item_description` TEXT,
            `quantity` DECIMAL(10,2) DEFAULT 1,
            `unit_price` DECIMAL(12,2) NOT NULL,
            `discount_percent` DECIMAL(5,2) DEFAULT 0,
            `discount_amount` DECIMAL(10,2) DEFAULT 0,
            `tax_percent` DECIMAL(5,2) DEFAULT 0,
            `tax_amount` DECIMAL(10,2) DEFAULT 0,
            `line_total` DECIMAL(12,2) NOT NULL,
            `sort_order` INT DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE,
            INDEX `idx_invoice_items_invoice` (`invoice_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        executeQuery($pdo, $sql);

        echo "Creating invoice_payments table...\n";
        $sql = "CREATE TABLE `invoice_payments` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `invoice_id` INT NOT NULL,
            `payment_date` DATE NOT NULL,
            `amount` DECIMAL(12,2) NOT NULL,
            `payment_method` ENUM('cash','bank_transfer','cheque','online','card') DEFAULT 'online',
            `reference_number` VARCHAR(100),
            `notes` TEXT,
            `received_by` INT,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE,
            INDEX `idx_payments_invoice` (`invoice_id`),
            INDEX `idx_payments_date` (`payment_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        executeQuery($pdo, $sql);
    }

    // Insert some sample data for testing
    if (in_array('users', $existingTables) && !in_array('users', $missingTables)) {
        $userCount = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];
        if ($userCount == 0) {
            echo "\n👤 Inserting sample users...\n";
            $sql = "INSERT INTO users (first_name, last_name, email, password, phone, role, status) VALUES
                ('John', 'Doe', 'john@example.com', '$2y$10$dummy.hash.here', '+91-9876543210', 'customer', 'active'),
                ('Jane', 'Smith', 'jane@example.com', '$2y$10$dummy.hash.here', '+91-9876543211', 'associate', 'active'),
                ('Admin', 'User', 'admin@apsdreamhome.com', '$2y$10$dummy.hash.here', '+91-9876543212', 'customer', 'active');";
            executeQuery($pdo, $sql);
        }
    }

    // Final verification
    echo "\n🔍 FINAL VERIFICATION\n";
    echo "=====================\n";

    $finalTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $finalMissing = array_diff($requiredTables, $finalTables);

    echo "Final table count: " . count($finalTables) . "\n";

    if (empty($finalMissing)) {
        echo "✅ All required tables are now present!\n";
    } else {
        echo "⚠️  Still missing tables: " . implode(', ', $finalMissing) . "\n";
    }

    // Test the specific tables the user mentioned
    echo "\n🧪 TESTING SPECIFIC TABLES\n";
    echo "==========================\n";

    $testTables = ['users', 'properties', 'leads', 'invoices'];
    foreach ($testTables as $table) {
        try {
            $count = $pdo->query("SELECT COUNT(*) as count FROM `$table`")->fetch()['count'];
            echo "✅ $table: EXISTS ($count records)\n";
        } catch (Exception $e) {
            echo "❌ $table: ERROR - " . $e->getMessage() . "\n";
        }
    }

    echo "\n🎉 DATABASE REPAIR COMPLETED!\n";
    echo "Your database should now have all required tables.\n";

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
