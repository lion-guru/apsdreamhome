<?php
/**
 * AIGateway — Unified AI Router for APS Dream Home
 * 
 * Routes every AI task through the smartest available engine:
 * 1. Pattern Engine (IntentDetector + LeadScorer) — instant, free, deterministic
 * 2. SelfLearningAI — free, learns from every conversation
 * 3. Rule Engine — heuristic, instant, free
 * 4. Free AI Engines (Ollama → Groq) — unlimited free intelligence
 * 5. Gemini Flash — free tier fallback for complex NLP (rate limited)
 * 
 * Cost: ₹0. Ever. All free engines.
 * Hindi-first. Real estate specialized. Self-learning.
 */

namespace App\Services\AI;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;

class AIGateway
{
    private static $instance = null;
    private $db;
    private $config;
    private $freeEngines;
    private $geminiApiKey;
    private $geminiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

    private function __construct()
    {
        $this->db = Database::getInstance();
        $this->freeEngines = FreeAIEngines::getInstance();
        $this->loadConfig();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadConfig()
    {
        try {
            $tid = TenantContext::getId();
            $this->config = $this->db->fetch("SELECT * FROM ai_settings WHERE is_active = 1" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$tid] : []) ?: [];
        } catch (\Throwable $e) {
            $this->config = [];
        }
        $this->geminiApiKey = $this->config['api_key'] ?? getenv('GEMINI_API_KEY') ?: '';
    }

    // ─────────── Main Entry Point ─────────────────────────────────────

    /**
     * Process any AI task through the gateway
     * @param string $task  'chat', 'qualify_lead', 'match_property', 'schedule', 'analyze_market', 'score_lead', 'detect_intent'
     * @param array $input  Task-specific data
     * @param array $context  User/session context
     * @return array  ['engine' => string, 'result' => ..., 'confidence' => 0-1]
     */
    public function process(string $task, array $input, array $context = []): array
    {
        $startTime = microtime(true);

        // 1. Try IntentDetector first (instant, free)
        $patternResult = $this->patternProcess($task, $input, $context);
        if ($patternResult['confidence'] >= 0.75) {
            return $this->logResult($task, $patternResult, 'pattern', $startTime);
        }

        // 2. Try SelfLearningAI (free, local, learns)
        $slResult = $this->selfLearningProcess($task, $input, $context);
        if ($slResult['confidence'] >= 0.65) {
            return $this->logResult($task, $slResult, 'self_learning', $startTime);
        }

        // 3. Try rule-based heuristics
        $ruleResult = $this->ruleProcess($task, $input, $context);
        if ($ruleResult['confidence'] >= 0.7) {
            return $this->logResult($task, $ruleResult, 'rule', $startTime);
        }

        // 4. Try Free AI Engines (Ollama → Groq → OpenRouter) — unlimited free intelligence
        $freeResult = $this->freeEnginesProcess($task, $input, $context);
        if ($freeResult['confidence'] >= 0.6) {
            return $this->logResult($task, $freeResult, $freeResult['engine'] ?? 'free_ai', $startTime);
        }

        // 5. Fall back to Gemini (free tier, complex NLP)
        if ($this->isGeminiAvailable()) {
            $geminiResult = $this->geminiProcess($task, $input, $context);
            if ($geminiResult['confidence'] >= 0.5) {
                return $this->logResult($task, $geminiResult, 'gemini', $startTime);
            }
        }

        // 6. Return best available
        $all = [$patternResult, $slResult, $ruleResult, $freeResult];
        usort($all, fn($a, $b) => $b['confidence'] <=> $a['confidence']);
        return $this->logResult($task, $all[0], $all[0]['engine'] ?? 'rule', $startTime);
    }

    // ─────────── Pattern Engine (IntentDetector + LeadScorer) ─────────

