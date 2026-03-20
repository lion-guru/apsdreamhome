<?php

namespace App\Services;

use App\Core\Database\Database;
use Exception;

class AIChatbotService
{
    private $db;
    private $intents = [
        'greeting' => ['hello', 'hi', 'hey', 'good morning', 'good evening', 'namaste'],
        'property_search' => ['property', 'house', 'home', 'apartment', 'villa', 'search', 'find', 'looking for'],
        'pricing' => ['price', 'cost', 'rate', 'budget', 'cheap', 'expensive', 'affordable'],
        'location' => ['location', 'area', 'city', 'near', 'close to', 'address'],
        'amenities' => ['amenities', 'facilities', 'features', 'swimming pool', 'gym', 'parking'],
        'contact' => ['contact', 'call', 'phone', 'email', 'visit', 'appointment'],
        'financing' => ['loan', 'finance', 'emi', 'payment', 'installment', 'bank'],
        'booking' => ['book', 'reserve', 'booking', 'register', 'sign up'],
        'help' => ['help', 'support', 'assistance', 'question', 'how to'],
        'goodbye' => ['bye', 'goodbye', 'see you', 'thank you', 'thanks']
    ];

    private $responses = [
        'greeting' => [
            "Hello! Welcome to APS Dream Home! How can I help you find your dream property today?",
            "Hi there! I'm here to assist you with all your real estate needs. What are you looking for?",
            "Namaste! Welcome to APS Dream Home. I can help you explore our premium properties."
        ],
        'property_search' => [
            "I'd be happy to help you find the perfect property! What type of property are you looking for - apartment, villa, or commercial space?",
            "Great! Let me help you search for properties. What's your preferred location and budget range?",
            "I can assist you with property search. Could you tell me more about your requirements?"
        ],
        'pricing' => [
            "We have properties ranging from ₹20 lakhs to ₹5 crores. What's your budget range?",
            "Our pricing varies based on location, size, and amenities. What's your preferred budget?",
            "I can help you find properties within your budget. What price range are you considering?"
        ],
        'location' => [
            "We have premium properties in Mumbai, Pune, Bangalore, and Delhi. Which location interests you?",
            "Great choice! We have properties in prime locations. Which city or area do you prefer?",
            "I can help you find properties in your preferred location. Where are you looking to buy?"
        ],
        'amenities' => [
            "Our properties come with premium amenities like swimming pools, gyms, clubhouses, and 24/7 security.",
            "We offer world-class amenities including landscaped gardens, children's play areas, and community spaces.",
            "Our projects feature modern amenities for comfortable living. What specific facilities are you looking for?"
        ],
        'contact' => [
            "You can reach us at +91-9876543210 or email us at info@apsdreamhome.com. Would you like to schedule a site visit?",
            "I'd be happy to connect you with our property experts. You can call us at +91-9876543210 or visit our office.",
            "Our team is ready to assist you! Call us at +91-9876543210 or fill out the contact form on our website."
        ],
        'financing' => [
            "We offer easy financing options with leading banks. EMI starts from just ₹15,000 per month.",
            "We have tie-ups with major banks for home loans. I can help you understand the financing options.",
            "Our financial experts can help you with loan approval. What's your budget and preferred loan tenure?"
        ],
        'booking' => [
            "Great! To book a property, I'll need some details. What property are you interested in?",
            "I can help you with the booking process. Which property would you like to reserve?",
            "Excellent choice! Let me guide you through the booking process. What's your preferred property?"
        ],
        'help' => [
            "I'm here to help! You can ask me about properties, pricing, locations, amenities, or booking process.",
            "I can assist you with property search, financing options, site visits, and more. What do you need help with?",
            "Feel free to ask me anything about APS Dream Home properties and services. How can I assist you?"
        ],
        'goodbye' => [
            "Thank you for visiting APS Dream Home! Feel free to reach out anytime. Have a great day!",
            "It was great helping you today! Remember, I'm here whenever you need assistance with your property search.",
            "Thank you for choosing APS Dream Home. We look forward to helping you find your dream home!"
        ],
        'fallback' => [
            "I'm not sure I understand. Could you please rephrase your question?",
            "I'm still learning. Could you tell me more about what you're looking for?",
            "I want to help you better. Could you provide more details about your query?"
        ]
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Process user message and generate response
     */
    public function processMessage($sessionId, $message, $userId = null)
    {
        try {
            // Store user message
            $this->storeMessage($sessionId, $message, null, null, 0, false);

            // Detect intent and entities
            $intentData = $this->detectIntent($message);

            // Generate response
            $response = $this->generateResponse($intentData);

            // Store bot response
            $this->storeMessage($sessionId, $response, $intentData['intent'], json_encode($intentData['entities']), $intentData['confidence'], true);

            return [
                'success' => true,
                'response' => $response,
                'intent' => $intentData['intent'],
                'confidence' => $intentData['confidence'],
                'entities' => $intentData['entities']
            ];
        } catch (Exception $e) {
            error_log("Chatbot processing error: " . $e->getMessage());

            $fallbackResponse = $this->responses['fallback'][array_rand($this->responses['fallback'])];
            $this->storeMessage($sessionId, $fallbackResponse, 'fallback', '{}', 0.5, true);

            return [
                'success' => true,
                'response' => $fallbackResponse,
                'intent' => 'fallback',
                'confidence' => 0.5,
                'entities' => []
            ];
        }
    }

    /**
     * Detect intent from message
     */
    private function detectIntent($message)
    {
        $message = strtolower(trim($message));
        $bestIntent = 'fallback';
        $bestConfidence = 0;
        $entities = [];

        foreach ($this->intents as $intent => $keywords) {
            $confidence = 0;
            $matchedKeywords = [];

            foreach ($keywords as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    $confidence += 0.3;
                    $matchedKeywords[] = $keyword;
                }
            }

            // Extract entities based on intent
            if ($confidence > 0) {
                $entities = $this->extractEntities($message, $intent);
                $confidence = min($confidence, 1.0);
            }

            if ($confidence > $bestConfidence) {
                $bestConfidence = $confidence;
                $bestIntent = $intent;
            }
        }

        return [
            'intent' => $bestIntent,
            'confidence' => $bestConfidence,
            'entities' => $entities
        ];
    }

