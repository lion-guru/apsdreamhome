<?php
/**
 * Create Missing Database Tables Migration
 * Creates agents, commissions, and payouts tables with proper foreign keys
 */

echo "🔧 CREATING MISSING DATABASE TABLES\n";
echo "====================================\n\n";

try {
    // Database connection
    $host = 'localhost';
    $dbname = 'apsdreamhome';
    $username = 'root';
    $password = '';

    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    echo "✅ Database connection established\n\n";

    // Create agents table first (referenced by other tables)
    echo "Creating agents table...\n";
    $agentsSql = "
        CREATE TABLE IF NOT EXISTS agents (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            license_number VARCHAR(50) NULL,
            experience_years INT DEFAULT 0,
            bank_name VARCHAR(100) NULL,
            account_number VARCHAR(50) NULL,
            ifsc_code VARCHAR(20) NULL,
            status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_user_agent (user_id),
            INDEX idx_user_id (user_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($agentsSql);
    echo "✅ Agents table created successfully\n";

    // Create commissions table (references users table)
    echo "Creating commissions table...\n";
    $commissionsSql = "
        CREATE TABLE IF NOT EXISTS commissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            associate_id INT NOT NULL,
            referred_user_id INT NULL,
            amount DECIMAL(10,2) NOT NULL,
            commission_type ENUM('direct', 'team', 'referral', 'bonus') DEFAULT 'direct',
            status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
            paid_at TIMESTAMP NULL,
            description TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_associate_id (associate_id),
            INDEX idx_referred_user_id (referred_user_id),
            INDEX idx_status (status),
            INDEX idx_commission_type (commission_type),
            INDEX idx_paid_at (paid_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($commissionsSql);
    echo "✅ Commissions table created successfully\n";

    // Create payouts table (references users table)
    echo "Creating payouts table...\n";
    $payoutsSql = "
        CREATE TABLE IF NOT EXISTS payouts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            associate_id INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            payment_method ENUM('bank_transfer', 'upi', 'paypal', 'check') DEFAULT 'bank_transfer',
            status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
            reference_number VARCHAR(100) NULL,
            paid_at TIMESTAMP NULL,
            notes TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_associate_id (associate_id),
            INDEX idx_status (status),
            INDEX idx_payment_method (payment_method),
            INDEX idx_paid_at (paid_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($payoutsSql);
    echo "✅ Payouts table created successfully\n";

    // Now add foreign key constraints (after tables are created)
    echo "\nAdding foreign key constraints...\n";

    // Add foreign keys to agents table
    try {
        $pdo->exec("ALTER TABLE agents ADD CONSTRAINT fk_agents_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE");
        echo "✅ Foreign key constraint added to agents table\n";
    } catch (Exception $e) {
        echo "⚠️  Foreign key constraint for agents table may already exist or users table not ready: " . $e->getMessage() . "\n";
    }

    // Add foreign keys to commissions table
    try {
        $pdo->exec("ALTER TABLE commissions ADD CONSTRAINT fk_commissions_associate_id FOREIGN KEY (associate_id) REFERENCES users(id) ON DELETE CASCADE");
        $pdo->exec("ALTER TABLE commissions ADD CONSTRAINT fk_commissions_referred_user_id FOREIGN KEY (referred_user_id) REFERENCES users(id) ON DELETE SET NULL");
        echo "✅ Foreign key constraints added to commissions table\n";
    } catch (Exception $e) {
        echo "⚠️  Foreign key constraints for commissions table may already exist or users table not ready: " . $e->getMessage() . "\n";
    }

    // Add foreign keys to payouts table
    try {
        $pdo->exec("ALTER TABLE payouts ADD CONSTRAINT fk_payouts_associate_id FOREIGN KEY (associate_id) REFERENCES users(id) ON DELETE CASCADE");
        echo "✅ Foreign key constraint added to payouts table\n";
    } catch (Exception $e) {
        echo "⚠️  Foreign key constraint for payouts table may already exist or users table not ready: " . $e->getMessage() . "\n";
    }

    // Verify tables were created
    echo "\nVerifying table creation...\n";

    $tablesToCheck = ['agents', 'commissions', 'payouts'];
    foreach ($tablesToCheck as $table) {
        try {
            $result = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($result->rowCount() > 0) {
                echo "✅ Table '$table' exists\n";

                // Check if table has data
                $countResult = $pdo->query("SELECT COUNT(*) as count FROM $table");
                $count = $countResult->fetch()['count'];
                echo "   📊 Records: $count\n";
            } else {
                echo "❌ Table '$table' was not created\n";
            }
        } catch (Exception $e) {
            echo "❌ Error checking table '$table': " . $e->getMessage() . "\n";
        }
    }

    echo "\n🎉 MISSING DATABASE TABLES CREATION COMPLETED!\n";
    echo "All required tables have been created with proper structure and constraints.\n";

    // Save completion log
    file_put_contents(__DIR__ . '/table_creation_completed.log', date('Y-m-d H:i:s') . " - Missing database tables created successfully\n", FILE_APPEND);

} catch (PDOException $e) {
    echo "❌ DATABASE ERROR: " . $e->getMessage() . "\n";
    echo "Please check your database connection and table schemas.\n";
    // DEBUG CODE REMOVED: 2026-02-22 19:56:15 CODE REMOVED: 2026-02-22 19:56:15
} catch (Exception $e) {
    echo "❌ GENERAL ERROR: " . $e->getMessage() . "\n";
    // DEBUG CODE REMOVED: 2026-02-22 19:56:15 CODE REMOVED: 2026-02-22 19:56:15
}

?>
