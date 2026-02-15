<?php

namespace App\Services\Legacy;

/**
 * APS Dream Home - AI Integration with OpenRouter
 * Advanced AI features for property valuation, customer support, and business intelligence
 */

// Prevent direct access
if (!defined('BASE_URL')) {
    // Define BASE_URL if not already defined
    define('BASE_URL', 'https://apsdreamhomes.com');
}

class AIDreamHome
{
    private $api_key;
    private $base_url = 'https://openrouter.ai/api/v1';
    private $model;

    public function __construct($api_key = null, $model = null)
    {
        // Use configuration values if not provided
        global $config;

        $this->api_key = $api_key ?: (getenv('OPENROUTER_API_KEY') ?: ($config['ai']['api_key'] ?? ''));
        $this->model = $model ?: (getenv('OPENROUTER_MODEL') ?: ($config['ai']['model'] ?? 'qwen/qwen3-coder:free'));
        $this->base_url = 'https://openrouter.ai/api/v1';

        // Set timezone for consistent timestamps
        date_default_timezone_set('Asia/Kolkata');
    }

    /**
     * Generate AI-powered property description
     */
    public function generatePropertyDescription($property_data)
    {
        $prompt = "Create an engaging, professional property description for:
        Type: {$property_data['type']}
        Location: {$property_data['location']}
        Price: {$property_data['price']}
        Bedrooms: {$property_data['bedrooms']}
        Area: {$property_data['area']} sq ft
        Features: " . implode(', ', $property_data['features']) . "

        Make it compelling for potential buyers, highlight key features, and emphasize the lifestyle benefits.";

        return $this->callOpenRouter($prompt, 200);
    }

    /**
     * AI-powered property valuation
     */
    public function estimatePropertyValue($property_data)
    {
        $prompt = "Analyze this property and provide a realistic market value estimate in INR:
        Type: {$property_data['type']}
        Location: {$property_data['location']}
        Area: {$property_data['area']} sq ft
        Bedrooms: {$property_data['bedrooms']}
        Bathrooms: {$property_data['bathrooms']}
        Year Built: {$property_data['year_built']}
        Condition: {$property_data['condition']}
        Nearby Amenities: " . implode(', ', $property_data['amenities']) . "

        Provide a price range with justification based on current market trends.";

        return $this->callOpenRouter($prompt, 300);
    }

    /**
     * Customer support chatbot responses
     */
    public function generateChatbotResponse($user_query, $context = [])
    {
        $context_str = '';
        if (!empty($context)) {
            $context_str = "Context: " . implode(', ', $context) . "\n";
        }

        $prompt = $context_str . "You are a helpful real estate assistant for APS Dream Home.
        User Question: {$user_query}

        Provide a helpful, accurate response. If the question is about properties, pricing, or services, be informative and encouraging.
        Keep responses professional but friendly.";

        return $this->callOpenRouter($prompt, 150);
    }

    /**
     * Generate property recommendations
     */
    public function getPropertyRecommendations($user_preferences)
    {
        $prompt = "Based on these user preferences, recommend 3-5 properties:
        Budget: {$user_preferences['budget']}
        Property Type: {$user_preferences['type']}
        Location Preference: {$user_preferences['location']}
        Must-have Features: " . implode(', ', $user_preferences['features']) . "
        Lifestyle: {$user_preferences['lifestyle']}

        Provide specific recommendations with reasons why each property matches their needs.";

        return $this->callOpenRouter($prompt, 250);
    }

    /**
     * Market analysis and trends
     */
    public function analyzeMarketTrends($location, $property_type)
    {
        $prompt = "Provide a market analysis for {$property_type} properties in {$location}:
        - Current average prices
        - Price trends over the last year
        - Demand vs supply analysis
        - Future growth predictions
        - Investment potential
        - Key factors affecting prices

        Base your analysis on current market data and trends.";

        return $this->callOpenRouter($prompt, 300);
    }

    /**
     * Generate investment insights
     */
    public function getInvestmentInsights($property_data)
    {
        $prompt = "Analyze this property as an investment opportunity:
        Location: {$property_data['location']}
        Property Type: {$property_data['type']}
        Purchase Price: {$property_data['price']}
        Market Value: {$property_data['market_value']}
        Rental Potential: {$property_data['rental_potential']}

        Provide:
        1. ROI analysis
        2. Risk assessment
        3. Market comparison
        4. Investment recommendation
        5. Timeline for returns";

        return $this->callOpenRouter($prompt, 250);
    }

    /**
     * Generate content for marketing
     */
    public function generateMarketingContent($content_type, $property_data = [])
    {
        switch ($content_type) {
            case 'social_media':
                $prompt = "Create an engaging social media post for this property:
                {$property_data['title']} - {$property_data['location']}
                Price: {$property_data['price']}
                Key features: " . implode(', ', $property_data['features']) . "

                Make it compelling with emojis and hashtags.";
                break;

            case 'email_campaign':
                $prompt = "Write a professional email campaign for {$property_data['title']}:
                Highlight the best features and create urgency for potential buyers.";
                break;

            case 'blog_post':
                $prompt = "Write a blog post about real estate investment opportunities in {$property_data['location']}.";
                break;

            default:
                $prompt = "Generate marketing content for real estate promotion.";
        }

        return $this->callOpenRouter($prompt, 200);
    }

