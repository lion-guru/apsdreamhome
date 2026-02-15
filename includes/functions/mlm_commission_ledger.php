<?php
// MLM Commission Payout Ledger Logic
require_once __DIR__ . '/../config/config.php';

/**
 * Record commission payouts for a transaction.
 * @param PDO $db
 * @param int $transaction_id
 * @param int $sale_associate_id
 * @param float $amount
 */
function recordCommissionPayouts($db, $transaction_id, $sale_associate_id, $amount) {
    require_once __DIR__ . '/mlm_commission_logic.php';
    $distribution = calculateCommissionDistribution($db, $sale_associate_id, $amount);
    foreach ($distribution as $level => $info) {
        $stmt = $db->prepare("INSERT INTO mlm_commission_ledger (transaction_id, associate_id, level, commission_amount) VALUES (:transaction_id, :associate_id, :level, :commission_amount)");
        $stmt->execute([
            'transaction_id' => $transaction_id,
            'associate_id' => $info['upline_id'],
            'level' => $level,
            'commission_amount' => $info['commission']
        ]);
    }
}

/**
 * Get total commission earned by an associate.
 * @param PDO $db
 * @param int $associate_id
 * @return float
 */
function getTotalCommissionEarned($db, $associate_id) {
    $stmt = $db->prepare("SELECT SUM(commission_amount) as total FROM mlm_commission_ledger WHERE associate_id = :associate_id");
    $stmt->execute(['associate_id' => $associate_id]);
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    return $row['total'] ?? 0;
}

/**
 * Get commission payout history for an associate.
 * @param PDO $db
 * @param int $associate_id
 * @return array
 */
function getCommissionLedger($db, $associate_id) {
    $stmt = $db->prepare("SELECT * FROM mlm_commission_ledger WHERE associate_id = :associate_id ORDER BY id DESC");
    $stmt->execute(['associate_id' => $associate_id]);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}
