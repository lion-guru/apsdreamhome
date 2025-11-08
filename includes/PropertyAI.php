<?php
/**
 * Enhanced PropertyAI - Advanced AI-powered property search and recommendations
 * Comprehensive AI system with chat, recommendations, and predictive analytics
 */

class PropertyAI {
    private $conn;
    private $config;
    private $openai_api_key;
    private $chat_history = [];

    // Enhanced property types and locations
    private $propertyTypes = [
        'apartment', 'villa', 'house', 'plot', 'land', 'commercial',
        'office', 'shop', 'warehouse', 'flat', 'penthouse', 'duplex',
        'bungalow', 'farmhouse', 'studio', 'row house', 'townhouse',
        'condo', 'loft', 'mansion', 'cottage', 'chalet', 'penthouse'
    ];

    private $locations = [
        'mumbai', 'delhi', 'bangalore', 'hyderabad', 'chennai',
        'kolkata', 'pune', 'ahmedabad', 'surat', 'jaipur',
        'lucknow', 'kanpur', 'nagpur', 'indore', 'thane',
        'bhopal', 'visakhapatnam', 'pimpri-chinchwad', 'patna', 'vadodara',
        'ghaziabad', 'ludhiana', 'agra', 'nashik', 'faridabad', 'meerut',
        'rajkot', 'kalyan-dombivli', 'vasai-virar', 'varanasi', 'srinagar'
    ];

    // AI conversation contexts
    private $conversationContexts = [
        'property_search' => 'Looking for properties',
        'price_inquiry' => 'Asking about prices',
        'location_info' => 'Seeking location information',
        'investment_advice' => 'Investment related queries',
        'legal_questions' => 'Legal and documentation queries',
        'market_trends' => 'Market analysis questions',
        'general_inquiry' => 'General questions'
    ];

    /**
     * Constructor
     */
    public function __construct($conn, $config = []) {
        $this->conn = $conn;
        $this->config = array_merge([
            'use_mock_data' => false,
            'cache_ttl' => 3600, // 1 hour cache
            'openai_api_key' => getenv('OPENAI_API_KEY') ?: '',
            'max_chat_history' => 50,
            'enable_ml_predictions' => true
        ], $config);

        $this->openai_api_key = $this->config['openai_api_key'];
    }

    /**
     * Process chat message with AI
     */
    public function processChatMessage($messageData) {
        $message = $messageData['message'] ?? '';
        $conversationId = $messageData['conversation_id'] ?? null;
        $context = $messageData['context'] ?? 'general_inquiry';
        $userAgent = $messageData['user_agent'] ?? '';
        $ipAddress = $messageData['ip_address'] ?? '';

        // Validate input
        if (empty($message)) {
            return [
                'response' => 'Please provide a message to chat about.',
                'conversation_id' => $conversationId,
                'context' => $context,
                'confidence' => 0
            ];
        }

        // Detect conversation context
        $detectedContext = $this->detectContext($message);

        // Store chat message
        $chatId = $this->storeChatMessage($message, $conversationId, $context, $ipAddress);

        try {
            // Generate AI response
            $aiResponse = $this->generateAIResponse($message, $detectedContext, $context);

            // Store AI response
            $this->storeAIResponse($chatId, $aiResponse['response'], $aiResponse['confidence']);

            return [
                'response' => $aiResponse['response'],
                'conversation_id' => $conversationId ?: $chatId,
                'context' => $detectedContext,
                'confidence' => $aiResponse['confidence'],
                'data' => $aiResponse['data'] ?? null
            ];

        } catch (Exception $e) {
            // Fallback to rule-based response
            $fallbackResponse = $this->generateFallbackResponse($message, $detectedContext);

            return [
                'response' => $fallbackResponse,
                'conversation_id' => $conversationId ?: $chatId,
                'context' => $detectedContext,
                'confidence' => 0.3,
                'fallback' => true
            ];
        }
    }

