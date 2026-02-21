<?php
/**
 * Script to create employee shift scheduling tables
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

    // Create shift types table
    $sql = "CREATE TABLE IF NOT EXISTS `shift_types` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `code` VARCHAR(20) NOT NULL UNIQUE,
        `description` TEXT NULL,
        `start_time` TIME NOT NULL,
        `end_time` TIME NOT NULL,
        `duration_hours` DECIMAL(4,2) NOT NULL,
        `is_overnight` TINYINT(1) DEFAULT 0,
        `break_duration` INT DEFAULT 60 COMMENT 'Break duration in minutes',
        `color` VARCHAR(7) DEFAULT '#007bff',
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_shift_type_code` (`code`),
        INDEX `idx_shift_type_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Shift types table created successfully!\n";
    }

    // Create employee shifts table (scheduled shifts)
    $sql = "CREATE TABLE IF NOT EXISTS `employee_shifts` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `employee_id` INT NOT NULL,
        `shift_type_id` INT NOT NULL,
        `shift_date` DATE NOT NULL,
        `start_time` TIME NOT NULL,
        `end_time` TIME NOT NULL,
        `actual_start_time` TIME NULL,
        `actual_end_time` TIME NULL,
        `duration_hours` DECIMAL(4,2) NULL,
        `status` ENUM('scheduled','confirmed','in_progress','completed','cancelled','no_show') DEFAULT 'scheduled',
        `notes` TEXT NULL,
        `assigned_by` INT NULL,
        `confirmed_at` DATETIME NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_employee_shift_employee` (`employee_id`),
        INDEX `idx_employee_shift_type` (`shift_type_id`),
        INDEX `idx_employee_shift_date` (`shift_date`),
        INDEX `idx_employee_shift_status` (`status`),
        UNIQUE KEY `unique_employee_shift_date` (`employee_id`, `shift_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Employee shifts table created successfully!\n";
    }

    // Create shift schedules table (recurring schedules)
    $sql = "CREATE TABLE IF NOT EXISTS `shift_schedules` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `description` TEXT NULL,
        `shift_type_id` INT NOT NULL,
        `department_id` INT NULL,
        `days_of_week` JSON NOT NULL COMMENT 'Array of days (0-6, 0=Sunday)',
        `start_date` DATE NOT NULL,
        `end_date` DATE NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_by` INT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_schedule_shift_type` (`shift_type_id`),
        INDEX `idx_schedule_department` (`department_id`),
        INDEX `idx_schedule_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Shift schedules table created successfully!\n";
    }

    // Create shift assignments table (linking schedules to employees)
    $sql = "CREATE TABLE IF NOT EXISTS `shift_assignments` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `schedule_id` INT NOT NULL,
        `employee_id` INT NOT NULL,
        `assigned_date` DATE NOT NULL,
        `is_primary` TINYINT(1) DEFAULT 1 COMMENT 'Primary shift for the day',
        `assigned_by` INT NULL,
        `assigned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (`schedule_id`) REFERENCES `shift_schedules`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`assigned_by`) REFERENCES `admin`(`aid`) ON DELETE SET NULL,

        UNIQUE KEY `unique_employee_schedule_date` (`employee_id`, `assigned_date`),
        INDEX `idx_assignment_schedule` (`schedule_id`),
        INDEX `idx_assignment_employee` (`employee_id`),
        INDEX `idx_assignment_date` (`assigned_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Shift assignments table created successfully!\n";
    }

    // Create shift swap requests table
    $sql = "CREATE TABLE IF NOT EXISTS `shift_swap_requests` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `requester_id` INT NOT NULL,
        `target_employee_id` INT NOT NULL,
        `requester_shift_id` INT NOT NULL,
        `target_shift_id` INT NOT NULL,
        `reason` TEXT NOT NULL,
        `status` ENUM('pending','approved','rejected','cancelled') DEFAULT 'pending',
        `approved_by` INT NULL,
        `approved_at` DATETIME NULL,
        `approved_notes` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        FOREIGN KEY (`requester_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`target_employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`requester_shift_id`) REFERENCES `employee_shifts`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`target_shift_id`) REFERENCES `employee_shifts`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`approved_by`) REFERENCES `admin`(`aid`) ON DELETE SET NULL,

        INDEX `idx_swap_requester` (`requester_id`),
        INDEX `idx_swap_target` (`target_employee_id`),
        INDEX `idx_swap_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Shift swap requests table created successfully!\n";
    }

    // Create time-off requests table (separate from leave system)
    $sql = "CREATE TABLE IF NOT EXISTS `time_off_requests` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `employee_id` INT NOT NULL,
        `request_type` ENUM('vacation','personal','sick','emergency') DEFAULT 'personal',
        `start_date` DATE NOT NULL,
        `end_date` DATE NOT NULL,
        `start_time` TIME NULL,
        `end_time` TIME NULL,
        `reason` TEXT NOT NULL,
        `status` ENUM('pending','approved','rejected','cancelled') DEFAULT 'pending',
        `approved_by` INT NULL,
        `approved_at` DATETIME NULL,
        `approved_notes` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`approved_by`) REFERENCES `admin`(`aid`) ON DELETE SET NULL,

        INDEX `idx_timeoff_employee` (`employee_id`),
        INDEX `idx_timeoff_status` (`status`),
        INDEX `idx_timeoff_dates` (`start_date`, `end_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Time-off requests table created successfully!\n";
    }

    // Insert default shift types
    $defaultShiftTypes = [
        ['Morning Shift', 'MORNING', 'Standard morning shift from 9 AM to 6 PM', '09:00:00', '18:00:00', 9.00, 0, 60, '#28a745'],
        ['Evening Shift', 'EVENING', 'Evening shift from 2 PM to 11 PM', '14:00:00', '23:00:00', 9.00, 0, 60, '#ffc107'],
        ['Night Shift', 'NIGHT', 'Night shift from 11 PM to 8 AM', '23:00:00', '08:00:00', 9.00, 1, 60, '#6f42c1'],
        ['Weekend Morning', 'WKND_MORN', 'Weekend morning shift', '10:00:00', '18:00:00', 8.00, 0, 45, '#17a2b8'],
        ['Short Shift', 'SHORT', 'Short shift for part-time or special duties', '09:00:00', '14:00:00', 5.00, 0, 30, '#fd7e14']
    ];

    $insertSql = "INSERT IGNORE INTO `shift_types` (`name`, `code`, `description`, `start_time`, `end_time`, `duration_hours`, `is_overnight`, `break_duration`, `color`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertSql);

    foreach ($defaultShiftTypes as $shiftType) {
        $stmt->execute($shiftType);
    }

    echo "✅ Default shift types inserted successfully!\n";

    echo "\n🎉 Employee shift scheduling system database setup completed!\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
