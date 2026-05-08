<?php
/**
 * Database Table Consolidation & Cleanup Script
 * ================================================
 * 
 * ISSUES ADDRESSED:
 * 1. farmers vs farmer_profiles - Duplicate farmer tables
 * 2. 4+ commission tables - Need consolidation to mlm_commissions standard
 * 3. Budget tables (3 types) - budgets, budget_items, budget_planning, plotting_budgets
 * 4. Referrals - referrals, mlm_referrals, mlm_profiles duplicates
 * 5. Missing ai_conversations table
 * 6. Missing payment_transactions table
 * 
 * SOLUTION: Create standardized tables and migration path
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Database\Database;

echo "🔍 DATABASE TABLE CONSOLIDATION ANALYSIS\n";
echo "========================================\n\n";

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Get all existing tables
    $stmt = $pdo->query("SHOW TABLES");
    $allTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "📊 Total Tables Found: " . count($allTables) . "\n\n";
    
    // ============================================
    // CONFLICT GROUP 1: FARMER TABLES
    // ============================================
    echo "🚜 CONFLICT GROUP 1: Farmer Tables\n";
    echo "-----------------------------------\n";
    $farmerTables = array_intersect($allTables, ['farmers', 'farmer_profiles', 'farmers_legacy']);
    echo "Found: " . implode(', ', $farmerTables) . "\n\n";
    
    // Check data in each
    foreach ($farmerTables as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo "  • $table: $count records\n";
    }
    
    // DECISION: Keep 'farmers' as primary, migrate data from 'farmer_profiles' if needed
    // 'farmers' is most complete with: id, name, email, phone, address, land_area, status
    
    // ============================================
    // CONFLICT GROUP 2: COMMISSION TABLES
    // ============================================
    echo "\n💰 CONFLICT GROUP 2: Commission Tables\n";
    echo "---------------------------------------\n";
    $commissionTables = array_intersect($allTables, [
        'commissions', 'mlm_commissions', 'commission_transactions', 
        'associate_commissions', 'commission_payouts', 'referral_rewards'
    ]);
    echo "Found: " . implode(', ', $commissionTables) . "\n\n";
    
    foreach ($commissionTables as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo "  • $table: $count records\n";
    }
    
    // DECISION: 'mlm_commissions' is the STANDARD table (most complete)
    // Structure: id, associate_id, source_associate_id, level, amount, status, created_at
    
    // ============================================
    // CONFLICT GROUP 3: BUDGET TABLES
    // ============================================
    echo "\n📊 CONFLICT GROUP 3: Budget Tables\n";
    echo "-----------------------------------\n";
    $budgetTables = array_intersect($allTables, [
        'budgets', 'budget_items', 'budget_planning', 'plotting_budgets', 'budget_expenses'
    ]);
    echo "Found: " . implode(', ', $budgetTables) . "\n\n";
    
    foreach ($budgetTables as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo "  • $table: $count records\n";
    }
    
    // DECISION: Keep 'plotting_budgets' as primary (real estate specific)
    // Migrate: budgets → plotting_budgets
    
    // ============================================
    // CONFLICT GROUP 4: REFERRAL TABLES
    // ============================================
    echo "\n🔗 CONFLICT GROUP 4: Referral Tables\n";
    echo "--------------------------------------\n";
    $referralTables = array_intersect($allTables, [
        'referrals', 'mlm_referrals', 'mlm_profiles', 'network_tree', 'referral_rewards'
    ]);
    echo "Found: " . implode(', ', $referralTables) . "\n\n";
    
    foreach ($referralTables as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo "  • $table: $count records\n";
    }
    
    // DECISION: 'network_tree' is the STANDARD for MLM structure
    // Keep 'referrals' for simple customer referrals
    
    echo "\n";
    
} catch (Exception $e) {
    echo "❌ Error during analysis: " . $e->getMessage() . "\n";
    exit(1);
}

echo "✅ Analysis Complete!\n\n";
