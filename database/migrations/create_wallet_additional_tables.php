<?php
/**
 * Additional Wallet System Tables
 * Creates tables for withdrawal requests, bank accounts, EMI schedules
 */

$host = 'localhost';
$port = 3307;
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to MySQL successfully\n";
    echo "📊 Database: $dbname\n\n";
    
    $pdo->exec("USE $dbname");
    echo "✅ Using database: $dbname\n\n";
    
    // ============================================
    // 1. Create withdrawal_requests table
    // ============================================
    echo "Creating withdrawal_requests table...\n";
    
    $createWithdrawalRequests = "
        CREATE TABLE IF NOT EXISTS withdrawal_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            bank_account_id INT NOT NULL,
            amount DECIMAL(10, 2) NOT NULL,
            tax_amount DECIMAL(10, 2) DEFAULT 0.00,
            net_amount DECIMAL(10, 2) NOT NULL,
            status ENUM('pending', 'approved', 'rejected', 'processing', 'completed', 'failed') DEFAULT 'pending',
            rejection_reason TEXT NULL,
            approved_by INT NULL,
            approved_at TIMESTAMP NULL,
            processed_at TIMESTAMP NULL,
            utr_number VARCHAR(50) NULL,
            remarks TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_status (status),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    $pdo->exec($createWithdrawalRequests);
    echo "✅ withdrawal_requests table created\n\n";
    
    // Add foreign key constraint
    echo "Adding foreign key constraint to withdrawal_requests...\n";
    try {
        $pdo->exec("ALTER TABLE withdrawal_requests ADD CONSTRAINT fk_withdrawal_requests_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE");
        echo "✅ Foreign key constraint added to withdrawal_requests\n\n";
    } catch (PDOException $e) {
        echo "⚠️  Foreign key constraint already exists or failed: " . $e->getMessage() . "\n\n";
    }
    
    // ============================================
    // 2. Create user_bank_accounts table
    // ============================================
    echo "Creating user_bank_accounts table...\n";
    
    $createUserBankAccounts = "
        CREATE TABLE IF NOT EXISTS user_bank_accounts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            bank_name VARCHAR(100) NOT NULL,
            account_number VARCHAR(50) NOT NULL,
            account_holder VARCHAR(100) NOT NULL,
            ifsc_code VARCHAR(20) NOT NULL,
            branch_name VARCHAR(100) NULL,
            account_type ENUM('savings', 'current', 'salary') DEFAULT 'savings',
            is_primary TINYINT(1) DEFAULT 0,
            is_verified TINYINT(1) DEFAULT 0,
            verification_method VARCHAR(50) NULL,
            verification_data TEXT NULL,
            status ENUM('active', 'inactive', 'blocked') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    $pdo->exec($createUserBankAccounts);
    echo "✅ user_bank_accounts table created\n\n";
    
    // Add foreign key constraint
    echo "Adding foreign key constraint to user_bank_accounts...\n";
    try {
        $pdo->exec("ALTER TABLE user_bank_accounts ADD CONSTRAINT fk_user_bank_accounts_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE");
        echo "✅ Foreign key constraint added to user_bank_accounts\n\n";
    } catch (PDOException $e) {
        echo "⚠️  Foreign key constraint already exists or failed: " . $e->getMessage() . "\n\n";
    }
    
    // ============================================
    // 3. Create emi_schedules table
    // ============================================
    echo "Creating emi_schedules table...\n";
    
    $createEmiSchedules = "
        CREATE TABLE IF NOT EXISTS emi_schedules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            property_id INT NULL,
            loan_amount DECIMAL(12, 2) NOT NULL,
            interest_rate DECIMAL(5, 2) DEFAULT 0.00,
            tenure_months INT NOT NULL,
            emi_amount DECIMAL(10, 2) NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            total_emi INT NOT NULL,
            paid_emi INT DEFAULT 0,
            pending_emi INT NOT NULL,
            status ENUM('active', 'completed', 'defaulted', 'closed') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_status (status),
            INDEX idx_due_date (start_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    $pdo->exec($createEmiSchedules);
    echo "✅ emi_schedules table created\n\n";
    
    // Add foreign key constraint
    echo "Adding foreign key constraint to emi_schedules...\n";
    try {
        $pdo->exec("ALTER TABLE emi_schedules ADD CONSTRAINT fk_emi_schedules_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE");
        echo "✅ Foreign key constraint added to emi_schedules\n\n";
    } catch (PDOException $e) {
        echo "⚠️  Foreign key constraint already exists or failed: " . $e->getMessage() . "\n\n";
    }
    
    // ============================================
    // 4. Create emi_payments table
    // ============================================
    echo "Creating emi_payments table...\n";
    
    $createEmiPayments = "
        CREATE TABLE IF NOT EXISTS emi_payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            emi_schedule_id INT NOT NULL,
            emi_number INT NOT NULL,
            due_date DATE NOT NULL,
            due_amount DECIMAL(10, 2) NOT NULL,
            paid_amount DECIMAL(10, 2) DEFAULT 0.00,
            payment_date DATE NULL,
            payment_method ENUM('cash', 'bank_transfer', 'wallet', 'upi', 'cheque') DEFAULT 'cash',
            wallet_transaction_id INT NULL,
            status ENUM('pending', 'paid', 'partial', 'overdue', 'defaulted') DEFAULT 'pending',
            late_fee DECIMAL(10, 2) DEFAULT 0.00,
            remarks TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_emi_schedule_id (emi_schedule_id),
            INDEX idx_status (status),
            INDEX idx_due_date (due_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    $pdo->exec($createEmiPayments);
    echo "✅ emi_payments table created\n\n";
    
    // Add foreign key constraint
    echo "Adding foreign key constraint to emi_payments...\n";
    try {
        $pdo->exec("ALTER TABLE emi_payments ADD CONSTRAINT fk_emi_payments_emi_schedule_id FOREIGN KEY (emi_schedule_id) REFERENCES emi_schedules(id) ON DELETE CASCADE");
        echo "✅ Foreign key constraint added to emi_payments\n\n";
    } catch (PDOException $e) {
        echo "⚠️  Foreign key constraint already exists or failed: " . $e->getMessage() . "\n\n";
    }
    
    // ============================================
    // 5. Update wallet_configuration with new settings
    // ============================================
    echo "Adding additional wallet configuration...\n";
    
    $additionalConfig = [
        ['minimum_withdrawal', '500', 'number', 'Minimum withdrawal amount in rupees'],
        ['withdrawal_fee_percentage', '1', 'number', 'Withdrawal fee percentage'],
        ['tax_on_withdrawal', '10', 'number', 'Tax percentage on withdrawals'],
        ['max_daily_withdrawal', '50000', 'number', 'Maximum daily withdrawal limit'],
        ['max_monthly_withdrawal', '200000', 'number', 'Maximum monthly withdrawal limit'],
        ['withdrawal_processing_days', '3', 'number', 'Days to process withdrawal'],
        ['wallet_to_emi_conversion_rate', '1', 'number', 'Conversion rate: 1 point = X rupees'],
        ['referral_expiry_days', '365', 'number', 'Days before referral points expire'],
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO wallet_configuration (config_key, config_value, config_type, description) VALUES (?, ?, ?, ?)");
    foreach ($additionalConfig as $config) {
        $stmt->execute($config);
    }
    echo "✅ Additional wallet configuration inserted\n\n";
    
    echo "🎉 Additional Wallet Tables Setup Complete!\n\n";
    echo "📊 Summary:\n";
    echo "• Created withdrawal_requests table\n";
    echo "• Created user_bank_accounts table\n";
    echo "• Created emi_schedules table\n";
    echo "• Created emi_payments table\n";
    echo "• Added additional wallet configuration\n\n";
    
    echo "✅ System is ready for advanced wallet features!\n";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
