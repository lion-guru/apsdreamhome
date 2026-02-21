<?php
/**
 * Script to create GST/Tax reporting system tables
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

    // Create GST settings table
    $sql = "CREATE TABLE IF NOT EXISTS `gst_settings` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `gstin` VARCHAR(15) NOT NULL COMMENT 'GST Identification Number',
        `business_name` VARCHAR(255) NOT NULL,
        `business_address` TEXT NOT NULL,
        `state_code` VARCHAR(2) NOT NULL,
        `state_name` VARCHAR(100) NOT NULL,
        `contact_person` VARCHAR(255) NULL,
        `contact_email` VARCHAR(255) NULL,
        `contact_phone` VARCHAR(20) NULL,
        `gst_type` ENUM('regular','composite','casual_taxable','non_resident') DEFAULT 'regular',
        `registration_date` DATE NULL,
        `threshold_limit` DECIMAL(15,2) DEFAULT 2000000 COMMENT 'GST threshold limit',
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_gstin` (`gstin`),
        INDEX `idx_gst_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ GST settings table created successfully!\n";
    }

    // Create HSN/SAC codes table
    $sql = "CREATE TABLE IF NOT EXISTS `hsn_sac_codes` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `code` VARCHAR(10) NOT NULL UNIQUE,
        `description` VARCHAR(500) NOT NULL,
        `type` ENUM('goods','services') DEFAULT 'goods',
        `cgst_rate` DECIMAL(5,2) DEFAULT 0,
        `sgst_rate` DECIMAL(5,2) DEFAULT 0,
        `igst_rate` DECIMAL(5,2) DEFAULT 0,
        `cess_rate` DECIMAL(5,2) DEFAULT 0,
        `effective_from` DATE NOT NULL,
        `effective_to` DATE NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        INDEX `idx_hsn_code` (`code`),
        INDEX `idx_hsn_type` (`type`),
        INDEX `idx_hsn_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ HSN/SAC codes table created successfully!\n";
    }

    // Create GST invoice details table
    $sql = "CREATE TABLE IF NOT EXISTS `gst_invoice_details` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `invoice_id` INT NOT NULL,
        `invoice_type` ENUM('b2b','b2c','export','sez') DEFAULT 'b2b',
        `place_of_supply` VARCHAR(2) NULL COMMENT 'State code for place of supply',
        `reverse_charge` ENUM('yes','no') DEFAULT 'no',
        `gst_payment_status` ENUM('paid','pending','exempted') DEFAULT 'pending',
        `eway_bill_no` VARCHAR(20) NULL,
        `eway_bill_date` DATE NULL,
        `vehicle_no` VARCHAR(20) NULL,
        `distance` INT NULL COMMENT 'Distance in KM',
        `transport_mode` ENUM('road','rail','air','ship','other') DEFAULT 'road',
        `transport_name` VARCHAR(255) NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE,
        INDEX `idx_gst_invoice` (`invoice_id`),
        INDEX `idx_gst_type` (`invoice_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ GST invoice details table created successfully!\n";
    }

    // Create GST returns table
    $sql = "CREATE TABLE IF NOT EXISTS `gst_returns` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `return_type` ENUM('gstr1','gstr2a','gstr2b','gstr3b','gstr4','gstr9','gstr9c') NOT NULL,
        `period_from` DATE NOT NULL,
        `period_to` DATE NOT NULL,
        `filing_date` DATE NULL,
        `due_date` DATE NOT NULL,
        `status` ENUM('pending','filed','extended','amended') DEFAULT 'pending',
        `arn_no` VARCHAR(20) NULL COMMENT 'Acknowledgement Reference Number',
        `json_file_path` VARCHAR(500) NULL,
        `remarks` TEXT NULL,
        `filed_by` INT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_return_type` (`return_type`),
        INDEX `idx_return_period` (`period_from`, `period_to`),
        INDEX `idx_return_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ GST returns table created successfully!\n";
    }

    // Create tax ledgers table
    $sql = "CREATE TABLE IF NOT EXISTS `tax_ledgers` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `ledger_type` ENUM('cgst','sgst','igst','cess','tds','tcs') NOT NULL,
        `transaction_date` DATE NOT NULL,
        `invoice_id` INT NULL,
        `particulars` VARCHAR(500) NOT NULL,
        `debit_amount` DECIMAL(15,2) DEFAULT 0,
        `credit_amount` DECIMAL(15,2) DEFAULT 0,
        `balance` DECIMAL(15,2) NOT NULL,
        `reference_no` VARCHAR(100) NULL,
        `created_by` INT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE SET NULL,
        INDEX `idx_ledger_type` (`ledger_type`),
        INDEX `idx_ledger_date` (`transaction_date`),
        INDEX `idx_ledger_invoice` (`invoice_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Tax ledgers table created successfully!\n";
    }

    // Insert default GST settings
    $gstSettings = [
        'gstin' => '22AAAAA0000A1Z5',
        'business_name' => 'APS Dream Home Realty Pvt Ltd',
        'business_address' => '123 Business Street, New Delhi - 110001',
        'state_code' => '07',
        'state_name' => 'Delhi',
        'contact_person' => 'Rajesh Kumar',
        'contact_email' => 'accounts@apsdreamhome.com',
        'contact_phone' => '+91-9876543210',
        'gst_type' => 'regular',
        'registration_date' => '2020-01-01',
        'threshold_limit' => 2000000.00,
        'is_active' => 1
    ];

    $insertSql = "INSERT IGNORE INTO `gst_settings` (`gstin`, `business_name`, `business_address`, `state_code`, `state_name`, `contact_person`, `contact_email`, `contact_phone`, `gst_type`, `registration_date`, `threshold_limit`, `is_active`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertSql);
    $stmt->execute(array_values($gstSettings));

    echo "✅ Default GST settings inserted successfully!\n";

    // Insert common HSN/SAC codes for real estate
    $hsnCodes = [
        ['997211', 'Real estate services', 'services', 9, 9, 18, 0, '2020-01-01'],
        ['997212', 'Leasing or rental services concerning buildings', 'services', 9, 9, 18, 0, '2020-01-01'],
        ['997213', 'Leasing or rental services concerning land', 'services', 9, 9, 18, 0, '2020-01-01'],
        ['997319', 'Other professional, technical and business services', 'services', 9, 9, 18, 0, '2020-01-01'],
        ['999799', 'Other services not specified elsewhere', 'services', 9, 9, 18, 0, '2020-01-01']
    ];

    $insertHsnSql = "INSERT IGNORE INTO `hsn_sac_codes` (`code`, `description`, `type`, `cgst_rate`, `sgst_rate`, `igst_rate`, `cess_rate`, `effective_from`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertHsnSql);

    foreach ($hsnCodes as $hsn) {
        $stmt->execute($hsn);
    }

    echo "✅ Default HSN/SAC codes inserted successfully!\n";

    echo "\n🎉 GST/Tax reporting system database setup completed!\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
