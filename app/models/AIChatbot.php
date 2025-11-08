<?php
/**
 * AI Chatbot Model
 * Handles intelligent property inquiries and customer support
 */

namespace App\Models;

class AIChatbot extends Model {
    protected static string $table = 'chatbot_conversations';

    /**
     * Process user message and generate response
     */
    public function processMessage($message, $context = []) {
        try {
            $message = trim(strtolower($message));

            // Intent recognition
            $intent = $this->recognizeIntent($message);

            switch ($intent) {
                case 'property_search':
                    return $this->handlePropertySearch($message, $context);
                case 'price_inquiry':
                    return $this->handlePriceInquiry($message, $context);
                case 'location_info':
                    return $this->handleLocationInfo($message, $context);
                case 'contact_request':
                    return $this->handleContactRequest($message, $context);
                case 'general_inquiry':
                    return $this->handleGeneralInquiry($message, $context);
                default:
                    return $this->getDefaultResponse();
            }

        } catch (\Exception $e) {
            error_log('Chatbot processing error: ' . $e->getMessage());
            return $this->getErrorResponse();
        }
    }

    /**
     * Recognize user intent from message
     */
    private function recognizeIntent($message) {
        // Property search keywords
        $property_keywords = ['property', 'house', 'home', 'apartment', 'villa', 'plot', 'land', 'show me', 'find', 'search'];
        if ($this->containsKeywords($message, $property_keywords)) {
            return 'property_search';
        }

        // Price related keywords
        $price_keywords = ['price', 'cost', 'budget', 'expensive', 'cheap', 'affordable', 'how much', 'rate'];
        if ($this->containsKeywords($message, $price_keywords)) {
            return 'price_inquiry';
        }

        // Location related keywords
        $location_keywords = ['location', 'area', 'city', 'near', 'close to', 'where', 'address', 'locality'];
        if ($this->containsKeywords($message, $location_keywords)) {
            return 'location_info';
        }

        // Contact related keywords
        $contact_keywords = ['contact', 'call', 'phone', 'email', 'speak', 'talk', 'agent', 'help'];
        if ($this->containsKeywords($message, $contact_keywords)) {
            return 'contact_request';
        }

        return 'general_inquiry';
    }

