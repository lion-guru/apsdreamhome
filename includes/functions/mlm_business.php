<?php
// MLM Business Tracking & Rewards Logic
require_once __DIR__ . '/../config/config.php';

if (!function_exists('fetchAllDownlineIds')) {
    /**
     * Recursively fetch all downline associate IDs.
     * @param PDO $db
     * @param int $root_id
     * @param array $ids
     * @return void
     */
    function fetchAllDownlineIds($db, $root_id, &$ids) {
        $stmt = $db->prepare("SELECT id FROM associates WHERE parent_id = :parent_id");
        $stmt->execute(['parent_id' => $root_id]);
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $ids[] = $row['id'];
            fetchAllDownlineIds($db, $row['id'], $ids);
        }
    }
}

/**
 * Calculate total business for an associate, including their team (downline).
 * @param PDO $db
 * @param int $associate_id
 * @return float
 */
function getTotalTeamBusiness($db, $associate_id) {
    $ids = [$associate_id];
    fetchAllDownlineIds($db, $associate_id, $ids);
    
    // Using named parameters for the IN clause is tricky, 
    // but we can generate them dynamically.
    $params = [];
    foreach ($ids as $index => $id) {
        $params["id_$index"] = $id;
    }
    
    $placeholders = implode(',', array_map(fn($k) => ":$k", array_keys($params)));
    
    $sql = "SELECT SUM(amount) as total_business FROM commission_transactions WHERE associate_id IN ($placeholders)";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    
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
 * @param PDO $db
 * @param int $associate_id
 * @return array
 */
function getAssociateBusinessSummary($db, $associate_id) {
    // Personal business
    $stmt = $db->prepare("SELECT SUM(amount) as personal_business FROM commission_transactions WHERE associate_id = :associate_id");
    $stmt->execute(['associate_id' => $associate_id]);
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    $personal = $row['personal_business'] ?? 0;
    
    // Team business
    $team = getTotalTeamBusiness($db, $associate_id) - $personal;
    $total = $personal + $team;
    $reward = getRewardTier($total);
    return [
        'personal_business' => $personal,
        'team_business' => $team,
        'total_business' => $total,
        'reward_tier' => $reward
    ];
}
