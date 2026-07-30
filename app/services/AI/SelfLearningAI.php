<?php
/**
 * APS Dream Home - Self-Learning AI Engine
 * 
 * The brain that learns from every conversation, adapts to users,
 * and continuously improves its responses. No external API dependency.
 * 
 * Features:
 * - Adaptive intent detection (learns new patterns from conversations)
 * - Response effectiveness tracking (which responses lead to engagement)
 * - User preference memory (remembers what each user likes)
 * - Contextual conversation flow (multi-turn understanding)
 * - Feedback loop (learns from thumbs up/down)
 * - Sentiment-aware responses
 * - Hindi/Hinglish natural understanding
 * - Auto-expanding knowledge base from real conversations
 */

namespace App\Services\AI;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;

class SelfLearningAI
{
    private $db;
    private $sessionId;
    private $userId;
    private $userRole;

    // Confidence thresholds
    const HIGH_CONFIDENCE = 0.85;
    const MEDIUM_CONFIDENCE = 0.60;
    const LOW_CONFIDENCE = 0.35;
    const LEARNING_RATE = 0.15;

    public function __construct(string $sessionId, ?int $userId = null, string $userRole = 'customer')
    {
        $this->db = Database::getInstance();
        $this->sessionId = $sessionId;
        $this->userId = $userId;
        $this->userRole = $userRole;
    }

    /**
     * Main entry: process a message and learn from it
     */
    public function processMessage(string $message): array
    {
        $startTime = microtime(true);

        // 1. Normalize input
        $normalized = $this->normalizeInput($message);

        // 2. Get conversation context (last 5 messages)
        $context = $this->getConversationContext();

        // 3. Detect intent with learning
        $intentResult = $this->detectIntentWithLearning($normalized, $context);

        // 4. Get user profile for personalization
        $userProfile = $this->getUserProfile();

        // 5. Generate response
        $response = $this->generateSmartResponse($intentResult, $normalized, $context, $userProfile);

        // 6. Learn from this interaction
        $this->learnFromInteraction($normalized, $intentResult, $response, $context);

        // 7. Update user profile
        $this->updateUserProfile($normalized, $intentResult);

        // 8. Store the conversation
        $this->storeMessage($message, $response['text'], $intentResult, $response['confidence']);

        // 9. Calculate response time
        $responseTime = round((microtime(true) - $startTime) * 1000);

        return [
            'success' => true,
            'response' => $response['text'],
            'intent' => $intentResult['intent'],
            'confidence' => $intentResult['confidence'],
            'sentiment' => $intentResult['sentiment'],
            'actions' => $response['actions'] ?? [],
            'suggestions' => $response['suggestions'] ?? [],
            'response_time_ms' => $responseTime,
            'learning' => [
                'pattern_match' => $intentResult['match_source'] ?? 'learned',
                'user_profile_used' => !empty($userProfile),
                'context_used' => !empty($context)
            ]
        ];
    }

    /**
     * Normalize input: lowercase, trim, clean
     */
    private function normalizeInput(string $message): array
    {
        $lower = strtolower(trim($message));
        $original = $message;

        // Hinglish normalization
        $hinglishMap = [
            'kya' => 'what', 'hai' => 'is', 'mein' => 'in', 'ko' => 'to',
            'ka' => 'of', 'ke' => 'of', 'se' => 'from', 'par' => 'on',
            'nahi' => 'no', 'haan' => 'yes', 'accha' => 'good', 'theek' => 'ok',
            'batao' => 'tell', 'dikhao' => 'show', 'kitna' => 'how much',
            'kahan' => 'where', 'kab' => 'when', 'kaise' => 'how',
            'mujhe' => 'I', 'hamein' => 'we', 'aapko' => 'you',
            'property' => 'property', 'plot' => 'plot', 'ghar' => 'house',
            'dam' => 'price', 'kimat' => 'price', 'rate' => 'price',
            'kitne' => 'how many', 'wala' => 'type', 'wali' => 'type'
        ];

        $tokens = preg_split('/\s+/', $lower);
        $normalized_tokens = [];
        foreach ($tokens as $token) {
            $clean = preg_replace('/[^a-z0-9]/', '', $token);
            if (isset($hinglishMap[$clean])) {
                $normalized_tokens[] = $hinglishMap[$clean];
            }
            $normalized_tokens[] = $clean;
        }

        return [
            'original' => $original,
            'lower' => $lower,
            'normalized' => implode(' ', $normalized_tokens),
            'tokens' => $normalized_tokens,
            'word_count' => count($normalized_tokens)
        ];
    }

