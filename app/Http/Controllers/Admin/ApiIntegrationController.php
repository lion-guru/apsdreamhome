<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class ApiIntegrationController extends AdminController
{
    public function developers()
    {
        $this->requireAdmin();

        $developers = $this->db->fetchAll("SELECT * FROM api_developers ORDER BY created_at DESC");
        $total = $this->db->fetch("SELECT COUNT(*) as c FROM api_developers")['c'] ?? 0;
        $active = $this->db->fetch("SELECT COUNT(*) as c FROM api_developers WHERE status = 'active'")['c'] ?? 0;

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

        $devName = $_POST['dev_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $status = $_POST['status'] ?? 'active';
        $apiKey = bin2hex(random_bytes(32));

        if (empty($devName) || empty($email)) {
            $this->setFlash('error', 'Developer name and email are required.');
            $this->redirect('/admin/api/developers/create');
        }

        $this->db->insert('api_developers', [
            'dev_name' => $devName,
            'email' => $email,
            'api_key' => $apiKey,
            'status' => $status,
        ]);

        $this->setFlash('success', 'Developer created successfully. API Key: ' . $apiKey);
        $this->redirect('/admin/api/developers');
    }

    public function integrations()
    {
        $this->requireAdmin();

        $apiIntegrations = $this->db->fetchAll("SELECT * FROM api_integrations ORDER BY created_at DESC");
        $thirdParty = $this->db->fetchAll("SELECT * FROM third_party_integrations ORDER BY created_at DESC");

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

        $logs = $this->db->fetchAll("
            SELECT il.*, ai.service_name 
            FROM integration_logs il 
            LEFT JOIN api_integrations ai ON il.integration_id = ai.id 
            ORDER BY il.created_at DESC 
            LIMIT 200
        ");

        return $this->render('admin/api/integration-logs', [
            'page_title' => 'Integration Logs',
            'logs' => $logs,
        ]);
    }
}
