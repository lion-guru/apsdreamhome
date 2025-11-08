<?php
/**
 * Hybrid MLM Commission Calculator & Plan Builder
 * Advanced Multi-Level Marketing System for APS Dream Home
 * Supports Binary, Unilevel, Matrix, and Hybrid Plans
 */

namespace App\Models;

/**
 * Advanced MLM Commission Calculator
 * Supports multiple MLM plan types and calculations
 */
class HybridMLMCalculator {
    private $db;
    private $planType;
    private $planConfig;

    public function __construct($planType = 'hybrid', $planConfig = []) {
        $this->db = \App\Models\Database::getInstance();
        $this->planType = $planType;
        $this->planConfig = $planConfig;
    }

    /**
     * Calculate commission for any MLM plan type
     */
    public function calculateCommission($associateId, $businessVolume, $level = 1) {
        switch ($this->planType) {
            case 'binary':
                return $this->calculateBinaryCommission($associateId, $businessVolume, $level);
            case 'unilevel':
                return $this->calculateUnilevelCommission($associateId, $businessVolume, $level);
            case 'matrix':
                return $this->calculateMatrixCommission($associateId, $businessVolume, $level);
            case 'hybrid':
            default:
                return $this->calculateHybridCommission($associateId, $businessVolume, $level);
        }
    }

    /**
     * Binary Plan Commission Calculation
     * Left and Right legs with balancing
     */
    private function calculateBinaryCommission($associateId, $businessVolume, $level) {
        $commission = 0;

        // Get associate's binary tree structure
        $binaryStructure = $this->getBinaryStructure($associateId);

        // Calculate commission based on weaker leg
        $leftVolume = $binaryStructure['left_volume'] ?? 0;
        $rightVolume = $binaryStructure['right_volume'] ?? 0;
        $weakerVolume = min($leftVolume, $rightVolume);

        // Commission percentage based on level and volume
        $commissionRate = $this->getBinaryCommissionRate($level, $businessVolume);

        $commission = $weakerVolume * ($commissionRate / 100);

        // Bonus for balanced tree
        if (abs($leftVolume - $rightVolume) / max($leftVolume, $rightVolume) < 0.2) {
            $commission *= 1.1; // 10% balance bonus
        }

        return [
            'commission' => $commission,
            'left_volume' => $leftVolume,
            'right_volume' => $rightVolume,
            'balanced_bonus' => abs($leftVolume - $rightVolume) / max($leftVolume, $rightVolume) < 0.2 ? $commission * 0.1 : 0
        ];
    }

    /**
     * Unilevel Plan Commission Calculation
     * Unlimited width, limited depth
     */
    private function calculateUnilevelCommission($associateId, $businessVolume, $level) {
        $commission = 0;

        // Get downline structure
        $downline = $this->getDownlineStructure($associateId, $level);

        // Commission decreases with level depth
        $commissionRate = $this->getUnilevelCommissionRate($level);

        foreach ($downline as $downlineAssociate) {
            $downlineVolume = $downlineAssociate['business_volume'] ?? 0;
            $commission += $downlineVolume * ($commissionRate / 100);
        }

        return [
            'commission' => $commission,
            'downline_count' => count($downline),
            'total_downline_volume' => array_sum(array_column($downline, 'business_volume'))
        ];
    }

    /**
     * Matrix Plan Commission Calculation
     * Fixed width, limited depth
     */
    private function calculateMatrixCommission($associateId, $businessVolume, $level) {
        $commission = 0;

        // Matrix configuration (e.g., 3x9 matrix)
        $matrixConfig = $this->planConfig['matrix_config'] ?? ['width' => 3, 'depth' => 9];

        // Get matrix structure
        $matrixStructure = $this->getMatrixStructure($associateId, $matrixConfig);

        // Commission calculation
        $commissionRate = $this->getMatrixCommissionRate($level);

        foreach ($matrixStructure as $matrixMember) {
            $memberVolume = $matrixMember['business_volume'] ?? 0;
            $commission += $memberVolume * ($commissionRate / 100);
        }

        return [
            'commission' => $commission,
            'matrix_width' => $matrixConfig['width'],
            'matrix_depth' => $matrixConfig['depth'],
            'filled_positions' => count($matrixStructure)
        ];
    }

