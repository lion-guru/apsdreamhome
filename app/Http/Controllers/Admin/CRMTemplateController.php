<?php

namespace App\Http\Controllers\Admin;

use App\Core\Database;

class CRMTemplateController extends AdminController
{
    use \App\Traits\TenantAwareTrait;

    private function pdo(): \PDO
    {
        $db = Database::getInstance()->getConnection();
        return $db;
    }
    public function index()
    {
        $this->requireAdmin();
        try {
            $db = Database::getInstance()->getConnection();
            $templates = $db->query("SELECT * FROM email_templates ORDER BY created_at DESC")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $smsTemplates = $db->query("SELECT * FROM sms_templates ORDER BY created_at DESC")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            $templates = [];
            $smsTemplates = [];
        }
        return $this->render('admin/crm/templates/index', [
            'templates' => $templates,
            'sms_templates' => $smsTemplates,
            'page_title' => 'Email & SMS Templates',
        ]);
    }

    public function create()
    {
        $this->requireAdmin();
        return $this->render('admin/crm/templates/form', [
            'template' => null,
            'page_title' => 'Create Template',
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        try {
            $db = Database::getInstance()->getConnection();
            $name = trim($_POST['name'] ?? '');
            $type = $_POST['type'] ?? 'email';
            $subject = trim($_POST['subject'] ?? '');
            $body = trim($_POST['body'] ?? '');
            $category = $_POST['category'] ?? 'general';

            if (empty($name) || empty($body)) {
                $this->setFlash('error', 'Name and body are required');
                return $this->redirect('/admin/crm/templates');
            }

            $tid = $this->tenantId();
            $table = $type === 'sms' ? 'sms_templates' : 'email_templates';
            $columns = $type === 'sms'
                ? 'name, body, category, tenant_id, created_at'
                : 'name, subject, body, category, tenant_id, created_at';
            $values = $type === 'sms'
                ? '?, ?, ?, ?, NOW()'
                : '?, ?, ?, ?, ?, NOW()';
            $params = $type === 'sms'
                ? [$name, $body, $category, $tid]
                : [$name, $subject, $body, $category, $tid];

            $db->query("INSERT INTO $table ($columns) VALUES ($values)", $params);
            $this->setFlash('success', 'Template created successfully');
        } catch (\Throwable $e) {
            error_log('CRMTemplateController@store: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to create template');
        }
        return $this->redirect('/admin/crm/templates');
    }

    public function edit($id)
    {
        $this->requireAdmin();
        $id = (int)$id;
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM email_templates WHERE id = ?");
            $stmt->execute([$id]);
            $template = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$template) {
                $stmt = $db->prepare("SELECT * FROM sms_templates WHERE id = ?");
                $stmt->execute([$id]);
                $template = $stmt->fetch(\PDO::FETCH_ASSOC);
            }
        } catch (\Throwable $e) {
            $template = null;
        }
        return $this->render('admin/crm/templates/form', [
            'template' => $template,
            'page_title' => 'Edit Template',
        ]);
    }

    public function update($id)
    {
        $this->requireAdmin();
        try {
            $db = Database::getInstance()->getConnection();
            $name = trim($_POST['name'] ?? '');
            $subject = trim($_POST['subject'] ?? '');
            $body = trim($_POST['body'] ?? '');
            $category = $_POST['category'] ?? 'general';
            $type = $_POST['type'] ?? 'email';

            $tid = $this->tenantId();
            $table = $type === 'sms' ? 'sms_templates' : 'email_templates';
            if ($type === 'sms') {
                $db->query("UPDATE $table SET name=?, body=?, category=?, updated_at=NOW() WHERE id=? AND tenant_id=?", [$name, $body, $category, $id, $tid]);
            } else {
                $db->query("UPDATE $table SET name=?, subject=?, body=?, category=?, updated_at=NOW() WHERE id=? AND tenant_id=?", [$name, $subject, $body, $category, $id, $tid]);
            }
            $this->setFlash('success', 'Template updated successfully');
        } catch (\Throwable $e) {
            $this->setFlash('error', 'Failed to update template');
        }
        return $this->redirect('/admin/crm/templates');
    }

    public function delete($id)
    {
        $this->requireAdmin();
        try {
            $tid = $this->tenantId();
            $db = Database::getInstance()->getConnection();
            $db->query("DELETE FROM email_templates WHERE id = ? AND tenant_id = ?", [$id, $tid]);
            $db->query("DELETE FROM sms_templates WHERE id = ? AND tenant_id = ?", [$id, $tid]);
            $this->setFlash('success', 'Template deleted');
        } catch (\Throwable $e) {
            $this->setFlash('error', 'Failed to delete template');
        }
        return $this->redirect('/admin/crm/templates');
    }
}
