<?php
/**
 * HindiConversationalBot — Hindi-first conversational AI for real estate
 * 
 * Handles all customer conversations in Hindi/Hinglish:
 * - Property inquiries
 * - Pricing questions
 * - EMI calculations
 * - Site visit scheduling
 * - Complaint handling
 * - General queries
 * 
 * Uses: SelfLearningAI → IntentDetector → Gemini (fallback)
 * Personality: Friendly, professional, like a real estate advisor
 */

namespace App\Services\AI\Agents;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;
use App\Services\AI\AIGateway;
use App\Services\AI\SelfLearningAI;
use App\Services\AI\IntentDetector;

class HindiConversationalBot
{
    use ServiceTenantTrait;

    private $db;
    private $gateway;

    // Personality system prompts
    private $personality = [
        'name' => 'APS Dream Home Assistant',
        'languages' => ['hi', 'en', 'hinglish'],
        'expertise' => ['Raghunath Nagri', 'Gorakhpur', 'property', 'plots', 'EMI', 'registry'],
        'tone' => 'Professional, warm, helpful — like a trusted real estate advisor',
        'rules' => [
            'Always greet in the language the user speaks',
            'If Hindi, respond in Hindi. If English, respond in English',
            'Never give false information about prices',
            'Always suggest site visit for serious inquiries',
            'For complaints, show empathy and escalate immediately',
        ],
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->gateway = AIGateway::getInstance();
    }

    /**
     * Process a chat message and return intelligent response
     */
    public function chat(string $message, array $context = []): array
    {
        $sessionId = $context['session_id'] ?? 'chat_' . md5($message . time());
        $userId = $context['user_id'] ?? null;

        // 1. Detect intent
        $intentDetector = new IntentDetector($this->db);
        $intent = $intentDetector->detect($message);

        // 2. Get SelfLearningAI response
        $selfLearning = new SelfLearningAI($sessionId, $userId, 'customer');
        $slResult = $selfLearning->processMessage($message);

        // 3. Build contextual response
        $response = $this->buildResponse($message, $intent, $slResult, $context);

        // 4. Generate follow-up suggestions
        $suggestions = $this->getFollowUpSuggestions($intent['intent'] ?? 'unknown');

        // 5. Log conversation
        $this->logConversation($sessionId, $userId, $message, $response['text'], $intent);

        return [
            'text' => $response['text'],
            'language' => $intent['language'] ?? 'hi',
            'intent' => $intent['intent'] ?? 'unknown',
            'confidence' => $intent['confidence'] ?? 0,
            'suggestions' => $suggestions,
            'action_taken' => $response['action'] ?? null,
            'engine' => $response['engine'] ?? 'rule',
        ];
    }

    /**
     * Handle specific real estate queries
     */
    public function handlePropertyQuery(string $query, array $context = []): array
    {
        $lowerQuery = strtolower($query);

        // Price inquiry
        if (preg_match('/(price|कीमत|दाम|rate|cost|kitna)/i', $lowerQuery)) {
            return $this->handlePriceInquiry($query, $context);
        }

        // EMI inquiry
        if (preg_match('/(emi|loan|लोन|किस्त|फाइनेंस)/i', $lowerQuery)) {
            return $this->handleEMIInquiry($query, $context);
        }

        // Site visit
        if (preg_match('/(visit|विजिट|देखना|आना|visit)/i', $lowerQuery)) {
            return $this->handleSiteVisitRequest($context);
        }

        // Property availability
        if (preg_match('/(available|बचा|खाली|status)/i', $lowerQuery)) {
            return $this->handleAvailabilityQuery($context);
        }

        // Location info
        if (preg_match('/(location|address|पता|कहाँ|map|direction)/i', $lowerQuery)) {
            return $this->handleLocationQuery($context);
        }

        // Default to chat
        return $this->chat($query, $context);
    }

    // ─────── Response Builders ───────────────────────────────────────

