<?php
/**
 * Script to create associate MLM training module tables
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

    // Create training courses table
    $sql = "CREATE TABLE IF NOT EXISTS `training_courses` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `course_title` VARCHAR(255) NOT NULL,
        `course_description` TEXT NOT NULL,
        `course_category` ENUM('sales','product','compliance','leadership','technical','business') DEFAULT 'sales',
        `difficulty_level` ENUM('beginner','intermediate','advanced','expert') DEFAULT 'beginner',
        `course_duration_hours` DECIMAL(5,2) DEFAULT 0,
        `course_objectives` JSON NULL COMMENT 'Learning objectives array',
        `prerequisites` JSON NULL COMMENT 'Required prerequisite courses',
        `target_audience` JSON NULL COMMENT 'Target audience criteria',
        `course_image` VARCHAR(500) NULL,
        `course_video_url` VARCHAR(500) NULL,
        `is_mandatory` TINYINT(1) DEFAULT 0,
        `is_active` TINYINT(1) DEFAULT 1,
        `max_enrollments` INT NULL,
        `current_enrollments` INT DEFAULT 0,
        `passing_score_percentage` DECIMAL(5,2) DEFAULT 70.00,
        `certificate_template` VARCHAR(100) NULL,
        `points_reward` INT DEFAULT 0,
        `badge_reward_id` INT NULL,
        `created_by` INT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_course_category` (`course_category`),
        INDEX `idx_course_difficulty` (`difficulty_level`),
        INDEX `idx_course_active` (`is_active`),
        INDEX `idx_course_mandatory` (`is_mandatory`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Training courses table created successfully!\n";
    }

    // Create course modules table
    $sql = "CREATE TABLE IF NOT EXISTS `course_modules` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `course_id` INT NOT NULL,
        `module_title` VARCHAR(255) NOT NULL,
        `module_description` TEXT NULL,
        `module_order` INT DEFAULT 0,
        `content_type` ENUM('video','text','presentation','interactive','quiz','assignment') DEFAULT 'text',
        `content_url` VARCHAR(500) NULL,
        `content_text` LONGTEXT NULL,
        `duration_minutes` INT DEFAULT 0,
        `is_required` TINYINT(1) DEFAULT 1,
        `prerequisites` JSON NULL COMMENT 'Required prerequisite modules',
        `completion_criteria` JSON NULL COMMENT 'Criteria for module completion',
        `points_reward` INT DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (`course_id`) REFERENCES `training_courses`(`id`) ON DELETE CASCADE,
        INDEX `idx_module_course` (`course_id`),
        INDEX `idx_module_order` (`module_order`),
        INDEX `idx_module_type` (`content_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Course modules table created successfully!\n";
    }

    // Create training quizzes table
    $sql = "CREATE TABLE IF NOT EXISTS `training_quizzes` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `course_id` INT NULL,
        `module_id` INT NULL,
        `quiz_title` VARCHAR(255) NOT NULL,
        `quiz_description` TEXT NULL,
        `quiz_type` ENUM('pre_course','post_course','module','final_exam','practice') DEFAULT 'module',
        `time_limit_minutes` INT NULL,
        `passing_score_percentage` DECIMAL(5,2) DEFAULT 70.00,
        `max_attempts` INT DEFAULT 3,
        `randomize_questions` TINYINT(1) DEFAULT 0,
        `show_answers_after` TINYINT(1) DEFAULT 0,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_by` INT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (`course_id`) REFERENCES `training_courses`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`module_id`) REFERENCES `course_modules`(`id`) ON DELETE CASCADE,
        INDEX `idx_quiz_course` (`course_id`),
        INDEX `idx_quiz_module` (`module_id`),
        INDEX `idx_quiz_type` (`quiz_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Training quizzes table created successfully!\n";
    }

    // Create quiz questions table
    $sql = "CREATE TABLE IF NOT EXISTS `quiz_questions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `quiz_id` INT NOT NULL,
        `question_text` TEXT NOT NULL,
        `question_type` ENUM('multiple_choice','true_false','short_answer','essay','matching') DEFAULT 'multiple_choice',
        `options` JSON NULL COMMENT 'Answer options for multiple choice',
        `correct_answer` TEXT NULL,
        `explanation` TEXT NULL,
        `points` INT DEFAULT 1,
        `question_order` INT DEFAULT 0,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (`quiz_id`) REFERENCES `training_quizzes`(`id`) ON DELETE CASCADE,
        INDEX `idx_question_quiz` (`quiz_id`),
        INDEX `idx_question_order` (`question_order`),
        INDEX `idx_question_type` (`question_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Quiz questions table created successfully!\n";
    }

    // Create user course enrollments table
    $sql = "CREATE TABLE IF NOT EXISTS `user_course_enrollments` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `user_type` ENUM('associate','employee','admin') DEFAULT 'associate',
        `course_id` INT NOT NULL,
        `enrollment_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `completion_date` TIMESTAMP NULL,
        `progress_percentage` DECIMAL(5,2) DEFAULT 0,
        `current_module_id` INT NULL,
        `total_time_spent_minutes` INT DEFAULT 0,
        `last_accessed_at` TIMESTAMP NULL,
        `certificate_issued` TINYINT(1) DEFAULT 0,
        `certificate_number` VARCHAR(50) NULL,
        `final_score_percentage` DECIMAL(5,2) NULL,
        `status` ENUM('enrolled','in_progress','completed','failed','expired') DEFAULT 'enrolled',
        `expires_at` DATE NULL,

        FOREIGN KEY (`course_id`) REFERENCES `training_courses`(`id`) ON DELETE CASCADE,
        INDEX `idx_enrollment_user` (`user_id`, `user_type`),
        INDEX `idx_enrollment_course` (`course_id`),
        INDEX `idx_enrollment_status` (`status`),
        INDEX `idx_enrollment_progress` (`progress_percentage`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ User course enrollments table created successfully!\n";
    }

    // Create module progress table
    $sql = "CREATE TABLE IF NOT EXISTS `module_progress` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `enrollment_id` INT NOT NULL,
        `module_id` INT NOT NULL,
        `started_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `completed_at` TIMESTAMP NULL,
        `time_spent_minutes` INT DEFAULT 0,
        `progress_percentage` DECIMAL(5,2) DEFAULT 0,
        `score_percentage` DECIMAL(5,2) NULL,
        `attempts_count` INT DEFAULT 0,
        `last_attempt_at` TIMESTAMP NULL,
        `status` ENUM('not_started','in_progress','completed','failed') DEFAULT 'not_started',

        FOREIGN KEY (`enrollment_id`) REFERENCES `user_course_enrollments`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`module_id`) REFERENCES `course_modules`(`id`) ON DELETE CASCADE,
        INDEX `idx_progress_enrollment` (`enrollment_id`),
        INDEX `idx_progress_module` (`module_id`),
        INDEX `idx_progress_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Module progress table created successfully!\n";
    }

    // Create quiz attempts table
    $sql = "CREATE TABLE IF NOT EXISTS `quiz_attempts` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `enrollment_id` INT NOT NULL,
        `quiz_id` INT NOT NULL,
        `attempt_number` INT DEFAULT 1,
        `started_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `completed_at` TIMESTAMP NULL,
        `time_spent_minutes` INT NULL,
        `total_questions` INT DEFAULT 0,
        `correct_answers` INT DEFAULT 0,
        `score_percentage` DECIMAL(5,2) DEFAULT 0,
        `passed` TINYINT(1) DEFAULT 0,
        `answers` JSON NULL COMMENT 'User answers',
        `feedback` TEXT NULL,

        FOREIGN KEY (`enrollment_id`) REFERENCES `user_course_enrollments`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`quiz_id`) REFERENCES `training_quizzes`(`id`) ON DELETE CASCADE,
        INDEX `idx_attempt_enrollment` (`enrollment_id`),
        INDEX `idx_attempt_quiz` (`quiz_id`),
        INDEX `idx_attempt_passed` (`passed`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Quiz attempts table created successfully!\n";
    }

    // Create training certifications table
    $sql = "CREATE TABLE IF NOT EXISTS `training_certificates` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `enrollment_id` INT NOT NULL,
        `certificate_number` VARCHAR(50) NOT NULL UNIQUE,
        `certificate_title` VARCHAR(255) NOT NULL,
        `issued_to_name` VARCHAR(255) NOT NULL,
        `issued_to_id` INT NOT NULL,
        `issued_by_name` VARCHAR(255) NOT NULL,
        `issued_by_id` INT NOT NULL,
        `course_name` VARCHAR(255) NOT NULL,
        `completion_date` DATE NOT NULL,
        `expiry_date` DATE NULL,
        `certificate_data` JSON NULL COMMENT 'Additional certificate data',
        `download_count` INT DEFAULT 0,
        `is_active` TINYINT(1) DEFAULT 1,
        `issued_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (`enrollment_id`) REFERENCES `user_course_enrollments`(`id`) ON DELETE CASCADE,
        INDEX `idx_certificate_enrollment` (`enrollment_id`),
        INDEX `idx_certificate_number` (`certificate_number`),
        INDEX `idx_certificate_user` (`issued_to_id`),
        INDEX `idx_certificate_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Training certificates table created successfully!\n";
    }

    // Create training analytics table
    $sql = "CREATE TABLE IF NOT EXISTS `training_analytics` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `course_id` INT NULL,
        `user_id` INT NULL,
        `user_type` ENUM('associate','employee','admin') DEFAULT 'associate',
        `event_type` ENUM('enrollment','module_view','module_complete','quiz_attempt','quiz_pass','course_complete','certificate_download') NOT NULL,
        `event_data` JSON NULL,
        `session_id` VARCHAR(100) NULL,
        `device_type` ENUM('desktop','mobile','tablet') DEFAULT 'desktop',
        `ip_address` VARCHAR(45) NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (`course_id`) REFERENCES `training_courses`(`id`) ON DELETE SET NULL,
        INDEX `idx_analytics_course` (`course_id`),
        INDEX `idx_analytics_user` (`user_id`, `user_type`),
        INDEX `idx_analytics_event` (`event_type`),
        INDEX `idx_analytics_date` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Training analytics table created successfully!\n";
    }

    // Create learning paths table
    $sql = "CREATE TABLE IF NOT EXISTS `learning_paths` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `path_name` VARCHAR(255) NOT NULL,
        `path_description` TEXT NOT NULL,
        `target_role` VARCHAR(100) NULL,
        `difficulty_level` ENUM('beginner','intermediate','advanced','expert') DEFAULT 'beginner',
        `estimated_completion_days` INT DEFAULT 30,
        `courses_sequence` JSON NOT NULL COMMENT 'Ordered array of course IDs',
        `prerequisites` JSON NULL COMMENT 'Required prerequisites',
        `is_active` TINYINT(1) DEFAULT 1,
        `enrollment_count` INT DEFAULT 0,
        `completion_rate` DECIMAL(5,2) DEFAULT 0,
        `created_by` INT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        INDEX `idx_path_active` (`is_active`),
        INDEX `idx_path_role` (`target_role`),
        INDEX `idx_path_difficulty` (`difficulty_level`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Learning paths table created successfully!\n";
    }

    // Insert default training courses
    $defaultCourses = [
        [
            'Real Estate Sales Fundamentals',
            'Comprehensive course covering basic principles of real estate sales, market analysis, and customer relationship management.',
            'sales',
            'beginner',
            8.0,
            '["Understand real estate basics","Learn sales techniques","Master customer communication","Know legal requirements"]',
            null,
            '["new_associates","sales_team"]',
            null,
            null,
            1,
            1,
            null,
            0,
            70.00,
            null,
            500,
            null
        ],
        [
            'Property Valuation & Pricing',
            'Learn how to accurately value properties, understand market trends, and set competitive pricing strategies.',
            'product',
            'intermediate',
            6.0,
            '["Understand valuation methods","Analyze market data","Set pricing strategies","Create competitive advantages"]',
            '["Real Estate Sales Fundamentals"]',
            '["sales_team","managers"]',
            null,
            null,
            0,
            1,
            null,
            0,
            75.00,
            null,
            400,
            null
        ],
        [
            'Legal Compliance & Documentation',
            'Essential legal knowledge for real estate transactions, documentation, and regulatory compliance.',
            'compliance',
            'intermediate',
            10.0,
            '["Understand legal frameworks","Master documentation","Learn compliance requirements","Handle legal issues"]',
            null,
            '["all_associates","sales_team","management"]',
            null,
            null,
            1,
            1,
            null,
            0,
            80.00,
            null,
            600,
            null
        ],
        [
            'Leadership & Team Management',
            'Develop leadership skills, learn team management techniques, and build high-performing sales teams.',
            'leadership',
            'advanced',
            12.0,
            '["Develop leadership skills","Learn team management","Build motivation","Create success plans"]',
            '["Real Estate Sales Fundamentals","Property Valuation & Pricing"]',
            '["team_leaders","managers","senior_associates"]',
            null,
            null,
            0,
            1,
            null,
            0,
            75.00,
            null,
            800,
            null
        ]
    ];

    $insertCourseSql = "INSERT IGNORE INTO `training_courses` (`course_title`, `course_description`, `course_category`, `difficulty_level`, `course_duration_hours`, `course_objectives`, `prerequisites`, `target_audience`, `course_image`, `course_video_url`, `is_mandatory`, `is_active`, `max_enrollments`, `current_enrollments`, `passing_score_percentage`, `certificate_template`, `points_reward`, `badge_reward_id`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertCourseSql);

    foreach ($defaultCourses as $course) {
        $stmt->execute($course);
    }

    echo "✅ Default training courses inserted successfully!\n";

    echo "\n🎉 Associate MLM training module database setup completed!\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
