<?php

namespace App\Services;

class PropertyChatbotService
{
    private $db;
    private $responses = [];
    private $quickReplies = [];

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
        $this->initResponses();
        $this->initQuickReplies();
    }

    private function initResponses()
    {
        $this->responses = [
            'greeting' => [
                'patterns' => ['hi', 'hello', 'hey', 'namaste', 'good morning', 'good evening'],
                'response' => "Namaste! 🙏 Welcome to APS Dream Home! I'm your property assistant. How can I help you today?\n\nYou can ask me about:\n• Properties & Projects\n• Prices & Payment Plans\n• Location & Amenities\n• Site Visits\n• Registration Process"
            ],
            'properties' => [
                'patterns' => ['property', 'properties', 'plot', 'plots', 'home', 'house', 'flat', 'apartment', 'villa'],
                'response' => "We offer premium properties in Gorakhpur, Lucknow & across UP!\n\n🏠 Our Featured Projects:\n1. Suyoday Colony - Premium Plots from ₹7.5 Lakhs\n2. Raghunat Nagri - Integrated Township from ₹8.5 Lakhs\n3. Braj Radha Nagri - Affordable Plots from ₹6.5 Lakhs\n\n📞 Call: +91 92771 21112\n🌐 Visit: {BASE_URL}/properties"
            ],
            'price' => [
                'patterns' => ['price', 'cost', 'budget', 'cheap', 'affordable', 'lakhs', 'rupees', 'expensive'],
                'response' => "Our plots start from ₹6.5 Lakhs!\n\n💰 Price Range:\n• Budget Plots: ₹6.5 - ₹8 Lakhs\n• Premium Plots: ₹8 - ₹12 Lakhs\n• Commercial: ₹15 Lakhs+\n\n📊 EMI starts at ₹8,000/month\n\nWould you like to see properties in your budget range?"
            ],
            'location' => [
                'patterns' => ['location', 'address', 'where', 'gorakhpur', 'lucknow', 'kushinagar', 'area'],
                'response' => "📍 Our Office:\n1st floor, Singhariya Chauraha, Kunraghat, Deoria Road, Gorakhpur, UP - 273008\n\n🏗️ Project Locations:\n• Suyoday Colony - Gorakhpur\n• Raghunat Nagri - Gorakhpur\n• Budh Bihar Colony - Kushinagar\n• Awadhpuri - Lucknow\n\n🕐 Mon-Sat: 9:00 AM - 7:00 PM\n📞 +91 92771 21112"
            ],
            'contact' => [
                'patterns' => ['contact', 'phone', 'call', 'email', 'reach', 'connect', 'whatsapp'],
                'response' => "📞 Contact Us:\n• Phone: +91 92771 21112\n• Alt: +91 70074 44842\n• Email: info@apsdreamhome.com\n• WhatsApp: wa.me/919277121112\n\n🏢 Office:\n1st floor, Singhariya Chauraha, Kunraghat, Gorakhpur"
            ],
            'visit' => [
                'patterns' => ['visit', 'site visit', 'schedule', 'book visit', 'see property'],
                'response' => "🔑 Schedule a Free Site Visit!\n\nWe offer complimentary visits to all our projects.\n\n📅 How to Book:\n1. Call: +91 92771 21112\n2. WhatsApp: Send 'VISIT' to wa.me/919277121112\n3. Fill form: {BASE_URL}/contact\n\n🕐 Available: Mon-Sat, 9 AM - 7 PM"
            ],
            'register' => [
                'patterns' => ['register', 'registration', 'signup', 'account', 'booking'],
                'response' => "📝 Easy Registration!\n\n🏠 For Customers:\n{BASE_URL}/register\n\n🤝 For Associates:\n{BASE_URL}/associate/register\n\n📋 Documents Needed:\n• Aadhaar Card\n• PAN Card\n• Photo\n• Address Proof"
            ],
            'loan' => [
                'patterns' => ['loan', 'finance', 'emi', 'bank', 'credit', 'home loan'],
                'response' => "💰 Home Loan Assistance Available!\n\nWe partner with leading banks:\n• SBI, HDFC, ICICI, Axis & more\n\n✅ Benefits:\n• Loan up to 85% of property value\n• Interest: 8.5% - 10%\n• Tenure: Up to 20 years\n• Quick approval\n\n📞 Ask about loan assistance during your visit!"
            ],
            'rera' => [
                'patterns' => ['rera', 'legal', 'verified', 'document', 'registration'],
                'response' => "✅ RERA Verified Company\n\nReg. No: U70109UP2022PTC163047\n\nAll our properties are:\n• RERA Approved\n• legally Verified\n• Clear Title\n• Free from Disputes\n\n📜 We handle complete documentation!"
            ],
            'amenities' => [
                'patterns' => ['amenities', 'facility', 'facilities', 'features', 'park', 'security', 'water'],
                'response' => "🏗️ Project Amenities:\n\n• 24/7 Security with CCTV\n• Wide Roads (30-40 ft)\n• Underground Electricity\n• 24/7 Water Supply\n• Green Parks\n• Gated Community\n• Rain Water Harvesting\n• Close to Schools/Hospitals"
            ],
            'payment' => [
                'patterns' => ['payment', 'pay', 'installment', 'EMI', 'down payment', 'plan'],
                'response' => "💳 Flexible Payment Plans!\n\n📋 Options:\n• Down Payment: 20%\n• Installments: Up to 24 months\n• Subvention Plan available\n\n💰 Easy EMI from ₹8,000/month\n\nContact us for personalized payment plan!"
            ],
            'thanks' => [
                'patterns' => ['thanks', 'thank you', 'thanking', 'helpful'],
                'response' => "You're welcome! 😊

Is there anything else I can help you with?\n\n🏠 Explore more:\n{BASE_URL}/properties"
            ],
            'bye' => [
                'patterns' => ['bye', 'goodbye', 'tata', 'see you', 'later'],
                'response' => "Thank you for chatting! 🙏\n\nVisit us:\n📞 +91 92771 21112\n📍 1st floor, Singhariya Chauraha, Kunraghat, Gorakhpur\n\nSee you soon!"
            ],
            'help' => [
                'patterns' => ['help', 'support', 'assist', 'can you', 'what can'],
                'response' => "I'm here to help! 🏠\n\nI can assist with:\n• Property Information\n• Price Details\n• Site Visit Booking\n• Registration Process\n• Loan Assistance\n• Document Queries\n\nJust type your question!"
            ]
        ];
    }

    private function initQuickReplies()
    {
        $this->quickReplies = [
            'View Properties',
            'Price Details',
            'Book Site Visit',
            'Contact Us'
        ];
    }

    public function processMessage($message)
    {
        $message = strtolower(trim($message));
        
        // Special handling for booking intent
        if ($this->isBookingIntent($message)) {
            return $this->handleBookingIntent($message);
        }
        
        $response = $this->findBestResponse($message);
        return $this->formatResponse($response);
    }

    private function isBookingIntent($message)
    {
        $bookingPatterns = ['book', 'visit', 'schedule', 'appointment', 'meeting', 'see property', 'tour'];
        foreach ($bookingPatterns as $pattern) {
            if (strpos($message, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }

    private function handleBookingIntent($message)
    {
        $baseUrl = rtrim(BASE_URL ?? 'http://localhost/apsdreamhome', '/');
        return [
            'reply' => "🗓️ <strong>Book Free Site Visit!</strong>\n\n" .
                       "I'd love to help you visit our properties.\n\n" .
                       "📅 <strong>Available Slots:</strong>\n" .
                       "• Mon-Sat: 9:00 AM - 7:00 PM\n\n" .
                       "🔗 <strong>Quick Booking:</strong>\n" .
                       "1. <a href='{$baseUrl}/contact' target='_blank'>Fill Contact Form</a>\n" .
                       "2. Call: <strong>+91 92771 21112</strong>\n" .
                       "3. WhatsApp: <a href='https://wa.me/919277121112?text=Hi, I want to book a site visit' target='_blank'>Send Message</a>\n\n" .
                       "🏠 <strong>What You'll Get:</strong>\n" .
                       "• Free site tour\n" .
                       "• Property details\n" .
                       "• Price breakup\n" .
                       "• Loan assistance",
            'quick_replies' => [
                'Call Now',
                'WhatsApp Now',
                'View Properties',
                'Price Details'
            ],
            'intent' => 'booking'
        ];
    }

    private function findBestResponse($message)
    {
        foreach ($this->responses as $category => $data) {
            foreach ($data['patterns'] as $pattern) {
                if (strpos($message, $pattern) !== false) {
                    return str_replace('{BASE_URL}', rtrim(BASE_URL ?? 'http://localhost/apsdreamhome', '/'), $data['response']);
                }
            }
        }

        return $this->getDefaultResponse();
    }

    private function getDefaultResponse()
    {
        $baseUrl = rtrim(BASE_URL ?? 'http://localhost/apsdreamhome', '/');
        return "🤔 <strong>I'm not sure I understand that.</strong>\n\n" .
               "I can help you with:\n" .
               "• 🏠 Properties & Projects\n" .
               "• 💰 Prices & Payment Plans\n" .
               "• 📅 Site Visit Booking\n" .
               "• 🏦 Home Loan Info\n" .
               "• 📜 RERA Documents\n" .
               "• 📞 Contact Details\n\n" .
               "💬 <strong>Try these:</strong>\n" .
               "• \"View Properties\"\n" .
               "• \"Plot Prices\"\n" .
               "• \"Book Site Visit\"\n" .
               "• \"Home Loan Help\"\n\n" .
               "📞 Or call: <strong>+91 92771 21112</strong>";
    }

    private function formatResponse($response)
    {
        return [
            'reply' => $response,
            'quick_replies' => $this->quickReplies,
            'intent' => 'general'
        ];
    }

    public function getQuickReplies()
    {
        return $this->quickReplies;
    }

    public function saveConversation($userId, $message, $response)
    {
        try {
            $sql = "INSERT INTO chatbot_conversations (user_id, user_message, bot_response, intent, created_at) 
                    VALUES (?, ?, ?, ?, NOW())";
            $this->db->execute($sql, [
                $userId,
                $message,
                $response['reply'],
                $response['intent']
            ]);
            return true;
        } catch (\Exception $e) {
            error_log("Chatbot save error: " . $e->getMessage());
            return false;
        }
    }
}