    private function buildResponse(string $message, array $intent, array $slResult, array $context): array
    {
        $intentName = $intent['intent'] ?? 'unknown';
        $lang = $intent['language'] ?? 'hi';

        switch ($intentName) {
            case 'buy_property':
            case 'enquiry':
                return [
                    'text' => $lang === 'hi'
                        ? "Namaste! APS Dream Home mein aapka swagat hai. 🏠\n\nHum Raghunath Nagri, Gorakhpur mein premium plots offer karte hain.\n\nAapko kis type ki property chahiye?\n- Budget kitna hai?\n- Kaunsa area pasand hai?\n- Kitne sqft chahiye?\n\nHum aapko best options dikhayenge!"
                        : "Welcome to APS Dream Home! 🏠\n\nWe offer premium plots in Raghunath Nagri, Gorakhpur.\n\nPlease share your requirements:\n- Budget range?\n- Preferred area?\n- Size needed?\n\nWe'll show you the best options!",
                    'engine' => 'rule',
                ];

            case 'site_visit':
                return [
                    'text' => $lang === 'hi'
                        ? "Bilkul! Site visit ke liye aapko welcome karenge. 📅\n\nKripya batayein:\n- Kaunsa din convenient hai?\n- Kitne baje aana chahenge?\n- Aap ka phone number?\n\nHum aapko confirm kar denge!"
                        : "Absolutely! You're welcome for a site visit. 📅\n\nPlease share:\n- Which day is convenient?\n- What time?\n- Your phone number?\n\nWe'll confirm your visit!",
                    'action' => 'schedule_visit',
                    'engine' => 'rule',
                ];

            case 'price_inquiry':
                return [
                    'text' => $lang === 'hi'
                        ? "APS Dream Home mein plots ki starting price ₹10 Lakh se hai. 💰\n\nExact price plot size aur location par depend karta hai.\n\nAapka budget kitna hai? Hum aapko perfect plot suggest karenge.\n\nYa aap humein call karein: [PHONE]"
                        : "APS Dream Home plots start from ₹10 Lakh. 💰\n\nExact pricing depends on plot size and location.\n\nWhat's your budget? We'll suggest the perfect plot.\n\nOr call us: [PHONE]",
                    'engine' => 'rule',
                ];

            case 'greeting':
                $timeGreeting = $this->getTimeGreeting();
                return [
                    'text' => $lang === 'hi'
                        ? "$timeGreeting! 🙏\n\nAPS Dream Home mein aapka swagat hai.\n\nMain aapki real estate mein help kar sakta hun. Aapko kya janna hai?\n\n- Property dekhni hai?\n- Price jaanna hai?\n- Site visit book karna hai?"
                        : "$timeGreeting! 🙏\n\nWelcome to APS Dream Home.\n\nI can help you with real estate queries.\n\n- View properties?\n- Check prices?\n- Book a site visit?",
                    'engine' => 'rule',
                ];

            default:
                // Use SelfLearningAI for complex queries
                $slResponse = $slResult['response'] ?? null;
                if ($slResponse && strlen($slResponse) > 10) {
                    return ['text' => $slResponse, 'engine' => 'self_learning'];
                }

                return [
                    'text' => $lang === 'hi'
                        ? "Main samajh gaya. APS Dream Home mein aapki kya help kar sakta hun?\n\n- Property ki jaankari\n- Price aur EMI\n- Site visit booking\n- Complaint register\n\nAapko kya chahiye?"
                        : "I understand. How can I help you at APS Dream Home?\n\n- Property information\n- Pricing and EMI\n- Site visit booking\n- Register complaint\n\nWhat would you like?",
                    'engine' => 'rule',
                ];
        }
    }

    private function handlePriceInquiry(string $query, array $context): array
    {
        try {
            $plots = $this->db->fetchAll(
                "SELECT p.*, c.name as colony_name FROM plots p LEFT JOIN colonies c ON p.colony_id = c.id
                 WHERE p.status = 'available' ORDER BY p.total_price ASC LIMIT 5"
            ) ?: [];

            $response = "APS Dream Home - Available Plots:\n\n";
            foreach ($plots as $i => $p) {
                $response .= ($i + 1) . ". {$p['colony_name']} - {$p['block']} Block\n";
                $response .= "   Size: {$p['area_sqft']} sqft | Price: ₹" . number_format($p['total_price']) . "\n\n";
            }
            $response .= "Zyada jaankari ke liye call karein ya site visit book karein.";

            return ['text' => $response, 'engine' => 'database'];
        } catch (\Throwable $e) {
            return ['text' => "Prices start from ₹10 Lakh. Please call us for exact pricing.", 'engine' => 'rule'];
        }
    }

    private function handleEMIInquiry(string $query, array $context): array
    {
        return [
            'text' => "EMI Calculator:\n\nHumare plots par flexible EMI options available hain.\n\nExample:\n- Plot ₹15 Lakh → ~₹12,500/month (5 years)\n- Plot ₹25 Lakh → ~₹20,833/month (5 years)\n- Plot ₹40 Lakh → ~₹33,333/month (5 years)\n\nDown payment: 20-30%\nTenure: 3-7 years\n\nBank financing available through:\n- SBI, HDFC, ICICI, Axis\n\nAapka budget kitna hai? Hum exact EMI calculate kar denge.",
            'engine' => 'rule',
        ];
    }

