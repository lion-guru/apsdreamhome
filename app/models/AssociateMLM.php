<?php
/**
 * Associate MLM Model
 * Handles MLM functionality for associates
 */

namespace App\Models;

class AssociateMLM extends Model {
    protected static string $table = 'associate_mlm';

    /**
     * MLM Levels Configuration
     */
    private $mlm_levels = [
        1 => ['name' => 'Associate', 'commission' => 5, 'min_downline' => 0, 'max_downline' => 3],
        2 => ['name' => 'Senior Associate', 'commission' => 7, 'min_downline' => 3, 'max_downline' => 9],
        3 => ['name' => 'Team Leader', 'commission' => 10, 'min_downline' => 9, 'max_downline' => 27],
        4 => ['name' => 'Manager', 'commission' => 12, 'min_downline' => 27, 'max_downline' => 81],
        5 => ['name' => 'Senior Manager', 'commission' => 15, 'min_downline' => 81, 'max_downline' => 243],
        6 => ['name' => 'Director', 'commission' => 18, 'min_downline' => 243, 'max_downline' => 729],
        7 => ['name' => 'Senior Director', 'commission' => 20, 'min_downline' => 729, 'max_downline' => 2187]
    ];

    /**
     * Create associate in MLM system
     */
    public function createAssociate($associate_data) {
        try {
            $sql = "INSERT INTO {$this->table} (
                user_id, sponsor_id, placement_id, level, position,
                left_leg, right_leg, total_downline, total_commission,
                status, joining_date, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $associate_data['user_id'],
                $associate_data['sponsor_id'] ?? null,
                $associate_data['placement_id'] ?? null,
                $associate_data['level'] ?? 1,
                $associate_data['position'] ?? 'left',
                $associate_data['left_leg'] ?? 0,
                $associate_data['right_leg'] ?? 0,
                $associate_data['total_downline'] ?? 0,
                $associate_data['total_commission'] ?? 0,
                $associate_data['status'] ?? 'active',
                $associate_data['joining_date'] ?? date('Y-m-d')
            ]);

        } catch (\Exception $e) {
            error_log('Associate MLM creation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get associate MLM details
     */
    public function getAssociateMLM($user_id) {
        try {
            $sql = "SELECT am.*, u.name as associate_name, u.email as associate_email,
                           u.phone as associate_phone, s.name as sponsor_name
                    FROM {$this->table} am
                    LEFT JOIN users u ON am.user_id = u.id
                    LEFT JOIN users s ON am.sponsor_id = s.id
                    WHERE am.user_id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$user_id]);

            return $stmt->fetch();

        } catch (\Exception $e) {
            error_log('Associate MLM fetch error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get downline structure
     */
    public function getDownline($associate_id, $levels = null) {
        try {
            $sql = "SELECT am.*, u.name, u.email, u.phone, u.city, u.state
                    FROM {$this->table} am
                    LEFT JOIN users u ON am.user_id = u.id
                    WHERE am.sponsor_id = ? OR am.placement_id = ?
                    ORDER BY am.level, am.position, am.joining_date";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$associate_id, $associate_id]);

            $downline = $stmt->fetchAll();

            // Organize by levels if specified
            if ($levels) {
                $organized = [];
                foreach ($downline as $member) {
                    $organized[$member['level']][] = $member;
                }
                return $organized;
            }

            return $downline;

        } catch (\Exception $e) {
            error_log('Downline fetch error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Calculate commission for associate
     */
    public function calculateCommission($associate_id, $sale_amount) {
        try {
            $associate = $this->getAssociateMLM($associate_id);
            if (!$associate) {
                return 0;
            }

            $level_config = $this->mlm_levels[$associate['level']] ?? $this->mlm_levels[1];
            $commission_rate = $level_config['commission'];

            return ($sale_amount * $commission_rate) / 100;

        } catch (\Exception $e) {
            error_log('Commission calculation error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Update associate level based on downline
     */
    public function updateLevel($associate_id) {
        try {
            $downline = $this->getDownline($associate_id);
            $total_downline = count($downline);

            // Find appropriate level
            $new_level = 1;
            foreach ($this->mlm_levels as $level => $config) {
                if ($total_downline >= $config['min_downline'] && $total_downline <= $config['max_downline']) {
                    $new_level = $level;
                    break;
                }
            }

            // Update level
            $sql = "UPDATE {$this->table} SET level = ?, updated_at = NOW() WHERE user_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$new_level, $associate_id]);

            return $new_level;

        } catch (\Exception $e) {
            error_log('Level update error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get MLM genealogy tree
     */
    public function getGenealogy($associate_id, $max_levels = 5) {
        try {
            $tree = [];
            $this->buildGenealogyTree($associate_id, $tree, 1, $max_levels);

            return $tree;

        } catch (\Exception $e) {
            error_log('Genealogy fetch error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Build genealogy tree recursively
     */
    private function buildGenealogyTree($associate_id, &$tree, $current_level, $max_levels) {
        if ($current_level > $max_levels) {
            return;
        }

        try {
            $sql = "SELECT am.*, u.name, u.email, u.phone, u.city, u.state
                    FROM {$this->table} am
                    LEFT JOIN users u ON am.user_id = u.id
                    WHERE am.sponsor_id = ? OR am.placement_id = ?
                    ORDER BY am.position, am.joining_date";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$associate_id, $associate_id]);

            $downline = $stmt->fetchAll();

            if (!empty($downline)) {
                $tree['children'] = [];

                foreach ($downline as $member) {
                    $member_data = [
                        'id' => $member['user_id'],
                        'name' => $member['name'],
                        'level' => $member['level'],
                        'position' => $member['position'],
                        'joining_date' => $member['joining_date'],
                        'status' => $member['status'],
                        'total_commission' => $member['total_commission']
                    ];

                    // Recursively build tree for this member
                    $this->buildGenealogyTree($member['user_id'], $member_data, $current_level + 1, $max_levels);

                    $tree['children'][] = $member_data;
                }
            }

        } catch (\Exception $e) {
            error_log('Genealogy tree build error: ' . $e->getMessage());
        }
    }

    /**
     * Get MLM statistics for associate
     */
    public function getMLMStats($associate_id) {
        try {
            $sql = "SELECT
                        COUNT(*) as total_downline,
                        SUM(CASE WHEN level = 1 THEN 1 ELSE 0 END) as level_1,
                        SUM(CASE WHEN level = 2 THEN 1 ELSE 0 END) as level_2,
                        SUM(CASE WHEN level = 3 THEN 1 ELSE 0 END) as level_3,
                        SUM(CASE WHEN level = 4 THEN 1 ELSE 0 END) as level_4,
                        SUM(CASE WHEN level >= 5 THEN 1 ELSE 0 END) as higher_levels,
                        SUM(total_commission) as total_earnings,
                        AVG(total_commission) as avg_earnings_per_member
                    FROM {$this->table}
                    WHERE sponsor_id = ? OR placement_id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$associate_id, $associate_id]);

            $stats = $stmt->fetch();

            // Get monthly stats
            $monthly_sql = "SELECT
                               DATE_FORMAT(created_at, '%Y-%m') as month,
                               COUNT(*) as new_joins,
                               SUM(total_commission) as monthly_earnings
                            FROM {$this->table}
                            WHERE (sponsor_id = ? OR placement_id = ?)
                              AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                            ORDER BY month DESC";

            $monthly_stmt = $this->db->prepare($monthly_sql);
            $monthly_stmt->execute([$associate_id, $associate_id]);
            $monthly_stats = $monthly_stmt->fetchAll();

            return [
                'overall' => $stats,
                'monthly' => $monthly_stats
            ];

        } catch (\Exception $e) {
            error_log('MLM stats error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Process commission payout
     */
    public function processCommission($sale_data) {
        try {
            $commissions = [];

            // Get all upline associates
            $upline = $this->getUpline($sale_data['associate_id']);

            foreach ($upline as $associate) {
                $commission_amount = $this->calculateCommission($associate['user_id'], $sale_data['sale_amount']);

                if ($commission_amount > 0) {
                    // Update associate commission
                    $update_sql = "UPDATE {$this->table}
                                   SET total_commission = total_commission + ?, updated_at = NOW()
                                   WHERE user_id = ?";

                    $update_stmt = $this->db->prepare($update_sql);
                    $update_stmt->execute([$commission_amount, $associate['user_id']]);

                    $commissions[] = [
                        'associate_id' => $associate['user_id'],
                        'associate_name' => $associate['associate_name'],
                        'level' => $associate['level'],
                        'commission_amount' => $commission_amount,
                        'sale_id' => $sale_data['sale_id']
                    ];
                }
            }

            return $commissions;

        } catch (\Exception $e) {
            error_log('Commission processing error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get upline associates
     */
    private function getUpline($associate_id) {
        try {
            $upline = [];
            $current_id = $associate_id;

            // Go up the chain up to 7 levels
            for ($i = 0; $i < 7; $i++) {
                $sql = "SELECT am.*, u.name as associate_name
                        FROM {$this->table} am
                        LEFT JOIN users u ON am.user_id = u.id
                        WHERE am.user_id = ? AND am.status = 'active'";

                $stmt = $this->db->prepare($sql);
                $stmt->execute([$current_id]);

                $associate = $stmt->fetch();
                if (!$associate) {
                    break;
                }

                $upline[] = $associate;
                $current_id = $associate['sponsor_id'];

                if (!$current_id) {
                    break;
                }
            }

            return $upline;

        } catch (\Exception $e) {
            error_log('Upline fetch error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get MLM level configuration
     */
    public function getLevelConfig($level = null) {
        if ($level) {
            return $this->mlm_levels[$level] ?? null;
        }

        return $this->mlm_levels;
    }

    /**
     * Check if position is available
     */
    public function isPositionAvailable($sponsor_id, $position) {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table}
                    WHERE sponsor_id = ? AND position = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$sponsor_id, $position]);

            $count = (int)$stmt->fetch()['count'];

            // Get sponsor's max downline for current level
            $sponsor = $this->getAssociateMLM($sponsor_id);
            if (!$sponsor) {
                return false;
            }

            $level_config = $this->mlm_levels[$sponsor['level']] ?? $this->mlm_levels[1];

            return $count < ($level_config['max_downline'] / 2); // Assuming balanced binary structure

        } catch (\Exception $e) {
            error_log('Position availability check error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get associate rank and achievements
     */
    public function getAssociateRank($associate_id) {
        try {
            $associate = $this->getAssociateMLM($associate_id);
            if (!$associate) {
                return [];
            }

            $stats = $this->getMLMStats($associate_id);

            $achievements = [];
            $current_month_earnings = 0;

            // Check monthly earnings for current month
            if (!empty($stats['monthly'])) {
                $current_month = date('Y-m');
                foreach ($stats['monthly'] as $monthly) {
                    if ($monthly['month'] === $current_month) {
                        $current_month_earnings = (float)$monthly['monthly_earnings'];
                        break;
                    }
                }
            }

            // Define achievement thresholds
            $achievement_thresholds = [
                'bronze' => ['min_earnings' => 10000, 'min_downline' => 10],
                'silver' => ['min_earnings' => 25000, 'min_downline' => 25],
                'gold' => ['min_earnings' => 50000, 'min_downline' => 50],
                'platinum' => ['min_earnings' => 100000, 'min_downline' => 100],
                'diamond' => ['min_earnings' => 250000, 'min_downline' => 250]
            ];

            foreach ($achievement_thresholds as $rank => $thresholds) {
                if ($current_month_earnings >= $thresholds['min_earnings'] &&
                    ($stats['overall']['total_downline'] ?? 0) >= $thresholds['min_downline']) {
                    $achievements[] = [
                        'rank' => $rank,
                        'name' => ucfirst($rank) . ' Associate',
                        'badge' => $rank . '_badge.png',
                        'earned_date' => date('Y-m-d')
                    ];
                }
            }

            return [
                'current_level' => $associate['level'],
                'level_name' => $this->mlm_levels[$associate['level']]['name'] ?? 'Associate',
                'total_earnings' => (float)($stats['overall']['total_earnings'] ?? 0),
                'current_month_earnings' => $current_month_earnings,
                'total_downline' => (int)($stats['overall']['total_downline'] ?? 0),
                'achievements' => $achievements,
                'next_level_progress' => $this->calculateNextLevelProgress($associate, $stats)
            ];

        } catch (\Exception $e) {
            error_log('Associate rank error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Calculate progress towards next level
     */
    private function calculateNextLevelProgress($associate, $stats) {
        $current_level = $associate['level'];
        $next_level = $current_level + 1;

        if (!isset($this->mlm_levels[$next_level])) {
            return ['percentage' => 100, 'next_level' => null];
        }

        $next_level_config = $this->mlm_levels[$next_level];
        $current_downline = (int)($stats['overall']['total_downline'] ?? 0);
        $required_downline = $next_level_config['min_downline'];

        $progress = min(100, ($current_downline / $required_downline) * 100);

        return [
            'percentage' => round($progress, 1),
            'current_downline' => $current_downline,
            'required_downline' => $required_downline,
            'next_level' => $next_level,
            'next_level_name' => $next_level_config['name']
        ];
    }

    /**
     * Get MLM dashboard data for associate
     */
    public function getDashboardData($associate_id) {
        try {
            $associate = $this->getAssociateMLM($associate_id);
            $stats = $this->getMLMStats($associate_id);
            $rank = $this->getAssociateRank($associate_id);
            $downline = $this->getDownline($associate_id, 3); // First 3 levels

            return [
                'associate_info' => $associate,
                'mlm_stats' => $stats,
                'rank_info' => $rank,
                'downline_preview' => $downline,
                'recent_activities' => $this->getRecentActivities($associate_id),
                'commission_summary' => $this->getCommissionSummary($associate_id)
            ];

        } catch (\Exception $e) {
            error_log('Dashboard data error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent activities for associate
     */
    private function getRecentActivities($associate_id) {
        try {
            $sql = "SELECT 'new_join' as activity_type, u.name as member_name,
                           am.joining_date as activity_date, am.level
                    FROM {$this->table} am
                    LEFT JOIN users u ON am.user_id = u.id
                    WHERE am.sponsor_id = ? OR am.placement_id = ?
                    ORDER BY am.joining_date DESC LIMIT 10";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$associate_id, $associate_id]);

            return $stmt->fetchAll();

        } catch (\Exception $e) {
            error_log('Recent activities error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get commission summary
     */
    private function getCommissionSummary($associate_id) {
        try {
            $sql = "SELECT
                        SUM(total_commission) as total_earned,
                        AVG(total_commission) as avg_monthly,
                        MAX(total_commission) as highest_month,
                        COUNT(CASE WHEN total_commission > 0 THEN 1 END) as earning_months
                    FROM {$this->table}
                    WHERE user_id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$associate_id]);

            return $stmt->fetch();

        } catch (\Exception $e) {
            error_log('Commission summary error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate MLM report for admin
     */
    public function getMLMReport($filters = []) {
        try {
            $where_conditions = [];
            $params = [];

            if (isset($filters['level']) && !empty($filters['level'])) {
                $where_conditions[] = "am.level = ?";
                $params[] = $filters['level'];
            }

            if (isset($filters['status']) && !empty($filters['status'])) {
                $where_conditions[] = "am.status = ?";
                $params[] = $filters['status'];
            }

            if (isset($filters['date_from']) && !empty($filters['date_from'])) {
                $where_conditions[] = "am.joining_date >= ?";
                $params[] = $filters['date_from'];
            }

            if (isset($filters['date_to']) && !empty($filters['date_to'])) {
                $where_conditions[] = "am.joining_date <= ?";
                $params[] = $filters['date_to'];
            }

            $where_clause = empty($where_conditions) ? '' : 'WHERE ' . implode(' AND ', $where_conditions);

            $sql = "SELECT am.*, u.name, u.email, u.phone, u.city, u.state,
                           s.name as sponsor_name
                    FROM {$this->table} am
                    LEFT JOIN users u ON am.user_id = u.id
                    LEFT JOIN users s ON am.sponsor_id = s.id
                    {$where_clause}
                    ORDER BY am.joining_date DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            $associates = $stmt->fetchAll();

            // Generate summary
            $summary = [
                'total_associates' => count($associates),
                'active_associates' => count(array_filter($associates, function($a) { return $a['status'] === 'active'; })),
                'inactive_associates' => count(array_filter($associates, function($a) { return $a['status'] !== 'active'; })),
                'level_distribution' => array_count_values(array_column($associates, 'level')),
                'total_commission_paid' => array_sum(array_column($associates, 'total_commission')),
                'avg_commission_per_associate' => count($associates) > 0 ? array_sum(array_column($associates, 'total_commission')) / count($associates) : 0
            ];

            return [
                'associates' => $associates,
                'summary' => $summary
            ];

        } catch (\Exception $e) {
            error_log('MLM report error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Binary leg balance check
     */
    public function checkLegBalance($associate_id) {
        try {
            $sql = "SELECT
                        SUM(CASE WHEN position = 'left' THEN 1 ELSE 0 END) as left_leg_count,
                        SUM(CASE WHEN position = 'right' THEN 1 ELSE 0 END) as right_leg_count,
                        SUM(CASE WHEN position = 'left' THEN total_commission ELSE 0 END) as left_leg_commission,
                        SUM(CASE WHEN position = 'right' THEN total_commission ELSE 0 END) as right_leg_commission
                    FROM {$this->table}
                    WHERE sponsor_id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$associate_id]);

            return $stmt->fetch();

        } catch (\Exception $e) {
            error_log('Leg balance check error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get top performers
     */
    public function getTopPerformers($limit = 10) {
        try {
            $sql = "SELECT am.*, u.name, u.email, u.city, u.state,
                           RANK() OVER (ORDER BY am.total_commission DESC) as rank
                    FROM {$this->table} am
                    LEFT JOIN users u ON am.user_id = u.id
                    WHERE am.status = 'active' AND am.total_commission > 0
                    ORDER BY am.total_commission DESC
                    LIMIT ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit]);

            return $stmt->fetchAll();

        } catch (\Exception $e) {
            error_log('Top performers error: ' . $e->getMessage());
            return [];
        }
    }
}
