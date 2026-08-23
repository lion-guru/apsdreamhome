<?php

namespace App\Http\Controllers\Admin;

use App\Services\Loyalty\LoyaltyRewardsService;

/**
 * Admin Loyalty Controller
 * Manage loyalty program from admin panel
 */
class AdminLoyaltyController extends AdminController
{
    private $loyaltyService;
    
    public function __construct()
    {
        parent::__construct();
        $this->loyaltyService = new LoyaltyRewardsService();
    }
    
    /**
     * Loyalty dashboard
     */
    public function index(): void
    {
        $stats = $this->loyaltyService->getAdminStats();
        $tiers = $this->loyaltyService->getAllTiers();
        
        $this->render('admin/loyalty/index', [
            'stats' => $stats,
            'tiers' => $tiers,
            'title' => 'Loyalty Program Management'
        ]);
    }
    
    /**
     * List all loyalty members
     */
    public function members(): void
    {
        $page = $_GET['page'] ?? 1;
        $perPage = 50;
        
        $db = \App\Core\Database\Database::getInstance();
        
        // Get members with pagination
        $sql = "SELECT lp.*, u.name, u.email, u.phone 
            FROM loyalty_points lp
            LEFT JOIN users u ON lp.user_id = u.id
            WHERE lp.user_type = 'customer'
            ORDER BY lp.points DESC
            LIMIT ? OFFSET ?";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$perPage, ($page - 1) * $perPage]);
        $members = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Get total count
        $countSql = "SELECT COUNT(*) FROM loyalty_points WHERE user_type = 'customer'";
        $total = $db->query($countSql)->fetchColumn();
        
        $this->render('admin/loyalty/members', [
            'members' => $members,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => ceil($total / $perPage),
            'title' => 'Loyalty Members'
        ]);
    }
    
    /**
     * View member details
     */
    public function memberDetails(int $userId): void
    {
        // Build member data for the view
        $member = [];
        try {
            $dashboard = $this->loyaltyService->getDashboard($userId, 'customer');
            $member = $dashboard['account'] ?? [];
            $member['name'] = $member['name'] ?? 'User #' . $userId;
            $member['tier'] = $member['current_tier'] ?? 'bronze';
            $member['total_redeemed'] = $member['total_redeemed'] ?? 0;
        } catch (\Exception $e) {
            $member = ['id' => $userId, 'name' => 'User #' . $userId, 'email' => '', 'phone' => '', 'points' => 0, 'tier' => 'bronze', 'status' => 'active', 'join_date' => '', 'total_redeemed' => 0];
        }
        
        // Get transaction history
        $points_history = [];
        try {
            $db = \App\Core\Database\Database::getInstance();
            $sql = "SELECT * FROM loyalty_transactions 
                WHERE user_id = ?
                ORDER BY created_at DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute([$userId]);
            $points_history = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) { error_log('AdminLoyaltyController member details: ' . $e->getMessage()); }
        
        $this->render('admin/loyalty/member_details', [
            'member' => $member,
            'points_history' => $points_history,
            'pageTitle' => 'Member Loyalty Details'
        ]);
    }
    
