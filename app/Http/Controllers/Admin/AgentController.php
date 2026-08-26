<?php

namespace App\Http\Controllers\Admin;

use App\Traits\TenantAwareTrait;

class AgentController extends AdminController
{
    use TenantAwareTrait;

    public function index()
    {
        $this->requireAdmin();
        try {
            $db = $this->db;
            [$tidSql, $tidParams] = $this->tenantWhere();
            $this->data['agents'] = $db->fetchAll(
                "SELECT u.id, u.name, u.email, u.phone, u.status,
                    (SELECT COUNT(*) FROM plot_bookings pb WHERE pb.associate_id = u.id) as deals_count,
                    (SELECT COALESCE(SUM(ml.commission_amount), 0) FROM mlm_commission_ledger ml WHERE ml.beneficiary_user_id = u.id) as total_commission
                 FROM users u WHERE u.role = 'agent'{$tidSql} ORDER BY u.name ASC",
                $tidParams
            ) ?: [];
            $this->data['totalAgents'] = count($this->data['agents']);
            $this->data['activeAgents'] = count(array_filter($this->data['agents'], fn($a) => ($a['status'] ?? 'active') === 'active'));
        } catch (\Exception $e) {
            $this->data['agents'] = [];
            $this->data['totalAgents'] = 0;
            $this->data['activeAgents'] = 0;
        }
        $this->data['page_title'] = 'Agent Management';
        return $this->render('admin/agents/index', $this->data);
    }
}
