<?php

use App\Http\Controllers\BaseController;
use Security;
use Exception;

/**
 * AI Property Assistant Controller
 * Provides AI-powered property recommendations and chat interface
 */
class AIAssistantController extends BaseController
{
    public function index()
    {
        $this->render('pages/ai-assistant', [
            'page_title' => 'AI Property Assistant - APS Dream Home',
            'page_description' => 'Get AI-powered property recommendations and find your dream home with our intelligent assistant'
        ]);
    }
    
    /**
     * API endpoint for AI chat responses
     */
    public function chat()
    {
        $this->setCorsHeaders();
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) $data = $_POST;

        $userMessage = Security::sanitize($data['message']) ?? '';
        $propertyId = Security::sanitize($data['property_id'] ?? null);
        $context = Security::sanitize($data['context'] ?? 'general');
        
        if (empty($userMessage)) {
            echo json_encode(['error' => 'Message is required']);
            return;
        }
        
        // If propertyId is present, fetch property details for the AI context
        $propertyContext = "";
        if ($propertyId) {
            $prop = $this->db->fetchOne("SELECT * FROM properties WHERE id = ?", [$propertyId]);
            if ($prop) {
                $propertyContext = " Context: Supporting sale for property '{$prop['title']}' located in {$prop['city']} priced at {$prop['price']}. ";
            }
        }

        // Generate AI response based on message and property context
        $response = $this->generateAIResponse($userMessage, $propertyContext);
        
        // Auto-detect if user wants to visit or buy to generate lead
        $intent = $this->detectIntent($userMessage);
        
        echo json_encode([
            'success' => true,
            'response' => $response,
            'intent_detected' => $intent,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Simple intent detection for lead generation
     */
    private function detectIntent($message)
    {
        $m = strtolower($message);
        if (preg_match('/(visit|buy|purchase|site visit|call me|book|interested|kharidna)/i', $m)) {
            return 'lead_generation';
        }
        return 'informational';
    }

    /**
     * Set CORS headers for API requests
     */
    protected function setCorsHeaders()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit;
        }
    }

    /**
     * API endpoint to parse natural language text into a Lead object
     */
    public function parseLead()
    {
        $this->setCorsHeaders();
        header('Content-Type: application/json');

        $text = Security::sanitize($_POST['text']) ?? '';

        if (empty($text)) {
            echo json_encode(['success' => false, 'message' => 'Text is required']);
            return;
        }

        $leadData = $this->extractLeadDetails($text);

        echo json_encode([
            'success' => true,
            'data' => $leadData,
            'message' => 'Lead details extracted successfully'
        ]);
    }

    /**
     * Extracts lead details from natural language using pattern matching
     */
    private function extractLeadDetails($text)
    {
        $data = [
            'name' => '',
            'phone' => '',
            'location' => '',
            'budget' => '',
            'property_type' => 'residential',
            'raw_text' => $text
        ];

        // 1. Extract Phone (10 digit number)
        if (preg_match('/[0-9]{10}/', $text, $matches)) {
            $data['phone'] = $matches[0];
        }

        // 2. Extract Name (Heuristic: "मेरा नाम है X" or "Name is X" or just looking for capitalized words after introductory words)
        // Simplified Logic for Demo:
        $textLower = strtolower($text);
        $words = explode(' ', $text);
        
        // Simple search for "Rahul", "Amit", etc. following "name" or "hun"
        if (preg_match('/(?:name\s+is|नाम\s+है|हूं|hu|hun)\s+([A-Z][a-z]+)/i', $text, $m)) {
            $data['name'] = $m[1];
        }

        // 3. Extract Location
        $locations = ['gomti nagar', 'hazratganj', 'lucknow', 'noida', 'kanpur', 'indira nagar', 'kisan path', 'deva road'];
        foreach ($locations as $loc) {
            if (strpos($textLower, $loc) !== false) {
                $data['location'] = ucwords($loc);
                break;
            }
        }

        // 4. Extract Budget
        if (preg_match('/([0-9]+)\s*(?:lakh|lakhs|laakh|l|cr|crore|crores)/i', $text, $m)) {
            $data['budget'] = $m[0];
        }

        // 5. Property Type
        if (strpos($textLower, 'plot') !== false || strpos($textLower, 'zameen') !== false) {
            $data['property_type'] = 'plot';
        } elseif (strpos($textLower, 'flat') !== false || strpos($textLower, 'apartment') !== false) {
            $data['property_type'] = 'apartment';
        }

        return $data;
    }
    
