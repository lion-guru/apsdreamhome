<?php

namespace App\Services\AI;

use App\Core\Database\Database;

/**
 * AI Gemini Chatbot Service
 * Advanced chatbot with Google Gemini API integration
 */
class AIGeminiChatbotService
{
    use \App\Traits\ServiceTenantTrait;

    private $database;
    private $geminiApiKey;
    private $geminiApiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';
    
    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->geminiApiKey = $_ENV['GEMINI_API_KEY'] ?? '';
    }
    
    /**
     * Process user message with Gemini AI
     */
    public function processMessage(string $message, array $context = []): array
    {
        $detectedLanguage = $this->detectLanguage($message);
        $intent = $this->detectIntent($message);
        
        // If Gemini API key is configured, use it for enhanced responses
        if (!empty($this->geminiApiKey)) {
            $geminiResponse = $this->callGeminiAPI($message, $context);
            if ($geminiResponse['success']) {
                return [
                    'response' => $geminiResponse['text'],
                    'intent' => $intent,
                    'language' => $detectedLanguage,
                    'confidence' => $geminiResponse['confidence'] ?? 0.95,
                    'source' => 'gemini',
                    'actions' => $this->extractActions($geminiResponse['text'])
                ];
            }
        }
        
        // Fallback to local AI
        return $this->generateLocalResponse($message, $intent, $detectedLanguage);
    }
    
    /**
     * Call Google Gemini API
     */
    private function callGeminiAPI(string $message, array $context): array
    {
        try {
            $prompt = $this->buildPrompt($message, $context);
            
            $url = $this->geminiApiUrl . '?key=' . $this->geminiApiKey;
            
            $data = [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 1024,
                    'topP' => 0.9,
                    'topK' => 40
                ],
                'safetySettings' => [
                    [
                        'category' => 'HARM_CATEGORY_HARASSMENT',
                        'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                    ],
                    [
                        'category' => 'HARM_CATEGORY_HATE_SPEECH',
                        'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                    ]
                ]
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200 && $response) {
                $result = json_decode($response, true);
                
                if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                    return [
                        'success' => true,
                        'text' => $result['candidates'][0]['content']['parts'][0]['text'],
                        'confidence' => 0.95,
                        'raw' => $result
                    ];
                }
            }
            
            return ['success' => false, 'error' => 'Invalid API response'];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Build prompt for Gemini
     */
    private function buildPrompt(string $message, array $context): string
    {
        $propertyContext = '';
        if (!empty($context['property'])) {
            $p = $context['property'];
            $propertyContext = "Property Context:\n";
            $propertyContext .= "- Name: {$p['name']}\n";
            $propertyContext .= "- Type: {$p['type']}\n";
            $propertyContext .= "- Price: ₹" . number_format($p['price']) . "\n";
            $propertyContext .= "- Location: {$p['location']}\n";
        }
        
        return <<<PROMPT
You are a helpful real estate assistant for APS Dream Home, a real estate and MLM company in India.

{$propertyContext}

User Query: {$message}

Respond naturally in the same language as the user query. Be helpful, professional, and concise.
If the query is about buying/renting properties, provide relevant guidance.
If the query is about joining as an associate/agent, explain the benefits.
If the query is about loans/legal services, provide helpful information.

Response:
PROMPT;
    }
    
    /**
     * Detect language (Hindi or English)
     */
    private function detectLanguage(string $message): string
    {
        $hindiPattern = '/[\x{0900}-\x{097F}]/u';
        return preg_match($hindiPattern, $message) ? 'hi' : 'en';
    }
    
    /**
     * Detect user intent
     */
    private function detectIntent(string $message): string
    {
        $message = strtolower($message);
        
        $intents = [
            'buy' => ['buy', 'purchase', 'kharidna', 'kharid', 'property chahiye', 'ghar chahiye'],
            'sell' => ['sell', 'sale', 'bechna', 'bech', 'property dena'],
            'rent' => ['rent', 'rental', 'kiraye', 'kiraya', 'rent pe chahiye'],
            'loan' => ['loan', 'finance', 'emi', 'loan chahiye', 'home loan'],
            'join' => ['join', 'associate', 'agent', 'mlm', 'join karna', 'associate banana'],
            'contact' => ['contact', 'call', 'phone', 'number', 'contact karna'],
            'price' => ['price', 'cost', 'kitne ka', 'rate', 'price kya'],
        ];
        
        foreach ($intents as $intent => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    return $intent;
                }
            }
        }
        
        return 'general';
    }
    
    /**
     * Generate local AI response (fallback)
     */
    private function generateLocalResponse(string $message, string $intent, string $language): array
    {
        $responses = [
            'hi' => [
                'buy' => "🏠 Properties kharidne ke liye humare pass kai options hain. Aapko kis area mein property chahiye?",
                'sell' => "✅ Property bechne ke liye humari help le sakte hain. Aapki property details dijiye.",
                'rent' => "🔑 Rent properties available hain. Budget aur location bataiye.",
                'loan' => "💰 Home loan assistance available hai. Aapki requirements kya hain?",
                'join' => "🤝 Join as associate! Good commission structure hai. Details chahiye?",
                'general' => "Namaste! 🙏 Main aapki madad kar sakta hoon properties, loans, ya associate joining mein."
            ],
            'en' => [
                'buy' => "🏠 We have many properties for purchase. Which area are you interested in?",
                'sell' => "✅ We can help you sell your property. Please share the details.",
                'rent' => "🔑 We have rental properties available. What's your budget and preferred location?",
                'loan' => "💰 We provide home loan assistance. What are your requirements?",
                'join' => "🤝 Join us as an associate! We offer great commission structures. Want details?",
                'general' => "Hello! 🙏 I can help you with properties, loans, or joining as an associate."
            ]
        ];
        
        return [
            'response' => $responses[$language][$intent] ?? $responses[$language]['general'],
            'intent' => $intent,
            'language' => $language,
            'confidence' => 0.8,
            'source' => 'local',
            'actions' => []
        ];
    }
    
    /**
     * Extract actionable items from response
     */
    private function extractActions(string $response): array
    {
        $actions = [];
        
        if (strpos($response, 'property') !== false || strpos($response, 'buy') !== false) {
            $actions[] = ['type' => 'link', 'label' => 'View Properties', 'url' => '/properties'];
        }
        
        if (strpos($response, 'loan') !== false) {
            $actions[] = ['type' => 'link', 'label' => 'Apply for Loan', 'url' => '/services/loan'];
        }
        
        if (strpos($response, 'associate') !== false || strpos($response, 'join') !== false) {
            $actions[] = ['type' => 'link', 'label' => 'Join as Associate', 'url' => '/associate/register'];
        }
        
        return $actions;
    }
    
    /**
     * Save conversation to database
     */
    public function saveConversation(int $userId, string $userMessage, string $botResponse, string $intent): bool
    {
        try {
            $db = $this->database->getConnection();
            $tenantData = $this->tenantInsertData();
            $tenantCols = array_keys($tenantData);
            $tenantVals = array_values($tenantData);
            $columns = array_merge(['user_id', 'user_message', 'bot_response', 'intent', 'created_at'], $tenantCols);
            $values  = array_merge([$userId, $userMessage, $botResponse, $intent], $tenantVals);
            $colStr = implode(', ', $columns);
            $placeholders = implode(', ', array_fill(0, count($values), '?'));
            $stmt = $db->prepare("INSERT INTO ai_conversations ($colStr) VALUES ($placeholders)");
            return $stmt->execute($values);
        } catch (\Exception $e) {
            return false;
        }
    }
}
