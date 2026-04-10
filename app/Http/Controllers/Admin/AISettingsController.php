<?php

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
        $this->data['page_title'] = 'AI Settings';
        $this->data['stats'] = ['requests_today' => 0, 'requests_this_month' => 0, 'error_count' => 0];
        $this->data['recent_logs'] = [];
        
        return $this->render('admin/ai_settings/index');
    }
    
    /**
     * Update API key
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
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $apiKey;
        
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
        
        $result = $this->db->execute(
            'DELETE FROM ai_api_logs WHERE service = ? AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)',
            ['gemini', $days]
        );
        
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
        
        $logs = $this->db->fetchAll(
            'SELECT * FROM ai_api_logs 
             WHERE service = ? AND DATE(created_at) BETWEEN ? AND ?
             ORDER BY created_at DESC',
            ['gemini', $startDate, $endDate]
        );
        
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