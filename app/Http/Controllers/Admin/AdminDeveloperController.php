<?php

namespace App\Http\Controllers\Admin;

class AdminDeveloperController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();

        $totalApps = $activeApps = $totalKeys = $activeKeys = $totalWebhooks = $activeWebhooks = 0;
        $recentCalls = $apps = $webhooks = [];

        try {
            $totalApps = (int)($this->db->query("SELECT COUNT(*) FROM api_developers")->fetchColumn());
            $activeApps = (int)($this->db->query("SELECT COUNT(*) FROM api_developers WHERE status = 'active'")->fetchColumn());
            $totalKeys = (int)($this->db->query("SELECT COUNT(*) FROM api_keys")->fetchColumn());
            $activeKeys = (int)($this->db->query("SELECT COUNT(*) FROM api_keys WHERE is_active = 1")->fetchColumn());
            $totalWebhooks = (int)($this->db->query("SELECT COUNT(*) FROM webhook_endpoints")->fetchColumn());
            $activeWebhooks = (int)($this->db->query("SELECT COUNT(*) FROM webhook_endpoints WHERE is_active = 1")->fetchColumn());
            $recentCalls = $this->db->query("SELECT k.key_name, k.service_name, k.usage_count, k.last_used_at FROM api_keys k WHERE k.last_used_at IS NOT NULL ORDER BY k.last_used_at DESC LIMIT 15")->fetchAll(\PDO::FETCH_ASSOC);
            $apps = $this->db->query("SELECT * FROM api_developers ORDER BY created_at DESC")->fetchAll(\PDO::FETCH_ASSOC);
            $webhooks = $this->db->query("SELECT * FROM webhook_endpoints ORDER BY created_at DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('AdminDeveloperController::index error: ' . $e->getMessage());
        }

        return $this->render('admin/developer/index', [
            'page_title' => 'Developer Portal - APS Dream Home',
            'page_heading' => 'Developer Portal',
            'totalApps' => $totalApps,
            'activeApps' => $activeApps,
            'totalKeys' => $totalKeys,
            'activeKeys' => $activeKeys,
            'totalWebhooks' => $totalWebhooks,
            'activeWebhooks' => $activeWebhooks,
            'recentCalls' => $recentCalls,
            'apps' => $apps,
            'webhooks' => $webhooks,
        ]);
    }
}
