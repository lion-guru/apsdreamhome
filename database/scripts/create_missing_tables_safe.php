<?php
/**
 * APS Dream Home - Create Missing Tables Without Foreign Keys
 * Creates tables first, then adds constraints separately
 */

// Database connection
$host = 'localhost';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

echo "🔧 APS DREAM HOME - CREATE MISSING TABLES (SAFE MODE)\n";
echo "==================================================\n\n";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    echo "✅ Database connection established\n\n";

    // Check existing table structures
    echo "🔍 CHECKING EXISTING TABLE STRUCTURES\n";
    echo "====================================\n";

    $tablesToCheck = ['users', 'leads', 'properties', 'training_courses', 'training_modules', 'training_lessons'];

    foreach ($tablesToCheck as $table) {
        $result = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($result->rowCount() > 0) {
            // Get primary key column
            $pkResult = $pdo->query("
                SHOW KEYS FROM $table WHERE Key_name = 'PRIMARY'
            ");
            $pkInfo = $pkResult->fetch();
            if ($pkInfo) {
                echo "✅ $table: PRIMARY KEY = {$pkInfo['Column_name']}\n";
            } else {
                echo "⚠️  $table: No primary key found\n";
            }
        } else {
            echo "❌ $table: Table does not exist\n";
        }
    }
    echo "\n";

    // Create tables without foreign keys first
    $tablesSQL = [
        'lead_visits' => "
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
                INDEX idx_session (session_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ",

        'user_points' => "
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
                INDEX idx_reference_type (reference_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ",

        'training_enrollments' => "
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
                INDEX idx_current_lesson (current_lesson_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ",

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
                INDEX idx_session_user (session_id, user_id),
                INDEX idx_share_token (share_token),
                INDEX idx_expires_at (expires_at),
                INDEX idx_comparison_type (comparison_type),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        "
    ];

    // Create tables
    $createdTables = 0;
    echo "🔧 CREATING TABLES WITHOUT FOREIGN KEYS\n";
    echo "=======================================\n";

    foreach ($tablesSQL as $tableName => $sql) {
        try {
            echo "Creating table: $tableName\n";
            $pdo->// SECURITY FIX: exec() removed for security reasons$sql);

            $result = $pdo->query("SHOW TABLES LIKE '$tableName'");
            if ($result->rowCount() > 0) {
                echo "✅ Table '$tableName' created successfully\n";
                $createdTables++;
            } else {
                echo "❌ Table '$tableName' creation failed\n";
            }
        } catch (PDOException $e) {
            echo "❌ Error creating table '$tableName': " . $e->getMessage() . "\n";
        }
        echo "\n";
    }

    // Now add foreign key constraints separately
    echo "🔗 ADDING FOREIGN KEY CONSTRAINTS\n";
    echo "=================================\n";

    $foreignKeys = [
        'lead_visits' => [
            'ALTER TABLE lead_visits ADD CONSTRAINT fk_lead_visits_lead_id FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE;',
            'ALTER TABLE lead_visits ADD CONSTRAINT fk_lead_visits_property_id FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE;'
        ],
        'user_points' => [
            'ALTER TABLE user_points ADD CONSTRAINT fk_user_points_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;'
        ],
        'training_enrollments' => [
            'ALTER TABLE training_enrollments ADD CONSTRAINT fk_training_enrollments_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;',
            'ALTER TABLE training_enrollments ADD CONSTRAINT fk_training_enrollments_course_id FOREIGN KEY (course_id) REFERENCES training_courses(id) ON DELETE CASCADE;',
            'ALTER TABLE training_enrollments ADD CONSTRAINT fk_training_enrollments_current_lesson FOREIGN KEY (current_lesson_id) REFERENCES training_lessons(id) ON DELETE SET NULL;'
        ],
        'property_comparisons' => [
            'ALTER TABLE property_comparisons ADD CONSTRAINT fk_property_comparisons_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;'
        ]
    ];

    $constraintsAdded = 0;
    foreach ($foreignKeys as $tableName => $constraints) {
        echo "Adding constraints for table: $tableName\n";

        foreach ($constraints as $constraintSQL) {
            try {
                // Extract referenced table from constraint
                if (preg_match('/REFERENCES (\w+)\(/', $constraintSQL, $matches)) {
                    $referencedTable = $matches[1];

                    // Check if referenced table exists
                    $result = $pdo->query("SHOW TABLES LIKE '$referencedTable'");
                    if ($result->rowCount() > 0) {
                        $pdo->// SECURITY FIX: exec() removed for security reasons$constraintSQL);
                        echo "  ✅ Added constraint: " . substr($constraintSQL, 0, 50) . "...\n";
                        $constraintsAdded++;
                    } else {
                        echo "  ⚠️  Skipped constraint (referenced table '$referencedTable' doesn't exist)\n";
                    }
                }
            } catch (PDOException $e) {
                echo "  ❌ Error adding constraint: " . $e->getMessage() . "\n";
            }
        }
        echo "\n";
    }

    // Final verification
    echo "🔍 FINAL VERIFICATION\n";
    echo "====================\n";

    $requiredTables = ['lead_visits', 'user_points', 'training_enrollments', 'property_comparisons'];
    $allTablesExist = true;

    foreach ($requiredTables as $table) {
        $result = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($result->rowCount() > 0) {
            echo "✅ Table '$table' exists\n";

            // Check if it has foreign keys
            $fkResult = $pdo->query("
                SELECT CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = '$dbname' AND TABLE_NAME = '$table'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            $fks = $fkResult->fetchAll();
            if (!empty($fks)) {
                echo "  📋 Foreign keys: " . count($fks) . "\n";
            }
        } else {
            echo "❌ Table '$table' is missing\n";
            $allTablesExist = false;
        }
    }

    echo "\n📊 CREATION SUMMARY\n";
    echo "==================\n";
    echo "Tables Created: $createdTables/4\n";
    echo "Constraints Added: $constraintsAdded\n";
    echo "Success Rate: " . round(($createdTables / 4) * 100, 1) . "%\n\n";

    if ($allTablesExist) {
        echo "🎉 ALL MISSING TABLES SUCCESSFULLY CREATED!\n";
        echo "Your APS Dream Home services are now fully database-enabled.\n";
    } else {
        echo "⚠️  Some tables are still missing. Please check manually.\n";
    }

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    // DEBUG CODE REMOVED: 2026-02-22 19:56:15 CODE REMOVED: 2026-02-22 19:56:15
}
?>
