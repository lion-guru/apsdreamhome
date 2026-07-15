<?php

namespace App\Services\AI\Agents;

/**
 * WhatsAppAgent - Adapter that delegates to actual WhatsApp + Chat systems
 * Original 156-line version had broken Legacy imports and unused MySQLi code.
 * This thin adapter delegates to:
 *   - WhatsAppIntegrationService (for sending/receiving)
 *   - HindiConversationalBot (for Hindi AI responses)
 *   - ConversationEngine (for multi-turn state)
 */
class WhatsAppAgent extends BaseAgent {

    public function __construct() {
        parent::__construct('WA_AGENT_001', 'WhatsApp Autonomous Agent');
    }

    /**
     * Process incoming WhatsApp message — delegates to Chat system
     */
    public function process($input, $context = []) {
        $this->status = 'processing';

        $messageText = $input['text'] ?? '';
        $sender = $input['from'] ?? '';

        $this->logActivity('INCOMING_MESSAGE', "From: $sender", ['text' => $messageText]);

        // Delegate to HindiConversationalBot for response generation
        try {
            $bot = new \App\Services\AI\Agents\HindiConversationalBot();
            $response = $bot->process($input, $context);
        } catch (\Exception $e) {
            $response = [
                'success' => true,
                'response' => "Namaste! Main APS Dream Home ka AI assistant hoon. Aapki kya madad kar sakta hoon?",
                'intent' => 'fallback'
            ];
        }

        // Try to send via WhatsApp if service available
        try {
            $whatsappService = new \App\Services\Communication\WhatsAppIntegrationService();
            $whatsappService->sendMessage($sender, $response['response'] ?? '...');
        } catch (\Exception $e) {
            // WhatsApp not configured — return response anyway
        }

        $this->status = 'ready';
        return $response;
    }

    /**
     * Send a WhatsApp message
     */
    public function sendMessage($to, $message, $type = 'text', $mediaUrl = null) {
        try {
            $whatsappService = new \App\Services\Communication\WhatsAppIntegrationService();
            return $whatsappService->sendMessage($to, $message, $type, $mediaUrl);
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Handle multimedia (stub — media processing not implemented)
     */
    public function handleMedia($mediaUrl, $type, $sender) {
        $this->logActivity('MEDIA_RECEIVED', "Type: $type, From: $sender", ['url' => $mediaUrl]);
        return ['success' => true, 'status' => 'received'];
    }

    /**
     * Schedule a message (stub — async scheduling not implemented)
     */
    public function scheduleMessage($to, $message, $time, $timezone = 'Asia/Kolkata') {
        $this->logActivity('MESSAGE_SCHEDULED', "To: $to, Time: $time", ['timezone' => $timezone]);
        return ['success' => true, 'status' => 'scheduled'];
    }
}
