<?php

namespace App\Http\Controllers;

class AIAssistantController
{
    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    public function chat()
    {
        header("Content-Type: application/json");

        // Handle both JSON and form-urlencoded
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents("php://input"), true);
        } else {
            $input = $_POST;
        }

        $message = strtolower(trim($input['message'] ?? ''));
        $sessionId = $input['session_id'] ?? session_id();

        // Smart local responses based on intent
        $response = $this->getSmartResponse($message);

        // Log conversation if tables exist
        try {
            $this->db->prepare("INSERT INTO ai_conversations (session_id, message, response, intent, created_at) VALUES (?, ?, ?, ?, NOW())")
                ->execute([$sessionId, $message, $response, $this->detectIntent($message)]);
        } catch (\Exception $e) {
            // Table might not exist
        }

        echo json_encode([
            "success" => true,
            "response" => $response,
            "intent" => $this->detectIntent($message)
        ]);
        exit;
    }

    private function getSmartResponse($message)
    {
        $patterns = [
            'greeting' => ['hello', 'hi', 'namaste', 'hey', 'good morning', 'good afternoon', 'good evening', 'help', 'start'],
            'price' => ['price', 'kitna', 'rate', 'cost', 'rupees', 'how much', 'rate kitna', 'kimat'],
            'location' => ['where', 'location', 'address', 'kaha', 'kahan', 'place', 'area', 'gaya', 'city'],
            'projects' => ['project', 'suryoday', 'raghunath', 'braj radha', 'buddh bihar', 'ganga nagri', 'society'],
            'plots' => ['plot', 'land', 'naksha', 'jameen', 'khet', 'residential', 'commercial'],
            'loan' => ['loan', 'finance', 'emi', 'home loan', 'bank', 'mortgage', 'paise', 'udhar', 'finance'],
            'contact' => ['contact', 'phone', 'number', 'call', 'mobile', 'whatsapp', 'reach', 'connect'],
            'services' => ['service', 'legal', 'registry', 'mutation', 'interior', 'design', 'documentation'],
            'about' => ['about', 'company', 'aps', 'dream home', 'who are you', 'kaun ho', 'introduction'],
            'buy' => ['buy', 'purchase', 'kharidna', 'lena', 'book', 'kharedna'],
            'sell' => ['sell', 'sale', 'bechna', 'dena', 'bijna'],
            'rent' => ['rent', 'kiraya', 'lease', 'rental', 'paidal'],
        ];

        // Detect intent
        $intent = null;
        foreach ($patterns as $intentName => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    $intent = $intentName;
                    break 2;
                }
            }
        }

        // Return contextual response
        switch ($intent) {
            case 'greeting':
                return "🙏 Namaste! Welcome to APS Dream Home!\n\nI'm your personal property assistant. I can help you with:\n\n🏠 Find properties\n📍 Location details\n💰 Price information\n🏦 Home loan assistance\n📞 Contact details\n\nWhat are you looking for today?";

            case 'price':
                return "💰 **Property Prices:**\n\n• Plots starting from ₹4.5 Lakhs\n• Houses from ₹25 Lakhs\n• Commercial shops from ₹15 Lakhs\n• Apartments from ₹30 Lakhs\n\nPrices vary by location and size. Which property type interests you?";

            case 'location':
                return "📍 **Our Projects are in:**\n\n• Gorakhpur - Suryoday Colony, Raghunath Nagri, Brajradha Nagri\n• Lucknow - Gomti Enclave\n• Kushinagar - Buddh Bihar Colony\n• Varanasi - Ganga Nagri\n\nWhich location would you prefer?";

            case 'projects':
                return "🏗️ **Current Projects:**\n\n**Gorakhpur:**\n• Suryoday Colony - Premium Plots\n• Raghunath Nagri - Commercial\n\n**Lucknow:**\n• Gomti Enclave - Residential\n\n**Kushinagar:**\n• Buddh Bihar Colony - Affordable plots\n\n**Varanasi:**\n• Ganga Nagri - Premium location\n\nWould you like details on any specific project?";

            case 'plots':
                return "📐 **Available Plots:**\n\n• Residential plots: 451-5000 sq ft\n• Commercial plots: 2000-20000 sq ft\n• Farm houses: 1-5 acres\n• Starting from ₹5.5 Lakhs\n\nWe have plots in Gorakhpur, Lucknow, Kushinagar & Varanasi.\n\nInterested in any specific size?";

            case 'loan':
                return "🏦 **Home Loan Assistance:**\n\nWe partner with leading banks:\n• SBI, HDFC, ICICI, Axis\n• Interest rates from 8.5% onwards\n• Up to 90% property value\n• Quick approval process\n\nOur team will help you with all documentation!\n\nWould you like to speak with our loan expert?";

            case 'contact':
                return "📞 **Contact Us:**\n\n🕐 Mon-Sat: 9AM - 7PM\n\n📱 **Phone/WhatsApp:**\n+91 92771 21112\n+91 70074 44842\n\n📧 **Email:**\ninfo@apsdreamhome.com\n\n🏢 **Office:**\n1st Floor, Singhariya Chauraha\nKunraghat, Gorakhpur, UP - 273008\n\nCall now for free consultation!";

            case 'services':
                return "🛠️ **Our Services:**\n\n• Property Sales & Purchase\n• Legal Documentation\n• Registry & Mutation\n• Interior Design\n• Home Loan Assistance\n• Property Management\n• Investment Consulting\n\nWhich service do you need?";

            case 'about':
                return "🏢 **APS Dream Home**\n\nYour trusted real estate partner in Uttar Pradesh since 2010.\n\n✅ 5000+ Happy Customers\n✅ 50+ Projects Completed\n✅ 10+ Cities Covered\n✅ RERA Registered\n✅ Legal & Transparent\n\nWe deal in Residential, Commercial & Agricultural properties.\n\nHow can we help you today?";

            case 'buy':
                return "🏠 **Buy Property:**\n\nGreat choice! We have:\n• Ready-to-move houses\n• Investment plots\n• Commercial shops\n• Apartments & flats\n\nWhat's your budget and preferred location?\n\nYou can also browse at:\n🌐 localhost/apsdreamhome/properties";

            case 'sell':
                return "💰 **Sell Your Property:**\n\nList your property with us - **100% FREE!**\n\n✅ Zero listing charges\n✅ No commission\n✅ Direct buyer contact\n✅ Quick verification\n
Visit:\n🌐 localhost/apsdreamhome/list-property\n\nOr call us at +91 92771 21112";

            case 'rent':
                return "🔑 **Rental Properties:**\n\nWe have rental options:\n• Shops & offices\n• Residential flats\n• Commercial spaces\n• Farm houses\n
Visit our properties page or call us to discuss your rental requirements.\n\n📞 +91 92771 21112";

            default:
                return "🤔 I didn't understand that. Let me help you with:\n\n🏠 Find properties\n📍 Locations\n💰 Prices\n🏦 Home loans\n📞 Contact details\n\nOr type 'help' to see all options!\n\nYou can also call us directly:\n📞 +91 92771 21112";
        }
    }

    private function detectIntent($message)
    {
        $intents = [
            'greeting' => ['hello', 'hi', 'namaste', 'hey', 'good morning'],
            'price' => ['price', 'kitna', 'rate', 'cost', 'how much'],
            'location' => ['where', 'location', 'address', 'kaha'],
            'contact' => ['contact', 'phone', 'number', 'call'],
            'buy' => ['buy', 'purchase', 'kharidna'],
            'sell' => ['sell', 'sale', 'bechna'],
        ];

        foreach ($intents as $intent => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    return $intent;
                }
            }
        }

        return 'unknown';
    }

    public function parseLead()
    {
        header("Content-Type: application/json");

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents("php://input"), true);
        } else {
            $input = $_POST;
        }

        $message = $input['message'] ?? '';

        // Simple regex parsing for name, phone, email
        $name = '';
        $phone = '';
        $email = '';
        $propertyType = '';

        // Extract phone (10 digit)
        if (preg_match('/\b\d{10}\b/', $message, $matches)) {
            $phone = $matches[0];
        }

        // Extract email
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $message, $matches)) {
            $email = $matches[0];
        }

        // Property type detection
        if (preg_match('/(plot|flat|house|shop|farm)/i', $message, $matches)) {
            $propertyType = strtolower($matches[1]);
        }

        echo json_encode([
            "success" => true,
            "parsed_lead" => [
                "name" => $name,
                "phone" => $phone,
                "email" => $email,
                "property_type" => $propertyType,
                "raw_message" => $message
            ]
        ]);
        exit;
    }

    public function recommendations()
    {
        header("Content-Type: application/json");

        try {
            // Get featured properties from database
            $properties = $this->db->fetchAll(
                "SELECT name, property_type, price, location FROM user_properties WHERE status = 'approved' ORDER BY created_at DESC LIMIT 5"
            );

            if (empty($properties)) {
                $properties = [
                    ["name" => "Suryoday Colony - Premium Plots", "location" => "Gorakhpur", "price" => "4.5 Lakh onwards"],
                    ["name" => "Raghunath Nagri", "location" => "Gorakhpur", "price" => "4.5 Lakh onwards"],
                    ["name" => "Braj Nagri", "location" => "Gorakhpur", "price" => "15 Lakh onwards"]
                ];
            }

            echo json_encode([
                "success" => true,
                "recommendations" => $properties
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                "success" => true,
                "recommendations" => [
                    ["name" => "Suryoday Colony", "location" => "Gorakhpur", "price" => "₹9L onwards"],
                    ["name" => "Raghunath Nagri", "location" => "Gorakhpur", "price" => "₹4.5L onwards"],
                    ["name" => "Braj Radha Nagri", "location" => "Gorakhpur", "price" => "₹15L onwards"]
                ]
            ]);
        }
        exit;
    }

    public function analyze($id)
    {
        header("Content-Type: application/json");

        try {
            $property = $this->db->fetchOne(
                "SELECT * FROM user_properties WHERE id = ?",
                [$id]
            );

            if ($property) {
                $analysis = [
                    "property_id" => $id,
                    "name" => $property['name'] ?? 'Unknown',
                    "type" => $property['property_type'] ?? 'N/A',
                    "price" => $property['price'] ?? 'N/A',
                    "location" => $property['address'] ?? 'N/A',
                    "status" => $property['status'] ?? 'N/A',
                    "insights" => "This property is located in " . ($property['address'] ?? 'a prime location') . ". " .
                        "Market analysis suggests fair pricing for this area."
                ];
            } else {
                $analysis = [
                    "property_id" => $id,
                    "error" => "Property not found",
                    "insights" => "Unable to analyze - property data not available"
                ];
            }

            echo json_encode([
                "success" => true,
                "analysis" => $analysis
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                "success" => false,
                "error" => "Analysis failed: " . $e->getMessage()
            ]);
        }
        exit;
    }
}