    /**
     * Extract entities from message
     */
    private function extractEntities($message, $intent)
    {
        $entities = [];

        switch ($intent) {
            case 'pricing':
                // Extract budget amounts
                if (preg_match('/(\d+(?:,\d+)*)\s*(?:lakhs?|lakh|crore|cr|rupees?|rs?)/i', $message, $matches)) {
                    $entities['budget'] = $matches[1];
                }
                break;

            case 'location':
                // Extract city names
                $cities = ['mumbai', 'pune', 'bangalore', 'delhi', 'hyderabad', 'chennai', 'kolkata'];
                foreach ($cities as $city) {
                    if (strpos($message, $city) !== false) {
                        $entities['city'] = $city;
                        break;
                    }
                }
                break;

            case 'property_search':
                // Extract property types
                $types = ['apartment', 'villa', 'commercial', 'plot', 'flat', 'house'];
                foreach ($types as $type) {
                    if (strpos($message, $type) !== false) {
                        $entities['property_type'] = $type;
                        break;
                    }
                }
                break;

            case 'amenities':
                // Extract specific amenities
                $amenityList = ['swimming pool', 'gym', 'parking', 'garden', 'security', 'clubhouse'];
                $foundAmenities = [];
                foreach ($amenityList as $amenity) {
                    if (strpos($message, $amenity) !== false) {
                        $foundAmenities[] = $amenity;
                    }
                }
                if (!empty($foundAmenities)) {
                    $entities['amenities'] = $foundAmenities;
                }
                break;
        }

        return $entities;
    }

    /**
     * Generate response based on intent
     */
    private function generateResponse($intentData)
    {
        $intent = $intentData['intent'];
        $entities = $intentData['entities'];

        if (isset($this->responses[$intent])) {
            $responses = $this->responses[$intent];
            $baseResponse = $responses[array_rand($responses)];

            // Personalize response with entities
            if (!empty($entities)) {
                $baseResponse = $this->personalizeResponse($baseResponse, $entities, $intent);
            }

            return $baseResponse;
        }

        return $this->responses['fallback'][array_rand($this->responses['fallback'])];
    }

