<?php

namespace App\Services\AI\Agents;
/**
 * WhatsAppAgent - Autonomous AI Agent for WhatsApp Communication
 */
require_once __DIR__ . '/BaseAgent.php';
require_once __DIR__ . '/../../Legacy/whatsapp_integration.php';
require_once __DIR__ . '/../../Legacy/ai_learning_system.php';
require_once __DIR__ . '/../../Legacy/ai_personality_system.php';

use App\Services\Legacy\WhatsAppIntegration;
use App\Services\Legacy\AILearningSystem;
use App\Services\Legacy\AIAgentPersonality;

class WhatsAppAgent extends BaseAgent {
    private $whatsapp;
    private $learningSystem;
    private $personalitySystem;
    private $supportedLanguages = ['en', 'hi', 'es'];

    public function __construct() {
        parent::__construct('WA_AGENT_001', 'WhatsApp Autonomous Agent');
        $this->whatsapp = new WhatsAppIntegration();
        $this->learningSystem = new AILearningSystem();
        $this->personalitySystem = new AIAgentPersonality();

        $this->config = [
            'auto_reply' => true,
            'sentiment_threshold' => 0.5,
            'default_language' => 'hi',
            'max_retries' => 3
        ];
    }

    /**
     * Process incoming WhatsApp messages autonomously
     */
    public function process($input, $context = []) {
        $this->status = 'processing';
        $sender = $input['from'] ?? '';
        $messageText = $input['text'] ?? '';
        $messageType = $input['type'] ?? 'text';
        $mediaUrl = $input['media_url'] ?? null;

        $this->logActivity("INCOMING_MESSAGE", "From: $sender, Type: $messageType", ['text' => $messageText, 'media_url' => $mediaUrl]);

        // 1. Sentiment Analysis
        $sentiment = $this->analyzeSentiment($messageText);

        // 2. Language Detection
        $lang = $this->detectLanguage($messageText);

        // 3. Learning from interaction
        $this->learningSystem->learnFromInteraction($sender, $messageText, [
            'sentiment' => $sentiment,
            'language' => $lang,
            'media_url' => $mediaUrl
        ]);

        // 4. Generate Autonomous Response
        $response = $this->generateResponse($messageText, $sender, $lang, $sentiment);

        // 5. Send Response (with multimedia support if needed)
        if ($this->config['auto_reply']) {
            $replyType = 'text';
            $replyMedia = null;

            // Simple logic to attach property brochure if requested
            if (stripos($messageText, 'brochure') !== false || stripos($messageText, 'details') !== false) {
                $replyType = 'document';
                $replyMedia = SITE_URL . '/assets/docs/property_brochure.pdf';
            }

            $result = $this->whatsapp->sendMessage($sender, $response, $replyType, $replyMedia);
            $this->logActivity("OUTGOING_REPLY", "To: $sender", [
                'reply' => $response,
                'type' => $replyType,
                'media' => $replyMedia,
                'success' => $result['success']
            ]);
            return $result;
        }

        $this->status = 'ready';
        return ['success' => true, 'response' => $response];
    }

    private function analyzeSentiment($text) {
        // Basic sentiment analysis logic
        $positiveWords = ['good', 'great', 'happy', 'thanks', 'धन्यवाद', 'अच्छा', 'excelente'];
        $negativeWords = ['bad', 'poor', 'angry', 'late', 'बेकार', 'खराब', 'malo'];

        $score = 0;
        foreach ($positiveWords as $word) if (stripos($text, $word) !== false) $score += 0.2;
        foreach ($negativeWords as $word) if (stripos($text, $word) !== false) $score -= 0.2;

        return max(-1, min(1, $score));
    }

    private function detectLanguage($text) {
        // Simple regex based detection for Hindi/English
        if (preg_match('/[\x{0900}-\x{097F}]/u', $text)) return 'hi';
        return 'en'; // Default to English
    }

    private function generateResponse($text, $sender, $lang, $sentiment) {
        // Use personality system to get context-aware response
        $personality = ($sentiment < -0.3) ? 'empathetic' : 'professional';

        $context = [
            'language' => $lang,
            'sentiment' => $sentiment,
            'sender_role' => $this->getUserRole($sender),
            'personality_mode' => $personality
        ];

        // This calls the personality system which uses AIDreamHome internally
        return $this->personalitySystem->generatePersonalizedResponse($text, $context);
    }

    private function getUserRole($phone) {
        // Fetch user role from database based on phone number
        $stmt = $this->db->prepare("SELECT role FROM users WHERE phone = ? LIMIT 1");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row['role'];
        }
        return 'guest';
    }

    /**
     * Handle Multimedia processing
     */
    public function handleMedia($mediaUrl, $type, $sender) {
        $this->logActivity("MEDIA_RECEIVED", "Type: $type, From: $sender", ['url' => $mediaUrl]);
        // Logic to download, scan for viruses, and potentially analyze image/doc
        return ['success' => true, 'status' => 'analyzing'];
    }

    /**
     * Schedule a message
     */
    public function scheduleMessage($to, $message, $time, $timezone = 'Asia/Kolkata') {
        $this->logActivity("MESSAGE_SCHEDULED", "To: $to, Time: $time", ['timezone' => $timezone]);
        // Insert into a tasks table for AsyncTaskManager to pick up
        $stmt = $this->db->prepare("INSERT INTO scheduled_tasks (task_type, payload, run_at) VALUES ('whatsapp_message', ?, ?)");
        $payload = json_encode(['to' => $to, 'message' => $message]);
        $stmt->bind_param("ss", $payload, $time);
        return $stmt->execute();
    }
}
