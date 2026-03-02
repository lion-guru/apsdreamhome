<?php

namespace App\Models;

/**
 * Advanced MLM Analytics
 */
class MLMAdvancedAnalytics
{

    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database::getInstance();
    }

    /**
     * Generate Comprehensive MLM Analytics
     */
    public function generateMLMAnalytics($associateId = null, $period = 'monthly')
    {
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

    private function calculateNetworkGrowth($associateId, $period)
    {
        $metrics = [
            'total_downline' => 0,
            'new_joins' => 0,
            'active_members' => 0,
            'growth_rate' => 0
        ];

        try {
            if ($associateId) {
                // Get direct and indirect downline count (simplified 1 level for now to avoid complex recursion issues without knowing DB limits)
                // Better: Use the recursive logic if possible, or just direct referrals if recursion is too heavy.
                // Given the context of "Advanced Analytics", I should try to get total downline.

                // Using a recursive CTE is best if supported (MySQL 8.0+).
                $sql = "
                    WITH RECURSIVE downline AS (
                        SELECT id, registration_date, status FROM mlm_agents WHERE sponsor_id = ?
                        UNION ALL
                        SELECT m.id, m.registration_date, m.status FROM mlm_agents m
                        INNER JOIN downline d ON m.sponsor_id = d.id
                    )
                    SELECT 
                        COUNT(*) as total_downline,
                        SUM(CASE WHEN registration_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as new_joins,
                        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_members
                    FROM downline
                ";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$associateId]);
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($result) {
                    $metrics['total_downline'] = (int)$result['total_downline'];
                    $metrics['new_joins'] = (int)$result['new_joins'];
                    $metrics['active_members'] = (int)$result['active_members'];
                }
            } else {
                // System-wide
                $sql = "
                    SELECT 
                        COUNT(*) as total_downline,
                        SUM(CASE WHEN registration_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as new_joins,
                        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_members
                    FROM mlm_agents
                ";
                $stmt = $this->db->query($sql);
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($result) {
                    $metrics['total_downline'] = (int)$result['total_downline'];
                    $metrics['new_joins'] = (int)$result['new_joins'];
                    $metrics['active_members'] = (int)$result['active_members'];
                }
            }

            // Calculate growth rate
            $previousTotal = $metrics['total_downline'] - $metrics['new_joins'];
            if ($previousTotal > 0) {
                $metrics['growth_rate'] = round(($metrics['new_joins'] / $previousTotal) * 100, 2);
            } else if ($metrics['new_joins'] > 0) {
                $metrics['growth_rate'] = 100;
            }
        } catch (\Exception $e) {
            // Fallback for older MySQL versions that don't support CTEs
            // Just count direct referrals
            if ($associateId) {
                $sql = "SELECT COUNT(*) as count FROM mlm_agents WHERE sponsor_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$associateId]);
                $metrics['total_downline'] = (int)$stmt->fetchColumn();
            }
        }

        return $metrics;
    }

    private function calculateCommissionAnalytics($associateId, $period)
    {
        $metrics = [
            'total_earned' => 0,
            'monthly_average' => 0,
            'top_earning_month' => 0,
            'earning_trend' => 'stable'
        ];

        try {
            $params = [];
            $whereClause = "";
            if ($associateId) {
                $whereClause = "WHERE associate_id = ?";
                $params[] = $associateId;
            }

            // Total earned
            $sql = "SELECT SUM(commission_amount) as total FROM commission_tracking $whereClause";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $metrics['total_earned'] = (float)$stmt->fetchColumn();

            // Monthly average and trend
            $sql = "
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    SUM(commission_amount) as monthly_total
                FROM commission_tracking
                $whereClause
                GROUP BY month
                ORDER BY month DESC
                LIMIT 12
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $monthlyData = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (!empty($monthlyData)) {
                $totalMonthly = 0;
                $maxMonthly = 0;
                foreach ($monthlyData as $data) {
                    $amount = (float)$data['monthly_total'];
                    $totalMonthly += $amount;
                    if ($amount > $maxMonthly) {
                        $maxMonthly = $amount;
                    }
                }
                $metrics['monthly_average'] = $totalMonthly / count($monthlyData);
                $metrics['top_earning_month'] = $maxMonthly;

                // Trend
                if (count($monthlyData) >= 2) {
                    $currentMonth = $monthlyData[0]['monthly_total'];
                    $lastMonth = $monthlyData[1]['monthly_total'];
                    if ($currentMonth > $lastMonth) $metrics['earning_trend'] = 'up';
                    elseif ($currentMonth < $lastMonth) $metrics['earning_trend'] = 'down';
                }
            }
        } catch (\Exception $e) {
            // Log error
        }

        return $metrics;
    }

    private function calculateRankProgression($associateId)
    {
        $progression = [
            'current_rank' => 'Associate',
            'next_rank' => 'Senior Associate',
            'progress_percentage' => 0,
            'requirements_met' => []
        ];

        if (!$associateId) return $progression;

        try {
            // Get current rank
            $sql = "SELECT current_level, total_business, direct_referrals FROM mlm_agents WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$associateId]);
            $agent = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($agent) {
                $progression['current_rank'] = $agent['current_level'] ?? 'Associate';

                // This logic mirrors HybridMLMCalculator roughly
                $ranks = [
                    'Associate',
                    'Senior Associate',
                    'Bronze Director',
                    'Silver Director',
                    'Gold Director',
                    'Sapphire Director',
                    'Emerald Ambassador',
                    'Diamond Ambassador',
                    'Royal Ambassador',
                    'Crown Ambassador'
                ];

                $currentRankIndex = array_search($progression['current_rank'], $ranks);
                if ($currentRankIndex !== false && isset($ranks[$currentRankIndex + 1])) {
                    $progression['next_rank'] = $ranks[$currentRankIndex + 1];

                    // Mock progress calculation based on next rank requirements
                    // In a real scenario, we'd fetch specific requirements.
                    // Assuming simplified progress based on business volume for now.
                    $progression['progress_percentage'] = 50; // Placeholder
                } else {
                    $progression['next_rank'] = 'Max Rank';
                    $progression['progress_percentage'] = 100;
                }
            }
        } catch (\Exception $e) {
            // Log error
        }

        return $progression;
    }

    private function calculateTeamPerformance($associateId)
    {
        $metrics = [
            'team_size' => 0,
            'active_team_members' => 0,
            'team_business_volume' => 0,
            'team_earnings' => 0
        ];

        if (!$associateId) return $metrics;

        try {
            // Use recursive CTE for team size and volume
            $sql = "
                WITH RECURSIVE downline AS (
                    SELECT id, total_business, status FROM mlm_agents WHERE sponsor_id = ?
                    UNION ALL
                    SELECT m.id, m.total_business, m.status FROM mlm_agents m
                    INNER JOIN downline d ON m.sponsor_id = d.id
                )
                SELECT 
                    COUNT(*) as team_size,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_members,
                    SUM(total_business) as team_volume
                FROM downline
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$associateId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($result) {
                $metrics['team_size'] = (int)$result['team_size'];
                $metrics['active_team_members'] = (int)$result['active_members'];
                $metrics['team_business_volume'] = (float)$result['team_volume'];
            }
        } catch (\Exception $e) {
            // Fallback
        }

        return $metrics;
    }

    private function calculateBusinessVolumeAnalytics($associateId, $period)
    {
        $metrics = [
            'personal_volume' => 0,
            'team_volume' => 0,
            'total_volume' => 0,
            'volume_trend' => 'stable'
        ];

        if (!$associateId) return $metrics;

        try {
            // Personal volume
            $sql = "SELECT total_business FROM mlm_agents WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$associateId]);
            $metrics['personal_volume'] = (float)$stmt->fetchColumn();

            // Team volume (reusing calculation from team performance)
            $metrics['team_volume'] = $this->calculateTeamPerformance($associateId)['team_business_volume'];

            $metrics['total_volume'] = $metrics['personal_volume'] + $metrics['team_volume'];
        } catch (\Exception $e) {
            // Log error
        }

        return $metrics;
    }
}
