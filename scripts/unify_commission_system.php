<?php
/**
 * Phase 2: Unify Commission System
 * 1. Extend mlm_commission_ledger with missing features from booking_commissions
 * 2. Migrate all data
 * 3. Update controllers to use mlm_commission_ledger
 * 4. Remove dual-write
 * 5. Drop booking_commissions
 */
require_once __DIR__ . '/../app/Core/autoload.php';

$db = \App\Core\Database\Database::getInstance();

echo "========================================\n";
echo " PHASE 2: UNIFY COMMISSION SYSTEM\n";
echo "========================================\n\n";

$results = ['done' => [], 'failed' => []];

function run($db, $sql, $desc, &$results) {
    try {
        $db->execute($sql);
        echo "[OK] $desc\n";
        $results['done'][] = $desc;
        return true;
    } catch (\Throwable $e) {
        echo "[FAIL] $desc: " . $e->getMessage() . "\n";
        $results['failed'][] = "$desc: " . $e->getMessage();
        return false;
    }
}

// ============================================================
// STEP 1: Extend mlm_commission_ledger schema
// ============================================================
echo "\n--- STEP 1: Extend mlm_commission_ledger schema ---\n";

// Add missing commission types
run($db, "ALTER TABLE mlm_commission_ledger MODIFY commission_type ENUM('referral','direct_sale','team_bonus','level_bonus','performance_bonus','special_reward','override','associate_referral','agent_referral','team_override','mlm_level_1','mlm_level_2','mlm_level_3')", "Add missing commission types to mlm_commission_ledger", $results);

// Add missing status values
run($db, "ALTER TABLE mlm_commission_ledger MODIFY status ENUM('pending','approved','paid','cancelled','clawed_back')", "Add missing status values to mlm_commission_ledger", $results);

// Add missing columns
run($db, "ALTER TABLE mlm_commission_ledger ADD COLUMN approved_by BIGINT(20) UNSIGNED NULL AFTER status", "Add approved_by column", $results);
run($db, "ALTER TABLE mlm_commission_ledger ADD COLUMN paid_at DATETIME NULL AFTER approved_by", "Add paid_at column", $results);
run($db, "ALTER TABLE mlm_commission_ledger ADD COLUMN mlm_ledger_id BIGINT(20) UNSIGNED NULL AFTER paid_at", "Add mlm_ledger_id column", $results);

// Add FK for approved_by
run($db, "ALTER TABLE mlm_commission_ledger ADD CONSTRAINT fk_mlm_approved_by FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL", "Add FK for approved_by", $results);

// ============================================================
// STEP 2: Migrate data from booking_commissions
// ============================================================
echo "\n--- STEP 2: Migrate data from booking_commissions ---\n";

$legacyEntries = $db->fetchAll("SELECT * FROM booking_commissions");
echo "Migrating " . count($legacyEntries) . " entries...\n";

$migrated = 0;
$skipped = 0;
$ins = $db->prepare("
    INSERT INTO mlm_commission_ledger
        (beneficiary_user_id, source_user_id, commission_type, level, amount, status, property_id, sale_amount, commission_percentage, notes, booking_id, receipt_id, approved_by, paid_at, mlm_ledger_id, hold_until, created_at, updated_at)
    VALUES
        (?, ?, ?, ?, ?, ?, NULL, ?, ?, ?, ?, 0, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY), ?, ?)
");

foreach ($legacyEntries as $e) {
    // Check if already migrated
    $existing = $db->fetch("SELECT id FROM mlm_commission_ledger WHERE booking_id = ? AND beneficiary_user_id = ? AND commission_type = ? AND amount = ? LIMIT 1", [
        $e['booking_id'], $e['beneficiary_user_id'], $e['commission_type'], $e['amount']
    ]);
    
    if ($existing) {
        $skipped++;
        continue;
    }
    
    $status = $e['status'];
    $paidAt = null;
    $approvedBy = null;
    
    if ($status === 'paid' || $status === 'approved') {
        $paidAt = $e['paid_at'] ?? date('Y-m-d H:i:s');
        $approvedBy = $e['approved_by'] ?? null;
    }
    
    $holdUntil = $e['hold_until'] ?? null;
    if ($status === 'pending') {
        $holdUntil = date('Y-m-d', strtotime('+30 days'));
    }
    
    try {
        $ins->execute([
            $e['beneficiary_user_id'],
            $e['source_user_id'],
            $e['commission_type'],
            $e['level'],
            $e['amount'],
            $status,
            $e['amount'] * 100, // sale_amount estimate
            $e['percent'],
            'Migrated from booking_commissions ID ' . $e['id'],
            $e['booking_id'],
            $approvedBy,
            $paidAt,
            $e['id'],
            $holdUntil,
            $e['created_at'],
            $e['updated_at'] ?? $e['created_at']
        ]);
        $migrated++;
    } catch (\Throwable $ex) {
        echo "  Failed to migrate entry {$e['id']}: " . $ex->getMessage() . "\n";
    }
}

echo "Migrated: $migrated, Skipped (already exist): $skipped\n";
$results['done'][] = "Migrated $migrated entries from booking_commissions";

// ============================================================
// STEP 3: Update BookingLifecycleService to remove dual-write
// ============================================================
echo "\n--- STEP 3: Update BookingLifecycleService ---\n";

// We'll update the file separately after this script
$results['done'][] = "Pending: Update BookingLifecycleService::calculateCommission() to remove dual-write";

// ============================================================
// STEP 4: Update BookingLifecycleController
// ============================================================
echo "\n--- STEP 4: Update BookingLifecycleController ---\n";

$results['done'][] = "Pending: Update BookingLifecycleController::commissions() and fetchCommissions()";

echo "\n========================================\n";
echo " SUMMARY\n";
echo "========================================\n";
echo "Done: " . count($results['done']) . "\n";
foreach ($results['done'] as $d) echo "  - $d\n";
echo "Failed: " . count($results['failed']) . "\n";
foreach ($results['failed'] as $f) echo "  - $f\n";

echo "\n=== NEXT STEPS (Manual) ===\n";
echo "1. Update BookingLifecycleService::calculateCommission() - remove booking_commissions insert\n";
echo "2. Update BookingLifecycleController::commissions() - query mlm_commission_ledger\n";
echo "3. Update BookingLifecycleController::fetchCommissions() - query mlm_commission_ledger\n";
echo "4. Update admin/sales/commissions.php view\n";
echo "5. Drop booking_commissions table\n";