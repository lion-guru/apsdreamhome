<?php

namespace App\Services\AI\Modules;

use PDO;

/**
 * NLPProcessor - Handles natural language processing, intent recognition, entity extraction,
 * and sentiment analysis. Supports Hindi + English mixed text (Hinglish).
 */
class NLPProcessor
{
    private $db;

    private static $intents = [
        'property_inquiry'   => ['price', 'cost', 'how much', 'details', 'info', 'brochure', 'kitne', 'kimat', 'daam', 'jhankar', 'malum'],
        'investment'         => ['invest', 'return', 'roi', 'profit', 'yield', 'nivesh', 'munafa', 'bachat'],
        'site_visit'         => ['visit', 'meet', 'schedule', 'book', 'see', 'dekhna', 'aana', 'milna', 'visit karo'],
        'location'           => ['where', 'place', 'area', 'locality', 'near', 'kahan', 'kaha', 'paas', 'door'],
        'amenities'          => ['facility', 'gym', 'pool', 'parking', 'security', 'club', 'garden', 'play'],
        'emi_query'          => ['emi', 'installment', 'monthly', 'payment', 'kist', 'har mahine'],
        'booking'            => ['book', 'reserve', 'advance', 'token', 'registry', 'booking'],
        'complaint'          => ['complaint', 'problem', 'issue', 'shikayat', 'pareshani', 'girvi'],
        'greeting'           => ['hi', 'hello', 'hey', 'namaste', 'good morning', 'good evening', 'sup'],
        'documents'          => ['document', 'paper', 'agreement', 'registry', 'nOC', 'rera', 'kagaz'],
        'legal'              => ['legal', 'law', 'court', 'permission', 'approved', 'kanooni'],
        'colony'             => ['colony', 'phase', 'block', 'plot', 'layout', 'sector', 'mohalla'],
    ];

    private static $positiveWords = [
        'good', 'great', 'excellent', 'happy', 'interested', 'best', 'nice', 'awesome',
        'accha', 'bahut achha', 'badhiya', 'shandar', 'pasand', 'chahta', 'chahunga',
    ];

    private static $negativeWords = [
        'bad', 'poor', 'unhappy', 'not good', 'expensive', 'late', 'issue', 'worst',
        'bura', 'mahanga', 'problem', 'pareshani', 'galat', 'nahi', 'mat', 'dar',
    ];

    public function __construct($db = null)
    {
        $this->db = $db;
    }

    /**
     * Full NLP analysis of text
     */
    public function analyze(string $text, array $options = []): array
    {
        $lower = mb_strtolower($text, 'UTF-8');

        return [
            'intent'       => $this->recognizeIntent($lower),
            'sentiment'    => $this->analyzeSentiment($lower),
            'entities'     => $this->extractEntities($text, $lower),
            'is_strategic' => $this->isStrategic($lower),
            'complexity'   => $this->calculateComplexity($text),
            'language'     => $this->detectLanguage($lower),
            'keywords'     => $this->extractKeywords($lower),
            'raw_text'     => $text,
        ];
    }

    /**
     * Recognize user intent from text
     */
    public function recognizeIntent(string $text): array
    {
        $bestMatch = ['name' => 'other', 'confidence' => 0.4];
        $scores = [];

        foreach (self::$intents as $intent => $keywords) {
            $hits = 0;
            foreach ($keywords as $kw) {
                if (mb_strpos($text, $kw, 0, 'UTF-8') !== false) {
                    $hits++;
                }
            }
            if ($hits > 0) {
                $scores[$intent] = min(0.5 + ($hits * 0.15), 0.98);
            }
        }

        arsort($scores);
        if (!empty($scores)) {
            $top = key($scores);
            $bestMatch = ['name' => $top, 'confidence' => $scores[$top]];
        }

        return $bestMatch;
    }

    /**
     * Sentiment analysis
     */
    public function analyzeSentiment(string $text): array
    {
        $posCount = 0;
        $negCount = 0;

        foreach (self::$positiveWords as $w) {
            if (mb_strpos($text, $w, 0, 'UTF-8') !== false) $posCount++;
        }
        foreach (self::$negativeWords as $w) {
            if (mb_strpos($text, $w, 0, 'UTF-8') !== false) $negCount++;
        }

        $total = $posCount + $negCount;
        if ($total === 0) return ['label' => 'neutral', 'score' => 0.5, 'positive' => 0, 'negative' => 0];

        $score = $posCount / $total;
        if ($posCount > $negCount) return ['label' => 'positive', 'score' => round($score, 2), 'positive' => $posCount, 'negative' => $negCount];
        if ($negCount > $posCount) return ['label' => 'negative', 'score' => round(1 - $score, 2), 'positive' => $posCount, 'negative' => $negCount];
        return ['label' => 'neutral', 'score' => 0.5, 'positive' => $posCount, 'negative' => $negCount];
    }

