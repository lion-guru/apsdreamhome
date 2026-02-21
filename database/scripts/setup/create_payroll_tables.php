<?php
/**
 * Script to create employee payroll management tables
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

    // Create salary structures table
    $sql = "CREATE TABLE IF NOT EXISTS `salary_structures` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `employee_id` INT NOT NULL,
        `basic_salary` DECIMAL(15,2) NOT NULL,
        `house_rent_allowance` DECIMAL(15,2) DEFAULT 0,
        `conveyance_allowance` DECIMAL(15,2) DEFAULT 0,
        `medical_allowance` DECIMAL(15,2) DEFAULT 0,
        `lta_allowance` DECIMAL(15,2) DEFAULT 0,
        `special_allowance` DECIMAL(15,2) DEFAULT 0,
        `other_allowances` JSON DEFAULT NULL,
        `provident_fund` DECIMAL(15,2) DEFAULT 0,
        `professional_tax` DECIMAL(15,2) DEFAULT 0,
        `income_tax` DECIMAL(15,2) DEFAULT 0,
        `other_deductions` JSON DEFAULT NULL,
        `gross_salary` DECIMAL(15,2) NOT NULL,
        `net_salary` DECIMAL(15,2) NOT NULL,
        `effective_from` DATE NOT NULL,
        `effective_to` DATE NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_employee_salary` (`employee_id`, `is_active`),
        INDEX `idx_salary_effective` (`effective_from`, `effective_to`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Salary structures table created successfully!\n";
    }

    // Create payroll runs table
    $sql = "CREATE TABLE IF NOT EXISTS `payroll_runs` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `run_name` VARCHAR(100) NOT NULL,
        `pay_period_start` DATE NOT NULL,
        `pay_period_end` DATE NOT NULL,
        `pay_date` DATE NOT NULL,
        `status` ENUM('draft','processing','completed','cancelled') DEFAULT 'draft',
        `total_employees` INT DEFAULT 0,
        `total_gross` DECIMAL(15,2) DEFAULT 0,
        `total_net` DECIMAL(15,2) DEFAULT 0,
        `total_deductions` DECIMAL(15,2) DEFAULT 0,
        `processed_by` INT NULL,
        `approved_by` INT NULL,
        `approved_at` DATETIME NULL,
        `notes` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_payroll_period` (`pay_period_start`, `pay_period_end`),
        INDEX `idx_payroll_status` (`status`),
        INDEX `idx_payroll_date` (`pay_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Payroll runs table created successfully!\n";
    }

    // Create payroll entries table
    $sql = "CREATE TABLE IF NOT EXISTS `payroll_entries` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `payroll_run_id` INT NOT NULL,
        `employee_id` INT NOT NULL,
        `basic_salary` DECIMAL(15,2) NOT NULL,
        `house_rent_allowance` DECIMAL(15,2) DEFAULT 0,
        `conveyance_allowance` DECIMAL(15,2) DEFAULT 0,
        `medical_allowance` DECIMAL(15,2) DEFAULT 0,
        `lta_allowance` DECIMAL(15,2) DEFAULT 0,
        `special_allowance` DECIMAL(15,2) DEFAULT 0,
        `other_allowances` DECIMAL(15,2) DEFAULT 0,
        `overtime_hours` DECIMAL(5,2) DEFAULT 0,
        `overtime_rate` DECIMAL(10,2) DEFAULT 0,
        `overtime_amount` DECIMAL(15,2) DEFAULT 0,
        `provident_fund` DECIMAL(15,2) DEFAULT 0,
        `professional_tax` DECIMAL(15,2) DEFAULT 0,
        `income_tax` DECIMAL(15,2) DEFAULT 0,
        `loan_deductions` DECIMAL(15,2) DEFAULT 0,
        `other_deductions` DECIMAL(15,2) DEFAULT 0,
        `gross_earnings` DECIMAL(15,2) NOT NULL,
        `total_deductions` DECIMAL(15,2) DEFAULT 0,
        `net_salary` DECIMAL(15,2) NOT NULL,
        `payment_status` ENUM('pending','paid','failed','cancelled') DEFAULT 'pending',
        `payment_date` DATE NULL,
        `payment_method` ENUM('bank_transfer','cash','cheque','online') DEFAULT 'bank_transfer',
        `bank_reference` VARCHAR(100) NULL,
        `remarks` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_payroll_entry_run` (`payroll_run_id`),
        INDEX `idx_payroll_entry_employee` (`employee_id`),
        INDEX `idx_payroll_payment_status` (`payment_status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Payroll entries table created successfully!\n";
    }

    // Create salary advances table
    $sql = "CREATE TABLE IF NOT EXISTS `salary_advances` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `employee_id` INT NOT NULL,
        `advance_amount` DECIMAL(15,2) NOT NULL,
        `reason` TEXT NOT NULL,
        `requested_date` DATE NOT NULL,
        `approved_date` DATE NULL,
        `repayment_months` INT NOT NULL,
        `monthly_deduction` DECIMAL(15,2) NOT NULL,
        `status` ENUM('pending','approved','rejected','completed','cancelled') DEFAULT 'pending',
        `approved_by` INT NULL,
        `approved_at` DATETIME NULL,
        `remarks` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_advance_employee` (`employee_id`),
        INDEX `idx_advance_status` (`status`),
        INDEX `idx_advance_date` (`requested_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Salary advances table created successfully!\n";
    }

    // Create tax slabs table
    $sql = "CREATE TABLE IF NOT EXISTS `tax_slabs` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `slab_name` VARCHAR(50) NOT NULL,
        `min_income` DECIMAL(15,2) NOT NULL,
        `max_income` DECIMAL(15,2) NULL,
        `tax_rate` DECIMAL(5,2) NOT NULL,
        `cess_rate` DECIMAL(5,2) DEFAULT 4.00,
        `financial_year` YEAR NOT NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        INDEX `idx_tax_slab_year` (`financial_year`, `is_active`),
        INDEX `idx_tax_income_range` (`min_income`, `max_income`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Tax slabs table created successfully!\n";
    }

    // Insert default tax slabs for current financial year
    $currentYear = date('Y');
    $taxSlabs = [
        ['Up to ₹2.5L', 0, 250000, 0, 4, $currentYear],
        ['₹2.5L - ₹5L', 250000, 500000, 5, 4, $currentYear],
        ['₹5L - ₹10L', 500000, 1000000, 20, 4, $currentYear],
        ['Above ₹10L', 1000000, null, 30, 4, $currentYear]
    ];

    $insertSql = "INSERT IGNORE INTO `tax_slabs` (`slab_name`, `min_income`, `max_income`, `tax_rate`, `cess_rate`, `financial_year`) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertSql);

    foreach ($taxSlabs as $slab) {
        $stmt->execute($slab);
    }

    echo "✅ Default tax slabs inserted successfully!\n";

    // Insert sample salary structure for testing
    $sampleSalary = [
        'employee_id' => 1,
        'basic_salary' => 30000.00,
        'house_rent_allowance' => 9000.00,
        'conveyance_allowance' => 1920.00,
        'medical_allowance' => 1500.00,
        'lta_allowance' => 1500.00,
        'special_allowance' => 5000.00,
        'provident_fund' => 3600.00,
        'professional_tax' => 2400.00,
        'income_tax' => 3000.00,
        'gross_salary' => 50000.00,
        'net_salary' => 38000.00,
        'effective_from' => date('Y-m-01'),
        'is_active' => 1
    ];

    $columns = implode(', ', array_keys($sampleSalary));
    $placeholders = implode(', ', array_fill(0, count($sampleSalary), '?'));
    $insertSalarySql = "INSERT IGNORE INTO `salary_structures` ($columns) VALUES ($placeholders)";

    $stmt = $pdo->prepare($insertSalarySql);
    $stmt->execute(array_values($sampleSalary));

    echo "✅ Sample salary structure inserted successfully!\n";

    echo "\n🎉 Employee payroll management system database setup completed!\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
