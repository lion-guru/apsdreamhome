<?php
require_once __DIR__ . '/../app/Core/autoload.php';

$db = \App\Core\Database\Database::getInstance();

echo "Migrating booking_commissions to mlm_commission_ledger...\n";

$legacyEntries = $db->fetchAll("SELECT * FROM booking_commissions");
echo "Total entries: " . count($legacyEntries) . "\n";

$ins = $db->prepare("
    INSERT INTO mlm_commission_ledger
        (beneficiary_user_id, source_user_id, commission_type, level, amount, status, property_id, sale_amount, commission_percentage, notes, booking_id, receipt_id, approved_by, paid_at, mlm_ledger_id, hold_until, created_at, updated_at)
    VALUES
        (?, ?, ?, ?, ?, ?, NULL, ?, ?, ?, ?, 0, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY), ?, ?)
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
    
    $holdUntil = null;
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
            $e['amount'] * 100,
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
        echo "Failed entry {$e['id']}: " . $ex->getMessage() . "\n";
    }
}

echo "Migrated: $migrated, Skipped: $skipped\n";?>