<?php
// TODO: Consider async file operations for better performance

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\GeminiAIService;

/**
 * AI Settings Controller - Manage Gemini AI Integration
 */
class AISettingsController extends AdminController
{
    private $geminiService;
    
    public function __construct()
    {
        parent::__construct();
        $this->geminiService = new GeminiAIService();
    }
    
    /**
     * Display AI settings page
     */
    public function index()
    {
        $this->data['page_title'] = 'AI Provider Settings';

        $config = [
            'gemini_key' => '', 'gemini_model' => 'gemini-2.5-flash',
            'groq_key' => '', 'openrouter_key' => '',
            'ollama_url' => 'http://localhost:11434', 'ollama_model' => 'llama3.1:8b',
            'tts_engine' => 'google', 'stt_engine' => 'groq', 'is_active' => 1,
        ];
        try {
            $row = $this->db->fetch("SELECT api_key, is_active, settings, groq_api_key, openrouter_api_key, ollama_url, ollama_model FROM ai_settings WHERE id = 1");
            if ($row) {
                $cfg = json_decode((string)($row['settings'] ?? '{}'), true) ?: [];
                $config['gemini_key'] = $row['api_key'];
                $config['is_active'] = (int)($row['is_active'] ?? 1);
                $config['groq_key'] = $row['groq_api_key'];
                $config['openrouter_key'] = $row['openrouter_api_key'];
                $config['ollama_url'] = $row['ollama_url'] ?: $config['ollama_url'];
                $config['ollama_model'] = $row['ollama_model'] ?: $config['ollama_model'];
                $config['gemini_model'] = $cfg['model'] ?? $config['gemini_model'];
                $ttsCfg = $cfg['tts_engine'] ?? 'google';
                $config['tts_engine'] = in_array($ttsCfg, ['google', 'groq', 'espeak', 'ollama'], true) ? $ttsCfg : 'google';
                $config['stt_engine'] = ($cfg['stt_engine'] ?? 'groq') === 'whisper' ? 'whisper' : 'groq';
            }
        } catch (\Throwable $e) {
            error_log("AISettings index config load failed: " . $e->getMessage());
        }
        // Masked previews only — full keys never re-sent to the browser
        $mask = static function (?string $k): string {
            $k = trim((string)$k);
            return $k === '' ? '' : substr($k, 0, 4) . '••••••••' . substr($k, -4);
        };
        $this->data['masked'] = [
            'gemini' => $mask($config['gemini_key']),
            'groq' => $mask($config['groq_key']),
            'openrouter' => $mask($config['openrouter_key']),
        ];
        $this->data['has_keys'] = [
            'gemini' => trim($config['gemini_key']) !== '',
            'groq' => trim($config['groq_key']) !== '',
            'openrouter' => trim($config['openrouter_key']) !== '',
        ];
        unset($config['gemini_key'], $config['groq_key'], $config['openrouter_key']);
        $this->data['config'] = $config;

        // Usage stats by engine (ai_api_logs.engine_used)
        $this->data['usage'] = ['today' => 0, 'month' => 0, 'errors_30d' => 0, 'by_engine' => []];
        try {
            $this->data['usage']['today'] = (int)($this->db->fetch("SELECT COUNT(*) c FROM ai_api_logs WHERE created_at >= CURDATE()")['c'] ?? 0);
            $this->data['usage']['month'] = (int)($this->db->fetch("SELECT COUNT(*) c FROM ai_api_logs WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)")['c'] ?? 0);
            $this->data['usage']['errors_30d'] = (int)($this->db->fetch("SELECT COUNT(*) c FROM ai_api_logs WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND (status_code != 200 OR status_code IS NULL)")['c'] ?? 0);
        } catch (\Throwable $e) {
            error_log("AISettings index stats failed: " . $e->getMessage());
        }
        try {
            $this->data['usage']['by_engine'] = $this->db->fetchAll(
                "SELECT COALESCE(NULLIF(engine_used,''), service) engine, COUNT(*) calls,
                        ROUND(AVG(response_time_ms)) avg_ms,
                        SUM(CASE WHEN status_code != 200 OR status_code IS NULL THEN 1 ELSE 0 END) errors
                 FROM ai_api_logs
                 WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                 GROUP BY engine ORDER BY calls DESC LIMIT 10"
            );
        } catch (\Throwable $e) {
            error_log("AISettings index engine stats failed: " . $e->getMessage());
        }

        return $this->render('admin/ai_settings/index');
    }

