<?php
/**
 * IntentDetector - NLP-based intent classification
 * Supports Hindi + English
 * No external API - uses pattern matching + scoring
 */

namespace App\Services\AI;

use PDO;
use \App\Traits\ServiceTenantTrait;

class IntentDetector
{
    use \App\Traits\ServiceTenantTrait;

    private $db;
    private $pdo;

    // Common real estate intents with multi-language patterns (English, Hindi, Hinglish)
    private array $defaultIntents = [
        'buy_property' => [
            'en' => ['buy', 'purchase', 'want to buy', 'looking to buy', 'need a property', 'want a house', 'want flat', 'want plot', 'looking for', 'property details', 'property info'],
            'hi' => ['खरीदना', 'खरीद', 'लेना है', 'चाहिए', 'घर चाहिए', 'प्लाट चाहिए', 'फ्लैट चाहिए'],
            'hi_lat' => ['khareedna', 'khareed', 'lena hai', 'ghar chahiye', 'plot chahiye', 'flat chahiye', 'lenge', 'chahiye mujhe', 'mujhe chahiye', 'mujhe plot', 'mujhe flat', 'mujhe ghar', 'chahiye', 'property details', 'details chahiye']
        ],
        'sell_property' => [
            'en' => ['sell', 'want to sell', 'have property to sell', 'list my property'],
            'hi' => ['बेचना', 'बेचना है', 'बेचना चाहता', 'बेचनी है', 'प्रॉपर्टी बेचनी'],
            'hi_lat' => ['bechna', 'bechna hai', 'bechna chahta', 'bechni hai', 'property bechni']
        ],
        'rent_property' => [
            'en' => ['rent', 'rental', 'on rent', 'for rent', 'lease'],
            'hi' => ['किराए', 'किराये', 'रेंट', 'लीज', 'किराए पर'],
            'hi_lat' => ['kirae', 'kiraaye', 'rent', 'lease', 'kirae par']
        ],
        'site_visit' => [
            'en' => ['site visit', 'visit property', 'see the property', 'schedule visit', 'book visit'],
            'hi' => ['साइट विजिट', 'विजिट', 'देखना है', 'मिलने', 'आना चाहता'],
            'hi_lat' => ['site visit', 'visit', 'dekhna hai', 'milne', 'aana chahta', 'book karo', 'schedule karo']
        ],
        'price_inquiry' => [
            'en' => ['price', 'cost', 'how much', 'rate', 'pricing', 'expensive', 'cheap'],
            'hi' => ['कीमत', 'दाम', 'कितना', 'रेट', 'महंगा', 'सस्ता', 'प्राइस'],
            'hi_lat' => ['keemat', 'daam', 'kitna', 'rate', 'mahnga', 'sasta', 'price', 'kitne ka', 'kitna ka']
        ],
        'loan' => [
            'en' => ['loan', 'home loan', 'mortgage', 'finance', 'emi'],
            'hi' => ['लोन', 'होम लोन', 'कर्ज', 'फाइनेंस', 'ईएमआई'],
            'hi_lat' => ['loan', 'home loan', 'karz', 'finance', 'emi', 'kist']
        ],
        'legal_help' => [
            'en' => ['legal', 'lawyer', 'agreement', 'registry', 'documentation'],
            'hi' => ['कानूनी', 'वकील', 'रजिस्ट्री', 'डॉक्यूमेंट', 'कागजात'],
            'hi_lat' => ['kanooni', 'vakil', 'registry', 'document', 'kagazat', 'agreement']
        ],
        'greeting' => [
            'en' => ['hello', 'hey', 'namaste', 'good morning', 'good evening'],
            'hi' => ['नमस्ते', 'नमस्कार', 'हैलो', 'हाय'],
            'hi_lat' => ['namaste', 'namaskar', 'hello', 'hey', 'kaise ho', 'kya haal', 'kaise hain', 'kya bolte']
        ],
        'thanks' => [
            'en' => ['thanks', 'thank you', 'thx', 'appreciated'],
            'hi' => ['धन्यवाद', 'शुक्रिया', 'थैंक्स'],
            'hi_lat' => ['dhanyawad', 'shukriya', 'thanks', 'thx']
        ],
        'goodbye' => [
            'en' => ['bye', 'goodbye', 'see you', 'talk later'],
            'hi' => ['अलविदा', 'बाय', 'फिर मिलेंगे'],
            'hi_lat' => ['alvida', 'bye', 'phir milenge', 'chalta hoon']
        ]
    ];

    public function __construct($db)
    {
        $this->db = $db;
        $this->pdo = is_object($db) && method_exists($db, 'getPdo') ? $db->getPdo() : $db;
        $this->seedDefaultIntents();
    }

