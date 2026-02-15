<?php

namespace App\Services\Legacy;
/**
 * OpenRouter Client for AI Integration
 * Provides access to various AI models via OpenRouter API
 */

class OpenRouterClient {
    private $apiKey;
    private $baseUrl;
    private $model;

    public function __construct($model = 'anthropic/claude-3.5-haiku', $apiKey = null) {
        $this->apiKey = $apiKey ?: getenv('OPENROUTER_API_KEY');
        $this->baseUrl = 'https://openrouter.ai/api/v1';
        $this->model = $model;
    }

    /**
     * Generate text using OpenRouter
     * @param string $prompt The input prompt
     * @param array $options Additional options
     * @return string|null Generated text or null on error
     */
    public function generate($prompt, $options = []) {
        $data = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens' => $options['max_tokens'] ?? 1000,
            'stream' => false
        ];

        $response = $this->makeRequest('/chat/completions', $data);

        if ($response && isset($response['choices'][0]['message']['content'])) {
            return $response['choices'][0]['message']['content'];
        }

        return null;
    }

    /**
     * Generate code using OpenRouter
     * @param string $task Description of coding task
     * @param string $language Programming language
     * @param array $context Additional context
     * @return string|null Generated code or null on error
     */
    public function generateCode($task, $language = 'php', $context = []) {
        $prompt = "You are an expert {$language} developer. Generate clean, efficient, and well-documented code for the following task:\n\n";
        $prompt .= "Task: {$task}\n";
        $prompt .= "Language: {$language}\n";

        if (!empty($context)) {
            $prompt .= "\nContext:\n";
            foreach ($context as $key => $value) {
                $prompt .= "- {$key}: {$value}\n";
            }
        }

        $prompt .= "\nPlease provide only the code without explanation, wrapped in appropriate code blocks if needed.";

        return $this->generate($prompt, [
            'temperature' => 0.3, // Lower temperature for code generation
            'max_tokens' => 2000
        ]);
    }

    /**
     * Chat with the model
     * @param array $messages Array of message objects
     * @param array $options Additional options
     * @return string|null Response or null on error
     */
    public function chat($messages, $options = []) {
        $data = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens' => $options['max_tokens'] ?? 1000,
            'stream' => false
        ];

        $response = $this->makeRequest('/chat/completions', $data);

        if ($response && isset($response['choices'][0]['message']['content'])) {
            return $response['choices'][0]['message']['content'];
        }

        return null;
    }

    /**
     * Check if OpenRouter is available
     * @return bool
     */
    public function isAvailable() {
        $response = $this->makeRequest('/models', []);
        return $response !== null;
    }

    /**
     * List available models
     * @return array|null
     */
    public function listModels() {
        $response = $this->makeRequest('/models', []);
        return $response ? $response['data'] : null;
    }

    /**
     * Make HTTP request to OpenRouter API
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @return array|null Decoded JSON response or null on error
     */
    private function makeRequest($endpoint, $data) {
        $url = $this->baseUrl . $endpoint;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, !empty($data));
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
            'HTTP-Referer: https://apsdreamhome.com',
            'X-Title: APS Dream Home AI Assistant'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            return json_decode($response, true);
        }

        return null;
    }
}

/**
 * Helper function to get OpenRouter client instance
 * @param string $model Model name (default: free tier model)
 * @return OpenRouterClient
 */
function getOpenRouterClient($model = 'anthropic/claude-3.5-haiku') {
    return new OpenRouterClient($model);
}

/**
 * Free tier models available on OpenRouter
 */
const OPENROUTER_FREE_MODELS = [
    'anthropic/claude-3.5-haiku',     // Good for coding
    'meta-llama/llama-3.2-3b-instruct', // Fast and free
    'microsoft/wizardlm-2-8x22b',    // Good for code
    'google/gemma-7b-it',            // Google's free model
    'mistralai/mistral-7b-instruct', // Versatile
];
