<?php
/**
 * APS Dream Homes - Ultimate MLM Commission System
 * Designed to maximize team growth and business expansion
 */

/**
 * Commission Structure by Level
 * This system encourages team building and business growth
 */
function getCommissionStructure() {
    return [
        'Associate' => [
            'direct_commission' => 5,      // 5% on personal sales
            'team_commission' => 2,        // 2% on level 1 team
            'level_bonus' => 0,           // No level difference bonus
            'matching_bonus' => 0,         // No matching bonus
            'target' => 1000000,          // Target for next level
            'description' => 'Entry level - Focus on personal sales'
        ],
        'Sr. Associate' => [
            'direct_commission' => 7,      // 7% on personal sales
            'team_commission' => 3,        // 3% on level 1 team
            'level_bonus' => 2,           // 2% difference from juniors
            'matching_bonus' => 5,         // 5% matching bonus
            'target' => 3500000,          // Target for next level
            'description' => 'Mid level - Start building team'
        ],
        'BDM' => [
            'direct_commission' => 10,     // 10% on personal sales
            'team_commission' => 4,        // 4% on level 1 team
            'level_bonus' => 3,           // 3% difference from juniors
            'matching_bonus' => 8,         // 8% matching bonus
            'leadership_bonus' => 1,       // 1% on total team business
            'target' => 7000000,          // Target for next level
            'description' => 'Leadership level - Team building focus'
        ],
        'Sr. BDM' => [
            'direct_commission' => 12,     // 12% on personal sales
            'team_commission' => 5,        // 5% on level 1 team
            'level_bonus' => 4,           // 4% difference from juniors
            'matching_bonus' => 10,        // 10% matching bonus
            'leadership_bonus' => 2,       // 2% on total team business
            'performance_bonus' => 1,      // 1% performance bonus
            'target' => 15000000,         // Target for next level
            'description' => 'Senior leadership - Advanced team management'
        ],
        'Vice President' => [
            'direct_commission' => 15,     // 15% on personal sales
            'team_commission' => 6,        // 6% on level 1 team
            'level_bonus' => 5,           // 5% difference from juniors
            'matching_bonus' => 12,        // 12% matching bonus
            'leadership_bonus' => 3,       // 3% on total team business
            'performance_bonus' => 2,      // 2% performance bonus
            'target' => 30000000,         // Target for next level
            'description' => 'Executive level - Strategic leadership'
        ],
        'President' => [
            'direct_commission' => 18,     // 18% on personal sales
            'team_commission' => 7,        // 7% on level 1 team
            'level_bonus' => 6,           // 6% difference from juniors
            'matching_bonus' => 15,        // 15% matching bonus
            'leadership_bonus' => 4,       // 4% on total team business
            'performance_bonus' => 3,      // 3% performance bonus
            'target' => 50000000,         // Target for next level
            'description' => 'Top leadership - Maximum incentives'
        ],
        'Site Manager' => [
            'direct_commission' => 20,     // 20% on personal sales
            'team_commission' => 8,        // 8% on level 1 team
            'level_bonus' => 7,           // 7% difference from juniors
            'matching_bonus' => 18,        // 18% matching bonus
            'leadership_bonus' => 5,       // 5% on total team business
            'performance_bonus' => 5,      // 5% performance bonus
            'target' => 999999999,        // Maximum level
            'description' => 'Highest level - Ultimate rewards'
        ]
    ];
}

/**
 * Calculate commission for a specific booking
 */
