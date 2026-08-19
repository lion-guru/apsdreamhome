<?php
/**
 * AI Model Detector Service
 * Detects available free/cheap AI models and suggests the best option
 */

namespace App\Services;

class AIModelDetector
{
    private $db;
    
    // Free models list
    private $freeModels = [
        'openrouter' => [
            'name' => 'OpenRouter',
            'models' => [
                'qwen/qwen3-coder:free' => ['name' => 'Qwen Coder (Free)', 'context' => '32K', 'type' => 'code'],
                'deepseek/deepseek-chat-v3:free' => ['name' => 'DeepSeek Chat V3 (Free)', 'context' => '64K', 'type' => 'chat'],
                'mistralai/mistral-nemo:free' => ['name' => 'Mistral Nemo (Free)', 'context' => '128K', 'type' => 'chat'],
                'anthropic/claude-3-haiku:free' => ['name' => 'Claude 3 Haiku (Free)', 'context' => '200K', 'type' => 'chat'],
                'google/gemini-2.0-flash-thinking-exp:free' => ['name' => 'Gemini Flash Thinking (Free)', 'context' => '1M', 'type' => 'thinking'],
                'openai/chatgpt-4o-latest:free' => ['name' => 'GPT-4o (Free)', 'context' => '128K', 'type' => 'chat'],
            ]
        ],
        'groq' => [
            'name' => 'Groq',
            'models' => [
                'llama-3.1-8b-instant' => ['name' => 'Llama 3.1 8B (Free)', 'context' => '8K', 'type' => 'chat'],
                'mixtral-8x7b-32768' => ['name' => 'Mixtral 8x7B (Free)', 'context' => '32K', 'type' => 'chat'],
                'gemma2-9b-it' => ['name' => 'Gemma 2 9B (Free)', 'context' => '8K', 'type' => 'chat'],
            ]
        ],
        'cohere' => [
            'name' => 'Cohere',
            'models' => [
                'command-r-plus' => ['name' => 'Command R+ (Free Tier)', 'context' => '128K', 'type' => 'chat'],
                'command-r' => ['name' => 'Command R (Free Tier)', 'context' => '128K', 'type' => 'chat'],
            ]
        ],
        'huggingface' => [
            'name' => 'HuggingFace',
            'models' => [
                'mistralai/Mistral-7B-Instruct-v0.3' => ['name' => 'Mistral 7B (Free Endpoint)', 'context' => '8K', 'type' => 'chat'],
                'meta-llama/Llama-3-8B-Instruct' => ['name' => 'Llama 3 8B (Free Endpoint)', 'context' => '8K', 'type' => 'chat'],
            ]
        ]
    ];

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    /**
     * Get all available AI models from database keys
     */
    public function getAvailableModels()
    {
        $keys = $this->db->query("SELECT * FROM api_keys WHERE is_active = 1")->fetchAll(\PDO::FETCH_ASSOC);
        
        $available = [];
        foreach ($keys as $key) {
            $service = strtolower($key['service_name']);
            $provider = $this->mapServiceToProvider($service);
            
            if ($provider && isset($this->freeModels[$provider])) {
                foreach ($this->freeModels[$provider]['models'] as $modelId => $modelInfo) {
                    $available[] = [
                        'provider' => $provider,
                        'service' => $key['service_name'],
                        'model_id' => $modelId,
                        'model_name' => $modelInfo['name'],
                        'context' => $modelInfo['context'],
                        'type' => $modelInfo['type'],
                        'is_free' => true,
                        'api_key' => $key['key_value'],
                    ];
                }
            }
        }
        
        return $available;
    }

