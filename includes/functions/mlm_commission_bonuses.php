<?php
// MLM Bonuses & Ranks Logic
require_once __DIR__ . '/../config/config.php';

/**
 * Calculate bonus based on business volume and rank
 * @param float $total_business
 * @param string $rank
 * @return float
 */
function calculateBonus($total_business, $rank) {
    // Example logic: higher ranks get higher bonus rates
    $bonus_rate = 0;
    switch ($rank) {
        case 'Diamond': $bonus_rate = 0.10; break;
        case 'Platinum': $bonus_rate = 0.07; break;
        case 'Gold': $bonus_rate = 0.05; break;
        case 'Silver': $bonus_rate = 0.03; break;
        default: $bonus_rate = 0.01; break;
    }
    return $total_business * $bonus_rate;
}

/**
 * Determine associate rank based on total business
 * @param float $total_business
 * @return string
 */
function getAssociateRank($total_business) {
    if ($total_business >= 1000000) return 'Diamond';
    if ($total_business >= 500000) return 'Platinum';
    if ($total_business >= 200000) return 'Gold';
    if ($total_business >= 100000) return 'Silver';
    return 'Starter';
}
