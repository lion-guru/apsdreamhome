<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Services\AI\AIManager;
use App\Services\Voice\AIVoicePipeline;
use App\Services\Communication\WhatsAppWebService;
use App\Traits\TenantAwareTrait;

class AIBotController extends BaseController
{
    use TenantAwareTrait;

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    private function ai(): AIManager
    {
        return new AIManager($this->db);
    }

    // Chat API - powered by self-learning AI
    public function chat()
    {
        header('Content-Type: application/json');

        $message = trim($_POST['message'] ?? $_GET['message'] ?? '');
        $sessionId = $_POST['session_id'] ?? $_GET['session_id'] ?? session_id();
        $platform = $_POST['platform'] ?? 'website';
        $userId = (int)($_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0);

        if (empty($message)) {
            echo json_encode(['error' => 'Message is required']);
            exit;
        }

        // Process via self-learning AI
        $aiResponse = $this->ai()->processChat($sessionId, $userId, $message, $platform);

        // Make the property-aware LLM (Ollama local → Gemini cloud → rule fallback)
        // the primary reply source. The pattern matcher's precise answers remain
        // as the automatic fallback if the LLM is unavailable. Buy/rent property
        // suggestions below still run independently.
        try {
            $pipeline = new AIVoicePipeline();
            $llmReply = $pipeline->generateChatReply($message);
            if (!empty($llmReply)) {
                $aiResponse['text'] = $llmReply;
                $aiResponse['engine'] = 'llm';
            }
        } catch (\Throwable $e) {
            // keep pattern-based reply on any LLM failure
        }

        // Also save to legacy ai_conversations for backward compatibility
        $this->saveConversation($sessionId, $message, $aiResponse['text'], $aiResponse['intent'], $platform);

        // Track behavior
        try {
            $this->ai()->track($userId, 'chat_message', null, 'chat', null, ['message' => $message], $sessionId);
        } catch (\Exception $e) {}

        // If intent is buy/rent and user is logged in, suggest properties
        $suggestions = [];
        if (in_array($aiResponse['intent'], ['buy_property', 'rent_property']) && $userId) {
            try {
                $recs = $this->ai()->getRecommendations($userId, 3);
                foreach ($recs as $r) {
                    $suggestions[] = [
                        'id' => $r['item']['id'] ?? 0,
                        'name' => $r['item']['title'] ?? $r['item']['name'] ?? 'Property',
                        'score' => $r['score'],
                        'reason' => $r['reason']
                    ];
                }
            } catch (\Exception $e) {}
        }

        echo json_encode([
            'success' => true,
            'response' => $aiResponse['text'],
            'intent' => $aiResponse['intent'],
            'confidence' => $aiResponse['confidence'],
            'session_id' => $sessionId,
            'suggestions' => $suggestions,
            'response_time_ms' => $aiResponse['response_time_ms'] ?? 0
        ]);
        exit;
    }

    // Backward-compat intent detection (used by whatsappWebhook)
    private function processMessage($message, $sessionId, $platform)
    {
        $aiResponse = $this->ai()->processChat($sessionId, null, $message, $platform);
        return $aiResponse['text'];
    }

