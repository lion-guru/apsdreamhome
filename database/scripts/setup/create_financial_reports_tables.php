<?php
/**
 * Script to create financial reporting system tables
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

    // Create chart of accounts table
    $sql = "CREATE TABLE IF NOT EXISTS `chart_of_accounts` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `account_code` VARCHAR(20) NOT NULL UNIQUE,
        `account_name` VARCHAR(255) NOT NULL,
        `account_type` ENUM('asset','liability','equity','income','expense') NOT NULL,
        `account_subtype` ENUM('current_asset','fixed_asset','current_liability','long_term_liability','owners_equity','retained_earnings','sales_revenue','other_income','cost_of_goods_sold','operating_expense','other_expense') NULL,
        `parent_account_id` INT NULL,
        `description` TEXT NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `opening_balance` DECIMAL(15,2) DEFAULT 0,
        `current_balance` DECIMAL(15,2) DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        FOREIGN KEY (`parent_account_id`) REFERENCES `chart_of_accounts`(`id`) ON DELETE SET NULL,
        INDEX `idx_account_type` (`account_type`),
        INDEX `idx_account_subtype` (`account_subtype`),
        INDEX `idx_account_code` (`account_code`),
        INDEX `idx_account_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Chart of accounts table created successfully!\n";
    }

    // Create journal entries table
    $sql = "CREATE TABLE IF NOT EXISTS `journal_entries` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `entry_number` VARCHAR(20) NOT NULL UNIQUE,
        `entry_date` DATE NOT NULL,
        `description` TEXT NOT NULL,
        `reference_type` ENUM('invoice','payment','expense','journal','adjustment','opening_balance') DEFAULT 'journal',
        `reference_id` INT NULL,
        `total_debit` DECIMAL(15,2) NOT NULL DEFAULT 0,
        `total_credit` DECIMAL(15,2) NOT NULL DEFAULT 0,
        `status` ENUM('draft','posted','voided') DEFAULT 'draft',
        `posted_by` INT NULL,
        `posted_at` DATETIME NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_entry_date` (`entry_date`),
        INDEX `idx_entry_status` (`status`),
        INDEX `idx_entry_reference` (`reference_type`, `reference_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Journal entries table created successfully!\n";
    }

    // Create journal entry lines table
    $sql = "CREATE TABLE IF NOT EXISTS `journal_entry_lines` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `journal_entry_id` INT NOT NULL,
        `account_id` INT NOT NULL,
        `debit_amount` DECIMAL(15,2) DEFAULT 0,
        `credit_amount` DECIMAL(15,2) DEFAULT 0,
        `description` TEXT NULL,
        `line_order` INT DEFAULT 0,

        FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`account_id`) REFERENCES `chart_of_accounts`(`id`) ON DELETE CASCADE,

        INDEX `idx_journal_line_entry` (`journal_entry_id`),
        INDEX `idx_journal_line_account` (`account_id`),
        INDEX `idx_journal_line_order` (`line_order`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Journal entry lines table created successfully!\n";
    }

    // Create financial periods table
    $sql = "CREATE TABLE IF NOT EXISTS `financial_periods` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `period_name` VARCHAR(100) NOT NULL,
        `period_type` ENUM('monthly','quarterly','yearly') DEFAULT 'monthly',
        `start_date` DATE NOT NULL,
        `end_date` DATE NOT NULL,
        `is_closed` TINYINT(1) DEFAULT 0,
        `closed_by` INT NULL,
        `closed_at` DATETIME NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        INDEX `idx_period_dates` (`start_date`, `end_date`),
        INDEX `idx_period_type` (`period_type`),
        INDEX `idx_period_closed` (`is_closed`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Financial periods table created successfully!\n";
    }

    // Create budget table
    $sql = "CREATE TABLE IF NOT EXISTS `budgets` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `budget_name` VARCHAR(255) NOT NULL,
        `period_type` ENUM('monthly','quarterly','yearly') DEFAULT 'monthly',
        `start_date` DATE NOT NULL,
        `end_date` DATE NOT NULL,
        `total_budget` DECIMAL(15,2) NOT NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_by` INT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_budget_dates` (`start_date`, `end_date`),
        INDEX `idx_budget_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Budgets table created successfully!\n";
    }

    // Create budget items table
    $sql = "CREATE TABLE IF NOT EXISTS `budget_items` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `budget_id` INT NOT NULL,
        `account_id` INT NOT NULL,
        `budgeted_amount` DECIMAL(15,2) NOT NULL,
        `actual_amount` DECIMAL(15,2) DEFAULT 0,
        `variance` DECIMAL(15,2) DEFAULT 0,

        FOREIGN KEY (`budget_id`) REFERENCES `budgets`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`account_id`) REFERENCES `chart_of_accounts`(`id`) ON DELETE CASCADE,

        INDEX `idx_budget_item_budget` (`budget_id`),
        INDEX `idx_budget_item_account` (`account_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Budget items table created successfully!\n";
    }

    // Insert default chart of accounts
    $defaultAccounts = [
        // Assets
        ['1001', 'Cash in Hand', 'asset', 'current_asset', null, 'Physical cash available'],
        ['1002', 'Bank Account - Current', 'asset', 'current_asset', null, 'Primary business bank account'],
        ['1003', 'Accounts Receivable', 'asset', 'current_asset', null, 'Money owed by customers'],
        ['1004', 'Inventory', 'asset', 'current_asset', null, 'Stock of goods for sale'],
        ['1005', 'Office Equipment', 'asset', 'fixed_asset', null, 'Computers, furniture, etc.'],
        ['1006', 'Building', 'asset', 'fixed_asset', null, 'Business premises'],

        // Liabilities
        ['2001', 'Accounts Payable', 'liability', 'current_liability', null, 'Money owed to suppliers'],
        ['2002', 'Bank Loan - Short Term', 'liability', 'current_liability', null, 'Short-term loans'],
        ['2003', 'GST Payable', 'liability', 'current_liability', null, 'GST amounts to be paid'],
        ['2004', 'TDS Payable', 'liability', 'current_liability', null, 'TDS amounts to be paid'],

        // Equity
        ['3001', 'Owner\'s Capital', 'equity', 'owners_equity', null, 'Initial investment by owners'],
        ['3002', 'Retained Earnings', 'equity', 'retained_earnings', null, 'Accumulated profits'],

        // Income
        ['4001', 'Sales Revenue', 'income', 'sales_revenue', null, 'Revenue from property sales'],
        ['4002', 'Service Revenue', 'income', 'sales_revenue', null, 'Revenue from services'],
        ['4003', 'Commission Income', 'income', 'other_income', null, 'Commission from associates'],
        ['4004', 'Interest Income', 'income', 'other_income', null, 'Interest earned'],

        // Expenses
        ['5001', 'Cost of Goods Sold', 'expense', 'cost_of_goods_sold', null, 'Direct cost of goods sold'],
        ['5002', 'Salaries & Wages', 'expense', 'operating_expense', null, 'Employee compensation'],
        ['5003', 'Rent Expense', 'expense', 'operating_expense', null, 'Office rent'],
        ['5004', 'Utilities', 'expense', 'operating_expense', null, 'Electricity, water, internet'],
        ['5005', 'Marketing & Advertising', 'expense', 'operating_expense', null, 'Marketing expenses'],
        ['5006', 'Office Supplies', 'expense', 'operating_expense', null, 'Stationery and supplies'],
        ['5007', 'Professional Fees', 'expense', 'operating_expense', null, 'Legal, accounting fees'],
        ['5008', 'Depreciation', 'expense', 'operating_expense', null, 'Asset depreciation'],
        ['5009', 'GST Expense', 'expense', 'operating_expense', null, 'GST paid on purchases'],
        ['5010', 'Bad Debts', 'expense', 'other_expense', null, 'Uncollectible receivables']
    ];

    $insertSql = "INSERT IGNORE INTO `chart_of_accounts` (`account_code`, `account_name`, `account_type`, `account_subtype`, `parent_account_id`, `description`, `is_active`) VALUES (?, ?, ?, ?, ?, ?, 1)";
    $stmt = $pdo->prepare($insertSql);

    foreach ($defaultAccounts as $account) {
        $stmt->execute($account);
    }

    echo "✅ Default chart of accounts inserted successfully!\n";

    // Insert current financial period
    $currentYear = date('Y');
    $financialPeriods = [
        ['FY ' . $currentYear, 'yearly', $currentYear . '-04-01', ($currentYear + 1) . '-03-31', 0],
        ['Q1 ' . $currentYear, 'quarterly', $currentYear . '-04-01', $currentYear . '-06-30', 0],
        ['Q2 ' . $currentYear, 'quarterly', $currentYear . '-07-01', $currentYear . '-09-30', 0],
        ['Q3 ' . $currentYear, 'quarterly', $currentYear . '-10-01', $currentYear . '-12-31', 0],
        ['Q4 ' . $currentYear, 'quarterly', ($currentYear + 1) . '-01-01', ($currentYear + 1) . '-03-31', 0]
    ];

    $periodSql = "INSERT IGNORE INTO `financial_periods` (`period_name`, `period_type`, `start_date`, `end_date`, `is_closed`) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($periodSql);

    foreach ($financialPeriods as $period) {
        $stmt->execute($period);
    }

    echo "✅ Default financial periods inserted successfully!\n";

    echo "\n🎉 Financial reporting system database setup completed!\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
