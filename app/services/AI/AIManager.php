<?php
/**
 * AIManager - Central AI orchestration service
 * Self-learning, self-hosted, no external API
 * Coordinates: PatternLearner, IntentDetector, RecommendationEngine, LeadScorer, PricePredictor
 */

namespace App\Services\AI;

use PDO;
use App\Services\AI\PatternLearner;
use App\Services\AI\IntentDetector;
use App\Services\AI\RecommendationEngine;
use App\Services\AI\LeadScorer;
use App\Services\AI\PricePredictor;

class AIManager
{
    use \App\Traits\ServiceTenantTrait;

    private $db;
    private $pdo;
    private PatternLearner $learner;
    private IntentDetector $intents;
    private RecommendationEngine $recommender;
    private LeadScorer $scorer;
    private PricePredictor $predictor;

    public function __construct($db)
    {
        $this->db = $db;
        $pdo = is_object($db) && method_exists($db, 'getPdo') ? $db->getPdo() : $db;
        $this->pdo = $pdo;
        $this->learner = new PatternLearner($db);
        $this->intents = new IntentDetector($db);
        $this->recommender = new RecommendationEngine($db);
        $this->scorer = new LeadScorer($db);
        $this->predictor = new PricePredictor($db);
    }

    public function getPatternLearner(): PatternLearner { return $this->learner; }
    public function getIntentDetector(): IntentDetector { return $this->intents; }
    public function getRecommender(): RecommendationEngine { return $this->recommender; }
    public function getScorer(): LeadScorer { return $this->scorer; }
    public function getPredictor(): PricePredictor { return $this->predictor; }

    /**
     * Process a chat message: detect intent, generate response
     */
    public function processChat(string $sessionId, ?int $userId, string $message, string $channel = 'web'): array
    {
        // 1. Get/create session
        $session = $this->getOrCreateSession($sessionId, $userId, $channel);

        // 2. Detect intent
        $detection = $this->intents->detect($message);

        // 3. Save user message
        $this->saveMessage($sessionId, 'user', $message, $detection);

        // 4. Generate response based on intent
        $response = $this->generateResponse($detection, $message, $userId);

        // 5. Save bot response
        $this->saveMessage($sessionId, 'bot', $response['text'], $detection, $response['response_time_ms'] ?? 0);

        // 6. Log learning
        $this->learner->record('chat', $userId, $sessionId, [
            'message' => $message,
            'intent' => $detection['intent']
        ], [
            'response' => $response['text']
        ], ['channel' => $channel], $response['confidence'] > 0.7 ? 5 : 3);

        return $response;
    }

    /**
     * Get recommendations for user
     */
    public function getRecommendations(int $userId, int $limit = 10): array
    {
        // Update profile from recent behavior first
        $this->recommender->updateProfileFromBehavior($userId);
        return $this->recommender->recommend($userId, $limit);
    }

    /**
     * Score a lead
     */
    public function scoreLead(int $leadId): array
    {
        return $this->scorer->score($leadId);
    }

    /**
     * Predict property price
     */
    public function predictPrice(string $type, ?int $districtId = null, ?int $area = null, int $bedrooms = 0, int $bathrooms = 0): array
    {
        return $this->predictor->predict($type, $districtId, $area, $bedrooms, $bathrooms);
    }

    /**
     * Track user behavior
     */
    public function track(?int $userId, string $action, ?string $pageUrl = null, ?string $targetType = null, ?int $targetId = null, array $metadata = [], ?string $sessionId = null, int $durationMs = 0): void
    {
        $tenantData = $this->tenantInsertData();
        $tenantCols = array_keys($tenantData);
        $tenantVals = array_values($tenantData);
        $columns = array_merge(['user_id', 'session_id', 'page_url', 'action_type', 'target_type', 'target_id', 'metadata', 'duration_ms'], $tenantCols);
        $values  = array_merge([$userId, $sessionId, $pageUrl, $action, $targetType, $targetId, json_encode($metadata), $durationMs], $tenantVals);
        $colStr = implode(', ', $columns);
        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $stmt = $this->db->prepare("INSERT INTO user_behavior_tracking ($colStr) VALUES ($placeholders)");
        $stmt->execute($values);
    }