    /**
     * Hybrid Plan Commission Calculation
     * Combines best features of Binary, Unilevel, and Matrix
     */
    private function calculateHybridCommission($associateId, $businessVolume, $level) {
        $results = [];

        // Binary component (40% weight)
        $binaryResult = $this->calculateBinaryCommission($associateId, $businessVolume * 0.4, $level);
        $results['binary'] = $binaryResult;

        // Unilevel component (35% weight)
        $unilevelResult = $this->calculateUnilevelCommission($associateId, $businessVolume * 0.35, $level);
        $results['unilevel'] = $unilevelResult;

        // Matrix component (25% weight)
        $matrixResult = $this->calculateMatrixCommission($associateId, $businessVolume * 0.25, $level);
        $results['matrix'] = $matrixResult;

        // Calculate total commission
        $totalCommission = $binaryResult['commission'] + $unilevelResult['commission'] + $matrixResult['commission'];

        // Apply hybrid bonuses
        $bonuses = $this->calculateHybridBonuses($associateId, $businessVolume, $results);

        return [
            'total_commission' => $totalCommission + $bonuses['total_bonus'],
            'components' => $results,
            'bonuses' => $bonuses,
            'breakdown' => [
                'binary_commission' => $binaryResult['commission'],
                'unilevel_commission' => $unilevelResult['commission'],
                'matrix_commission' => $matrixResult['commission'],
                'total_bonuses' => $bonuses['total_bonus']
            ]
        ];
    }

    /**
     * Calculate Hybrid Plan Bonuses
     */
    private function calculateHybridBonuses($associateId, $businessVolume, $components) {
        $bonuses = [
            'leadership_bonus' => 0,
            'team_building_bonus' => 0,
            'volume_bonus' => 0,
            'rank_bonus' => 0,
            'total_bonus' => 0
        ];

        // Leadership Bonus - Based on team size and performance
        $teamSize = ($components['binary']['left_volume'] + $components['binary']['right_volume']) / 1000000;
        if ($teamSize >= 10) {
            $bonuses['leadership_bonus'] = $businessVolume * 0.02; // 2% of personal volume
        }

        // Team Building Bonus - For balanced growth
        $leftTeam = $components['binary']['left_volume'] / 1000000;
        $rightTeam = $components['binary']['right_volume'] / 1000000;
        if ($leftTeam >= 5 && $rightTeam >= 5) {
            $bonuses['team_building_bonus'] = min($leftTeam, $rightTeam) * 1000; // ₹1000 per million in weaker leg
        }

        // Volume Bonus - For high performers
        if ($businessVolume >= 10000000) { // 1 crore+
            $bonuses['volume_bonus'] = $businessVolume * 0.005; // 0.5% bonus
        }

        // Rank Bonus - Based on current rank
        $currentRank = $this->getAssociateRank($associateId);
        $bonuses['rank_bonus'] = $this->getRankBonus($currentRank, $businessVolume);

        $bonuses['total_bonus'] = array_sum($bonuses) - $bonuses['total_bonus'];

        return $bonuses;
    }

    /**
     * Get Binary Commission Rate
     */
    private function getBinaryCommissionRate($level, $businessVolume) {
        $baseRate = 10; // 10% base rate

        // Increase rate based on volume
        if ($businessVolume >= 1000000) $baseRate += 2;
        if ($businessVolume >= 5000000) $baseRate += 3;
        if ($businessVolume >= 10000000) $baseRate += 5;

        // Decrease rate with level depth
        $levelMultiplier = max(0.5, 1 - ($level - 1) * 0.1);

        return $baseRate * $levelMultiplier;
    }

    /**
     * Get Unilevel Commission Rate
     */
    private function getUnilevelCommissionRate($level) {
        $rates = [
            1 => 10,  // Level 1: 10%
            2 => 8,   // Level 2: 8%
            3 => 6,   // Level 3: 6%
            4 => 4,   // Level 4: 4%
            5 => 3,   // Level 5: 3%
            6 => 2,   // Level 6: 2%
            7 => 1,   // Level 7: 1%
        ];

        return $rates[$level] ?? 0;
    }

    /**
     * Get Matrix Commission Rate
     */
    private function getMatrixCommissionRate($level) {
        return max(2, 12 - ($level - 1) * 0.5); // Decreases from 12% to 2%
    }

    /**
     * Get Associate Rank
     */
    private function getAssociateRank($associateId) {
        $sql = "
            SELECT current_level,
                   (SELECT COUNT(*) FROM mlm_agents ma2 WHERE ma2.sponsor_id = ma.id) as direct_referrals,
                   (SELECT SUM(total_business) FROM mlm_agents ma3 WHERE ma3.sponsor_id = ma.id) as team_business
            FROM mlm_agents ma WHERE id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$associateId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$result) return 'Associate';