    /**
     * Detect intent from user message
     * Returns intent name, confidence, language
     */
    public function detect(string $message): array
    {
        $message = strtolower(trim($message));
        $language = $this->detectLanguage($message);

        $scores = [];

        // Check DB patterns first
        $stmt = $this->db->prepare("
            SELECT intent_name, pattern_text, pattern_type, weight, hit_count
            FROM ai_intent_patterns
            WHERE is_active = 1 AND (language = ? OR language = 'en'){$this->tenantSql()}
        ");
        $stmt->execute(array_merge([$language], $this->tenantId() > 1 ? [$this->tenantId()] : []));
        $dbPatterns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($dbPatterns as $p) {
            $score = $this->matchPattern($message, $p['pattern_text'], $p['pattern_type']);
            if ($score > 0) {
                $scores[$p['intent_name']] = ($scores[$p['intent_name']] ?? 0) + $score * (float)$p['weight'];
            }
        }

        // Fall back to default patterns
        foreach ($this->defaultIntents as $intent => $langs) {
            $patterns = $langs[$language] ?? $langs['en'] ?? [];
            foreach ($patterns as $p) {
                if (stripos($message, strtolower($p)) !== false) {
                    $scores[$intent] = ($scores[$intent] ?? 0) + 0.7;
                }
            }
            
            // Also check Hinglish patterns if language is English but message has Hinglish words
            if ($language === 'en' && isset($langs['hi_lat'])) {
                foreach ($langs['hi_lat'] as $p) {
                    if (stripos($message, strtolower($p)) !== false) {
                        $scores[$intent] = ($scores[$intent] ?? 0) + 0.8; // Higher weight for Hinglish
                    }
                }
            }
        }

        if (empty($scores)) {
            return ['intent' => 'unknown', 'confidence' => 0.0, 'language' => $language];
        }

        arsort($scores);
        $topIntent = array_key_first($scores);
        $confidence = min(1.0, $scores[$topIntent]);

        return [
            'intent' => $topIntent,
            'confidence' => $confidence,
            'language' => $language,
            'all_scores' => $scores
        ];
    }

    /**
     * Add new learned pattern
     */
    public function learn(string $intent, string $pattern, string $type = 'keyword', float $weight = 1.0, string $language = 'en'): int
    {
        $tenantData = $this->tenantInsertData();
        $tenantCols = array_keys($tenantData);
        $tenantVals = array_values($tenantData);
        $columns = array_merge(['intent_name', 'pattern_text', 'pattern_type', 'weight', 'language'], $tenantCols);
        $values  = array_merge([$intent, $pattern, $type, $weight, $language], $tenantVals);
        $colStr = implode(', ', $columns);
        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $stmt = $this->db->prepare("
            INSERT INTO ai_intent_patterns ($colStr)
            VALUES ($placeholders)
            ON DUPLICATE KEY UPDATE weight = weight + 0.1, hit_count = hit_count + 1
        ");
        $stmt->execute($values);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Record successful match for reinforcement
     */
    public function reinforce(int $patternId, bool $success): void
    {
        $col = $success ? 'success_count' : 'hit_count';
        $stmt = $this->db->prepare("UPDATE ai_intent_patterns SET $col = $col + 1 WHERE id = ?{$this->tenantSql()}");
        $params = [$patternId];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $stmt->execute($params);
    }

    /**
     * Seed default intents on first run
     */
    private function seedDefaultIntents(): void
    {
        $tid = $this->tenantId();
        $sql = "SELECT COUNT(*) FROM ai_intent_patterns WHERE language = 'en'" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $stmt = $this->db->prepare($sql);
        $stmt->execute($tid > 1 ? [$tid] : []);
        if ((int)$stmt->fetchColumn() > 0) return;

        foreach ($this->defaultIntents as $intent => $langs) {
            foreach ($langs as $lang => $patterns) {
                foreach ($patterns as $p) {
                    try {
                        $tenantData = $this->tenantInsertData();
                        $columns = array_merge(['intent_name', 'pattern_text', 'pattern_type', 'language', 'weight'], array_keys($tenantData));
                        $values  = array_merge([$intent, $p, 'keyword', $lang, 1.0], array_values($tenantData));
                        $colStr = implode(', ', $columns);
                        $placeholders = implode(', ', array_fill(0, count($values), '?'));
                        $ins = $this->db->prepare("INSERT INTO ai_intent_patterns ($colStr) VALUES ($placeholders)");
                        $ins->execute($values);
                    } catch (\Exception $e) {
                        error_log($e->getMessage());
                    }
                }
            }
        }
    }

    private function matchPattern(string $text, string $pattern, string $type): float
    {
        $text = strtolower($text);
        $pattern = strtolower($pattern);
        return match ($type) {
            'regex' => @preg_match($pattern, $text) ? 1.0 : 0.0,
            'exact' => $text === $pattern ? 1.0 : 0.0,
            'phrase' => stripos($text, $pattern) !== false ? 0.9 : 0.0,
            default => stripos($text, $pattern) !== false ? 0.7 : 0.0, // keyword
        };
    }

    private function detectLanguage(string $text): string
    {
        // Detect Devanagari
        if (preg_match('/[\x{0900}-\x{097F}]/u', $text)) return 'hi';
        return 'en';
    }
}
