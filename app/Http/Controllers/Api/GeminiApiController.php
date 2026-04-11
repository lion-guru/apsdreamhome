<?php

namespace App\Http\Controllers\Api;

use App\Services\GeminiAIService;
use App\Http\Controllers\BaseController;

/**
 * Gemini AI API Controller - Public API endpoints
 */
class GeminiApiController extends BaseController
{
    private $geminiService;

    public function __construct()
    {
        parent::__construct();
        $this->geminiService = new GeminiAIService();
    }

    /**
     * Skip CSRF protection for chat API endpoint
     */
    protected function skipCsrfProtection(): bool
    {
        // Skip CSRF for all Gemini API endpoints
        $uri = $_SERVER['REQUEST_URI'] ?? $_SERVER['PATH_INFO'] ?? '';
        $script = $_SERVER['SCRIPT_NAME'] ?? '';

        // Check various ways the URI might be presented
        $checkStrings = [
            '/api/gemini/',
            'api/gemini/',
        ];

        foreach ($checkStrings as $check) {
            if (strpos($uri, $check) !== false || strpos($script, $check) !== false) {
                return true;
            }
        }

        // Also skip if it's an AJAX/Fetch API request with JSON content type
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            // Additional check for API endpoint
            if (strpos($uri, '/api/') !== false) {
                return true;
            }
        }