        $level = $result['current_level'];
        $directReferrals = $result['direct_referrals'] ?? 0;
        $teamBusiness = $result['team_business'] ?? 0;

        // Rank calculation logic
        if ($level >= 10 && $directReferrals >= 50 && $teamBusiness >= 100000000) {
            return 'Crown Ambassador';
        } elseif ($level >= 9 && $directReferrals >= 30 && $teamBusiness >= 50000000) {
            return 'Royal Ambassador';
        } elseif ($level >= 8 && $directReferrals >= 20 && $teamBusiness >= 25000000) {
            return 'Diamond Ambassador';
        } elseif ($level >= 7 && $directReferrals >= 15 && $teamBusiness >= 10000000) {
            return 'Emerald Ambassador';
        } elseif ($level >= 6 && $directReferrals >= 10 && $teamBusiness >= 5000000) {
            return 'Sapphire Director';
        } elseif ($level >= 5 && $directReferrals >= 7 && $teamBusiness >= 2000000) {
            return 'Gold Director';
        } elseif ($level >= 4 && $directReferrals >= 5 && $teamBusiness >= 1000000) {
            return 'Silver Director';
        } elseif ($level >= 3 && $directReferrals >= 3 && $teamBusiness >= 500000) {
            return 'Bronze Director';
        } elseif ($level >= 2 && $directReferrals >= 2) {
            return 'Senior Associate';
        } elseif ($level >= 1) {
            return 'Associate';
        }

