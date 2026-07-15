<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;

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
        $message = $_POST['message'] ?? json_decode(file_get_contents('php://input'), true)['message'] ?? '';
        echo json_encode([
            'success' => true,
            'data' => [
                'reply' => 'I am APS Dream Home AI assistant. How can I help you today?',
                'intent' => 'greeting',
                'confidence' => 0.95
            ]
        ]);
    }

    public function parseLead()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $text = $data['text'] ?? $_POST['text'] ?? '';
        echo json_encode([
            'success' => true,
            'data' => [
                'name' => '',
                'phone' => '',
                'email' => '',
                'budget' => '',
                'location' => '',
                'property_type' => '',
                'extracted_from' => $text
            ]
        ]);
    }

    public function recommendations()
    {
        header('Content-Type: application/json');
        $userId = $_GET['user_id'] ?? $GLOBALS['api_user_id'] ?? null;
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
