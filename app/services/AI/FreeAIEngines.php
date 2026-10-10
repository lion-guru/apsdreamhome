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

    // OpenRouter (free tier: deprecated 2026, used as last resort)
    private $openRouterKey = '';
    private $openRouterUrl = 'https://openrouter.ai/api/v1/chat/completions';

    // Google Gemini (free tier: 15 RPM, 1M tokens/day)
    private $geminiKey = '';
    private $geminiModel = 'gemini-2.5-flash';
    private $geminiUrlBase = 'https://generativelanguage.googleapis.com/v1beta/models/';

    // xAI Grok (free tier: available via xAI API)
    private $xaiKey = '';
    private $xaiUrl = 'https://api.x.ai/v1/chat/completions';
    private $xaiModel = 'grok-beta';

    // Hugging Face Inference API (free tier: 30k tokens/day)
    private $hfKey = '';
    private $hfUrl = 'https://api-inference.huggingface.co/models/';
    private $hfModel = 'meta-llama/Meta-Llama-3.1-8B-Instruct';

    // Together.ai (free tier)
    private $togetherKey = '';
    private $togetherUrl = 'https://api.together.xyz/v1/chat/completions';
    private $togetherModel = 'meta-llama/Meta-Llama-3.1-8B-Instruct-Turbo';

    // DeepSeek (free tier via their API)
    private $deepseekKey = '';
    private $deepseekUrl = 'https://api.deepseek.com/v1/chat/completions';
    private $deepseekModel = 'deepseek-chat';

    // Cohere (free tier: 100 calls/min)
    private $cohereKey = '';
    private $cohereUrl = 'https://api.cohere.ai/v1/chat';
    private $cohereModel = 'command-r-plus';

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
            // New free cloud AI providers
            $this->xaiKey = $settings['xai_api_key'] ?? getenv('XAI_API_KEY') ?: '';
            $this->hfKey = $settings['hf_api_key'] ?? getenv('HF_API_KEY') ?: '';
            $this->togetherKey = $settings['together_api_key'] ?? getenv('TOGETHER_API_KEY') ?: '';
            $this->deepseekKey = $settings['deepseek_api_key'] ?? getenv('DEEPSEEK_API_KEY') ?: '';
            $this->togetherAiKey = $settings['together_api_key'] ?? getenv('TOGETHER_API_KEY') ?: '';
            $this->cohereKey = $settings['cohere_api_key'] ?? getenv('COHERE_API_KEY') ?: '';
            // Model saved by the AI Provider Settings dashboard (settings JSON column)
            $cfg = json_decode($settings['settings'] ?? '', true);
            if (is_array($cfg) && !empty($cfg['model']) && is_string($cfg['model'])) {
                $this->geminiModel = $cfg['model'];
            }
        } catch (\Throwable $e) {
            $this->groqKey = getenv('GROQ_API_KEY') ?: '';
            $this->openRouterKey = getenv('OPENROUTER_API_KEY') ?: '';
            $this->geminiKey = getenv('GEMINI_API_KEY') ?: '';
            $this->xaiKey = getenv('XAI_API_KEY') ?: '';
            $this->hfKey = getenv('HF_API_KEY') ?: '';
            $this->togetherKey = getenv('TOGETHER_API_KEY') ?: '';
            $this->deepseekKey = getenv('DEEPSEEK_API_KEY') ?: '';
            $this->togetherAiKey = getenv('TOGETHER_API_KEY') ?: '';
            $this->cohereKey = getenv('COHERE_API_KEY') ?: '';
        }
    }

    // ─────────── Main: Generate with best available free engine ───────

    /**
     * Strip model reasoning internals before text reaches users.
     * Reasoning models leak `<think>…</think>` blocks; some free models
     * instead dump "Here's a thinking process: … Draft Response: <reply>".
     * Users must only ever see the final reply — never the analysis.
     */
    public static function cleanResponse(string $text): string
    {
        // 1. Remove <think>/<thinking>/<reasoning> blocks (reasoning models)
        $cleaned = preg_replace('/<\s*(think|thinking|reasoning)\b.*?<\/\s*\1\s*>/is', '', $text);
        if (is_string($cleaned)) {
            $text = $cleaned;
        }
        // 2. Remove free-form "thinking process … Draft Response:" preamble, keep the draft
        if (preg_match('/draft response(?:\s*\(mental\))?\s*:\s*["“]?/i', $text, $m, PREG_OFFSET_CAPTURE)) {
            $text = substr($text, $m[0][1] + strlen($m[0][0]));
            $text = rtrim($text, "\"” \t\n\r");
        }
        return trim($text);
    }

    /**
     * Generate text using best available free engine
     * Priority: Ollama (local) → Groq (fastest) → xAI Grok → HuggingFace → Together.ai → DeepSeek → Together.ai → Cohere → OpenRouter (free models) → Google Gemini
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
            if ($result) return ['text' => self::cleanResponse($result), 'engine' => 'ollama', 'model' => $this->ollamaModel, 'tokens' => 0];
        }

        // 2. Try Groq (fastest in world, free tier) - models change frequently
        if (!empty($this->groqKey)) {
            $result = $this->groqGenerate($prompt, $system, $temperature, $maxTokens);
            if ($result) return ['text' => self::cleanResponse($result), 'engine' => 'groq', 'model' => $this->groqModel, 'tokens' => 0];
        }

        // 3. Try xAI Grok (free tier: available via xAI API)
        if (!empty($this->xaiKey)) {
            $result = $this->xaiGenerate($prompt, $system, $temperature, $maxTokens);
            if ($result) return ['text' => self::cleanResponse($result), 'engine' => 'xai_grok', 'model' => $this->xaiModel, 'tokens' => 0];
        }

        // 4. Try Hugging Face Inference API (free tier: 30k tokens/day)
        if (!empty($this->hfKey)) {
            $result = $this->hfGenerate($prompt, $system, $temperature, $maxTokens);
            if ($result) return ['text' => self::cleanResponse($result), 'engine' => 'huggingface', 'model' => $this->hfModel, 'tokens' => 0];
        }

        // 5. Try Together.ai (free tier: 100k tokens/day)
        if (!empty($this->togetherAiKey)) {
            $result = $this->togetherAiGenerate($prompt, $system, $temperature, $maxTokens);
            if ($result) return ['text' => self::cleanResponse($result), 'engine' => 'together_ai', 'model' => $this->togetherModel, 'tokens' => 0];
        }

        // 6. Try DeepSeek (free tier via their API)
        if (!empty($this->deepseekKey)) {
            $result = $this->deepseekGenerate($prompt, $system, $temperature, $maxTokens);
            if ($result) return ['text' => self::cleanResponse($result), 'engine' => 'deepseek', 'model' => $this->deepseekModel, 'tokens' => 0];
        }

        // 7. Try Together.ai (free tier: 100k tokens/day)
        if (!empty($this->togetherAiKey)) {
            $result = $this->togetherGenerate($prompt, $system, $temperature, $maxTokens);
            if ($result) return ['text' => self::cleanResponse($result), 'engine' => 'together', 'model' => $this->togetherModel, 'tokens' => 0];
        }

        // 8. Try Cohere (free tier: 100 calls/min)
        if (!empty($this->cohereKey)) {
            $result = $this->cohereGenerate($prompt, $system, $temperature, $maxTokens);
            if ($result) return ['text' => self::cleanResponse($result), 'engine' => 'cohere', 'model' => $this->cohereModel, 'tokens' => 0];
        }

        // 9. Try OpenRouter (free tier: deprecated 2026, used as last resort)
        if (!empty($this->openRouterKey)) {
            $result = $this->openRouterGenerate($prompt, $system, $temperature, $maxTokens);
            if ($result) return ['text' => self::cleanResponse($result), 'engine' => 'openrouter', 'model' => 'free-model', 'tokens' => 0];
        }

        // 10. Try Google Gemini (free tier: 15 RPM, 1M tokens/day)
        if (!empty($this->geminiKey)) {
            $result = $this->geminiGenerate($prompt, $system, $temperature, $maxTokens);
            if ($result) return ['text' => self::cleanResponse($result), 'engine' => 'gemini', 'model' => $this->geminiModel, 'tokens' => 0];
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

        foreach ($this->getOpenRouterFreeModels() as $model) {
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

    /**
     * Discover currently-available free models from OpenRouter's /models API.
     * Free models rotate frequently — never hardcode. Cached 6h in a temp file;
     * falls back to a static list when discovery fails.
     */
    private function getOpenRouterFreeModels(): array
    {
        static $inRequest = null;
        if ($inRequest !== null && $inRequest !== []) return $inRequest;

        // Allowed providers per account privacy settings (openrouter.ai/settings/privacy)
        $allowedPrefixes = ['nvidia/', 'groq/', 'openai/', 'minimax/', 'anthropic/', 'moonshotai/', 'google-ai-studio/'];

        $cacheFile = sys_get_temp_dir() . '/or_free_models.json';
        if (is_file($cacheFile)) {
            $cached = @json_decode((string)@file_get_contents($cacheFile), true);
            if (is_array($cached) && !empty($cached['models'])
                && (time() - (int)($cached['ts'] ?? 0)) < 21600) { // 6h TTL
                $inRequest = $cached['models'];
                return $inRequest;
            }
        }

        $models = [];
        try {
            $ch = curl_init('https://openrouter.ai/api/v1/models');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $this->openRouterKey],
            ]);
            $body = curl_exec($ch);
            curl_close($ch);
            $data = json_decode((string)$body, true)['data'] ?? [];

            foreach ($data as $m) {
                $id = $m['id'] ?? '';
                if ($id === '' || stripos($id, ':free') === false) continue;
                if ((float)($m['pricing']['prompt'] ?? 1) > 0 || (float)($m['pricing']['completion'] ?? 1) > 0) continue;
                foreach ($allowedPrefixes as $p) {
                    if (str_starts_with($id, $p)) { $models[] = $id; break; }
                }
            }
            // Heuristic: prefer smaller/faster models first (shorter id, then bigger context)
            usort($models, fn($a, $b) => strlen($a) <=> strlen($b));
            $models = array_slice(array_values(array_unique($models)), 0, 4);

            if ($models) {
                @file_put_contents($cacheFile, json_encode(['ts' => time(), 'models' => $models]));
            }
        } catch (\Throwable $e) {
            error_log("OpenRouter model discovery failed: " . $e->getMessage());
        }

        $fallback = [
            'nvidia/nemotron-3.5-lightning:free',
            'nvidia/nemotron-3-super-120b-a12b:free',
        ];
        $inRequest = $models ?: $fallback;
        return $inRequest;
    }

    /**
     * Public helper for other clients (e.g. OpenRouterClient) that need a
     * currently-free OpenRouter model id without duplicating discovery logic.
     */
    public function getPreferredOpenRouterModel(): string
    {
        $models = $this->getOpenRouterFreeModels();
        return $models[0] ?? 'nvidia/nemotron-3.5-lightning:free';
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

        $response = $this->httpPost($this->geminiUrlBase . $this->geminiModel . ':generateContent?key=' . $this->geminiKey, $payload, 15);
        if ($response && isset($response['candidates'][0]['content']['parts'][0]['text'])) {
            return $response['candidates'][0]['content']['parts'][0]['text'];
        }
        return null;
    }

    // xAI Grok (free tier: available via xAI API)
    private function xaiGenerate(string $prompt, string $system, float $temp, int $maxTokens): ?string
    {
        if (empty($this->xaiKey)) return null;

        $messages = [];
        if ($system) $messages[] = ['role' => 'system', 'content' => $system];
        $messages[] = ['role' => 'user', 'content' => $prompt];

        $payload = [
            'model' => $this->xaiModel,
            'messages' => $messages,
            'temperature' => $temp,
            'max_tokens' => $maxTokens,
            'stream' => false,
        ];

        $response = $this->httpPost($this->xaiUrl, $payload, 20, [
            'Authorization: Bearer ' . $this->xaiKey,
            'Content-Type: application/json',
        ]);

        if ($response && isset($response['choices'][0]['message']['content'])) {
            return $response['choices'][0]['message']['content'];
        }
        return null;
    }

    // Hugging Face Inference API (free tier: 30k tokens/day)
    private function hfGenerate(string $prompt, string $system, float $temp, int $maxTokens): ?string
    {
        if (empty($this->hfKey)) return null;

        $messages = [];
        if ($system) $messages[] = ['role' => 'system', 'content' => $system];
        $messages[] = ['role' => 'user', 'content' => $prompt];

        $payload = [
            'model' => $this->hfModel,
            'messages' => $messages,
            'temperature' => $temp,
            'max_tokens' => $maxTokens,
            'stream' => false,
        ];

        $response = $this->httpPost($this->hfUrl . $this->hfModel, $payload, 25, [
            'Authorization: Bearer ' . $this->hfKey,
            'Content-Type: application/json',
        ]);

        if ($response && isset($response[0]['generated_text'])) {
            return $response[0]['generated_text'];
        }
        return null;
    }

    // Together.ai (free tier: 100k tokens/day)
    private function togetherGenerate(string $prompt, string $system, float $temp, int $maxTokens): ?string
    {
        if (empty($this->togetherKey)) return null;

        $messages = [];
        if ($system) $messages[] = ['role' => 'system', 'content' => $system];
        $messages[] = ['role' => 'user', 'content' => $prompt];

        $payload = [
            'model' => $this->togetherModel,
            'messages' => $messages,
            'temperature' => $temp,
            'max_tokens' => $maxTokens,
            'stream' => false,
        ];

        $response = $this->httpPost($this->togetherUrl, $payload, 20, [
            'Authorization: Bearer ' . $this->togetherKey,
            'Content-Type: application/json',
        ]);

        if ($response && isset($response['choices'][0]['message']['content'])) {
            return $response['choices'][0]['message']['content'];
        }
        return null;
    }

    // DeepSeek (free tier via their API)
    private function deepseekGenerate(string $prompt, string $system, float $temp, int $maxTokens): ?string
    {
        if (empty($this->deepseekKey)) return null;

        $messages = [];
        if ($system) $messages[] = ['role' => 'system', 'content' => $system];
        $messages[] = ['role' => 'user', 'content' => $prompt];

        $payload = [
            'model' => $this->deepseekModel,
            'messages' => $messages,
            'temperature' => $temp,
            'max_tokens' => $maxTokens,
            'stream' => false,
        ];

        $response = $this->httpPost($this->deepseekUrl, $payload, 20, [
            'Authorization: Bearer ' . $this->deepseekKey,
            'Content-Type: application/json',
        ]);

        if ($response && isset($response['choices'][0]['message']['content'])) {
            return $response['choices'][0]['message']['content'];
        }
        return null;
    }

    // Together.ai (free tier: 100k tokens/day)
    private function togetherAiGenerate(string $prompt, string $system, float $temp, int $maxTokens): ?string
    {
        if (empty($this->togetherAiKey)) return null;

        $messages = [];
        if ($system) $messages[] = ['role' => 'system', 'content' => $system];
        $messages[] = ['role' => 'user', 'content' => $prompt];

        $payload = [
            'model' => $this->togetherModel,
            'messages' => $messages,
            'temperature' => $temp,
            'max_tokens' => $maxTokens,
            'stream' => false,
        ];

        $response = $this->httpPost($this->togetherUrl, $payload, 20, [
            'Authorization: Bearer ' . $this->togetherAiKey,
            'Content-Type: application/json',
        ]);

        if ($response && isset($response['choices'][0]['message']['content'])) {
            return $response['choices'][0]['message']['content'];
        }
        return null;
    }

    // Cohere (free tier: 100 calls/min)
    private function cohereGenerate(string $prompt, string $system, float $temp, int $maxTokens): ?string
    {
        if (empty($this->cohereKey)) return null;

        $payload = [
            'model' => $this->cohereModel,
            'messages' => [
                ['role' => 'user', 'content' => $system . "\n\n" . $prompt]
            ],
            'temperature' => $temp,
            'max_tokens' => $maxTokens,
        ];

        $response = $this->httpPost($this->cohereUrl, $payload, 20, [
            'Authorization: Bearer ' . $this->cohereKey,
            'Content-Type: application/json',
        ]);

        if ($response && isset($response['text'])) {
            return $response['text'];
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
            'xai_grok' => [
                'available' => !empty($this->xaiKey),
                'model' => $this->xaiModel,
                'cost' => 'Free tier: xAI API',
                'speed' => '~200 tokens/sec',
            ],
            'huggingface' => [
                'available' => !empty($this->hfKey),
                'model' => $this->hfModel,
                'cost' => 'Free: 30k tokens/day',
                'speed' => '~100 tokens/sec',
            ],
            'together_ai' => [
                'available' => !empty($this->togetherAiKey),
                'model' => $this->togetherModel,
                'cost' => 'Free: 100k tokens/day',
                'speed' => '~100 tokens/sec',
            ],
            'deepseek' => [
                'available' => !empty($this->deepseekKey),
                'model' => $this->deepseekModel,
                'cost' => 'Free tier: DeepSeek API',
                'speed' => '~150 tokens/sec',
            ],
            'together' => [
                'available' => !empty($this->togetherKey),
                'model' => $this->togetherModel,
                'cost' => 'Free: 100k tokens/day',
                'speed' => '~100 tokens/sec',
            ],
            'cohere' => [
                'available' => !empty($this->cohereKey),
                'model' => $this->cohereModel,
                'cost' => 'Free: 100 calls/min',
                'speed' => '~200 tokens/sec',
            ],
            'openrouter' => [
                'available' => !empty($this->openRouterKey),
                'model' => 'Low-cost paid models (Llama, Phi-3)',
                'cost' => 'Paid (free tier deprecated)',
                'speed' => '~100 tokens/sec',
            ],
            'gemini' => [
                'available' => !empty($this->geminiKey),
                'model' => $this->geminiModel,
                'cost' => 'Free: 15 RPM, 1M tokens/day',
                'speed' => '~150 tokens/sec',
            ],
        ];
    }
}