        return 'Associate';
    }

    /**
     * Get Rank Bonus
     */
    private function getRankBonus($rank, $businessVolume) {
        $rankBonuses = [
            'Crown Ambassador' => 0.05,      // 5%
            'Royal Ambassador' => 0.04,      // 4%
            'Diamond Ambassador' => 0.03,    // 3%
            'Emerald Ambassador' => 0.025,   // 2.5%
            'Sapphire Director' => 0.02,     // 2%
            'Gold Director' => 0.015,        // 1.5%
            'Silver Director' => 0.01,       // 1%
            'Bronze Director' => 0.005,      // 0.5%
            'Senior Associate' => 0.002,     // 0.2%
            'Associate' => 0,                // 0%
        ];

        return ($businessVolume * ($rankBonuses[$rank] ?? 0));
    }

    /**
     * Get Binary Structure
     */
    private function getBinaryStructure($associateId) {
        // Simplified binary structure calculation
        return [
            'left_volume' => 2500000,  // Example data
            'right_volume' => 1800000, // Example data
            'left_members' => 15,
            'right_members' => 12
        ];
    }

    /**
     * Get Downline Structure
     */
    private function getDownlineStructure($associateId, $maxLevel = 7) {
        // Get downline associates up to specified level
        $downline = [];

        $sql = "
            WITH RECURSIVE downline_tree AS (
                SELECT id, sponsor_id, 1 as level, total_business
                FROM mlm_agents
                WHERE sponsor_id = ?

                UNION ALL

                SELECT ma.id, ma.sponsor_id, dt.level + 1, ma.total_business
                FROM mlm_agents ma
                INNER JOIN downline_tree dt ON ma.sponsor_id = dt.id
                WHERE dt.level < ?
            )
            SELECT * FROM downline_tree ORDER BY level, id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$associateId, $maxLevel]);
        $downline = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $downline;
    }

    /**
     * Get Matrix Structure
     */
    private function getMatrixStructure($associateId, $matrixConfig) {
        // Get matrix structure based on configuration
        $matrixMembers = [];

        // Simplified matrix structure calculation
        for ($level = 1; $level <= $matrixConfig['depth']; $level++) {
            $positions = pow($matrixConfig['width'], $level - 1);
            for ($pos = 1; $pos <= $positions; $pos++) {
                $matrixMembers[] = [
                    'level' => $level,
                    'position' => $pos,
                    'business_volume' => rand(100000, 1000000) // Example data
                ];
            }
        }

        return $matrixMembers;
    }

    /**
     * Calculate Payout Schedule
     */
    public function calculatePayoutSchedule($associateId, $totalCommission) {
        $payoutSchedule = [];

        // Weekly payouts for smaller amounts
        if ($totalCommission < 10000) {
            $payoutSchedule[] = [
                'type' => 'weekly',
                'amount' => $totalCommission,
                'date' => date('Y-m-d', strtotime('next Friday'))
            ];
        }
        // Monthly payouts for larger amounts
        else {
            $monthlyAmount = $totalCommission / 12;
            for ($month = 1; $month <= 12; $month++) {
                $payoutSchedule[] = [
                    'type' => 'monthly',
                    'amount' => $monthlyAmount,
                    'date' => date('Y-m-d', strtotime("+$month months"))
                ];
            }
        }

        return $payoutSchedule;
    }

    /**
     * Generate Commission Report
     */
    public function generateCommissionReport($associateId, $period = 'monthly') {
        $startDate = date('Y-m-01');
        $endDate = date('Y-m-t');

        if ($period === 'weekly') {
            $startDate = date('Y-m-d', strtotime('monday this week'));
            $endDate = date('Y-m-d', strtotime('sunday this week'));
        }

        $sql = "
            SELECT
                SUM(commission_amount) as total_commission,
                COUNT(*) as transaction_count,
                AVG(commission_amount) as avg_commission,
                MAX(commission_date) as last_commission_date
            FROM commission_history
            WHERE associate_id = ? AND commission_date BETWEEN ? AND ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$associateId, $startDate, $endDate]);
        $report = $stmt->fetch(\PDO::FETCH_ASSOC);

        return [
            'period' => $period,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'data' => $report,
            'rank' => $this->getAssociateRank($associateId),
            'next_rank_requirements' => $this->getNextRankRequirements($associateId)
        ];
    }

    /**
     * Get Next Rank Requirements
     */
    public function getNextRankRequirements($associateId) {
        $currentRank = $this->getAssociateRank($associateId);
        $associateData = $this->getAssociateData($associateId);

        $rankRequirements = [
            'Associate' => [
                'next_rank' => 'Senior Associate',
                'requirements' => [
                    'direct_referrals' => 2,
                    'team_business' => 500000
                ]
            ],
            'Senior Associate' => [
                'next_rank' => 'Bronze Director',
                'requirements' => [
                    'direct_referrals' => 3,
                    'team_business' => 1000000
                ]
            ],
            'Bronze Director' => [
                'next_rank' => 'Silver Director',
                'requirements' => [
                    'direct_referrals' => 5,
                    'team_business' => 2000000
                ]
            ],
            'Silver Director' => [
                'next_rank' => 'Gold Director',
                'requirements' => [
                    'direct_referrals' => 7,
                    'team_business' => 5000000
                ]
            ],
            'Gold Director' => [
                'next_rank' => 'Sapphire Director',
                'requirements' => [
                    'direct_referrals' => 10,
                    'team_business' => 10000000
                ]
            ],
            'Sapphire Director' => [
                'next_rank' => 'Emerald Ambassador',
                'requirements' => [
                    'direct_referrals' => 15,
                    'team_business' => 25000000
                ]
            ],
            'Emerald Ambassador' => [
                'next_rank' => 'Diamond Ambassador',
                'requirements' => [
                    'direct_referrals' => 20,
                    'team_business' => 50000000
                ]
            ],
            'Diamond Ambassador' => [
                'next_rank' => 'Royal Ambassador',
                'requirements' => [
                    'direct_referrals' => 30,
                    'team_business' => 100000000
                ]
            ],
            'Royal Ambassador' => [
                'next_rank' => 'Crown Ambassador',
                'requirements' => [
                    'direct_referrals' => 50,
                    'team_business' => 200000000
                ]
            ],
            'Crown Ambassador' => [
                'next_rank' => null,
                'requirements' => [
                    'message' => 'Congratulations! You have achieved the highest rank!'
                ]
            ]
        ];

        $currentReq = $rankRequirements[$currentRank] ?? null;

        if (!$currentReq) return null;

        return [
            'current_rank' => $currentRank,
            'next_rank' => $currentReq['next_rank'],
            'requirements' => $currentReq['requirements'],
            'current_progress' => [
                'direct_referrals' => $associateData['direct_referrals'] ?? 0,
                'team_business' => $associateData['team_business'] ?? 0
            ]
        ];
    }

    /**
     * Get Associate Data
     */
    private function getAssociateData($associateId) {
        $sql = "
            SELECT
                current_level,
                (SELECT COUNT(*) FROM mlm_agents ma2 WHERE ma2.sponsor_id = ma.id) as direct_referrals,
                (SELECT SUM(total_business) FROM mlm_agents ma3 WHERE ma3.sponsor_id = ma.id) as team_business,
                total_business
            FROM mlm_agents ma WHERE id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$associateId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?? [];
    }
}

