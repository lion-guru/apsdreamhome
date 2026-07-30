<?php
/**
 * Lead Routing Rules Controller
 * Phase 3: Department-based lead routing
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\LeadRoutingService;
use \App\Traits\TenantAwareTrait;

class LeadRoutingController extends AdminController
{
    use TenantAwareTrait;

    private $routing;

    public function __construct() {
        parent::__construct();
        $this->routing = new LeadRoutingService();
    }

    /**
     * GET /admin/crm/routing — Dashboard with rules list + stats
     */
    public function index()
    {
        $this->requireAdmin();
        $data = [
            'page_title' => 'Lead Routing Rules',
            'rules' => $this->routing->getAllRules(),
            'stats' => $this->routing->getRoutingStats(),
        ];
        return $this->render('admin/crm/routing_rules', $data);
    }

    /**
     * GET /admin/crm/routing/create — Create rule form
     */
    public function create()
    {
        $this->requireAdmin();
        $db = \App\Core\Database::getInstance();
        $departments = $db->fetchAll("SELECT id, name FROM departments WHERE status = 'active' ORDER BY name") ?: [];
        [$tidSql, $tidParams] = $this->tenantWhere();
        $users = $db->fetchAll("SELECT id, name, email FROM users WHERE deleted_at IS NULL{$tidSql} ORDER BY name", $tidParams) ?: [];

        return $this->render('admin/crm/routing_rule_form', [
            'page_title' => 'Create Routing Rule',
            'rule' => null,
            'departments' => $departments,
            'users' => $users,
        ]);
    }

    /**
     * POST /admin/crm/routing/store — Save new rule
     */
    public function store()
    {
        $this->requireAdmin();
        $result = $this->routing->createRule($_POST);

        if ($result['success']) {
            header('Location: /admin/crm/routing?success=1');
            exit;
        }
        header('Location: /admin/crm/routing/create?error=' . urlencode($result['error']));
        exit;
    }

    /**
     * GET /admin/crm/routing/{id}/edit — Edit rule form
     */
    public function edit(int $id)
    {
        $this->requireAdmin();
        $rule = $this->routing->getRuleById($id);
        if (!$rule) {
            header('Location: /admin/crm/routing');
            exit;
        }

        $db = \App\Core\Database::getInstance();
        $departments = $db->fetchAll("SELECT id, name FROM departments WHERE status = 'active' ORDER BY name") ?: [];
        [$tidSql, $tidParams] = $this->tenantWhere();
        $users = $db->fetchAll("SELECT id, name, email FROM users WHERE deleted_at IS NULL{$tidSql} ORDER BY name", $tidParams) ?: [];

        return $this->render('admin/crm/routing_rule_form', [
            'page_title' => 'Edit Routing Rule',
            'rule' => $rule,
            'departments' => $departments,
            'users' => $users,
        ]);
    }

    /**
     * POST /admin/crm/routing/{id}/update — Update rule
     */
    public function update(int $id)
    {
        $this->requireAdmin();
        $result = $this->routing->updateRule($id, $_POST);

        if ($result['success']) {
            header('Location: /admin/crm/routing?updated=1');
            exit;
        }
        header('Location: /admin/crm/routing/{$id}/edit?error=' . urlencode($result['error']));
        exit;
    }

    /**
     * POST /admin/crm/routing/{id}/delete — Delete rule
     */
    public function delete(int $id)
    {
        $this->requireAdmin();
        $this->routing->deleteRule($id);
        header('Location: /admin/crm/routing?deleted=1');
        exit;
    }

    /**
     * POST /admin/crm/routing/{id}/toggle — Toggle active state
     */
    public function toggle(int $id)
    {
        $this->requireAdmin();
        $rule = $this->routing->getRuleById($id);
        if ($rule) {
            $this->routing->updateRule($id, ['is_active' => $rule['is_active'] ? 0 : 1]);
        }
        header('Location: /admin/crm/routing');
        exit;
    }
}