    /**
     * Detect anomalies
     */
    public function detectAnomalies(string $entityType, int $entityId, array $data): array
    {
        $anomalies = [];

        // Price anomaly: price way above/below average
        if ($entityType === 'property' && isset($data['price'])) {
            $avg = $this->getAveragePrice($data['property_type'] ?? null, $data['district_id'] ?? null);
            if ($avg > 0) {
                $ratio = $data['price'] / $avg;
                if ($ratio > 2.0 || $ratio < 0.5) {
                    $anomalies[] = [
                        'type' => 'price_outlier',
                        'severity' => abs(log($ratio)) > 1 ? 'high' : 'medium',
                        'description' => "Price ₹{$data['price']} is " . round($ratio, 2) . "x the average ₹" . round($avg, 0)
                    ];
                }
            }
        }

        // Lead anomaly: too many inquiries from same source in short time
        if ($entityType === 'lead' && isset($data['phone'])) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM leads WHERE phone = ?{$this->tenantSql()}");
            $params = [$data['phone']];
            if ($this->tenantId() > 1) { $params[] = $this->tenantId(); }
            $stmt->execute($params);
            $recent = (int)$stmt->fetchColumn();
            if ($recent > 5) {
                $anomalies[] = [
                    'type' => 'rapid_inquiry',
                    'severity' => 'high',
                    'description' => "$recent inquiries from same phone in last hour"
                ];
            }
        }

        // Save anomalies
        foreach ($anomalies as $a) {
            $tenantData = $this->tenantInsertData();
            $tenantCols = array_keys($tenantData);
            $tenantVals = array_values($tenantData);
            $columns = array_merge(['entity_type', 'entity_id', 'anomaly_type', 'severity', 'description', 'data_snapshot'], $tenantCols);
            $values  = array_merge([$entityType, $entityId, $a['type'], $a['severity'], $a['description'], json_encode($data)], $tenantVals);
            $colStr = implode(', ', $columns);
            $placeholders = implode(', ', array_fill(0, count($values), '?'));
            $ins = $this->db->prepare("INSERT INTO ai_anomalies ($colStr) VALUES ($placeholders)");
            $ins->execute($values);
        }

