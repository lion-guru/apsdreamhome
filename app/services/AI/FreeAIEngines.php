<?php
/**
 * FreeAIEngines — All free AI engines in one place
 * 
 * 1. Ollama (localhost) — Unlimited, free, private, Hindi-capable
 * 2. Groq (free tier) — Llama 3.3 70B, fastest inference in world
 * 3. OpenRouter (low-cost paid) — Free tier deprecated, used as last resort
 * 4. Google Gemini Flash (free tier) — Already in AIGateway
 * 
 * Cost: ₹0. Ever.
 */

namespace App\Services\AI;

class FreeAIEngines
{
    private static $instance = null;

    // Ollama (local)
    private $ollamaUrl = 'http://localhost:11434';
    private $ollamaModel = 'llama3.2:3b';

// Groq (free tier: 30 RPM, 14,400 RPD) - models may change, check console.groq.com
    private $groqKey = '';
    private $groqUrl = 'https://api.groq.com/openai/v1/chat/completions';
    private $groqModel = 'groq/compound-mini';

    // OpenRouter (paid models, low-cost fallback - free tier deprecated 2026)
    private $openRouterKey = '';
    private $openRouterUrl = 'https://openrouter.ai/api/v1/chat/completions';

    // Google Gemini (free tier: 15 RPM, 1M tokens/day)
    private $geminiKey = '';
    private $geminiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