    private function saveConversation($sessionId, $message, $response, $intent, $platform)
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO ai_conversations (session_id, message, response, intent, platform, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$sessionId, $message, $response, $intent, $platform]);
        } catch (\Exception $e) {
            // table may not exist; ignore
        }
    }

    // WhatsApp Webhook - AI auto-reply (LLM + live inventory) via self-hosted WA service
    public function whatsappWebhook()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (isset($data['messages'][0])) {
            $message = $data['messages'][0]['text']['body'] ?? '';
            $from = $data['messages'][0]['from'] ?? '';
            $sessionId = 'wa_' . $from;

            // 1. AI reply: local LLM (Ollama) → cloud Gemini → rule fallback,
            //    enriched with live inventory context so customers get real plots.
            $reply = '';
            try {
                $pipeline = new AIVoicePipeline();
                $reply = $pipeline->generateChatReply($message) ?: '';
            } catch (\Throwable $e) {
                $reply = '';
            }
            if (empty($reply)) {
                $aiResponse = $this->ai()->processChat($sessionId, null, $message, 'whatsapp');
                $reply = $aiResponse['text'] ?? '';
            }

            $this->saveConversation($sessionId, $message, $reply, 'whatsapp_ai', 'whatsapp');

            // 2. Send the AI reply back over WhatsApp (if the local WA service is up)
            try {
                $wa = new WhatsAppWebService();
                $status = $wa->isConnected();
                if (!empty($status['connected']) || !isset($status['error'])) {
                    $wa->sendMessage($from, $reply);
                }
            } catch (\Throwable $e) {
                error_log("[WhatsAppWebhook] reply send failed: " . $e->getMessage());
            }

            // 3. Capture as a CRM lead (deduped) for follow-up
            $this->createLeadFromWhatsApp($from, $message, 'whatsapp_ai');
        }

        echo 'OK';
        exit;
    }

    private function createLeadFromWhatsApp($phone, $message, $intent = 'general')
    {
        try {
            $phone = '91' . preg_replace('/[^0-9]/', '', $phone);

            $stmt = $this->db->prepare("SELECT id FROM leads WHERE phone LIKE ? ORDER BY id DESC LIMIT 1");
            $stmt->execute(['%' . substr($phone, -10)]);
            $existing = $stmt->fetch();

            if (!$existing) {
                if (!$this->tenantEnforce('create_lead')) {
                    return;
                }
                $stmt = $this->db->prepare("INSERT INTO leads (name, phone, message, source, status, created_at) VALUES (?, ?, ?, 'whatsapp', 'new', NOW())");
                $stmt->execute(['WhatsApp User', $phone, $message]);

                $leadId = (int)$this->db->lastInsertId();
                if ($leadId) {
                    try { $this->ai()->scoreLead($leadId); } catch (\Exception $e) {}
                }
            }
        } catch (\Exception $e) {
            error_log("WhatsApp lead creation error: " . $e->getMessage());
        }
    }

    // AI Lead Scoring API
    public function scoreLead()
    {
        header('Content-Type: application/json');
        $leadId = (int)($_POST['lead_id'] ?? $_GET['lead_id'] ?? 0);
        if (!$leadId) { echo json_encode(['error' => 'lead_id required']); exit; }

        $result = $this->ai()->scoreLead($leadId);
        echo json_encode(['success' => true, 'result' => $result]);
        exit;
    }

    // AI Price Prediction API
    public function predictPrice()
    {
        header('Content-Type: application/json');
        $type = $_POST['property_type'] ?? $_GET['property_type'] ?? 'plot';
        $districtId = (int)($_POST['district_id'] ?? $_GET['district_id'] ?? 0) ?: null;
        $area = (int)($_POST['area'] ?? $_GET['area'] ?? 0) ?: null;
        $bedrooms = (int)($_POST['bedrooms'] ?? 0);
        $bathrooms = (int)($_POST['bathrooms'] ?? 0);

        $result = $this->ai()->predictPrice($type, $districtId, $area, $bedrooms, $bathrooms);
        echo json_encode(['success' => true, 'prediction' => $result]);
        exit;
    }

    // AI Recommendations API
    public function recommend()
    {
        header('Content-Type: application/json');
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $limit = (int)($_GET['limit'] ?? 10);

        if (!$userId) { echo json_encode(['error' => 'login required']); exit; }

        $result = $this->ai()->getRecommendations($userId, $limit);
        echo json_encode(['success' => true, 'recommendations' => $result]);
        exit;
    }

    // AI Stats Dashboard
    public function stats()
    {
        header('Content-Type: application/json');
        $stats = $this->ai()->getStats();
        echo json_encode(['success' => true, 'stats' => $stats]);
        exit;
    }

    // AI Retrain
    public function retrain()
    {
        header('Content-Type: application/json');
        $results = $this->ai()->retrain();
        echo json_encode(['success' => true, 'results' => $results]);
        exit;
    }
}
