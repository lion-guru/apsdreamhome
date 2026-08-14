<?php
require_once __DIR__ . '/../app/Core/autoload.php';

$db = \App\Core\Database\Database::getInstance();

echo "Migrating booking_commissions to mlm_commission_ledger...\n";

$legacyEntries = $db->fetchAll("SELECT * FROM booking_commissions");
echo "Total entries: " . count($legacyEntries) . "\n";

// 19 columns excluding auto-increment id
$ins = $db->prepare("
    INSERT INTO mlm_commission_ledger
        (beneficiary_user_id, source_user_id, commission_type, level, amount, property_id, sale_amount, commission_percentage, status, approved_by, paid_at, mlm_ledger_id, payout_batch_id, notes, created_at, updated_at, booking_id, receipt_id, hold_until)
    VALUES
        (?, ?, ?, ?, ?, NULL, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))
");

$migrated = 0;
$skipped = 0;

foreach ($legacyEntries as $e) {
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
    
    try {
        $ins->execute([
            $e['beneficiary_user_id'],
            $e['source_user_id'],
            $e['commission_type'],
            $e['level'],
            $e['amount'],
            $e['amount'] * 100,  // sale_amount
            $e['percent'],
            $status,
            $approvedBy,
            $paidAt,
            $e['id'],
            'Migrated from booking_commissions ID ' . $e['id'],
            $e['created_at'],
            $e['updated_at'] ?? $e['created_at'],
            $e['booking_id'],
            0,  // receipt_id
        ]);
        $migrated++;
    } catch (\Throwable $ex) {
        echo "Failed entry {$e['id']}: " . $ex->getMessage() . "\n";
    }
}

echo "Migrated: $migrated, Skipped: $skipped\n";?>