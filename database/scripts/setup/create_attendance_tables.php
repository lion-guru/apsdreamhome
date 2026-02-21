<?php
/**
 * Script to create employee attendance table with location tracking
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

    // Create employee attendance table
    $sql = "CREATE TABLE IF NOT EXISTS `employee_attendance` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `employee_id` INT NOT NULL,
        `check_in_time` DATETIME NULL,
        `check_out_time` DATETIME NULL,
        `check_in_location` VARCHAR(255) NULL,
        `check_out_location` VARCHAR(255) NULL,
        `check_in_latitude` DECIMAL(10,8) NULL,
        `check_in_longitude` DECIMAL(11,8) NULL,
        `check_out_latitude` DECIMAL(10,8) NULL,
        `check_out_longitude` DECIMAL(11,8) NULL,
        `check_in_ip` VARCHAR(45) NULL,
        `check_out_ip` VARCHAR(45) NULL,
        `check_in_device` VARCHAR(255) NULL,
        `check_out_device` VARCHAR(255) NULL,
        `working_hours` DECIMAL(5,2) NULL COMMENT 'Hours worked in decimal',
        `status` ENUM('present','absent','half_day','late','early_leave') DEFAULT 'present',
        `notes` TEXT NULL,
        `approved_by` INT NULL,
        `approved_at` DATETIME NULL,
        `attendance_date` DATE NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`approved_by`) REFERENCES `admin`(`aid`) ON DELETE SET NULL,

        UNIQUE KEY `unique_employee_date` (`employee_id`, `attendance_date`),
        INDEX `idx_employee_date` (`employee_id`, `attendance_date`),
        INDEX `idx_status` (`status`),
        INDEX `idx_attendance_date` (`attendance_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Employee attendance table created successfully!\n";
    }

    // Create attendance logs table for audit trail (without foreign keys for now)
    $sql = "CREATE TABLE IF NOT EXISTS `attendance_logs` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `attendance_id` INT NOT NULL,
        `action` ENUM('check_in','check_out','update','approve','reject') NOT NULL,
        `old_data` JSON NULL,
        `new_data` JSON NULL,
        `performed_by` INT NOT NULL,
        `ip_address` VARCHAR(45) NULL,
        `user_agent` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        INDEX `idx_attendance_action` (`attendance_id`, `action`),
        INDEX `idx_performed_by` (`performed_by`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Attendance logs table created successfully!\n";
    }

    // Create attendance settings table (without foreign keys for now)
    $sql = "CREATE TABLE IF NOT EXISTS `attendance_settings` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `setting_key` VARCHAR(100) NOT NULL UNIQUE,
        `setting_value` TEXT NULL,
        `description` VARCHAR(255) NULL,
        `updated_by` INT NULL,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_setting_key` (`setting_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Attendance settings table created successfully!\n";
    }

    // Insert default attendance settings
    $defaultSettings = [
        ['work_start_time', '09:00:00', 'Default work start time'],
        ['work_end_time', '18:00:00', 'Default work end time'],
        ['grace_period_minutes', '15', 'Grace period in minutes for late arrival'],
        ['location_required', '1', 'Whether location tracking is required'],
        ['location_radius_meters', '100', 'Allowed radius from office location in meters'],
        ['office_latitude', '28.6139', 'Office latitude coordinate'],
        ['office_longitude', '77.2090', 'Office longitude coordinate'],
        ['auto_checkout_enabled', '1', 'Whether to auto-checkout at end of day'],
        ['overtime_calculation', '1', 'Whether to calculate overtime hours'],
        ['holiday_weekends', '1', 'Whether weekends are considered holidays'],
        ['max_working_hours', '9', 'Maximum working hours per day']
    ];

    $insertSql = "INSERT IGNORE INTO `attendance_settings` (`setting_key`, `setting_value`, `description`) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($insertSql);

    foreach ($defaultSettings as $setting) {
        $stmt->execute($setting);
    }

    echo "✅ Default attendance settings inserted successfully!\n";

    echo "\n🎉 Employee attendance system database setup completed!\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