    /**
     * Test if a specific model endpoint is working
     */
    public function testModelEndpoint($provider, $modelId, $apiKey)
    {
        $endpoints = [
            'openrouter' => 'https://openrouter.ai/api/v1/chat/completions',
            'groq' => 'https://api.groq.com/openai/v1/chat/completions',
            'cohere' => 'https://api.cohere.ai/v1/chat',
            'huggingface' => 'https://api-inference.huggingface.co/models/' . $modelId,
        ];

        if (!isset($endpoints[$provider])) {
            return ['success' => false, 'message' => 'Unknown provider'];
        }

        $ch = curl_init($endpoints[$provider]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);

        switch ($provider) {
            case 'openrouter':
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer ' . $apiKey,
                    'Content-Type: application/json',
                    'HTTP-Referer: http://localhost',
                    'X-Title: APS Dream Home'
                ]);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                    'model' => $modelId,
                    'messages' => [['role' => 'user', 'content' => 'Hi']]
                ]));
                break;
            case 'groq':
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer ' . $apiKey,
                    'Content-Type: application/json'
                ]);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                    'model' => $modelId,
                    'messages' => [['role' => 'user', 'content' => 'Hi']]
                ]));
                break;
            case 'huggingface':
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer ' . $apiKey
                ]);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['inputs' => 'Hi']));
                break;
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($httpCode == 200) {
            return ['success' => true, 'message' => 'Model is working', 'http_code' => $httpCode];
        } elseif ($httpCode == 429) {
            return ['success' => false, 'message' => 'Rate limited - try later', 'http_code' => $httpCode];
        } elseif ($httpCode == 503) {
            return ['success' => false, 'message' => 'Model loading - try again', 'http_code' => $httpCode];
        } else {
            return ['success' => false, 'message' => 'Error: ' . $error, 'http_code' => $httpCode];
        }
    }

    /**
     * Get the best free model suggestion
     */
    public function getBestFreeModel()
    {
        $available = $this->getAvailableModels();
        
        if (empty($available)) {
            return $this->getSuggestionWhenNoKeys();
        }

        // Sort by preference: OpenRouter > Groq > Others
        $preference = ['openrouter' => 1, 'groq' => 2, 'cohere' => 3, 'huggingface' => 4];
        
        usort($available, function($a, $b) use ($preference) {
            return ($preference[$a['provider']] ?? 99) <=> ($preference[$b['provider']] ?? 99);
        });

        // Test the top 3 models
        $working = [];
        foreach (array_slice($available, 0, 3) as $model) {
            $result = $this->testModelEndpoint($model['provider'], $model['model_id'], $model['api_key']);
            if ($result['success']) {
                $model['test_result'] = $result;
                $working[] = $model;
            }
        }

        if (empty($working)) {
            return [
                'suggestion' => 'All models may be rate limited. Try again later or add more API keys.',
                'models' => $available,
                'all_tested' => false
            ];
        }

        return [
            'suggestion' => 'Use ' . $working[0]['model_name'] . ' via ' . ucfirst($working[0]['provider']),
            'best_model' => $working[0],
            'all_models' => $working,
            'all_tested' => true
        ];
    }

    /**
     * When no API keys are set
     */
    public function getSuggestionWhenNoKeys()
    {
        return [
            'suggestion' => 'No AI API keys configured. Add OpenRouter or Groq API key for free AI access.',
            'steps' => [
                '1. Get free API key from openrouter.ai/keys (free tier available)',
                '2. Or get Groq API key from console.groq.com (free tier with high limits)',
                '3. Add the key in Admin > API Keys management',
                '4. The system will auto-select the best free model'
            ],
            'recommended' => [
                'provider' => 'openrouter',
                'reason' => 'Most free models, high quality, easy to get started',
                'url' => 'https://openrouter.ai/keys'
            ]
        ];
    }

    /**
     * Map service name to provider key
     */
    private function mapServiceToProvider($service)
    {
        $map = [
            'openai' => 'openai',
            'google gemini' => 'google',
            'openrouter' => 'openrouter',
            'anthropic claude' => 'anthropic',
            'hugging face' => 'huggingface',
            'groq' => 'groq',
            'cohere' => 'cohere',
        ];
        
        return $map[$service] ?? null;
    }

    /**
     * Get all free models list (for display)
     */
    public function getAllFreeModels()
    {
        return $this->freeModels;
    }
}
