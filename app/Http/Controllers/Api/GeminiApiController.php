<?php

namespace App\Http\Controllers\Api;

use App\Services\GeminiAIService;
use App\Http\Controllers\BaseController;

/**
 * Gemini AI API Controller - Public API endpoints
 */
class GeminiApiController extends BaseController
{
    private $geminiService;
    
    public function __construct()
    {
        parent::__construct();
        $this->geminiService = new GeminiAIService();
    }
    
    /**
     * Chat with Gemini AI - Public API
     */
    public function chat()
    {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $message = $input['message'] ?? '';
        
        if (empty($message)) {
            echo json_encode([
                'success' => false,
                'error' => 'Message is required'
            ]);
            return;
        }
        
        // Simple chat context
        $messages = [
            ['role' => 'user', 'content' => $message]
        ];
        
        $result = $this->geminiService->chat($messages);
        
        echo json_encode($result);
    }
    
    /**
     * Generate content - Public API
     */
    public function generateContent()
    {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $prompt = $input['prompt'] ?? '';
        $options = $input['options'] ?? [];
        
        if (empty($prompt)) {
            echo json_encode([
                'success' => false,
                'error' => 'Prompt is required'
            ]);
            return;
        }
        
        $result = $this->geminiService->generateContent($prompt, $options);
        
        echo json_encode($result);
    }
    
    /**
     * Property recommendations - Public API
     */
    public function propertyRecommendations()
    {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $preferences = $input['preferences'] ?? '';
        
        if (empty($preferences)) {
            echo json_encode([
                'success' => false,
                'error' => 'Preferences are required'
            ]);
            return;
        }
        
        $result = $this->geminiService->generatePropertyRecommendations($preferences);
        
        echo json_encode($result);
    }
    
    /**
     * Customer support - Public API
     */
    public function customerSupport()
    {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $query = $input['query'] ?? '';
        $context = $input['context'] ?? '';
        
        if (empty($query)) {
            echo json_encode([
                'success' => false,
                'error' => 'Query is required'
            ]);
            return;
        }
        
        $result = $this->geminiService->customerSupport($query, $context);
        
        echo json_encode($result);
    }
    
    /**
     * Market analysis - Public API
     */
    public function marketAnalysis()
    {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $location = $input['location'] ?? '';
        $propertyType = $input['property_type'] ?? '';
        
        if (empty($location)) {
            echo json_encode([
                'success' => false,
                'error' => 'Location is required'
            ]);
            return;
        }
        
        $result = $this->geminiService->analyzeMarketTrends($location, $propertyType);
        
        echo json_encode($result);
    }
    
    /**
     * Social media content - Public API
     */
    public function socialMediaContent()
    {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $topic = $input['topic'] ?? '';
        $platform = $input['platform'] ?? 'general';
        
        if (empty($topic)) {
            echo json_encode([
                'success' => false,
                'error' => 'Topic is required'
            ]);
            return;
        }
        
        $result = $this->geminiService->generateSocialMediaContent($topic, $platform);
        
        echo json_encode($result);
    }
    
    /**
     * Test API connection - Public API
     */
    public function testConnection()
    {
        header('Content-Type: application/json');
        
        $result = $this->geminiService->testConnection();
        
        echo json_encode($result);
    }
    
    /**
     * Get API status - Public API
     */
    public function getStatus()
    {
        header('Content-Type: application/json');
        
        $stats = $this->geminiService->getUsageStats();
        
        echo json_encode([
            'success' => true,
            'service' => 'Gemini AI',
            'status' => 'active',
            'version' => '1.0.0',
            'endpoints' => [
                '/api/gemini/chat' => 'POST - Chat with AI',
                '/api/gemini/generate' => 'POST - Generate content',
                '/api/gemini/recommendations' => 'POST - Property recommendations',
                '/api/gemini/support' => 'POST - Customer support',
                '/api/gemini/market-analysis' => 'POST - Market analysis',
                '/api/gemini/social-media' => 'POST - Social media content',
                '/api/gemini/test' => 'GET - Test connection',
                '/api/gemini/status' => 'GET - Service status'
            ],
            'statistics' => $stats
        ]);
    }
}