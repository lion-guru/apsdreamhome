<?php

/**
 * Smart AI Chatbot Controller
 * RBAC-enabled, Gemini-powered, Human-like conversations
 * Can learn and perform actions
 */

namespace App\Http\Controllers;

use App\Core\Database\Database;

class SmartAIController extends BaseController
{
    private $geminiApiKey;
    private $geminiEndpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';
    private $systemPrompt;

    public function __construct()
    {
        parent::__construct();

        // Load Gemini API key from config
        $config = json_decode(file_get_contents(__DIR__ . '/../../../config/app_config.json'), true);
        $this->geminiApiKey = $config['ai']['gemini_api_key'] ?? '';

        // Build system prompt with project knowledge
        $this->systemPrompt = $this->buildSystemPrompt();
    }

    /**
     * Main chat endpoint - RBAC aware
     */
    public function chat()
    {
        header('Content-Type: application/json');

        // Get user context
        $userContext = $this->getUserContext();

        $message = trim($_POST['message'] ?? $_GET['message'] ?? '');
        $sessionId = $_POST['session_id'] ?? $_GET['session_id'] ?? session_id();
        $language = $_POST['language'] ?? $this->detectLanguage($message);

        if (empty($message)) {
            echo json_encode(['error' => 'Kuch to likhiye! / Please type something!']);
            exit;
        }

        // Check for actions first (booking, lead creation, etc.)
        $actionResult = $this->detectAndPerformAction($message, $userContext);

        // Get AI response
        if (!empty($this->geminiApiKey) && $this->geminiApiKey !== 'YOUR_GEMINI_API_KEY_HERE') {
            $response = $this->getGeminiResponse($message, $userContext, $language);
        } else {
            // Fallback to smart local processing
            $response = $this->getSmartLocalResponse($message, $userContext, $language);
        }

        // Add action confirmation if action was performed
        if ($actionResult['performed']) {
            $response .= "\n\n✅ " . $actionResult['message'];
        }

        // Save conversation for learning
        $this->saveConversation($sessionId, $message, $response, $userContext);

        echo json_encode([
            'success' => true,
            'response' => $response,
            'session_id' => $sessionId,
            'user_context' => $userContext['role'],
            'language' => $language,
            'action_performed' => $actionResult['performed'] ?? false
        ]);
        exit;
    }

    /**
     * Get user context with RBAC
     */
    private function getUserContext()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $context = [
            'role' => 'guest',
            'name' => 'Guest',
            'id' => null,
            'data' => []
        ];

        // Check all user types
        if (isset($_SESSION['admin_user_id'])) {
            $context['role'] = 'admin';
            $context['name'] = $_SESSION['admin_name'] ?? 'Admin';
            $context['id'] = $_SESSION['admin_user_id'];
        } elseif (isset($_SESSION['associate_id'])) {
            $context['role'] = 'associate';
            $context['name'] = $_SESSION['associate_name'] ?? 'Associate';
            $context['id'] = $_SESSION['associate_id'];
            $context['data'] = $this->getAssociateData($context['id']);
        } elseif (isset($_SESSION['agent_id'])) {
            $context['role'] = 'agent';
            $context['name'] = $_SESSION['agent_name'] ?? 'Agent';
            $context['id'] = $_SESSION['agent_id'];
        } elseif (isset($_SESSION['employee_id'])) {
            $context['role'] = 'employee';
            $context['name'] = $_SESSION['employee_name'] ?? 'Employee';
            $context['id'] = $_SESSION['employee_id'];
        } elseif (isset($_SESSION['user_id'])) {
            $context['role'] = 'customer';
            $context['name'] = $_SESSION['user_name'] ?? 'Customer';
            $context['id'] = $_SESSION['user_id'];
            $context['data'] = $this->getCustomerData($context['id']);
        }

