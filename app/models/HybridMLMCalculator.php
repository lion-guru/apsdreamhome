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
class HybridMLMCalculator
{
    private $db;
    private $planType;
    /**
     * Configuration for the current plan
     * @var array
     */
    private $planConfig;

    public function __construct($planType = 'hybrid', $planConfig = [])
    {
        $this->db = \App\Core\Database::getInstance();
        $this->planType = $planType;
        $this->planConfig = $planConfig;
    }

    /**
     * Calculate commission for any MLM plan type
     */
    public function calculateCommission($associateId, $businessVolume, $level = 1)
    {
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
    private function calculateBinaryCommission($associateId, $businessVolume, $level)
    {
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
    private function calculateUnilevelCommission($associateId, $businessVolume, $level)
    {
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
    private function calculateMatrixCommission($associateId, $businessVolume, $level)
    {
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
    private function calculateHybridCommission($associateId, $businessVolume, $level)
    {
        $results = [];

        // Get weights from config or use defaults
        $weights = $this->planConfig['commission_rates'] ?? [
            'binary' => ['weight' => 40],
            'unilevel' => ['weight' => 35],
            'matrix' => ['weight' => 25]
        ];

        // Binary component
        $binaryWeight = ($weights['binary']['weight'] ?? 40) / 100;
        $binaryResult = $this->calculateBinaryCommission($associateId, $businessVolume * $binaryWeight, $level);
        $results['binary'] = $binaryResult;

        // Unilevel component
        $unilevelWeight = ($weights['unilevel']['weight'] ?? 35) / 100;
        $unilevelResult = $this->calculateUnilevelCommission($associateId, $businessVolume * $unilevelWeight, $level);
        $results['unilevel'] = $unilevelResult;

        // Matrix component
        $matrixWeight = ($weights['matrix']['weight'] ?? 25) / 100;
        $matrixResult = $this->calculateMatrixCommission($associateId, $businessVolume * $matrixWeight, $level);
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
    private function calculateHybridBonuses($associateId, $businessVolume, $components)
    {
        $bonuses = [
            'leadership_bonus' => 0,
            'team_building_bonus' => 0,
            'volume_bonus' => 0,
            'rank_bonus' => 0,
            'total_bonus' => 0
        ];

        $bonusConfig = $this->planConfig['bonuses'] ?? [
            'leadership_bonus' => 2,
            'team_building_bonus' => 1000,
            'volume_bonus' => 0.5
        ];

        // Leadership Bonus - Based on team size and performance
        $teamSize = ($components['binary']['left_volume'] + $components['binary']['right_volume']) / 1000000;
        if ($teamSize >= 10) {
            $bonuses['leadership_bonus'] = $businessVolume * (($bonusConfig['leadership_bonus'] ?? 2) / 100);
        }

        // Team Building Bonus - For balanced growth
        $leftTeam = $components['binary']['left_volume'] / 1000000;
        $rightTeam = $components['binary']['right_volume'] / 1000000;
        if ($leftTeam >= 5 && $rightTeam >= 5) {
            $bonuses['team_building_bonus'] = min($leftTeam, $rightTeam) * ($bonusConfig['team_building_bonus'] ?? 1000);
        }

        // Volume Bonus - For high performers
        if ($businessVolume >= 10000000) { // 1 crore+
            $bonuses['volume_bonus'] = $businessVolume * (($bonusConfig['volume_bonus'] ?? 0.5) / 100);
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
    private function getBinaryCommissionRate($level, $businessVolume)
    {
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
    private function getUnilevelCommissionRate($level)
    {
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
    private function getMatrixCommissionRate($level)
    {
        return max(2, 12 - ($level - 1) * 0.5); // Decreases from 12% to 2%
    }

    /**
     * Get Associate Rank
     */
    private function getAssociateRank($associateId)
    {
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
    private function getRankBonus($rank, $businessVolume)
    {
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
    private function getBinaryStructure($associateId)
    {
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
    private function getDownlineStructure($associateId, $maxLevel = 7)
    {
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
    private function getMatrixStructure($associateId, $matrixConfig)
    {
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
    public function calculatePayoutSchedule($associateId, $totalCommission)
    {
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
    public function generateCommissionReport($associateId, $period = 'monthly')
    {
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
                MAX(created_at) as last_commission_date
            FROM commission_tracking
            WHERE associate_id = ? AND created_at BETWEEN ? AND ?
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
    public function getNextRankRequirements($associateId)
    {
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
    private function getAssociateData($associateId)
    {
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
