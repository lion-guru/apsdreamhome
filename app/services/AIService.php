<?php

namespace App\Services;

use Exception;
use App\Core\AI\OpenRouterClient;

class AIService
{
    private $apiKey;
    private $model = 'gpt-3.5-turbo';
    private $openRouterClient;

    public function __construct()
    {
        // Try to load OpenAI config
        $openaiConfigPath = __DIR__ . '/../../includes/config/openai.php';
        if (file_exists($openaiConfigPath)) {
            $config = require $openaiConfigPath;
            $this->apiKey = $config['api_key'] ?? '';
        }

        $this->openRouterClient = new OpenRouterClient();
    }

    /**
     * Generate role-based suggestions
     */
    public function generateRoleBasedSuggestions(int $userId, string $role, array $context = [])
    {
        $prompts = [
            'admin' => "As an administrator of APS Dream Homes, focus on system health, pending KYC approvals, and platform growth metrics.",
            'associate' => "As an associate, focus on lead generation strategies, upcoming site visits, and commission maximization.",
            'builder' => "As a builder, focus on project completion timelines, material sourcing, and new property listings.",
            'agent' => "As a real estate agent, focus on client relationship management, property valuation, and closing deals.",
            'customer' => "As a customer, focus on property investment opportunities, home loan options, and market trends."
        ];

        $rolePrompt = $prompts[$role] ?? "Provide general real estate platform suggestions.";
        
        // Try OpenRouter first if available
        $result = $this->openRouterClient->chat($rolePrompt, "Provide 3 suggestions for user ID {$userId}. Context: " . json_encode($context));
        
        if ($result['ok']) {
            return explode("\n", trim($result['content']));
        } else {
            // Log OpenRouter error if needed
            error_log("OpenRouter error in AIService: " . $result['error']);
        }

        // Try Gemini if OpenRouter fails
        $geminiService = new \App\Services\GeminiService();
        $geminiPrompt = $rolePrompt . "\nProvide 3 suggestions for user ID {$userId}. Context: " . json_encode($context);
        $geminiResponse = $geminiService->generateText($geminiPrompt);
        
        if ($geminiResponse) {
            return explode("\n", trim($geminiResponse));
        }

        // Fallback to OpenAI if configured
        if (!empty($this->apiKey) && $this->apiKey !== 'YOUR_OPENAI_API_KEY_HERE') {
            try {
                $response = $this->getChatCompletion("Provide 3 suggestions for user ID {$userId}. Context: " . json_encode($context), $rolePrompt);
                if ($response) {
                    return explode("\n", trim($response));
                }
            } catch (Exception $e) {
                // Fallback to mock
            }
        }

        return $this->getMockSuggestions($role);
    }

    private function getMockSuggestions(string $role)
    {
        $mocks = [
            'admin' => [
                "Review 15 pending KYC applications for Gorakhpur region.",
                "System Audit: 3 database tables need optimization.",
                "Analytics: Investor activity increased by 12% this week."
            ],
            'associate' => [
                "Schedule a follow-up with the 5 leads from 'Suryoday Colony'.",
                "New Commission Tier: Complete 2 more sales to hit Platinum level.",
                "Tip: Shared your profile on WhatsApp to increase visibility."
            ],
            'builder' => [
                "Update RERA status for 'Raghunath Nagri' Phase 2.",
                "Weekly Progress: Tower A is 80% complete.",
                "Inventory: 5 Commercial units listed today."
            ],
            'customer' => [
                "Investment Opportunity: New plots starting at 15L in Kanpur.",
                "Market Trend: Property prices in Gorakhpur expected to rise by 5%.",
                "Tip: Complete your KYC to unlock premium property tours."
            ]
        ];
        return $mocks[$role] ?? ["Explore latest properties in your preferred locations."];
    }

    /**
     * Get a chat completion from OpenAI
     *
     * @param string $message
     * @param string $systemPrompt
     * @return string|null
     */
    public function getChatCompletion(string $message, string $systemPrompt = 'You are APS Dream Homes AI Assistant. Help with real estate, bookings, and site info.')
    {
        // Try OpenRouter first
        $result = $this->openRouterClient->chat($systemPrompt, $message);
        if ($result['ok']) {
            return $result['content'];
        }

        // Try Gemini
        $geminiService = new \App\Services\GeminiService();
        $geminiResponse = $geminiService->generateText($systemPrompt . "\n" . $message);
        if ($geminiResponse) {
            return $geminiResponse;
        }

        // Fallback to OpenAI if configured
        if (!empty($this->apiKey) && $this->apiKey !== 'YOUR_OPENAI_API_KEY_HERE') {

        $data = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $message]
            ],
            'max_tokens' => 256,
            'temperature' => 0.7
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $result = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return null;
        }

        $resp = json_decode($result, true);

        if (isset($resp['choices'][0]['message']['content'])) {
            return trim($resp['choices'][0]['message']['content']);
        }

        return null;
    }
}