    private function patternProcess(string $task, array $input, array $context): array
    {
        try {
            $text = $input['message'] ?? $input['text'] ?? $input['query'] ?? '';
            if (empty($text) && $task !== 'score_lead') {
                return ['result' => null, 'confidence' => 0, 'engine' => 'pattern'];
            }

            $result = ['task' => $task];

            // Intent detection
            if (!empty($text)) {
                $intentDetector = new IntentDetector($this->db);
                $result['intent'] = $intentDetector->detect($text);
            }

            // Lead scoring
            if ($task === 'score_lead' && !empty($input['lead_id'])) {
                $leadScorer = new LeadScorer($this->db);
                $result['score'] = $leadScorer->score((int)$input['lead_id']);
                $confidence = ($result['score']['score'] ?? 0) / 100;
            } else {
                $confidence = $result['intent']['confidence'] ?? 0;
            }

            return ['result' => $result, 'confidence' => $confidence, 'engine' => 'pattern'];
        } catch (\Throwable $e) {
            error_log('AIGateway::patternProcess error: ' . $e->getMessage());
            return ['result' => null, 'confidence' => 0, 'engine' => 'pattern'];
        }
    }

    // ─────────── SelfLearningAI ───────────────────────────────────────

    private function selfLearningProcess(string $task, array $input, array $context): array
    {
        try {
            $sessionId = $context['session_id'] ?? 'gw_' . md5(serialize($input));
            $userId = $context['user_id'] ?? null;
            $userRole = $context['user_role'] ?? 'customer';

            $selfLearning = new SelfLearningAI($sessionId, $userId, $userRole);
            $message = $input['message'] ?? $input['text'] ?? json_encode($input);
            $result = $selfLearning->processMessage($message);

            return [
                'result' => $result,
                'confidence' => $result['confidence'] ?? 0.5,
                'engine' => 'self_learning',
            ];
        } catch (\Throwable $e) {
            error_log('AIGateway::selfLearningProcess error: ' . $e->getMessage());
            return ['result' => null, 'confidence' => 0, 'engine' => 'self_learning'];
        }
    }

    // ─────────── Rule Engine (heuristic, fast) ────────────────────────

    private function ruleProcess(string $task, array $input, array $context): array
    {
        $result = ['task' => $task, 'actions' => []];
        $confidence = 0;

        switch ($task) {
            case 'qualify_lead':
                $score = 0;
                $budget = (float)($input['budget'] ?? 0);
                $phone = $input['phone'] ?? '';
                $message = strtolower($input['message'] ?? '');

                // Budget scoring
                if ($budget >= 5000000) $score += 40;
                elseif ($budget >= 2000000) $score += 30;
                elseif ($budget >= 1000000) $score += 20;
                elseif ($budget > 0) $score += 10;

                // Phone verification
                if (!empty($phone) && strlen($phone) >= 10) $score += 10;

                // Intent signals
                $hotKeywords = ['immediate', 'urgent', 'ready', 'cash', 'booking', 'अभी', 'तुरंत', 'कैश', 'बुकिंग'];
                $warmKeywords = ['interested', 'planning', 'next month', 'exploring', 'दिलचस्प', 'सोच रहा'];
                foreach ($hotKeywords as $kw) {
                    if (stripos($message, $kw) !== false) { $score += 20; break; }
                }
                foreach ($warmKeywords as $kw) {
                    if (stripos($message, $kw) !== false) { $score += 10; break; }
                }

                $score = min(100, $score);
                $qualification = $score >= 70 ? 'hot' : ($score >= 40 ? 'warm' : 'cold');

                $result['score'] = $score;
                $result['qualification'] = $qualification;
                $result['next_action'] = $qualification === 'hot' ? 'Call immediately' : ($qualification === 'warm' ? 'Follow up in 24h' : 'Add to nurture campaign');
                $confidence = $score / 100;
                break;

            case 'match_property':
                $budget = (float)($input['budget'] ?? 0);
                $location = strtolower($input['location'] ?? '');
                $size = (int)($input['size'] ?? 0);

                try {
                    $sql = "SELECT p.*, c.name as colony_name FROM plots p LEFT JOIN colonies c ON p.colony_id = c.id WHERE p.status = 'available'";
                    $params = [];
                    if ($budget > 0) {
                        $sql .= " AND p.price <= ?";
                        $params[] = $budget * 1.1; // 10% flexibility
                    }
                    if ($size > 0) {
                        $sql .= " AND p.area >= ?";
                        $params[] = $size * 0.8;
                    }
                    if (!empty($location)) {
                        $sql .= " AND (c.name LIKE ? OR p.block LIKE ?)";
                        $params[] = "%$location%";
                        $params[] = "%$location%";
                    }
                    $sql .= " ORDER BY p.price DESC LIMIT 5";
                    $plots = $this->db->fetchAll($sql, $params) ?: [];

                    $result['matches'] = array_map(function ($p) use ($budget) {
                        $fit = $budget > 0 ? min(100, (1 - abs($p['price'] - $budget) / $budget) * 100) : 50;
                        return [
                            'plot_id' => $p['id'],
                            'colony' => $p['colony_name'] ?? '',
                            'block' => $p['block'] ?? '',
                            'area' => $p['area'] ?? 0,
                            'price' => $p['price'] ?? 0,
                            'fit_score' => round($fit),
                        ];
                    }, $plots);
                    $confidence = count($plots) > 0 ? 0.8 : 0.3;
                } catch (\Throwable $e) {
                    $confidence = 0;
                }
                break;

            case 'detect_intent':
                $text = strtolower($input['message'] ?? $input['text'] ?? '');
                $intents = [
                    'buy' => ['buy', 'purchase', 'want', 'need', 'खरीद', 'चाहिए', 'लेना'],
                    'sell' => ['sell', 'बेच', 'बेचना'],
                    'visit' => ['visit', 'see', 'देख', 'आना', 'विजिट'],
                    'price' => ['price', 'cost', 'rate', 'कीमत', 'दाम', 'कितना'],
                    'emi' => ['emi', 'loan', 'finance', 'लोन', 'फाइनेंस'],
                    'complaint' => ['problem', 'issue', 'complaint', 'शिकायत', 'समस्या'],
                ];
                $detected = 'unknown';
                $maxScore = 0;
                foreach ($intents as $intent => $keywords) {
                    foreach ($keywords as $kw) {
                        if (stripos($text, $kw) !== false) {
                            $score = strlen($kw) / strlen($text) * 100;
                            if ($score > $maxScore) { $maxScore = $score; $detected = $intent; }
                        }
                    }
                }
                $result['intent'] = $detected;
                $result['confidence_score'] = min(100, $maxScore * 3);
                $confidence = min(1.0, $maxScore * 3 / 100);
                break;

            default:
                $confidence = 0;
        }

        return ['result' => $result, 'confidence' => $confidence, 'engine' => 'rule'];
    }