        return parent::skipCsrfProtection();
    }

    /**
     * Chat with Gemini AI - Public API with fallback
     */
    public function chat()
    {
        header('Content-Type: application/json');

        // Read and decode input
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);

        // Support both 'message' and 'query' parameters
        $message = $input['message'] ?? $input['query'] ?? '';
        $userName = $input['user_name'] ?? null;
        $context = $input['context'] ?? [];
        $sessionId = $input['session_id'] ?? session_id();

        if (empty($message)) {
            echo json_encode([
                'success' => false,
                'error' => 'Message is required'
            ]);
            return;
        }

        // Detect user role and fetch personalized context
        $userContext = $this->getUserContext();
        $userRole = $userContext['role'] ?? 'guest';
        $userId = $userContext['user_id'] ?? null;

        // Override with session data if available
        if (!$userName && isset($_SESSION['user_name'])) {
            $userName = $_SESSION['user_name'];
        }

        // Try Gemini API first with context
        $messages = [];

        // Build dynamic system prompt based on user role
        $systemPrompt = $this->buildSystemPrompt($userRole, $userContext);

        if ($userName) {
            $systemPrompt .= " The user's name is {$userName}.";
        }

        $messages[] = ['role' => 'system', 'content' => $systemPrompt];

        // Add conversation context
        foreach ($context as $ctx) {
            $messages[] = ['role' => $ctx['role'], 'content' => $ctx['content']];
        }

        // Add current message
        $messages[] = ['role' => 'user', 'content' => $message];

        $geminiResult = $this->geminiService->chat($messages);

        // If Gemini API succeeds and has content, use it
        if ($geminiResult['success'] && !empty($geminiResult['data']['candidates'][0]['content']['parts'][0]['text'])) {
            $aiResponse = $geminiResult['data']['candidates'][0]['content']['parts'][0]['text'];

            // Log conversation if table exists
            $this->logConversation($sessionId, $userName, $message, $aiResponse, 'gemini');

            echo json_encode([
                'success' => true,
                'reply' => $this->formatResponse($aiResponse),
                'source' => 'gemini',
                'quick_replies' => $this->getQuickReplies($message, $userContext)
            ]);
            return;
        }

        // Fallback to smart local response
        $localResponse = $this->getSmartLocalResponse($message, $userName);

        echo json_encode([
            'success' => true,
            'reply' => $localResponse,
            'source' => 'local',
            'quick_replies' => $this->getQuickReplies($message, $userContext)
        ]);
    }

    /**
     * Generate content - Public API
     */
    public function generateContent()
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $prompt = $input['prompt'] ?? '';
        $options = $input['options'] ?? [];

        if (empty($prompt)) {
            echo json_encode([
                'success' => false,
                'error' => 'Prompt is required'
            ]);
            return;
        }

        $result = $this->geminiService->generateContent($prompt, $options);

        echo json_encode($result);
    }

    /**
     * Property recommendations - Public API
     */
    public function propertyRecommendations()
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $preferences = $input['preferences'] ?? '';

        if (empty($preferences)) {
            echo json_encode([
                'success' => false,
                'error' => 'Preferences are required'
            ]);
            return;
        }

        $result = $this->geminiService->generatePropertyRecommendations($preferences);

        echo json_encode($result);
    }

    /**
     * Customer support - Public API
     */
    public function customerSupport()
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $query = $input['query'] ?? '';
        $context = $input['context'] ?? '';

        if (empty($query)) {
            echo json_encode([
                'success' => false,
                'error' => 'Query is required'
            ]);
            return;
        }

        $result = $this->geminiService->customerSupport($query, $context);

        echo json_encode($result);
    }

    /**
     * Market analysis - Public API
     */
    public function marketAnalysis()
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $location = $input['location'] ?? '';
        $propertyType = $input['property_type'] ?? '';

        if (empty($location)) {
            echo json_encode([
                'success' => false,
                'error' => 'Location is required'
            ]);
            return;
        }

        $result = $this->geminiService->analyzeMarketTrends($location, $propertyType);

        echo json_encode($result);
    }

    /**
     * Social media content - Public API
     */
    public function socialMediaContent()
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $topic = $input['topic'] ?? '';
        $platform = $input['platform'] ?? 'general';

        if (empty($topic)) {
            echo json_encode([
                'success' => false,
                'error' => 'Topic is required'
            ]);
            return;
        }

        $result = $this->geminiService->generateSocialMediaContent($topic, $platform);

        echo json_encode($result);
    }

    /**
     * Test API connection - Public API
     */
    public function testConnection()
    {
        header('Content-Type: application/json');

        $result = $this->geminiService->testConnection();

        echo json_encode($result);
    }

    /**
     * Get API status - Public API
     */
    public function getStatus()
    {
        header('Content-Type: application/json');

        $stats = $this->geminiService->getUsageStats();

        echo json_encode([
            'success' => true,
            'service' => 'Gemini AI',
            'status' => 'active',
            'version' => '1.0.0',
            'endpoints' => [
                '/api/gemini/chat' => 'POST - Chat with AI',
                '/api/gemini/generate' => 'POST - Generate content',
                '/api/gemini/recommendations' => 'POST - Property recommendations',
                '/api/gemini/support' => 'POST - Customer support',
                '/api/gemini/market-analysis' => 'POST - Market analysis',
                '/api/gemini/social-media' => 'POST - Social media content',
                '/api/gemini/test' => 'GET - Test connection',
                '/api/gemini/status' => 'GET - Service status'
            ],
            'statistics' => $stats
        ]);
    }

    /**
     * Get smart local response when Gemini API is unavailable
     */
    private function getSmartLocalResponse(string $message, ?string $userName = null): string
    {
        $msg = strtolower(trim($message));

        // Personalized greeting if user name is known
        $greeting = $userName ? "🙏 Namaste {$userName}! " : "🙏 Namaste! ";

        // First, try to find answer from AI Knowledge Base
        $knowledgeAnswer = $this->getAnswerFromKnowledgeBase($msg);
        if ($knowledgeAnswer) {
            return $greeting . $knowledgeAnswer;
        }

        // Intent patterns
        if (strpos($msg, 'hello') !== false || strpos($msg, 'hi') !== false || strpos($msg, 'namaste') !== false || strpos($msg, 'hey') !== false) {
            return $greeting . "Welcome to APS Dream Home!\n\nI'm your personal property assistant. I can help you with:\n\n🏠 Find properties\n📍 Location details\n💰 Price information\n🏦 Home loan assistance\n📞 Contact details\n\nWhat are you looking for today?";
        }

        if (strpos($msg, 'price') !== false || strpos($msg, 'kitna') !== false || strpos($msg, 'rate') !== false || strpos($msg, 'cost') !== false || strpos($msg, 'kimat') !== false) {
            return "💰 **Property Prices:**\n\n• Plots starting from ₹5.5 Lakhs\n• Houses from ₹25 Lakhs\n• Commercial shops from ₹15 Lakhs\n• Apartments from ₹30 Lakhs\n\nPrices vary by location and size. Which property type interests you?";
        }

        if (strpos($msg, 'location') !== false || strpos($msg, 'where') !== false || strpos($msg, 'kaha') !== false || strpos($msg, 'address') !== false) {
            return "📍 **Our Projects are in:**\n\n• Gorakhpur - Suryoday Colony, Raghunath Nagri, Braj Radha Nagri\n• Lucknow - Braj Radha Enclave\n• Kushinagar - Buddh Bihar Colony\n• Varanasi - Ganga Nagri\n\nWhich location would you prefer?";
        }

        if (strpos($msg, 'project') !== false || strpos($msg, 'suryoday') !== false || strpos($msg, 'raghunath') !== false || strpos($msg, 'braj') !== false) {
            return "🏗️ **Current Projects:**\n\n**Gorakhpur:**\n• Suryoday Colony - Premium Plots\n• Raghunath Nagri - Commercial\n\n**Lucknow:**\n• Braj Radha Enclave - Residential\n\n**Kushinagar:**\n• Buddh Bihar Colony - Affordable plots\n\n**Varanasi:**\n• Ganga Nagri - Premium location\n\nWould you like details on any specific project?";
        }

        if (strpos($msg, 'plot') !== false || strpos($msg, 'land') !== false || strpos($msg, 'jameen') !== false || strpos($msg, 'naksha') !== false) {
            return "📐 **Available Plots:**\n\n• Residential plots: 451-5000 sq ft\n• Commercial plots: 500-2000 sq ft\n• Farm houses: 1-5 acres\n• Starting from ₹5.5 Lakhs\n\nWe have plots in Gorakhpur, Lucknow, Kushinagar & Varanasi.\n\nInterested in any specific size?";
        }

        if (strpos($msg, 'loan') !== false || strpos($msg, 'finance') !== false || strpos($msg, 'emi') !== false || strpos($msg, 'bank') !== false) {
            return "🏦 **Home Loan Assistance:**\n\nWe partner with leading banks:\n• SBI, HDFC, ICICI, Axis\n• Interest rates from 8.5% onwards\n• Up to 90% property value\n• Quick approval process\n\nOur team will help you with all documentation!\n\nWould you like to speak with our loan expert?";
        }

        if (strpos($msg, 'contact') !== false || strpos($msg, 'phone') !== false || strpos($msg, 'number') !== false || strpos($msg, 'call') !== false || strpos($msg, 'mobile') !== false) {
            return "📞 **Contact Us:**\n\n🕐 Mon-Sat: 9AM - 7PM\n\n📱 **Phone/WhatsApp:**\n+91 92771 21112\n+91 70074 44842\n\n📧 **Email:**\ninfo@apsdreamhome.com\n\n🏢 **Office:**\n1st Floor, Singhariya Chauraha\nKunraghat, Gorakhpur, UP - 273008\n\nCall now for free consultation!";
        }

        if (strpos($msg, 'service') !== false || strpos($msg, 'legal') !== false || strpos($msg, 'registry') !== false || strpos($msg, 'mutation') !== false) {
            return "🛠️ **Our Services:**\n\n• Property Sales & Purchase\n• Legal Documentation\n• Registry & Mutation\n• Interior Design\n• Home Loan Assistance\n• Property Management\n• Investment Consulting\n\nWhich service do you need?";
        }

        if (strpos($msg, 'about') !== false || strpos($msg, 'company') !== false || strpos($msg, 'aps') !== false || strpos($msg, 'dream home') !== false || strpos($msg, 'who') !== false) {
            return "🏢 **APS Dream Home**\n\nYour trusted real estate partner in Uttar Pradesh since 2010.\n\n✅ 5000+ Happy Customers\n✅ 50+ Projects Completed\n✅ 10+ Cities Covered\n✅ RERA Registered\n✅ Legal & Transparent\n\nWe deal in Residential, Commercial & Agricultural properties.\n\nHow can we help you today?";
        }

        if (strpos($msg, 'buy') !== false || strpos($msg, 'purchase') !== false || strpos($msg, 'kharidna') !== false || strpos($msg, 'book') !== false) {
            return "🏠 **Buy Property:**\n\nGreat choice! We have:\n• Ready-to-move houses\n• Investment plots\n• Commercial shops\n• Apartments & flats\n\nWhat's your budget and preferred location?\n\nYou can also browse at:\n🌐 localhost/apsdreamhome/properties";
        }

        if (strpos($msg, 'sell') !== false || strpos($msg, 'sale') !== false || strpos($msg, 'bechna') !== false) {
            return "💰 **Sell Your Property:**\n\nList your property with us - **100% FREE!**\n\n✅ Zero listing charges\n✅ No commission\n✅ Direct buyer contact\n✅ Quick verification\n\nVisit:\n🌐 localhost/apsdreamhome/list-property\n\nOr call us at +91 92771 21112";
        }

        if (strpos($msg, 'rent') !== false || strpos($msg, 'kiraya') !== false || strpos($msg, 'lease') !== false) {
            return "🔑 **Rental Properties:**\n\nWe have rental options:\n• Shops & offices\n• Residential flats\n• Commercial spaces\n• Farm houses\n\nVisit our properties page or call us to discuss your rental requirements.\n\n📞 +91 92771 21112";
        }

        // Default response
        return "🤔 I didn't understand that. Let me help you with:\n\n🏠 Find properties\n📍 Locations\n💰 Prices\n🏦 Home loans\n📞 Contact details\n\nOr type 'help' to see all options!\n\nYou can also call us directly:\n📞 +91 92771 21112";
    }

    /**
     * Format response for display
     */
    private function formatResponse(string $text): string
    {
        // Convert newlines to <br> for HTML display
        return nl2br(htmlspecialchars($text));
    }

    /**
     * Get quick reply suggestions based on message and user role
     */
    private function getQuickReplies(string $message, array $userContext = []): array
    {
        $msg = strtolower($message);
        $role = $userContext['role'] ?? 'guest';

        // Role-based quick replies
        if ($role === 'customer' || $role === 'user') {
            if (strpos($msg, 'booking') !== false || strpos($msg, 'property') !== false) {
                return ['📋 My Bookings', '💰 Payment Status', '🏠 New Property', '📞 Support'];
            }
            if (strpos($msg, 'payment') !== false || strpos($msg, 'emi') !== false) {
                return ['💳 Pay Now', '📄 Payment History', '🏦 EMI Status', '💰 Due Amount'];
            }
            // Default for customers
            return ['📋 My Bookings', '💳 Pay EMI', '🏠 Browse Properties', '📞 Call Support'];
        }

        if ($role === 'associate' || $role === 'agent') {
            if (strpos($msg, 'lead') !== false) {
                return ['📊 My Leads', '➕ Add Lead', '📈 Lead Status', '💰 Commission'];
            }
            if (strpos($msg, 'commission') !== false || strpos($msg, 'payment') !== false) {
                return ['💰 My Commission', '📄 Payout Status', '⏱️ Pending', '📞 Withdraw'];
            }
            if (strpos($msg, 'team') !== false || strpos($msg, 'downline') !== false) {
                return ['👥 My Team', '📊 Team Performance', '➕ Add Member', '🌐 Genealogy'];
            }
            // Default for associates
            return ['📊 My Leads', '💰 Commission', '👥 My Team', '📞 Support'];
        }

        if ($role === 'admin') {
            if (strpos($msg, 'pending') !== false || strpos($msg, 'approval') !== false) {
                return ['✅ Approve Properties', '⏱️ Pending List', '📊 Stats', '📧 Notifications'];
            }
            if (strpos($msg, 'report') !== false || strpos($msg, 'stats') !== false) {
                return ['📊 Today Report', '💰 Revenue', '👥 Visitors', '🏠 Bookings'];
            }
            // Default for admin
            return ['✅ Pending Approvals', '📊 Dashboard', '👥 Users', '📞 Support'];
        }

        // Guest/Public quick replies
        if (strpos($msg, 'price') !== false || strpos($msg, 'kitna') !== false || strpos($msg, 'rate') !== false) {
            return ['📐 Plot prices', '🏠 House prices', '🏪 Shop prices', '💰 EMI options'];
        }

        if (strpos($msg, 'location') !== false || strpos($msg, 'kaha') !== false || strpos($msg, 'address') !== false) {
            return ['📍 Gorakhpur', '📍 Lucknow', '📍 Kushinagar', '📍 Varanasi'];
        }

        if (strpos($msg, 'loan') !== false || strpos($msg, 'emi') !== false || strpos($msg, 'finance') !== false) {
            return ['🏦 SBI Loan', '🏦 HDFC Loan', '📋 Documents needed', '💰 EMI Calculator'];
        }

        if (strpos($msg, 'buy') !== false || strpos($msg, 'plot') !== false || strpos($msg, 'property') !== false) {
            return ['📐 Buy Plot', '🏠 Buy House', '🏪 Buy Shop', '📞 Schedule Visit'];
        }

        if (strpos($msg, 'project') !== false || strpos($msg, 'suryoday') !== false || strpos($msg, 'raghunath') !== false) {
            return ['🏗️ Suryoday Heights', '🏗️ Raghunath City', '🏗️ Braj Radha', '📍 All Projects'];
        }

        if (strpos($msg, 'contact') !== false || strpos($msg, 'phone') !== false || strpos($msg, 'call') !== false) {
            return ['📞 +91 92771 21112', '💬 WhatsApp', '📧 Email', '📍 Visit Office'];
        }

        // Default suggestions for guests
        return ['💰 Check Prices', '📍 Locations', '🏦 Home Loan', '📞 Contact Us'];
    }

    /**
     * Get answer from AI Knowledge Base
     */
    private function getAnswerFromKnowledgeBase(string $message): ?string
    {
        try {
            $db = \App\Core\Database\Database::getInstance();

            // Search for matching patterns in knowledge base (all entries are active by default)
            $patterns = $db->fetchAll(
                "SELECT question_pattern, answer FROM ai_knowledge_base"
            );

            foreach ($patterns as $row) {
                $pattern = strtolower($row['question_pattern']);
                // Check if message contains the pattern keywords
                $keywords = explode(' ', $pattern);
                $matchCount = 0;

                foreach ($keywords as $keyword) {
                    if (strlen($keyword) > 2 && strpos($message, $keyword) !== false) {
                        $matchCount++;
                    }
                }

                // If at least 2 keywords match or 50% match
                if ($matchCount >= 2 || ($matchCount > 0 && count($keywords) <= 2)) {
                    // Update usage count
                    try {
                        $db->execute(
                            "UPDATE ai_knowledge_base SET usage_count = usage_count + 1 WHERE question_pattern = ?",
                            [$row['question_pattern']]
                        );
                    } catch (\Exception $e) {
                        // Ignore update errors
                    }
                    return $row['answer'];
                }
            }

            return null;
        } catch (\Exception $e) {
            // Table might not exist
            return null;
        }
    }

    /**
     * Log conversation to database (if table exists)
     */
    private function logConversation(string $sessionId, ?string $userName, string $message, string $response, string $source): void
    {
        try {
            $db = \App\Core\Database\Database::getInstance();
            $db->execute(
                "INSERT INTO ai_conversations (session_id, user_name, message, response, source, created_at) 
                 VALUES (?, ?, ?, ?, ?, NOW())",
                [$sessionId, $userName, $message, $response, $source]
            );
        } catch (\Exception $e) {
            // Table might not exist, ignore error
        }
    }

    /**
     * Detect user role and fetch context from session/database
     */
    private function getUserContext(): array
    {
        $context = ['role' => 'guest', 'user_id' => null];

        // Check session for logged in user
        if (isset($_SESSION['user_id'])) {
            $context['user_id'] = $_SESSION['user_id'];
            $context['user_name'] = $_SESSION['user_name'] ?? null;
            $context['role'] = $_SESSION['user_role'] ?? 'customer';

            // Fetch additional context based on role
            try {
                $db = \App\Core\Database\Database::getInstance();

                if ($context['role'] === 'customer' || $context['role'] === 'user') {
                    // Get customer's bookings/properties
                    $bookings = $db->fetchAll(
                        "SELECT COUNT(*) as count FROM bookings WHERE user_id = ?",
                        [$context['user_id']]
                    );
                    $context['booking_count'] = $bookings[0]['count'] ?? 0;

                    // Get total spent
                    $payments = $db->fetch(
                        "SELECT SUM(amount) as total FROM payments WHERE user_id = ? AND status = 'completed'",
                        [$context['user_id']]
                    );
                    $context['total_spent'] = $payments['total'] ?? 0;
                }

                if ($context['role'] === 'associate' || $context['role'] === 'agent') {
                    // Get associate's stats
                    $leads = $db->fetch(
                        "SELECT COUNT(*) as count FROM leads WHERE assigned_to = ?",
                        [$context['user_id']]
                    );
                    $context['lead_count'] = $leads['count'] ?? 0;

                    // Get commission
                    $commission = $db->fetch(
                        "SELECT SUM(amount) as total FROM commissions WHERE user_id = ?",
                        [$context['user_id']]
                    );
                    $context['total_commission'] = $commission['total'] ?? 0;
                }
            } catch (\Exception $e) {
                // Ignore database errors
            }
        }

        // Check for admin session
        if (isset($_SESSION['admin_id'])) {
            $context['role'] = 'admin';
            $context['user_id'] = $_SESSION['admin_id'];
            $context['user_name'] = $_SESSION['admin_name'] ?? 'Admin';

            // Get admin stats
            try {
                $db = \App\Core\Database\Database::getInstance();

                // Today's visitors
                $visitors = $db->fetch(
                    "SELECT COUNT(*) as count FROM visits WHERE DATE(created_at) = CURDATE()"
                );
                $context['today_visitors'] = $visitors['count'] ?? 0;

                // Pending approvals
                $pending = $db->fetch(
                    "SELECT COUNT(*) as count FROM user_properties WHERE status = 'pending'"
                );
                $context['pending_approvals'] = $pending['count'] ?? 0;

                // Today's bookings
                $bookings = $db->fetch(
                    "SELECT COUNT(*) as count FROM bookings WHERE DATE(created_at) = CURDATE()"
                );
                $context['today_bookings'] = $bookings['count'] ?? 0;
            } catch (\Exception $e) {
                // Ignore errors
            }
        }

        return $context;
    }

    /**
     * Build system prompt based on user role
     */
    private function buildSystemPrompt(string $role, array $context): string
    {
        $basePrompt = "You are APS Property Assistant, a helpful real estate chatbot for APS Dream Home. ";
        $basePrompt .= "You help customers with property inquiries in Gorakhpur, Lucknow, Kushinagar, and Varanasi. ";
        $basePrompt .= "Current projects: Suryoday Colony, Raghunath Nagri, Braj Radha Nagri (Gorakhpur), ";
        $basePrompt .= "Braj Radha Enclave (Lucknow), Buddh Bihar Colony (Kushinagar), Ganga Nagri (Varanasi). ";
        $basePrompt .= "Plot prices start from ₹4.5 Lakhs. ";

        switch ($role) {
            case 'customer':
            case 'user':
                $prompt = $basePrompt . "The user is a registered customer. ";
                if (!empty($context['booking_count'])) {
                    $prompt .= "They have {$context['booking_count']} active booking(s). ";
                }
                if (!empty($context['total_spent'])) {
                    $spent = number_format($context['total_spent'], 0);
                    $prompt .= "They have spent ₹{$spent} with us. ";
                }
                $prompt .= "Provide personalized property recommendations. ";
                $prompt .= "You can check their booking status, payment history, and suggest new properties. ";
                $prompt .= "Be warm, professional, and treat them as a valued customer.";
                break;

            case 'associate':
            case 'agent':
                $prompt = $basePrompt . "The user is an APS Associate/Agent. ";
                if (!empty($context['lead_count'])) {
                    $prompt .= "They have {$context['lead_count']} lead(s) assigned. ";
                }
                if (!empty($context['total_commission'])) {
                    $comm = number_format($context['total_commission'], 0);
                    $prompt .= "Their total commission is ₹{$comm}. ";
                }
                $prompt .= "Help them with lead management, commission queries, and sales strategies. ";
                $prompt .= "Provide team performance insights and motivate them.";
                break;

            case 'admin':
                $prompt = $basePrompt . "The user is an Admin. ";
                $prompt .= "Today's stats: {$context['today_visitors']} visitors, ";
                $prompt .= "{$context['today_bookings']} bookings, ";
                $prompt .= "{$context['pending_approvals']} pending approvals. ";
                $prompt .= "Help with admin tasks, reports, approvals, and system management. ";
                $prompt .= "Be efficient and provide actionable insights.";
                break;

            default:
            case 'guest':
                $prompt = $basePrompt . "The user is a guest/public visitor. ";
                $prompt .= "Help them discover properties, prices, locations, and services. ";
                $prompt .= "Encourage them to register for personalized assistance. ";
                $prompt .= "Be friendly and informative to convert them into customers.";
                break;
        }

        return $prompt;
    }
}
