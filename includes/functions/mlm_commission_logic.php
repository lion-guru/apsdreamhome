<?php
// MLM Commission Calculation Logic using Super Admin Settings
require_once __DIR__ . '/../config/config.php';

/**
 * Get company commission share percent
 * @param mysqli $con
 * @return float
 */
function getCompanySharePercent($con) {
    $res = $con->query("SELECT share_percent FROM mlm_company_share LIMIT 1");
    if ($row = $res->fetch_assoc()) {
        return floatval($row['share_percent']);
    }
    return 25.0; // Default
}

/**
 * Get commission percent for a given level (1-based)
 * @param mysqli $con
 * @param int $level
 * @return float
 */
function getLevelCommissionPercent($con, $level) {
    $stmt = $con->prepare("SELECT percent FROM mlm_commission_settings WHERE level=?");
    $stmt->bind_param('i', $level);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        return floatval($row['percent']);
    }
    return 0.0;
}

/**
 * Calculate commission distribution for a transaction.
 * @param mysqli $con
 * @param int $associate_id (who made the sale)
 * @param float $amount (total transaction amount)
 * @return array (level => commission_amount)
 */
function calculateCommissionDistribution($con, $associate_id, $amount) {
    $company_share = getCompanySharePercent($con);
    $total_commission = ($company_share / 100.0) * $amount;
    $distribution = [];
    $current_id = $associate_id;
    for ($level = 1; $level <= 10; $level++) {
        $percent = getLevelCommissionPercent($con, $level);
        if ($percent <= 0) break;
        // Find upline at this level
        $stmt = $con->prepare("SELECT parent_id FROM associates WHERE id=?");
        $stmt->bind_param('i', $current_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        if (!$row || !$row['parent_id']) break;
        $upline_id = $row['parent_id'];
        $commission = ($percent / 100.0) * $total_commission;
        $distribution[$level] = [
            'upline_id' => $upline_id,
            'commission' => $commission
        ];
        $current_id = $upline_id;
    }
    return $distribution;
}

/**
 * Example usage:
 * $dist = calculateCommissionDistribution($con, $associate_id, 10000);
 * foreach ($dist as $level => $info) {
 *     echo "Level $level: Associate {$info['upline_id']} gets ₹{$info['commission']}<br>";
 * }
 */
