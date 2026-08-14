<?php
/**
 * Creates insurance, investment, and address tables
 * Run: php scripts/create_user_portal_tables.php
 */
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $sqls = [
        // user_addresses
        "CREATE TABLE IF NOT EXISTS user_addresses (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            label VARCHAR(50) NOT NULL DEFAULT 'Home',
            address_type ENUM('home','office','billing','shipping','other') NOT NULL DEFAULT 'home',
            address_line1 VARCHAR(255) NOT NULL,
            address_line2 VARCHAR(255) DEFAULT NULL,
            city VARCHAR(80) NOT NULL,
            state VARCHAR(80) NOT NULL,
            pincode VARCHAR(10) NOT NULL,
            country VARCHAR(60) NOT NULL DEFAULT 'India',
            phone VARCHAR(20) DEFAULT NULL,
            is_primary TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user_addresses_user (user_id),
            INDEX idx_user_addresses_pincode (pincode)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        // insurance_plans (catalog)
        "CREATE TABLE IF NOT EXISTS insurance_plans (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            plan_code VARCHAR(30) NOT NULL UNIQUE,
            plan_name VARCHAR(120) NOT NULL,
            plan_category ENUM('home','health','term_life','vehicle','travel') NOT NULL,
            description TEXT,
            coverage_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
            premium_monthly DECIMAL(10,2) NOT NULL DEFAULT 0,
            premium_yearly DECIMAL(12,2) NOT NULL DEFAULT 0,
            features JSON DEFAULT NULL,
            insurer_name VARCHAR(120) DEFAULT NULL,
            insurer_logo_url VARCHAR(500) DEFAULT NULL,
            is_featured TINYINT(1) NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            display_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_insurance_plans_category (plan_category),
            INDEX idx_insurance_plans_active (is_active, is_featured)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        // insurance_policies (user enrollments)
        "CREATE TABLE IF NOT EXISTS insurance_policies (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            plan_id INT UNSIGNED NOT NULL,
            policy_number VARCHAR(40) NOT NULL UNIQUE,
            nominee_name VARCHAR(120) DEFAULT NULL,
            nominee_relation VARCHAR(40) DEFAULT NULL,
            sum_insured DECIMAL(14,2) NOT NULL DEFAULT 0,
            premium_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            status ENUM('active','expired','cancelled','pending') NOT NULL DEFAULT 'pending',
            payment_status ENUM('paid','pending','failed') NOT NULL DEFAULT 'pending',
            notes TEXT,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_insurance_policies_user (user_id, status),
            INDEX idx_insurance_policies_plan (plan_id),
            INDEX idx_insurance_policies_end (end_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        // investment_plans (catalog)
        "CREATE TABLE IF NOT EXISTS investment_plans (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            plan_code VARCHAR(30) NOT NULL UNIQUE,
            plan_name VARCHAR(120) NOT NULL,
            plan_category ENUM('sip','lumpsum','recurring_deposit','gold','real_estate_fund','crypto') NOT NULL,
            description TEXT,
            min_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
            max_amount DECIMAL(14,2) DEFAULT NULL,
            expected_return_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
            tenure_months INT NOT NULL DEFAULT 12,
            features JSON DEFAULT NULL,
            risk_level ENUM('low','medium','high') NOT NULL DEFAULT 'low',
            is_featured TINYINT(1) NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            display_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_investment_plans_category (plan_category),
            INDEX idx_investment_plans_active (is_active, is_featured)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        // investments (user holdings)
        "CREATE TABLE IF NOT EXISTS investments (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            plan_id INT UNSIGNED NOT NULL,
            investment_ref VARCHAR(40) NOT NULL UNIQUE,
            principal_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
            current_value DECIMAL(14,2) NOT NULL DEFAULT 0,
            units_held DECIMAL(14,4) DEFAULT NULL,
            monthly_amount DECIMAL(12,2) DEFAULT NULL,
            sip_date TINYINT DEFAULT NULL,
            start_date DATE NOT NULL,
            maturity_date DATE DEFAULT NULL,
            status ENUM('active','matured','cancelled','paused') NOT NULL DEFAULT 'active',
            auto_invest TINYINT(1) NOT NULL DEFAULT 0,
            notes TEXT,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_investments_user (user_id, status),
            INDEX idx_investments_plan (plan_id),
            INDEX idx_investments_maturity (maturity_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        // investor_levels (gamification)
        "CREATE TABLE IF NOT EXISTS investor_levels (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            level_name VARCHAR(40) NOT NULL DEFAULT 'Bronze',
            total_invested DECIMAL(14,2) NOT NULL DEFAULT 0,
            total_returns DECIMAL(14,2) NOT NULL DEFAULT 0,
            xp_points INT NOT NULL DEFAULT 0,
            next_level_threshold DECIMAL(14,2) NOT NULL DEFAULT 100000,
            last_updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_investor_levels_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    ];

    foreach ($sqls as $sql) {
        $pdo->exec($sql);
        echo "OK: " . substr($sql, 0, 80) . "...\n";
    }

    // Seed insurance_plans
    $count = $pdo->query("SELECT COUNT(*) FROM insurance_plans")->fetchColumn();
    if ($count == 0) {
        $pdo->exec("INSERT INTO insurance_plans (plan_code, plan_name, plan_category, description, coverage_amount, premium_monthly, premium_yearly, features, insurer_name, is_featured, display_order) VALUES
            ('HOME-SHIELD', 'Home Shield Plan', 'home', 'Property + Contents coverage up to â‚¹1 Cr', 10000000.00, 499.00, 5499.00, '[\"Fire, theft, natural disasters\",\"Personal accident cover\",\"Free annual inspection\"]', 'HDFC ERGO', 1, 1),
            ('FAMILY-HEALTH', 'Family Health Plan', 'health', 'Cashless treatment up to â‚¹25 Lakh', 2500000.00, 1299.00, 14299.00, '[\"10,000+ network hospitals\",\"Pre & post hospitalization cover\",\"Free annual health check-up\"]', 'Star Health', 1, 2),
            ('LIFE-SECURE', 'Life Secure Term Plan', 'term_life', 'Pure protection up to â‚¹1 Cr cover', 10000000.00, 649.00, 7199.00, '[\"Affordable premium\",\"Tax benefits under 80C\",\"Critical illness rider\"]', 'ICICI Pru', 1, 3),
            ('VEHICLE-COVER', 'Comprehensive Vehicle Cover', 'vehicle', 'Zero-dep + engine protect', 800000.00, 399.00, 4399.00, '[\"Zero depreciation\",\"Engine protect\",\"24x7 roadside assistance\"]', 'Bajaj Allianz', 0, 4),
            ('TRAVEL-SAFE', 'Travel Safe Plan', 'travel', 'International travel insurance', 5000000.00, 199.00, 2199.00, '[\"Medical emergency\",\"Trip cancellation\",\"Lost baggage cover\"]', 'TATA AIG', 0, 5)
        ");
        echo "Seeded 5 insurance_plans\n";
    }

    // Seed investment_plans
    $count = $pdo->query("SELECT COUNT(*) FROM investment_plans")->fetchColumn();
    if ($count == 0) {
        $pdo->exec("INSERT INTO investment_plans (plan_code, plan_name, plan_category, description, min_amount, expected_return_pct, tenure_months, features, risk_level, is_featured, display_order) VALUES
            ('PROP-SIP', 'Property SIP', 'sip', 'Builds corpus for property purchase', 500.00, 12.00, 24, '[\"Builds corpus for property purchase\",\"12% projected annual return\",\"Auto-invests in property fund\"]', 'medium', 1, 1),
            ('RE-FUND', 'Real Estate Fund', 'real_estate_fund', 'REIT-backed investment', 50000.00, 15.00, 36, '[\"REIT-backed investment\",\"Quarterly dividend payouts\",\"Liquidity after 1 year\"]', 'medium', 1, 2),
            ('RECURRING-FD', 'Recurring Deposit', 'recurring_deposit', 'Fixed return 7.5% p.a.', 1000.00, 7.50, 36, '[\"Fixed return: 7.5% p.a.\",\"Tax-saving under 80C\",\"Premature withdrawal allowed\"]', 'low', 0, 3),
            ('GOLD-SAVER', 'Gold Saver', 'gold', 'Digital gold from â‚¹100', 100.00, 8.00, 60, '[\"24K pure gold, 999 purity\",\"Insured vault storage\",\"Redeemable as coins/bars\"]', 'low', 0, 4)
        ");
        echo "Seeded 4 investment_plans\n";
    }

    echo "\nAll tables and seed data ready.\n";
} catch (Throwable $e) {
    echo "ERR: " . $e->getMessage() . "\n";
}?>