/**
 * MLM Plan Templates
 */
class MLMPlanTemplates {

    public static function getPlanTemplates() {
        return [
            [
                'id' => 'starter',
                'name' => 'Starter Plan',
                'type' => 'hybrid',
                'description' => 'Perfect for new associates',
                'joining_fee' => 1000,
                'monthly_target' => 50000,
                'commission_rates' => [
                    'binary' => ['rate' => 10, 'weight' => 40],
                    'unilevel' => ['rate' => 8, 'weight' => 35],
                    'matrix' => ['rate' => 6, 'weight' => 25]
                ],
                'bonuses' => [
                    'leadership_bonus' => 2,
                    'team_building_bonus' => 1000,
                    'volume_bonus' => 0.5
                ]
            ],
            [
                'id' => 'premium',
                'name' => 'Premium Plan',
                'type' => 'hybrid',
                'description' => 'For serious business builders',
                'joining_fee' => 5000,
                'monthly_target' => 200000,
                'commission_rates' => [
                    'binary' => ['rate' => 15, 'weight' => 45],
                    'unilevel' => ['rate' => 10, 'weight' => 35],
                    'matrix' => ['rate' => 8, 'weight' => 20]
                ],
                'bonuses' => [
                    'leadership_bonus' => 3,
                    'team_building_bonus' => 2000,
                    'volume_bonus' => 1
                ]
            ],
            [
                'id' => 'enterprise',
                'name' => 'Enterprise Plan',
                'type' => 'hybrid',
                'description' => 'Maximum earning potential',
                'joining_fee' => 10000,
                'monthly_target' => 500000,
                'commission_rates' => [
                    'binary' => ['rate' => 20, 'weight' => 50],
                    'unilevel' => ['rate' => 12, 'weight' => 30],
                    'matrix' => ['rate' => 10, 'weight' => 20]
                ],
                'bonuses' => [
                    'leadership_bonus' => 5,
                    'team_building_bonus' => 5000,
                    'volume_bonus' => 2
                ]
            ]
        ];
    }
}

/**
 * Advanced MLM Analytics
 */
class MLMAdvancedAnalytics {

    private $db;

    public function __construct() {
        $this->db = \App\Models\Database::getInstance();
    }

    /**
     * Generate Comprehensive MLM Analytics
     */
    public function generateMLMAnalytics($associateId = null, $period = 'monthly') {
        $analytics = [];

        // Network Growth Analytics
        $analytics['network_growth'] = $this->calculateNetworkGrowth($associateId, $period);

        // Commission Analytics
        $analytics['commission_analytics'] = $this->calculateCommissionAnalytics($associateId, $period);

        // Rank Progression Analytics
        $analytics['rank_progression'] = $this->calculateRankProgression($associateId);

        // Team Performance Analytics
        $analytics['team_performance'] = $this->calculateTeamPerformance($associateId);

        // Business Volume Analytics
        $analytics['business_volume'] = $this->calculateBusinessVolumeAnalytics($associateId, $period);

        return $analytics;
    }

    private function calculateNetworkGrowth($associateId, $period) {
        // Calculate network growth metrics
        return [
            'total_downline' => 0,
            'new_joins' => 0,
            'active_members' => 0,
            'growth_rate' => 0
        ];
    }

    private function calculateCommissionAnalytics($associateId, $period) {
        // Calculate commission analytics
        return [
            'total_earned' => 0,
            'monthly_average' => 0,
            'top_earning_month' => 0,
            'earning_trend' => 'up'
        ];
    }

    private function calculateRankProgression($associateId) {
        // Calculate rank progression
        return [
            'current_rank' => 'Associate',
            'next_rank' => 'Senior Associate',
            'progress_percentage' => 0,
            'requirements_met' => []
        ];
    }

    private function calculateTeamPerformance($associateId) {
        // Calculate team performance metrics
        return [
            'team_size' => 0,
            'active_team_members' => 0,
            'team_business_volume' => 0,
            'team_earnings' => 0
        ];
    }

