<?php

namespace App\Services;

use App\Core\Database\Database;

/**
 * Gemini AI Service - Advanced AI Integration for APS Dream Home
 * 
 * Features:
 * - AI-powered property recommendations
 * - Customer support chatbot
 * - Content generation
 * - Data analysis and insights
 * - Automated responses
 */
class GeminiAIService
{
    private $db;
    private $apiKey;
    private $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->apiKey = $this->getApiKey();
    }
    
    /**
     * Get API Key from database or config
     */
    private function getApiKey(): string
    {
        try {
            // Try to get from database first
            $result = $this->db->fetch(
                'SELECT api_key FROM ai_settings WHERE service = ? AND is_active = 1',
                ['gemini']
            );
            
            if ($result && !empty($result['api_key'])) {
                return $result['api_key'];
            }
            
            // Fallback to environment or config
            return $_ENV['GEMINI_API_KEY'] ?? 'AIzaSyCkVFFk4xU7cawmvg14HUEugmSrLt-aW5Y';
            
        } catch (\Exception $e) {
            // Log error and return default
            error_log('Gemini API Key Error: ' . $e->getMessage());
            return 'AIzaSyCkVFFk4xU7cawmvg14HUEugmSrLt-aW5Y';
        }
    }
    
    /**
     * Update API Key in database
     */
    public function updateApiKey(string $newKey): bool
    {
        try {
            // Check if record exists
            $existing = $this->db->fetch(
                'SELECT id FROM ai_settings WHERE service = ?',
                ['gemini']
            );
            
            if ($existing) {
                // Update existing
                $this->db->execute(
                    'UPDATE ai_settings SET api_key = ?, updated_at = NOW() WHERE service = ?',
                    [$newKey, 'gemini']
                );
            } else {
                // Insert new
                $this->db->execute(
                    'INSERT INTO ai_settings (service, api_key, is_active, created_at, updated_at) VALUES (?, ?, 1, NOW(), NOW())',
                    ['gemini', $newKey]
                );
            }
            
            // Clear cached key
            $this->apiKey = $newKey;
            
            return true;
        } catch (\Exception $e) {
            error_log('Update API Key Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate content using Gemini AI
     */
    public function generateContent(string $prompt, array $options = []): array
    {
        $url = $this->baseUrl . '/gemini-1.5-flash:generateContent?key=' . $this->apiKey;
        
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ];
        
        // Add optional parameters
        if (isset($options['temperature'])) {
            $data['generationConfig'] = [
                'temperature' => $options['temperature'],
                'topK' => $options['topK'] ?? 40,
                'topP' => $options['topP'] ?? 0.95,
                'maxOutputTokens' => $options['maxTokens'] ?? 8192,
            ];
        }
        
        return $this->makeRequest($url, $data);
    }
    
    /**
     * Chat with Gemini AI
     */
    public function chat(array $messages, array $options = []): array
    {
        $url = $this->baseUrl . '/gemini-1.5-flash:generateContent?key=' . $this->apiKey;
        
        $contents = [];
        foreach ($messages as $message) {
            $contents[] = [
                'parts' => [
                    ['text' => $message['content']]
                ],
                'role' => $message['role'] ?? 'user'
            ];
        }
        
        $data = [
            'contents' => $contents
        ];
        
        if (isset($options['temperature'])) {
            $data['generationConfig'] = [
                'temperature' => $options['temperature'],
                'topK' => $options['topK'] ?? 40,
                'topP' => $options['topP'] ?? 0.95,
                'maxOutputTokens' => $options['maxTokens'] ?? 8192,
            ];
        }
        
        return $this->makeRequest($url, $data);
    }
    
    /**
     * Generate property recommendations
     */
    public function generatePropertyRecommendations(string $userPreferences): array
    {
        $prompt = "As a real estate AI assistant, analyze these user preferences and provide personalized property recommendations:

User Preferences: {$userPreferences}

Please provide:
1. Top 3 property recommendations
2. Reasoning for each recommendation
3. Price range suggestions
4. Location preferences analysis
5. Additional tips for the user

Format the response in a structured, professional manner.";

        return $this->generateContent($prompt, [
            'temperature' => 0.7,
            'maxTokens' => 2048
        ]);
    }
    
    /**
     * Generate property description
     */
    public function generatePropertyDescription(array $propertyDetails): array
    {
        $prompt = "Generate a compelling property description for this real estate listing:

Property Details:
" . json_encode($propertyDetails, JSON_PRETTY_PRINT) . "

Requirements:
- Professional and engaging tone
- Highlight key features
- Mention location benefits
- Include lifestyle aspects
- Keep it under 300 words
- Make it appealing to potential buyers";

        return $this->generateContent($prompt, [
            'temperature' => 0.8,
            'maxTokens' => 1024
        ]);
    }
    
    /**
     * Customer support chatbot
     */
    public function customerSupport(string $userQuery, string $context = ''): array
    {
        $prompt = "You are a helpful customer support assistant for APS Dream Home, a premium real estate company.

Context: {$context}

User Query: {$userQuery}

Provide helpful, professional, and friendly responses about:
- Property listings and availability
- Company services
- General real estate questions
- Contact information
- Appointment scheduling

If you don't have specific information, guide them to contact the team.";

        return $this->generateContent($prompt, [
            'temperature' => 0.6,
            'maxTokens' => 1024
        ]);
    }
    
    /**
     * Analyze market trends
     */
    public function analyzeMarketTrends(string $location, string $propertyType = ''): array
    {
        $prompt = "As a real estate market analyst, provide insights for:

Location: {$location}
Property Type: {$propertyType}

Include:
1. Current market trends
2. Price predictions
3. Investment potential
4. Market challenges
5. Recommendations for buyers/sellers

Provide data-driven, professional analysis.";

        return $this->generateContent($prompt, [
            'temperature' => 0.3,
            'maxTokens' => 2048
        ]);
    }
    
    /**
     * Generate social media content
     */
    public function generateSocialMediaContent(string $topic, string $platform = 'general'): array
    {
        $prompt = "Generate engaging social media content for APS Dream Home:

Topic: {$topic}
Platform: {$platform}

Requirements:
- Professional yet engaging tone
- Include relevant hashtags
- Call to action
- Brand voice consistency
- Platform-appropriate formatting";

        return $this->generateContent($prompt, [
            'temperature' => 0.8,
            'maxTokens' => 512
        ]);
    }
    
    /**
     * Make HTTP request to Gemini API
     */
    private function makeRequest(string $url, array $data): array
    {
        try {
            $ch = curl_init($url);
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            curl_close($ch);
            
            // Log the request
            $this->logApiRequest($url, $data, $response, $httpCode);
            
            if ($httpCode === 200) {
                return [
                    'success' => true,
                    'data' => json_decode($response, true),
                    'status_code' => $httpCode
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'HTTP Error: ' . $httpCode,
                    'response' => $response,
                    'status_code' => $httpCode
                ];
            }
            
        } catch (\Exception $e) {
            error_log('Gemini API Request Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status_code' => 500
            ];
        }
    }
    
    /**
     * Log API requests for monitoring
     */
    private function logApiRequest(string $url, array $data, string $response, int $httpCode): void
    {
        try {
            $this->db->execute(
                'INSERT INTO ai_api_logs (service, endpoint, request_data, response_data, status_code, created_at) VALUES (?, ?, ?, ?, ?, NOW())',
                [
                    'gemini',
                    $url,
                    json_encode($data),
                    substr($response, 0, 1000), // Limit response size
                    $httpCode
                ]
            );
        } catch (\Exception $e) {
            error_log('Failed to log API request: ' . $e->getMessage());
        }
    }
    
    /**
     * Test API connection
     */
    public function testConnection(): array
    {
        return $this->generateContent('Hello, is this API active? Test message.');
    }
    
    /**
     * Get API usage statistics
     */
    public function getUsageStats(): array
    {
        try {
            $today = $this->db->fetchAll(
                'SELECT COUNT(*) as requests_today FROM ai_api_logs WHERE service = ? AND DATE(created_at) = CURDATE()',
                ['gemini']
            );
            
            $thisMonth = $this->db->fetchAll(
                'SELECT COUNT(*) as requests_this_month FROM ai_api_logs WHERE service = ? AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())',
                ['gemini']
            );
            
            $errors = $this->db->fetchAll(
                'SELECT COUNT(*) as error_count FROM ai_api_logs WHERE service = ? AND status_code != 200',
                ['gemini']
            );
            
            return [
                'success' => true,
                'requests_today' => $today[0]['requests_today'] ?? 0,
                'requests_this_month' => $thisMonth[0]['requests_this_month'] ?? 0,
                'error_count' => $errors[0]['error_count'] ?? 0
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}