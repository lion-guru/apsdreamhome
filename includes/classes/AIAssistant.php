<?php
/**
 * Secure AI Assistant Class
 * Handles AI-powered suggestions with robust security measures
 */
class AIAssistant {
    private $dbSecurity;
    private $logger;
    private $openaiApiKey;

    /**
     * Constructor with Dependency Injection
     * @param DatabaseSecurityUpgrade $dbSecurity Secure database connection
     */
    public function __construct(DatabaseSecurityUpgrade $dbSecurity) {
        $this->dbSecurity = $dbSecurity;
        $this->initLogger();
        $this->loadApiCredentials();
    }

    /**
     * Initialize secure logging mechanism
     */
    private function initLogger() {
        // Implement a secure logging mechanism
        $this->logger = new class {
            public function log($level, $message, $context = []) {
                $logFile = __DIR__ . '/../../logs/ai_assistant.log';
                $timestamp = date('Y-m-d H:i:s');
                $logEntry = "[{$timestamp}] [{$level}] " . 
                    json_encode(['message' => $message, 'context' => $context]) . PHP_EOL;
                
                error_log($logEntry, 3, $logFile);
            }
        };
    }

    /**
     * Load API credentials securely
     */
    private function loadApiCredentials() {
        try {
            $this->openaiApiKey = getenv('OPENAI_// SECURITY: Sensitive information removed');
            if (!$this->openaiApiKey) {
                throw new Exception('OpenAI API Key not configured');
            }
        } catch (Exception $e) {
            $this->logger->log('error', 'API Credential Loading Failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Generate AI-powered suggestions for real estate
     * @param int $userId User identifier
     * @param array $context Contextual information for suggestions
     * @return array AI-generated suggestions
     */
    public function generateSuggestions(int $userId, array $context = []) {
        try {
            // Validate user context
            $this->validateUserContext($userId, $context);

            // Prepare AI request payload
            $payload = $this->prepareSuggestionPayload($userId, $context);

            // Make secure API request
            $suggestions = $this->makeOpenAIRequest($payload);

            // Log successful suggestion generation
            $this->logger->log('info', 'AI Suggestions Generated', [
                'user_id' => $userId,
                'suggestion_count' => count($suggestions)
            ]);

            return $suggestions;
        } catch (Exception $e) {
            // Log and handle errors
            $this->logger->log('error', 'Suggestion Generation Failed', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            throw new Exception('Unable to generate AI suggestions');
        }
    }

    /**
     * Validate user context before generating suggestions
     * @param int $userId User identifier
     * @param array $context Contextual information
     * @throws Exception If context is invalid
     */
    private function validateUserContext(int $userId, array $context) {
        // Implement comprehensive validation
        if ($userId <= 0) {
            throw new Exception('Invalid User ID');
        }

        // Additional context validation logic
        $requiredContextKeys = ['property_type', 'budget', 'location'];
        foreach ($requiredContextKeys as $key) {
            if (!isset($context[$key]) || empty($context[$key])) {
                throw new Exception("Missing required context: {$key}");
            }
        }
    }

    /**
     * Prepare payload for OpenAI API
     * @param int $userId User identifier
     * @param array $context Contextual information
     * @return array Prepared API payload
     */
    private function prepareSuggestionPayload(int $userId, array $context): array {
        return [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system', 
                    'content' => 'You are a helpful real estate AI assistant.'
                ],
                [
                    'role' => 'user',
                    'content' => $this->constructPrompt($userId, $context)
                ]
            ],
            'max_tokens' => 150,
            'temperature' => 0.7
        ];
    }

    /**
     * Construct AI prompt based on user context
     * @param int $userId User identifier
     * @param array $context Contextual information
     * @return string Constructed prompt
     */
    private function constructPrompt(int $userId, array $context): string {
        return sprintf(
            "Generate 3 personalized real estate suggestions for a user looking for a %s property in %s with a budget of %s. " .
            "Consider user preferences and market trends.",
            $context['property_type'],
            $context['location'],
            $context['budget']
        );
    }

    /**
     * Make secure API request to OpenAI
     * @param array $payload API request payload
     * @return array AI-generated suggestions
     */
    private function makeOpenAIRequest(array $payload): array {
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->openaiApiKey,
                'OpenAI-Organization: org-your_org_id'
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            throw new Exception('OpenAI API Request Failed: ' . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception("OpenAI API Error: HTTP {$httpCode}");
        }

        $result = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid API Response');
        }

        return $this->extractSuggestions($result);
    }

    /**
     * Extract suggestions from OpenAI response
     * @param array $apiResponse Raw API response
     * @return array Processed suggestions
     */
    private function extractSuggestions(array $apiResponse): array {
        if (!isset($apiResponse['choices'][0]['message']['content'])) {
            throw new Exception('No suggestions found in API response');
        }

        $rawSuggestions = $apiResponse['choices'][0]['message']['content'];
        
        // Basic parsing of suggestions
        $suggestionList = array_filter(
            explode("\n", $rawSuggestions), 
            function($suggestion) {
                return trim($suggestion) !== '';
            }
        );

        return array_map(function($suggestion) {
            return $this->dbSecurity->sanitizeInput($suggestion);
        }, $suggestionList);
    }
}