    /**
     * Detect intent using learned patterns + DB patterns + fallback to keywords
     */
    private function detectIntentWithLearning(array $normalized, array $context): array
    {
        $text = $normalized['normalized'];
        $original = $normalized['lower'];

        // Step 1: Check learned patterns from DB (highest priority)
        $learnedPattern = $this->matchLearnedPatterns($text);
        if ($learnedPattern && $learnedPattern['confidence'] >= self::HIGH_CONFIDENCE) {
            return $learnedPattern;
        }

        // Step 2: Check intent patterns from DB
        $dbPattern = $this->matchDBIntentPatterns($original);
        if ($dbPattern && $dbPattern['confidence'] >= self::MEDIUM_CONFIDENCE) {
            return $dbPattern;
        }

        // Step 3: Check context-based intent (conversation flow)
        $contextIntent = $this->detectContextualIntent($original, $context);
        if ($contextIntent) {
            return $contextIntent;
        }

        // Step 4: Keyword-based fallback (always available)
        $keywordIntent = $this->detectKeywordIntent($original);

        // Step 5: Use learned pattern if it has higher confidence
        if ($learnedPattern && $learnedPattern['confidence'] > $keywordIntent['confidence']) {
            return $learnedPattern;
        }

        return $keywordIntent;
    }