function calculateCommission($associate_id, $booking_amount, $customer_id) {
    $db = \App\Core\App::database();

    // Get associate details
    $associate_query = "SELECT current_level, sponsor_id FROM mlm_agents WHERE id = :id";
    $associate = $db->fetch($associate_query, ['id' => $associate_id]);

    if (!$associate) {
        return ['error' => 'Associate not found'];
    }

    $level = $associate['current_level'];
    $sponsor_id = $associate['sponsor_id'];
    $structure = getCommissionStructure();
    $commissions = [];

    // 1. Direct Commission (on personal sales)
    $direct_commission = ($booking_amount * $structure[$level]['direct_commission']) / 100;
    $commissions['direct'] = [
        'type' => 'Direct Commission',
        'amount' => $direct_commission,
        'percentage' => $structure[$level]['direct_commission'],
        'description' => "Commission on your personal sale of ₹" . number_format($booking_amount)
    ];

    // 2. Level Difference Bonus (from downline)
    $level_bonus = calculateLevelDifferenceBonus($associate_id, $booking_amount);
    if ($level_bonus > 0) {
        $commissions['level_bonus'] = [
            'type' => 'Level Difference Bonus',
            'amount' => $level_bonus,
            'description' => 'Bonus from team members at lower levels'
        ];
    }

    // 3. Team Commission (from direct recruits)
    $team_commission = calculateTeamCommission($associate_id, $booking_amount);
    if ($team_commission > 0) {
        $commissions['team'] = [
            'type' => 'Team Commission',
            'amount' => $team_commission,
            'description' => 'Commission from direct team members'
        ];
    }

    // 4. Matching Bonus
    $matching_bonus = calculateMatchingBonus($associate_id, $booking_amount);
    if ($matching_bonus > 0) {
        $commissions['matching'] = [
            'type' => 'Matching Bonus',
            'amount' => $matching_bonus,
            'description' => 'Bonus for team performance matching'
        ];
    }

    // 5. Leadership Bonus (for higher levels)
    if (isset($structure[$level]['leadership_bonus'])) {
        $leadership_bonus = ($booking_amount * $structure[$level]['leadership_bonus']) / 100;
        $commissions['leadership'] = [
            'type' => 'Leadership Bonus',
            'amount' => $leadership_bonus,
            'percentage' => $structure[$level]['leadership_bonus'],
            'description' => 'Leadership bonus on total team business'
        ];
    }

    // 6. Performance Bonus (for achieving targets)
    $performance_bonus = calculatePerformanceBonus($associate_id, $booking_amount);
    if ($performance_bonus > 0) {
        $commissions['performance'] = [
            'type' => 'Performance Bonus',
            'amount' => $performance_bonus,
            'description' => 'Bonus for achieving monthly targets'
        ];
    }

    // Calculate total commission
    $total_commission = array_sum(array_column($commissions, 'amount'));

    // Save commission record
    saveCommissionRecord($associate_id, $customer_id, $booking_amount, $commissions, $total_commission);

    return [
        'associate_id' => $associate_id,
        'associate_level' => $level,
        'booking_amount' => $booking_amount,
        'commissions' => $commissions,
        'total_commission' => $total_commission,
        'payout_date' => date('Y-m-d', strtotime('+30 days')) // Payout after 30 days
    ];
}

/**
 * Calculate Level Difference Bonus
 * Higher level associates get percentage of lower level business
 */
function calculateLevelDifferenceBonus($associate_id, $booking_amount) {
    $db = \App\Core\App::database();

    // Get associate level
    $associate_query = "SELECT current_level FROM mlm_agents WHERE id = :id";
    $associate = $db->fetch($associate_query, ['id' => $associate_id]);
    
    if (!$associate) return 0;
    
    $associate_level = $associate['current_level'];

    // Get team members at lower levels
    $team_query = "
        WITH RECURSIVE team_tree AS (
            SELECT id, current_level, sponsor_id, 1 as level
            FROM mlm_agents
            WHERE sponsor_id = :sponsor_id AND status = 'active'

            UNION ALL

            SELECT m.id, m.current_level, m.sponsor_id, t.level + 1
            FROM mlm_agents m
            JOIN team_tree t ON m.sponsor_id = t.id
            WHERE m.status = 'active' AND t.level < 5
        )
        SELECT id, current_level FROM team_tree WHERE id != :associate_id
    ";

    $team_members = $db->fetchAll($team_query, [
        'sponsor_id' => $associate_id,
        'associate_id' => $associate_id
    ]);

    $level_structure = getCommissionStructure();
    $associate_commission_rate = $level_structure[$associate_level]['direct_commission'];
    $total_bonus = 0;

    foreach ($team_members as $member) {
        $member_level = $member['current_level'];
        $member_commission_rate = $level_structure[$member_level]['direct_commission'];

        // Calculate difference
        $difference = $associate_commission_rate - $member_commission_rate;

        if ($difference > 0) {
            // Higher level gets difference percentage of lower level business
            $bonus = ($booking_amount * $difference) / 100;
            $total_bonus += $bonus;

            // Also credit to the associate who made the sale
            creditUplineCommission($associate_id, $member['id'], $bonus, 'Level Difference Bonus');
        }
    }

    return $total_bonus;
}

