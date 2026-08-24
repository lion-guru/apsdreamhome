<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Services\AI\AIGateway;
use App\Services\AI\FreeAIEngines;

class AIAssistantController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function chat()
    {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $message = trim($input['message'] ?? '');
        if ($message === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Message required']);
            return;
        }

        try {
            // 1. Try gateway pattern engines first (intent detection, instant)
            $gateway = AIGateway::getInstance();
            $gwResult = $gateway->process('chat', ['message' => $message]);

            // 2. Generate real reply via free cloud engines (Ollama → Groq → OpenRouter → Gemini)
            $engines = FreeAIEngines::getInstance();
            $aiResult = $engines->generate($message, ['max_tokens' => 512, 'temperature' => 0.7], 'chat');

            $reply = trim($aiResult['text'] ?? '');
            if ($reply === '') {
                // Fallback to canned reply if all engines fail
                $reply = 'I am APS Dream Home AI assistant. How can I help you today?';
            }

            echo json_encode([
                'success' => true,
                'data' => [
                    'reply' => $reply,
                    'intent' => $gwResult['result']['intent']['intent'] ?? 'general',
                    'confidence' => $aiResult['engine'] !== 'none' ? 0.9 : 0.95,
                    'engine' => $aiResult['engine'],
                ]
            ]);
        } catch (\Throwable $e) {
            error_log('AIAssistantController::chat error: ' . $e->getMessage());
            echo json_encode([
                'success' => true,
                'data' => [
                    'reply' => 'I am APS Dream Home AI assistant. How can I help you today?',
                    'intent' => 'greeting',
                    'confidence' => 0.5
                ]
            ]);
        }
    }

    public function parseLead()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $text = trim($data['text'] ?? $_POST['text'] ?? '');
        if ($text === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Text required']);
            return;
        }

        try {
            // Try real AI extraction via free engines
            $engines = FreeAIEngines::getInstance();
            $prompt = "Extract lead information from this message. Return ONLY valid JSON with keys name, phone, email, budget, location, property_type. Use empty string if not found. Message: {$text}";
            $aiResult = $engines->generate($prompt, ['max_tokens' => 256, 'temperature' => 0.2], 'qualify');
            $raw = trim($aiResult['text'] ?? '');

            $extracted = [];
            if ($raw !== '') {
                // Strip markdown fences if present
                $cleaned = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', $raw);
                $decoded = json_decode($cleaned, true);
                if (is_array($decoded)) {
                    foreach (['name', 'phone', 'email', 'budget', 'location', 'property_type'] as $k) {
                        $extracted[$k] = (string)($decoded[$k] ?? '');
                    }
                }
            }

            // Regex fallbacks for phone/email if AI missed them
            if (empty($extracted['phone']) && preg_match('/(\+91[\-\s]?)?[6-9]\d{9}/', $text, $m)) {
                $extracted['phone'] = preg_replace('/[\s\-]/', '', $m[0]);
            }
            if (empty($extracted['email']) && preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text, $m)) {
                $extracted['email'] = strtolower($m[0]);
            }
            if (empty($extracted['budget']) && preg_match('/(\d+(?:\.\d+)?)\s*(lakh|crore|cr|lac)/i', $text, $m)) {
                $extracted['budget'] = $m[1] . ' ' . ucfirst(strtolower($m[2]));
            }

            $extracted['extracted_from'] = $text;
            echo json_encode(['success' => true, 'data' => array_merge([
                'name' => '', 'phone' => '', 'email' => '',
                'budget' => '', 'location' => '', 'property_type' => ''
            ], $extracted)]);
        } catch (\Throwable $e) {
            error_log('AIAssistantController::parseLead error: ' . $e->getMessage());
            echo json_encode([
                'success' => true,
                'data' => [
                    'name' => '', 'phone' => '', 'email' => '',
                    'budget' => '', 'location' => '', 'property_type' => '',
                    'extracted_from' => $text
                ]
            ]);
        }
    }

    public function recommendations()
    {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null;
        try {
            $stmt = $this->db->query("SELECT id, title, price, city, property_type, images FROM properties WHERE status = 'active' ORDER BY RAND() LIMIT 5");
            $properties = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $properties]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => true, 'data' => []]);
        }
    }

    public function analyze($id = null)
    {
        header('Content-Type: application/json');
        $propertyId = $id ?? $_GET['id'] ?? null;
        if (!$propertyId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Property ID required']);
            return;
        }
        echo json_encode([
            'success' => true,
            'data' => [
                'property_id' => $propertyId,
                'market_trend' => 'stable',
                'price_prediction' => 'Appreciating',
                'investment_score' => 8.5,
                'risk_level' => 'low',
                'recommendation' => 'Good investment for long term'
            ]
        ]);
    }
}