    private function __construct()
    {
        $this->ollamaUrl = getenv('OLLAMA_URL') ?: $this->ollamaUrl;
        $this->ollamaModel = getenv('OLLAMA_MODEL') ?: $this->ollamaModel;
        $this->loadKeys();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadKeys()
    {
        try {
            $db = \App\Core\Database\Database::getInstance();
            $settings = $db->fetch("SELECT * FROM ai_settings WHERE is_active = 1") ?: [];
            $this->groqKey = $settings['groq_api_key'] ?? getenv('GROQ_API_KEY') ?: '';
            $this->openRouterKey = $settings['openrouter_api_key'] ?? getenv('OPENROUTER_API_KEY') ?: '';
            $this->geminiKey = $settings['api_key'] ?? getenv('GEMINI_API_KEY') ?: '';
        } catch (\Throwable $e) {
            $this->groqKey = getenv('GROQ_API_KEY') ?: '';
            $this->openRouterKey = getenv('OPENROUTER_API_KEY') ?: '';
            $this->geminiKey = getenv('GEMINI_API_KEY') ?: '';
        }
    }

    // ─────────── Main: Generate with best available free engine ───────

    /**
     * Generate text using best available free engine
     * Priority: Ollama (local) → Groq (fastest) → OpenRouter (free models)
     * @param string $prompt
     * @param array $options  ['temperature' => 0.7, 'max_tokens' => 1024, 'system' => '...']
     * @param string $purpose  'chat', 'qualify', 'match', 'analyze', 'translate'
     * @return array ['text' => string, 'engine' => string, 'model' => string, 'tokens' => int]
     */
    public function generate(string $prompt, array $options = [], string $purpose = 'chat'): array
    {
        $system = $options['system'] ?? $this->getSystemPrompt($purpose);
        $maxTokens = $options['max_tokens'] ?? 1024;
        $temperature = $options['temperature'] ?? 0.7;

        // 1. Try Ollama (local, unlimited, private)
        if ($this->isOllamaAvailable()) {
            $result = $this->ollamaGenerate($prompt, $system, $temperature, $maxTokens);
            if ($result) return ['text' => $result, 'engine' => 'ollama', 'model' => $this->ollamaModel, 'tokens' => 0];
        }

        // 2. Try Groq (fastest in world, free tier) - models change frequently
        if (!empty($this->groqKey)) {
            $result = $this->groqGenerate($prompt, $system, $temperature, $maxTokens);
            if ($result) return ['text' => $result, 'engine' => 'groq', 'model' => $this->groqModel, 'tokens' => 0];
        }

        // 3. Try OpenRouter (free models) - free tier deprecated
        if (!empty($this->openRouterKey)) {
            $result = $this->openRouterGenerate($prompt, $system, $temperature, $maxTokens);
            if ($result) return ['text' => $result, 'engine' => 'openrouter', 'model' => 'free-model', 'tokens' => 0];
        }

        // 4. Try Google Gemini (free tier: 15 RPM, 1M tokens/day)
        if (!empty($this->geminiKey)) {
            $result = $this->geminiGenerate($prompt, $system, $temperature, $maxTokens);
            if ($result) return ['text' => $result, 'engine' => 'gemini', 'model' => 'gemini-2.5-flash', 'tokens' => 0];
        }

        return ['text' => '', 'engine' => 'none', 'model' => '', 'tokens' => 0];
    }

    // ─────────── Ollama (Local, Unlimited) ────────────────────────────

    public function isOllamaAvailable(): bool
    {
        static $checked = null;
        if ($checked !== null) return $checked;

        $ch = curl_init($this->ollamaUrl . '/api/tags');
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 2]);
        curl_exec($ch);
        $checked = curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200;
        curl_close($ch);
        return $checked;
    }

    private function ollamaGenerate(string $prompt, string $system, float $temp, int $maxTokens): ?string
    {
        $messages = [];
        if ($system) $messages[] = ['role' => 'system', 'content' => $system];
        $messages[] = ['role' => 'user', 'content' => $prompt];

        $payload = [
            'model' => $this->ollamaModel,
            'messages' => $messages,
            'stream' => false,
            'keep_alive' => -1,
            'options' => ['temperature' => $temp, 'num_predict' => $maxTokens],
        ];

        $response = $this->httpPost($this->ollamaUrl . '/api/chat', $payload, 30);
        if ($response && isset($response['message']['content'])) {
            return $response['message']['content'];
        }
        return null;
    }

    // ─────────── Groq (Fastest Free API) ──────────────────────────────

    private function groqGenerate(string $prompt, string $system, float $temp, int $maxTokens): ?string
    {
        $messages = [];
        if ($system) $messages[] = ['role' => 'system', 'content' => $system];
        $messages[] = ['role' => 'user', 'content' => $prompt];

        $payload = [
            'model' => $this->groqModel,
            'messages' => $messages,
            'temperature' => $temp,
            'max_completion_tokens' => $maxTokens,
            'stream' => false,
        ];

        $response = $this->httpPost($this->groqUrl, $payload, 15, [
            'Authorization: Bearer ' . $this->groqKey,
            'Content-Type: application/json',
        ]);

        if ($response && isset($response['choices'][0]['message']['content'])) {
            return $response['choices'][0]['message']['content'];
        }
        return null;
    }

    // ─────────── OpenRouter (Free Models) ─────────────────────────────

    private function openRouterGenerate(string $prompt, string $system, float $temp, int $maxTokens): ?string
    {
        $messages = [];
        if ($system) $messages[] = ['role' => 'system', 'content' => $system];
        $messages[] = ['role' => 'user', 'content' => $prompt];

        // Free models from allowed providers (groq/nvidia/openai/minimax/anthropic/moonshotai/google-ai-studio)
        $freeModels = [
            'nvidia/nemotron-3.5-lightning:free',
            'nvidia/nemotron-3-super-120b-a12b:free',
            'nvidia/nemotron-3-nano-omni-30b-a3b-reasoning:free',
        ];

        foreach ($freeModels as $model) {
            $payload = [
                'model' => $model,
                'messages' => $messages,
                'temperature' => $temp,
                'max_tokens' => $maxTokens,
            ];

            $response = $this->httpPost($this->openRouterUrl, $payload, 15, [
                'Authorization: Bearer ' . $this->openRouterKey,
                'Content-Type: application/json',
                'HTTP-Referer: https://apsdreamhome.com',
                'X-Title: APS Dream Home AI',
            ]);

            if ($response && isset($response['choices'][0]['message']['content'])) {
                return $response['choices'][0]['message']['content'];
            }
        }
        return null;
    }

    // Google Gemini (Free tier: 15 RPM, 1M tokens/day)
    private function geminiGenerate(string $prompt, string $system, float $temp, int $maxTokens): ?string
    {
        $parts = [];
        if ($system) $parts[] = ['text' => $system];
        $parts[] = ['text' => $prompt];

        $payload = [
            'contents' => [['parts' => $parts]],
            'generationConfig' => [
                'temperature' => $temp,
                'maxOutputTokens' => $maxTokens,
                'thinkingConfig' => ['thinkingBudget' => 0],
            ],
        ];

        $response = $this->httpPost($this->geminiUrl . '?key=' . $this->geminiKey, $payload, 15);
        if ($response && isset($response['candidates'][0]['content']['parts'][0]['text'])) {
            return $response['candidates'][0]['content']['parts'][0]['text'];
        }
        return null;
    }

    // ─────────── System Prompts by Purpose ────────────────────────────

    private function getSystemPrompt(string $purpose): string
    {
        $prompts = [
            'chat' => "Tum APS Dream Home ka AI assistant ho. Real estate expert ho Gorakhpur, UP mein. Hindi aur English dono mein baat karte ho. Professional, helpful, friendly tone. Property prices, EMI, site visits, registry ke baare mein expert ho.",

            'qualify' => "Tum ek real estate lead qualifier ho. Lead ke message se budget, urgency, location preference, aur interest level samjho. JSON format mein jawab do: {\"score\": 0-100, \"qualification\": \"hot|warm|cold\", \"budget\": \"...\", \"timeline\": \"...\", \"next_action\": \"...\"}",

            'match' => "Tum property matchmaker ho. Lead ki requirements se best matching plots suggest karo. Budget, location, size, aur preferences consider karo. JSON: {\"matches\": [{\"plot_id\": N, \"score\": 0-100, \"reason\": \"...\"}]}",

            'analyze' => "Tum real estate market analyst ho. Data se trends, patterns, aur insights nikalo. Gorakhpur aur UP market ka expert ho. Specific numbers aur actionable recommendations do.",

            'translate' => "Tum Hindi-English translator ho. Real estate terminology expertly translate karo. Formal business translation, conversational translation dono kar sakte ho.",
        ];

        return $prompts[$purpose] ?? $prompts['chat'];
    }

    // ─────────── Hindi-specific AI ────────────────────────────────────

    /**
     * Specialized Hindi AI — understands Hinglish, Hindi+English mixed
     */
    public function hindiAI(string $message, string $context = 'chat'): ?string
    {
        $system = "Tum APS Dream Home ka Hindi AI assistant ho. " .
            "Hinglish (Hindi written in English) samajhte ho. " .
            "Hindi mein jawab do unless user English mein baat kare. " .
            "Real estate expert ho — property, price, EMI, registry, site visit sab jaante ho. " .
            "Professional aur friendly tone.";

        $result = $this->generate($message, ['system' => $system, 'temperature' => 0.7], $context);
        return !empty($result['text']) ? $result['text'] : null;
    }

    // ─────────── Utility ──────────────────────────────────────────────

    private function httpPost(string $url, array $data, int $timeout = 15, array $headers = []): ?array
    {
        $ch = curl_init();
        $defaultHeaders = ['Content-Type: application/json'];
        $allHeaders = array_merge($defaultHeaders, $headers);

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $allHeaders,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300 && $response) {
            $decoded = json_decode($response, true);
            if ($decoded) return $decoded;
        }
        return null;
    }

    // ─────────── Accessors (for health checks / admin UI) ─────────────

    public function getOllamaUrl(): string
    {
        return $this->ollamaUrl;
    }

    public function getOllamaModel(): string
    {
        return $this->ollamaModel;
    }

    public function isGroqConfigured(): bool
    {
        return !empty($this->groqKey);
    }

    public function isOpenRouterConfigured(): bool
    {
        return !empty($this->openRouterKey);
    }

    /**
     * Get status of all free engines
     */
    public function getStatus(): array
    {
        return [
            'ollama' => [
                'available' => $this->isOllamaAvailable(),
                'model' => $this->ollamaModel,
                'cost' => 'Free (local)',
                'speed' => '~20 tokens/sec',
            ],
            'groq' => [
                'available' => !empty($this->groqKey),
                'model' => $this->groqModel,
                'cost' => 'Free tier: 30 RPM',
                'speed' => '~500 tokens/sec',
            ],
            'openrouter' => [
                'available' => !empty($this->openRouterKey),
                'model' => 'Low-cost paid models (Llama, Phi-3)',
                'cost' => 'Paid (free tier deprecated)',
                'speed' => '~100 tokens/sec',
            ],
            'gemini' => [
                'available' => !empty($this->geminiKey),
                'model' => 'gemini-2.5-flash',
                'cost' => 'Free: 15 RPM, 1M tokens/day',
                'speed' => '~150 tokens/sec',
            ],
        ];
    }
}