    /**
     * Add points to member (admin adjustment)
     */
    public function addPoints(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            $userId = (int) ($_POST['user_id'] ?? 0);
            $points = (int) ($_POST['points'] ?? 0);
            $reason = $_POST['reason'] ?? 'Admin adjustment';
            
            if ($userId && $points) {
                $db = \App\Core\Database\Database::getInstance();
                
                // Add transaction
                $tid = $this->tenantId();
                $sql = "INSERT INTO loyalty_transactions 
                    (user_id, user_type, transaction_type, points, description, balance_after, tenant_id)
                    VALUES (?, 'customer', 'adjusted', ?, ?, 
                        (SELECT points + ? FROM loyalty_points WHERE user_id = ? AND user_type = 'customer'), ?)";
                
                $stmt = $db->prepare($sql);
                $stmt->execute([$userId, $points, $reason, $points, $userId, $tid]);
                
                // Update points
                [$tenantSql, $tenantParams] = $this->tenantWhere();
                $updateSql = "UPDATE loyalty_points SET 
                    points = points + ?,
                    lifetime_points = lifetime_points + ?
                    WHERE user_id = ? AND user_type = 'customer' $tenantSql";
                
                $updateStmt = $db->prepare($updateSql);
                $updateStmt->execute(array_merge([$points, $points, $userId], $tenantParams));
                
                $_SESSION['success'] = "Added $points points to member";
            }
            
            redirect('/admin/loyalty/members');
            exit;
        }
    }
    
    /**
     * Manage rewards catalog
     */
    public function rewards(): void
    {
        $db = \App\Core\Database\Database::getInstance();
        
        $sql = "SELECT * FROM rewards_catalog ORDER BY points_cost ASC";
        $rewards = $db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        
        $this->render('admin/loyalty/rewards', [
            'rewards' => $rewards,
            'title' => 'Rewards Catalog'
        ]);
    }
    
    /**
     * Add/Edit reward
     */
    public function editReward(?int $id = null): void
    {
        $db = \App\Core\Database\Database::getInstance();
        $reward = null;
        
        if ($id) {
            $sql = "SELECT * FROM rewards_catalog WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$id]);
            $reward = $stmt->fetch(\PDO::FETCH_ASSOC);
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            $data = [
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? '',
                'points_required' => (int) ($_POST['points_required'] ?? 0),
                'reward_type' => $_POST['reward_type'] ?? 'cashback',
                'reward_value' => (float) ($_POST['reward_value'] ?? 0),
                'stock_quantity' => (int) ($_POST['stock_quantity'] ?? -1),
                'is_limited' => isset($_POST['is_limited']) ? 1 : 0,
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];
            
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            if ($id) {
                // Update
                $sql = "UPDATE rewards_catalog SET 
                    name = ?, description = ?, points_required = ?, reward_type = ?,
                    reward_value = ?, stock_quantity = ?, is_limited = ?, is_active = ?
                    WHERE id = ? $tenantSql";
                $stmt = $db->prepare($sql);
                $stmt->execute(array_merge([
                    $data['name'], $data['description'], $data['points_required'],
                    $data['reward_type'], $data['reward_value'], $data['stock_quantity'],
                    $data['is_limited'], $data['is_active'], $id
                ], $tenantParams));
                
                $_SESSION['success'] = 'Reward updated successfully';
            } else {
                // Create
                $tid = $this->tenantId();
                $sql = "INSERT INTO rewards_catalog 
                    (name, description, points_required, reward_type, reward_value, stock_quantity, is_limited, is_active, tenant_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    $data['name'], $data['description'], $data['points_required'],
                    $data['reward_type'], $data['reward_value'], $data['stock_quantity'],
                    $data['is_limited'], $data['is_active'], $tid
                ]);
                
                $_SESSION['success'] = 'Reward created successfully';
            }
            
            redirect('/admin/loyalty/rewards');
            exit;
        }
        
        $this->render('admin/loyalty/edit_reward', [
            'reward' => $reward,
            'title' => $id ? 'Edit Reward' : 'Add Reward'
        ]);
    }
    
    /**
     * View redemption history
     */
    public function redemptions(): void
    {
        $page = $_GET['page'] ?? 1;
        $perPage = 50;
        
        $db = \App\Core\Database\Database::getInstance();
        
        try {
            $sql = "SELECT rr.*, rc.reward_name, u.name as user_name, u.email
                FROM reward_redemptions rr
                JOIN rewards_catalog rc ON rr.reward_id = rc.id
                LEFT JOIN users u ON rr.user_id = u.id
                ORDER BY rr.redemption_date DESC
                LIMIT ? OFFSET ?";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$perPage, ($page - 1) * $perPage]);
        $redemptions = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        try {
            // Get stats
            $statsSql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                COALESCE(SUM(points_spent), 0) as total_points
                FROM reward_redemptions";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        $stats = $db->query($statsSql)->fetch(\PDO::FETCH_ASSOC);
        
        $this->render('admin/loyalty/redemptions', [
            'redemptions' => $redemptions,
            'stats' => $stats,
            'page' => $page,
            'title' => 'Reward Redemptions'
        ]);
    }
    
    /**
     * Update redemption status
     */
    public function updateRedemptionStatus(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            $id = (int)($_POST['id'] ?? 0);
            $status = $_POST['status'] ?? '';
            
            $db = \App\Core\Database\Database::getInstance();
            try {
                [$tenantSql, $tenantParams] = $this->tenantWhere();
                $sql = "UPDATE reward_redemptions SET status = ? WHERE id = ? $tenantSql";
            } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
            error_log($e->getMessage());
            }
            $stmt = $db->prepare($sql);
            $stmt->execute(array_merge([$status, $id], $tenantParams));
            
            $_SESSION['success'] = 'Redemption status updated';
            redirect('/admin/loyalty/redemptions');
            exit;
        }
    }
    
    /**
     * Points rules management
     */
    public function rules(): void
    {
        $db = \App\Core\Database\Database::getInstance();
        $sql = "SELECT * FROM points_rules ORDER BY action_type";
        $rules = $db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        
        $this->render('admin/loyalty/rules', [
            'rules' => $rules,
            'title' => 'Points Earning Rules'
        ]);
    }
    
    /**
     * Tier benefits management
     */
    public function tierBenefits(): void
    {
        $db = \App\Core\Database\Database::getInstance();
        
        $tiers = ['bronze', 'silver', 'gold', 'platinum', 'diamond'];
        $tierData = [];
        
        foreach ($tiers as $tier) {
            try {
                $sql = "SELECT * FROM tier_benefits WHERE tier_name = ? ORDER BY sort_order";
            } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
            error_log($e->getMessage());
            }
            $stmt = $db->prepare($sql);
            $stmt->execute([$tier]);
            $tierData[$tier] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        
        $this->render('admin/loyalty/tier_benefits', [
            'tier_data' => $tierData,
            'title' => 'Tier Benefits'
        ]);
    }
}
