<?php

namespace App\Http\Controllers\Admin;

use App\Core\Database;

class CRMFormController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        try {
            $db = Database::getInstance()->getConnection();
            $forms = $db->query("SELECT * FROM crm_lead_forms ORDER BY created_at DESC")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            $forms = [];
        }
        return $this->render('admin/crm/forms/index', [
            'forms' => $forms,
            'page_title' => 'Lead Capture Forms',
        ]);
    }

    public function create()
    {
        $this->requireAdmin();
        return $this->render('admin/crm/forms/builder', [
            'form' => null,
            'page_title' => 'Create Lead Form',
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        try {
            $db = Database::getInstance()->getConnection();
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $fields = $_POST['fields'] ?? '[]';
            $settings = [
                'submit_text' => $_POST['submit_text'] ?? 'Submit',
                'success_message' => $_POST['success_message'] ?? 'Thank you for your interest!',
                'redirect_url' => $_POST['redirect_url'] ?? '',
                'auto_assign' => isset($_POST['auto_assign']),
                'assign_to' => $_POST['assign_to'] ?? null,
                'drip_campaign' => $_POST['drip_campaign'] ?? null,
                'tags' => array_filter(explode(',', $_POST['tags'] ?? '')),
            ];

            if (empty($name)) {
                $this->setFlash('error', 'Form name is required');
                return $this->redirect('/admin/crm/forms');
            }

            $db->query(
                "INSERT INTO crm_lead_forms (name, description, fields, settings, created_by, tenant_id, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, NOW())",
                [$name, $description, $fields, json_encode($settings), $_SESSION['admin_id'] ?? 0, $this->tenantId()]
            );
            $this->setFlash('success', 'Form created successfully');
        } catch (\Throwable $e) {
            $this->setFlash('error', 'Failed to create form: ' . $e->getMessage());
        }
        return $this->redirect('/admin/crm/forms');
    }

    public function edit($id)
    {
        $this->requireAdmin();
        $id = (int)$id;
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM crm_lead_forms WHERE id = ?");
            $stmt->execute([$id]);
            $form = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            $form = null;
        }
        return $this->render('admin/crm/forms/builder', [
            'form' => $form,
            'page_title' => 'Edit Lead Form',
        ]);
    }

    public function update($id)
    {
        $this->requireAdmin();
        try {
            $db = Database::getInstance()->getConnection();
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $fields = $_POST['fields'] ?? '[]';
            $settings = [
                'submit_text' => $_POST['submit_text'] ?? 'Submit',
                'success_message' => $_POST['success_message'] ?? 'Thank you for your interest!',
                'redirect_url' => $_POST['redirect_url'] ?? '',
                'auto_assign' => isset($_POST['auto_assign']),
                'assign_to' => $_POST['assign_to'] ?? null,
                'drip_campaign' => $_POST['drip_campaign'] ?? null,
                'tags' => array_filter(explode(',', $_POST['tags'] ?? '')),
            ];

            [$tenantSql, $tenantParams] = $this->tenantWhere();
            $db->query(
                "UPDATE crm_lead_forms SET name=?, description=?, fields=?, settings=?, updated_at=NOW() WHERE id=? $tenantSql",
                array_merge([$name, $description, $fields, json_encode($settings), $id], $tenantParams)
            );
            $this->setFlash('success', 'Form updated successfully');
        } catch (\Throwable $e) {
            $this->setFlash('error', 'Failed to update form');
        }
        return $this->redirect('/admin/crm/forms');
    }

    public function delete($id)
    {
        $this->requireAdmin();
        try {
            $db = Database::getInstance()->getConnection();
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            $db->query("DELETE FROM crm_lead_forms WHERE id = ? $tenantSql", array_merge([$id], $tenantParams));
            $this->setFlash('success', 'Form deleted');
        } catch (\Throwable $e) {
            $this->setFlash('error', 'Failed to delete form');
        }
        return $this->redirect('/admin/crm/forms');
    }

    public function preview($id)
    {
        $this->requireAdmin();
        $id = (int)$id;
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM crm_lead_forms WHERE id = ?");
            $stmt->execute([$id]);
            $form = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            $form = null;
        }
        return $this->render('admin/crm/forms/preview', [
            'form' => $form,
            'page_title' => 'Form Preview',
        ]);
    }

    public function embedCode($id)
    {
        $this->requireAdmin();
        $id = (int)$id;
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM crm_lead_forms WHERE id = ?");
            $stmt->execute([$id]);
            $form = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            $form = null;
        }
        
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        $embedCode = '<iframe src="' . $baseUrl . '/form/' . $id . '" width="100%" height="500" frameborder="0" class="style-37644"></iframe>';
        $scriptCode = '<script src="' . $baseUrl . '/assets/js/lead-form-embed.js" data-form-id="' . $id . '"></script>';
        
        return $this->render('admin/crm/forms/embed', [
            'form' => $form,
            'embed_code' => $embedCode,
            'script_code' => $scriptCode,
            'page_title' => 'Embed Code',
        ]);
    }
}