        return $context;
    }

    /**
     * Get associate data for personalized responses
     */
    private function getAssociateData($associateId)
    {
        try {
            $data = [];

            // Get network stats
            $networkStats = $this->db->fetch(
                "SELECT COUNT(*) as total FROM associates WHERE referrer_id = ?",
                [$associateId]
            );
            $data['network_size'] = $networkStats['total'] ?? 0;

            // Get commission stats
            $commissionStats = $this->db->fetch(
                "SELECT SUM(amount) as total FROM commissions WHERE associate_id = ? AND status = 'paid'",
                [$associateId]
            );
            $data['total_commission'] = $commissionStats['total'] ?? 0;

            // Get pending commission
            $pendingStats = $this->db->fetch(
                "SELECT SUM(amount) as total FROM commissions WHERE associate_id = ? AND status = 'pending'",
                [$associateId]
            );
            $data['pending_commission'] = $pendingStats['total'] ?? 0;

            // Get leads count
            $leadsStats = $this->db->fetch(
                "SELECT COUNT(*) as total FROM leads WHERE associate_id = ?",
                [$associateId]
            );
            $data['total_leads'] = $leadsStats['total'] ?? 0;

            return $data;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get customer data for personalized responses
     */
    private function getCustomerData($userId)
    {
        try {
            $data = [];

            // Get property count
            $propertyStats = $this->db->fetch(
                "SELECT COUNT(*) as total FROM user_properties WHERE user_id = ?",
                [$userId]
            );
            $data['total_properties'] = $propertyStats['total'] ?? 0;

            // Get inquiry count
            $inquiryStats = $this->db->fetch(
                "SELECT COUNT(*) as total FROM inquiries WHERE user_id = ? OR email = ?",
                [$userId, $_SESSION['user_email'] ?? '']
            );
            $data['total_inquiries'] = $inquiryStats['total'] ?? 0;

            return $data;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get response from Gemini API
     */
    private function getGeminiResponse($message, $userContext, $language)
    {
        // Build context-aware prompt
        $contextPrompt = $this->buildContextPrompt($userContext);

        $prompt = $this->systemPrompt . "\n\n" . $contextPrompt . "\n\n";
        $prompt .= "User Message (in " . ($language === 'hi' ? 'Hindi' : 'English') . "): " . $message . "\n\n";
        $prompt .= "Instructions:\n";
        $prompt .= "1. Reply naturally like a helpful human assistant\n";
        $prompt .= "2. Use mix of Hindi-English (Hinglish) if user message is in Hindi\n";
        $prompt .= "3. Be friendly and professional\n";
        $prompt .= "4. If user is asking about projects, give specific details\n";
        $prompt .= "5. If user wants to buy/sell/rent, guide them step by step\n";
        $prompt .= "6. Keep responses concise but informative\n";
        $prompt .= "7. Use emojis where appropriate 😊\n\n";
        $prompt .= "Response:";

        try {
            $url = $this->geminiEndpoint . '?key=' . $this->geminiApiKey;

            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 500,
                    'topP' => 0.9
                ]
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $response = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($response, true);

            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                return trim($result['candidates'][0]['content']['parts'][0]['text']);
            }

            return $this->getSmartLocalResponse($message, $userContext, $language);
        } catch (\Exception $e) {
            error_log("Gemini API Error: " . $e->getMessage());
            return $this->getSmartLocalResponse($message, $userContext, $language);
        }
    }

    /**
     * Smart local response (fallback)
     */
    private function getSmartLocalResponse($message, $userContext, $language)
    {
        $msg = strtolower($message);
        $name = $userContext['name'];
        $role = $userContext['role'];

        // Role-specific greetings
        if (strpos($msg, 'hello') !== false || strpos($msg, 'hi') !== false || strpos($msg, 'namaste') !== false || strpos($msg, 'namaskar') !== false) {
            if ($role === 'associate') {
                $commission = number_format($userContext['data']['total_commission'] ?? 0);
                return "👋 Namaste {$name}! Aapka APS Dream Home associate dashboard mein swagat hai!\n\n💰 Aapki total commission: ₹{$commission}\n👥 Network size: " . ($userContext['data']['network_size'] ?? 0) . "\n\nMain aapki kya madad kar sakta hoon?";
            } elseif ($role === 'customer') {
                return "👋 Hello {$name}! APS Dream Home mein aapka swagat hai!\n\n🏠 Aapki properties: " . ($userContext['data']['total_properties'] ?? 0) . "\n\nMain aapki kya help kar sakta hoon? Buy, sell, rent ya kuch aur?";
            } else {
                return "👋 Namaste! APS Dream Home mein aapka swagat hai!\n\n🏠 Main aapki property search mein madad kar sakta hoon. Kya chahiye aapko?";
            }
        }

        // Intent detection
        if (strpos($msg, 'buy') !== false || strpos($msg, 'kharid') !== false || strpos($msg, 'plot') !== false || strpos($msg, 'ghar') !== false || strpos($msg, 'makan') !== false) {
            return "🎯 *Perfect! Aap buy karna chahte hain.*\n\n🏠 Main projects:\n📍 *Suryoday Colony* - Gorakhpur (Premium)\n📍 *Raghunath City Center* - Gorakhpur\n📍 *Braj Radha Enclave* - Lucknow\n📍 *Budh Bihar Colony* - Kushinagar\n\n💰 *Starting from ₹5.5 Lakh*\n\nKaunsa location prefer karenge aap? Ya budget bataiye?";
        }

        if (strpos($msg, 'sell') !== false || strpos($msg, 'bech') !== false || strpos($msg, 'list') !== false || strpos($msg, 'post') !== false) {
            return "🏷️ *Aap apni property sell karna chahte hain!*\n\n✅ *100% FREE listing*\n✅ *No commission*\n✅ *Verified buyers*\n\n📋 Form fill karein bas 1 minute mein:\n👉 " . BASE_URL . "/list-property\n\nYa seedha phone karein:\n📱 +91 92771 21112\n\nProperty ka type kya hai? (Plot, House, Flat, Shop)";
        }

        if (strpos($msg, 'price') !== false || strpos($msg, 'rate') !== false || strpos($msg, 'cost') !== false || strpos($msg, 'kitna') !== false || strpos($msg, 'paisa') !== false) {
            return "💰 *Pricing Details:*\n\n🏠 *Residential Plots:*\n• Suryoday Colony: ₹5.5L - ₹15L\n• Raghunath City: ₹8L - ₹20L\n• Budh Bihar: ₹4L - ₹10L\n\n🏢 *Commercial:*\n• Raghunath City Center: ₹15L - ₹50L\n\n🏡 *Houses:*\n• Starting ₹25L onwards\n\nBudget bataiye, main best options suggest karunga!";
        }

        if (strpos($msg, 'loan') !== false || strpos($msg, 'finance') !== false || strpos($msg, 'emi') !== false || strpos($msg, 'bank') !== false) {
            return "🏦 *Home Loan Facility Available!*\n\n✅ Instant approval\n✅ Low interest rates (8.5% onwards)\n✅ Flexible EMI options\n✅ 20 years tenure\n\n📋 Required Documents:\n• Aadhaar & PAN\n• Income Proof\n• Bank Statements (6 months)\n• Property Documents\n\n👉 Apply now: " . BASE_URL . "/financial-services\n\nLoan amount kitna chahiye aapko?";
        }

        if (strpos($msg, 'commission') !== false && $role === 'associate') {
            $total = number_format($userContext['data']['total_commission'] ?? 0);
            $pending = number_format($userContext['data']['pending_commission'] ?? 0);
            return "💰 *Aapki Commission Details:*\n\n✅ Total Earned: ₹{$total}\n⏳ Pending: ₹{$pending}\n\n💡 *Commission Structure:*\n• Direct Sale: 2%\n• Level 1 Referral: 1%\n• Level 2 Referral: 0.5%\n\nAur leads add karein commission badhane ke liye! 👥";
        }

        if (strpos($msg, 'network') !== false || strpos($msg, 'team') !== false || strpos($msg, 'referral') !== false) {
            if ($role === 'associate') {
                $size = $userContext['data']['network_size'] ?? 0;
                return "👥 *Aapka Network:*\n\nTotal Associates: {$size}\n\n🔗 *Referral Link:*\n" . BASE_URL . "/associate/register?ref=" . $userContext['id'] . "\n\n📱 Social media par share karein:\n• WhatsApp\n• Facebook\n• Instagram\n\nJitne zyada referrals, utna zyada commission! 💰";
            }
        }

        // Default response
        return "🤔 Main samajh gaya aap yeh kehna chahte hain: \"{$message}\"\n\n🏠 *APS Dream Home Services:*\n1️⃣ Property Buy/Sell/Rent\n2️⃣ Home Loan Assistance\n3️⃣ Legal Documentation\n4️⃣ Interior Design\n5️⃣ Property Valuation\n\nKya main inmein se kisi mein madad kar sakta hoon? 😊";
    }

    /**
     * Detect and perform actions
     */
    private function detectAndPerformAction($message, $userContext)
    {
        $msg = strtolower($message);
        $result = ['performed' => false, 'message' => ''];

        // Auto-create lead if user shows buying intent
        if (($userContext['role'] === 'customer' || $userContext['role'] === 'guest') &&
            (strpos($msg, 'interested') !== false || strpos($msg, 'book') !== false || strpos($msg, 'buy') !== false)
        ) {

            // Extract phone number if present
            preg_match('/\d{10}/', $message, $matches);
            $phone = $matches[0] ?? null;

            if ($phone && $userContext['id']) {
                try {
                    $this->db->query(
                        "INSERT INTO leads (user_id, name, phone, source, status, created_at) VALUES (?, ?, ?, 'ai_chatbot', 'new', NOW())",
                        [$userContext['id'], $userContext['name'], $phone]
                    );
                    $result['performed'] = true;
                    $result['message'] = "Aapki lead humare team ko bhej di gayi hai! Aapko 24 ghante mein call karenge. 📞";
                } catch (\Exception $e) {
                    error_log("Lead creation error: " . $e->getMessage());
                }
            }
        }

        return $result;
    }

    /**
     * Build system prompt with project knowledge
     */
    private function buildSystemPrompt()
    {
        return <<<EOT
You are APS AI - a smart, friendly real estate assistant for APS Dream Home (Uttar Pradesh, India).

PROJECT KNOWLEDGE:
- Company: APS Dream Home - Premium Real Estate in UP
- Locations: Gorakhpur, Kushinagar, Lucknow, Varanasi
- Projects: Suryoday Heights, Raghunath City Center, Braj Radha Enclave, Budh Bihar Colony
- Price Range: ₹5.5 Lakh to ₹50 Lakh
- Services: Buy, Sell, Rent, Home Loan, Legal, Interior Design

PERSONALITY:
- Friendly, helpful, professional
- Speak in Hinglish (Hindi + English mix)
- Use emojis naturally
- Be concise but informative
- Always offer next steps

RULES:
1. Greet users warmly
2. Ask clarifying questions when needed
3. Provide specific project details when asked
4. Guide users to appropriate services
5. Never make up information
6. Always direct to /list-property for selling
7. Promote APS Dream Home positively
EOT;
    }

    /**
     * Build context prompt based on user role
     */
    private function buildContextPrompt($userContext)
    {
        $prompt = "CURRENT USER CONTEXT:\n";
        $prompt .= "Role: " . ucfirst($userContext['role']) . "\n";
        $prompt .= "Name: " . $userContext['name'] . "\n";

        if ($userContext['role'] === 'associate') {
            $prompt .= "Network Size: " . ($userContext['data']['network_size'] ?? 0) . "\n";
            $prompt .= "Total Commission: ₹" . number_format($userContext['data']['total_commission'] ?? 0) . "\n";
            $prompt .= "Pending Commission: ₹" . number_format($userContext['data']['pending_commission'] ?? 0) . "\n";
            $prompt .= "Total Leads: " . ($userContext['data']['total_leads'] ?? 0) . "\n";
        } elseif ($userContext['role'] === 'customer') {
            $prompt .= "Total Properties: " . ($userContext['data']['total_properties'] ?? 0) . "\n";
            $prompt .= "Total Inquiries: " . ($userContext['data']['total_inquiries'] ?? 0) . "\n";
        }

        return $prompt;
    }

    /**
     * Detect language
     */
    private function detectLanguage($message)
    {
        $hindiWords = ['namaste', 'kya', 'kaise', 'kitna', 'kaha', 'kaun', 'mera', 'aapka', 'hai', 'hain', 'karo', 'kar', 'do', 'de', 'le', 'ja', 'aa', 'ghar', 'plot', 'makan', 'dukan', 'kharid', 'bech'];

        $msg = strtolower($message);
        foreach ($hindiWords as $word) {
            if (strpos($msg, $word) !== false) {
                return 'hi';
            }
        }

        return 'en';
    }

    /**
     * Save conversation for learning
     */
    private function saveConversation($sessionId, $message, $response, $userContext)
    {
        try {
            $this->db->query(
                "INSERT INTO ai_conversations (session_id, user_id, user_role, message, response, created_at) VALUES (?, ?, ?, ?, ?, NOW())",
                [$sessionId, $userContext['id'], $userContext['role'], $message, $response]
            );
        } catch (\Exception $e) {
            error_log("Conversation save error: " . $e->getMessage());
        }
    }

    /**
     * Get conversation history
     */
    public function history()
    {
        header('Content-Type: application/json');

        $sessionId = $_GET['session_id'] ?? session_id();

        try {
            $history = $this->db->fetchAll(
                "SELECT message, response, created_at FROM ai_conversations WHERE session_id = ? ORDER BY created_at DESC LIMIT 50",
                [$sessionId]
            );

            echo json_encode(['success' => true, 'history' => array_reverse($history)]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Render AI Assistant Page
     */
    public function assistantPage()
    {
        // Get user context for personalized greeting
        $userContext = $this->getUserContext();

        $data = [
            'page_title' => 'AI Assistant - APS Dream Home',
            'page_description' => 'Get instant help from our AI assistant',
            'user_context' => $userContext
        ];

        $this->render('pages/ai_assistant', $data);
    }
}