/**
 * Calculate Team Commission
 */
function calculateTeamCommission($associate_id, $booking_amount) {
    $db = \App\Core\App::database();

    // Get direct team members
    $team_query = "SELECT id, current_level FROM mlm_agents WHERE sponsor_id = :sponsor_id AND status = 'active'";
    $team_members = $db->fetchAll($team_query, ['sponsor_id' => $associate_id]);

    $structure = getCommissionStructure();
    $associate_level = getAssociateLevel($associate_id);
    $team_commission_rate = $structure[$associate_level]['team_commission'];

    $total_commission = 0;

    foreach ($team_members as $member) {
        $member_commission_rate = $structure[$member['current_level']]['direct_commission'];
        $difference = $team_commission_rate - $member_commission_rate;

        if ($difference > 0) {
            $commission = ($booking_amount * $difference) / 100;
            $total_commission += $commission;

            // Credit to team member
            creditUplineCommission($associate_id, $member['id'], $commission, 'Team Commission');
        }
    }

    return $total_commission;
}

/**
 * Calculate Matching Bonus
 */
function calculateMatchingBonus($associate_id, $booking_amount) {
    $db = \App\Core\App::database();

    // Get associate's direct recruits
    $direct_recruits_query = "SELECT id FROM mlm_agents WHERE sponsor_id = :sponsor_id AND status = 'active'";
    $direct_recruits = $db->fetchAll($direct_recruits_query, ['sponsor_id' => $associate_id]);

    $associate_level = getAssociateLevel($associate_id);
    $structure = getCommissionStructure();
    $matching_rate = $structure[$associate_level]['matching_bonus'];

    $total_bonus = 0;

    foreach ($direct_recruits as $recruit) {
        // Check if recruit has also made sales in the same period
        $recruit_sales = getRecruitSales($recruit['id']);

        if ($recruit_sales > 0) {
            // Matching bonus: percentage of recruit's sales
            $bonus = ($recruit_sales * $matching_rate) / 100;
            $total_bonus += $bonus;

            // Credit to associate
            creditUplineCommission($associate_id, $recruit['id'], $bonus, 'Matching Bonus');
        }
    }

    return $total_bonus;
}

/**
 * Calculate Performance Bonus
 */
function calculatePerformanceBonus($associate_id, $booking_amount) {
    // Get monthly performance
    $monthly_sales = getMonthlySales($associate_id);
    $associate_level = getAssociateLevel($associate_id);
    $structure = getCommissionStructure();
    $target = $structure[$associate_level]['target'];

    // Performance bonus for exceeding targets
    if ($monthly_sales >= $target) {
        $performance_rate = $structure[$associate_level]['performance_bonus'] ?? 0;
        return ($booking_amount * $performance_rate) / 100;
    }

    return 0;
}

/**
 * Credit commission to upline
 */
function creditUplineCommission($associate_id, $downline_id, $amount, $type) {
    $db = \App\Core\App::database();

    try {
        $query = "INSERT INTO mlm_commissions
                  (associate_id, downline_id, commission_amount, commission_type, status, created_at)
                  VALUES (:associate_id, :downline_id, :amount, :type, 'pending', NOW())";

        $db->execute($query, [
            'associate_id' => $associate_id,
            'downline_id' => $downline_id,
            'amount' => $amount,
            'type' => $type
        ]);

    } catch (Exception $e) {
        error_log("Error crediting commission: " . $e->getMessage());
    }
}

/**
 * Save commission record
 */
