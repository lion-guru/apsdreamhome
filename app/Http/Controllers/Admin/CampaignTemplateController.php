<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Traits\TenantAwareTrait;

class CampaignTemplateController extends AdminController {
    use TenantAwareTrait;

    public function __construct() {
        parent::__construct();
    }

    private function pdo(): \PDO {
        return \App\Core\Database\Database::getInstance()->getPdo();
    }

    public function index() {
        $this->requireAdmin();
        $tid = (int)$this->tenantId();

        $search = trim($_GET['search'] ?? '');
        $typeFilter = trim($_GET['type'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $where = ['1=1'];
        $params = [];

        if ($tid > 1) {
            $where[] = 'tenant_id = ?';
            $params[] = $tid;
        }

        if ($search !== '') {
            $where[] = 'name LIKE ?';
            $params[] = "%{$search}%";
        }

        if ($typeFilter !== '' && in_array($typeFilter, ['email', 'sms', 'whatsapp', 'push'])) {
            $where[] = 'type = ?';
            $params[] = $typeFilter;
        }

        $whereSql = implode(' AND ', $where);

        try {
            $countRow = $this->pdo()->query("SELECT COUNT(*) as cnt FROM marketing_campaign_templates WHERE {$whereSql}")->fetch();
            $total = (int)($countRow['cnt'] ?? 0);
            $totalPages = max(1, ceil($total / $perPage));

            $stmt = $this->pdo()->prepare(
                "SELECT * FROM marketing_campaign_templates WHERE {$whereSql} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}"
            );
            $stmt->execute($params);
            $templates = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $stats = [
                'total' => (int)($this->pdo()->query("SELECT COUNT(*) as c FROM marketing_campaign_templates" . ($tid > 1 ? " WHERE tenant_id = {$tid}" : ""))->fetch()['c'] ?? 0),
                'email' => (int)($this->pdo()->query("SELECT COUNT(*) as c FROM marketing_campaign_templates WHERE type='email'" . ($tid > 1 ? " AND tenant_id = {$tid}" : ""))->fetch()['c'] ?? 0),
                'sms' => (int)($this->pdo()->query("SELECT COUNT(*) as c FROM marketing_campaign_templates WHERE type='sms'" . ($tid > 1 ? " AND tenant_id = {$tid}" : ""))->fetch()['c'] ?? 0),
                'whatsapp' => (int)($this->pdo()->query("SELECT COUNT(*) as c FROM marketing_campaign_templates WHERE type='whatsapp'" . ($tid > 1 ? " AND tenant_id = {$tid}" : ""))->fetch()['c'] ?? 0),
                'push' => (int)($this->pdo()->query("SELECT COUNT(*) as c FROM marketing_campaign_templates WHERE type='push'" . ($tid > 1 ? " AND tenant_id = {$tid}" : ""))->fetch()['c'] ?? 0),
            ];
        } catch (\Throwable $e) {
            error_log('CampaignTemplateController::index error: ' . $e->getMessage());
            $templates = [];
            $total = 0;
            $totalPages = 1;
            $stats = ['total' => 0, 'email' => 0, 'sms' => 0, 'whatsapp' => 0, 'push' => 0];
        }

        $this->render('admin/campaign-templates/index', compact('templates', 'stats', 'total', 'totalPages', 'page', 'search', 'typeFilter'));
    }

    public function create() {
        $this->requireAdmin();
        $template = null;
        $this->render('admin/campaign-templates/form', compact('template'));
    }

    public function store() {
        $this->requireAdmin();
        $tid = (int)$this->tenantId();
        $userId = (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0);

        $name = trim($_POST['name'] ?? '');
        $type = trim($_POST['type'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $body = trim($_POST['body'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '' || $body === '') {
            $_SESSION['flash_error'] = 'Name and Body are required.';
            header('Location: /admin/campaign-templates/create');
            exit;
        }

        if (!in_array($type, ['email', 'sms', 'whatsapp', 'push'])) {
            $_SESSION['flash_error'] = 'Invalid template type.';
            header('Location: /admin/campaign-templates/create');
            exit;
        }

        if ($type !== 'email') {
            $subject = null;
        }

        try {
            $stmt = $this->pdo()->prepare(
                "INSERT INTO marketing_campaign_templates (name, type, subject, body, is_active, created_by, tenant_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())"
            );
            $stmt->execute([$name, $type, $subject ?: null, $body, $isActive, $userId ?: null, $tid]);

            $_SESSION['flash_success'] = 'Template created successfully.';
            header('Location: /admin/campaign-templates');
            exit;
        } catch (\Throwable $e) {
            error_log('CampaignTemplateController::store error: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Failed to create template: ' . $e->getMessage();
            header('Location: /admin/campaign-templates/create');
            exit;
        }
    }

    public function edit($id) {
        $this->requireAdmin();
        $tid = (int)$this->tenantId();
        $id = (int)$id;

        try {
            $sql = "SELECT * FROM marketing_campaign_templates WHERE id = ?";
            $params = [$id];
            if ($tid > 1) {
                $sql .= " AND tenant_id = ?";
                $params[] = $tid;
            }
            $stmt = $this->pdo()->prepare($sql);
            $stmt->execute($params);
            $template = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$template) {
                $_SESSION['flash_error'] = 'Template not found.';
                header('Location: /admin/campaign-templates');
                exit;
            }

            $this->render('admin/campaign-templates/form', compact('template'));
        } catch (\Throwable $e) {
            error_log('CampaignTemplateController::edit error: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Failed to load template.';
            header('Location: /admin/campaign-templates');
            exit;
        }
    }

    public function update($id) {
        $this->requireAdmin();
        $tid = (int)$this->tenantId();
        $id = (int)$id;

        $name = trim($_POST['name'] ?? '');
        $type = trim($_POST['type'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $body = trim($_POST['body'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '' || $body === '') {
            $_SESSION['flash_error'] = 'Name and Body are required.';
            header('Location: /admin/campaign-templates/edit/' . $id);
            exit;
        }

        if (!in_array($type, ['email', 'sms', 'whatsapp', 'push'])) {
            $_SESSION['flash_error'] = 'Invalid template type.';
            header('Location: /admin/campaign-templates/edit/' . $id);
            exit;
        }

        if ($type !== 'email') {
            $subject = null;
        }

        try {
            $sql = "UPDATE marketing_campaign_templates SET name = ?, type = ?, subject = ?, body = ?, is_active = ?, updated_at = NOW() WHERE id = ?";
            $params = [$name, $type, $subject ?: null, $body, $isActive, $id];
            if ($tid > 1) {
                $sql .= " AND tenant_id = ?";
                $params[] = $tid;
            }
            $stmt = $this->pdo()->prepare($sql);
            $stmt->execute($params);

            $_SESSION['flash_success'] = 'Template updated successfully.';
            header('Location: /admin/campaign-templates');
            exit;
        } catch (\Throwable $e) {
            error_log('CampaignTemplateController::update error: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Failed to update template: ' . $e->getMessage();
            header('Location: /admin/campaign-templates/edit/' . $id);
            exit;
        }
    }

    public function destroy($id) {
        $this->requireAdmin();
        $tid = (int)$this->tenantId();
        $id = (int)$id;

        try {
            $sql = "DELETE FROM marketing_campaign_templates WHERE id = ?";
            $params = [$id];
            if ($tid > 1) {
                $sql .= " AND tenant_id = ?";
                $params[] = $tid;
            }
            $stmt = $this->pdo()->prepare($sql);
            $stmt->execute($params);

            $_SESSION['flash_success'] = 'Template deleted.';
            header('Location: /admin/campaign-templates');
            exit;
        } catch (\Throwable $e) {
            error_log('CampaignTemplateController::destroy error: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Failed to delete template.';
            header('Location: /admin/campaign-templates');
            exit;
        }
    }
}
