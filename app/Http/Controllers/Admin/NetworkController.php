<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\CoreFunctionsServiceCustom;
use App\Services\LoggingService;
use App\Core\Database;
use Exception;

/**
 * Network Controller - Custom MVC Implementation
 * Admin UI endpoints for MLM network management
 */
class NetworkController extends AdminController
{
    private $loggingService;
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->loggingService = new LoggingService();
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Display network overview
     */
    public function index()
    {
        try {
            $data = [
                'page_title' => 'MLM Network - APS Dream Home',
                'active_page' => 'network',
                'network_stats' => $this->getNetworkStats(),
                'top_performers' => $this->getTopPerformers(),
                'recent_joins' => $this->getRecentJoins()
            ];

            return $this->render('admin/network/index', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Network Index error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load network data');
            return $this->redirect('admin/dashboard');
        }
    }

    /**
     * Display network tree view
     */
    public function tree()
    {
        try {
            $associateId = (int)($_GET['associate_id'] ?? 0);
            
            if ($associateId > 0) {
                $networkData = $this->getNetworkTree($associateId);
            } else {
                // Get root associates (those without sponsors)
                $sql = "SELECT u.* FROM users u 
                        LEFT JOIN users s ON u.sponsor_id = s.id
                        WHERE u.role = 'associate' AND u.sponsor_id IS NULL
                        ORDER BY u.created_at ASC";
                $rootAssociates = $this->db->fetchAll($sql);
                $networkData = ['root_associates' => $rootAssociates];
            }

            $data = [
                'page_title' => 'Network Tree - APS Dream Home',
                'active_page' => 'network',
                'network_data' => $networkData,
                'selected_associate' => $associateId
            ];

            return $this->render('admin/network/tree', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Network Tree error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load network tree');
            return $this->redirect('admin/network');
        }
    }

    /**
     * Display commission structure
     */
    public function commission()
    {
        try {
            $data = [
                'page_title' => 'Commission Structure - APS Dream Home',
                'active_page' => 'network',
                'commission_levels' => $this->getCommissionLevels(),
                'rank_requirements' => $this->getRankRequirements(),
                'payout_history' => $this->getPayoutHistory()
            ];

            return $this->render('admin/network/commission', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Network Commission error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load commission data');
            return $this->redirect('admin/network');
        }
    }

    /**
     * Display rank management
     */
    public function ranks()
    {
        try {
            $data = [
                'page_title' => 'Rank Management - APS Dream Home',
                'active_page' => 'network',
                'ranks' => $this->getRanks(),
                'rank_distribution' => $this->getRankDistribution(),
                'rank_progression' => $this->getRankProgression()
            ];

            return $this->render('admin/network/ranks', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Network Ranks error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load rank data');
            return $this->redirect('admin/network');
        }
    }

    /**
     * Display genealogy report
     */
    public function genealogy()
    {
        try {
            $associateId = (int)($_GET['associate_id'] ?? 0);
            $levels = (int)($_GET['levels'] ?? 5);
            
            if ($associateId <= 0) {
                $this->setFlash('error', 'Please select an associate');
                return $this->redirect('admin/network/tree');
            }

            $genealogyData = $this->getGenealogyData($associateId, $levels);

            $data = [
                'page_title' => 'Genealogy Report - APS Dream Home',
                'active_page' => 'network',
                'genealogy_data' => $genealogyData,
                'associate_id' => $associateId,
                'levels' => $levels
            ];

            return $this->render('admin/network/genealogy', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Network Genealogy error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load genealogy data');
            return $this->redirect('admin/network');
        }
    }

    /**
     * Get network statistics
     */
    private function getNetworkStats(): array
    {
        try {
            $stats = [];

            // Total associates
            $sql = "SELECT COUNT(*) as total FROM users WHERE role = 'associate'";
            $result = $this->db->fetchOne($sql);
            $stats['total_associates'] = (int)($result['total'] ?? 0);

            // Active associates
            $sql = "SELECT COUNT(*) as total FROM users WHERE role = 'associate' AND status = 'active'";
            $result = $this->db->fetchOne($sql);
            $stats['active_associates'] = (int)($result['total'] ?? 0);

            // This month's joins
            $sql = "SELECT COUNT(*) as total FROM users 
                    WHERE role = 'associate' 
                    AND MONTH(created_at) = MONTH(CURRENT_DATE) 
                    AND YEAR(created_at) = YEAR(CURRENT_DATE)";
            $result = $this->db->fetchOne($sql);
            $stats['monthly_joins'] = (int)($result['total'] ?? 0);

            // Total commissions paid
            $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM mlm_commission_ledger WHERE status = 'paid'";
            $result = $this->db->fetchOne($sql);
            $stats['total_commissions'] = (float)($result['total'] ?? 0);

            // Average team size
            $sql = "SELECT AVG(team_size) as avg_size FROM (
                        SELECT COUNT(*) as team_size
                        FROM users u1
                        JOIN users u2 ON u1.id = u2.sponsor_id
                        WHERE u1.role = 'associate'
                        GROUP BY u1.id
                    ) as team_sizes";
            $result = $this->db->fetchOne($sql);
            $stats['avg_team_size'] = round((float)($result['avg_size'] ?? 0), 2);

            return $stats;
        } catch (Exception $e) {
            $this->loggingService->error("Get Network Stats error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get top performers
     */
    private function getTopPerformers(): array
    {
        try {
            $sql = "SELECT u.*, 
                           COUNT(DISTINCT u2.id) as direct_referrals,
                           COALESCE(SUM(mcl.amount), 0) as total_commissions,
                           u.mlm_rank
                    FROM users u
                    LEFT JOIN users u2 ON u.id = u2.sponsor_id
                    LEFT JOIN mlm_commission_ledger mcl ON u.id = mcl.associate_id AND mcl.status = 'paid'
                    WHERE u.role = 'associate' AND u.status = 'active'
                    GROUP BY u.id
                    ORDER BY total_commissions DESC, direct_referrals DESC
                    LIMIT 10";
            return $this->db->fetchAll($sql) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Top Performers error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent joins
     */
    private function getRecentJoins(): array
    {
        try {
            $sql = "SELECT u.*, s.name as sponsor_name
                    FROM users u
                    LEFT JOIN users s ON u.sponsor_id = s.id
                    WHERE u.role = 'associate'
                    ORDER BY u.created_at DESC
                    LIMIT 10";
            return $this->db->fetchAll($sql) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Recent Joins error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get network tree for specific associate
     */
    private function getNetworkTree(int $associateId): array
    {
        try {
            // Get associate details
            $sql = "SELECT * FROM users WHERE id = ? AND role = 'associate'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$associateId]);
            $associate = $stmt->fetch();

            if (!$associate) {
                return [];
            }

            // Get downline (recursive query simulation)
            $downline = $this->getDownline($associateId, 0, 5);

            return [
                'associate' => $associate,
                'downline' => $downline
            ];
        } catch (Exception $e) {
            $this->loggingService->error("Get Network Tree error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get downline recursively
     */
    private function getDownline(int $sponsorId, int $level, int $maxLevel): array
    {
        if ($level >= $maxLevel) {
            return [];
        }

        try {
            $sql = "SELECT u.*, 
                           COUNT(DISTINCT u2.id) as direct_referrals,
                           COALESCE(SUM(mcl.amount), 0) as total_commissions
                    FROM users u
                    LEFT JOIN users u2 ON u.id = u2.sponsor_id
                    LEFT JOIN mlm_commission_ledger mcl ON u.id = mcl.associate_id AND mcl.status = 'paid'
                    WHERE u.sponsor_id = ? AND u.role = 'associate'
                    GROUP BY u.id
                    ORDER BY u.created_at ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$sponsorId]);
            $referrals = $stmt->fetchAll();

            foreach ($referrals as &$referral) {
                $referral['level'] = $level + 1;
                $referral['children'] = $this->getDownline($referral['id'], $level + 1, $maxLevel);
            }

            return $referrals;
        } catch (Exception $e) {
            $this->loggingService->error("Get Downline error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get commission levels
     */
    private function getCommissionLevels(): array
    {
        try {
            $sql = "SELECT * FROM commission_levels ORDER BY level ASC";
            return $this->db->fetchAll($sql) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Commission Levels error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get rank requirements
     */
    private function getRankRequirements(): array
    {
        try {
            $sql = "SELECT * FROM rank_requirements ORDER BY rank_order ASC";
            return $this->db->fetchAll($sql) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Rank Requirements error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get payout history
     */
    private function getPayoutHistory(): array
    {
        try {
            $sql = "SELECT mcl.*, u.name as associate_name, u.email as associate_email
                    FROM mlm_commission_ledger mcl
                    JOIN users u ON mcl.associate_id = u.id
                    WHERE mcl.status = 'paid'
                    ORDER BY mcl.payout_date DESC
                    LIMIT 20";
            return $this->db->fetchAll($sql) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Payout History error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get ranks
     */
    private function getRanks(): array
    {
        try {
            $sql = "SELECT * FROM mlm_ranks ORDER BY rank_order ASC";
            return $this->db->fetchAll($sql) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Ranks error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get rank distribution
     */
    private function getRankDistribution(): array
    {
        try {
            $sql = "SELECT u.mlm_rank, COUNT(*) as count
                    FROM users u
                    WHERE u.role = 'associate' AND u.status = 'active'
                    GROUP BY u.mlm_rank
                    ORDER BY count DESC";
            return $this->db->fetchAll($sql) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Rank Distribution error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get rank progression
     */
    private function getRankProgression(): array
    {
        try {
            $sql = "SELECT u.name, u.mlm_rank, u.created_at,
                           COUNT(DISTINCT u2.id) as team_size,
                           COALESCE(SUM(mcl.amount), 0) as total_earnings
                    FROM users u
                    LEFT JOIN users u2 ON u.id = u2.sponsor_id
                    LEFT JOIN mlm_commission_ledger mcl ON u.id = mcl.associate_id AND mcl.status = 'paid'
                    WHERE u.role = 'associate' AND u.status = 'active'
                    GROUP BY u.id
                    ORDER BY total_earnings DESC
                    LIMIT 20";
            return $this->db->fetchAll($sql) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Rank Progression error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get genealogy data
     */
    private function getGenealogyData(int $associateId, int $levels): array
    {
        try {
            // Get root associate
            $sql = "SELECT * FROM users WHERE id = ? AND role = 'associate'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$associateId]);
            $root = $stmt->fetch();

            if (!$root) {
                return [];
            }

            // Get complete downline
            $downline = $this->getDownline($associateId, 0, $levels);

            // Calculate statistics
            $totalMembers = count($this->flattenDownline($downline));
            $activeMembers = $this->countActiveMembers($downline);

            return [
                'root' => $root,
                'downline' => $downline,
                'stats' => [
                    'total_members' => $totalMembers,
                    'active_members' => $activeMembers,
                    'levels' => $levels
                ]
            ];
        } catch (Exception $e) {
            $this->loggingService->error("Get Genealogy Data error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Flatten downline array
     */
    private function flattenDownline(array $downline): array
    {
        $flat = [];
        foreach ($downline as $member) {
            $flat[] = $member;
            if (!empty($member['children'])) {
                $flat = array_merge($flat, $this->flattenDownline($member['children']));
            }
        }
        return $flat;
    }

    /**
     * Count active members
     */
    private function countActiveMembers(array $downline): int
    {
        $count = 0;
        foreach ($downline as $member) {
            if ($member['status'] === 'active') {
                $count++;
            }
            if (!empty($member['children'])) {
                $count += $this->countActiveMembers($member['children']);
            }
        }
        return $count;
    }

    /**
     * Export network data
     */
    public function export()
    {
        try {
            $format = $_GET['format'] ?? 'csv';
            $type = $_GET['type'] ?? 'overview';

            switch ($type) {
                case 'overview':
                    $data = $this->getNetworkOverviewExport();
                    break;
                case 'tree':
                    $associateId = (int)($_GET['associate_id'] ?? 0);
                    $data = $this->getNetworkTreeExport($associateId);
                    break;
                case 'ranks':
                    $data = $this->getRankExport();
                    break;
                default:
                    $data = [];
            }

            if ($format === 'csv') {
                return $this->exportCSV($data, $type);
            } elseif ($format === 'json') {
                return $this->exportJSON($data, $type);
            }

            $this->setFlash('error', 'Invalid export format');
            return $this->redirect('admin/network');
        } catch (Exception $e) {
            $this->loggingService->error("Network Export error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to export data');
            return $this->redirect('admin/network');
        }
    }

    /**
     * Get network overview for export
     */
    private function getNetworkOverviewExport(): array
    {
        try {
            $sql = "SELECT u.id, u.name, u.email, u.phone, u.mlm_rank, u.status,
                           u.created_at, u.sponsor_id,
                           s.name as sponsor_name,
                           COUNT(DISTINCT u2.id) as direct_referrals,
                           COALESCE(SUM(mcl.amount), 0) as total_commissions
                    FROM users u
                    LEFT JOIN users s ON u.sponsor_id = s.id
                    LEFT JOIN users u2 ON u.id = u2.sponsor_id
                    LEFT JOIN mlm_commission_ledger mcl ON u.id = mcl.associate_id AND mcl.status = 'paid'
                    WHERE u.role = 'associate'
                    GROUP BY u.id
                    ORDER BY u.created_at DESC";
            return $this->db->fetchAll($sql) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Network Overview Export error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get network tree for export
     */
    private function getNetworkTreeExport(int $associateId): array
    {
        try {
            $treeData = $this->getNetworkTree($associateId);
            return $this->flattenTreeForExport($treeData['downline'] ?? []);
        } catch (Exception $e) {
            $this->loggingService->error("Get Network Tree Export error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Flatten tree for export
     */
    private function flattenTreeForExport(array $downline): array
    {
        $flat = [];
        foreach ($downline as $member) {
            $flat[] = [
                'id' => $member['id'],
                'name' => $member['name'],
                'email' => $member['email'],
                'rank' => $member['mlm_rank'],
                'level' => $member['level'],
                'direct_referrals' => $member['direct_referrals'],
                'total_commissions' => $member['total_commissions'],
                'status' => $member['status'],
                'created_at' => $member['created_at']
            ];
            
            if (!empty($member['children'])) {
                $flat = array_merge($flat, $this->flattenTreeForExport($member['children']));
            }
        }
        return $flat;
    }

    /**
     * Get rank data for export
     */
    private function getRankExport(): array
    {
        try {
            $sql = "SELECT u.mlm_rank, COUNT(*) as count,
                           AVG(DATEDIFF(CURRENT_DATE, u.created_at)) as avg_days_in_rank
                    FROM users u
                    WHERE u.role = 'associate' AND u.status = 'active'
                    GROUP BY u.mlm_rank
                    ORDER BY count DESC";
            return $this->db->fetchAll($sql) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Rank Export error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Export data as CSV
     */
    private function exportCSV(array $data, string $type): void
    {
        $filename = "network_{$type}_export_" . date('Y-m-d') . ".csv";
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        if (!empty($data)) {
            // Header row
            fputcsv($output, array_keys($data[0]));
            
            // Data rows
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
        exit;
    }

    /**
     * Export data as JSON
     */
    private function exportJSON(array $data, string $type): void
    {
        $filename = "network_{$type}_export_" . date('Y-m-d') . ".json";
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        echo json_encode([
            'type' => $type,
            'export_date' => date('Y-m-d H:i:s'),
            'data' => $data
        ]);
        
        exit;
    }
}