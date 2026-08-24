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
            // Featured first, then newest. Real columns only (no property_type/images on this table).
            $tid = (int)($_SESSION['tenant_id'] ?? 1);
            $tenantSql = $tid > 1 ? " AND tenant_id = {$tid}" : "";
            $stmt = $this->db->query(
                "SELECT id, title, price, city, location, type, bedrooms, area_sqft
                 FROM properties
                 WHERE status = 'active' AND deleted_at IS NULL{$tenantSql}
                 ORDER BY featured DESC, created_at DESC LIMIT 8"
            );
            $properties = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $properties]);
        } catch (\Throwable $e) {
            error_log("AIAssistantController::recommendations: " . $e->getMessage());
            echo json_encode(['success' => true, 'data' => []]);
        }
    }

    public function analyze($id = null)
    {
        header('Content-Type: application/json');
        $propertyId = (int)($id ?? $_GET['id'] ?? 0);
        if (!$propertyId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Property ID required']);
            return;
        }
        try {
            $stmt = $this->db->query(
                "SELECT id, title, price, city, location, type, bedrooms, area_sqft, created_at
                 FROM properties WHERE id = ? AND deleted_at IS NULL",
                [$propertyId]
            );
            $p = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$p) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Property not found']);
                return;
            }

            // Comparable market stats: same city + type, active listings
            $comp = $this->db->query(
                "SELECT COUNT(*) c, AVG(price) avg_price, MIN(price) min_price, MAX(price) max_price,
                        AVG(CASE WHEN area_sqft > 0 THEN price / area_sqft END) avg_psf,
                        AVG(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY) THEN price END) recent_avg,
                        AVG(CASE WHEN created_at < DATE_SUB(NOW(), INTERVAL 90 DAY) THEN price END) older_avg
                 FROM properties
                 WHERE status = 'active' AND deleted_at IS NULL AND city = ? AND type = ? AND id != ?",
                [$p['city'], $p['type'], $propertyId]
            )->fetch(\PDO::FETCH_ASSOC) ?: [];

            $compCount = (int)($comp['c'] ?? 0);
            $price = (float)$p['price'];
            $cityAvg = (float)($comp['avg_price'] ?? 0);
            $psf = (float)($comp['avg_psf'] ?? 0);

            // Trend from 90-day split of comparable prices
            $recentAvg = (float)($comp['recent_avg'] ?? 0);
            $olderAvg = (float)($comp['older_avg'] ?? 0);
            if ($recentAvg > 0 && $olderAvg > 0) {
                $delta = ($recentAvg - $olderAvg) / $olderAvg;
                $trend = $delta > 0.03 ? 'Appreciating' : ($delta < -0.03 ? 'Softening' : 'Stable');
            } else {
                $trend = 'Stable';
            }

            // Score: value vs city average (cheaper = higher), bounded 1-10
            $score = 5.0;
            if ($cityAvg > 0) {
                $ratio = $price / $cityAvg; // <1 means below-market
                $score = min(10.0, max(1.0, round(5.0 + (1.2 - $ratio) * 3.5, 1)));
            }
            // Thin comparable market raises risk
            $risk = $compCount >= 5 ? 'low' : ($compCount >= 2 ? 'medium' : 'high');

            // AI narrative grounded in the computed numbers
            $facts = "Property: {$p['title']}, {$p['type']} in {$p['city']} ({$p['location']}), "
                . "listed at INR " . number_format($price) . ($psf > 0 ? ", area " . (float)$p['area_sqft'] . " sqft" : "")
                . ". Market data from {$compCount} comparable active listings in same city+type: "
                . "average price INR " . number_format($cityAvg)
                . ", price range INR " . number_format((float)($comp['min_price'] ?? 0)) . " to INR " . number_format((float)($comp['max_price'] ?? 0))
                . ($psf > 0 ? ", average INR " . number_format($psf, 0) . " per sqft" : "")
                . ". Computed trend: {$trend}. Computed investment score: {$score}/10. Computed risk: {$risk}.";
            $aiText = null;
            try {
                $result = \App\Services\AI\FreeAIEngines::getInstance()->generate(
                    "You are a real estate investment analyst for APS Dream Home. In 3-4 short sentences, "
                    . "assess this property for a buyer using ONLY the provided figures — do not invent data:\n{$facts}",
                    ['max_tokens' => 300, 'temperature' => 0.5],
                    'chat'
                );
                $aiText = trim($result['text'] ?? '') ?: null;
            } catch (\Throwable $ae) {
                error_log("AIAssistantController::analyze AI: " . $ae->getMessage());
            }
            if ($aiText === null) {
                $aiText = $cityAvg > 0
                    ? "Listed " . ($price < $cityAvg ? "below" : "above") . " the {$p['city']} average of ₹" . number_format($cityAvg)
                      . " across {$compCount} comparable listings. Trend is {$trend}; risk level {$risk}."
                    : "Limited comparable data in {$p['city']}; treat valuation estimates as indicative.";
            }

            echo json_encode([
                'success' => true,
                'data' => [
                    'property_id' => $propertyId,
                    'market_trend' => strtolower($trend),
                    'price_prediction' => $trend,
                    'investment_score' => $score,
                    'risk_level' => $risk,
                    'comparables_count' => $compCount,
                    'city_avg_price' => round($cityAvg),
                    'city_avg_per_sqft' => round($psf),
                    'analysis' => $aiText,
                ]
            ]);
        } catch (\Throwable $e) {
            error_log("AIAssistantController::analyze: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Analysis failed']);
        }
    }
}
