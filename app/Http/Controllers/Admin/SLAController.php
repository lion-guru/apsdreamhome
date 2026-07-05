<?php
namespace App\Http\Controllers\Admin;
use App\Services\SLAService;

class SLAController extends AdminController {
    public function dashboard() {
        $this->requireAdmin();
        $service = new SLAService();
        $stats = $service->getComplianceStats((int)($_GET['days'] ?? 30));
        $pending = $service->getPendingSLAs();
        $breached = $service->getBreachedSLAs(20);
        $rules = $service->getActiveRules();
        return $this->render('admin/crm/sla/dashboard', [
            'stats' => $stats, 'pending' => $pending, 'breached' => $breached,
            'rules' => $rules, 'page_title' => 'SLA Dashboard'
        ]);
    }

    public function rules() {
        $this->requireAdmin();
        $service = new SLAService();
        $rules = $service->getAllRules();
        return $this->render('admin/crm/sla/rules', ['rules' => $rules, 'page_title' => 'SLA Rules']);
    }

    public function storeRule() {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->redirect('/admin/crm/sla/rules');
        $service = new SLAService();
        $result = $service->createRule([
            'name' => $_POST['name'] ?? '',
            'rule_type' => $_POST['rule_type'] ?? 'first_response',
            'target_minutes' => (int)($_POST['target_minutes'] ?? 60),
            'applies_to_roles' => $_POST['applies_to_roles'] ?? 'all',
            'applies_to_stages' => $_POST['applies_to_stages'] ?? 'all',
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ]);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['success'] ? 'Rule created' : 'Error');
        return $this->redirect('/admin/crm/sla/rules');
    }

    public function breachLog() {
        $this->requireAdmin();
        $service = new SLAService();
        $breached = $service->getBreachedSLAs(100);
        return $this->render('admin/crm/sla/breach_log', ['breached' => $breached, 'page_title' => 'SLA Breach Log']);
    }
}
