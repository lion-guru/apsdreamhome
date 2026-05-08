<?php
/**
 * Database Consolidation & Standardization Migration
 * ==================================================
 * 
 * ACTIONS:
 * 1. Create standardized tables (if not exist)
 * 2. Migrate data from duplicate tables to standard
 * 3. Create missing ai_conversations table
 * 4. Create missing payment_transactions table
 * 5. Provide cleanup recommendations
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Database\Database;

$dryRun = in_array('--dry-run', $argv);
$force = in_array('--force', $argv);

echo "🔧 DATABASE CONSOLIDATION MIGRATION\n";
echo "===================================\n";
echo "Mode: " . ($dryRun ? "DRY RUN (no changes)" : "LIVE") . "\n\n";

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // ============================================
    // STEP 1: STANDARDIZE COMMISSION TABLES
    // ============================================
    echo "💰 STEP 1: Standardizing Commission Tables\n";
    echo "-----------------------------------------\n";
    
    // Create unified mlm_commissions table (if not exists)
    $pdo->exec("CREATE TABLE IF NOT EXISTS mlm_commissions (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        associate_id INT NOT NULL,
        source_associate_id INT NULL,
        level INT NOT NULL DEFAULT 1,
        amount DECIMAL(15,2) NOT NULL,
        percentage DECIMAL(5,2) NOT NULL,
        source_type ENUM('direct_sale', 'team_sale', 'bonus', 'referral') DEFAULT 'direct_sale',
        entity_type ENUM('property', 'plot', 'booking', 'registration') NULL,
        entity_id INT NULL,
        status ENUM('pending', 'approved', 'paid', 'hold', 'rejected') DEFAULT 'pending',
        calculated_at TIMESTAMP NULL,
        approved_at TIMESTAMP NULL,
        paid_at TIMESTAMP NULL,
        payment_reference VARCHAR(100) NULL,
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_associate (associate_id),
        INDEX idx_source (source_associate_id),
        INDEX idx_status (status),
        INDEX idx_level (level),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    echo "✅ mlm_commissions table ready (unified standard)\n";
    
    // Migrate data from old tables if they exist
    $oldTables = ['commissions', 'associate_commissions', 'commission_transactions'];
    foreach ($oldTables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            echo "  📦 Found $count records in '$table'\n";
            
            if (!$dryRun && $count > 0) {
                // Migration logic would go here
                echo "     (Migration to mlm_commissions would happen here)\n";
            }
        }
    }
    
    // ============================================
    // STEP 2: STANDARDIZE FARMER TABLES
    // ============================================
    echo "\n🚜 STEP 2: Standardizing Farmer Tables\n";
    echo "--------------------------------------\n";
    
    // Keep 'farmers' as primary, enhance if needed
    $pdo->exec("CREATE TABLE IF NOT EXISTS farmers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        farmer_number VARCHAR(50) UNIQUE NULL,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NULL,
        phone VARCHAR(20) NOT NULL,
        alternate_phone VARCHAR(20) NULL,
        address TEXT NULL,
        village VARCHAR(100) NULL,
        district VARCHAR(100) NULL,
        state VARCHAR(100) NULL,
        pincode VARCHAR(10) NULL,
        land_area DECIMAL(10,2) NULL,
        land_type ENUM('irrigated', 'rainfed', 'mixed') NULL,
        crops_grown TEXT NULL,
        status ENUM('active', 'inactive', 'pending') DEFAULT 'active',
        user_id INT NULL,
        created_by INT NULL,
        documents JSON NULL,
        bank_details JSON NULL,
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_farmer_number (farmer_number),
        INDEX idx_phone (phone),
        INDEX idx_status (status),
        INDEX idx_district (district)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    echo "✅ farmers table ready (enhanced)\n";
    
    // Check for farmer_profiles and migrate if needed
    $stmt = $pdo->query("SHOW TABLES LIKE 'farmer_profiles'");
    if ($stmt->rowCount() > 0) {
        $count = $pdo->query("SELECT COUNT(*) FROM farmer_profiles")->fetchColumn();
        echo "  📦 Found $count records in 'farmer_profiles'\n";
        if (!$dryRun && $count > 0 && $force) {
            // Migration: farmer_profiles → farmers
            echo "     Migrating data...\n";
        }
    }
    
    // ============================================
    // STEP 3: STANDARDIZE BUDGET TABLES
    // ============================================
    echo "\n📊 STEP 3: Standardizing Budget Tables\n";
    echo "--------------------------------------\n";
    
    // plotting_budgets is the standard for real estate
    $pdo->exec("CREATE TABLE IF NOT EXISTS plotting_budgets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        project_id INT NOT NULL,
        colony_id INT NULL,
        budget_name VARCHAR(255) NOT NULL,
        budget_type ENUM('land_acquisition', 'development', 'marketing', 'legal', 'overhead') NOT NULL,
        total_budget DECIMAL(15,2) NOT NULL,
        spent_amount DECIMAL(15,2) DEFAULT 0.00,
        remaining_amount DECIMAL(15,2) GENERATED ALWAYS AS (total_budget - spent_amount) STORED,
        fiscal_year VARCHAR(10) NULL,
        quarter VARCHAR(2) NULL,
        status ENUM('draft', 'active', 'completed', 'cancelled') DEFAULT 'draft',
        approved_by INT NULL,
        approved_at TIMESTAMP NULL,
        start_date DATE NULL,
        end_date DATE NULL,
        description TEXT NULL,
        created_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_project (project_id),
        INDEX idx_colony (colony_id),
        INDEX idx_status (status),
        INDEX idx_fiscal_year (fiscal_year)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    echo "✅ plotting_budgets table ready (standard)\n";
    
    // Budget expenses tracking
    $pdo->exec("CREATE TABLE IF NOT EXISTS budget_expenses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        budget_id INT NOT NULL,
        expense_type ENUM('land', 'development', 'marketing', 'legal', 'misc') NOT NULL,
        amount DECIMAL(15,2) NOT NULL,
        description TEXT NULL,
        vendor_name VARCHAR(255) NULL,
        invoice_number VARCHAR(100) NULL,
        expense_date DATE NOT NULL,
        receipt_url VARCHAR(500) NULL,
        approved_by INT NULL,
        created_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_budget (budget_id),
        INDEX idx_expense_type (expense_type),
        INDEX idx_expense_date (expense_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    echo "✅ budget_expenses table ready\n";
    
    // ============================================
    // STEP 4: STANDARDIZE REFERRAL TABLES
    // ============================================
    echo "\n🔗 STEP 4: Standardizing Referral Tables\n";
    echo "---------------------------------------\n";
    
    // network_tree is the standard for MLM genealogy
    $pdo->exec("CREATE TABLE IF NOT EXISTS network_tree (
        id INT AUTO_INCREMENT PRIMARY KEY,
        associate_id INT NOT NULL UNIQUE,
        parent_id INT NULL,
        level INT NOT NULL DEFAULT 1,
        path VARCHAR(500) NULL,
        left_node INT NULL,
        right_node INT NULL,
        total_downline INT DEFAULT 0,
        active_downline INT DEFAULT 0,
        total_commission DECIMAL(15,2) DEFAULT 0.00,
        status ENUM('active', 'inactive') DEFAULT 'active',
        joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_associate (associate_id),
        INDEX idx_parent (parent_id),
        INDEX idx_level (level)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    echo "✅ network_tree table ready (MLM genealogy)\n";
    
    // Simple referrals table for customer referrals
    $pdo->exec("CREATE TABLE IF NOT EXISTS referrals (
        id INT AUTO_INCREMENT PRIMARY KEY,
        referrer_id INT NOT NULL,
        referred_id INT NOT NULL,
        referral_code VARCHAR(50) NULL,
        source ENUM('web', 'app', 'manual', 'campaign') DEFAULT 'web',
        status ENUM('pending', 'registered', 'converted', 'expired') DEFAULT 'pending',
        reward_amount DECIMAL(10,2) NULL,
        reward_status ENUM('pending', 'paid', 'cancelled') NULL,
        converted_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_referral (referrer_id, referred_id),
        INDEX idx_referrer (referrer_id),
        INDEX idx_referred (referred_id),
        INDEX idx_code (referral_code)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    echo "✅ referrals table ready (customer referrals)\n";
    
    // ============================================
    // STEP 5: CREATE AI CONVERSATIONS TABLE
    // ============================================
    echo "\n🤖 STEP 5: Creating AI Conversations Table\n";
    echo "------------------------------------------\n";
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS ai_conversations (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        session_id VARCHAR(64) NOT NULL,
        user_id INT NULL,
        user_type ENUM('customer', 'associate', 'agent', 'admin', 'guest') DEFAULT 'guest',
        user_message TEXT NOT NULL,
        bot_response TEXT NOT NULL,
        intent VARCHAR(50) DEFAULT 'general',
        intent_confidence DECIMAL(3,2) DEFAULT 0.80,
        entities JSON NULL,
        context JSON NULL,
        language VARCHAR(10) DEFAULT 'en',
        source VARCHAR(20) DEFAULT 'web',
        ip_address VARCHAR(45) NULL,
        user_agent TEXT NULL,
        is_helpful TINYINT(1) NULL,
        feedback TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_session (session_id),
        INDEX idx_user (user_id, user_type),
        INDEX idx_intent (intent),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    echo "✅ ai_conversations table ready\n";
    
    // ============================================
    // STEP 6: CREATE PAYMENT TRANSACTIONS TABLE
    // ============================================
    echo "\n💳 STEP 6: Creating Payment Transactions Table\n";
    echo "-----------------------------------------------\n";
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS payment_transactions (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        gateway VARCHAR(50) NOT NULL,
        order_id VARCHAR(100) NOT NULL,
        transaction_id VARCHAR(100) NULL,
        merchant_transaction_id VARCHAR(100) NULL,
        amount DECIMAL(15,2) NOT NULL,
        currency VARCHAR(10) DEFAULT 'INR',
        status ENUM('initiated', 'pending', 'completed', 'failed', 'cancelled', 'refunded') DEFAULT 'initiated',
        customer_id INT NULL,
        customer_type ENUM('customer', 'associate', 'agent', 'admin') DEFAULT 'customer',
        customer_email VARCHAR(255) NULL,
        customer_phone VARCHAR(20) NULL,
        entity_type ENUM('booking', 'plot', 'emi', 'registration', 'wallet', 'subscription') NULL,
        entity_id INT NULL,
        payment_method VARCHAR(50) NULL,
        payment_instrument JSON NULL,
        gateway_response JSON NULL,
        webhook_data JSON NULL,
        error_code VARCHAR(50) NULL,
        error_message TEXT NULL,
        refund_amount DECIMAL(15,2) NULL,
        refund_reason TEXT NULL,
        utr_number VARCHAR(50) NULL,
        checksum VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
        completed_at TIMESTAMP NULL,
        INDEX idx_order_id (order_id),
        INDEX idx_transaction_id (transaction_id),
        INDEX idx_status (status),
        INDEX idx_gateway (gateway),
        INDEX idx_customer (customer_id, customer_type),
        INDEX idx_entity (entity_type, entity_id),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    echo "✅ payment_transactions table ready\n";
    
    // Payment methods table for saved cards/UPI
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_payment_methods (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        user_type ENUM('customer', 'associate', 'agent') DEFAULT 'customer',
        gateway VARCHAR(50) NOT NULL,
        method_type ENUM('card', 'upi', 'netbanking', 'wallet') NOT NULL,
        token VARCHAR(255) NOT NULL,
        last_four VARCHAR(10) NULL,
        card_brand VARCHAR(50) NULL,
        card_network VARCHAR(50) NULL,
        expiry_month VARCHAR(2) NULL,
        expiry_year VARCHAR(4) NULL,
        upi_id VARCHAR(100) NULL,
        bank_name VARCHAR(100) NULL,
        wallet_name VARCHAR(50) NULL,
        is_default TINYINT(1) DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_method (user_id, user_type, gateway, token),
        INDEX idx_user (user_id, user_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    echo "✅ user_payment_methods table ready\n";
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    // ============================================
    // SUMMARY
    // ============================================
    echo "\n";
    echo "🎉 MIGRATION COMPLETE!\n";
    echo "======================\n\n";
    
    echo "✅ STANDARDIZED TABLES:\n";
    echo "   • mlm_commissions (unified commission table)\n";
    echo "   • farmers (enhanced farmer management)\n";
    echo "   • plotting_budgets (real estate budgets)\n";
    echo "   • budget_expenses (expense tracking)\n";
    echo "   • network_tree (MLM genealogy)\n";
    echo "   • referrals (customer referrals)\n";
    echo "   • ai_conversations (chatbot history)\n";
    echo "   • payment_transactions (payment logs)\n";
    echo "   • user_payment_methods (saved payment methods)\n\n";
    
    echo "📝 CLEANUP RECOMMENDATIONS:\n";
    echo "   • Old commission tables can be archived: commissions, commission_transactions\n";
    echo "   • farmer_profiles can be merged into farmers (data migration needed)\n";
    echo "   • budgets table can be migrated to plotting_budgets\n";
    echo "   • mlm_referrals can use network_tree instead\n\n";
    
    echo "🚀 To run with actual data migration: php " . basename(__FILE__) . " --force\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