    // ─────────── Free AI Engines (Ollama/Groq/OpenRouter) ─────────────

    private function freeEnginesProcess(string $task, array $input, array $context): array
    {
        try {
            $purpose = match($task) {
                'qualify_lead' => 'qualify',
                'match_property' => 'match',
                'analyze_market' => 'analyze',
                default => 'chat',
            };

            $prompt = $input['message'] ?? $input['text'] ?? $input['query'] ?? json_encode($input);
            $system = match($purpose) {
                'qualify' => "Real estate lead qualify karo. Budget, urgency, interest level identify karo. JSON: {\"score\": 0-100, \"qualification\": \"hot|warm|cold\", \"next_action\": \"...\"}",
                'match' => "Property matching. Find best plots for given requirements. JSON: {\"matches\": [{\"plot_id\": N, \"score\": 0-100, \"reason\": \"...\"}]}",
                'analyze' => "Real estate market analysis. Trends, patterns, insights with specific numbers.",
                default => "APS Dream Home AI assistant. Real estate expert. Hindi/English. Professional.",
            };

            $result = $this->freeEngines->generate($prompt, ['system' => $system, 'max_tokens' => 1024], $purpose);

            if (!empty($result['text'])) {
                $parsed = $this->parseGeminiResponse($result['text']);
                return [
                    'result' => ['text' => $result['text'], 'parsed' => $parsed],
                    'confidence' => 0.75,
                    'engine' => $result['engine'] ?? 'free_ai',
                ];
            }
        } catch (\Throwable $e) {
            error_log('AIGateway::freeEnginesProcess error: ' . $e->getMessage());
        }

        return ['result' => null, 'confidence' => 0, 'engine' => 'free_ai'];
    }

    // ─────────── Gemini Flash (free tier) ─────────────────────────────