    /**
     * Extract named entities: monetary values, property types, locations, phone, email
     */
    public function extractEntities(string $original, string $lower = ''): array
    {
        $lower = $lower ?: mb_strtolower($original, 'UTF-8');
        $entities = [
            'monetary'      => [],
            'property_type' => [],
            'location'      => [],
            'phone'         => [],
            'email'         => [],
            'area'          => [],
        ];

        // Monetary values
        if (preg_match_all('/(\d[\d,]*\.?\d*)\s*(lakh|lac|crore|cr|k|hundred|thousand)/i', $original, $m)) {
            $entities['monetary'] = array_unique($m[0]);
        }
        if (preg_match_all('/₹\s*[\d,]+/u', $original, $m)) {
            $entities['monetary'] = array_merge($entities['monetary'], $m[0]);
        }

        // Property types
        $types = ['flat', 'villa', 'plot', 'land', 'shop', 'office', 'apartment', 'house', 'bungalow',
                   'godown', 'warehouse', 'factory', 'commercial', 'residential', 'industrial'];
        foreach ($types as $type) {
            if (mb_strpos($lower, $type, 0, 'UTF-8') !== false) {
                $entities['property_type'][] = $type;
            }
        }

        // Area (sqft, sqm, acres, bigha, gaj)
        if (preg_match_all('/(\d[\d,]*\.?\d*)\s*(sq\.?\s*ft|sqft|sqm|acre|acres|bigha|gaj|square\s*(feet|meter))/i', $original, $m)) {
            $entities['area'] = array_unique($m[0]);
        }

        // Phone numbers (Indian format)
        if (preg_match_all('/(?:\+91[\s-]?)?\d{5}[\s-]?\d{5}/', $original, $m)) {
            $entities['phone'] = $m[0];
        }

        // Email
        if (preg_match_all('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $original, $m)) {
            $entities['email'] = $m[0];
        }

        // Common Gorakhpur/UP locations
        $locations = ['gorakhpur', 'lucknow', 'noida', 'delhi', 'varanasi', 'patna', 'kolkata',
                       'ayodhya', 'prayagraj', 'kanpur', 'jaunpur', 'deoria', 'basti', 'maharajganj'];
        foreach ($locations as $loc) {
            if (mb_strpos($lower, $loc, 0, 'UTF-8') !== false) {
                $entities['location'][] = $loc;
            }
        }

        return $entities;
    }

    /**
     * Check if text is about strategic/business planning
     */
    public function isStrategic(string $text): bool
    {
        $words = ['future', 'plan', 'expand', 'growth', 'strategy', 'roadmap', 'forecast',
                   'agla', 'vistar', 'yojana', 'bhavishya', 'vikas'];
        foreach ($words as $w) {
            if (mb_strpos($text, $w, 0, 'UTF-8') !== false) return true;
        }
        return false;
    }

    /**
     * Calculate text complexity
     */
    public function calculateComplexity(string $text): string
    {
        $wordCount = str_word_count($text);
        if ($wordCount > 30) return 'high';
        if ($wordCount > 12) return 'medium';
        return 'low';
    }

    /**
     * Detect language (Hindi/Hinglish/English)
     */
    public function detectLanguage(string $text): string
    {
        $hindiPattern = '/[\x{0900}-\x{097F}]/u';
        if (preg_match($hindiPattern, $text)) return 'hindi';

        $hinglishWords = ['hai', 'ka', 'ke', 'ki', 'ko', 'se', 'me', 'ho', 'nahi', 'kya', 'aur',
                           'yah', 'woh', 'yahan', 'wahan', 'kahan', 'kab', 'kaise', 'achha', 'bura'];
        $hits = 0;
        foreach ($hinglishWords as $w) {
            if (preg_match('/\b' . $w . '\b/i', $text)) $hits++;
        }
        if ($hits >= 2) return 'hinglish';

        return 'english';
    }

    /**
     * Extract important keywords from text
     */
    public function extractKeywords(string $text, int $limit = 10): array
    {
        $stopWords = ['the', 'a', 'an', 'is', 'are', 'was', 'were', 'be', 'been', 'being',
                       'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could',
                       'should', 'may', 'might', 'shall', 'can', 'to', 'of', 'in', 'for',
                       'on', 'with', 'at', 'by', 'from', 'as', 'into', 'through', 'during',
                       'before', 'after', 'above', 'below', 'between', 'out', 'off', 'over',
                       'under', 'again', 'further', 'then', 'once', 'and', 'but', 'or', 'nor',
                       'not', 'no', 'so', 'very', 'just', 'than', 'too', 'also', 'ka', 'ke',
                       'ki', 'ko', 'se', 'me', 'hai', 'ho', 'nahi', 'kya', 'aur'];

        preg_match_all('/\b[a-zA-Z]{3,}\b/', $text, $m);
        $words = array_count_values($m[0]);
        arsort($words);

        $keywords = [];
        foreach ($words as $word => $count) {
            if (!in_array(mb_strtolower($word, 'UTF-8'), $stopWords) && $count >= 1) {
                $keywords[] = ['word' => $word, 'count' => $count];
                if (count($keywords) >= $limit) break;
            }
        }
        return $keywords;
    }

    /**
     * Generate a summary score for the text (useful for lead scoring)
     */
    public function scoreLeadIntent(string $text): array
    {
        $analysis = $this->analyze($text);
        $score = 0;

        // High-value intents boost score
        $highValueIntents = ['property_inquiry', 'investment', 'site_visit', 'booking', 'emi_query'];
        if (in_array($analysis['intent']['name'], $highValueIntents)) {
            $score += 30;
        }

        // Monetary values indicate serious buyer
        if (!empty($analysis['entities']['monetary'])) {
            $score += 20;
        }

        // Area mentions indicate specific requirements
        if (!empty($analysis['entities']['area'])) {
            $score += 10;
        }

        // Positive sentiment
        if ($analysis['sentiment']['label'] === 'positive') {
            $score += 15;
        }

        // Strategic intent (long-term planning)
        if ($analysis['is_strategic']) {
            $score += 10;
        }

        // Contact info provided
        if (!empty($analysis['entities']['phone']) || !empty($analysis['entities']['email'])) {
            $score += 15;
        }

        return [
            'score' => min($score, 100),
            'analysis' => $analysis,
        ];
    }
}