    private function calculateBusinessVolumeAnalytics($associateId, $period) {
        // Calculate business volume analytics
        return [
            'personal_volume' => 0,
            'team_volume' => 0,
            'total_volume' => 0,
            'volume_trend' => 'stable'
        ];
    }
}

/**
 * MLM Plan Comparison Tool
 */
class MLMPlanComparison {

    public static function comparePlans($planIds) {
        $plans = [];

        foreach ($planIds as $planId) {
            $plans[] = self::getPlanDetails($planId);
        }

        return [
            'plans' => $plans,
            'comparison' => self::generateComparison($plans),
            'recommendation' => self::generateRecommendation($plans)
        ];
    }

    private static function getPlanDetails($planId) {
        // Get plan details from database
        return [
            'id' => $planId,
            'name' => 'Sample Plan',
            'type' => 'hybrid',
            'joining_fee' => 5000,
            'potential_earnings' => 50000,
            'risk_level' => 'medium',
            'time_to_profit' => '3-6 months'
        ];
    }

    private static function generateComparison($plans) {
        return [
            'best_earnings' => $plans[0]['id'],
            'lowest_risk' => $plans[0]['id'],
            'fastest_roi' => $plans[0]['id'],
            'most_suitable' => $plans[0]['id']
        ];
    }

    private static function generateRecommendation($plans) {
        return [
            'recommended_plan' => $plans[0]['id'],
            'reason' => 'Best balance of risk and reward',
            'expected_earnings' => '₹50,000/month',
            'timeframe' => '6-12 months'
        ];
    }
}

/**
 * MLM Training System
 */
class MLMTrainingSystem {

    private $db;

    public function __construct() {
        $this->db = \App\Models\Database::getInstance();
    }

    public static function getTrainingModules() {
        return [
            [
                'id' => 'basics',
                'title' => 'MLM Fundamentals',
                'description' => 'Learn the basics of multi-level marketing',
                'duration' => '2 hours',
                'difficulty' => 'Beginner',
                'modules' => [
                    'What is MLM?',
                    'Understanding Compensation Plans',
                    'Building Your Network',
                    'Legal and Ethical Considerations'
                ]
            ],
            [
                'id' => 'advanced',
                'title' => 'Advanced MLM Strategies',
                'description' => 'Master advanced MLM techniques',
                'duration' => '4 hours',
                'difficulty' => 'Advanced',
                'modules' => [
                    'Leadership Development',
                    'Team Building Strategies',
                    'Commission Maximization',
                    'Business Scaling'
                ]
            ],
            [
                'id' => 'digital',
                'title' => 'Digital MLM Marketing',
                'description' => 'Leverage digital tools for MLM success',
                'duration' => '3 hours',
                'difficulty' => 'Intermediate',
                'modules' => [
                    'Social Media Marketing',
                    'Content Creation',
                    'Online Lead Generation',
                    'Digital Tools and Automation'
                ]
            ]
        ];
    }

    public function trackProgress($associateId, $moduleId, $progress) {
        // Track training progress
        $sql = "INSERT INTO mlm_training_progress (associate_id, module_id, progress_percentage, completed_at)
                VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE progress_percentage = ?, completed_at = ?";

        $stmt = $this->db->prepare($sql);
        $completedAt = $progress >= 100 ? date('Y-m-d H:i:s') : null;
        $stmt->execute([$associateId, $moduleId, $progress, $completedAt, $progress, $completedAt]);

        return ['success' => true, 'progress' => $progress];
    }
}

/**
 * Hybrid MLM Dashboard Calculator
 */
class HybridMLMDashboard {

    public static function getDashboardData($associateId) {
        $calculator = new HybridMLMCalculator('hybrid');

        // Get current month business volume
        $businessVolume = self::getCurrentMonthVolume($associateId);

        // Calculate current commission
        $commissionData = $calculator->calculateCommission($associateId, $businessVolume);

        // Get rank information
        $rankInfo = $calculator->generateCommissionReport($associateId);

        // Get payout schedule
        $payoutSchedule = $calculator->calculatePayoutSchedule($associateId, $commissionData['total_commission']);

        return [
            'current_commission' => $commissionData,
            'rank_information' => $rankInfo,
            'payout_schedule' => $payoutSchedule,
            'business_volume' => $businessVolume,
            'next_rank_requirements' => $calculator->getNextRankRequirements($associateId)
        ];
    }

    private static function getCurrentMonthVolume($associateId) {
        // Get current month's business volume
        return 2500000; // Example: 25 lakhs
    }
}
