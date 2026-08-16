<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Traits\TenantAwareTrait;

class AgentCommissionController extends AdminController {
    use TenantAwareTrait;

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $this->requireAdmin();
        $tid = (int)$this->tenantId();

        $totalAgents = $this->db->query("SELECT COUNT(*) as cnt FROM users WHERE role IN ('agent','associate') AND tenant_id=?", [$tid])->fetch()['cnt'];

        $activeListings = $this->db->query("SELECT COUNT(*) as cnt FROM property_agents WHERE status='active' AND tenant_id=?", [$tid])->fetch()['cnt'];

        $totalCommission = $this->db->query(
            "SELECT COALESCE(SUM(amount),0) as total FROM mlm_commission_ledger WHERE type='direct_sale' AND tenant_id=?", [$tid]
        )->fetch()['total'];

        $totalSales = $this->db->query(
            "SELECT COUNT(*) as cnt FROM mlm_commission_ledger WHERE type='direct_sale' AND tenant_id=?", [$tid]
        )->fetch()['cnt'];

        $recentCommissions = $this->db->query(
            "SELECT mcl.*, u.name as agent_name, u.email as agent_email
             FROM mlm_commission_ledger mcl
             LEFT JOIN users u ON mcl.beneficiary_user_id = u.id
             WHERE mcl.commission_type = 'direct_sale' AND mcl.tenant_id = ?
             ORDER BY mcl.created_at DESC LIMIT 10",
            [$tid]
        )->fetchAll();

        $topAgents = $this->db->query(
            "SELECT u.id, u.name, u.email, COUNT(mcl.id) as sale_count, SUM(mcl.amount) as total_earned
             FROM mlm_commission_ledger mcl
             JOIN users u ON mcl.beneficiary_user_id = u.id
             WHERE mcl.commission_type = 'direct_sale' AND mcl.tenant_id = ?
             GROUP BY u.id
             ORDER BY total_earned DESC LIMIT 10",
            [$tid]
        )->fetchAll();

        $agentListings = $this->db->query(
            "SELECT pa.*, up.name as property_name, up.location as property_location, u.name as agent_name
             FROM property_agents pa
             LEFT JOIN user_properties up ON pa.property_id = up.id
             LEFT JOIN users u ON pa.agent_user_id = u.id
             WHERE pa.tenant_id = ?
             ORDER BY pa.created_at DESC LIMIT 20",
            [$tid]
        )->fetchAll();

        $allAgents = $this->db->query(
            "SELECT id, name, email FROM users WHERE role IN ('agent','associate') AND tenant_id=? ORDER BY name",
            [$tid]
        )->fetchAll();

        $allProperties = $this->db->query(
            "SELECT id, name, location FROM user_properties WHERE tenant_id=? ORDER BY name LIMIT 100",
            [$tid]
        )->fetchAll();

        $this->render('admin.agent-commission.index', compact(
            'totalAgents', 'activeListings', 'totalCommission', 'totalSales',
            'recentCommissions', 'topAgents', 'agentListings', 'allAgents', 'allProperties'
        ));
    }

    public function agentDetail($id) {
        $this->requireAdmin();
        $tid = (int)$this->tenantId();
        $id = (int)$id;

        $agent = $this->db->query("SELECT * FROM users WHERE id=? AND tenant_id=?", [$id, $tid])->fetch();
        if (!$agent) {
            header('Location: /admin/agent-commission');
            exit;
        }

        $commissions = $this->db->query(
            "SELECT * FROM mlm_commission_ledger WHERE beneficiary_user_id=? AND type='direct_sale' AND tenant_id=? ORDER BY created_at DESC",
            [$id, $tid]
        )->fetchAll();

        $listings = $this->db->query(
            "SELECT pa.*, up.name as property_name, up.location as property_location
             FROM property_agents pa
             LEFT JOIN user_properties up ON pa.property_id = up.id
             WHERE pa.agent_user_id=? AND pa.tenant_id=? ORDER BY pa.created_at DESC",
            [$id, $tid]
        )->fetchAll();

        $totalEarned = array_sum(array_column($commissions, 'amount'));
        $totalListings = count($listings);

        $this->render('admin.agent-commission.agent_detail', compact('agent', 'commissions', 'listings', 'totalEarned', 'totalListings'));
    }

    public function assignAgent() {
        $this->requireAdmin();
        $tid = (int)$this->tenantId();
        $propertyId = (int)($_POST['property_id'] ?? 0);
        $agentId = (int)($_POST['agent_user_id'] ?? 0);
        $commissionPct = (float)($_POST['commission_pct'] ?? 0);

        if ($propertyId > 0 && $agentId > 0) {
            $this->db->query(
                "INSERT INTO property_agents (agent_user_id, property_id, commission_pct, status, tenant_id) VALUES (?, ?, ?, 'active', ?)",
                [$agentId, $propertyId, $commissionPct, $tid]
            );
            $_SESSION['flash_success'] = 'Agent assigned to property successfully.';
        } else {
            $_SESSION['flash_error'] = 'Please select both an agent and a property.';
        }

        header('Location: /admin/agent-commission');
        exit;
    }
}