    private function geminiProcess(string $task, array $input, array $context): array
    {
        try {
            $prompt = $this->buildPrompt($task, $input, $context);
            $response = $this->callGemini($prompt);

            if ($response) {
                return [
                    'result' => ['text' => $response, 'parsed' => $this->parseGeminiResponse($response)],
                    'confidence' => 0.85,
                    'engine' => 'gemini',
                ];
            }
        } catch (\Throwable $e) {
            error_log('AIGateway::geminiProcess error: ' . $e->getMessage());
        }

        return ['result' => null, 'confidence' => 0, 'engine' => 'gemini'];
    }

    private function buildPrompt(string $task, array $input, array $context): string
    {
        $sys = "Tum APS Dream Home ka AI assistant ho. Real estate expert ho. Hindi aur English dono mein baat karte ho. Professional, helpful, friendly. Raghunath Nagri, Gorakhpur specialist.";

        switch ($task) {
            case 'qualify_lead':
                return "$sys\n\nLead qualify karo (JSON reply):\nName: " . ($input['name'] ?? '?') . "\nPhone: " . ($input['phone'] ?? '?') . "\nMessage: " . ($input['message'] ?? '?') . "\nBudget: " . ($input['budget'] ?? '?') . "\n\nJSON: {\"qualification\":\"hot|warm|cold\",\"score\":0-100,\"next_action\":\"...\",\"budget_estimate\":\"...\",\"timeline\":\"...\",\"reasoning\":\"...\"}";

            case 'chat':
                $hist = '';
                foreach (array_slice($context['history'] ?? [], -5) as $m) {
                    $hist .= ($m['role'] ?? 'user') . ": " . ($m['text'] ?? '') . "\n";
                }
                return "$sys\n\n$hist\nUser: " . ($input['message'] ?? '') . "\n\nAssistant:";

            default:
                return "$sys\n\nTask: $task\nData: " . json_encode($input) . "\n\nJSON response:";
        }
    }

    private function callGemini(string $prompt): ?string
    {
        $payload = [
            "contents" => [["role" => "user", "parts" => [["text" => $prompt]]]],
            "generationConfig" => ["temperature" => 0.7, "maxOutputTokens" => 1024]
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->geminiUrl . '?key=' . $this->geminiApiKey,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $result = json_decode($response, true);
            return $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
        }
        return null;
    }

    private function parseGeminiResponse(string $response): array
    {
        if (preg_match('/\{[\s\S]*\}/', $response, $matches)) {
            $parsed = json_decode($matches[0], true);
            if ($parsed) return $parsed;
        }
        return ['text' => $response];
    }

    // ─────────── Utility ──────────────────────────────────────────────

    private function logResult(string $task, array $result, string $engine, float $startTime): array
    {
        $elapsed = round((microtime(true) - $startTime) * 1000, 2);
        try {
            $this->db->getConnection()->prepare(
                "INSERT INTO ai_api_logs (service, endpoint, status_code, response_time_ms, request_data, tenant_id, user_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
            )->execute([$task, $engine, $result['confidence'] ?? 0, $elapsed, substr(json_encode($result['result'] ?? []), 0, 500), TenantContext::getId(), $GLOBALS['api_user_id'] ?? null]);
        } catch (\Throwable $e) { /* non-critical */ error_log($e->getMessage()); }

        $result['engine'] = $engine;
        $result['response_time_ms'] = $elapsed;
        return $result;
    }

    public function isGeminiAvailable(): bool
    {
        return !empty($this->geminiApiKey) && $this->geminiApiKey !== 'YOUR_REAL_GEMINI_API_KEY_HERE';
    }

    public function getStats(): array
    {
        try {
            $tid = TenantContext::getId();
            $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";
            return $this->db->fetch("
                SELECT COUNT(*) as total_calls,
                    SUM(CASE WHEN engine_used='rule' THEN 1 ELSE 0 END) as rule_calls,
                    SUM(CASE WHEN engine_used='self_learning' THEN 1 ELSE 0 END) as sl_calls,
                    SUM(CASE WHEN engine_used='pattern' THEN 1 ELSE 0 END) as pattern_calls,
                    SUM(CASE WHEN engine_used='gemini' THEN 1 ELSE 0 END) as gemini_calls,
                    ROUND(AVG(confidence),2) as avg_confidence,
                    ROUND(AVG(response_time_ms),1) as avg_response_ms
                FROM ai_api_logs WHERE DATE(created_at) = CURDATE()" . $tenantFilter, $tid > 1 ? [$tid] : []) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }
}
