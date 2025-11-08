<?php
// MLM Commission Payout Ledger Logic
require_once __DIR__ . '/../config/config.php';

/**
 * Record commission payouts for a transaction.
 * @param mysqli $con
 * @param int $transaction_id
 * @param int $sale_associate_id
 * @param float $amount
 */
function recordCommissionPayouts($con, $transaction_id, $sale_associate_id, $amount) {
    require_once __DIR__ . '/mlm_commission_logic.php';
    $distribution = calculateCommissionDistribution($con, $sale_associate_id, $amount);
    foreach ($distribution as $level => $info) {
        $stmt = $con->prepare("INSERT INTO mlm_commission_ledger (transaction_id, associate_id, level, commission_amount) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('iiid', $transaction_id, $info['upline_id'], $level, $info['commission']);
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Get total commission earned by an associate.
 * @param mysqli $con
 * @param int $associate_id
 * @return float
 */
function getTotalCommissionEarned($con, $associate_id) {
    $stmt = $con->prepare("SELECT SUM(commission_amount) as total FROM mlm_commission_ledger WHERE associate_id=?");
    $stmt->bind_param('i', $associate_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    return $row['total'] ?? 0;
}

/**
 * Get commission payout history for an associate.
 * @param mysqli $con
 * @param int $associate_id
 * @return mysqli_result
 */
function getCommissionLedger($con, $associate_id) {
    $stmt = $con->prepare("SELECT * FROM mlm_commission_ledger WHERE associate_id=? ORDER BY id DESC");
    $stmt->bind_param('i', $associate_id);
    $stmt->execute();
    return $stmt->get_result();
}
