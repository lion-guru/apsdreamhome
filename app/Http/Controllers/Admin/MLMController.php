<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class MLMController extends AdminController
{
    public function index() 
    {
        $this->requireAdmin();
        $this->data['page_title'] = 'MLM Dashboard';
        return $this->render('admin/mlm/dashboard');
    }
    
    public function users() 
    {
        $this->data['page_title'] = 'users';
        return $this->render('admin/mlm/users/index');
    }
    
    public function createAssociate() 
    {
        $this->data['page_title'] = 'Create Associate';
        
        $db = \App\Core\Database\Database::getInstance()->getPdo();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfOrFail();
            
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $password = $_POST['password'] ?? '';
            $sponsorId = !empty($_POST['sponsor_id']) ? (int)$_POST['sponsor_id'] : null;
            $level = $_POST['level'] ?? 'Associate';
            $status = $_POST['status'] ?? 'active';
            
            $agentTrack = $_POST['agent_track'] ?? 'mlm';
            $brokerageModel = $_POST['brokerage_model'] ?? 'differential';
            $brokerageRate = !empty($_POST['brokerage_rate']) ? (float)$_POST['brokerage_rate'] : 0.00;
            
            // Telecaller fields
            $telecallerSalary = !empty($_POST['telecaller_salary']) ? (float)$_POST['telecaller_salary'] : 0.00;
            $telecallerIncentiveRate = !empty($_POST['telecaller_incentive_rate']) ? (float)$_POST['telecaller_incentive_rate'] : 0.00;
            $telecallerSqftRate = !empty($_POST['telecaller_sqft_rate']) ? (float)$_POST['telecaller_sqft_rate'] : 0.00;
            $telecallerParentId = !empty($_POST['telecaller_parent_id']) ? (int)$_POST['telecaller_parent_id'] : null;
            
            if (empty($name) || empty($email) || empty($password)) {
                $this->setFlash('error', 'Name, email and password are required');
                return $this->render('admin/mlm/users/create');
            }
            
            try {
                $db->beginTransaction();
                
                // 1. Check if email already exists
                $check = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
                $check->execute([$email]);
                if ($check->fetch()) {
                    throw new \Exception("Email already exists");
                }
                
                // 2. Hash password
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                
                // 3. Insert into users
                $role = ($agentTrack === 'telecaller') ? 'telecaller' : 'associate';
                $onboardingTrack = ($agentTrack === 'telecaller') ? 'telecaller' : (($agentTrack === 'independent') ? 'free_consultant' : 'networker');
                
                $stmt = $db->prepare("
                    INSERT INTO users (name, email, phone, password, role, onboarding_track, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$name, $email, $phone, $hashedPassword, $role, $onboardingTrack, $status]);
                $userId = (int)$db->lastInsertId();
                
                // 4. Insert into associates
                $stmt = $db->prepare("
                    INSERT INTO associates 
                        (user_id, status, agent_track, brokerage_model, brokerage_rate, 
                         telecaller_salary, telecaller_incentive_rate, telecaller_sqft_rate, telecaller_parent_id, 
                         created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $userId, $status, $agentTrack, $brokerageModel, $brokerageRate,
                    $telecallerSalary, $telecallerIncentiveRate, $telecallerSqftRate, $telecallerParentId
                ]);
                
                // 5. Insert into user_wallets
                $stmt = $db->prepare("
                    INSERT INTO user_wallets (user_id, user_type, balance, total_credited, is_active) 
                    VALUES (?, 'associate', 0, 0, 1)
                ");
                $stmt->execute([$userId]);
                
                // 6. Insert into mlm_profiles
                $refCode = 'APS' . str_pad($userId, 4, '0', STR_PAD_LEFT);
                $stmt = $db->prepare("
                    INSERT INTO mlm_profiles 
                        (user_id, referral_code, sponsor_user_id, user_type, current_level, 
                         lifetime_sales, total_team_size, direct_referrals, total_commission, pending_commission, 
                         status, created_at) 
                    VALUES (?, ?, ?, ?, ?, 0, 0, 0, 0, 0, 'active', NOW())
                ");
                $stmt->execute([
                    $userId, $refCode, $sponsorId ?: 1, $role, $level
                ]);
                
                // 7. Insert into mlm_network_tree
                $parentId = $sponsorId ?: 1;
                $stmtP = $db->prepare("SELECT level FROM mlm_network_tree WHERE associate_id = ? LIMIT 1");
                $stmtP->execute([$parentId]);
                $parentRow = $stmtP->fetch(\PDO::FETCH_ASSOC);
                $levelNumber = $parentRow ? ($parentRow['level'] + 1) : 1;
                
                $treeCols = $db->query("SHOW COLUMNS FROM mlm_network_tree")->fetchAll(\PDO::FETCH_COLUMN, 0);
                if (in_array('sponsor_id', $treeCols)) {
                    $stmtTree = $db->prepare("
                        INSERT INTO mlm_network_tree (associate_id, sponsor_id, parent_id, level, position) 
                        VALUES (?, ?, ?, ?, 'left')
                    ");
                    $stmtTree->execute([$userId, $parentId, $parentId, $levelNumber]);
                } else {
                    $stmtTree = $db->prepare("
                        INSERT INTO mlm_network_tree (associate_id, parent_id, level) 
                        VALUES (?, ?, ?)
                    ");
                    $stmtTree->execute([$userId, $parentId, $levelNumber]);
                }
                
                // Update parent's direct referrals count
                if ($sponsorId) {
                    $db->prepare("UPDATE mlm_profiles SET direct_referrals = direct_referrals + 1 WHERE user_id = ?")
                       ->execute([$sponsorId]);
                }
                
                $db->commit();
                $this->setFlash('success', 'MLM Associate / Agent created successfully');
                return $this->redirect('/admin/mlm/users');
                
            } catch (\Exception $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                $this->setFlash('error', 'Error creating associate: ' . $e->getMessage());
            }
        }
        
        // Fetch sponsors for selection
        try {
            $this->data['sponsors'] = $db->query("SELECT id, name, email FROM users WHERE role IN ('associate', 'admin', 'telecaller') ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);
            $this->data['telecallers'] = $db->query("SELECT id, name, email FROM users WHERE role = 'telecaller' ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $this->data['sponsors'] = [];
            $this->data['telecallers'] = [];
        }
        
        return $this->render('admin/mlm/users/create', $this->data);
    }
    
    public function commission() 
    {
        $this->data['page_title'] = 'Commission';
        return $this->render('admin/mlm/commission/index');
    }
    
    public function network() 
    {
        $this->data['page_title'] = 'Network';
        return $this->render('admin/mlm/network/tree');
    }
    
    public function payouts() 
    {
        $this->data['page_title'] = 'Payouts';
        return $this->render('admin/mlm/payouts/index');
    }
    
    public function tree() 
    {
        $this->data['page_title'] = 'Network Tree';
        $this->data['treeData'] = $this->getTreeData();
        return $this->render('admin/mlm/tree');
    }
    
    public function genealogy() 
    {
        $this->data['page_title'] = 'Genealogy';
        $this->data['genealogyData'] = $this->getGenealogyData();
        return $this->render('admin/mlm/genealogy');
    }
    
    public function ranks() 
    {
        $this->data['page_title'] = 'Ranks';
        $this->data['ranksData'] = $this->getRanksData();
        return $this->render('admin/mlm/ranks');
    }

    private function getTreeData()
    {
        $data = ['nodes' => [], 'stats' => ['total_downline' => 0, 'left_count' => 0, 'right_count' => 0, 'pairing_bonus' => 0]];
        try {
            $db = $this->db;
            $nodes = $db->fetchAll("SELECT nt.id, nt.root_id, nt.parent_id, nt.level, nt.position, nt.total_left_count, nt.total_right_count, nt.total_left_bv, nt.total_right_bv, nt.personal_bv, nt.is_active, nt.joined_at, u.name, u.email, mp.current_level, mp.total_commission, mp.referral_code FROM network_tree nt LEFT JOIN users u ON nt.root_id = u.id LEFT JOIN mlm_profiles mp ON nt.root_id = mp.user_id ORDER BY nt.level ASC, nt.id ASC");
            $data['nodes'] = $nodes ?? [];
            $data['stats']['total_downline'] = count($data['nodes']);
            foreach ($data['nodes'] as $n) {
                if ($n['position'] === 'left') $data['stats']['left_count']++;
                if ($n['position'] === 'right') $data['stats']['right_count']++;
            }
            try {
                $bonus = $db->fetch("SELECT COALESCE(SUM(amount),0) as total FROM mlm_commission_ledger WHERE commission_type = 'pairing'");
                $data['stats']['pairing_bonus'] = (float)($bonus['total'] ?? 0);
            } catch (\Exception $e) { $data['stats']['pairing_bonus'] = 0; }
        } catch (\Exception $e) { error_log('MLMController::getTreeData error: ' . $e->getMessage()); }
        return $data;
    }

    private function getGenealogyData()
    {
        $data = ['members' => [], 'stats' => ['total' => 0, 'active' => 0, 'total_volume' => 0, 'max_depth' => 0]];
        try {
            $db = $this->db;
            $members = $db->fetchAll("SELECT mp.id, mp.user_id, mp.referral_code, mp.sponsor_user_id, mp.current_level, mp.total_commission, mp.pending_commission, mp.lifetime_sales, mp.direct_referrals, mp.total_team_size, mp.status, mp.created_at, u.name, u.email, sp.name as sponsor_name FROM mlm_profiles mp LEFT JOIN users u ON mp.user_id = u.id LEFT JOIN mlm_profiles sp ON mp.sponsor_user_id = sp.user_id LEFT JOIN users su ON mp.sponsor_user_id = su.id ORDER BY mp.id ASC");
            $data['members'] = $members ?? [];
            $data['stats']['total'] = count($data['members']);
            foreach ($data['members'] as $m) {
                if ($m['status'] === 'active') $data['stats']['active']++;
                $data['stats']['total_volume'] += (float)$m['lifetime_sales'];
            }
            try {
                $depth = $db->fetch("SELECT COALESCE(MAX(level),0) as max_depth FROM network_tree");
                $data['stats']['max_depth'] = (int)($depth['max_depth'] ?? 0);
            } catch (\Exception $e) { $data['stats']['max_depth'] = 0; }
        } catch (\Exception $e) { error_log('MLMController::getGenealogyData error: ' . $e->getMessage()); }
        return $data;
    }

    private function getRanksData()
    {
        $data = ['benefits' => [], 'rankCounts' => [], 'recentPromotions' => [], 'stats' => ['total_ranks' => 0, 'total_members' => 0]];
        try {
            $db = $this->db;
            try {
                $data['benefits'] = $db->fetchAll("SELECT * FROM mlm_rank_benefits ORDER BY rank_order ASC") ?? [];
            } catch (\Exception $e) { $data['benefits'] = []; }
            try {
                $raw = $db->fetchAll("SELECT mp.current_level, COUNT(*) as cnt FROM mlm_profiles mp GROUP BY mp.current_level");
                $data['rankCounts'] = [];
                foreach ($raw as $r) { $data['rankCounts'][$r['current_level']] = (int)$r['cnt']; }
            } catch (\Exception $e) { $data['rankCounts'] = []; }
            $data['stats']['total_ranks'] = count($data['benefits']);
            $data['stats']['total_members'] = array_sum($data['rankCounts']);
            try {
                $data['recentPromotions'] = $db->fetchAll("SELECT mp.user_id, u.name, mp.current_level, mp.rank_updated_at FROM mlm_profiles mp LEFT JOIN users u ON mp.user_id = u.id WHERE mp.rank_updated_at IS NOT NULL ORDER BY mp.rank_updated_at DESC LIMIT 10") ?? [];
            } catch (\Exception $e) { $data['recentPromotions'] = []; }
        } catch (\Exception $e) { error_log('MLMController::getRanksData error: ' . $e->getMessage()); }
        return $data;
    }
}