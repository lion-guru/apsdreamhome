<?php
// MLM Business Tracking & Rewards Logic
require_once __DIR__ . '/../config/config.php';

if (!function_exists('fetchAllDownlineIds')) {
    /**
     * Recursively fetch all downline associate IDs.
     * @param mysqli $con
     * @param int $root_id
     * @param array $ids
     * @return void
     */
    function fetchAllDownlineIds($con, $root_id, &$ids) {
        $stmt = $con->prepare("SELECT id FROM associates WHERE parent_id=?");
        $stmt->bind_param('i', $root_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $ids[] = $row['id'];
            fetchAllDownlineIds($con, $row['id'], $ids);
        }
        $stmt->close();
    }
}

/**
 * Calculate total business for an associate, including their team (downline).
 * @param mysqli $con
 * @param int $associate_id
 * @return float
 */
function getTotalTeamBusiness($con, $associate_id) {
    $ids = [$associate_id];
    fetchAllDownlineIds($con, $associate_id, $ids);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $stmt = $con->prepare("SELECT SUM(amount) as total_business FROM commission_transactions WHERE associate_id IN ($placeholders)");
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['total_business'] ?? 0;
}

/**
 * Calculate reward tier based on total business amount.
 * @param float $total_business
 * @return string
 */
function getRewardTier($total_business) {
    if ($total_business >= 1000000) {
        return 'Diamond';
    } elseif ($total_business >= 500000) {
        return 'Platinum';
    } elseif ($total_business >= 200000) {
        return 'Gold';
    } elseif ($total_business >= 100000) {
        return 'Silver';
    } elseif ($total_business >= 50000) {
        return 'Bronze';
    } else {
        return 'Starter';
    }
}

/**
 * Get business and reward info for an associate (personal + team).
 * @param mysqli $con
 * @param int $associate_id
 * @return array
 */
function getAssociateBusinessSummary($con, $associate_id) {
    // Personal business
    $stmt = $con->prepare("SELECT SUM(amount) as personal_business FROM commission_transactions WHERE associate_id=?");
    $stmt->bind_param('i', $associate_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $personal = $row['personal_business'] ?? 0;
    $stmt->close();
    // Team business
    $team = getTotalTeamBusiness($con, $associate_id) - $personal;
    $total = $personal + $team;
    $reward = getRewardTier($total);
    return [
        'personal_business' => $personal,
        'team_business' => $team,
        'total_business' => $total,
        'reward_tier' => $reward
    ];
}