function saveCommissionRecord($associate_id, $customer_id, $booking_amount, $commissions, $total) {
    $db = \App\Core\App::database();

    try {
        $query = "INSERT INTO mlm_commission_records
                  (associate_id, customer_id, booking_amount, commission_details, total_commission, status, created_at)
                  VALUES (:associate_id, :customer_id, :booking_amount, :details, :total, 'calculated', NOW())";

        $details_json = json_encode($commissions);

        $db->execute($query, [
            'associate_id' => $associate_id,
            'customer_id' => $customer_id,
            'booking_amount' => $booking_amount,
            'details' => $details_json,
            'total' => $total
        ]);

    } catch (Exception $e) {
        error_log("Error saving commission record: " . $e->getMessage());
    }
}

/**
 * Get associate level
 */
function getAssociateLevel($associate_id) {
    $db = \App\Core\App::database();

    $query = "SELECT current_level FROM mlm_agents WHERE id = :id";
    $result = $db->fetch($query, ['id' => $associate_id]);

    if ($result) {
        return $result['current_level'];
    }

    return 'Associate';
}

/**
 * Get monthly sales for associate
 */
function getMonthlySales($associate_id) {
    $db = \App\Core\App::database();

    $query = "SELECT COALESCE(SUM(amount), 0) as monthly_sales
              FROM bookings
              WHERE associate_id = :associate_id AND status IN ('confirmed', 'completed')
              AND booking_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";

    $result = $db->fetch($query, ['associate_id' => $associate_id]);
    return $result['monthly_sales'] ?? 0;
}

/**
 * Get recruit sales
 */
function getRecruitSales($recruit_id) {
    $db = \App\Core\App::database();

    $query = "SELECT COALESCE(SUM(amount), 0) as recruit_sales
              FROM bookings
              WHERE associate_id = :recruit_id AND status IN ('confirmed', 'completed')
              AND booking_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";

    $result = $db->fetch($query, ['recruit_id' => $recruit_id]);
    return $result['recruit_sales'] ?? 0;
}

/**
 * Get commission summary for associate
 */
function getCommissionSummary($associate_id, $period = 'monthly') {
    $db = \App\Core\App::database();

    $date_filter = "";
    if ($period == 'monthly') {
        $date_filter = "AND created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
    } elseif ($period == 'yearly') {
        $date_filter = "AND created_at >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
    }

    $query = "SELECT
                commission_type,
                SUM(commission_amount) as total_amount,
                COUNT(*) as count,
                status
              FROM mlm_commissions
              WHERE associate_id = :associate_id $date_filter
              GROUP BY commission_type, status";

    return $db->fetchAll($query, ['associate_id' => $associate_id]);
}

/**
 * Process commission payout
 */
function processCommissionPayout($associate_id) {
    $db = \App\Core\App::database();

    try {
        // Get pending commissions
        $query = "SELECT id, commission_amount FROM mlm_commissions
                  WHERE associate_id = :associate_id AND status = 'pending'";

        $pending_commissions = $db->fetchAll($query, ['associate_id' => $associate_id]);

        if (empty($pending_commissions)) {
            return ['success' => false, 'message' => 'No pending commissions'];
        }

        $total_payout = array_sum(array_column($pending_commissions, 'commission_amount'));

        // Update commission status to paid
        $update_query = "UPDATE mlm_commissions SET status = 'paid', paid_at = NOW()
                         WHERE associate_id = :associate_id AND status = 'pending'";

        $db->execute($update_query, ['associate_id' => $associate_id]);

        // Record payout
        $payout_query = "INSERT INTO mlm_payouts
                        (associate_id, amount, commission_ids, payout_date, status)
                        VALUES (:associate_id, :amount, :commission_ids, NOW(), 'completed')";

        $commission_ids = implode(',', array_column($pending_commissions, 'id'));
        
        $db->execute($payout_query, [
            'associate_id' => $associate_id,
            'amount' => $total_payout,
            'commission_ids' => $commission_ids
        ]);

        return [
            'success' => true,
            'message' => 'Commission payout processed successfully',
            'amount' => $total_payout,
            'count' => count($pending_commissions)
        ];

    } catch (Exception $e) {
        error_log("Error processing commission payout: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error processing payout'];
    }
}
?>
