<?php

// TODO: Add proper error handling with try-catch blocks


namespace App\Http\Controllers\AI;

use App\Http\Controllers\BaseController;

class AIWebController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->data = [];
    }

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    /**
     * Display AI Chatbot page
     */
    public function chatbot()
    {
        $this->data['page_title'] = 'AI Property Assistant - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'AI Assistant', 'url' => BASE_URL . 'ai/chatbot']
        ];

        $this->render('pages/ai/chatbot');
    }

    /**
     * Display Property Description Generator page
     */
    public function descriptionGenerator()
    {
        $this->data['page_title'] = 'Property Description Generator - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'AI Tools', 'url' => '#'],
            ['title' => 'Description Generator', 'url' => BASE_URL . 'ai/description-generator']
        ];

        // Get property types for the dropdown
        $this->data['property_types'] = $this->getPropertyTypes();

        $this->render('pages/ai/description-generator');
    }

    /**
     * Display AI Property Suggestions page
     */
    public function suggestions()
    {
        $this->data['page_title'] = 'AI Property Suggestions - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'AI Tools', 'url' => '#'],
            ['title' => 'Property Suggestions', 'url' => BASE_URL . 'ai/suggestions']
        ];

        // Get property types for the dropdown
        $this->data['property_types'] = $this->getPropertyTypes();

        $this->render('pages/ai/suggestions');
    }

    /**
     * POST /ai/suggestions and POST /api/ai/suggestions
     * Personalized property suggestions grounded in real active listings.
     * Accepts form-encoded or JSON: property_type, budget, location.
     */
    public function generateSuggestions()
    {
        header('Content-Type: application/json');

        $input = $_POST;
        if (empty($input)) {
            $json = json_decode(file_get_contents('php://input') ?: '', true);
            if (is_array($json)) {
                $input = $json;
            }
        }

        $type = trim((string)($input['property_type'] ?? 'plot'));
        $budget = (float)preg_replace('/[^0-9.]/', '', (string)($input['budget'] ?? 0));
        $location = trim((string)($input['location'] ?? ''));
        // Users say "plot"; inventory stores land as type='land'
        $dbType = ($type === 'plot') ? 'land' : $type;

        // Ground suggestions in real inventory
        $matches = [];
        try {
            $sql = "SELECT title, price, city, location, type, area_sqft FROM properties
                     WHERE status = 'active' AND deleted_at IS NULL";
            $params = [];
            if ($location !== '') {
                $sql .= " AND (city LIKE ? OR location LIKE ?)";
                $params[] = "%{$location}%";
                $params[] = "%{$location}%";
            }
            if ($type !== '') {
                $sql .= " AND type = ?";
                $params[] = $dbType;
            }
            if ($budget > 0) {
                $sql .= " AND price <= ?";
                $params[] = $budget * 1.15;
            }
            $sql .= " ORDER BY featured DESC, created_at DESC LIMIT 5";
            $matches = $this->db->fetchAll($sql, $params);
        } catch (\Exception $e) {
            error_log("generateSuggestions inventory query failed: " . $e->getMessage());
        }

        $listingLines = '';
        foreach ($matches as $m) {
            $listingLines .= "- {$m['title']} ({$m['type']}), " . ($m['location'] ?: $m['city'])
                . ", ₹" . number_format((float)$m['price']) . ", " . round((float)$m['area_sqft']) . " sqft\n";
        }

        $prompt = "You are a helpful real estate advisor for APS Dream Home, a plot/property company in Uttar Pradesh, India."
            . " Buyer profile: budget Rs. " . number_format($budget) . ", preferred location: " . ($location ?: 'anywhere in UP') . ", property type: {$type}."
            . ($listingLines !== '' ? "\nCurrently available listings:\n{$listingLines}" : "\nNo exact matches currently listed.")
            . "\nGive 4 to 6 short, practical, numbered suggestions for this buyer (what to check, how to negotiate, what to consider)."
            . " Keep each under 25 words. Reply in simple English.";

        $text = '';
        try {
            $gateway = \App\Services\AI\AIGateway::getInstance();
            $result = $gateway->process($prompt, [], ['type' => 'ad_copy']);
            $text = trim((string)($result['response'] ?? ''));
        } catch (\Exception $e) {
            error_log("generateSuggestions AI failed: " . $e->getMessage());
        }

        if ($text === '') {
            // Template fallback so the tool never dead-ends
            $lines = [];
            if (!empty($matches)) {
                foreach ($matches as $m) {
                    $lines[] = "Consider \"{$m['title']}\" in " . ($m['location'] ?: $m['city']) . " at ₹" . number_format((float)$m['price']) . ".";
                }
            } else {
                $lines[] = "No exact {$type} matches under your budget right now — widen the location or raise the budget slightly.";
            }
            $lines[] = "Verify colony approval (RERA/lda) and title documents before booking.";
            $lines[] = "Visit the site in person and confirm plot dimensions match the layout plan.";
            if ($budget > 0) {
                $lines[] = "Keep ~8% of budget aside for stamp duty, registry and other charges.";
            }
            $text = implode("\n", $lines);
        }

        $items = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $text)), static function ($l) {
            return $l !== '';
        }));

        echo json_encode([
            'success'     => true,
            'suggestions' => $text,
            'items'       => $items,
            'matches'     => count($matches),
        ]);
        exit;
    }

    /**
     * Get property types (helper method)
     */
    private function getPropertyTypes()
    {
        try {
            $query = "SELECT * FROM property_types ORDER BY `type`";
            return $this->db->fetchAll($query);
        } catch (\Exception $e) {
            return [['id' => 0, 'type' => 'Residential'], ['id' => 1, 'type' => 'Commercial'], ['id' => 2, 'type' => 'Plot']];
        }
    }
}
