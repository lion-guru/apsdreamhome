<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class MLMController extends AdminController
{
    public function index() 
    {
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
        return $this->render('admin/mlm/users/create');
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