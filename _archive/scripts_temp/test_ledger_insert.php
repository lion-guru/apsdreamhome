<?php
require_once __DIR__ . '/../app/Core/autoload.php';

$db = \App\Core\Database\Database::getInstance();

// Test insert manually
$ins = $db->prepare('
    INSERT INTO mlm_commission_ledger
        (beneficiary_user_id, source_user_id, commission_type, level, amount, status, property_id, sale_amount, commission_percentage, notes, booking_id, receipt_id, hold_until, created_at)
    VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
');
try {
    $ins->execute([2, 2, 'direct_sale', 0, 48000, 'pending', 99941, 2400000, 2.0, 'test', 99941, 0, '2026-12-31']);
    echo 'Insert OK, ID: ' . $db->lastInsertId();
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}