    /**
     * Generate AI response based on user input
     */
    private function generateAIResponse($message, $propertyContext = "")
    {
        $message = strtolower($message);
        
        // Use property context if provided to act as a sales agent
        if ($propertyContext) {
            $prefix = "As your dedicated assistant for this property: ";
            // Simplified logic: prepend context and use it to guide response if needed
        }
        
        // Property type responses
        if (strpos($message, 'apartment') !== false || strpos($message, 'flat') !== false) {
            return "I found excellent apartments in Gomti Nagar, Hazratganj, and Gomti Nagar Extension. Prices range from ₹45 Lakhs to ₹1.2 Crore. Most offer 2-3 bedrooms with modern amenities. Would you like specific details?";
        }
        
        if (strpos($message, 'villa') !== false || strpos($message, 'house') !== false) {
            return "We have premium villas in Gomti Nagar and Mahanagar. These spacious properties range from ₹1 Crore to ₹3 Crore, featuring 4-5 bedrooms with private gardens. Perfect for families!";
        }
        
        if (strpos($message, 'commercial') !== false || strpos($message, 'shop') !== false) {
            return "Commercial spaces are available in Vibhuti Khand and Hazratganj. Prime locations with high foot traffic. Prices start from ₹85 Lakhs. Great for businesses and investments!";
        }
        
        // Budget-based responses
        if (strpos($message, 'budget') !== false || strpos($message, 'cheap') !== false || strpos($message, 'affordable') !== false) {
            return "Great choice for budget-conscious buyers! We have properties under ₹50 Lakhs in Alambagh, Gomti Nagar Extension, and Indira Nagar. These offer good ROI potential!";
        }
        
        if (strpos($message, 'luxury') !== false || strpos($message, 'premium') !== false) {
            return "For luxury properties, I recommend Gomti Nagar and Hazratganj areas. Premium apartments and villas with high-end finishes, modern amenities, and prime locations. Starting from ₹75 Lakhs.";
        }
        
        // Location-based responses
        if (strpos($message, 'gomti nagar') !== false) {
            return "Gomti Nagar is excellent! Well-developed area with great connectivity. Properties range from ₹45 Lakhs to ₹2 Crore. Good schools, hospitals, and shopping centers nearby.";
        }
        
        if (strpos($message, 'hazratganj') !== false) {
            return "Hazratganj is the heart of Lucknow! Premium location with commercial and residential properties. Higher prices but excellent investment potential. Starting from ₹1 Crore.";
        }
        
        // Investment responses
        if (strpos($message, 'investment') !== false || strpos($message, 'roi') !== false) {
            return "Current best investment areas: Gomti Nagar Extension (15-18% ROI), Vibhuti Khand (12-15% ROI), and Mahanagar (10-12% ROI). These areas show good appreciation potential!";
        }
        
        // Default response
        return "I'd be happy to help you find the perfect property! We have options across Lucknow including apartments, villas, and commercial spaces. Could you tell me more about your budget, preferred location, or property type?";
    }
    
    /**
     * Get property recommendations
     */
    public function recommendations()
    {
        header('Content-Type: application/json');
        
        try {
            $properties = $this->db->fetchAll(
                "SELECT * FROM properties WHERE status = 'active' ORDER BY featured DESC, created_at DESC LIMIT 6"
            );
            
            echo json_encode([
                'success' => true,
                'properties' => $properties,
                'count' => count($properties)
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to fetch recommendations'
            ]);
        }
    }
    
    /**
     * Property analysis endpoint
     */
    public function analyze($propertyId)
    {
        header('Content-Type: application/json');
        
        try {
            $property = $this->db->fetch(
                "SELECT * FROM properties WHERE id = ? AND status = 'active'",
                [$propertyId]
            );
            
            if (!$property) {
                echo json_encode(['success' => false, 'error' => 'Property not found']);
                return;
            }
            
            // Generate AI analysis
            $analysis = $this->generatePropertyAnalysis($property);
            
            echo json_encode([
                'success' => true,
                'property' => $property,
                'analysis' => $analysis
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Analysis failed'
            ]);
        }
    }
    
    /**
     * Generate AI-powered property analysis
     */
    private function generatePropertyAnalysis($property)
    {
        $analysis = [
            'investment_rating' => $this->calculateInvestmentRating($property),
            'price_comparison' => $this->getPriceComparison($property),
            'location_score' => $this->getLocationScore($property['location']),
            'recommendation' => $this->getRecommendation($property),
            'market_trends' => $this->getMarketTrends($property['type'])
        ];
        
        return $analysis;
    }
    
    private function calculateInvestmentRating($property)
    {
        // Simple investment rating calculation
        $rating = 7; // Base rating
        
        if ($property['featured']) $rating += 1;
        if ($property['price'] < 5000000) $rating += 1; // Good value
        if (strpos(strtolower($property['location']), 'gomti nagar') !== false) $rating += 1;
        
        return min(10, $rating);
    }
    
    private function getPriceComparison($property)
    {
        $avgPrice = $this->db->fetchColumn(
            "SELECT AVG(price) FROM properties WHERE type = ? AND status = 'active'",
            [$property['type']]
        );
        
        if ($avgPrice > 0) {
            $difference = (($property['price'] - $avgPrice) / $avgPrice) * 100;
            return [
                'average_price' => round($avgPrice),
                'difference_percent' => round($difference, 2),
                'value_rating' => $difference < 0 ? 'Good Value' : 'Premium'
            ];
        }
        
        return ['average_price' => $property['price'], 'difference_percent' => 0, 'value_rating' => 'Market Rate'];
    }
    
    private function getLocationScore($location)
    {
        $locationScores = [
            'gomti nagar' => 9,
            'hazratganj' => 9,
            'vibhuti khand' => 8,
            'mahanagar' => 7,
            'alambagh' => 6,
            'indira nagar' => 8
        ];
        
        $locationLower = strtolower($location);
        foreach ($locationScores as $area => $score) {
            if (strpos($locationLower, $area) !== false) {
                return $score;
            }
        }
        
        return 6; // Default score
    }
    
    private function getRecommendation($property)
    {
        if ($property['price'] < 3000000) {
            return "Excellent value for money! Great for first-time buyers or investors looking for good ROI.";
        } elseif ($property['price'] < 7000000) {
            return "Well-priced property in a good location. Suitable for both end-users and investors.";
        } else {
            return "Premium property with excellent features. Ideal for those seeking luxury and comfort.";
        }
    }
    
    private function getMarketTrends($propertyType)
    {
        $trends = [
            'apartment' => 'Apartments in Lucknow are seeing 8-10% annual appreciation. High demand in Gomti Nagar area.',
            'villa' => 'Villas are premium investments with 12-15% ROI. Limited supply driving prices up.',
            'commercial' => 'Commercial spaces showing 15-18% returns. High demand in business districts.',
            'land' => 'Land prices appreciating at 10-12% annually. Good long-term investment.'
        ];
        
        return $trends[$propertyType] ?? 'Property market is stable with good investment potential.';
    }
}
