<?php

namespace App\Http\Controllers\Associate;

use App\Services\Associate\AssociateService;
use App\Http\Controllers\Admin\AdminController;
use Exception;
use App\Core\Database\Database;

/**
 * Associate Controller - APS Dream Home
 * Associate management and relationship tracking
 * Custom MVC implementation without Laravel dependencies
 */
class AssociateController extends AdminController
{
    private $associateService;

    public function __construct()
    {
        parent::__construct();
        $this->associateService = new AssociateService();
    }

    /**
     * Display associate dashboard
     */
    public function dashboard()
    {
        try {
            $associateId = $_SESSION['associate_id'] ?? $_SESSION['user_id'] ?? 1;
            
            // Get associate info
            $associate = $this->db->fetchOne("SELECT * FROM users WHERE id = ?", [$associateId]);
            
            // Get rank info
            $rank = $associate['rank'] ?? 'associate';
            $rankInfo = $this->db->fetchOne("SELECT * FROM mlm_rank_benefits WHERE rank_name = ?", [$rank]);
            
            // Calculate total earnings from commission ledger
            $totalEarnings = $this->db->fetchOne(
                "SELECT COALESCE(SUM(amount), 0) as total FROM mlm_commission_ledger WHERE associate_id = ?",
                [$associateId]
            );
            
            // Calculate this month earnings
            $monthEarnings = $this->db->fetchOne(
                "SELECT COALESCE(SUM(amount), 0) as total FROM mlm_commission_ledger 
                 WHERE associate_id = ? AND MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())",
                [$associateId]
            );
            
            // Get network size (downline count)
            $networkSize = $this->db->fetchOne(
                "SELECT COUNT(*) as cnt FROM mlm_network_tree WHERE parent_id = ?",
                [$associateId]
            );
            
            // Get direct referrals
            $directReferrals = $this->db->fetchOne(
                "SELECT COUNT(*) as cnt FROM mlm_network_tree WHERE parent_id = ? AND level = 1",
                [$associateId]
            );
            
            // Get network by level
            $networkByLevel = $this->db->fetchAll(
                "SELECT level, COUNT(*) as members,
                        SUM(CASE WHEN nt.associate_id IN (SELECT id FROM users WHERE status = 'active') THEN 1 ELSE 0 END) as active,
                        COALESCE(SUM((SELECT SUM(amount) FROM mlm_commission_ledger WHERE associate_id = nt.associate_id)), 0) as commission
                 FROM mlm_network_tree nt
                 WHERE nt.parent_id = ?
                 GROUP BY level
                 ORDER BY level",
                [$associateId]
            );
            
            // Get recent commissions
            $recentCommissions = $this->db->fetchAll(
                "SELECT commission_type as type, amount, created_at as date
                 FROM mlm_commission_ledger
                 WHERE associate_id = ?
                 ORDER BY created_at DESC
                 LIMIT 10",
                [$associateId]
            );
            
            // Get pending payouts
            $pendingPayouts = $this->db->fetchOne(
                "SELECT COALESCE(SUM(amount), 0) as total FROM mlm_commission_ledger 
                 WHERE associate_id = ? AND status = 'pending'",
                [$associateId]
            );
            
            // Get rank progress
            $nextRank = $this->db->fetchOne(
                "SELECT * FROM mlm_rank_benefits WHERE gbv_threshold > ? ORDER BY gbv_threshold ASC LIMIT 1",
                [$rankInfo['gbv_threshold'] ?? 0]
            );
            
            // Calculate GBV (Group Business Volume) for current associate
            $gbv = $this->db->fetchOne(
                "SELECT COALESCE(SUM(total_plot_value), 0) as gbv FROM plot_bookings pb
                 JOIN plots p ON pb.plot_id = p.id
                 WHERE pb.associate_id = ? AND pb.status NOT IN ('cancelled')",
                [$associateId]
            );
            
            $data = [
                'page_title' => 'Associate Dashboard - APS Dream Home',
                'associate' => $associate,
                'rank' => $rank,
                'rank_info' => $rankInfo,
                'next_rank' => $nextRank,
                'stats' => [
                    'total_earnings' => $totalEarnings['total'] ?? 0,
                    'month_earnings' => $monthEarnings['total'] ?? 0,
                    'network_size' => $networkSize['cnt'] ?? 0,
                    'direct_referrals' => $directReferrals['cnt'] ?? 0,
                    'pending_payouts' => $pendingPayouts['total'] ?? 0,
                    'gbv' => $gbv['gbv'] ?? 0,
                    'next_rank_threshold' => $nextRank['gbv_threshold'] ?? 0,
                ],
                'network' => $networkByLevel,
                'commissions' => $recentCommissions,
            ];
            
            $this->render('associate/dashboard', $data);
        } catch (Exception $e) {
            $this->renderError('Error loading associate dashboard', $e->getMessage());
        }
    }

    /**
     * Display associate list
     */
    public function index()
    {
        try {
            $users = $this->associateService->getAllAssociates();
            
            $data = [
                'page_title' => 'users - APS Dream Home',
                'users' => $users,
                'total_count' => count($users)
            ];
            
            $this->render('associate/index', $data);
        } catch (Exception $e) {
            $this->renderError('Error loading users', $e->getMessage());
        }
    }

    /**
     * Display create associate form
     */
    public function create()
    {
        $data = [
            'page_title' => 'Create Associate - APS Dream Home',
            'action' => '/users/store'
        ];
        
        $this->render('associate/create', $data);
    }

    /**
     * Store new associate
     */
    public function store()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'name' => $_POST['name'] ?? '',
                    'email' => $_POST['email'] ?? '',
                    'phone' => $_POST['phone'] ?? '',
                    'address' => $_POST['address'] ?? '',
                    'commission_rate' => $_POST['commission_rate'] ?? 0,
                    'status' => $_POST['status'] ?? 'active'
                ];
                
                // Validate required fields
                if (empty($data['name']) || empty($data['email'])) {
                    throw new Exception('Name and email are required');
                }
                
                $associateId = $this->associateService->createAssociate($data);
                
                if ($associateId) {
                    header('Location: /users');
                    exit;
                } else {
                    throw new Exception('Failed to create associate');
                }
            }
        } catch (Exception $e) {
            $this->renderError('Error creating associate', $e->getMessage());
        }
    }

    /**
     * Display edit associate form
     */
    public function edit($id)
    {
        try {
            $associate = $this->associateService->getAssociateById($id);
            
            if (!$associate) {
                $this->renderError('Associate not found', 'Associate with ID ' . $id . ' not found');
                return;
            }
            
            $data = [
                'page_title' => 'Edit Associate - APS Dream Home',
                'associate' => $associate,
                'action' => '/users/update/' . $id
            ];
            
            $this->render('associate/edit', $data);
        } catch (Exception $e) {
            $this->renderError('Error loading associate', $e->getMessage());
        }
    }

    /**
     * Update associate
     */
    public function update($id)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'name' => $_POST['name'] ?? '',
                    'email' => $_POST['email'] ?? '',
                    'phone' => $_POST['phone'] ?? '',
                    'address' => $_POST['address'] ?? '',
                    'commission_rate' => $_POST['commission_rate'] ?? 0,
                    'status' => $_POST['status'] ?? 'active'
                ];
                
                // Validate required fields
                if (empty($data['name']) || empty($data['email'])) {
                    throw new Exception('Name and email are required');
                }
                
                $result = $this->associateService->updateAssociate($id, $data);
                
                if ($result) {
                    header('Location: /users');
                    exit;
                } else {
                    throw new Exception('Failed to update associate');
                }
            }
        } catch (Exception $e) {
            $this->renderError('Error updating associate', $e->getMessage());
        }
    }

    /**
     * Delete associate
     */
    public function delete($id)
    {
        try {
            $result = $this->associateService->deleteAssociate($id);
            
            if ($result) {
                header('Location: /users');
                exit;
            } else {
                throw new Exception('Failed to delete associate');
            }
        } catch (Exception $e) {
            $this->renderError('Error deleting associate', $e->getMessage());
        }
    }

    /**
     * Display associate details
     */
    public function show($id)
    {
        try {
            $associate = $this->associateService->getAssociateById($id);
            $metrics = $this->associateService->getAssociateMetrics($id, date('Y-m-01'), date('Y-m-t'));
            $salesHistory = $this->associateService->getAssociateSalesHistory($id);
            
            if (!$associate) {
                $this->renderError('Associate not found', 'Associate with ID ' . $id . ' not found');
                return;
            }
            
            $data = [
                'page_title' => 'Associate Details - APS Dream Home',
                'associate' => $associate,
                'metrics' => $metrics,
                'sales_history' => $salesHistory
            ];
            
            $this->render('associate/show', $data);
        } catch (Exception $e) {
            $this->renderError('Error loading associate details', $e->getMessage());
        }
    }

    /**
     * Display associate performance metrics
     */
    public function metrics($id)
    {
        try {
            $associate = $this->associateService->getAssociateById($id);
            
            if (!$associate) {
                $this->renderError('Associate not found', 'Associate with ID ' . $id . ' not found');
                return;
            }
            
            $startDate = $_GET['start_date'] ?? date('Y-m-01');
            $endDate = $_GET['end_date'] ?? date('Y-m-t');
            
            $metrics = $this->associateService->getAssociateMetrics($id, $startDate, $endDate);
            
            $data = [
                'page_title' => 'Associate Metrics - APS Dream Home',
                'associate' => $associate,
                'metrics' => $metrics,
                'start_date' => $startDate,
                'end_date' => $endDate
            ];
            
            $this->render('associate/metrics', $data);
        } catch (Exception $e) {
            $this->renderError('Error loading associate metrics', $e->getMessage());
        }
    }

    /**
     * Update associate status
     */
    public function updateStatus($id)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $status = $_POST['status'] ?? 'active';
                
                $result = $this->associateService->updateAssociateStatus($id, $status);
                
                if ($result) {
                    header('Location: /users');
                    exit;
                } else {
                    throw new Exception('Failed to update associate status');
                }
            }
        } catch (Exception $e) {
            $this->renderError('Error updating associate status', $e->getMessage());
        }
    }
}
