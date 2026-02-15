<?php
// MLM Commission Calculation Logic using Super Admin Settings
require_once __DIR__ . '/../config/config.php';

/**
 * Get company commission share percent
 * @param PDO|null $db
 * @return float
 */
function getCompanySharePercent($db = null)
{
    if ($db === null) {
        $db = \App\Core\App::database();
    }
    $row = $db->fetch("SELECT share_percent FROM mlm_company_share LIMIT 1");
    if ($row) {
        return floatval($row['share_percent']);
    }
    return 25.0; // Default
}

/**
 * Get commission percent for a given level (1-based)
 * @param PDO|null $db
 * @param int $level
 * @return float
 */
function getLevelCommissionPercent($db, $level = null)
{
    // Handle case where $db is passed as level (backward compatibility)
    if (!is_object($db) && $level === null) {
        $level = $db;
        $db = \App\Core\App::database();
    } elseif ($db === null) {
        $db = \App\Core\App::database();
    }

    $row = $db->fetch("SELECT percent FROM mlm_commission_settings WHERE level = :level", ['level' => $level]);
    if ($row) {
        return floatval($row['percent']);
    }
    return 0.0;
}

/**
 * Calculate commission distribution for a transaction.
 * @param PDO|null $db
 * @param int $associate_id (who made the sale)
 * @param float $amount (total transaction amount)
 * @return array (level => commission_amount)
 */
function calculateCommissionDistribution($db, $associate_id = null, $amount = null)
{
    // Handle case where $db is passed as associate_id (backward compatibility)
    if (!is_object($db) && $associate_id !== null && $amount === null) {
        $amount = $associate_id;
        $associate_id = $db;
        $db = \App\Core\App::database();
    } elseif ($db === null) {
        $db = \App\Core\App::database();
    }

    $company_share = getCompanySharePercent($db);
    $total_commission = ($company_share / 100.0) * $amount;
    $distribution = [];
    $current_id = $associate_id;
    for ($level = 1; $level <= 10; $level++) {
        $percent = getLevelCommissionPercent($db, $level);
        if ($percent <= 0) break;
        // Find upline at this level
        $row = $db->fetch("SELECT parent_id FROM associates WHERE id = :id", ['id' => $current_id]);
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
