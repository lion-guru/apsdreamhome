<?php

namespace App\Http\Controllers\Admin;

// AdminController resolved via namespace
use App\Services\Voice\AsteriskService;

/**
 * SIM Calling Admin — Manage Asterisk + GSM Gateway voice calls
 *
 * Features:
 * - Dashboard: connection status, active calls, call stats
 * - Config: Asterisk AMI settings, GSM gateway, caller ID
 * - Make call: initiate outbound call to customer
 * - Call history: past calls with status
 * - Dialplan: generate/download Asterisk config
 */
class SIMCallingController extends AdminController
{
    private $asterisk;

    public function __construct()
    {
        parent::__construct();
        $this->asterisk = new AsteriskService();
    }

    /**
     * SIM Calling Dashboard
     */
    public function dashboard()
    {
        $connected = $this->asterisk->ping();
        $channels = $connected ? $this->asterisk->getActiveChannels() : [];
        $peers = $connected ? $this->asterisk->getSIPPeers() : [];

        // Get call stats from database
        $db = $this->db;
        try {
            $stats = $db->fetch("SELECT 
                COUNT(*) as total_calls,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'answered' THEN 1 ELSE 0 END) as answered,
                SUM(CASE WHEN status = 'no-answer' THEN 1 ELSE 0 END) as no_answer,
                SUM(CASE WHEN status = 'busy' THEN 1 ELSE 0 END) as busy,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_calls
                FROM voice_call_logs WHERE channel_type = 'asterisk'") ?: [];

            $recentCalls = $db->fetchAll("SELECT vcl.*, u.name as customer_name
                FROM voice_call_logs vcl
                LEFT JOIN users u ON vcl.customer_id = u.id
                WHERE vcl.channel_type = 'asterisk'
                ORDER BY vcl.created_at DESC LIMIT 15") ?: [];
        } catch (\Exception $e) {
            $stats = ['total_calls' => 0, 'completed' => 0, 'answered' => 0, 'no_answer' => 0, 'busy' => 0, 'today_calls' => 0];
            $recentCalls = [];
        }

        $data = [
            'page_title' => 'SIM Calling Dashboard',
            'connected' => $connected,
            'channels' => $channels,
            'peers' => $peers,
            'stats' => $stats,
            'recent_calls' => $recentCalls,
        ];

        $this->render('admin/sim-calling/dashboard', $data);
    }

    /**
     * Settings page — configure Asterisk AMI connection
     */
    public function settings()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $config = [
                'host' => trim($_POST['host'] ?? '127.0.0.1'),
                'port' => (int)($_POST['port'] ?? 5038),
                'username' => trim($_POST['username'] ?? 'admin'),
                'secret' => trim($_POST['secret'] ?? ''),
                'context' => trim($_POST['context'] ?? 'outbound-calls'),
                'trunk' => trim($_POST['trunk'] ?? 'gsm-gateway'),
                'caller_id' => trim($_POST['caller_id'] ?? ''),
            ];

            $this->asterisk->saveConfig($config);
            $_SESSION['success'] = 'Asterisk settings saved!';
            header('Location: /admin/sim-calling/settings');
            exit;
        }

        // Load current config
        try {
            $db = \App\Core\Database\Database::getInstance();
            $row = $db->query("SELECT settings_value FROM system_settings WHERE settings_key = 'asterisk_config' LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
            $config = $row ? json_decode($row['settings_value'], true) : [];
        } catch (\Exception $e) {
            $config = [];
        }

        $data = [
            'page_title' => 'SIM Calling Settings',
            'config' => $config,
            'connected' => $this->asterisk->ping(),
        ];

        $this->render('admin/sim-calling/settings', $data);
    }

    /**
     * API: Make outbound call
     */
    public function makeCall()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'POST required']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $phone = trim($input['phone'] ?? '');
        $agentScript = $input['agent_script'] ?? 'default';
        $callerId = $input['caller_id'] ?? '';

        if (empty($phone)) {
            http_response_code(400);
            echo json_encode(['error' => 'Phone number required']);
            return;
        }

        $result = $this->asterisk->makeCall($phone, $agentScript, [
            'caller_id' => $callerId,
        ]);

        // Log the call attempt
        if ($result['success']) {
            try {
                $this->db->execute(
                    "INSERT INTO voice_call_logs (call_id, customer_phone, channel_type, status, started_at, notes, created_at) VALUES (?, ?, 'asterisk', 'initiated', NOW(), ?, NOW())",
                    [$result['call_id'], $phone, json_encode(['agent_script' => $agentScript])]
                );
            } catch (\Exception $e) {
                error_log("Call log error: " . $e->getMessage());
            }
        }

        echo json_encode($result);
    }

    /**
     * API: Check call status / active channels
     */
    public function status()
    {
        header('Content-Type: application/json');
        $connected = $this->asterisk->ping();
        $channels = $connected ? $this->asterisk->getActiveChannels() : [];
        $peers = $connected ? $this->asterisk->getSIPPeers() : [];

        echo json_encode([
            'connected' => $connected,
            'active_channels' => count($channels),
            'channels' => $channels,
            'peers' => $peers,
        ]);
    }

    /**
     * API: Hangup call
     */
    public function hangup()
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $channel = $input['channel'] ?? '';

        if (empty($channel)) {
            http_response_code(400);
            echo json_encode(['error' => 'Channel name required']);
            return;
        }

        $success = $this->asterisk->hangupCall($channel);
        echo json_encode(['success' => $success]);
    }

    /**
     * Generate dialplan for download
     */
    public function generateDialplan()
    {
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="aps_outbound_calls.conf"');
        echo $this->asterisk->generateDialplan();
    }
}