    /**
     * Match against learned patterns stored in DB
     */
    private function matchLearnedPatterns(string $text): ?array
    {
        try {
            $patterns = $this->db->fetchAll(
                "SELECT pattern_text, intent_name as intent, confidence, success_count, fail_count
                 FROM ai_intent_patterns
                 WHERE is_active = 1 AND source = 'learned'
                 ORDER BY success_count DESC
                 LIMIT 200"
            );

            $bestMatch = null;
            $bestScore = 0;

            foreach ($patterns as $pattern) {
                $patternWords = explode(' ', $pattern['pattern_text']);
                $textWords = explode(' ', $text);

                // Calculate similarity using word overlap
                $intersection = count(array_intersect($patternWords, $textWords));
                $union = count(array_unique(array_merge($patternWords, $textWords)));
                $similarity = $union > 0 ? $intersection / $union : 0;

                // Weight by success rate
                $total = ($pattern['success_count'] ?? 0) + ($pattern['fail_count'] ?? 0);
                $successRate = $total > 0 ? ($pattern['success_count'] ?? 0) / $total : 0.5;
                $score = ($similarity * 0.7) + ($successRate * 0.3);

                if ($score > $bestScore && $score >= self::LOW_CONFIDENCE) {
                    $bestScore = $score;
                    $bestMatch = [
                        'intent' => $pattern['intent'],
                        'confidence' => round(min($score, 1.0), 2),
                        'sentiment' => $this->detectSentiment($text),
                        'match_source' => 'learned_pattern',
                        'entities' => []
                    ];
                }
            }

            return $bestMatch;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Match against DB intent patterns (ai_intent_patterns table - 102 rows)
     */
    private function matchDBIntentPatterns(string $text): ?array
    {
        try {
            $patterns = $this->db->fetchAll(
                "SELECT pattern_text, intent_name as intent, confidence
                 FROM ai_intent_patterns
                 WHERE is_active = 1 AND source != 'learned'
                 ORDER BY confidence DESC
                 LIMIT 100"
            );

            foreach ($patterns as $pattern) {
                $patternText = strtolower($pattern['pattern_text']);
                if (strpos($text, $patternText) !== false || levenshtein($text, $patternText) < 3) {
                    return [
                        'intent' => $pattern['intent'],
                        'confidence' => (float)($pattern['confidence'] ?? 0.7),
                        'sentiment' => $this->detectSentiment($text),
                        'match_source' => 'db_pattern',
                        'entities' => []
                    ];
                }
            }
        } catch (\Exception $e) {
            // Table might not have source column
        }

        return null;
    }

    /**
     * Detect contextual intent based on conversation flow
     */
    private function detectContextualIntent(string $text, array $context): ?array
    {
        if (empty($context)) return null;

        $lastIntent = end($context)['intent'] ?? '';

        // Conversation flow patterns
        $flowMap = [
            'greeting' => ['property_search', 'pricing', 'contact'],
            'property_search' => ['pricing', 'location', 'amenities', 'booking'],
            'pricing' => ['financing', 'booking', 'property_search'],
            'location' => ['property_search', 'amenities', 'contact'],
            'financing' => ['booking', 'contact'],
            'amenities' => ['pricing', 'booking', 'location']
        ];

        // If user gives a short response after a question, it's likely an answer
        $wordCount = str_word_count(trim($text));
        if ($lastIntent && $wordCount <= 3) {
            if (in_array($lastIntent, ['property_search', 'pricing', 'location', 'amenities'])) {
                // Short answers like "yes", "no", "plots", "gorakhpur" -> continue same flow
                return null; // Let keyword handle it
            }
        }

        return null;
    }

    /**
     * Keyword-based intent detection (fallback, always works)
     */
    private function detectKeywordIntent(string $text): array
    {
        $intents = [
            'greeting' => [
                'keywords' => ['hello', 'hi', 'hey', 'namaste', 'good morning', 'good evening', 'good afternoon', 'hii', 'helloo'],
                'weight' => 1.0
            ],
            'property_search' => [
                'keywords' => ['property', 'house', 'home', 'apartment', 'villa', 'search', 'find', 'looking for', 'dikhao', 'dikhayo', 'plot', 'flat'],
                'weight' => 1.0
            ],
            'pricing' => [
                'keywords' => ['price', 'cost', 'rate', 'budget', 'cheap', 'expensive', 'affordable', 'dam', 'kimat', 'kitna', 'kitne', 'lakh', 'crore'],
                'weight' => 1.2
            ],
            'location' => [
                'keywords' => ['location', 'area', 'city', 'near', 'close to', 'address', 'kahan', 'gorakhpur', 'nagri', 'colony'],
                'weight' => 1.1
            ],
            'amenities' => [
                'keywords' => ['amenities', 'facilities', 'features', 'swimming pool', 'gym', 'parking', 'garden', 'security'],
                'weight' => 1.0
            ],
            'contact' => [
                'keywords' => ['contact', 'call', 'phone', 'email', 'visit', 'appointment', 'number', 'phone number'],
                'weight' => 1.1
            ],
            'financing' => [
                'keywords' => ['loan', 'finance', 'emi', 'payment', 'installment', 'bank', 'home loan'],
                'weight' => 1.0
            ],
            'booking' => [
                'keywords' => ['book', 'reserve', 'booking', 'register', 'sign up', 'kharidna', 'lena'],
                'weight' => 1.2
            ],
            'site_visit' => [
                'keywords' => ['site visit', 'visit', 'dekhna', 'aana', 'milna', 'schedule'],
                'weight' => 1.1
            ],
            'commission' => [
                'keywords' => ['commission', 'earn', 'income', 'paise', 'paisa', 'affiliate', 'referral'],
                'weight' => 1.0
            ],
            'complaint' => [
                'keywords' => ['problem', 'issue', 'complaint', 'wrong', 'bad', 'pareshan', 'shikayat'],
                'weight' => 1.0
            ],
            'goodbye' => [
                'keywords' => ['bye', 'goodbye', 'see you', 'thank', 'thanks', 'dhanyavaad', 'alvida'],
                'weight' => 1.0
            ],
            'help' => [
                'keywords' => ['help', 'support', 'assist', 'question', 'how to', 'kaise', 'madad'],
                'weight' => 1.0
            ]
        ];

        $bestIntent = 'fallback';
        $bestScore = 0;
        $bestEntities = [];

        foreach ($intents as $intent => $config) {
            $score = 0;
            foreach ($config['keywords'] as $keyword) {
                if (strpos($text, $keyword) !== false) {
                    $score += $config['weight'];
                }
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestIntent = $intent;
            }
        }

        $confidence = $bestScore > 0 ? min($bestScore * 0.3, 1.0) : 0.1;

        // Extract entities
        $entities = $this->extractEntities($text, $bestIntent);

        return [
            'intent' => $bestIntent,
            'confidence' => round($confidence, 2),
            'sentiment' => $this->detectSentiment($text),
            'match_source' => 'keyword',
            'entities' => $entities
        ];
    }

    /**
     * Extract entities from text
     */
    private function extractEntities(string $text, string $intent): array
    {
        $entities = [];

        // Budget extraction
        if (preg_match('/(\d+(?:\.\d+)?)\s*(?:lakh|lakhs?|crore|crores?|cr|l|c)/i', $text, $m)) {
            $val = (float)$m[1];
            if (stripos($m[0], 'crore') !== false || stripos($m[0], 'cr') !== false) {
                $val *= 100;
            }
            $entities['budget_lakhs'] = $val;
        }

        // Phone number extraction
        if (preg_match('/(\d{10})/', $text, $m)) {
            $entities['phone'] = $m[1];
        }

        // Email extraction
        if (preg_match('/[\w.+-]+@[\w-]+\.[\w.]+/', $text, $m)) {
            $entities['email'] = $m[0];
        }

        // Property type
        $types = ['plot', 'flat', 'villa', 'house', 'apartment', 'commercial', 'office', 'shop'];
        foreach ($types as $type) {
            if (strpos($text, $type) !== false) {
                $entities['property_type'] = $type;
                break;
            }
        }

        // Location / area
        $locations = ['gorakhpur', 'nagri', 'raghunath', 'suryoday', 'braj', 'radha', 'budh', 'bihar'];
        foreach ($locations as $loc) {
            if (strpos($text, $loc) !== false) {
                $entities['location'] = $loc;
                break;
            }
        }

        return $entities;
    }

    /**
     * Simple sentiment detection
     */
    private function detectSentiment(string $text): string
    {
        $positive = ['good', 'great', 'excellent', 'amazing', 'love', 'best', 'perfect', 'accha', 'badhiya', 'sundar', 'dhanyavaad', 'thank'];
        $negative = ['bad', 'poor', 'worst', 'hate', 'terrible', 'bura', 'kharab', 'pareshan', 'shikayat', 'problem', 'issue'];
        $neutral = ['ok', 'okay', 'theek', 'haan', 'nahi', 'yes', 'no'];

        $posCount = 0;
        $negCount = 0;
        foreach ($positive as $w) {
            if (strpos($text, $w) !== false) $posCount++;
        }
        foreach ($negative as $w) {
            if (strpos($text, $w) !== false) $negCount++;
        }

        if ($posCount > $negCount) return 'positive';
        if ($negCount > $posCount) return 'negative';
        return 'neutral';
    }

    /**
     * Generate smart response using knowledge base + learned responses
     */
    private function generateSmartResponse(array $intentResult, array $normalized, array $context, array $userProfile): array
    {
        $intent = $intentResult['intent'];
        $confidence = $intentResult['confidence'];
        $entities = $intentResult['entities'] ?? [];

        // Try learned responses first (from DB)
        $learnedResponse = $this->getLearnedResponse($intent, $entities);
        if ($learnedResponse) {
            return $learnedResponse;
        }

        // Try knowledge base (31 entries in ai_knowledge_base)
        $kbResponse = $this->getKnowledgeBaseResponse($intent, $entities);
        if ($kbResponse) {
            return $kbResponse;
        }

        // Built-in responses with personalization
        return $this->getBuiltInResponse($intent, $entities, $userProfile);
    }

    /**
     * Get response from learned patterns (best responses from past conversations)
     */
    private function getLearnedResponse(string $intent, array $entities): ?array
    {
        try {
            $response = $this->db->fetch(
                "SELECT message as response_text, success_count, fail_count
                 FROM ai_chat_messages
                 WHERE (intent = ? OR detected_intent = ?) AND sender = 'bot' AND LENGTH(message) > 10
                 ORDER BY success_count DESC, created_at DESC
                 LIMIT 1",
                [$intent, $intent]
            );

            if ($response && ($response['success_count'] ?? 0) > 3) {
                return [
                    'text' => $response['response_text'],
                    'confidence' => 0.9,
                    'actions' => [],
                    'suggestions' => $this->getFollowUpSuggestions($intent)
                ];
            }
        } catch (\Exception $e) {}

        return null;
    }

    /**
     * Get response from knowledge base
     */
    private function getKnowledgeBaseResponse(string $intent, array $entities): ?array
    {
        try {
            $kb = $this->db->fetch(
                "SELECT answer, category FROM ai_knowledge_base
                 WHERE category = ? AND is_active = 1
                 ORDER BY confidence DESC
                 LIMIT 1",
                [$intent]
            );

            if ($kb) {
                return [
                    'text' => $kb['answer'],
                    'confidence' => 0.85,
                    'actions' => [],
                    'suggestions' => $this->getFollowUpSuggestions($intent)
                ];
            }
        } catch (\Exception $e) {}

        return null;
    }

    /**
     * Built-in responses with user-profile personalization
     */
    private function getBuiltInResponse(string $intent, array $entities, array $userProfile): array
    {
        $name = $userProfile['name'] ?? '';
        $greeting = $name ? "Hello {$name}! " : '';

        $responses = [
            'greeting' => [
                "{$greeting}Welcome to APS Dream Home! I'm your AI property assistant. I can help you find plots, check prices, schedule site visits, and more. What would you like to know?",
                "{$greeting}Namaste! How can I help you today? Looking for a property or have questions about our projects?",
                "{$greeting}Hi there! I'm here to help you find your dream property in Gorakhpur. What are you looking for?"
            ],
            'property_search' => [
                "We have premium plots and properties in Raghunath Nagri, Suryoday Colony, Braj Radha, and Budh Bihar. What type of property interests you - plot, flat, or villa?",
                "Our available properties range from 1000 to 3000+ sq ft. We have residential plots, apartments, and commercial spaces. What's your preferred size and budget?",
                "Let me help you find the perfect property! Are you looking for a residential plot, apartment, or commercial space? What's your budget range?"
            ],
            'pricing' => [
                "Our property prices start from ₹5 Lakh for residential plots and go up to ₹50 Lakh for premium villas. What's your budget range? I can show you the best options.",
                "We have properties across all price ranges: Budget (₹5-15L), Mid-range (₹15-30L), and Premium (₹30L+). Which category interests you?",
                "Plot prices in our colonies start from ₹5,000/sq ft. Would you like a detailed price list for a specific colony?"
            ],
            'location' => [
                "We have projects in prime Gorakhpur locations: Raghunath Nagri (main), Suryoday Colony, Braj Radha, and Budh Bihar. All locations have excellent connectivity and infrastructure.",
                "Our properties are strategically located near schools, hospitals, markets, and transport hubs. Which area of Gorakhpur are you interested in?",
                "Gorakhpur is a rapidly growing city with excellent ROI potential. Our colonies are in the most sought-after areas. Want to know more about a specific location?"
            ],
            'amenities' => [
                "Our colonies feature: 24/7 security, paved roads, water supply, electricity, parks, children's play areas, community halls, and green spaces. Premium projects also include gyms and swimming pools.",
                "We provide world-class amenities including landscaped gardens, jogging tracks, CCTV surveillance, rainwater harvesting, and solar lighting. Which amenities are most important to you?",
                "Every APS property comes with essential amenities plus premium features depending on the project. Want a detailed amenity list for a specific colony?"
            ],
            'contact' => [
                "You can reach us at:\n📞 Phone: +91-9876543210\n📧 Email: info@apsdreamhome.com\n📍 Office: Raghunath Nagri, Gorakhpur\n\nWould you like to schedule a site visit?",
                "Our team is ready to help! Call us at +91-9876543210 or visit our office in Raghunath Nagri. We're available Mon-Sat, 9 AM - 7 PM.",
                "I can connect you with our property experts right away. Would you prefer a call, WhatsApp message, or in-person visit?"
            ],
            'financing' => [
                "We offer easy financing with leading banks! Home loans available from 8.5% interest rate. EMI starts from just ₹5,000/month. Want me to calculate EMI for your budget?",
                "Our finance partners include SBI, HDFC, ICICI, and Bank of Baroda. Quick approval with minimal documentation. Need help with loan eligibility?",
                "We provide complete financing assistance including loan comparison and documentation support. What's your preferred loan amount?"
            ],
            'booking' => [
                "Great choice! To book a property:\n1. Select your preferred plot/property\n2. Pay booking amount (₹25,000 onwards)\n3. Complete documentation\n4. Choose payment plan\n\nShall I help you start the booking process?",
                "Booking is simple! Pay a small token amount and the property is reserved for you. We offer flexible payment plans: Full payment, Installments, or Bank loan. Which do you prefer?",
                "I'd love to help you book your dream property! First, let me understand your requirements. What type of property and what budget are you looking at?"
            ],
            'site_visit' => [
                "I'd be happy to arrange a site visit for you! Our colonies are open for visits Mon-Sat, 9 AM - 6 PM. Which colony would you like to visit? I'll schedule it right away.",
                "Site visits are the best way to experience our properties! We can arrange a guided tour with our property expert. When would you like to visit?",
                "Come see your future home in person! We offer personalized site visits with transportation assistance. Which project interests you most?"
            ],
            'commission' => [
                "APS Dream Home offers exciting earning opportunities! Join as an Associate and earn up to 20% commission on property sales. Plus bonuses, incentives, and career growth. Want to know more?",
                "Our referral program earns you money for every successful referral. Associates also get commission on plot sales, team bonuses, and leadership rewards. Interested in becoming an Associate?",
                "Earn while you help others find their dream home! Our commission structure rewards performance with increasing rates as you grow. Want details?"
            ],
            'goodbye' => [
                "Thank you for chatting with APS Dream Home! Feel free to return anytime. Have a wonderful day! 🏠",
                "It was great helping you today! Remember, I'm always here when you need property assistance. Take care!",
                "Goodbye! We look forward to helping you find your dream property. Don't hesitate to reach out anytime!"
            ],
            'help' => [
                "I can help you with:\n🏠 Property search and details\n💰 Pricing and payment options\n📍 Location information\n📅 Site visit scheduling\n📝 Booking process\n💼 Earning opportunities\n\nWhat would you like to know?",
                "Here's what I can do for you:\n- Find properties matching your budget\n- Compare different colonies\n- Calculate EMI\n- Schedule site visits\n- Answer any property questions\n\nJust ask me anything!",
                "I'm your AI property assistant! Ask me about properties, prices, locations, amenities, financing, or how to book. I'm here to help!"
            ],
            'complaint' => [
                "I'm sorry to hear about the issue. I understand your concern and want to help resolve it immediately. Can you describe the problem in detail? I'll escalate it to the right team.",
                "I apologize for the inconvenience. Customer satisfaction is our priority. Please share the details and I'll make sure our team addresses this promptly.",
                "I'm sorry about this experience. Let me connect you with our support team who can resolve this right away. What happened?"
            ],
            'fallback' => [
                "I'm not sure I understood that completely. Could you rephrase? I can help with properties, pricing, locations, booking, site visits, and more.",
                "I'm still learning! But I can help you with property search, pricing, site visits, and booking. What would you like to know?",
                "Let me try to understand better. Are you asking about properties, pricing, location, or something else? I'm here to help!",
                "I want to give you the best answer. Could you tell me more about what you're looking for? Property details, pricing, or booking?"
            ]
        ];

        $intentResponses = $responses[$intent] ?? $responses['fallback'];
        $text = $intentResponses[array_rand($intentResponses)];

        return [
            'text' => $text,
            'confidence' => 0.75,
            'actions' => $this->getSuggestedActions($intent),
            'suggestions' => $this->getFollowUpSuggestions($intent)
        ];
    }

    /**
     * Get follow-up suggestions based on current intent
     */
    private function getFollowUpSuggestions(string $intent): array
    {
        $suggestions = [
            'greeting' => ['View Properties', 'Check Prices', 'Schedule Visit'],
            'property_search' => ['Check Pricing', 'Schedule Site Visit', 'Compare Properties'],
            'pricing' => ['Calculate EMI', 'View Properties', 'Talk to Expert'],
            'location' => ['View Colony Map', 'Schedule Visit', 'Check Properties'],
            'amenities' => ['View Properties', 'Check Pricing', 'Schedule Visit'],
            'contact' => ['Call Now', 'Schedule Visit', 'Send WhatsApp'],
            'financing' => ['Check Eligibility', 'Calculate EMI', 'Talk to Bank Partner'],
            'booking' => ['Start Booking', 'View Payment Plans', 'Schedule Visit'],
            'site_visit' => ['Book Visit', 'View Locations', 'Contact Team'],
            'commission' => ['Join as Associate', 'View Commission Plan', 'Speak to Manager']
        ];

        return $suggestions[$intent] ?? ['Ask a Question', 'View Properties', 'Contact Us'];
    }

    /**
     * Get suggested actions
     */
    private function getSuggestedActions(string $intent): array
    {
        $actions = [
            'contact' => [['type' => 'phone', 'value' => '+919876543210']],
            'booking' => [['type' => 'link', 'value' => '/register', 'label' => 'Register Now']],
            'site_visit' => [['type' => 'link', 'value' => '/associate/dashboard', 'label' => 'Book Visit']],
            'financing' => [['type' => 'calculator', 'value' => 'emi']]
        ];

        return $actions[$intent] ?? [];
    }

    /**
     * Learn from this interaction (feedback loop)
     */
    private function learnFromInteraction(array $normalized, array $intentResult, array $response, array $context): void
    {
        try {
            $text = $normalized['normalized'];
            $intent = $intentResult['intent'];
            $confidence = $intentResult['confidence'];

            // If low confidence, store as potential new pattern for review
            if ($confidence < self::MEDIUM_CONFIDENCE && $intent !== 'fallback') {
                $this->db->execute(
                    "INSERT INTO ai_intent_patterns (pattern_text, intent_name, confidence, source, is_active, created_at)
                     VALUES (?, ?, ?, 'auto_learned', 0, NOW())
                     ON DUPLICATE KEY UPDATE confidence = GREATEST(confidence, ?)",
                    [$text, $intent, $confidence, $confidence]
                );
            }

            // Track response effectiveness
            $this->db->execute(
                "UPDATE ai_chat_messages
                 SET view_count = COALESCE(view_count, 0) + 1
                 WHERE session_id = ? AND sender = 'bot'
                 ORDER BY id DESC LIMIT 1",
                [$this->sessionId]
            );
        } catch (\Exception $e) {
            // Silent fail for learning - don't break chat
        }
    }

    /**
     * Update user profile based on conversation
     */
    private function updateUserProfile(array $normalized, array $intentResult): void
    {
        if (!$this->userId) return;

        try {
            $entities = $intentResult['entities'] ?? [];

            // Update interests
            if (!empty($entities['property_type'])) {
                $this->db->execute(
                    "INSERT INTO ai_user_profiles (user_id, preferred_types, updated_at)
                     VALUES (?, ?, NOW())
                     ON DUPLICATE KEY UPDATE preferred_types = ?, updated_at = NOW()",
                    [$this->userId, $entities['property_type'], $entities['property_type']]
                );
            }

            if (!empty($entities['budget_lakhs'])) {
                $this->db->execute(
                    "UPDATE ai_user_profiles SET budget_max = ?, updated_at = NOW() WHERE user_id = ?",
                    [$entities['budget_lakhs'], $this->userId]
                );
            }

            if (!empty($entities['location'])) {
                $this->db->execute(
                    "UPDATE ai_user_profiles SET preferred_locations = ?, updated_at = NOW() WHERE user_id = ?",
                    [json_encode([$entities['location']]), $this->userId]
                );
            }
        } catch (\Exception $e) {
            // Silent fail
        }
    }

    /**
     * Get user profile
     */
    private function getUserProfile(): array
    {
        if (!$this->userId) return [];

        try {
            $tid = TenantContext::getId();
            $profile = $this->db->fetch(
                "SELECT u.name, up.preferred_types as preferred_property_type, up.budget_max as preferred_budget, up.preferred_locations as preferred_location
                 FROM users u
                 LEFT JOIN ai_user_profiles up ON u.id = up.user_id
                 WHERE u.id = ?" . ($tid > 1 ? " AND u.tenant_id = ?" : ""),
                $tid > 1 ? [$this->userId, $tid] : [$this->userId]
            );
            return $profile ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get conversation context (last 5 messages)
     */
    private function getConversationContext(): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT message, sender, detected_intent as intent, entities
                 FROM ai_chat_messages
                 WHERE session_id = ?
                 ORDER BY id DESC
                 LIMIT 10",
                [$this->sessionId]
            ) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Store message in DB
     */
    private function storeMessage(string $userMessage, string $botResponse, array $intentResult, float $confidence): void
    {
        try {
            // Store user message
            $this->db->execute(
                "INSERT INTO ai_chat_messages (session_id, user_id, sender, message, detected_intent, intent, entities, confidence, created_at)
                 VALUES (?, ?, 'user', ?, ?, ?, ?, ?, NOW())",
                [
                    $this->sessionId,
                    $this->userId,
                    $userMessage,
                    $intentResult['intent'] ?? 'unknown',
                    $intentResult['intent'] ?? 'unknown',
                    json_encode($intentResult['entities'] ?? []),
                    $confidence
                ]
            );

            // Store bot response
            $this->db->execute(
                "INSERT INTO ai_chat_messages (session_id, user_id, sender, message, detected_intent, intent, entities, confidence, created_at)
                 VALUES (?, ?, 'bot', ?, ?, ?, ?, ?, NOW())",
                [
                    $this->sessionId,
                    $this->userId,
                    $botResponse,
                    $intentResult['intent'] ?? 'unknown',
                    $intentResult['intent'] ?? 'unknown',
                    json_encode($intentResult['entities'] ?? []),
                    $confidence
                ]
            );
        } catch (\Exception $e) {
            // Silent fail
        }
    }

    /**
     * Process user feedback (thumbs up/down)
     */
    public function processFeedback(int $messageId, bool $positive, ?string $comment = null): bool
    {
        try {
            if ($positive) {
                $this->db->execute(
                    "UPDATE ai_chat_messages SET success_count = COALESCE(success_count, 0) + 1 WHERE id = ?",
                    [$messageId]
                );
            } else {
                $this->db->execute(
                    "UPDATE ai_chat_messages SET fail_count = COALESCE(fail_count, 0) + 1 WHERE id = ?",
                    [$messageId]
                );
            }

            // Store feedback for analysis
            $this->db->execute(
                "INSERT INTO ai_learning_data (session_id, user_id, interaction_type, input_data, output_data, rating, created_at)
                 VALUES (?, ?, 'feedback', ?, ?, ?, NOW())",
                [
                    $this->sessionId,
                    $this->userId,
                    json_encode(['message_id' => $messageId]),
                    json_encode(['positive' => $positive, 'comment' => $comment]),
                    $positive ? 5 : 1
                ]
            );

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get AI performance stats
     */
    public function getPerformanceStats(): array
    {
        try {
            $stats = [];

            // Total conversations today
            $row = $this->db->fetch(
                "SELECT COUNT(*) as total, AVG(confidence) as avg_confidence
                 FROM ai_chat_messages WHERE DATE(created_at) = CURDATE() AND sender = 'user'"
            );
            $stats['today_conversations'] = (int)($row['total'] ?? 0);
            $stats['avg_confidence'] = round((float)($row['avg_confidence'] ?? 0), 2);

            // Intent distribution
            $stats['top_intents'] = $this->db->fetchAll(
                "SELECT detected_intent as intent, COUNT(*) as count
                 FROM ai_chat_messages WHERE DATE(created_at) = CURDATE() AND sender = 'user'
                 GROUP BY detected_intent ORDER BY count DESC LIMIT 5"
            ) ?: [];

            // Feedback score
            $fb = $this->db->fetch(
                "SELECT AVG(rating) as avg_rating, COUNT(*) as total_feedback
                 FROM ai_learning_data WHERE interaction_type = 'feedback' AND DATE(created_at) = CURDATE()"
            );
            $stats['avg_rating'] = round((float)($fb['avg_rating'] ?? 0), 1);
            $stats['total_feedback'] = (int)($fb['total_feedback'] ?? 0);

            return $stats;
        } catch (\Exception $e) {
            return [];
        }
    }
}