    private function handleSiteVisitRequest(array $context): array
    {
        return [
            'text' => "Site visit ke liye dhanyavaad! 📅\n\nRaghunath Nagri, Gorakhpur visit kar sakte hain.\n\nKripya batayein:\n1. Kaunsa din? (aaj, kal, is hafte)\n2. Kitne baje? (subah/afternoon/evening)\n3. Aapka phone number\n4. Kitne log aayenge?\n\nHum aapko confirm kar denge aur plot details ready rakhenge!",
            'action' => 'schedule_visit',
            'engine' => 'rule',
        ];
    }

    private function handleAvailabilityQuery(array $context): array
    {
        try {
            $count = (int)$this->db->fetch("SELECT COUNT(*) as cnt FROM plots WHERE status = 'available'")['cnt'];
            $colonyWise = $this->db->fetchAll(
                "SELECT c.name, COUNT(p.id) as available FROM plots p LEFT JOIN colonies c ON p.colony_id = c.id
                 WHERE p.status = 'available' GROUP BY c.name"
            ) ?: [];

            $response = "Available Plots: $count total\n\n";
            foreach ($colonyWise as $c) {
                $response .= "- {$c['name']}: {$c['available']} plots\n";
            }
            $response .= "\nDetailed list ke liye visit karein ya call karein.";

            return ['text' => $response, 'engine' => 'database'];
        } catch (\Throwable $e) {
            return ['text' => "Multiple plots available. Please visit or call for current availability.", 'engine' => 'rule'];
        }
    }

    private function handleLocationQuery(array $context): array
    {
        return [
            'text' => "APS Dream Home - Raghunath Nagri, Gorakhpur\n\n📍 Location: Raghunath Nagri, Gorakhpur, UP\n\nLandmarks:\n- Gorakhpur Junction: 5 km\n- BRD Medical College: 3 km\n- City Mall: 4 km\n- Gorakhpur University: 6 km\n\nConnectivity:\n- NH-27 access\n- Public transport available\n- School, Hospital nearby\n\nGoogle Maps: [LINK]\n\nVisit karne ke liye hum se contact karein!",
            'engine' => 'rule',
        ];
    }

    // ─────── Helpers ─────────────────────────────────────────────────

    private function getTimeGreeting(): string
    {
        $hour = (int)date('H');
        if ($hour < 12) return 'Suprabhat';
        if ($hour < 17) return 'Namaste';
        if ($hour < 21) return 'Namaste';
        return 'Shubh Sandhya';
    }

    private function getFollowUpSuggestions(string $intent): array
    {
        $suggestions = [
            'buy_property' => ['Price dekhein', 'Site visit book karein', 'EMI calculate karein'],
            'greeting' => ['Property dekhni hai', 'Price jaanna hai', 'Site visit book karein'],
            'price_inquiry' => ['EMI calculator', 'Site visit book karein', 'Payment plan dekhein'],
            'site_visit' => ['Calendar dekhein', 'Location dekhein', 'Contact karein'],
        ];
        return $suggestions[$intent] ?? ['Property dekhni hai', 'Price jaanna hai', 'Contact karein'];
    }

    private function logConversation(string $sessionId, ?int $userId, string $message, string $response, array $intent): void
    {
        try {
            $this->db->getConnection()->prepare(
                "INSERT INTO ai_chat_messages (session_id, user_id, role, content, intent, language, created_at" . ( $this->tenantId() > 1 ? ', tenant_id' : '' ) . ")
                 VALUES (?, ?, 'user', ?, ?, ?, NOW()" . ( $this->tenantId() > 1 ? ', ' . $this->tenantId() : '' ) . ")"
            )->execute(array_merge([$sessionId, $userId, $message, $intent['intent'] ?? '', $intent['language'] ?? 'hi'], $this->tenantId() > 1 ? [$this->tenantId()] : []));

            $this->db->getConnection()->prepare(
                "INSERT INTO ai_chat_messages (session_id, user_id, role, content, created_at" . ( $this->tenantId() > 1 ? ', tenant_id' : '' ) . ")
                 VALUES (?, ?, 'assistant', ?, NOW()" . ( $this->tenantId() > 1 ? ', ' . $this->tenantId() : '' ) . ")"
            )->execute(array_merge([$sessionId, $userId, $response], $this->tenantId() > 1 ? [$this->tenantId()] : []));
        } catch (\Throwable $e) { /* non-critical */ error_log($e->getMessage()); }
    }
}