    /**
     * Call OpenRouter API
     */
    private function callOpenRouter($prompt, $max_tokens = 150)
    {
        $data = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert real estate AI assistant for APS Dream Home. Provide accurate, helpful, and professional responses.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => $max_tokens,
            'temperature' => 0.7
        ];

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->api_key,
            'HTTP-Referer: https://apsdreamhomes.com',
            'X-Title: APS Dream Home AI Assistant'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base_url . '/chat/completions');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        if ($error) {
            return ['error' => 'AI Service Error: ' . $error];
        }

        if ($http_code !== 200) {
            return ['error' => 'AI Service Error: HTTP ' . $http_code];
        }

        $result = json_decode($response, true);

        if (isset($result['error'])) {
            return ['error' => 'AI API Error: ' . $result['error']['message']];
        }

        if (isset($result['choices'][0]['message']['content'])) {
            return ['success' => trim($result['choices'][0]['message']['content'])];
        }

        return ['error' => 'Unexpected AI response format'];
    }

    /**
     * Market trends analysis (Alias for analyzeMarketTrends with default type)
     * 
     * @param string $location
     * @return array|string
     */
    public function getMarketTrends($location)
    {
        return $this->analyzeMarketTrends($location, 'Residential');
    }

    /**
     * Property insights (Alias for getInvestmentInsights by fetching property data)
     * 
     * @param int $property_id
     * @return array|string
     */
    public function getPropertyInsights($property_id)
    {
        $db = \App\Core\App::database();

        $property = $db->fetch("SELECT * FROM properties WHERE id = :id", ['id' => $property_id]);

        if (!$property) {
            return ['error' => 'Property not found'];
        }

        // Prepare data for investment insights
        $property_data = [
            'location' => $property['location'] ?? $property['city'] ?? 'Unknown',
            'type' => $property['property_type'] ?? 'Property',
            'price' => $property['price'],
            'market_value' => $property['price'], // Use price as market value if not specified
            'rental_potential' => $property['price'] * 0.04 // Estimate 4% annual rental yield
        ];

        return $this->getInvestmentInsights($property_data);
    }

    /**
     * Log AI interactions for analytics
     */
    private function logInteraction($type, $input, $output)
    {
        $log_file = __DIR__ . '/../logs/ai_interactions.log';
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => $type,
            'input_length' => strlen($input),
            'output_length' => strlen($output),
            'model' => $this->model
        ];

        file_put_contents($log_file, json_encode($log_entry) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    /**
     * Get AI usage statistics
     */
    public function getUsageStats()
    {
        $log_file = __DIR__ . '/../logs/ai_interactions.log';

        if (!file_exists($log_file)) {
            return ['total_requests' => 0, 'total_input_tokens' => 0, 'total_output_tokens' => 0];
        }

        $logs = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $stats = ['total_requests' => count($logs), 'total_input_tokens' => 0, 'total_output_tokens' => 0];

        foreach ($logs as $log) {
            $data = json_decode($log, true);
            if ($data) {
                $stats['total_input_tokens'] += $data['input_length'];
                $stats['total_output_tokens'] += $data['output_length'];
            }
        }

        return $stats;
    }
}

// Utility functions for easy AI integration

/**
 * Quick property description generator
 */
function generateAIPropertyDescription($property_data)
{
    global $config;
    $ai = new AIDreamHome(
        $config['ai']['api_key'] ?? null,
        $config['ai']['model'] ?? null
    );
    $result = $ai->generatePropertyDescription($property_data);
    return $result['success'] ?? $result['error'] ?? 'AI service unavailable';
}

/**
 * Quick property valuation
 */
function getAIPropertyValuation($property_data)
{
    global $config;
    $ai = new AIDreamHome(
        $config['ai']['api_key'] ?? null,
        $config['ai']['model'] ?? null
    );
    $result = $ai->estimatePropertyValue($property_data);
    return $result['success'] ?? $result['error'] ?? 'AI service unavailable';
}

/**
 * AI chatbot response
 */
function getAIChatbotResponse($user_query, $context = [])
{
    global $config;
    $ai = new AIDreamHome(
        $config['ai']['api_key'] ?? null,
        $config['ai']['model'] ?? null
    );
    $result = $ai->generateChatbotResponse($user_query, $context);
    return $result['success'] ?? $result['error'] ?? 'AI service unavailable';
}

/**
 * Property recommendations
 */
function getAIPropertyRecommendations($user_preferences)
{
    global $config;
    $ai = new AIDreamHome(
        $config['ai']['api_key'] ?? null,
        $config['ai']['model'] ?? null
    );
    $result = $ai->getPropertyRecommendations($user_preferences);
    return $result['success'] ?? $result['error'] ?? 'AI service unavailable';
}
