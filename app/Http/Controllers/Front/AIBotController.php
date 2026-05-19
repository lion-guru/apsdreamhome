<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;

class AIBotController extends BaseController
{
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    // Chat API
    public function chat()
    {
        header('Content-Type: application/json');
        
        $message = trim($_POST['message'] ?? $_GET['message'] ?? '');
        $sessionId = $_POST['session_id'] ?? $_GET['session_id'] ?? session_id();
        $platform = $_POST['platform'] ?? 'website';
        
        if (empty($message)) {
            echo json_encode(['error' => 'Message is required']);
            exit;
        }
        
        // Process message and get response
        $response = $this->processMessage($message, $sessionId, $platform);
        
        // Save conversation
        $this->saveConversation($sessionId, $message, $response, $platform);
        
        echo json_encode([
            'success' => true,
            'response' => $response,
            'session_id' => $sessionId
        ]);
        exit;
    }

    private function processMessage($message, $sessionId, $platform)
    {
        $message = strtolower(trim($message));
        
        // Intent detection
        $intents = [
            'buy' => ['buy', 'purchase', 'chahiy', 'chahta', 'chahata', 'plot', 'ghar', 'makan'],
            'sell' => ['sell', 'bechna', 'bechana', 'list', 'post'],
            'rent' => ['rent', 'kiraya', 'lease'],
            'home_loan' => ['loan', 'lending', 'credit', 'bank', 'home loan', 'ghar loan'],
            'legal' => ['legal', 'registry', 'document', 'paper', 'naksha', 'map'],
            'interior' => ['interior', 'furnish', 'design', 'decoration'],
            'price' => ['price', 'cost', 'kitna', 'rate', 'mahangi'],
            'location' => ['location', 'place', 'address', 'kahan', 'kahaan'],
            'contact' => ['contact', 'call', 'phone', 'whatsapp', 'speak', 'bat']
        ];
        
        $detectedIntent = 'general';
        foreach ($intents as $intent => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    $detectedIntent = $intent;
                    break 2;
                }
            }
        }
        
        // Generate response based on intent
        $responses = [
            'buy' => "🎯 *Perfect! Aap buy karna chahte hain.*

Main projects:
🏠 *Suryoday Colony* - Gorakhpur (Premium)
🏠 *Raghunath Nagri* - Gorakhpur
🏠 *Braj Radha Nagri* - Gorakhpur
🏠 *Budh Bihar Colony* - Kushinagar

*Starting from ₹5.5 Lakh* se.

Konsi location prefer karein aap?",
            
            'sell' => "🏷️ *Aap apni property sell karna chahte hain!*

✅ *100% FREE listing*
✅ *No commission*
✅ *Buyers dhundhne mein madad*

Form fill karein - 1 minute:
👉 " . BASE_URL . "/list-property

Ya WhatsApp karein:
📱 +91 92771 21112",
            
            'rent' => "🏠 *Rent property ke liye...*

暂时 mein rentals limited hain. Aapko specific requirements batao:
- Location?
- Budget?
- Property type?

Main team se baat karvata hoon.",
            
            'home_loan' => "🏦 *Home Loan Assistance!*

✅ *SBI, HDFC, ICICI, Axis* ke saath ties
✅ *Low interest rates*
✅ *Easy documentation*

*Required Documents:*
- Aadhaar Card
- PAN Card
- Income Proof
- Property Documents

Call karo: 📱 +91 92771 21112",
            
            'legal' => "📋 *Legal Services!*

✅ Registry
✅ Mutation
✅ NOC
✅ Property Verification
✅ Sale Agreement

*Hamare legal experts aapki madad karenge.*

Quote lene ke liye call karein: 📱 +91 92771 21112",
            
            'interior' => "🎨 *Interior Design Services!*

✅ Complete home furnishing
✅ Modular kitchen
✅ Modular wardrobe
✅ Living room design

*Starting ₹5 Lakh* se.

Design preview chahein? Call: 📱 +91 92771 21112",
            
            'price' => "💰 *Price Information:*

*Residential Plots:*
- ₹5.5 Lakh (Kushinagar)
- ₹7.5 Lakh (Gorakhpur Premium)
- ₹8.5 Lakh (Lucknow)

*Prices location aur size par depend karte hain.*

Detailed quotes ke liye contact karein!",
            
            'location' => "📍 *Hamare Projects:*

*Uttar Pradesh:*
🗺️ Gorakhpur - 3 Projects
🗺️ Lucknow - 1 Project
🗺️ Kushinagar - 1 Project
🗺️ Varanasi - 1 Project

*Coming Soon:* Ayodhya, Prayagraj, Meerut

Konsi location pasand hai?",
            
            'contact' => "📞 *Contact Us:*

*Phone:* +91 92771 21112
*WhatsApp:* wa.me/919277121112
*Email:* info@apsdreamhome.com

*Office:* 1st Floor, Singhariya Chauraha, Kunraghat, Gorakhpur

*Timing:* Mon-Sat, 9 AM - 7 PM",
            
            'greeting' => "🙏 *Namaste! APS Dream Home mein swagat hai!*

Main aapki kaise madad kar sakta hoon?

1️⃣ Buy Property
2️⃣ Sell Property
3️⃣ Home Loan
4️⃣ Legal Help
5️⃣ Interior Design
6️⃣ Talk to Executive",
            
            'general' => "🤖 *Sorry, main samjha nahi. Thoda aur batao.*

Quick options:
1️⃣ Browse projects - Type \"buy\"
2️⃣ Sell property - Type \"sell\"
3️⃣ Home loan - Type \"loan\"
4️⃣ Talk to person - Type \"human\""
        ];
        
        // Check for greetings
        $greetings = ['hi', 'hello', 'namaste', 'namaskar', 'hey', 'hola'];
        foreach ($greetings as $greeting) {
            if (strpos($message, $greeting) !== false) {
                return $responses['greeting'];
            }
        }
        
        // Check for human/executive request
        if (strpos($message, 'human') !== false || strpos($message, 'executive') !== false || strpos($message, 'person') !== false) {
            return "👤 *Aapka request note kar liya hai!*

Hamare executive aapko contact karenge.

*Direct call:* 📞 +91 92771 21112
*WhatsApp:* wa.me/919277121112";
        }
        
        return $responses[$detectedIntent] ?? $responses['general'];
    }

    private function saveConversation($sessionId, $message, $response, $platform)
    {
        try {
            // Try to detect intent
            $intent = 'general';
            $keywords = [
                'buy' => ['buy', 'purchase', 'plot'],
                'sell' => ['sell', 'bechna'],
                'loan' => ['loan', 'home loan'],
                'legal' => ['legal', 'registry'],
                'contact' => ['call', 'contact']
            ];
            
            foreach ($keywords as $k => $words) {
                foreach ($words as $word) {
                    if (stripos($message, $word) !== false) {
                        $intent = $k;
                        break 2;
                    }
                }
            }
            
            // Save to database
            $stmt = $this->db->prepare("INSERT INTO ai_conversations (session_id, message, response, intent, platform, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$sessionId, $message, $response, $intent, $platform]);
        } catch (\Exception $e) {
            error_log("AI Bot save error: " . $e->getMessage());
        }
    }

    // WhatsApp Webhook
    public function whatsappWebhook()
    {
        // This will be called by WhatsApp Business API
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($data['messages'][0])) {
            $message = $data['messages'][0]['text']['body'] ?? '';
            $from = $data['messages'][0]['from'] ?? '';
            $sessionId = 'wa_' . $from;
            
            // Process message
            $response = $this->processMessage($message, $sessionId, 'whatsapp');
            
            // Save conversation
            $this->saveConversation($sessionId, $message, $response, 'whatsapp');
            
            // Create lead if new
            $this->createLeadFromWhatsApp($from, $message);
        }
        
        echo 'OK';
        exit;
    }

    private function createLeadFromWhatsApp($phone, $message)
    {
        try {
            $phone = '91' . preg_replace('/[^0-9]/', '', $phone);
            
            // Check if lead exists
            $stmt = $this->db->prepare("SELECT id FROM leads WHERE phone LIKE ? ORDER BY id DESC LIMIT 1");
            $stmt->execute(['%' . substr($phone, -10)]);
            $existing = $stmt->fetch();
            
            if (!$existing) {
                $stmt = $this->db->prepare("INSERT INTO leads (name, phone, message, source, source_detail, status, created_at) VALUES (?, ?, ?, 'whatsapp', 'whatsapp_bot', 'new', NOW())");
                $stmt->execute(['WhatsApp User', $phone, $message]);
            }
        } catch (\Exception $e) {
            error_log("WhatsApp lead creation error: " . $e->getMessage());
        }
    }
}