    /**
     * Save multi-provider configuration (keys left blank keep current value)
     */
    public function saveConfig()
    {
        if (!$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $row = $this->db->fetch("SELECT api_key, groq_api_key, openrouter_api_key, settings FROM ai_settings WHERE id = 1");
            if (!$row) {
                $this->jsonResponse(['success' => false, 'message' => 'ai_settings row #1 missing'], 500);
                return;
            }
            $cfg = json_decode((string)($row['settings'] ?? '{}'), true) ?: [];

            $fields = [];
            $params = [];

            // Gemini key lives in the shared api_key column (service=gemini)
            $newGemini = trim($_POST['gemini_key'] ?? '');
            if ($newGemini !== '') {
                $fields[] = "api_key = ?";
                $params[] = $newGemini;
            }
            $newGroq = trim($_POST['groq_key'] ?? '');
            if ($newGroq !== '') {
                $fields[] = "groq_api_key = ?";
                $params[] = $newGroq;
            }
            $newOrr = trim($_POST['openrouter_key'] ?? '');
            if ($newOrr !== '') {
                $fields[] = "openrouter_api_key = ?";
                $params[] = $newOrr;
            }

            $ollamaUrl = trim($_POST['ollama_url'] ?? '');
            if ($ollamaUrl !== '' && filter_var($ollamaUrl, FILTER_VALIDATE_URL)) {
                $fields[] = "ollama_url = ?";
                $params[] = $ollamaUrl;
            }
            $ollamaModel = trim($_POST['ollama_model'] ?? '');
            if ($ollamaModel !== '') {
                $fields[] = "ollama_model = ?";
                $params[] = $ollamaModel;
            }

            // JSON settings blob: gemini model + voice engines
            if (!empty($_POST['gemini_model'])) {
                $cfg['model'] = trim($_POST['gemini_model']);
            }
            $tts = $_POST['tts_engine'] ?? 'google';
            if (in_array($tts, ['google', 'groq', 'espeak', 'ollama'], true)) {
                $cfg['tts_engine'] = $tts;
            }
            $stt = $_POST['stt_engine'] ?? 'groq';
            if (in_array($stt, ['groq', 'whisper'], true)) {
                $cfg['stt_engine'] = $stt;
            }
            $fields[] = "settings = ?";
            $params[] = json_encode($cfg);

            $params[] = 1;
            $sql = "UPDATE ai_settings SET " . implode(', ', $fields) . " WHERE id = ?";
            $this->db->execute($sql, $params);

            $this->jsonResponse(['success' => true, 'message' => 'Configuration saved. New values take effect immediately.']);
        } catch (\Throwable $e) {
            error_log("AISettings saveConfig failed: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Save failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Live connection test per provider
     */
    public function testProvider()
    {
        if (!$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $provider = $_POST['provider'] ?? '';
        try {
            $row = $this->db->fetch("SELECT api_key, settings, groq_api_key, openrouter_api_key, ollama_url FROM ai_settings WHERE id = 1");
            $cfg = json_decode((string)($row['settings'] ?? '{}'), true) ?: [];
        } catch (\Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Config load failed'], 500);
            return;
        }

        $start = microtime(true);
        $result = ['success' => false, 'message' => 'Unknown provider'];

        switch ($provider) {
            case 'gemini':
                $key = trim((string)($row['api_key'] ?? ''));
                if ($key === '') { $result['message'] = 'No Gemini key saved'; break; }
                $model = $cfg['model'] ?? 'gemini-2.5-flash';
                $ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . urlencode($key));
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_TIMEOUT => 20,
                    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                    CURLOPT_POSTFIELDS => json_encode(['contents' => [['parts' => [['text' => 'ping']]]]]),
                ]);
                $resp = curl_exec($ch);
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                $ok = $code === 200 && strpos((string)$resp, 'candidates') !== false;
                $result = ['success' => $ok, 'message' => $ok ? "Gemini {$model} replied" : "HTTP {$code}: " . substr((string)$resp, 0, 160)];
                break;

            case 'groq':
                $key = trim((string)($row['groq_api_key'] ?? ''));
                if ($key === '') { $result['message'] = 'No Groq key saved'; break; }
                $ch = curl_init('https://api.groq.com/openai/v1/models');
                curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_HTTPHEADER => ["Authorization: Bearer {$key}"]]);
                $resp = curl_exec($ch);
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                $data = json_decode((string)$resp, true);
                $count = is_array($data['data'] ?? null) ? count($data['data']) : 0;
                $ok = $code === 200 && $count > 0;
                $result = ['success' => $ok, 'message' => $ok ? "Groq OK — {$count} models available" : "HTTP {$code}: " . substr((string)$resp, 0, 160)];
                break;

            case 'openrouter':
                $key = trim((string)($row['openrouter_api_key'] ?? ''));
                if ($key === '') { $result['message'] = 'No OpenRouter key saved'; break; }
                $ch = curl_init('https://openrouter.ai/api/v1/auth/key');
                curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_HTTPHEADER => ["Authorization: Bearer {$key}"]]);
                $resp = curl_exec($ch);
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                $data = json_decode((string)$resp, true);
                $label = $data['data']['label'] ?? '';
                $limit = $data['data']['limit'] ?? null;
                $usage = $data['data']['usage'] ?? null;
                $ok = $code === 200 && !empty($data['data']);
                $detail = $ok ? ('Key valid' . ($label ? " ({$label})" : '') . ($usage !== null ? ' — used ' . $usage . ($limit !== null && $limit !== false ? '/' . $limit : '') : '')) : "HTTP {$code}: " . substr((string)$resp, 0, 160);
                $result = ['success' => $ok, 'message' => $detail];
                break;

            case 'ollama':
                $url = rtrim((string)($row['ollama_url'] ?? 'http://localhost:11434'), '/');
                $ch = curl_init($url . '/api/tags');
                curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 6]);
                $resp = curl_exec($ch);
                $err = curl_error($ch);
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                $data = json_decode((string)$resp, true);
                $models = array_map(static fn($m) => $m['name'] ?? '', $data['models'] ?? []);
                $ok = $code === 200;
                $msg = $ok ? (empty($models) ? 'Ollama reachable but NO models pulled — chain skips it' : 'Ollama OK — models: ' . implode(', ', array_slice($models, 0, 5))) : 'Unreachable: ' . ($err ?: "HTTP {$code}");
                $result = ['success' => $ok, 'message' => $msg];
                break;
        }

        $result['latency_ms'] = (int)round((microtime(true) - $start) * 1000);
        $this->jsonResponse($result);
    }

    /**
     * Update API key (legacy Gemini-only endpoint, kept for compatibility)
     */
    public function updateApiKey()
    {
        if (!$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }
        
        $apiKey = $_POST['api_key'] ?? '';
        
        if (empty($apiKey)) {
            $this->jsonResponse(['success' => false, 'message' => 'API key is required'], 400);
            return;
        }
        
        // Validate API key format (basic validation)
        if (!preg_match('/^AIza[A-Za-z0-9_-]{35}$/', $apiKey)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid API key format'], 400);
            return;
        }
        
        // Test the API key
        $testResult = $this->testApiKey($apiKey);
        
        if (!$testResult['success']) {
            $this->jsonResponse(['success' => false, 'message' => 'API key test failed: ' . $testResult['error']], 400);
            return;
        }
        
        // Update the key
        $updateResult = $this->geminiService->updateApiKey($apiKey);
        
        if ($updateResult) {
            $this->jsonResponse([
                'success' => true, 
                'message' => 'API key updated successfully',
                'test_result' => $testResult
            ]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to update API key'], 500);
        }
    }
    
    /**
     * Test API key
     */
    public function testApiKey(string $apiKey): array
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey;
        
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => 'Hello, is this API active? Test message.']
                    ]
                ]
            ]
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        if ($httpCode === 200) {
            $responseData = json_decode($response, true);
            return [
                'success' => true,
                'message' => 'API key is working correctly',
                'response' => $responseData
            ];
        } else {
            return [
                'success' => false,
                'error' => 'HTTP ' . $httpCode . ': ' . $response
            ];
        }
    }
    
    /**
     * Test current API connection
     */
    public function testConnection()
    {
        if (!$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }
        
        $result = $this->geminiService->testConnection();
        
        $this->jsonResponse($result);
    }
    
    /**
     * Generate sample content
     */
    public function generateSampleContent()
    {
        if (!$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }
        
        $type = $_POST['content_type'] ?? 'property_description';
        $prompt = $_POST['prompt'] ?? 'Generate a sample property description';
        
        switch ($type) {
            case 'property_description':
                $result = $this->geminiService->generatePropertyDescription([
                    'type' => 'Residential',
                    'location' => 'Gorakhpur',
                    'bedrooms' => 3,
                    'area' => '1500 sq.ft'
                ]);
                break;
                
            case 'social_media':
                $result = $this->geminiService->generateSocialMediaContent($prompt);
                break;
                
            case 'customer_support':
                $result = $this->geminiService->customerSupport($prompt);
                break;
                
            default:
                $result = $this->geminiService->generateContent($prompt);
        }
        
        $this->jsonResponse($result);
    }
    
    /**
     * Get usage analytics
     */
    public function getUsageAnalytics()
    {
        if (!$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }
        
        $stats = $this->geminiService->getUsageStats();
        
        try {
            // Get daily usage for last 30 days
            $dailyUsage = $this->db->fetchAll(
                'SELECT DATE(created_at) as date, COUNT(*) as requests, 
                        AVG(response_time_ms) as avg_response_time
                 FROM ai_api_logs 
                 WHERE service = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                 GROUP BY DATE(created_at)
                 ORDER BY date DESC',
                ['gemini']
            );
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        
        // Get error breakdown
        $errorBreakdown = $this->db->fetchAll(
            'SELECT status_code, COUNT(*) as count
             FROM ai_api_logs 
             WHERE service = ? AND status_code != 200
             GROUP BY status_code
             ORDER BY count DESC',
            ['gemini']
        );
        
        $this->jsonResponse([
            'success' => true,
            'stats' => $stats,
            'daily_usage' => $dailyUsage,
            'error_breakdown' => $errorBreakdown
        ]);
    }
    
    /**
     * Clear API logs
     */
    public function clearLogs()
    {
        if (!$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }
        
        $days = $_POST['days'] ?? 30;
        
        try {
            $result = $this->db->execute(
                'DELETE FROM ai_api_logs WHERE service = ? AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)',
                ['gemini', $days]
            );
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        
        if ($result) {
            $this->jsonResponse([
                'success' => true,
                'message' => "Logs older than {$days} days cleared successfully"
            ]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to clear logs'], 500);
        }
    }
    
    /**
     * Export usage report
     */
    public function exportUsageReport()
    {
        if (!$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }
        
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');
        
        try {
            $logs = $this->db->fetchAll(
                'SELECT * FROM ai_api_logs 
                 WHERE service = ? AND DATE(created_at) BETWEEN ? AND ?
                 ORDER BY created_at DESC',
                ['gemini', $startDate, $endDate]
            );
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        
        $csv = "Date,Endpoint,Status Code,Response Time (ms),User ID\n";
        
        foreach ($logs as $log) {
            $csv .= sprintf(
                "%s,%s,%d,%d,%s\n",
                $log['created_at'],
                str_replace(',', ';', $log['endpoint']),
                $log['status_code'],
                $log['response_time_ms'] ?? 0,
                $log['user_id'] ?? 'N/A'
            );
        }
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="gemini_usage_report_' . $startDate . '_to_' . $endDate . '.csv"');
        
        echo $csv;
        exit;
    }
    
    /**
     * Chat endpoint for AI interface
     */
    public function chat()
    {
        if (!$this->isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $message = $input['message'] ?? '';
        
        if (empty($message)) {
            $this->jsonResponse(['success' => false, 'message' => 'Message is required'], 400);
            return;
        }
        
        $messages = [
            ['role' => 'user', 'content' => $message]
        ];
        
        $result = $this->geminiService->chat($messages);
        
        $this->jsonResponse($result);
    }
}