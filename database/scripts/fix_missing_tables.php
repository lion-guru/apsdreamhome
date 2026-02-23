<?php
/**
 * APS Dream Home - Fix Missing Database Tables
 * Creates the remaining tables with corrected foreign key constraints
 */

// Database connection
$host = 'localhost';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

echo "🔧 APS DREAM HOME - FIX MISSING DATABASE TABLES\n";
echo "=============================================\n\n";

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    echo "✅ Database connection established\n\n";

    // Check which tables are missing
    $requiredTables = [
        'lead_visits',
        'user_points',
        'training_enrollments',
        'property_comparisons'
    ];

    $missingTables = [];
    foreach ($requiredTables as $table) {
        $result = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($result->rowCount() === 0) {
            $missingTables[] = $table;
        }
    }

    echo "📋 TABLES TO CREATE: " . count($missingTables) . "\n";
    echo "====================\n";

    if (empty($missingTables)) {
        echo "✅ All tables already exist!\n";
        // DEBUG CODE REMOVED: 2026-02-22 19:56:16 CODE REMOVED: 2026-02-22 19:56:16
    }

    foreach ($missingTables as $table) {
        echo "• $table\n";
    }
    echo "\n";

    // Check existing table structures to fix foreign keys
    echo "🔍 ANALYZING EXISTING TABLES FOR FOREIGN KEYS\n";
    echo "============================================\n";

    $existingTables = [
        'users' => false,
        'leads' => false,
        'properties' => false,
        'training_courses' => false,
        'training_modules' => false,
        'training_lessons' => false
    ];

    foreach ($existingTables as $table => &$exists) {
        $result = $pdo->query("SHOW TABLES LIKE '$table'");
        $exists = $result->rowCount() > 0;
        echo ($exists ? "✅" : "❌") . " $table\n";
    }
    echo "\n";

    // Create tables with corrected foreign keys
    $tablesToCreate = [];

    // lead_visits table
    if (in_array('lead_visits', $missingTables)) {
        $tablesToCreate['lead_visits'] = "
            CREATE TABLE IF NOT EXISTS lead_visits (
                id INT AUTO_INCREMENT PRIMARY KEY,
                lead_id INT NULL,
                property_id INT NULL,
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
                INDEX idx_lead_property (lead_id, property_id),
                INDEX idx_visit_date (visit_date),
                INDEX idx_source_type (source, visit_type),
                INDEX idx_duration (duration_seconds),
                INDEX idx_session (session_id)" .
                ($existingTables['leads'] ? ",
                FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE" : "") .
                ($existingTables['properties'] ? ",
                FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE" : "") .
                ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
    }

    // user_points table
    if (in_array('user_points', $missingTables)) {
        $tablesToCreate['user_points'] = "
            CREATE TABLE IF NOT EXISTS user_points (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NULL,
                points INT DEFAULT 0,
                points_type ENUM('earned', 'bonus', 'penalty', 'redeemed') DEFAULT 'earned',
                source VARCHAR(150) NOT NULL,
                source_id INT NULL,
                description TEXT NULL,
                reference_type ENUM('property_view', 'inquiry', 'booking', 'review', 'referral', 'training', 'social_share', 'badge_earned', 'milestone', 'admin_bonus') NULL,
                earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at TIMESTAMP NULL,
                is_expired BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_points (user_id, points),
                INDEX idx_points_type (points_type),
                INDEX idx_source (source),
                INDEX idx_earned_at (earned_at),
                INDEX idx_expires_at (expires_at),
                INDEX idx_reference_type (reference_type)" .
                ($existingTables['users'] ? ",
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE" : "") .
                ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
    }

    // training_enrollments table
    if (in_array('training_enrollments', $missingTables)) {
        $tablesToCreate['training_enrollments'] = "
            CREATE TABLE IF NOT EXISTS training_enrollments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NULL,
                course_id INT NULL,
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
                INDEX idx_user_course (user_id, course_id),
                INDEX idx_status_progress (status, progress_percentage),
                INDEX idx_enrolled_completed (enrolled_at, completed_at),
                INDEX idx_deadline (deadline_at),
                INDEX idx_current_lesson (current_lesson_id)" .
                ($existingTables['users'] ? ",
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE" : "") .
                ($existingTables['training_courses'] ? ",
                FOREIGN KEY (course_id) REFERENCES training_courses(id) ON DELETE CASCADE" : "") .
                ($existingTables['training_lessons'] ? ",
                FOREIGN KEY (current_lesson_id) REFERENCES training_lessons(id) ON DELETE SET NULL" : "") .
                ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
    }

    // property_comparisons table
    if (in_array('property_comparisons', $missingTables)) {
        $tablesToCreate['property_comparisons'] = "
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
                INDEX idx_session_user (session_id, user_id),
                INDEX idx_share_token (share_token),
                INDEX idx_expires_at (expires_at),
                INDEX idx_comparison_type (comparison_type),
                INDEX idx_created_at (created_at)" .
                ($existingTables['users'] ? ",
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE" : "") .
                ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
    }

    // Create the tables
    $createdTables = 0;
    $failedTables = 0;

    echo "🔧 CREATING CORRECTED TABLES\n";
    echo "===========================\n";

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

    // Summary
    echo "📊 FIX CREATION SUMMARY\n";
    echo "======================\n";
    echo "Total Tables Attempted: " . count($tablesToCreate) . "\n";
    echo "Successfully Created: $createdTables\n";
    echo "Failed: $failedTables\n";
    echo "Success Rate: " . round(($createdTables / count($tablesToCreate)) * 100, 1) . "%\n\n";

    if ($createdTables > 0) {
        echo "✅ Database table fixes completed successfully!\n";
        echo "🎉 All missing tables have been created.\n\n";

        echo "📋 Fixed Tables:\n";
        foreach (array_keys($tablesToCreate) as $tableName) {
            echo "  • $tableName\n";
        }
    }

    if ($failedTables > 0) {
        echo "⚠️  Some tables still failed to create. Manual intervention may be needed.\n";
    }

    // Final verification
    echo "\n🔍 FINAL VERIFICATION\n";
    echo "====================\n";

    $allCreated = true;
    foreach ($requiredTables as $table) {
        $result = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($result->rowCount() > 0) {
            echo "✅ Table '$table' exists\n";
        } else {
            echo "❌ Table '$table' is still missing\n";
            $allCreated = false;
        }
    }

    if ($allCreated) {
        echo "\n🎉 ALL MISSING TABLES SUCCESSFULLY CREATED!\n";
        echo "Your APS Dream Home services are now fully database-enabled.\n";
    } else {
        echo "\n⚠️  Some tables are still missing. Please check the errors above.\n";
    }

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    // DEBUG CODE REMOVED: 2026-02-22 19:56:16 CODE REMOVED: 2026-02-22 19:56:16
}
?>