    /**
     * Personalize response with extracted entities
     */
    private function personalizeResponse($response, $entities, $intent)
    {
        switch ($intent) {
            case 'pricing':
                if (isset($entities['budget'])) {
                    $response = "Based on your budget of {$entities['budget']} lakhs, I can show you some great options. Would you like to see properties in this range?";
                }
                break;

            case 'location':
                if (isset($entities['city'])) {
                    $response = "Great choice! We have excellent properties in " . ucfirst($entities['city']) . ". What type of property are you looking for there?";
                }
                break;

            case 'property_search':
                if (isset($entities['property_type'])) {
                    $response = "Perfect! We have premium {$entities['property_type']} options available. What's your preferred location and budget?";
                }
                break;

            case 'amenities':
                if (isset($entities['amenities'])) {
                    $amenityList = implode(', ', $entities['amenities']);
                    $response = "Excellent! Our properties offer $amenityList along with many other premium facilities. Which location interests you?";
                }
                break;
        }

        return $response;
    }

    /**
     * Store conversation message
     */
    private function storeMessage($sessionId, $message, $intent, $entities, $confidence, $isBot)
    {
        $query = "INSERT INTO chatbot_conversations (user_id, session_id, message, response, intent, entities, confidence, is_bot) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $this->db->execute($query, [
            $_SESSION['user_id'] ?? null,
            $sessionId,
            $isBot ? '' : $message,
            $isBot ? $message : '',
            $intent,
            $entities,
            $confidence,
            $isBot
        ]);
    }

    /**
     * Get conversation history
     */
    public function getConversationHistory($sessionId, $limit = 20)
    {
        $query = "SELECT * FROM chatbot_conversations WHERE session_id = ? ORDER BY created_at ASC LIMIT ?";
        return $this->db->fetchAll($query, [$sessionId, $limit]);
    }

    /**
     * Clear conversation history
     */
    public function clearConversationHistory($sessionId)
    {
        $query = "DELETE FROM chatbot_conversations WHERE session_id = ?";
        $this->db->execute($query, [$sessionId]);
        return true;
    }

    /**
     * Get chatbot analytics
     */
    public function getChatbotAnalytics($startDate = null, $endDate = null)
    {
        $dateCondition = "";
        $params = [];

        if ($startDate && $endDate) {
            $dateCondition = "WHERE created_at BETWEEN ? AND ?";
            $params = [$startDate, $endDate];
        }

        $query = "SELECT 
                    COUNT(*) as total_conversations,
                    COUNT(DISTINCT session_id) as unique_sessions,
                    COUNT(DISTINCT user_id) as unique_users,
                    AVG(confidence) as avg_confidence,
                    intent,
                    COUNT(*) as intent_count
                FROM chatbot_conversations 
                $dateCondition
                GROUP BY intent
                ORDER BY intent_count DESC";

        return $this->db->fetchAll($query, $params);
    }

    /**
     * Train chatbot with new responses
     */
    public function trainChatbot($intent, $examples, $responses)
    {
        try {
            // This would typically involve machine learning
            // For now, we'll just update the response patterns
            if (!isset($this->responses[$intent])) {
                $this->responses[$intent] = [];
            }

            $this->responses[$intent] = array_merge($this->responses[$intent], $responses);

            // Store training data for future ML implementation
            $query = "INSERT INTO ai_chatbot_training (intent, examples, responses, created_at) VALUES (?, ?, ?, NOW())";
            $this->db->execute($query, [$intent, json_encode($examples), json_encode($responses)]);

            return true;
        } catch (Exception $e) {
            error_log("Chatbot training error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get popular intents
     */
    public function getPopularIntents($limit = 10)
    {
        $query = "SELECT intent, COUNT(*) as count FROM chatbot_conversations WHERE is_bot = 0 GROUP BY intent ORDER BY count DESC LIMIT ?";
        return $this->db->fetchAll($query, [$limit]);
    }

    /**
     * Get conversation statistics
     */
    public function getConversationStats($sessionId = null)
    {
        $condition = "";
        $params = [];

        if ($sessionId) {
            $condition = "WHERE session_id = ?";
            $params = [$sessionId];
        }

        $query = "SELECT 
                    COUNT(*) as total_messages,
                    COUNT(DISTINCT session_id) as total_sessions,
                    AVG(confidence) as avg_confidence,
                    MAX(created_at) as last_activity
                FROM chatbot_conversations 
                $condition";

        return $this->db->fetch($query, $params);
    }
}
