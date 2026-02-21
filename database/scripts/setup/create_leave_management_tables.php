<?php
/**
 * Script to create employee leave management tables
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

    // Create leave types table
    $sql = "CREATE TABLE IF NOT EXISTS `leave_types` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `code` VARCHAR(20) NOT NULL UNIQUE,
        `description` TEXT NULL,
        `days_per_year` INT NOT NULL DEFAULT 0,
        `max_consecutive_days` INT NULL,
        `requires_approval` TINYINT(1) DEFAULT 1,
        `is_paid` TINYINT(1) DEFAULT 1,
        `color` VARCHAR(7) DEFAULT '#007bff',
        `status` ENUM('active','inactive') DEFAULT 'active',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_leave_type_code` (`code`),
        INDEX `idx_leave_type_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Leave types table created successfully!\n";
    }

    // Create employee leave balances table
    $sql = "CREATE TABLE IF NOT EXISTS `employee_leave_balances` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `employee_id` INT NOT NULL,
        `leave_type_id` INT NOT NULL,
        `year` YEAR NOT NULL,
        `allocated_days` DECIMAL(5,2) NOT NULL DEFAULT 0,
        `used_days` DECIMAL(5,2) NOT NULL DEFAULT 0,
        `remaining_days` DECIMAL(5,2) NOT NULL DEFAULT 0,
        `carried_forward` DECIMAL(5,2) NOT NULL DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types`(`id`) ON DELETE CASCADE,

        UNIQUE KEY `unique_employee_leave_year` (`employee_id`, `leave_type_id`, `year`),
        INDEX `idx_employee_leave_balance` (`employee_id`, `year`),
        INDEX `idx_leave_balance_type` (`leave_type_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Employee leave balances table created successfully!\n";
    }

    // Create leave requests table
    $sql = "CREATE TABLE IF NOT EXISTS `leave_requests` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `employee_id` INT NOT NULL,
        `leave_type_id` INT NOT NULL,
        `start_date` DATE NOT NULL,
        `end_date` DATE NOT NULL,
        `total_days` DECIMAL(5,2) NOT NULL,
        `reason` TEXT NOT NULL,
        `status` ENUM('pending','approved','rejected','cancelled') DEFAULT 'pending',
        `approved_by` INT NULL,
        `approved_at` DATETIME NULL,
        `approved_notes` TEXT NULL,
        `emergency_contact` VARCHAR(255) NULL,
        `work_coverage` TEXT NULL,
        `attachment_path` VARCHAR(255) NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`approved_by`) REFERENCES `admin`(`aid`) ON DELETE SET NULL,

        INDEX `idx_leave_request_employee` (`employee_id`),
        INDEX `idx_leave_request_status` (`status`),
        INDEX `idx_leave_request_dates` (`start_date`, `end_date`),
        INDEX `idx_leave_request_type` (`leave_type_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Leave requests table created successfully!\n";
    }

    // Create leave approvals workflow table
    $sql = "CREATE TABLE IF NOT EXISTS `leave_approvals` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `leave_request_id` INT NOT NULL,
        `approver_id` INT NOT NULL,
        `level` INT NOT NULL DEFAULT 1,
        `status` ENUM('pending','approved','rejected') DEFAULT 'pending',
        `comments` TEXT NULL,
        `approved_at` DATETIME NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (`leave_request_id`) REFERENCES `leave_requests`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`approver_id`) REFERENCES `admin`(`aid`) ON DELETE CASCADE,

        UNIQUE KEY `unique_leave_approval` (`leave_request_id`, `approver_id`),
        INDEX `idx_leave_approval_request` (`leave_request_id`),
        INDEX `idx_leave_approval_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Leave approvals table created successfully!\n";
    }

    // Insert default leave types
    $defaultLeaveTypes = [
        ['Annual Leave', 'ANNUAL', 'Regular paid annual leave', 21, 14, 1, 1, '#28a745'],
        ['Sick Leave', 'SICK', 'Medical leave for illness', 7, 3, 1, 1, '#dc3545'],
        ['Casual Leave', 'CASUAL', 'Short-term leave for personal matters', 7, 2, 1, 1, '#ffc107'],
        ['Maternity Leave', 'MATERNITY', 'Leave for new mothers', 84, 84, 1, 1, '#e83e8c'],
        ['Paternity Leave', 'PATERNITY', 'Leave for new fathers', 7, 7, 1, 1, '#17a2b8'],
        ['Emergency Leave', 'EMERGENCY', 'Unplanned emergency situations', 3, 1, 1, 1, '#fd7e14'],
        ['Unpaid Leave', 'UNPAID', 'Leave without pay', 0, 30, 1, 0, '#6c757d']
    ];

    $insertSql = "INSERT IGNORE INTO `leave_types` (`name`, `code`, `description`, `days_per_year`, `max_consecutive_days`, `requires_approval`, `is_paid`, `color`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertSql);

    foreach ($defaultLeaveTypes as $leaveType) {
        $stmt->execute($leaveType);
    }

    echo "✅ Default leave types inserted successfully!\n";

    echo "\n🎉 Employee leave management system database setup completed!\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