    /**
     * Detect conversation context from message
     */
    private function detectContext($message) {
        $message = strtolower($message);

        // Context keywords mapping
        $contextKeywords = [
            'property_search' => ['find', 'search', 'looking for', 'show me', 'want to buy', 'interested in'],
            'price_inquiry' => ['price', 'cost', 'budget', 'how much', 'expensive', 'cheap', 'afford'],
            'location_info' => ['location', 'area', 'neighborhood', 'near', 'close to', 'where is'],
            'investment_advice' => ['invest', 'investment', 'roi', 'return', 'profit', 'rental'],
            'legal_questions' => ['legal', 'document', 'paper', 'registration', 'title', 'ownership'],
            'market_trends' => ['market', 'trend', 'analysis', 'prediction', 'future', 'growth']
        ];

        foreach ($contextKeywords as $context => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    return $context;
                }
            }
        }

        return 'general_inquiry';
    }

    /**
     * Generate AI response using OpenAI or fallback
     */
    private function generateAIResponse($message, $detectedContext, $originalContext) {
        // Try OpenAI if API key is available
        if (!empty($this->openai_api_key)) {
            return $this->generateOpenAIResponse($message, $detectedContext);
        }

        // Fallback to rule-based response
        return $this->generateFallbackResponse($message, $detectedContext);
    }

    /**
     * Generate response using OpenAI API
     */
    private function generateOpenAIResponse($message, $context) {
        $systemPrompt = $this->getSystemPrompt($context);

        $data = [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $message]
            ],
            'max_tokens' => 500,
            'temperature' => 0.7
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->openai_api_key
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $result = json_decode($response, true);
            $aiResponse = $result['choices'][0]['message']['content'] ?? 'I apologize, but I could not generate a response at this time.';

            return [
                'response' => $aiResponse,
                'confidence' => 0.9,
                'data' => ['source' => 'openai']
            ];
        }

        // Fallback if OpenAI fails
        return $this->generateFallbackResponse($message, $context);
    }

    /**
     * Get system prompt based on context
     */
    private function getSystemPrompt($context) {
        $basePrompt = "You are an AI assistant for APS Dream Home, a real estate platform. You help customers with property-related queries, provide information about properties, locations, pricing, and real estate market trends in India.";

        $contextPrompts = [
            'property_search' => "Help users find properties based on their requirements. Ask about location, budget, property type, and specific features they are looking for.",
            'price_inquiry' => "Provide information about property prices, price ranges for different locations, and factors affecting property prices.",
            'location_info' => "Share information about different locations, neighborhoods, amenities, connectivity, and lifestyle factors.",
            'investment_advice' => "Provide investment advice, ROI calculations, rental yield information, and market growth potential.",
            'legal_questions' => "Explain legal aspects of property purchase, documentation requirements, registration process, and legal considerations.",
            'market_trends' => "Analyze market trends, price movements, growth predictions, and future outlook for different property segments.",
            'general_inquiry' => "Provide general information about real estate, answer common questions, and guide users to appropriate resources."
        ];

        return $basePrompt . " " . ($contextPrompts[$context] ?? $contextPrompts['general_inquiry']);
    }

    /**
     * Generate fallback response using rule-based system
     */
    private function generateFallbackResponse($message, $context) {
        $message = strtolower($message);

        switch ($context) {
            case 'property_search':
                return $this->handlePropertySearch($message);
            case 'price_inquiry':
                return $this->handlePriceInquiry($message);
            case 'location_info':
                return $this->handleLocationInquiry($message);
            case 'investment_advice':
                return $this->handleInvestmentInquiry($message);
            case 'legal_questions':
                return $this->handleLegalInquiry($message);
            case 'market_trends':
                return $this->handleMarketInquiry($message);
            default:
                return $this->handleGeneralInquiry($message);
        }
    }

    /**
     * Handle property search queries
     */
    private function handlePropertySearch($message) {
        $properties = $this->findPropertiesByQuery($message, 3);

        if (!empty($properties)) {
            $response = "I found some properties that might interest you:\n\n";
            foreach ($properties as $property) {
                $response .= "• " . ($property['title'] ?? 'Property') . " in " . $property['location'] . "\n";
                $response .= "  Price: ₹" . number_format($property['price']) . "\n";
                $response .= "  Type: " . $property['property_type_name'] . "\n\n";
            }
            $response .= "Would you like more details about any of these properties?";
        } else {
            $response = "I couldn't find properties matching your criteria. Could you please provide more specific details about what you're looking for? (location, budget, property type)";
        }

        return $response;
    }

    /**
     * Handle price-related queries
     */
    private function handlePriceInquiry($message) {
        if (strpos($message, 'budget') !== false || strpos($message, 'afford') !== false) {
            return "To help you find properties within your budget, I need to know your price range. Properties in major Indian cities typically range from ₹20 lakhs for budget apartments to ₹5+ crores for luxury properties. What is your approximate budget?";
        }

        return "Property prices vary significantly based on location, size, and amenities. In Mumbai, average prices range from ₹50 lakhs to ₹3 crores. In Delhi, prices range from ₹30 lakhs to ₹2 crores. Could you specify a location or price range for more accurate information?";
    }

    /**
     * Handle location-related queries
     */
    private function handleLocationInquiry($message) {
        foreach ($this->locations as $location) {
            if (strpos($message, $location) !== false) {
                return "Great choice! $location is an excellent location with good connectivity and amenities. The area offers a mix of residential and commercial properties with prices ranging from ₹40 lakhs to ₹2 crores depending on the specific neighborhood. What type of property are you interested in?";
            }
        }

        return "I can help you with information about various locations across India. Popular areas include Mumbai, Delhi, Bangalore, Pune, and Hyderabad. Which location would you like to know more about?";
    }

    /**
     * Handle investment-related queries
     */
    private function handleInvestmentInquiry($message) {
        return "Real estate investment in India offers good returns, especially in growing cities. Current rental yields range from 2-4% annually, with capital appreciation of 5-8% per year. For investment advice, consider factors like location growth potential, rental demand, and infrastructure development. What type of investment are you considering?";
    }

    /**
     * Handle legal queries
     */
    private function handleLegalInquiry($message) {
        return "For legal matters related to property purchase, you'll need proper documentation including title deeds, property tax receipts, and building approvals. The registration process typically takes 15-30 days. I recommend consulting a legal expert for your specific case. What specific legal aspect would you like to know about?";
    }

    /**
     * Handle market trend queries
     */
    private function handleMarketInquiry($message) {
        return "The Indian real estate market is showing steady growth with increased demand in tier-2 cities. Prices have appreciated by 5-7% annually in major cities. The market is expected to grow further with infrastructure development and urbanization. Which market segment interests you - residential, commercial, or investment properties?";
    }

    /**
     * Handle general inquiries
     */
    private function handleGeneralInquiry($message) {
        if (strpos($message, 'hello') !== false || strpos($message, 'hi') !== false) {
            return "Hello! I'm your AI assistant for APS Dream Home. I can help you with property searches, price information, location details, investment advice, and much more. What would you like to know about real estate?";
        }

        return "I'm here to help you with all your real estate needs. You can ask me about:\n\n• Property searches and recommendations\n• Price information and market trends\n• Location details and amenities\n• Investment opportunities\n• Legal and documentation requirements\n\nWhat specific information are you looking for?";
    }

    /**
     * Store chat message in database
     */
    private function storeChatMessage($message, $conversationId, $context, $ipAddress) {
        if ($conversationId) {
            // Update existing conversation
            $sql = "INSERT INTO ai_chat_messages (conversation_id, message_type, message, context, ip_address, created_at)
                    VALUES (?, 'user', ?, ?, ?, NOW())";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssss", $conversationId, $message, $context, $ipAddress);
        } else {
            // Create new conversation
            $sql = "INSERT INTO ai_chat_conversations (ip_address, user_agent, created_at)
                    VALUES (?, ?, NOW())";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ss", $ipAddress, $_SERVER['HTTP_USER_AGENT'] ?? '');

            if ($stmt->execute()) {
                $conversationId = $this->conn->insert_id;

                $sql = "INSERT INTO ai_chat_messages (conversation_id, message_type, message, context, ip_address, created_at)
                        VALUES (?, 'user', ?, ?, ?, NOW())";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("ssss", $conversationId, $message, $context, $ipAddress);
            }
        }

        if ($stmt->execute()) {
            return $conversationId;
        }

        return null;
    }

    /**
     * Store AI response in database
     */
    private function storeAIResponse($conversationId, $response, $confidence) {
        $sql = "INSERT INTO ai_chat_messages (conversation_id, message_type, message, confidence, created_at)
                VALUES (?, 'ai', ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssd", $conversationId, $response, $confidence);
        $stmt->execute();
    }

    /**
     * Get recommended properties with AI scoring
     */
    public function getRecommendedProperties($userId, $limit = 6) {
        try {
            $sql = "SELECT p.*, pt.name as property_type_name,
                           (SELECT image FROM property_images WHERE property_id = p.id LIMIT 1) as main_image,
                           (SELECT AVG(rating) FROM property_reviews WHERE property_id = p.id) as avg_rating
                    FROM properties p
                    LEFT JOIN property_types pt ON p.property_type_id = pt.id
                    WHERE p.status = 'active'
                    ORDER BY p.featured DESC, p.created_at DESC LIMIT ?";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            $result = $stmt->get_result();

            $properties = [];
            while ($row = $result->fetch_assoc()) {
                // Add AI scoring
                $row['ai_score'] = $this->calculatePropertyScore($row, $userId);
                $properties[] = $row;
            }

            // Sort by AI score
            usort($properties, function($a, $b) {
                return $b['ai_score'] <=> $a['ai_score'];
            });

            return $properties;

        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Calculate AI score for property
     */
    private function calculatePropertyScore($property, $userId) {
        $score = 0;

        // Base score for active property
        if ($property['status'] === 'active') {
            $score += 50;
        }

        // Featured property bonus
        if ($property['featured']) {
            $score += 30;
        }

        // Rating bonus
        if (!empty($property['avg_rating'])) {
            $score += min($property['avg_rating'] * 10, 20); // Max 20 points for rating
        }

        // Price competitiveness (assuming average market price)
        $avgPrice = 5000000; // 50 lakhs
        $priceRatio = $property['price'] / $avgPrice;

        if ($priceRatio <= 1.5) { // Within reasonable range
            $score += 20;
        } elseif ($priceRatio <= 2.5) {
            $score += 10;
        }

        // Location popularity (simplified)
        $popularLocations = ['mumbai', 'delhi', 'bangalore', 'pune'];
        foreach ($popularLocations as $location) {
            if (stripos($property['location'], $location) !== false) {
                $score += 15;
                break;
            }
        }

        return min($score, 100);
    }

    /**
     * Get property suggestions based on partial input
     */
    public function getSuggestions($input, $type = 'location') {
        $suggestions = [];

        if ($type === 'location') {
            foreach ($this->locations as $location) {
                if (stripos($location, $input) === 0) {
                    $suggestions[] = $location;
                }
            }
        } elseif ($type === 'property_type') {
            foreach ($this->propertyTypes as $type) {
                if (stripos($type, $input) === 0) {
                    $suggestions[] = $type;
                }
            }
        }

        return array_slice($suggestions, 0, 5);
    }

    /**
     * Find properties based on natural language query
     */
    public function findPropertiesByQuery($query, $limit = 10) {
        // Parse the query to extract search criteria
        $criteria = $this->parseNaturalLanguageQuery($query);

        // Build SQL query based on criteria
        $sql = "SELECT p.*, pt.name as property_type_name,
                       (SELECT image FROM property_images WHERE property_id = p.id LIMIT 1) as main_image
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                WHERE p.status = 'active'";

        $params = [];
        $types = "";

        // Apply filters
        if (!empty($criteria['location'])) {
            $sql .= " AND (p.location LIKE ? OR p.city LIKE ?)";
            $params[] = "%" . $criteria['location'] . "%";
            $params[] = "%" . $criteria['location'] . "%";
            $types .= "ss";
        }

        if (!empty($criteria['property_type'])) {
            $sql .= " AND pt.name LIKE ?";
            $params[] = "%" . $criteria['property_type'] . "%";
            $types .= "s";
        }

        if (!empty($criteria['price_min'])) {
            $sql .= " AND p.price >= ?";
            $params[] = $criteria['price_min'];
            $types .= "d";
        }

        if (!empty($criteria['price_max'])) {
            $sql .= " AND p.price <= ?";
            $params[] = $criteria['price_max'];
            $types .= "d";
        }

        if (!empty($criteria['bedrooms'])) {
            $sql .= " AND p.bedrooms >= ?";
            $params[] = $criteria['bedrooms'];
            $types .= "i";
        }

        $sql .= " ORDER BY p.featured DESC, p.created_at DESC LIMIT ?";

        // Add limit parameter
        $params[] = $limit;
        $types .= "i";

        // Prepare and execute query
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();

            $properties = [];
            while ($row = $result->fetch_assoc()) {
                $properties[] = $row;
            }
            $stmt->close();

            return $properties;
        }

        return [];
    }

    /**
     * Parse natural language query to extract search criteria
     */
    private function parseNaturalLanguageQuery($query) {
        $criteria = [];
        $query = strtolower($query);

        // Extract location
        foreach ($this->locations as $location) {
            if (strpos($query, $location) !== false) {
                $criteria['location'] = $location;
                break;
            }
        }

        // Extract property type
        foreach ($this->propertyTypes as $type) {
            if (strpos($query, $type) !== false) {
                $criteria['property_type'] = $type;
                break;
            }
        }

        // Extract price information
        preg_match('/(\d+)bhk/i', $query, $bhkMatches);
        if (!empty($bhkMatches[1])) {
            $criteria['bedrooms'] = (int)$bhkMatches[1];
        }

        // Extract price range
        preg_match('/under (\d+)(?:lakh|lac)?/i', $query, $priceMatches);
        if (!empty($priceMatches[1])) {
            $criteria['price_max'] = (int)$priceMatches[1] * 100000; // Convert lakh to rupees
        }

        preg_match('/between (\d+) and (\d+)(?:lakh|lac)?/i', $query, $priceRangeMatches);
        if (!empty($priceRangeMatches)) {
            $criteria['price_min'] = (int)$priceRangeMatches[1] * 100000;
            $criteria['price_max'] = (int)$priceRangeMatches[2] * 100000;
        }

        return $criteria;
    }

    /**
     * Get chat conversation history
     */
    public function getConversationHistory($conversationId, $limit = 20) {
        $sql = "SELECT * FROM ai_chat_messages
                WHERE conversation_id = ?
                ORDER BY created_at DESC LIMIT ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $conversationId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }

        // Reverse to get chronological order
        return array_reverse($messages);
    }

    /**
     * Get AI analytics data
     */
    public function getAIAnalytics() {
        try {
            // Total conversations
            $sql = "SELECT COUNT(*) as total_conversations FROM ai_chat_conversations";
            $result = $this->conn->query($sql);
            $totalConversations = $result->fetch_assoc()['total_conversations'];

            // Total messages
            $sql = "SELECT COUNT(*) as total_messages FROM ai_chat_messages";
            $result = $this->conn->query($sql);
            $totalMessages = $result->fetch_assoc()['total_messages'];

            // Context distribution
            $sql = "SELECT context, COUNT(*) as count FROM ai_chat_messages GROUP BY context";
            $result = $this->conn->query($sql);

            $contextStats = [];
            while ($row = $result->fetch_assoc()) {
                $contextStats[] = $row;
            }

            // Response times
            $sql = "SELECT AVG(TIMESTAMPDIFF(SECOND, created_at, (SELECT created_at FROM ai_chat_messages m2 WHERE m2.conversation_id = ai_chat_messages.conversation_id AND m2.message_type = 'ai' AND m2.created_at > ai_chat_messages.created_at LIMIT 1))) as avg_response_time FROM ai_chat_messages WHERE message_type = 'user'";
            $result = $this->conn->query($sql);
            $avgResponseTime = $result->fetch_assoc()['avg_response_time'] ?? 0;

            return [
                'total_conversations' => $totalConversations,
                'total_messages' => $totalMessages,
                'context_stats' => $contextStats,
                'avg_response_time' => round($avgResponseTime, 2)
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
?>

    /**
     * Find properties based on natural language query
     */
    public function findPropertiesByQuery($query, $limit = 10) {
        // Parse the query to extract search criteria
        $criteria = $this->parseNaturalLanguageQuery($query);

        // Build SQL query based on criteria
        $sql = "SELECT p.*, pt.name as property_type_name,
                       (SELECT image FROM property_images WHERE property_id = p.id LIMIT 1) as main_image
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                WHERE p.status = 'active'";

        $params = [];
        $types = "";

        // Apply filters
        if (!empty($criteria['location'])) {
            $sql .= " AND (p.location LIKE ? OR p.city LIKE ?)";
            $params[] = "%" . $criteria['location'] . "%";
            $params[] = "%" . $criteria['location'] . "%";
            $types .= "ss";
        }

        if (!empty($criteria['property_type'])) {
            $sql .= " AND pt.name LIKE ?";
            $params[] = "%" . $criteria['property_type'] . "%";
            $types .= "s";
        }

        if (!empty($criteria['price_min'])) {
            $sql .= " AND p.price >= ?";
            $params[] = $criteria['price_min'];
            $types .= "d";
        }

        if (!empty($criteria['price_max'])) {
            $sql .= " AND p.price <= ?";
            $params[] = $criteria['price_max'];
            $types .= "d";
        }

        if (!empty($criteria['bedrooms'])) {
            $sql .= " AND p.bedrooms >= ?";
            $params[] = $criteria['bedrooms'];
            $types .= "i";
        }

        $sql .= " ORDER BY p.featured DESC, p.created_at DESC LIMIT ?";

        // Add limit parameter
        $params[] = $limit;
        $types .= "i";

        // Prepare and execute query
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();

            $properties = [];
            while ($row = $result->fetch_assoc()) {
                $properties[] = $row;
            }
            $stmt->close();

            return $properties;
        }

        return [];
    }

    /**
     * Parse natural language query to extract search criteria
     */
    private function parseNaturalLanguageQuery($query) {
        $criteria = [];
        $query = strtolower($query);

        // Extract location
        foreach ($this->locations as $location) {
            if (strpos($query, $location) !== false) {
                $criteria['location'] = $location;
                break;
            }
        }

        // Extract property type
        foreach ($this->propertyTypes as $type) {
            if (strpos($query, $type) !== false) {
                $criteria['property_type'] = $type;
                break;
            }
        }

        // Extract price information
        preg_match('/(\d+)bhk/i', $query, $bhkMatches);
        if (!empty($bhkMatches[1])) {
            $criteria['bedrooms'] = (int)$bhkMatches[1];
        }

        // Extract price range
        preg_match('/under (\d+)(?:lakh|lac)?/i', $query, $priceMatches);
        if (!empty($priceMatches[1])) {
            $criteria['price_max'] = (int)$priceMatches[1] * 100000; // Convert lakh to rupees
        }

        preg_match('/between (\d+) and (\d+)(?:lakh|lac)?/i', $query, $priceRangeMatches);
        if (!empty($priceRangeMatches)) {
            $criteria['price_min'] = (int)$priceRangeMatches[1] * 100000;
            $criteria['price_max'] = (int)$priceRangeMatches[2] * 100000;
        }

        return $criteria;
    }

    /**
     * Get property recommendations for a user
     */
    public function getRecommendations($userId, $limit = 5) {
        // Get user's previous searches or preferences
        $sql = "SELECT p.*, pt.name as property_type_name,
                       (SELECT image FROM property_images WHERE property_id = p.id LIMIT 1) as main_image
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                WHERE p.status = 'active' AND p.featured = 1
                ORDER BY p.created_at DESC LIMIT ?";

        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            $result = $stmt->get_result();

            $properties = [];
            while ($row = $result->fetch_assoc()) {
                $properties[] = $row;
            }
            $stmt->close();

            return $properties;
        }

        return [];
    }

    /**
     * Get property suggestions based on partial input
     */
    public function getSuggestions($input, $type = 'location') {
        $suggestions = [];

        if ($type === 'location') {
            foreach ($this->locations as $location) {
                if (stripos($location, $input) === 0) {
                    $suggestions[] = $location;
                }
            }
        } elseif ($type === 'property_type') {
            foreach ($this->propertyTypes as $type) {
                if (stripos($type, $input) === 0) {
                    $suggestions[] = $type;
                }
            }
        }

        return array_slice($suggestions, 0, 5);
    }

    /**
     * Calculate property match score for a user
     */
    public function calculateMatchScore($property, $userPreferences = []) {
        $score = 0;

        // Base score for active property
        if ($property['status'] === 'active') {
            $score += 50;
        }

        // Featured property bonus
        if ($property['featured']) {
            $score += 30;
        }

        // Price range match
        if (!empty($userPreferences['budget_min']) && !empty($userPreferences['budget_max'])) {
            if ($property['price'] >= $userPreferences['budget_min'] &&
                $property['price'] <= $userPreferences['budget_max']) {
                $score += 20;
            }
        }

        // Property type match
        if (!empty($userPreferences['property_type']) &&
            stripos($property['property_type_name'], $userPreferences['property_type']) !== false) {
            $score += 20;
        }

        // Location preference
        if (!empty($userPreferences['preferred_locations'])) {
            foreach ($userPreferences['preferred_locations'] as $location) {
                if (stripos($property['location'], $location) !== false ||
                    stripos($property['city'], $location) !== false) {
                    $score += 15;
                    break;
                }
            }
        }

        return min($score, 100); // Max score is 100
    }
}
?>
