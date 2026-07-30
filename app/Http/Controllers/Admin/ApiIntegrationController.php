<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use \App\Traits\TenantAwareTrait;

class ApiIntegrationController extends AdminController
{
    use TenantAwareTrait;

    public function developers()
    {
        $this->requireAdmin();

        $developers = [];
        $total = 0;
        $active = 0;

        try {
            $developers = $this->db->fetchAll("SELECT * FROM api_developers ORDER BY created_at DESC") ?? [];
            $total = (int)($this->db->fetch("SELECT COUNT(*) as c FROM api_developers")['c'] ?? 0);
            $active = (int)($this->db->fetch("SELECT COUNT(*) as c FROM api_developers WHERE status = 'active'")['c'] ?? 0);
        } catch (\Throwable $e) {
            error_log("ApiIntegrationController::developers query failed: " . $e->getMessage());
        }

        return $this->render('admin/api/developers', [
            'page_title' => 'API Developers',
            'developers' => $developers,
            'total' => $total,
            'active' => $active,
        ]);
    }

    public function developersCreate()
    {
        $this->requireAdmin();
        return $this->render('admin/api/create-developer', [
            'page_title' => 'Create API Developer',
        ]);
    }

    public function developersStore()
    {
        $this->requireAdmin();

        $devName = trim($_POST['dev_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $status = $_POST['status'] ?? 'active';
        $apiKey = bin2hex(random_bytes(32));

        if (empty($devName) || empty($email)) {
            $this->setFlash('error', 'Developer name and email are required.');
            $this->redirect('/admin/api/developers/create');
            return;
        }

        try {
            $this->db->insert('api_developers', [
                'dev_name' => $devName,
                'email' => $email,
                'api_key' => $apiKey,
                'status' => in_array($status, ['active', 'inactive', 'suspended'], true) ? $status : 'active',
                'tenant_id' => (int)$this->tenantId(),
            ]);
            $this->setFlash('success', 'Developer created successfully. API Key: ' . $apiKey);
        } catch (\Throwable $e) {
            error_log("ApiIntegrationController::developersStore insert failed: " . $e->getMessage());
            $this->setFlash('error', 'Could not create developer. Please try again.');
        }
        $this->redirect('/admin/api/developers');
    }

    public function integrations()
    {
        $this->requireAdmin();

        try {
            $apiIntegrations = $this->db->fetchAll("SELECT * FROM api_integrations ORDER BY created_at DESC");
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        try {
            $thirdParty = $this->db->fetchAll("SELECT * FROM third_party_integrations ORDER BY created_at DESC");
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }

        return $this->render('admin/api/integrations', [
            'page_title' => 'API Integrations',
            'api_integrations' => $apiIntegrations,
            'third_party' => $thirdParty,
        ]);
    }

    public function requestLogs()
    {
        $this->requireAdmin();

        $logs = $this->db->fetchAll("
            SELECT ar.*, ad.dev_name 
            FROM api_request_logs ar 
            LEFT JOIN api_developers ad ON ar.api_key_id = ad.id 
            ORDER BY ar.request_time DESC 
            LIMIT 200
        ");

        return $this->render('admin/api/logs', [
            'page_title' => 'API Request Logs',
            'logs' => $logs,
        ]);
    }

    public function integrationLogs()
    {
        $this->requireAdmin();

        try {
            $logs = $this->db->fetchAll("
                SELECT il.*, ai.service_name 
                FROM integration_logs il 
                LEFT JOIN api_integrations ai ON il.integration_id = ai.id 
                ORDER BY il.created_at DESC 
                LIMIT 200
            ");
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }

        return $this->render('admin/api/integration-logs', [
            'page_title' => 'Integration Logs',
            'logs' => $logs,
        ]);
    }
}
