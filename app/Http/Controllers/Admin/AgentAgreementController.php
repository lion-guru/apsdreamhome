<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Traits\TenantAwareTrait;

class AgentAgreementController extends AdminController {
    use TenantAwareTrait;

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $this->requireAdmin();
        $tid = (int)$this->tenantId();

        $agreements = $this->db->query(
            "SELECT aa.*, u.name as agent_name, u.email as agent_email,
                    up.name as property_name, up.location as property_location
             FROM agent_agreements aa
             LEFT JOIN users u ON aa.agent_id = u.id
             LEFT JOIN user_properties up ON aa.property_id = up.id
             WHERE aa.tenant_id = ?
             ORDER BY aa.created_at DESC",
            [$tid]
        )->fetchAll();

        $stats = [
            'total' => count($agreements),
            'draft' => count(array_filter($agreements, fn($a) => $a['status'] === 'draft')),
            'pending' => count(array_filter($agreements, fn($a) => $a['status'] === 'pending')),
            'signed' => count(array_filter($agreements, fn($a) => $a['status'] === 'signed')),
        ];

        $agents = $this->db->query(
            "SELECT id, name, email FROM users WHERE role IN ('agent','associate') AND tenant_id = ? ORDER BY name",
            [$tid]
        )->fetchAll();

        $properties = $this->db->query(
            "SELECT id, name, location FROM user_properties WHERE tenant_id = ? ORDER BY name",
            [$tid]
        )->fetchAll();

        $this->render('admin.agent-agreements.index', compact('agreements', 'stats', 'agents', 'properties'));
    }

    public function create() {
        $this->requireAdmin();
        $tid = (int)$this->tenantId();

        $agents = $this->db->query(
            "SELECT id, name, email FROM users WHERE role IN ('agent','associate') AND tenant_id = ? ORDER BY name",
            [$tid]
        )->fetchAll();

        $properties = $this->db->query(
            "SELECT id, name, location FROM user_properties WHERE tenant_id = ? ORDER BY name",
            [$tid]
        )->fetchAll();

        $this->render('admin.agent-agreements.create', compact('agents', 'properties'));
    }

    public function store() {
        $this->requireAdmin();
        $tid = (int)$this->tenantId();

        $agentId = (int)($_POST['agent_id'] ?? 0);
        $propertyId = !empty($_POST['property_id']) ? (int)$_POST['property_id'] : null;
        $title = trim($_POST['title'] ?? 'Agent Agreement');
        $content = trim($_POST['content'] ?? '');
        $commissionPct = (float)($_POST['commission_pct'] ?? 0);
        $startDate = $_POST['start_date'] ?? date('Y-m-d');
        $endDate = $_POST['end_date'] ?? date('Y-m-d', strtotime('+1 year'));
        $notes = trim($_POST['notes'] ?? '');

        if (empty($content)) {
            $content = $this->getDefaultAgreementContent($title, $commissionPct, $startDate, $endDate);
        }

        $this->db->query(
            "INSERT INTO agent_agreements (agent_id, property_id, agreement_type, title, content, commission_pct, start_date, end_date, status, notes, tenant_id)
             VALUES (?, ?, 'listing', ?, ?, ?, ?, ?, 'draft', ?, ?)",
            [$agentId, $propertyId, $title, $content, $commissionPct, $startDate, $endDate, $notes, $tid]
        );

        $_SESSION['flash_success'] = 'Agreement created successfully';
        header('Location: /admin/agent-agreements');
        exit;
    }

    public function detail($id) {
        $this->requireAdmin();
        $tid = (int)$this->tenantId();
        $id = (int)$id;

        $agreement = $this->db->query(
             "SELECT aa.*, u.name as agent_name, u.email as agent_email,
                    up.name as property_name, up.location as property_location,
                    su.name as signed_by_name
             FROM agent_agreements aa
             LEFT JOIN users u ON aa.agent_id = u.id
             LEFT JOIN user_properties up ON aa.property_id = up.id
             LEFT JOIN users su ON aa.signed_by_user_id = su.id
             WHERE aa.id = ? AND aa.tenant_id = ?",
            [$id, $tid]
        )->fetch();

        if (!$agreement) {
            header('Location: /admin/agent-agreements');
            exit;
        }

        $this->render('admin.agent-agreements.detail', compact('agreement'));
    }

    public function send($id) {
        $this->requireAdmin();
        $tid = (int)$this->tenantId();
        $id = (int)$id;

        $this->db->query(
            "UPDATE agent_agreements SET status='pending' WHERE id=? AND tenant_id=?",
            [$id, $tid]
        );

        $_SESSION['flash_success'] = 'Agreement sent for signature';
        header('Location: /admin/agent-agreements');
        exit;
    }

    public function sign($id) {
        $this->requireAdmin();
        $tid = (int)$this->tenantId();
        $id = (int)$id;
        $userId = (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0);

        $this->db->query(
            "UPDATE agent_agreements SET status='signed', signed_at=NOW(), signed_ip=?, signed_by_user_id=? WHERE id=? AND tenant_id=?",
            [$_SERVER['REMOTE_ADDR'] ?? '', $userId, $id, $tid]
        );

        $_SESSION['flash_success'] = 'Agreement signed successfully';
        header('Location: /admin/agent-agreements/detail/' . $id);
        exit;
    }

    public function cancel($id) {
        $this->requireAdmin();
        $tid = (int)$this->tenantId();
        $id = (int)$id;

        $this->db->query(
            "UPDATE agent_agreements SET status='cancelled' WHERE id=? AND tenant_id=?",
            [$id, $tid]
        );

        $_SESSION['flash_success'] = 'Agreement cancelled';
        header('Location: /admin/agent-agreements');
        exit;
    }

    private function getDefaultAgreementContent($title, $commissionPct, $startDate, $endDate) {
        $safeTitle = htmlspecialchars($title);
        return "<h2>{$safeTitle}</h2>
<p><strong>Effective Date:</strong> {$startDate} to {$endDate}</p>
<p><strong>Commission Rate:</strong> {$commissionPct}%</p>
<h3>Terms and Conditions</h3>
<ol>
<li>The Agent agrees to market and promote property listings as assigned by APS Dream Home.</li>
<li>Commission of {$commissionPct}% shall be paid on successful transaction closure.</li>
<li>The Agent shall maintain professionalism and ethical standards at all times.</li>
<li>This agreement is valid from {$startDate} to {$endDate}.</li>
<li>Either party may terminate this agreement with 30 days written notice.</li>
<li>The Agent shall not make unauthorized representations on behalf of APS Dream Home.</li>
</ol>
<p><strong>Agent Signature:</strong> ___________________</p>
<p><strong>Date:</strong> ___________________</p>";
    }
}