    /**
     * Check if message contains any of the keywords
     */
    private function containsKeywords($message, $keywords) {
        foreach ($keywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Handle property search requests
     */
    private function handlePropertySearch($message, $context) {
        try {
            global $pdo;

            // Extract search criteria
            $criteria = $this->extractSearchCriteria($message);

            // Build search query
            $where_conditions = ["p.status = 'available'"];
            $params = [];

            if (isset($criteria['property_type'])) {
                $where_conditions[] = "p.property_type = ?";
                $params[] = $criteria['property_type'];
            }

            if (isset($criteria['city'])) {
                $where_conditions[] = "p.city = ?";
                $params[] = $criteria['city'];
            }

            if (isset($criteria['budget'])) {
                $where_conditions[] = "p.price <= ?";
                $params[] = $criteria['budget'];
            }

            $where_clause = implode(' AND ', $where_conditions);

            $sql = "SELECT p.*, pt.name as property_type_name
                    FROM properties p
                    LEFT JOIN property_types pt ON p.property_type_id = pt.id
                    WHERE {$where_clause}
                    ORDER BY p.featured DESC, p.created_at DESC
                    LIMIT 5";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $properties = $stmt->fetchAll();

            if (empty($properties)) {
                return [
                    'response' => "I couldn't find properties matching your criteria. Can you provide more details about what you're looking for?",
                    'suggestions' => [
                        'Try different location',
                        'Adjust your budget',
                        'Specify property type'
                    ]
                ];
            }

            $response = "I found " . count($properties) . " properties matching your criteria:\n\n";

            foreach ($properties as $property) {
                $response .= "🏠 " . $property['title'] . "\n";
                $response .= "💰 ₹" . number_format($property['price']) . "\n";
                $response .= "📍 " . $property['city'] . ", " . $property['state'] . "\n";
                $response .= "🏡 " . $property['bedrooms'] . "BHK • " . $property['area_sqft'] . " sqft\n";
                $response .= "🔗 View: " . BASE_URL . "property/" . $property['id'] . "\n\n";
            }

            return [
                'response' => $response,
                'properties' => $properties,
                'action_buttons' => [
                    ['text' => 'Show More Properties', 'action' => 'search_more'],
                    ['text' => 'Refine Search', 'action' => 'refine_search'],
                    ['text' => 'Contact Agent', 'action' => 'contact_agent']
                ]
            ];

        } catch (\Exception $e) {
            return $this->getErrorResponse();
        }
    }

    /**
     * Extract search criteria from message
     */
    private function extractSearchCriteria($message) {
        $criteria = [];

        // Property type mapping
        $type_keywords = [
            'apartment' => 'Apartment',
            'flat' => 'Apartment',
            'villa' => 'Villa',
            'house' => 'Villa',
            'plot' => 'Plot',
            'land' => 'Plot',
            'commercial' => 'Commercial',
            'office' => 'Office Space'
        ];

        foreach ($type_keywords as $keyword => $type) {
            if (strpos($message, $keyword) !== false) {
                $criteria['property_type'] = $type;
                break;
            }
        }

        // Location extraction
        $cities = ['delhi', 'mumbai', 'bangalore', 'pune', 'hyderabad', 'chennai', 'kolkata', 'gorakhpur'];
        foreach ($cities as $city) {
            if (strpos($message, $city) !== false) {
                $criteria['city'] = ucfirst($city);
                break;
            }
        }

        // Budget extraction
        if (preg_match('/(\d+(?:,\d+)*)\s*(?:lakh|lakhs|crore|crores?)/i', $message, $matches)) {
            $amount = (int)str_replace(',', '', $matches[1]);

            // Convert to actual amount
            if (stripos($matches[2], 'lakh') !== false) {
                $criteria['budget'] = $amount * 100000;
            } elseif (stripos($matches[2], 'crore') !== false) {
                $criteria['budget'] = $amount * 10000000;
            }
        }

        return $criteria;
    }

    /**
     * Handle price inquiry
     */
    private function handlePriceInquiry($message, $context) {
        if (isset($context['property_id'])) {
            // Get specific property price
            try {
                global $pdo;
                $stmt = $pdo->prepare("SELECT title, price, city FROM properties WHERE id = ?");
                $stmt->execute([$context['property_id']]);
                $property = $stmt->fetch();

                if ($property) {
                    return [
                        'response' => "The price for {$property['title']} in {$property['city']} is ₹" . number_format($property['price']) . ". Would you like to know about financing options?",
                        'action_buttons' => [
                            ['text' => 'EMI Calculator', 'action' => 'show_emi'],
                            ['text' => 'Similar Properties', 'action' => 'similar_properties'],
                            ['text' => 'Schedule Visit', 'action' => 'schedule_visit']
                        ]
                    ];
                }
            } catch (\Exception $e) {
                // Continue to general response
            }
        }

        return [
            'response' => "Property prices vary based on location, type, and amenities. Our properties range from ₹10 lakhs to ₹10 crores. What type of property and budget are you looking for?",
            'suggestions' => [
                'Budget-friendly apartments under ₹50 lakhs',
                'Luxury villas above ₹2 crores',
                'Commercial properties for investment'
            ]
        ];
    }

    /**
     * Handle location information requests
     */
    private function handleLocationInfo($message, $context) {
        // Extract location from message
        $location = $this->extractLocation($message);

        if ($location) {
            return [
                'response' => "Great choice! {$location} is an excellent location. Here are some key highlights:\n\n" .
                             "🏫 Good schools and educational institutions\n" .
                             "🏥 Quality healthcare facilities nearby\n" .
                             "🚌 Excellent public transport connectivity\n" .
                             "🛍️ Shopping and entertainment options\n" .
                             "📈 High appreciation potential\n\n" .
                             "I can show you properties in {$location}. What type of property interests you?",
                'location' => $location,
                'action_buttons' => [
                    ['text' => 'Show Properties', 'action' => 'search_properties'],
                    ['text' => 'Area Guide', 'action' => 'area_guide'],
                    ['text' => 'Market Trends', 'action' => 'market_trends']
                ]
            ];
        }

        return [
            'response' => "Which location are you interested in? I can provide information about properties in major cities like Delhi, Mumbai, Bangalore, Pune, and many more.",
            'suggestions' => [
                'Delhi - National capital with excellent connectivity',
                'Mumbai - Financial hub with premium properties',
                'Bangalore - IT hub with modern developments'
            ]
        ];
    }

    /**
     * Extract location from message
     */
    private function extractLocation($message) {
        $locations = [
            'delhi', 'new delhi', 'mumbai', 'bangalore', 'bengaluru', 'pune',
            'hyderabad', 'chennai', 'kolkata', 'ahmedabad', 'jaipur', 'lucknow',
            'kanpur', 'nagpur', 'indore', 'thane', 'bhopal', 'visakhapatnam',
            'patna', 'vadodara', 'ghaziabad', 'ludhiana', 'agra', 'nashik',
            'faridabad', 'meerut', 'rajkot', 'kalyan', 'vasai', 'varanasi',
            'gorakhpur', 'allahabad'
        ];

        foreach ($locations as $location) {
            if (strpos($message, $location) !== false) {
                return ucwords(str_replace('-', ' ', $location));
            }
        }

        return null;
    }

    /**
     * Handle contact requests
     */
    private function handleContactRequest($message, $context) {
        return [
            'response' => "I'd be happy to connect you with one of our expert agents! They can provide personalized assistance for your property needs.\n\n" .
                         "📞 Call us: +91-9876543210\n" .
                         "📧 Email: info@apsdreamhome.com\n" .
                         "💬 WhatsApp: +91-9876543210\n\n" .
                         "Or I can schedule a callback for you. What time works best?",
            'action_buttons' => [
                ['text' => 'Schedule Callback', 'action' => 'schedule_callback'],
                ['text' => 'Live Chat with Agent', 'action' => 'live_chat'],
                ['text' => 'Send WhatsApp Message', 'action' => 'whatsapp']
            ]
        ];
    }

    /**
     * Handle general inquiries
     */
    private function handleGeneralInquiry($message, $context) {
        $responses = [
            'hello' => "Hello! I'm your APS Dream Home assistant. How can I help you find your perfect property today?",
            'help' => "I can help you with:\n• Finding properties\n• Price information\n• Location details\n• Agent contact\n• Property comparison\n\nWhat are you looking for?",
            'services' => "We offer:\n• Residential properties (apartments, villas)\n• Commercial properties\n• Plots and land\n• Property management\n• Real estate consultation\n• MLM business opportunities",
            'about' => "APS Dream Home is a leading real estate platform helping people find their dream properties. We combine technology with personalized service to make property buying simple and enjoyable."
        ];

        foreach ($responses as $keyword => $response) {
            if (strpos($message, $keyword) !== false) {
                return ['response' => $response];
            }
        }

        return $this->getDefaultResponse();
    }

    /**
     * Get default response for unrecognized queries
     */
    private function getDefaultResponse() {
        return [
            'response' => "I understand you're interested in properties. To help you better, could you please tell me:\n\n" .
                         "• What type of property are you looking for?\n" .
                         "• Your preferred location?\n" .
                         "• Your budget range?\n\n" .
                         "Or simply say 'help' to see all available options!",
            'suggestions' => [
                'Show me apartments in Delhi',
                'What are villa prices?',
                'Contact an agent',
                'Help me understand the process'
            ]
        ];
    }

    /**
     * Get error response
     */
    private function getErrorResponse() {
        return [
            'response' => "I'm experiencing some technical difficulties. Please try again in a moment or contact our support team directly at +91-9876543210.",
            'error' => true
        ];
    }

    /**
     * Save conversation
     */
    public function saveConversation($user_id, $message, $bot_response, $intent = '') {
        try {
            $sql = "INSERT INTO {$this->table} (user_id, user_message, bot_response, intent, created_at)
                    VALUES (?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$user_id, $message, $bot_response, $intent]);

        } catch (\Exception $e) {
            error_log('Conversation save error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get conversation history
     */
    public function getConversationHistory($user_id, $limit = 50) {
        try {
            $sql = "SELECT * FROM {$this->table}
                    WHERE user_id = ?
                    ORDER BY created_at DESC
                    LIMIT ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$user_id, $limit]);

            return $stmt->fetchAll();

        } catch (\Exception $e) {
            error_log('Conversation history error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get popular queries and responses
     */
    public function getPopularPatterns() {
        try {
            $sql = "SELECT user_message, bot_response, COUNT(*) as frequency
                    FROM {$this->table}
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY user_message, bot_response
                    ORDER BY frequency DESC
                    LIMIT 20";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();

        } catch (\Exception $e) {
            error_log('Popular patterns error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate quick replies based on context
     */
    public function getQuickReplies($context = []) {
        $quick_replies = [
            'greeting' => [
                'Hi! How can I help you find a property?',
                'Hello! Looking for apartments, villas, or plots?',
                'Welcome! What type of property interests you?'
            ],
            'property_found' => [
                'Show similar properties',
                'Contact the agent',
                'Schedule a viewing',
                'Calculate EMI'
            ],
            'no_results' => [
                'Try different location',
                'Adjust budget range',
                'Show all available properties'
            ],
            'contact' => [
                'Call now: +91-9876543210',
                'WhatsApp us',
                'Schedule callback'
            ]
        ];

        return $quick_replies[$context['type']] ?? $quick_replies['greeting'];
    }

    /**
     * Analyze conversation sentiment
     */
    public function analyzeSentiment($message) {
        $positive_words = ['great', 'excellent', 'amazing', 'perfect', 'love', 'interested', 'good', 'nice', 'beautiful'];
        $negative_words = ['bad', 'terrible', 'hate', 'worst', 'angry', 'disappointed', 'problem', 'issue'];

        $message_lower = strtolower($message);
        $positive_count = 0;
        $negative_count = 0;

        foreach ($positive_words as $word) {
            if (strpos($message_lower, $word) !== false) {
                $positive_count++;
            }
        }

        foreach ($negative_words as $word) {
            if (strpos($message_lower, $word) !== false) {
                $negative_count++;
            }
        }

        if ($positive_count > $negative_count) {
            return 'positive';
        } elseif ($negative_count > $positive_count) {
            return 'negative';
        }

        return 'neutral';
    }

    /**
     * Get chatbot statistics
     */
    public function getChatbotStats() {
        try {
            $stats = [];

            // Total conversations
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM {$this->table}");
            $stats['total_conversations'] = (int)$stmt->fetch()['total'];

            // Conversations today
            $stmt = $this->db->query("SELECT COUNT(*) as today FROM {$this->table}
                                     WHERE DATE(created_at) = CURDATE()");
            $stats['today_conversations'] = (int)$stmt->fetch()['today'];

            // Unique users
            $stmt = $this->db->query("SELECT COUNT(DISTINCT user_id) as users FROM {$this->table}");
            $stats['unique_users'] = (int)$stmt->fetch()['users'];

            // Intent distribution
            $stmt = $this->db->query("SELECT intent, COUNT(*) as count FROM {$this->table}
                                     WHERE intent IS NOT NULL AND intent != ''
                                     GROUP BY intent ORDER BY count DESC");
            $stats['intent_distribution'] = $stmt->fetchAll();

            // Average response time (placeholder)
            $stats['avg_response_time'] = '0.8 seconds';

            return $stats;

        } catch (\Exception $e) {
            error_log('Chatbot stats error: ' . $e->getMessage());
            return [];
        }
    }
}
