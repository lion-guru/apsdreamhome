<?php
/**
 * Script to create invoice generation system tables
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

    // Create invoice templates table
    $sql = "CREATE TABLE IF NOT EXISTS `invoice_templates` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `description` TEXT NULL,
        `template_html` LONGTEXT NOT NULL,
        `template_css` LONGTEXT NULL,
        `is_default` TINYINT(1) DEFAULT 0,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_by` INT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_template_default` (`is_default`),
        INDEX `idx_template_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Invoice templates table created successfully!\n";
    }

    // Create invoices table
    $sql = "CREATE TABLE IF NOT EXISTS `invoices` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `invoice_number` VARCHAR(50) NOT NULL UNIQUE,
        `invoice_date` DATE NOT NULL,
        `due_date` DATE NOT NULL,
        `client_id` INT NULL,
        `client_type` ENUM('customer','associate','vendor','employee') DEFAULT 'customer',
        `client_name` VARCHAR(255) NOT NULL,
        `client_email` VARCHAR(255) NULL,
        `client_phone` VARCHAR(20) NULL,
        `client_address` TEXT NULL,
        `billing_address` TEXT NULL,
        `shipping_address` TEXT NULL,
        `subtotal` DECIMAL(15,2) NOT NULL DEFAULT 0,
        `tax_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
        `discount_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
        `total_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
        `currency` VARCHAR(3) DEFAULT 'INR',
        `status` ENUM('draft','sent','viewed','paid','overdue','cancelled') DEFAULT 'draft',
        `payment_terms` VARCHAR(255) NULL,
        `notes` TEXT NULL,
        `template_id` INT NULL,
        `generated_by` INT NULL,
        `sent_at` DATETIME NULL,
        `paid_at` DATETIME NULL,
        `reminder_count` INT DEFAULT 0,
        `last_reminder` DATE NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        FOREIGN KEY (`template_id`) REFERENCES `invoice_templates`(`id`) ON DELETE SET NULL,
        FOREIGN KEY (`generated_by`) REFERENCES `admin`(`aid`) ON DELETE SET NULL,

        INDEX `idx_invoice_number` (`invoice_number`),
        INDEX `idx_invoice_client` (`client_id`, `client_type`),
        INDEX `idx_invoice_date` (`invoice_date`),
        INDEX `idx_invoice_due` (`due_date`),
        INDEX `idx_invoice_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Invoices table created successfully!\n";
    }

    // Create invoice items table
    $sql = "CREATE TABLE IF NOT EXISTS `invoice_items` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `invoice_id` INT NOT NULL,
        `item_type` ENUM('product','service','property','fee') DEFAULT 'service',
        `item_name` VARCHAR(255) NOT NULL,
        `item_description` TEXT NULL,
        `quantity` DECIMAL(10,2) NOT NULL DEFAULT 1,
        `unit_price` DECIMAL(15,2) NOT NULL,
        `discount_percent` DECIMAL(5,2) DEFAULT 0,
        `discount_amount` DECIMAL(15,2) DEFAULT 0,
        `tax_percent` DECIMAL(5,2) DEFAULT 0,
        `tax_amount` DECIMAL(15,2) DEFAULT 0,
        `line_total` DECIMAL(15,2) NOT NULL,
        `sort_order` INT DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE,

        INDEX `idx_invoice_item_invoice` (`invoice_id`),
        INDEX `idx_invoice_item_type` (`item_type`),
        INDEX `idx_invoice_item_sort` (`sort_order`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Invoice items table created successfully!\n";
    }

    // Create invoice payments table
    $sql = "CREATE TABLE IF NOT EXISTS `invoice_payments` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `invoice_id` INT NOT NULL,
        `payment_date` DATE NOT NULL,
        `amount` DECIMAL(15,2) NOT NULL,
        `payment_method` ENUM('cash','bank_transfer','cheque','online','card') DEFAULT 'online',
        `reference_number` VARCHAR(100) NULL,
        `notes` TEXT NULL,
        `received_by` INT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`received_by`) REFERENCES `admin`(`aid`) ON DELETE SET NULL,

        INDEX `idx_payment_invoice` (`invoice_id`),
        INDEX `idx_payment_date` (`payment_date`),
        INDEX `idx_payment_method` (`payment_method`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Invoice payments table created successfully!\n";
    }

    // Create invoice reminders table
    $sql = "CREATE TABLE IF NOT EXISTS `invoice_reminders` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `invoice_id` INT NOT NULL,
        `reminder_type` ENUM('email','sms','both') DEFAULT 'email',
        `reminder_date` DATETIME NOT NULL,
        `subject` VARCHAR(255) NULL,
        `message` TEXT NULL,
        `sent_by` INT NULL,
        `status` ENUM('pending','sent','failed') DEFAULT 'pending',
        `error_message` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`sent_by`) REFERENCES `admin`(`aid`) ON DELETE SET NULL,

        INDEX `idx_reminder_invoice` (`invoice_id`),
        INDEX `idx_reminder_date` (`reminder_date`),
        INDEX `idx_reminder_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Invoice reminders table created successfully!\n";
    }

    // Create recurring invoices table
    $sql = "CREATE TABLE IF NOT EXISTS `recurring_invoices` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `template_id` INT NULL,
        `client_id` INT NOT NULL,
        `client_type` ENUM('customer','associate','vendor','employee') DEFAULT 'customer',
        `client_name` VARCHAR(255) NOT NULL,
        `client_email` VARCHAR(255) NULL,
        `frequency` ENUM('weekly','monthly','quarterly','yearly') DEFAULT 'monthly',
        `start_date` DATE NOT NULL,
        `end_date` DATE NULL,
        `next_invoice_date` DATE NOT NULL,
        `last_generated` DATE NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `auto_send` TINYINT(1) DEFAULT 1,
        `created_by` INT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        FOREIGN KEY (`template_id`) REFERENCES `invoice_templates`(`id`) ON DELETE SET NULL,
        FOREIGN KEY (`created_by`) REFERENCES `admin`(`aid`) ON DELETE SET NULL,

        INDEX `idx_recurring_client` (`client_id`, `client_type`),
        INDEX `idx_recurring_active` (`is_active`),
        INDEX `idx_recurring_next_date` (`next_invoice_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Recurring invoices table created successfully!\n";
    }

    // Insert default invoice template
    $defaultTemplate = [
        'name' => 'Standard Invoice Template',
        'description' => 'Default professional invoice template',
        'template_html' => '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{invoice_number}}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
        .invoice-details { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .client-details, .company-details { width: 45%; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th, .items-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .items-table th { background-color: #f5f5f5; }
        .totals { text-align: right; margin-bottom: 30px; }
        .total-row { font-weight: bold; font-size: 18px; }
        .footer { border-top: 1px solid #ddd; padding-top: 20px; text-align: center; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>INVOICE</h1>
        <h2>{{company_name}}</h2>
        <p>{{company_address}}</p>
    </div>

    <div class="invoice-details">
        <div class="company-details">
            <h3>From:</h3>
            <p><strong>{{company_name}}</strong></p>
            <p>{{company_address}}</p>
            <p>Email: {{company_email}}</p>
            <p>Phone: {{company_phone}}</p>
        </div>
        <div class="client-details">
            <h3>Bill To:</h3>
            <p><strong>{{client_name}}</strong></p>
            <p>{{client_address}}</p>
            <p>Email: {{client_email}}</p>
            <p>Phone: {{client_phone}}</p>
        </div>
    </div>

    <div style="margin-bottom: 30px;">
        <p><strong>Invoice Number:</strong> {{invoice_number}}</p>
        <p><strong>Invoice Date:</strong> {{invoice_date}}</p>
        <p><strong>Due Date:</strong> {{due_date}}</p>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>Description</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Discount</th>
                <th>Tax</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            {{items}}
        </tbody>
    </table>

    <div class="totals">
        <p>Subtotal: ₹{{subtotal}}</p>
        <p>Tax: ₹{{tax_amount}}</p>
        <p>Discount: ₹{{discount_amount}}</p>
        <p class="total-row">Total: ₹{{total_amount}}</p>
    </div>

    <div class="footer">
        <p>Thank you for your business!</p>
        <p>{{payment_terms}}</p>
    </div>
</body>
</html>',
        'is_default' => 1,
        'is_active' => 1
    ];

    $insertSql = "INSERT IGNORE INTO `invoice_templates` (`name`, `description`, `template_html`, `is_default`, `is_active`) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertSql);
    $stmt->execute([
        $defaultTemplate['name'],
        $defaultTemplate['description'],
        $defaultTemplate['template_html'],
        $defaultTemplate['is_default'],
        $defaultTemplate['is_active']
    ]);

    echo "✅ Default invoice template inserted successfully!\n";

    echo "\n🎉 Invoice generation system database setup completed!\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
