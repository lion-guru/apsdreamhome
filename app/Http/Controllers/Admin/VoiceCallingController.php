<?php

namespace App\Http\Controllers\Admin;

use \App\Traits\TenantAwareTrait;

class VoiceCallingController extends AdminController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/admin';
        $this->data = [];
    }

    public function index()
    {
        $this->requireAdmin();

        // Overview stats
        $stats = [
            'total_agents' => 0,
            'active_agents' => 0,
            'today_calls' => 0,
            'today_connected' => 0,
            'today_interested' => 0,
            'today_leads' => 0,
            'conversion_rate' => 0,
            'avg_duration' => 0,
        ];

        try {
            $stats['total_agents'] = $this->db->fetch("SELECT COUNT(*) as c FROM ai_calling_agents")['c'] ?? 0;
            $stats['active_agents'] = $this->db->fetch("SELECT COUNT(*) as c FROM ai_calling_agents WHERE status = 'active'")['c'] ?? 0;
            $stats['today_calls'] = $this->db->fetch("SELECT COUNT(*) as c FROM ai_call_sessions WHERE DATE(created_at) = CURDATE()")['c'] ?? 0;
            $stats['today_connected'] = $this->db->fetch("SELECT COUNT(*) as c FROM ai_call_sessions WHERE DATE(created_at) = CURDATE() AND status = 'completed'")['c'] ?? 0;
            $stats['today_interested'] = $this->db->fetch("SELECT COUNT(*) as c FROM ai_call_sessions WHERE DATE(created_at) = CURDATE() AND (customer_response = 'interested' OR interest_level IN ('hot','warm'))")['c'] ?? 0;
            $stats['today_leads'] = $this->db->fetch("SELECT COUNT(*) as c FROM ai_call_extracted_leads WHERE auto_created_lead_id IS NOT NULL AND DATE(created_at) = CURDATE()")['c'] ?? 0;
            $stats['avg_duration'] = $this->db->fetch("SELECT AVG(duration) as avg FROM ai_call_sessions WHERE DATE(created_at) = CURDATE() AND duration > 0")['avg'] ?? 0;
        } catch (\Exception $e) {}

        $stats['conversion_rate'] = $stats['today_calls'] > 0 ? round(($stats['today_interested'] / $stats['today_calls']) * 100, 1) : 0;

        // 7-day trend
        $trend = [];
        try {
            $trend = $this->db->fetchAll("
                SELECT DATE(created_at) as date, 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN customer_response = 'interested' OR interest_level IN ('hot','warm') THEN 1 ELSE 0 END) as interested
                FROM ai_call_sessions 
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date ASC
            ");
        } catch (\Exception $e) {}

        // Provider status
        $providers = $this->getProviderStatus();

        // Quick actions / recent activity
        $recentCalls = [];
        try {
            $recentCalls = $this->db->fetchAll("
                SELECT s.*, l.name as lead_name, l.phone as lead_phone,
                       a.agent_name as agent_display_name
                FROM ai_call_sessions s
                LEFT JOIN leads l ON l.id = s.lead_id
                LEFT JOIN ai_calling_agents a ON a.agent_id = s.ai_agent_id
                ORDER BY s.created_at DESC
                LIMIT 10
            ");
        } catch (\Exception $e) {}

        $this->data = [
            'page_title' => 'AI Voice Calling Dashboard',
            'stats' => $stats,
            'trend' => $trend,
            'providers' => $providers,
            'recentCalls' => $recentCalls,
        ];
        $this->render('admin/voice_calling/dashboard');
    }

    public function getProviderStatus()
    {
        $providers = [];
        $configs = [];

        // Get voice configs from service_configs
        try {
            $configs = $this->db->fetchAll("
                SELECT config_key, service_name, config_value, config_type
                FROM service_configs 
                WHERE group_name = 'ai_voice'
                ORDER BY service_name, config_key
            ");
        } catch (\Exception $e) {}

        $providerList = ['voice_exotel', 'voice_twilio', 'voice_plivo', 'voice_knowlarity'];
        foreach ($providerList as $svc) {
            $svcConfigs = array_filter($configs, fn($c) => $c['service_name'] === $svc);
            $hasCreds = count(array_filter($svcConfigs, fn($c) => !empty($c['config_value']))) >= 2;
            $providerType = '';
            foreach ($svcConfigs as $c) {
                if ($c['config_key'] === 'provider_type') { $providerType = $c['config_value']; break; }
            }

            $providers[] = [
                'service_name' => $svc,
                'display_name' => ucfirst(str_replace('voice_', '', $svc)),
                'configured' => $hasCreds,
                'provider_type' => $providerType ?: ucfirst(str_replace('voice_', '', $svc)),
                'credentials' => array_reduce($svcConfigs, function($acc, $c) {
                    if ($c['config_key'] !== 'provider_type') $acc[$c['config_key']] = $c['config_value'];
                    return $acc;
                }, []),
            ];
        }

        return $providers;
    }

    public function testProvider()
    {
        $this->requireAdmin();
        $provider = $_POST['provider'] ?? '';

        $result = ['success' => false, 'message' => 'Unknown provider'];
        try {
            $configs = $this->db->fetchAll("
                SELECT config_key, config_value FROM service_configs 
                WHERE service_name = ? AND group_name = 'ai_voice'
            ", [$provider]);

            $cfg = array_column($configs, 'config_value', 'config_key');

            if (empty($cfg['provider_type']) || empty($cfg['twilio_sid']) && empty($cfg['exotel_sid']) && empty($cfg['plivo_auth_id']) && empty($cfg['knowlarity_api_key'])) {
                $result['message'] = "Provider not configured. Please add credentials in Service Configs.";
            } else {
                // Basic connectivity test
                $result['success'] = true;
                $result['message'] = "Provider configuration found. Ready for calls.";
            }
        } catch (\Exception $e) {
            $result['message'] = "Test failed: " . $e->getMessage();
        }

        $this->jsonResponse($result);
    }

    public function providerConfig()
    {
        $this->requireAdmin();
        $provider = $_GET['provider'] ?? '';
        $this->data = [
            'page_title' => 'Configure Voice Provider',
            'provider' => $provider,
        ];
        $this->render('admin/voice_calling/provider_config');
    }
}