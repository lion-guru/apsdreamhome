<?php
namespace App\Services\Auc;

/**
 * AUC — Unified AI Conversation Service
 * Handles AI responses across all 3 channels (A=Chatbot, U=WhatsApp, C=Calling)
 * Single brain, multiple mouths.
 */
class AucBrainService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance()->getConnection();
    }

    /**
     * Process user message and generate AI response
     * Works for all channels: website chat, WhatsApp, voice call
     */
    public function processMessage(string $message, string $channel, array $context = []): array
    {
        $userId = $context['user_id'] ?? null;
        $sessionId = $context['session_id'] ?? uniqid('auc_');
        $lang = $context['language'] ?? 'hi';

        $intent = $this->detectIntent($message);
        $response = $this->generateResponse($message, $intent, $channel, $lang, $context);

        $this->logConversation($sessionId, $userId, $channel, $message, $response['text'], $intent, $lang);

        return [
            'text' => $response['text'],
            'intent' => $intent,
            'actions' => $response['actions'] ?? [],
            'session_id' => $sessionId,
            'channel' => $channel,
        ];
    }

    /**
     * Intent detection — determines what the customer wants
     */
    private function detectIntent(string $message): string
    {
        $msg = strtolower($message);

        $intents = [
            'property_inquiry'   => ['plot', 'property', 'price', 'rate', 'kplot', 'kitna', 'mehenga', 'sasta', 'bigha', 'gaj', 'sqft', 'area'],
            'booking'            => ['book', 'kharid', 'lenna', 'advance', 'token', 'booking', 'register'],
            'payment'            => ['pay', 'emi', 'installment', 'payment', 'jama', 'dena', 'paisa'],
            'location'           => ['location', 'kahan', 'address', 'map', 'nagarsaha', 'gorakhpur', 'colony', 'where'],
            'contact'            => ['call', 'phone', 'number', 'contact', 'mil', 'baat', 'talk'],
            'complaint'          => ['complaint', 'problem', 'issue', 'dikkat', 'pareshan', 'service'],
            'mlm'                => ['commission', 'income', 'mlm', 'network', 'team', 'affiliate', 'join'],
            'greeting'           => ['hello', 'hi', 'namaste', 'namaskar', 'hey', 'ha'],
            'thanks'             => ['thank', 'dhanyavaad', 'shukriya', 'thanks'],
            'status'             => ['status', 'update', 'kya hua', 'progress', 'kitna hua'],
        ];

        foreach ($intents as $intent => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($msg, $keyword)) {
                    return $intent;
                }
            }
        }

        return 'general';
    }

    /**
     * Generate contextual response based on intent + channel
     */
    private function generateResponse(string $message, string $intent, string $channel, string $lang, array $context): array
    {
        $properties = $this->getContextProperties();
        $company = [
            'name' => 'APS Dream Homes',
            'phone' => '919277121112',
            'email' => 'apsdreamhome@gmail.com',
            'address' => 'Virat Bhawan, Kunraghat, Gorakhpur 273008',
        ];

        $responses = [
            'greeting' => [
                'hi' => "Namaste! 🙏 Main APS Dream Homes ka AI assistant hoon.\n\nMain aapki madad kar sakta hoon:\n🏠 Property & Plot ki jaankari\n💰 EMI & Payment details\n📍 Colony locations\n🤝 MLM Commission info\n\nBataiye, kya jaanna chahte hain?",
                'en' => "Hello! 🙏 I'm APS Dream Homes AI assistant.\n\nI can help you with:\n🏠 Property & Plot information\n💰 EMI & Payment details\n📍 Colony locations\n🤝 MLM Commission info\n\nHow can I help you today?",
            ],
            'property_inquiry' => [
                'hi' => "🏠 **APS Dream Homes — Properties**\n\n" . $this->formatProperties($properties) . "\n\n📍 Locations:\n• Suryoday Nagar — Kunraghat, Gorakhpur\n• Raghunath Nagri — Ramgarh Taal\n• Braj Radha Nagar — Sahjanwa\n• Budh Bihar — Kushingar\n\n📞 Detail ke liye call karein: {$company['phone']}\n🌐 Website: apsdreamhome.com",
                'en' => "🏠 **APS Dream Homes — Properties**\n\n" . $this->formatProperties($properties) . "\n\nFor details, call: {$company['phone']}",
            ],
            'booking' => [
                'hi' => "📋 **Booking Process:**\n\n1️⃣ Pehle site visit karein (FREE)\n2️⃣ Plot choose karein\n3️⃣ Token amount jama karein (₹21,000 se shuru)\n4️⃣ Documents verify honge\n5️⃣ Agreement sign hoga\n6️⃣ Possession mil jayega!\n\n📞 Abhi booking ke liye call karein: {$company['phone']}\n📍 Office: {$company['address']}",
                'en' => "📋 **Booking Process:**\n\n1️⃣ FREE site visit\n2️⃣ Choose your plot\n3️⃣ Pay token (starts ₹21,000)\n4️⃣ Document verification\n5️⃣ Agreement signing\n6️⃣ Possession!\n\nCall now: {$company['phone']}",
            ],
            'payment' => [
                'hi' => "💰 **Payment Options:**\n\n• Full Payment — Extra discount milega\n• EMI Plan — ₹5,000/month se shuru\n• Down Payment + Installments\n• Bank Loan available (SBI, HDFC, ICICI)\n\n📞 Payment details: {$company['phone']}",
                'en' => "💰 **Payment Options:**\n\n• Full Payment — Extra discount\n• EMI Plan — Starting ₹5,000/month\n• Bank Loan available\n\nCall: {$company['phone']}",
            ],
            'location' => [
                'hi' => "📍 **APS Dream Homes Locations:**\n\n🏠 Suryoday Nagar — Kunraghat, Gorakhpur (Main Office)\n🏠 Raghunath Nagri — Ramgarh Taal\n🏠 Braj Radha Nagar — Sahjanwa\n🏠 Budh Bihar — Kushingar, Kushinagar\n🏠 Awadhpuri — Lucknow\n🏠 Ganga Nagar — Prayagraj\n\n📞 Visit ke liye call karein: {$company['phone']}",
                'en' => "📍 **Locations:**\n\n• Suryoday Nagar — Kunraghat\n• Raghunath Nagri — Ramgarh Taal\n• Braj Radha Nagar — Sahjanwa\n• Budh Bihar — Kushingar\n\nCall for visit: {$company['phone']}",
            ],
            'contact' => [
                'hi' => "📞 **Contact APS Dream Homes:**\n\n📱 Phone: {$company['phone']}\n📧 Email: {$company['email']}\n📍 Office: {$company['address']}\n🌐 Website: apsdreamhome.com\n\n⏰ Office Hours: 9 AM — 7 PM (Mon-Sat)",
                'en' => "📞 **Contact Us:**\n\n📱 Phone: {$company['phone']}\n📧 Email: {$company['email']}\n📍 {$company['address']}",
            ],
            'mlm' => [
                'hi' => "🤝 **APS MLM Opportunity:**\n\n💰 Earn up to 20% commission on plot sales!\n📊 7 ranks: Associate → Site Manager\n🎁 Generation Bonus, Matching Bonus, Royalty Pool\n\n📞 Join karne ke liye: {$company['phone']}",
                'en' => "🤝 **MLM Opportunity:**\n\n💰 Earn up to 20% commission!\n7 ranks with bonuses\n\nCall to join: {$company['phone']}",
            ],
            'complaint' => [
                'hi' => "🙏 Aapki samasya hamari zimmedaari hai.\n\n📞 Complaint register: {$company['phone']}\n📧 Email: {$company['email']}\n\nHum 24 hours mein response denge.",
                'en' => "🙏 We take complaints seriously.\n\n📞 Register: {$company['phone']}\n📧 {$company['email']}\n\nResponse within 24 hours.",
            ],
            'thanks' => [
                'hi' => "🙏 Aapka swagat hai! Aur kuch puchna ho toh bataiye.\n\n📞 {$company['phone']}",
                'en' => "🙏 You're welcome! Feel free to ask anything.\n\n📞 {$company['phone']}",
            ],
        ];

        $lang = in_array($lang, ['hi', 'en']) ? $lang : 'hi';
        $text = $responses[$intent][$lang] ?? $responses['greeting'][$lang] ?? "Main aapki madad karna chahta hoon. Bataiye kya chahiye?";

        return [
            'text' => $text,
            'actions' => $this->getActionsForIntent($intent),
        ];
    }

    private function formatProperties(array $properties): string
    {
        $text = '';
        foreach (array_slice($properties, 0, 5) as $p) {
            $text .= "• {$p['name']} — ₹" . number_format($p['price']) . " ({$p['area']} sqft)\n";
        }
        return $text;
    }

    private function getContextProperties(): array
    {
        static $cache = null;
        if ($cache !== null) return $cache;

        try {
            $stmt = $this->db->prepare("
                SELECT p.title as name, p.price, p.area_sqft as area, p.status
                FROM properties p
                WHERE p.status = 'active'
                ORDER BY p.created_at DESC
                LIMIT 5
            ");
            $stmt->execute();
            $cache = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [
                ['name' => 'Suryoday Plot', 'price' => 500000, 'area' => 1200],
                ['name' => 'Raghunath Plot', 'price' => 450000, 'area' => 1000],
            ];
        } catch (\Exception $e) {
            $cache = [['name' => 'Suryoday Plot', 'price' => 500000, 'area' => 1200]];
        }
        return $cache;
    }

    private function getActionsForIntent(string $intent): array
    {
        $actions = [
            'booking'         => ['type' => 'show_booking_form', 'priority' => 'high'],
            'payment'         => ['type' => 'show_payment_options', 'priority' => 'medium'],
            'property_inquiry' => ['type' => 'show_properties', 'priority' => 'medium'],
            'contact'         => ['type' => 'show_contact', 'priority' => 'low'],
            'complaint'       => ['type' => 'create_ticket', 'priority' => 'high'],
        ];
        return isset($actions[$intent]) ? [$actions[$intent]] : [];
    }

    private function logConversation(string $sessionId, ?int $userId, string $channel, string $message, string $response, string $intent, string $lang): void
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO auc_conversations (session_id, user_id, channel, user_message, bot_response, intent, language, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$sessionId, $userId, $channel, $message, $response, $intent, $lang]);
        } catch (\Exception $e) {
            error_log("AUC log error: " . $e->getMessage());
        }
    }
}
