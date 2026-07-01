<?php
/**
 * RAGAgent - Retrieval-Augmented Generation for APS Dream Home
 * 
 * No external API needed. Uses:
 * - TF-IDF keyword matching for retrieval
 * - Live property/plot/colony data from DB
 * - Knowledge base from ai_knowledge_base
 * - Conversation context for follow-ups
 * 
 * "Chota packet bada dhamaka" — small code, huge value
 */

namespace App\Services\AI;

use App\Core\Database\Database;

class RAGAgent
{
    private $db;
    private $stopWords;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->stopWords = [
            'the', 'a', 'an', 'is', 'are', 'was', 'were', 'be', 'been', 'being',
            'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could',
            'should', 'may', 'might', 'shall', 'can', 'to', 'of', 'in', 'for',
            'on', 'with', 'at', 'by', 'from', 'up', 'about', 'into', 'through',
            'kya', 'hai', 'hain', 'ka', 'ki', 'ke', 'ko', 'mein', 'se', 'par',
            'hai', 'ho', 'hoga', 'tha', 'tha', 'hun', 'hoon', 'nahi', 'haan'
        ];
    }

    /**
     * Main entry: answer a question using RAG
     */
    public function answer(string $question, ?int $userId = null): array
    {
        $startTime = microtime(true);

        // 1. Extract intent and entities from question
        $parsed = $this->parseQuestion($question);

        // 2. Retrieve relevant context from multiple sources
        $context = $this->retrieveContext($parsed);

        // 3. Generate answer using retrieved context
        $answer = $this->generateAnswer($question, $parsed, $context);

        // 4. Track usage
        $this->trackUsage($question, $answer, $context);

        $responseTime = round((microtime(true) - $startTime) * 1000);

        return [
            'success' => true,
            'answer' => $answer['text'],
            'sources' => $answer['sources'],
            'confidence' => $answer['confidence'],
            'response_time_ms' => $responseTime,
            'context_used' => count($context),
            'intent' => $parsed['intent'],
            'entities' => $parsed['entities']
        ];
    }

    /**
     * Parse question to extract intent and entities
     */
    private function parseQuestion(string $question): array
    {
        $lower = strtolower(trim($question));
        $words = $this->tokenize($lower);

        $intent = 'general';
        $entities = [];

        // Intent detection
        $intentMap = [
            'pricing' => ['price', 'cost', 'rate', 'kitna', 'kitne', 'dam', 'kimat', 'budget', 'lakh', 'crore'],
            'property_search' => ['property', 'plot', 'flat', 'villa', 'house', 'apartment', 'dikhao', 'search', 'find', 'looking'],
            'location' => ['location', 'where', 'kahan', 'address', 'map', 'direction', 'kaha'],
            'colony' => ['colony', 'colony', 'nagri', 'suryoday', 'raghunath', 'braj', 'budh'],
            'amenities' => ['amenity', 'amenities', 'facility', 'facilities', 'feature', 'parking', 'security', 'gym'],
            'booking' => ['book', 'booking', 'reserve', 'register', 'kharid', 'buy', 'purchase'],
            'contact' => ['contact', 'phone', 'call', 'email', 'whatsapp', 'number'],
            'loan' => ['loan', 'finance', 'emi', 'bank', 'home loan', 'interest'],
            'commission' => ['commission', 'earn', 'income', 'affiliate', 'referral'],
            'visit' => ['visit', 'site visit', 'dekhna', 'aana', 'schedule'],
            'status' => ['status', 'available', 'sold', 'booked', 'kab', 'when']
        ];

        foreach ($intentMap as $intentName => $keywords) {
            foreach ($keywords as $kw) {
                if (strpos($lower, $kw) !== false) {
                    $intent = $intentName;
                    break 2;
                }
            }
        }

        // Entity extraction
        // Budget
        if (preg_match('/(\d+(?:\.\d+)?)\s*(?:lakh|lakhs?|crore|crores?|cr|l|c)/i', $lower, $m)) {
            $val = (float)$m[1];
            if (stripos($m[0], 'crore') !== false || stripos($m[0], 'cr') !== false) {
                $val *= 100;
            }
            $entities['budget_lakhs'] = $val;
        }

        // Colony name
        $colonyNames = ['suryoday', 'raghunath', 'braj', 'radha', 'budh', 'bihar'];
        foreach ($colonyNames as $cn) {
            if (strpos($lower, $cn) !== false) {
                $entities['colony'] = $cn;
                break;
            }
        }

        // Property type
        $types = ['plot', 'flat', 'villa', 'house', 'apartment', 'commercial', 'shop', 'office'];
        foreach ($types as $t) {
            if (strpos($lower, $t) !== false) {
                $entities['property_type'] = $t;
                break;
            }
        }

        // Area size
        if (preg_match('/(\d+)\s*(?:sq\.?\s*ft|sqft|square\s*feet)/i', $lower, $m)) {
            $entities['area_sqft'] = (int)$m[1];
        }

        return ['intent' => $intent, 'entities' => $entities, 'tokens' => $words];
    }

    /**
     * Retrieve context from multiple DB sources
     */
    private function retrieveContext(array $parsed): array
    {
        $context = [];

        // Source 1: Knowledge base (ai_knowledge_base)
        $kbResults = $this->searchKnowledgeBase($parsed);
        $context = array_merge($context, $kbResults);

        // Source 2: Live property data
        $propertyResults = $this->searchProperties($parsed);
        $context = array_merge($context, $propertyResults);

        // Source 3: Live plot data
        $plotResults = $this->searchPlots($parsed);
        $context = array_merge($context, $plotResults);

        // Source 4: Colony data
        $colonyResults = $this->searchColonies($parsed);
        $context = array_merge($context, $colonyResults);

        // Source 5: Pricing from price_history
        $priceResults = $this->searchPricing($parsed);
        $context = array_merge($context, $priceResults);

        return $context;
    }

    /**
     * Search knowledge base using TF-IDF similarity
     */
    private function searchKnowledgeBase(array $parsed): array
    {
        try {
            $rows = $this->db->fetchAll(
                "SELECT id, category, question_pattern, answer, usage_count, effectiveness_score
                 FROM ai_knowledge_base
                 ORDER BY usage_count DESC"
            );

            $results = [];
            $queryTokens = $parsed['tokens'];

            foreach ($rows as $row) {
                $patternTokens = $this->tokenize(strtolower($row['question_pattern']));
                $similarity = $this->cosineSimilarity($queryTokens, $patternTokens);

                if ($similarity > 0.2) {
                    $results[] = [
                        'source' => 'knowledge_base',
                        'id' => $row['id'],
                        'question' => $row['question_pattern'],
                        'answer' => $row['answer'],
                        'category' => $row['category'],
                        'relevance' => round($similarity, 3)
                    ];
                }
            }

            // Sort by relevance, take top 3
            usort($results, fn($a, $b) => $b['relevance'] <=> $a['relevance']);
            return array_slice($results, 0, 3);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Search live property data
     */
    private function searchProperties(array $parsed): array
    {
        try {
            $conditions = ["p.status = 'active'"];
            $params = [];

            if (!empty($parsed['entities']['property_type'])) {
                $conditions[] = "p.type = ?";
                $params[] = $parsed['entities']['property_type'];
            }

            if (!empty($parsed['entities']['budget_lakhs'])) {
                $budget = $parsed['entities']['budget_lakhs'] * 100000;
                $conditions[] = "p.price BETWEEN ? AND ?";
                $params[] = $budget * 0.5;
                $params[] = $budget * 2;
            }

            $where = implode(' AND ', $conditions);
            $rows = $this->db->fetchAll(
                "SELECT p.id, p.title, p.type, p.price, p.location, p.area_sqft, p.bedrooms, p.bathrooms
                 FROM properties p
                 WHERE $where
                 ORDER BY p.featured DESC, p.updated_at DESC
                 LIMIT 5",
                $params
            );

            $results = [];
            foreach ($rows as $row) {
                $results[] = [
                    'source' => 'property',
                    'id' => $row['id'],
                    'title' => $row['title'],
                    'type' => $row['type'],
                    'price' => $row['price'],
                    'location' => $row['location'],
                    'area' => $row['area_sqft'],
                    'details' => "{$row['bedrooms']}BHK {$row['type']} at {$row['location']}, ₹" . number_format($row['price']),
                    'relevance' => 0.8
                ];
            }

            return $results;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Search live plot data
     */
    private function searchPlots(array $parsed): array
    {
        try {
            $conditions = ["p.status = 'available'"];
            $params = [];

            if (!empty($parsed['entities']['colony'])) {
                $colonyMap = [
                    'suryoday' => 2, 'raghunath' => 4,
                    'braj' => 3, 'radha' => 3, 'budh' => 5, 'bihar' => 5
                ];
                $colonyName = $parsed['entities']['colony'];
                if (isset($colonyMap[$colonyName])) {
                    $conditions[] = "p.colony_id = ?";
                    $params[] = $colonyMap[$colonyName];
                }
            }

            if (!empty($parsed['entities']['area_sqft'])) {
                $area = $parsed['entities']['area_sqft'];
                $conditions[] = "p.area_sqft BETWEEN ? AND ?";
                $params[] = $area * 0.7;
                $params[] = $area * 1.5;
            }

            $where = implode(' AND ', $conditions);
            $rows = $this->db->fetchAll(
                "SELECT p.id, p.plot_number, p.area_sqft, p.colony_id, c.name as colony_name
                 FROM plots p
                 LEFT JOIN colonies c ON p.colony_id = c.id
                 WHERE $where
                 ORDER BY p.area_sqft ASC
                 LIMIT 5",
                $params
            );

            $results = [];
            foreach ($rows as $row) {
                $results[] = [
                    'source' => 'plot',
                    'id' => $row['id'],
                    'plot_number' => $row['plot_number'],
                    'area' => $row['area_sqft'],
                    'colony' => $row['colony_name'] ?? "Colony #{$row['colony_id']}",
                    'details' => "Plot {$row['plot_number']} ({$row['area_sqft']} sqft) in {$row['colony_name']}",
                    'relevance' => 0.9
                ];
            }

            return $results;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Search colony data
     */
    private function searchColonies(array $parsed): array
    {
        try {
            $rows = $this->db->fetchAll("SELECT id, name, is_active FROM colonies WHERE is_active = 1");

            $results = [];
            $queryLower = strtolower(implode(' ', $parsed['tokens']));

            foreach ($rows as $row) {
                $nameLower = strtolower($row['name']);
                // Check if colony name matches any query token
                $match = false;
                foreach ($parsed['tokens'] as $token) {
                    if (strlen($token) > 2 && strpos($nameLower, $token) !== false) {
                        $match = true;
                        break;
                    }
                }

                if ($match || !empty($parsed['entities']['colony'])) {
                    // Count available plots
                    $plotCount = $this->db->fetch(
                        "SELECT COUNT(*) as cnt FROM plots WHERE colony_id = ? AND status = 'available'",
                        [$row['id']]
                    );

                    $results[] = [
                        'source' => 'colony',
                        'id' => $row['id'],
                        'name' => $row['name'],
                        'status' => $row['is_active'] ? 'active' : 'inactive',
                        'available_plots' => $plotCount['cnt'] ?? 0,
                        'details' => "{$row['name']}: " . ($plotCount['cnt'] ?? 0) . " plots available",
                        'relevance' => 0.85
                    ];
                }
            }

            return $results;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Search pricing data
     */
    private function searchPricing(array $parsed): array
    {
        if ($parsed['intent'] !== 'pricing') return [];

        try {
            $rows = $this->db->fetchAll(
                "SELECT p.plot_number, p.area_sqft, c.name as colony_name, ph.old_price, ph.new_price, ph.effective_date
                 FROM price_history ph
                 JOIN plots p ON ph.plot_id = p.id
                 LEFT JOIN colonies c ON p.colony_id = c.id
                 ORDER BY ph.effective_date DESC
                 LIMIT 5"
            );

            $results = [];
            foreach ($rows as $row) {
                $results[] = [
                    'source' => 'pricing',
                    'plot' => $row['plot_number'],
                    'colony' => $row['colony_name'],
                    'area' => $row['area_sqft'],
                    'current_price' => $row['new_price'],
                    'previous_price' => $row['old_price'],
                    'date' => $row['effective_date'],
                    'details' => "{$row['plot_number']} ({$row['area_sqft']}sqft) in {$row['colony_name']}: ₹" . number_format($row['new_price']),
                    'relevance' => 0.7
                ];
            }

            return $results;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Generate answer using retrieved context
     */
    private function generateAnswer(string $question, array $parsed, array $context): array
    {
        if (empty($context)) {
            return [
                'text' => "Mujhe is sawaal ka jawab abhi nahi mil raha. Kya aap specific colony, property type, ya budget bata sakte hain? Main aapki madad karunga!",
                'sources' => [],
                'confidence' => 0.1
            ];
        }

        $sources = [];
        $answerParts = [];
        $maxRelevance = 0;

        // Group context by source type
        $bySource = [];
        foreach ($context as $item) {
            $bySource[$item['source']][] = $item;
            $maxRelevance = max($maxRelevance, $item['relevance']);
        }

        // Knowledge base answers (highest priority)
        if (!empty($bySource['knowledge_base'])) {
            $kb = $bySource['knowledge_base'][0];
            $answerParts[] = $kb['answer'];
            $sources[] = "Knowledge Base: {$kb['question']}";
        }

        // Property listings
        if (!empty($bySource['property'])) {
            $props = array_slice($bySource['property'], 0, 3);
            $propLines = [];
            foreach ($props as $p) {
                $propLines[] = "• {$p['details']}";
                $sources[] = "Property: {$p['title']}";
            }
            $answerParts[] = "Available Properties:\n" . implode("\n", $propLines);
        }

        // Plot listings
        if (!empty($bySource['plot'])) {
            $plots = array_slice($bySource['plot'], 0, 3);
            $plotLines = [];
            foreach ($plots as $p) {
                $plotLines[] = "• {$p['details']}";
                $sources[] = "Plot: {$p['plot_number']}";
            }
            $answerParts[] = "Available Plots:\n" . implode("\n", $plotLines);
        }

        // Colony info
        if (!empty($bySource['colony'])) {
            $colonies = array_slice($bySource['colony'], 0, 3);
            $colonyLines = [];
            foreach ($colonies as $c) {
                $colonyLines[] = "• {$c['details']}";
                $sources[] = "Colony: {$c['name']}";
            }
            $answerParts[] = "Colony Information:\n" . implode("\n", $colonyLines);
        }

        // Pricing info
        if (!empty($bySource['pricing'])) {
            $prices = array_slice($bySource['pricing'], 0, 3);
            $priceLines = [];
            foreach ($prices as $p) {
                $priceLines[] = "• {$p['details']}";
                $sources[] = "Pricing: {$p['plot']}";
            }
            $answerParts[] = "Pricing Details:\n" . implode("\n", $priceLines);
        }

        $finalAnswer = implode("\n\n", $answerParts);
        $confidence = min(0.95, $maxRelevance + 0.1);

        return [
            'text' => $finalAnswer,
            'sources' => $sources,
            'confidence' => round($confidence, 2)
        ];
    }

    /**
     * Tokenize text into meaningful words
     */
    private function tokenize(string $text): array
    {
        $words = preg_split('/\s+/', preg_replace('/[^a-z0-9\s]/', '', $text));
        return array_filter($words, function ($w) {
            return strlen($w) > 1 && !in_array($w, $this->stopWords);
        });
    }

    /**
     * Simple TF-IDF cosine similarity between two token arrays
     */
    private function cosineSimilarity(array $tokens1, array $tokens2): float
    {
        if (empty($tokens1) || empty($tokens2)) return 0;

        // Create term frequency vectors
        $allTerms = array_unique(array_merge($tokens1, $tokens2));
        $vec1 = [];
        $vec2 = [];

        foreach ($allTerms as $term) {
            $vec1[$term] = in_array($term, $tokens1) ? 1 : 0;
            $vec2[$term] = in_array($term, $tokens2) ? 1 : 0;
        }

        // Calculate cosine similarity
        $dot = 0;
        $norm1 = 0;
        $norm2 = 0;

        foreach ($allTerms as $term) {
            $dot += ($vec1[$term] ?? 0) * ($vec2[$term] ?? 0);
            $norm1 += ($vec1[$term] ?? 0) ** 2;
            $norm2 += ($vec2[$term] ?? 0) ** 2;
        }

        if ($norm1 == 0 || $norm2 == 0) return 0;

        return $dot / (sqrt($norm1) * sqrt($norm2));
    }

    /**
     * Track usage for learning
     */
    private function trackUsage(string $question, array $answer, array $context): void
    {
        try {
            // Update knowledge base usage counts
            foreach ($context as $item) {
                if ($item['source'] === 'knowledge_base') {
                    $this->db->execute(
                        "UPDATE ai_knowledge_base SET usage_count = usage_count + 1 WHERE id = ?",
                        [$item['id']]
                    );
                }
            }

            // Store in ai_chat_messages for learning
            $this->db->execute(
                "INSERT INTO ai_chat_messages (session_id, sender, message, detected_intent, confidence, entities, created_at)
                 VALUES (?, 'user', ?, ?, ?, ?, NOW())",
                [
                    session_id(),
                    $question,
                    'rag_query',
                    $answer['confidence'],
                    json_encode(['sources' => $answer['sources']])
                ]
            );

            $this->db->execute(
                "INSERT INTO ai_chat_messages (session_id, sender, message, detected_intent, confidence, created_at)
                 VALUES (?, 'bot', ?, ?, ?, NOW())",
                [
                    session_id(),
                    $answer['text'],
                    'rag_response',
                    $answer['confidence']
                ]
            );
        } catch (\Exception $e) {
            // Silent fail
        }
    }

    /**
     * Get stats about the RAG system
     */
    public function getStats(): array
    {
        try {
            $kbCount = $this->db->fetch("SELECT COUNT(*) as cnt FROM ai_knowledge_base")['cnt'] ?? 0;
            $propCount = $this->db->fetch("SELECT COUNT(*) as cnt FROM properties WHERE status = 'active'")['cnt'] ?? 0;
            $plotCount = $this->db->fetch("SELECT COUNT(*) as cnt FROM plots WHERE status = 'available'")['cnt'] ?? 0;
            $colonyCount = $this->db->fetch("SELECT COUNT(*) as cnt FROM colonies WHERE is_active = 1")['cnt'] ?? 0;

            return [
                'knowledge_base_entries' => $kbCount,
                'active_properties' => $propCount,
                'available_plots' => $plotCount,
                'active_colonies' => $colonyCount,
                'total_sources' => $kbCount + $propCount + $plotCount + $colonyCount
            ];
        } catch (\Exception $e) {
            return [];
        }
    }
}
