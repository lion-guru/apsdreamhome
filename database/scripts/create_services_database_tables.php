<?php
/**
 * APS Dream Home - Complete Services Database Tables Creation
 * Creates all missing database tables for the advanced services
 */

// Database connection
$host = 'localhost';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

echo "🔧 APS DREAM HOME - SERVICES DATABASE TABLES CREATION\n";
echo "=====================================================\n\n";

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    echo "✅ Database connection established\n\n";

    // Tables to create
    $tablesToCreate = [
        // WhatsApp Integration Tables
        'whatsapp_messages' => "
            CREATE TABLE IF NOT EXISTS whatsapp_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                phone_number VARCHAR(20) NOT NULL,
                message TEXT NOT NULL,
                direction ENUM('inbound', 'outbound') DEFAULT 'outbound',
                message_type ENUM('text', 'image', 'document', 'location', 'contact') DEFAULT 'text',
                status ENUM('sent', 'delivered', 'read', 'failed') DEFAULT 'sent',
                whatsapp_message_id VARCHAR(255) UNIQUE,
                media_url VARCHAR(500) NULL,
                media_caption TEXT NULL,
                latitude DECIMAL(10,8) NULL,
                longitude DECIMAL(11,8) NULL,
                contact_name VARCHAR(255) NULL,
                contact_phone VARCHAR(20) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_phone_status (phone_number, status),
                INDEX idx_direction_created (direction, created_at),
                INDEX idx_whatsapp_id (whatsapp_message_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ",

        'whatsapp_templates' => "
            CREATE TABLE IF NOT EXISTS whatsapp_templates (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL UNIQUE,
                content TEXT NOT NULL,
                category VARCHAR(50) DEFAULT 'general',
                language VARCHAR(10) DEFAULT 'en',
                template_type ENUM('text', 'media', 'interactive') DEFAULT 'text',
                variables JSON NULL,
                media_url VARCHAR(500) NULL,
                buttons JSON NULL,
                is_active BOOLEAN DEFAULT TRUE,
                usage_count INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_category_active (category, is_active),
                INDEX idx_name (name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ",

        // Lead Scoring Enhancement Tables
        'email_tracking' => "
            CREATE TABLE IF NOT EXISTS email_tracking (
                id INT AUTO_INCREMENT PRIMARY KEY,
                lead_id INT,
                email VARCHAR(255) NOT NULL,
                campaign_id VARCHAR(100) NULL,
                email_type ENUM('lead_nurture', 'property_alert', 'follow_up', 'newsletter') DEFAULT 'lead_nurture',
                opens INT DEFAULT 0,
                clicks INT DEFAULT 0,
                last_opened TIMESTAMP NULL,
                last_clicked TIMESTAMP NULL,
                first_opened TIMESTAMP NULL,
                first_clicked TIMESTAMP NULL,
                unsubscribed BOOLEAN DEFAULT FALSE,
                bounced BOOLEAN DEFAULT FALSE,
                spam_reported BOOLEAN DEFAULT FALSE,
                user_agent TEXT NULL,
                ip_address VARCHAR(45) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
                INDEX idx_lead_email (lead_id, email),
                INDEX idx_email_type (email_type),
                INDEX idx_opens_clicks (opens, clicks),
                INDEX idx_campaign (campaign_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ",

        'lead_visits' => "
            CREATE TABLE IF NOT EXISTS lead_visits (
                id INT AUTO_INCREMENT PRIMARY KEY,
                lead_id INT,
                property_id INT,
                visit_type ENUM('property_page', 'virtual_tour', 'video_call', 'site_visit') DEFAULT 'property_page',
                visit_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                duration_seconds INT DEFAULT 0,
                source VARCHAR(100) DEFAULT 'direct',
                referrer_url VARCHAR(500) NULL,
                user_agent TEXT NULL,
                ip_address VARCHAR(45) NULL,
                device_type ENUM('desktop', 'mobile', 'tablet') DEFAULT 'desktop',
                browser VARCHAR(100) NULL,
                country VARCHAR(100) NULL,
                city VARCHAR(100) NULL,
                latitude DECIMAL(10,8) NULL,
                longitude DECIMAL(11,8) NULL,
                session_id VARCHAR(255) NULL,
                page_views INT DEFAULT 1,
                interest_score DECIMAL(3,2) DEFAULT 0.00,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
                FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
                INDEX idx_lead_property (lead_id, property_id),
                INDEX idx_visit_date (visit_date),
                INDEX idx_source_type (source, visit_type),
                INDEX idx_duration (duration_seconds),
                INDEX idx_session (session_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ",

        // Gamification Tables
        'badges' => "
            CREATE TABLE IF NOT EXISTS badges (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL UNIQUE,
                display_name VARCHAR(150) NOT NULL,
                description TEXT,
                icon VARCHAR(255) NULL,
                category ENUM('achievement', 'milestone', 'social', 'expertise', 'loyalty') DEFAULT 'achievement',
                points_required INT DEFAULT 0,
                rarity ENUM('common', 'uncommon', 'rare', 'epic', 'legendary') DEFAULT 'common',
                is_active BOOLEAN DEFAULT TRUE,
                is_hidden BOOLEAN DEFAULT FALSE,
                prerequisites JSON NULL,
                rewards JSON NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_category_active (category, is_active),
                INDEX idx_rarity (rarity),
                INDEX idx_points_required (points_required)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ",

        'user_points' => "
            CREATE TABLE IF NOT EXISTS user_points (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                points INT DEFAULT 0,
                points_type ENUM('earned', 'bonus', 'penalty', 'redeemed') DEFAULT 'earned',
                source VARCHAR(150) NOT NULL,
                source_id INT NULL,
                description TEXT NULL,
                reference_type ENUM('property_view', 'inquiry', 'booking', 'review', 'referral', 'training', 'social_share', 'badge_earned', 'milestone', 'admin_bonus') NULL,
                earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at TIMESTAMP NULL,
                is_expired BOOLEAN DEFAULT FALSE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_user_points (user_id, points),
                INDEX idx_points_type (points_type),
                INDEX idx_source (source),
                INDEX idx_earned_at (earned_at),
                INDEX idx_expires_at (expires_at),
                INDEX idx_reference_type (reference_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ",

        // Training Enhancement Tables
        'training_lessons' => "
            CREATE TABLE IF NOT EXISTS training_lessons (
                id INT AUTO_INCREMENT PRIMARY KEY,
                module_id INT,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255) UNIQUE,
                content LONGTEXT NULL,
                summary TEXT NULL,
                video_url VARCHAR(500) NULL,
                video_type ENUM('youtube', 'vimeo', 'upload', 'external') DEFAULT 'upload',
                video_duration INT DEFAULT 0,
                duration_minutes INT DEFAULT 0,
                order_index INT DEFAULT 0,
                lesson_type ENUM('video', 'text', 'quiz', 'assignment', 'interactive') DEFAULT 'video',
                is_mandatory BOOLEAN DEFAULT TRUE,
                is_active BOOLEAN DEFAULT TRUE,
                passing_score INT DEFAULT 70,
                max_attempts INT DEFAULT 3,
                resources JSON NULL,
                quiz_data JSON NULL,
                assignment_data JSON NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (module_id) REFERENCES training_modules(id) ON DELETE CASCADE,
                INDEX idx_module_order (module_id, order_index),
                INDEX idx_type_active (lesson_type, is_active),
                INDEX idx_slug (slug)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ",

        'training_enrollments' => "
            CREATE TABLE IF NOT EXISTS training_enrollments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                course_id INT,
                enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                completed_at TIMESTAMP NULL,
                progress_percentage DECIMAL(5,2) DEFAULT 0.00,
                status ENUM('active', 'completed', 'dropped', 'expired', 'suspended') DEFAULT 'active',
                current_lesson_id INT NULL,
                last_accessed_at TIMESTAMP NULL,
                certificate_issued BOOLEAN DEFAULT FALSE,
                certificate_url VARCHAR(500) NULL,
                final_score DECIMAL(5,2) NULL,
                attempts_count INT DEFAULT 0,
                deadline_at TIMESTAMP NULL,
                reminder_sent BOOLEAN DEFAULT FALSE,
                notes TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (course_id) REFERENCES training_courses(id) ON DELETE CASCADE,
                FOREIGN KEY (current_lesson_id) REFERENCES training_lessons(id) ON DELETE SET NULL,
                INDEX idx_user_course (user_id, course_id),
                INDEX idx_status_progress (status, progress_percentage),
                INDEX idx_enrolled_completed (enrolled_at, completed_at),
                INDEX idx_deadline (deadline_at),
                INDEX idx_current_lesson (current_lesson_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ",

        // Property Comparison Table
        'property_comparisons' => "
            CREATE TABLE IF NOT EXISTS property_comparisons (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NULL,
                session_id VARCHAR(255) NOT NULL,
                comparison_name VARCHAR(255) NULL,
                property_ids JSON NOT NULL,
                comparison_data JSON NULL,
                comparison_type ENUM('basic', 'detailed', 'financial', 'location', 'features') DEFAULT 'basic',
                shared BOOLEAN DEFAULT FALSE,
                share_token VARCHAR(255) UNIQUE NULL,
                expires_at TIMESTAMP NULL,
                view_count INT DEFAULT 0,
                last_viewed_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_session_user (session_id, user_id),
                INDEX idx_share_token (share_token),
                INDEX idx_expires_at (expires_at),
                INDEX idx_comparison_type (comparison_type),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        "
    ];

    $createdTables = 0;
    $failedTables = 0;

    echo "📋 CREATING MISSING DATABASE TABLES\n";
    echo "==================================\n\n";

    foreach ($tablesToCreate as $tableName => $sql) {
        try {
            echo "🔧 Creating table: $tableName\n";
            $pdo->// SECURITY FIX: exec() removed for security reasons$sql);

            // Verify table was created
            $result = $pdo->query("SHOW TABLES LIKE '$tableName'");
            if ($result->rowCount() > 0) {
                echo "✅ Table '$tableName' created successfully\n";
                $createdTables++;
            } else {
                echo "❌ Table '$tableName' creation failed\n";
                $failedTables++;
            }

        } catch (PDOException $e) {
            echo "❌ Error creating table '$tableName': " . $e->getMessage() . "\n";
            $failedTables++;
        }
        echo "\n";
    }

    // Insert default data for some tables
    echo "📝 INSERTING DEFAULT DATA\n";
    echo "========================\n";

    // Insert default WhatsApp templates
    $whatsappTemplates = [
        ['name' => 'property_inquiry_response', 'content' => 'Thank you for your interest in our property! Our property consultant will contact you shortly with more details.', 'category' => 'property'],
        ['name' => 'site_visit_confirmation', 'content' => 'Your site visit has been confirmed for {date} at {time}. Please arrive 10 minutes early. Address: {address}', 'category' => 'booking'],
        ['name' => 'follow_up', 'content' => 'Hi {name}! We noticed you were interested in our property. Are you still looking for a home in {location}?', 'category' => 'follow_up'],
        ['name' => 'price_negotiation', 'content' => 'Based on your requirements, we can offer this property at {negotiated_price}. Would you like to proceed?', 'category' => 'negotiation']
    ];

    foreach ($whatsappTemplates as $template) {
        try {
            $stmt = $pdo->prepare("INSERT IGNORE INTO whatsapp_templates (name, content, category, variables) VALUES (?, ?, ?, ?)");
            $variables = json_encode(['name', 'date', 'time', 'address', 'location', 'negotiated_price']);
            $stmt->execute([$template['name'], $template['content'], $template['category'], $variables]);
        } catch (Exception $e) {
            // Template might already exist, continue
        }
    }
    echo "✅ WhatsApp templates inserted\n";

    // Insert default badges
    $defaultBadges = [
        ['name' => 'first_property_view', 'display_name' => 'First Look', 'description' => 'Viewed your first property', 'category' => 'milestone', 'points_required' => 0, 'rarity' => 'common'],
        ['name' => 'property_explorer', 'display_name' => 'Property Explorer', 'description' => 'Viewed 10 different properties', 'category' => 'milestone', 'points_required' => 10, 'rarity' => 'common'],
        ['name' => 'inquiry_maker', 'display_name' => 'Interested Buyer', 'description' => 'Made your first property inquiry', 'category' => 'achievement', 'points_required' => 5, 'rarity' => 'uncommon'],
        ['name' => 'site_visitor', 'display_name' => 'Site Visitor', 'description' => 'Scheduled and completed a site visit', 'category' => 'achievement', 'points_required' => 50, 'rarity' => 'rare'],
        ['name' => 'loyal_customer', 'display_name' => 'Loyal Customer', 'description' => 'Been with us for 6 months', 'category' => 'loyalty', 'points_required' => 100, 'rarity' => 'epic'],
        ['name' => 'referral_master', 'display_name' => 'Referral Master', 'description' => 'Successfully referred 5 customers', 'category' => 'social', 'points_required' => 200, 'rarity' => 'legendary']
    ];

    foreach ($defaultBadges as $badge) {
        try {
            $stmt = $pdo->prepare("INSERT IGNORE INTO badges (name, display_name, description, category, points_required, rarity) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $badge['name'],
                $badge['display_name'],
                $badge['description'],
                $badge['category'],
                $badge['points_required'],
                $badge['rarity']
            ]);
        } catch (Exception $e) {
            // Badge might already exist, continue
        }
    }
    echo "✅ Default badges inserted\n";

    // Summary
    echo "\n📊 CREATION SUMMARY\n";
    echo "==================\n";
    echo "Total Tables Attempted: " . count($tablesToCreate) . "\n";
    echo "Successfully Created: $createdTables\n";
    echo "Failed: $failedTables\n";
    echo "Success Rate: " . round(($createdTables / count($tablesToCreate)) * 100, 1) . "%\n\n";

    if ($createdTables > 0) {
        echo "✅ Database tables creation completed successfully!\n";
        echo "🎉 All services now have their required database tables.\n\n";

        echo "📋 Created Tables:\n";
        foreach (array_keys($tablesToCreate) as $tableName) {
            echo "  • $tableName\n";
        }
    }

    if ($failedTables > 0) {
        echo "⚠️  Some tables failed to create. Please check the errors above.\n";
    }

    // Final verification
    echo "\n🔍 FINAL VERIFICATION\n";
    echo "====================\n";

    $allTablesExist = true;
    foreach (array_keys($tablesToCreate) as $tableName) {
        $result = $pdo->query("SHOW TABLES LIKE '$tableName'");
        if ($result->rowCount() === 0) {
            echo "❌ Table '$tableName' is missing\n";
            $allTablesExist = false;
        }
    }

    if ($allTablesExist) {
        echo "✅ All required tables verified and present in database\n";
    }

    echo "\n🎉 DATABASE TABLES CREATION COMPLETED!\n";
    echo "Your APS Dream Home services are now fully database-enabled.\n";

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    // DEBUG CODE REMOVED: 2026-02-22 19:56:16 CODE REMOVED: 2026-02-22 19:56:16
}
?>