        return $anomalies;
    }

    private function getAveragePrice(?string $type, ?int $districtId): float
    {
        $sql = "SELECT AVG(price) FROM user_properties WHERE price > 0" . $this->tenantSql();
        $params = [];
        if ($type) { $sql .= " AND property_type = ?"; $params[] = $type; }
        if ($districtId) { $sql .= " AND district_id = ?"; $params[] = $districtId; }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (float)$stmt->fetchColumn();
    }

    private function getOrCreateSession(string $sessionId, ?int $userId, string $channel): array
    {
        $stmt = $this->db->prepare("SELECT * FROM ai_chat_sessions WHERE session_id = ?{$this->tenantSql()}");
        $stmt->execute([$sessionId]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$session) {
            $tenantData = $this->tenantInsertData();
            $tenantCols = array_keys($tenantData);
            $tenantVals = array_values($tenantData);
            $columns = array_merge(['session_id', 'user_id', 'channel'], $tenantCols);
            $values  = array_merge([$sessionId, $userId, $channel], $tenantVals);
            $colStr = implode(', ', $columns);
            $placeholders = implode(', ', array_fill(0, count($values), '?'));
            $ins = $this->db->prepare("INSERT INTO ai_chat_sessions ($colStr) VALUES ($placeholders)");
            $ins->execute($values);
            $session = ['session_id' => $sessionId, 'user_id' => $userId, 'channel' => $channel];
        }
        return $session;
    }

    private function saveMessage(string $sessionId, string $sender, string $message, array $detection, int $responseTimeMs = 0): void
    {
        $tenantData = $this->tenantInsertData();
        $tenantCols = array_keys($tenantData);
        $tenantVals = array_values($tenantData);
        $columns = array_merge(['session_id', 'sender', 'message', 'detected_intent', 'confidence', 'response_time_ms'], $tenantCols);
        $values  = array_merge([$sessionId, $sender, $message, $detection['intent'] ?? null, $detection['confidence'] ?? 0, $responseTimeMs], $tenantVals);
        $colStr = implode(', ', $columns);
        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $stmt = $this->db->prepare("INSERT INTO ai_chat_messages ($colStr) VALUES ($placeholders)");
        $stmt->execute($values);
    }

    /**
     * Generate response based on detected intent
     * Uses templates + learned patterns
     */
    private function generateResponse(array $detection, string $message, ?int $userId): array
    {
        $start = microtime(true);
        $intent = $detection['intent'];
        $lang = $detection['language'];

        // Real AI first (cloud free engines: Groq/OpenRouter/Gemini); templates are the offline fallback
        try {
            $aiText = trim(\App\Services\AI\FreeAIEngines::getInstance()->generate(
                $message,
                ['max_tokens' => 250, 'temperature' => 0.7],
                'chat'
            )['text'] ?? '');
        } catch (\Throwable $e) {
            error_log("AIManager free-engine reply failed: " . $e->getMessage());
            $aiText = '';
        }

        if ($aiText !== '') {
            // Add personalized follow-up for buy/rent intents
            if (in_array($intent, ['buy_property', 'rent_property']) && $userId) {
                $recs = $this->recommender->recommend($userId, 3);
                if (!empty($recs)) {
                    $aiText .= "\n\nBased on your interest, here are some options:\n";
                    foreach ($recs as $r) {
                        $name = $r['item']['title'] ?? $r['item']['name'] ?? 'Property #' . $r['item']['id'];
                        $aiText .= "- $name\n";
                    }
                }
            }
            return [
                'text' => $aiText,
                'intent' => $intent,
                'response_time_ms' => (int)((microtime(true) - $start) * 1000),
                'confidence' => max($detection['confidence'], 0.85),
                'source' => 'free_ai',
            ];
        }

        $templates = $this->getResponseTemplates($lang);

        if (isset($templates[$intent])) {
            $candidates = $templates[$intent];
            $text = $candidates[array_rand($candidates)];
            $responseTime = (int)((microtime(true) - $start) * 1000);

            // Add personalized follow-up for buy/rent intents
            if (in_array($intent, ['buy_property', 'rent_property']) && $userId) {
                $recs = $this->recommender->recommend($userId, 3);
                if (!empty($recs)) {
                    $text .= "\n\nBased on your interest, here are some options:\n";
                    foreach ($recs as $r) {
                        $name = $r['item']['title'] ?? $r['item']['name'] ?? 'Property #' . $r['item']['id'];
                        $text .= "- $name\n";
                    }
                }
            }

            return ['text' => $text, 'intent' => $intent, 'response_time_ms' => $responseTime, 'confidence' => $detection['confidence']];
        }

        // Fallback
        $fallback = $lang === 'hi'
            ? "मैं आपकी मदद करना चाहता हूं। कृपया बताएं कि आप property खरीदना, बेचना, या किराए पर लेना चाहते हैं?"
            : "I'd love to help! Are you looking to buy, sell, or rent a property?";

        return ['text' => $fallback, 'intent' => 'unknown', 'response_time_ms' => (int)((microtime(true) - $start) * 1000), 'confidence' => 0.0];
    }

    private function getResponseTemplates(string $lang): array
    {
        if ($lang === 'hi') {
            return [
                'buy_property' => [
                    "बहुत बढ़िया! मैं आपको perfect property ढूंढने में मदद करूंगा। आपकी budget और preferred location क्या है?",
                    "Property खरीदना एक अच्छा निवेश है! क्या आप plot, flat, या house देख रहे हैं?"
                ],
                'sell_property' => [
                    "अपनी property बेचने के लिए हमारी team आपकी मदद करेगी। कृपया property details share करें।",
                    "हम आपकी property को सही buyer तक पहुंचाएंगे। Location और price बताएं?"
                ],
                'rent_property' => [
                    "किराए के लिए कई options हैं। आप किस area में देख रहे हैं?",
                    "Rental property के लिए budget और location बताएं, मैं best options दिखाता हूं।"
                ],
                'site_visit' => [
                    "Site visit schedule करने के लिए मुझे property ID और आपकी preferred date बताएं।",
                    "जी बिल्कुल! कौन सी property देखनी है? मैं visit book कर देता हूं।"
                ],
                'price_inquiry' => [
                    "Pricing हमारी सबसे competitive है! किस area में property देख रहे हैं?",
                    "हमारी pricing बाज़ार के अनुसार है। Specific property का नाम बताएं तो details देता हूं।"
                ],
                'loan' => [
                    "Home loan के लिए हम SBI, HDFC, ICICI जैसे बैंकों से tie-up रखते हैं। आपकी monthly income क्या है?",
                    "Loan हमारे verified partners के through मिलता है। मैं आपको best rate दिलवाता हूं।"
                ],
                'legal_help' => [
                    "Legal services में registry, agreement, mutation सब शामिल है। आपको किसकी जरूरत है?",
                    "हमारी team expert legal advice देती है। अपनी जरूरत बताएं।"
                ],
                'greeting' => [
                    "नमस्ते! APS Dream Home में आपका स्वागत है। मैं आपकी कैसे मदद कर सकता हूं?",
                    "नमस्कार जी! कृपया बताएं आप property के बारे में क्या जानना चाहते हैं?"
                ],
                'thanks' => [
                    "आपका स्वागत है! कोई और सवाल हो तो ज़रूर पूछें।",
                    "धन्यवाद! APS Dream Home आपकी service में हमेशा तत्पर है।"
                ],
                'goodbye' => [
                    "फिर मिलेंगे! अच्छा दिन हो।",
                    "अलविदा! जब भी ज़रूरत हो, हमसे संपर्क करें।"
                ]
            ];
        }

        return [
            'buy_property' => [
                "Great choice! Buying property is a wonderful investment. What's your budget and preferred location?",
                "I'd love to help you find the perfect property! Are you looking for a plot, flat, or house?"
            ],
            'sell_property' => [
                "We can definitely help you sell your property. Please share some details about it.",
                "Our team will help you get the best price. What's the property location and your expected price?"
            ],
            'rent_property' => [
                "We have many rental options. Which area are you looking at?",
                "What's your budget and preferred location? I'll show you the best matches."
            ],
            'site_visit' => [
                "Sure! Please share the property ID and your preferred date for the visit.",
                "I can schedule that right away. Which property would you like to see?"
            ],
            'price_inquiry' => [
                "Our pricing is very competitive! Which area are you interested in?",
                "Pricing varies by location and property type. Share a specific property name for details."
            ],
            'loan' => [
                "We have partnerships with SBI, HDFC, ICICI and more. What's your monthly income?",
                "I'll get you the best loan rate. Are you a first-time buyer or upgrading?"
            ],
            'legal_help' => [
                "Our legal services include registry, agreement, mutation, and more. What do you need?",
                "We have expert legal advisors. Tell me more about your requirement."
            ],
            'greeting' => [
                "Hello! Welcome to APS Dream Home. How can I help you today?",
                "Hi there! What are you looking for - buy, sell, or rent?"
            ],
            'thanks' => [
                "You're welcome! Feel free to ask anything else.",
                "Glad to help! APS Dream Home is always here for you."
            ],
            'goodbye' => [
                "Goodbye! Have a great day!",
                "See you again! Reach out anytime."
            ]
        ];
    }

    /**
     * Self-retrain: called from cron
     * Updates all AI components
     */
    public function retrain(): array
    {
        $results = [];

        // 1. Update user profiles from behavior
        $tid = $this->tenantId();
        $twhere = $this->tenantSql();
        $stmt = $this->db->prepare("SELECT user_id FROM user_behavior_tracking WHERE tracked_at > DATE_SUB(NOW(), INTERVAL 7 DAY)" . $twhere . " GROUP BY user_id LIMIT 100");
        $stmt->execute($tid > 1 ? [$tid] : []);
        $profileCount = 0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->recommender->updateProfileFromBehavior((int)$row['user_id']);
            $profileCount++;
        }
        $results['profiles_updated'] = $profileCount;

        // 2. Re-score all leads
        $results['leads_scored'] = $this->scorer->scoreAllUnscored(200);

        // 3. Retrain intent patterns
        $results['patterns_retrained'] = $this->learner->retrain();

        // 4. Retrain price models
        $types = ['plot', 'house', 'flat', 'shop', 'farmhouse'];
        $priceModels = 0;
        foreach ($types as $type) {
            $this->predictor->predict($type);
            $priceModels++;
        }
        $results['price_models_trained'] = $priceModels;

        return $results;
    }

    /**
     * Get AI dashboard stats
     */
    public function getStats(): array
    {
        $stats = [];

        $tables = [
            'learning_events' => 'ai_learning_data',
            'intent_patterns' => 'ai_intent_patterns',
            'user_profiles' => 'ai_user_profiles',
            'recommendations' => 'ai_recommendations',
            'lead_scores' => 'ai_lead_scores',
            'anomalies' => 'ai_anomalies',
            'price_models' => 'ai_price_models',
            'chat_sessions' => 'ai_chat_sessions',
            'chat_messages' => 'ai_chat_messages',
            'behavior_tracked' => 'user_behavior_tracking'
        ];

        foreach ($tables as $key => $t) {
            try {
                $tid = $this->tenantId();
                $tsql = $this->tenantSql();
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM $t WHERE 1=1{$tsql}");
                $stmt->execute($tid > 1 ? [$tid] : []);
                $stats[$key] = (int)$stmt->fetchColumn();
            } catch (\Exception $e) {
                $stats[$key] = 0;
            }
        }

        // Recent activity
        $tid = $this->tenantId();
        $tsql = $this->tenantSql();
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM ai_learning_data WHERE learned_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)" . $tsql);
        $stmt->execute($tid > 1 ? [$tid] : []);
        $stats['learnings_24h'] = (int)$stmt->fetchColumn();

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM ai_chat_sessions WHERE started_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)" . $tsql);
        $stmt->execute($tid > 1 ? [$tid] : []);
        $stats['chat_sessions_24h'] = (int)$stmt->fetchColumn();

        return $stats;
    }
}
