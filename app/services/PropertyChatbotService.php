<?php

namespace App\Services;

use App\Traits\ServiceTenantTrait;

/**
 * Property Chatbot Service v3 — Realistic, Conversational, Never Repetitive
 *
 * Key features:
 * - 3-5 response variations per intent (shuffled, never same reply twice)
 * - Live DB data (colonies, plots, prices, EMI)
 * - Conversation context / memory (remembers name, budget, interest)
 * - Small talk handling (kaise ho, tumhara naam, etc.)
 * - Personality: friendly APS Dream Homes sales assistant
 * - Contextual follow-ups that change based on conversation state
 * - No more boring pattern matching
 */
class PropertyChatbotService
{
    use ServiceTenantTrait;

    private $db;
    private $context = [];

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
        $this->loadSessionContext();
    }

    private function loadSessionContext()
    {
        $this->context = $_SESSION['chatbot_context'] ?? [
            'name' => '',
            'interest' => '',
            'budget' => '',
            'location' => '',
            'last_colony' => '',
            'step' => 'greeting',
            'turn' => 0,
            'history' => [],
        ];
    }

    private function saveSessionContext()
    {
        $_SESSION['chatbot_context'] = $this->context;
    }

    /**
     * Pick random element from array
     */
    private function pick(array $arr): string
    {
        return $arr[array_rand($arr)];
    }

    /**
     * Format price in Indian style (1,23,456)
     */
    private function inr(float $amount): string
    {
        return '₹' . number_format($amount);
    }

    /**
     * Get base URL for building links
     */
    private function getBase(): string
    {
        return BASE_URL;
    }

    /**
     * Get Google Maps link for a colony (uses DB map_link, or generates one)
     */
    private function getGoogleMapsLink(array $colony): string
    {
        if (!empty($colony['map_link'])) {
            return $colony['map_link'];
        }
        if (!empty($colony['latitude']) && !empty($colony['longitude'])) {
            return "https://maps.google.com/?q={$colony['latitude']},{$colony['longitude']}";
        }
        $name = urlencode($colony['name'] . ' Gorakhpur');
        return "https://maps.google.com/?q={$name}";
    }

    /**
     * Get colony detail page link
     */
    private function getColonyPageLink(array $colony): string
    {
        $slug = $colony['slug'] ?? '';
        return "{$this->getBase()}/colony/{$slug}";
    }

    /**
     * Get live colony data from database
     */
    private function getLiveProperties(): array
    {
        try {
            return $this->db->query("
                SELECT c.id, c.name, c.slug, c.description, c.map_link, c.latitude, c.longitude,
                       c.starting_price, c.image_path,
                       COUNT(p.id) as total_plots,
                       SUM(CASE WHEN p.status = 'available' THEN 1 ELSE 0 END) as available_plots,
                       MIN(p.total_price) as min_price,
                       MAX(p.total_price) as max_price,
                       MIN(p.area_sqft) as min_area
                FROM colonies c
                LEFT JOIN plots p ON c.id = p.colony_id AND p.is_active = 1
                WHERE c.is_active = 1" . $this->tenantSqlForAlias('c') .
                " GROUP BY c.id, c.name, c.slug, c.description, c.map_link, c.latitude, c.longitude,
                         c.starting_price, c.image_path
                ORDER BY c.name
            ")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getAvailablePlots(string $colonyName = '', int $limit = 5): array
    {
        try {
            $where = "p.status = 'available' AND p.is_active = 1";
            $params = [];
            if ($colonyName) {
                $where .= " AND c.name LIKE ?";
                $params[] = "%{$colonyName}%";
            }
            $params[] = $limit;
            return $this->db->query(
                "SELECT p.plot_number, p.area_sqft, p.total_price, p.frontage_ft, p.depth_ft,
                        c.name as colony_name, c.slug as colony_slug
                 FROM plots p JOIN colonies c ON p.colony_id = c.id
                 WHERE {$where}" . $this->tenantSqlForAlias('p') . " ORDER BY p.total_price ASC LIMIT ?",
                $params
            )->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getPlotCount(): int
    {
        try {
            $row = $this->db->query("SELECT COUNT(*) as cnt FROM plots WHERE status='available' AND is_active=1" . $this->tenantSqlForAlias('plots'))->fetch(\PDO::FETCH_ASSOC);
            return (int)($row['cnt'] ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getColonyCount(): int
    {
        try {
            $row = $this->db->query("SELECT COUNT(*) as cnt FROM colonies WHERE is_active=1" . $this->tenantSqlForAlias('colonies'))->fetch(\PDO::FETCH_ASSOC);
            return (int)($row['cnt'] ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getEmiCalculation(float $price): array
    {
        $down = $price * 0.20;
        $loan = $price - $down;
        $r = 8.5 / 100 / 12;
        $emi5 = round(($loan * $r * pow(1 + $r, 60)) / (pow(1 + $r, 60) - 1));
        $emi10 = round(($loan * $r * pow(1 + $r, 120)) / (pow(1 + $r, 120) - 1));
        return ['price' => $price, 'down_payment' => $down, 'loan' => $loan, 'emi_60' => $emi5, 'emi_120' => $emi10];
    }

    /**
     * Detect language — Hindi if Devanagari chars present, else English
     */
    private function detectLanguage(string $text): string
    {
        return preg_match('/[\x{0900}-\x{097F}]/u', $text) ? 'hi' : 'en';
    }

    /**
     * Main entry — process message and return varied response
     */
    public function processMessage($message): array
    {
        $this->context['turn']++;
        $lower = strtolower(trim($message));
        $lang = $this->detectLanguage($message);

        $intent = $this->detectIntent($lower, $message);
        $reply = $this->generateReply($intent, $message, $lang);
        $quickReplies = $this->getContextualQuickReplies($intent, $lang);

        $this->context['history'][] = ['msg' => $message, 'intent' => $intent];
        if (count($this->context['history']) > 20) {
            $this->context['history'] = array_slice($this->context['history'], -20);
        }

        $this->saveSessionContext();

        return [
            'reply' => $reply,
            'quick_replies' => $quickReplies,
            'intent' => $intent,
        ];
    }

    private function detectIntent(string $lower, string $raw): string
    {
        if ($this->context['step'] === 'ask_name' && strlen($raw) < 30) return 'give_name';
        if ($this->context['step'] === 'ask_budget') return 'give_budget';
        if ($this->context['step'] === 'ask_location') return 'give_location';

        $intents = [
            'smalltalk'  => ['^kaise ho$', '^kaisa hai$', '^kya hal$', '^how are you$', '^how r u$', '^whats up$', '^kya scene$', '^theek$', '^sahi$', '^badhiya$', '^accha$', '^ok$', '^theek hai$', '^sahi hai$', '^acha$', '^namaskar$', '^namaste$', '^sup$', '^yo$'],
            'greeting'   => ['^hi$', '^hello$', '^hey$', '^good morning', '^good evening', '^hii+$', '^helo$', '^hlo$', '^gm$', '^ge$'],
            'price'      => ['price', 'rate', 'cost', 'kitna', 'mehe?nga', 'sasta', 'budget', 'lakh', 'hazaar', 'affordable', 'cheap', 'kii?mat', 'damm'],
            'emi'        => ['emi', 'installment', 'kist', 'monthly', 'payment plan', 'loan', 'finance', 'bank'],
            'booking'    => ['book', 'kharid', 'lena', 'advance', 'token', 'register', 'purchase'],
            'visit'      => ['visit', 'dekhna', 'site', 'aana', 'ghum', 'tour', 'ghumna'],
            'location'   => ['location', 'kahan', 'where', 'address', 'map', 'direction', 'pata'],
            'contact'    => ['call', 'phone', 'number', 'contact', 'whatsapp', 'email', '联系'],
            'amenities'  => ['amenities', 'facility', 'park', 'security', 'road', 'water', 'electricity', 'suvidha'],
            'rera'       => ['rera', 'legal', 'document', 'verified', 'title', 'kagaz'],
            'projects'   => ['project', 'colony', 'scheme', 'nagri', 'suryoday', 'raghunath', 'braj', 'budh', 'awadhpuri', 'ganga'],
            'size'       => ['size', 'area', 'sqft', 'bigha', 'gaj', 'dimension', 'width', 'front', 'depth', 'kitna bada'],
            'thanks'     => ['thanks', 'thank', 'dhanyavaad', 'shukriya'],
            'bye'        => ['bye', 'goodbye', 'tata', 'alvida', 'chal'],
            'help'       => ['help', 'support', 'kya kar', 'options', 'menu'],
            'trust'      => ['trusted', 'bharosa', 'believe', 'reliable', 'achha hai', 'sahi hai'],
            'competitor'  => ['dlf', 'godrej', 'tata', 'housing', '99acres', 'magicbricks', 'other'],
            'layout'     => ['layout', 'map', 'naksha', 'site plan', 'plot map', 'floor plan', 'blueprint'],
            'smalltalk_name' => ['naam kya hai', 'tumhara naam', 'your name', 'kaun ho', 'who are you', 'tum kaun'],
            'smalltalk_weather' => ['mausam', 'weather', 'garmi', 'sardi', 'barish'],
        ];

        foreach ($intents as $intent => $keywords) {
            foreach ($keywords as $kw) {
                if (preg_match("/{$kw}/i", $lower)) return $intent;
            }
        }

        return 'general';
    }

    private function generateReply(string $intent, string $raw, string $lang): string
    {
        $colonies = $this->getLiveProperties();
        $plotCount = $this->getPlotCount();
        $colonyCount = $this->getColonyCount();

        switch ($intent) {

            case 'smalltalk':
                if (in_array($intent, $this->context['history'] && count($this->context['history']) > 0
                    ? [$this->context['history'][count($this->context['history']) - 1]['intent']] : [], true)) {
                    // Repeated small talk
                }
                return $lang === 'hi' ? $this->pick([
                    "Main toh bilkul badhiya hoon! 😄 Aap bataiye, property dhoondh rahe hain?",
                    "Sab first class hai! 🙌 Aapko kis cheez mein help chahiye?",
                    "Accha hoon! 😊 Aap batayein — plot chahiye ya kuch aur?",
                    "Badhiya! Aapka din kaisa ja raha hai? Aur haan, koi property dekhni hai? 🏠",
                    "Theek thak hoon! Lekin aapse baat karke aur accha lag raha hai 😄 Bataiye kya help karun?",
                ]) : $this->pick([
                    "I'm doing great, thanks for asking! 😄 How can I help you today?",
                    "All good! 🙌 What can I help you with — property, pricing, or something else?",
                    "Doing well! 😊 Looking for a plot or just exploring?",
                    "Perfect! Let me know what you need — I'm here to help! 🏠",
                ]);

            case 'smalltalk_name':
                return $lang === 'hi' ? $this->pick([
                    "Mera naam APS Assistant hai! 🤖 Main aapki property dhoondhne mein help karta hoon. Bataiye kya chahiye?",
                    "Main APS Dream Homes ka AI assistant hoon! 🏠 Aapka naam kya hai, bhai?",
                    "APS Dream Homes ki taraf se baat kar raha hoon! 😊 Aapka naam jaanna chahunga.",
                ]) : $this->pick([
                    "I'm APS Dream Homes' AI assistant! 🤖 What's your name?",
                    "I'm the APS property assistant! 😊 How can I help you find your dream plot?",
                ]);

            case 'smalltalk_weather':
                return $lang === 'hi' ? $this->pick([
                    "Gorakhpur ka mausam toh hota hai! ☀️ Lekin plot dhoondhne ka mausam hamesha accha hai 😄",
                    "Mausam toh theek hai, lekin aapko plot ka mausam dikhata hoon! 🏘️",
                    "Barish ho ya dhoop, site visit ka plan banaiye! 📅",
                ]) : $this->pick([
                    "Weather's nice! Perfect time for a site visit though 😄",
                    "It's good! But let me help you find the perfect plot instead ☀️",
                ]);

            case 'greeting':
                if ($this->context['turn'] <= 1 || $this->context['step'] === 'greeting') {
                    $this->context['step'] = 'ask_name';
                    $namePart = $this->context['name'] ? " {$this->context['name']}" : '';
                    return $lang === 'hi' ? $this->pick([
                        "Namaste{$namePart}! 🙏 Main APS Dream Homes ka assistant hoon.\n\n{$plotCount} plots available hain {$colonyCount} colonies mein. Bataiye, aapka naam kya hai?",
                        "Hello{$namePart}! 🙏 Welcome to APS Dream Homes!\n\nMujhe bataiye — aapko kya chahiye? Plot, price, ya site visit?\n\nHaan, pehle aapka naam bata dijiye 😊",
                        "Namaskar{$namePart}! 🙏 APS Dream Homes mein aapka swagat hai.\n\nMain aapki property dhoondhne mein madad karunga. Aapka naam?",
                    ]) : $this->pick([
                        "Hello! 🙏 Welcome to APS Dream Homes!\n\n{$plotCount} plots available across {$colonyCount} colonies. What's your name?",
                        "Hey there! 👋 I'm your APS property assistant.\n\nWhat brings you here — looking for a plot, checking prices, or just browsing?",
                    ]);
                }
                $nm = $this->context['name'] ?: 'ji';
                return $lang === 'hi' ? $this->pick([
                    "Haan {$nm}, bataiye aur kya jaanna hai? 😊",
                    "Ji {$nm}, aur kuch poochna hai? Main hoon na! 😄",
                    "Of course! Bataiye kya help karun? 🏠",
                ]) : $this->pick([
                    "Sure! What else would you like to know? 😊",
                    "Of course! Ask me anything about properties! 🏠",
                    "Go ahead! I'm all ears 😄",
                ]);

            case 'give_name':
                $name = htmlspecialchars(ucfirst(trim($raw)));
                $this->context['name'] = $name;
                $this->context['step'] = 'ask_interest';
                return $lang === 'hi' ? $this->pick([
                    "Bahut accha {$name}! 😊 Ab bataiye, aapko kya chahiye?\n\n• 🏘️ Plot dekhna hai\n• 💰 Price jaanna hai\n• 📅 Site visit karna hai\n• 💳 EMI ka plan hai\n\nBol dijiye!",
                    "Nice, {$name} ji! 🙏 APS Dream Homes mein aapka swagat hai.\n\nAap kya dhoondh rahe hain?\n• Plot kharidna hai?\n• Price check karna hai?\n• Site visit plan karna hai?\n\nBataiye!",
                    "Accha {$name}! 😊 Chaliye baat karte hain.\n\nProperty, price, ya booking — kya jaanna hai aapko?",
                ]) : $this->pick([
                    "Nice to meet you, {$name}! 😊\n\nWhat are you looking for?\n• 🏘️ Properties\n• 💰 Pricing\n• 📅 Site visit\n• 💳 EMI options\n\nTell me!",
                    "Hey {$name}! 👋 Welcome!\n\nHow can I help? Plot details, pricing, or site visit?",
                ]);

            case 'price':
                $this->context['interest'] = 'price';
                $reply = $lang === 'hi' ? $this->pick([
                    "💰 Chaliye, live prices dikhata hoon!\n\n",
                    "💰 Abhi current rates batata hoon — ye real-time data hai!\n\n",
                    "💰 Aaj ka price list — sabse fresh data hai!\n\n",
                ]) : $this->pick([
                    "💰 Here are the current live prices!\n\n",
                    "💰 Let me show you real-time pricing!\n\n",
                ]);

                foreach ($colonies as $c) {
                    if ($c['available_plots'] > 0) {
                        $reply .= "🏘️ **{$c['name']}**\n";
                        $reply .= "   {$c['available_plots']} plots available\n";
                        $reply .= "   " . ($lang === 'hi' ? "Range" : "Range") . ": " . $this->inr($c['min_price']) . " — " . $this->inr($c['max_price']) . "\n";
                        $reply .= "   " . ($lang === 'hi' ? "Shuru" : "Starts") . ": " . number_format($c['min_area']) . " sqft\n";
                        $reply .= "   📍 " . ($lang === 'hi' ? "Location dekhein" : "View on map") . ": " . $this->getGoogleMapsLink($c) . "\n";
                        $reply .= "   📄 " . ($lang === 'hi' ? "Project page" : "Project page") . ": " . $this->getColonyPageLink($c) . "\n\n";
                    }
                }

                $reply .= $lang === 'hi' ? $this->pick([
                    "📊 EMI bhi available hai — ₹21,000 se token shuru!\nKya aapko kisi specific area ka price chahiye?",
                    "Sabse sasta plot bhi hai, sabse bada bhi! 🎯\nAapka budget kitna hai?",
                    "Prices mein 20% tak discount mil sakta hai early booking pe! 💸\nKonsa area pasand hai?",
                ]) : $this->pick([
                    "EMI available from ₹21,000 token! 💳\nWant to know about a specific area?",
                    "Prices start from just ₹" . number_format(min(array_column(array_filter($colonies, fn($c) => $c['min_price'] > 0), 'min_price'))) . "! 🎯\nWhat's your budget?",
                ]);
                return $reply;

            case 'emi':
                $this->context['interest'] = 'emi';
                $plots = $this->getAvailablePlots('', 3);
                if (empty($plots)) {
                    return $lang === 'hi'
                        ? "Abhi plots check ho rahe hain... Thoda wait karein! 📞 Ya call karein: **+91 92771 21112**"
                        : "Checking available plots... Please wait! 📞 Or call: **+91 92771 21112**";
                }
                $reply = $lang === 'hi' ? $this->pick([
                    "📊 **EMI Calculator** — 8.5% interest pe:\n\n",
                    "💳 **Monthly Payment Plan** — bahut affordable hai!\n\n",
                    "📊 **EMI Details** — aasan kiston mein plot kharidiye:\n\n",
                ]) : "📊 **EMI Calculator** (8.5% interest):\n\n";

                foreach ($plots as $p) {
                    $emi = $this->getEmiCalculation($p['total_price']);
                    $reply .= "🏘️ **{$p['plot_number']}** — {$p['colony_name']}\n";
                    $reply .= "   " . $this->inr($emi['price']) . "\n";
                    $reply .= "   " . ($lang === 'hi' ? "Down payment" : "Down payment") . ": " . $this->inr($emi['down_payment']) . "\n";
                    $reply .= "   EMI: " . $this->inr($emi['emi_60']) . "/mo (5yr) | " . $this->inr($emi['emi_120']) . "/mo (10yr)\n\n";
                }

                $reply .= $lang === 'hi' ? $this->pick([
                    "Konsa plot pasand aaya? Booking karein ya site visit karein! 😊",
                    "Budget ke hisaab se best plot bataun? Ya koi specific chahiye? 🤔",
                    "EMI flexible hai — bank se baat karke aur kam karwa sakte hain! 💪",
                ]) : "Want to proceed with booking? Or need more options? 😊";
                return $reply;

            case 'projects':
                $reply = $lang === 'hi' ? $this->pick([
                    "🏘️ **Saare Projects — APS Dream Homes:**\n\n",
                    "🏘️ **Humari colonies dekhiye:**\n\n",
                    "🏘️ **Ye hain hamare active projects:**\n\n",
                ]) : "🏘️ **All Projects — APS Dream Homes:**\n\n";

                foreach ($colonies as $c) {
                    $status = $c['available_plots'] > 0
                        ? "✅ {$c['available_plots']} plots available"
                        : "❌ Sold Out";
                    $reply .= "**{$c['name']}** — {$status}\n";
                    if ($c['description']) {
                        $reply .= "   " . strip_tags(substr($c['description'], 0, 120)) . "\n";
                    }
                    if ($c['starting_price'] > 0) {
                        $reply .= "   💰 " . ($lang === 'hi' ? "Shuruat" : "Starting") . ": " . $this->inr($c['starting_price']) . "\n";
                    }
                    $reply .= "   📍 " . $this->getGoogleMapsLink($c) . "\n";
                    $reply .= "   📄 " . $this->getColonyPageLink($c) . "\n\n";
                }

                $reply .= $lang === 'hi' ? $this->pick([
                    "Konsa project dekhna hai? Main detail mein bata sakta hoon! 😊",
                    "Sabmein se koi ek choose karein, main baaki ka kaam sambhalunga! 💪",
                    "Kisi ka naam bol dijiye — puri info mil jayegi!",
                ]) : "Which project interests you? I can share detailed info! 😊";
                $this->context['step'] = 'ask_location';
                return $reply;

            case 'visit':
                $reply = $lang === 'hi' ? $this->pick([
                    "🗓️ **FREE Site Visit!**\n\nKis colony mein jaana hai?\n\n",
                    "📅 **Chaliye dekhte hain!** Site visit bilkul free hai!\n\n",
                    "🗓️ **Ghum ke dekhiye!** Konsi colony mein aana hai?\n\n",
                ]) : "🗓️ **FREE Site Visit!**\n\nWhich colony?\n\n";

                foreach ($colonies as $c) {
                    $avail = $c['available_plots'] ?? 0;
                    $mapsLink = $this->getGoogleMapsLink($c);
                    $reply .= "• **{$c['name']}**" . ($avail > 0 ? " ({$avail} plots)" : "") . "\n";
                    $reply .= "  📍 " . ($lang === 'hi' ? "Google Maps pe dekhein" : "View on Google Maps") . ": " . $mapsLink . "\n";
                }

                $reply .= $lang === 'hi' ? $this->pick([
                    "\n📞 Ya call karein: **+91 92771 21112**\n📅 Mon-Sat, 9 AM — 7 PM\n\nKonsi colony? Bol dijiye!",
                    "\n🕐 Visit timing: Mon-Sat, 9 AM — 7 PM\n📞 **+91 92771 21112**\n\nAap bataiye kahan aana hai!",
                    "\nGadi bhej denge pick ke liye! 🚗\nCall karein: **+91 92771 21112**\n\nKaunsi colony?",
                ]) : "\n📞 Call: **+91 92771 21112** | Mon-Sat 9AM-7PM\n\nWhich colony?";
                return $reply;

            case 'location':
                $reply = $lang === 'hi' ? $this->pick([
                    "📍 **Locations — APS Dream Homes:**\n\n",
                    "📍 **Humari colonies kahan hain:**\n\n",
                ]) : "📍 **Our Locations:**\n\n";

                foreach ($colonies as $c) {
                    $reply .= "🏘️ **{$c['name']}** — Gorakhpur, UP\n";
                    $reply .= "   📍 " . ($lang === 'hi' ? "Google Maps" : "Google Maps") . ": " . $this->getGoogleMapsLink($c) . "\n";
                    $reply .= "   📄 " . ($lang === 'hi' ? "Project page" : "Project page") . ": " . $this->getColonyPageLink($c) . "\n\n";
                }

                $reply .= $lang === 'hi' ? $this->pick([
                    "\n🏢 **Office:** Virat Bhawan, Kunraghat, Gorakhpur 273008\n\n📞 Directions chahiye? Call karein: **+91 92771 21112**",
                    "\n🏢 Kunraghat, Gorakhpur mein hai hamara office.\n\n📍 Office map: https://maps.google.com/?q=Virat+Bhawan+Kunraghat+Gorakhpur+273008\n\n📞 **+91 92771 21112**",
                ]) : "\n📍 Office: Virat Bhawan, Kunraghat, Gorakhpur 273008\n\n📍 https://maps.google.com/?q=Virat+Bhawan+Kunraghat+Gorakhpur+273008\n\n📞 +91 92771 21112 for directions";
                return $reply;

            case 'contact':
                return $lang === 'hi' ? $this->pick([
                    "📞 **Contact APS Dream Homes:**\n\n"
                    . "📱 **+91 92771 21112** (Primary)\n"
                    . "📱 **+91 70074 44842** (Alt)\n"
                    . "📧 apsdreamhome@gmail.com\n"
                    . "💬 WhatsApp: [Click karein](https://wa.me/919277121112)\n\n"
                    . "🕐 Mon-Sat, 9 AM — 7 PM\n📍 Kunraghat, Gorakhpur",

                    "📞 **Phone & WhatsApp:**\n\n"
                    . "📱 **+91 92771 21112** — Seedha call karein!\n"
                    . "📱 **+91 70074 44842** — Dusra number\n"
                    . "💬 WhatsApp karein: [Yahan click karein](https://wa.me/919277121112)\n"
                    . "📧 apsdreamhome@gmail.com\n\n"
                    . "9 AM se 7 PM tak available hain! 🙌",
                ]) : "📞 **Contact:**\n\n📱 **+91 92771 21112**\n📱 Alt: **+91 70074 44842**\n📧 apsdreamhome@gmail.com\n💬 [WhatsApp](https://wa.me/919277121112)\n\n🕐 Mon-Sat 9AM-7PM";

            case 'amenities':
                return $lang === 'hi' ? $this->pick([
                    "🏗️ **Saari suvidhaein:**\n\n"
                    . "🔒 24/7 Security + CCTV\n"
                    . "🛣️ Wide Roads (30-40 ft)\n"
                    . "⚡ Underground Electricity\n"
                    . "💧 24/7 Water Supply\n"
                    . "🌳 Green Parks\n"
                    . "🏠 Gated Community\n"
                    . "🌧️ Rain Water Harvesting\n"
                    . "🏫 School/Hospital ke paas\n"
                    . "🛣️ Main Road se connected\n\n"
                    . "Koi specific cheez chahiye? 🤔",

                    "🏗️ **Facilities:**\n\n"
                    . "✅ 24/7 Security — full safety\n"
                    . "✅ Wide Roads — traffic free\n"
                    . "✅ 24/7 Water + Electricity\n"
                    . "✅ Parks — bacchon ke liye\n"
                    . "✅ Gated Society — privacy\n"
                    . "✅ Near School/Hospital\n"
                    . "✅ Main Road connectivity\n\n"
                    . "Aur kuch jaanna hai? 😊",
                ]) : "🏗️ **Amenities:**\n\n✅ 24/7 Security + CCTV\n✅ Wide Roads\n✅ Underground Electricity\n✅ 24/7 Water\n✅ Green Parks\n✅ Gated Community\n✅ Rain Water Harvesting\n\nAny specific facility? 🤔";

            case 'rera':
                return $lang === 'hi' ? $this->pick([
                    "✅ **RERA Verified Company**\n\n"
                    . "📜 CIN: U70109UP2022PTC163047\n"
                    . "🏛️ ROC Kanpur registered\n"
                    . "✅ All properties legally verified\n"
                    . "✅ Clear title — no disputes\n"
                    . "✅ Complete documentation handled\n\n"
                    . "Aap 100% bharosa kar sakte hain! 😊",

                    "✅ **Full Legal Compliance!**\n\n"
                    . "📜 Registered: CIN U70109UP2022PTC163047\n"
                    . "✅ ROC Kanpur verified\n"
                    . "✅ Clear land titles\n"
                    . "✅ All paperwork hamare hisse ka\n\n"
                    . "Koi doubt ho toh poochh lijiye! 🤝",
                ]) : "✅ **RERA Verified**\n\n📜 CIN: U70109UP2022PTC163047\n🏛️ ROC Kanpur registered\n✅ Legally verified properties\n✅ Clear titles\n\n100% trustworthy! 😊";

            case 'size':
                $plots = $this->getAvailablePlots('', 4);
                $reply = $lang === 'hi' ? $this->pick([
                    "📐 **Available Plot Sizes:**\n\n",
                    "📐 **Kitna bada chahiye?** Dekhiye:\n\n",
                ]) : "📐 **Available Sizes:**\n\n";

                if (!empty($plots)) {
                    foreach ($plots as $p) {
                        $dims = $p['front_ft'] && $p['depth_ft'] ? " ({$p['front_ft']}×{$p['depth_ft']} ft)" : '';
                        $reply .= "• **{$p['plot_number']}** — {$p['area_sqft']} sqft{$dims} — " . $this->inr($p['total_price']) . " ({$p['colony_name']})\n";
                    }
                } else {
                    $reply .= "Abhi available plots load ho rahe hain...\n";
                }

                $reply .= $lang === 'hi' ? $this->pick([
                    "\nKoi specific size chahiye? 1000, 1200, ya koi aur? 🤔",
                    "\nBada ya chhota — sab milta hai! Aapka budget kitna hai? 💰",
                ]) : "\nSpecific size needed? Tell me your preference! 🤔";
                return $reply;

            case 'booking':
                return $lang === 'hi' ? $this->pick([
                    "📋 **Booking Process — Bahut Simple Hai!**\n\n"
                    . "1️⃣ FREE site visit karein\n"
                    . "2️⃣ Plot choose karein\n"
                    . "3️⃣ Token ₹21,000 se book karein\n"
                    . "4️⃣ Documents verify honge\n"
                    . "5️⃣ Agreement sign hoga\n"
                    . "6️⃣ Possession mil jayega!\n\n"
                    . "📞 Abhi call karein: **+91 92771 21112**\n"
                    . "💬 WhatsApp: [Click](https://wa.me/919277121112?text=Hi, I want to book a plot)\n\n"
                    . "Konsa plot chahiye? 🏘️",

                    "📋 **6 Simple Steps:**\n\n"
                    . "1️⃣ FREE visit\n"
                    . "2️⃣ Plot choose\n"
                    . "3️⃣ Token ₹21,000\n"
                    . "4️⃣ Documents\n"
                    . "5️⃣ Agreement\n"
                    . "6️⃣ Ghar milega!\n\n"
                    . "📞 **+91 92771 21112** — Abhi baat karein!\n"
                    . "💰 Token se plot lock ho jayega! 🔒",
                ]) : "📋 **Booking:**\n\n1️⃣ FREE visit\n2️⃣ Choose plot\n3️⃣ Token ₹21,000\n4️⃣ Docs verified\n5️⃣ Agreement\n6️⃣ Possession!\n\n📞 Call: **+91 92771 21112**";

            case 'trust':
                return $lang === 'hi' ? $this->pick([
                    "🙏 **Bharosa rakhiye!**\n\n"
                    . "APS Dream Homes Private Limited — registered company hai.\n"
                    . "📜 CIN: U70109UP2022PTC163047\n"
                    . "🏛️ Gorakhpur mein 8 colonies already delivered\n"
                    . "👥 100+ khush customers\n"
                    . "✅ RERA compliant\n\n"
                    . "Aap aaram se soch sakte hain! 😊",

                    "💯 **100% Trustworthy!**\n\n"
                    . "• Registered company (ROC Kanpur)\n"
                    . "• 8 active colonies in Gorakhpur\n"
                    . "• Legal clear titles\n"
                    . "• Transparent pricing — koi hidden charge nahi\n\n"
                    . "Baaki aap dekh lijiye — site visit pe aaiye! 🏠",
                ]) : "💯 **100% Trusted!**\n\nRegistered company, RERA compliant, 8 colonies delivered. Come visit us! 😊";

            case 'competitor':
                return $lang === 'hi' ? $this->pick([
                    "Dusre builders se compare karna acchi baat hai! 👍\n\nLekin APS Dream Homes mein kuch khaas hai:\n"
                    . "✅ Gorakhpur ka local expertise\n"
                    . "✅ Direct builder — koi broker nahi\n"
                    . "✅ Flexible EMI options\n"
                    . "✅ Site visit kabhi bhi kar sakte hain\n\n"
                    . "Aap aake dekh lijiye, difference samajh aa jayega! 😊",

                    "Accha, aap dusron se bhi pooch rahe hain? Smart hai! 🧠\n\n"
                    . "APS ka advantage:\n"
                    . "• Local team — Gorakhpur jaanti hai\n"
                    . "• Price kam, quality zyada\n"
                    . "• Transparency — kuch chhupa nahi\n\n"
                    . "Ek baar visit toh kariye! 🏠",
                ]) : "Great you're comparing! APS offers local expertise, transparent pricing, and flexible EMI. Visit us to see the difference! 😊";

            case 'layout':
                $this->context['interest'] = 'layout';
                $reply = $lang === 'hi' ? $this->pick([
                    "🗺️ **Colony Layout Maps — APS Dream Homes:**\n\n",
                    "🗺️ **Ye hain hamare colony layouts:**\n\n",
                    "📋 **Site Plans dekhiye:**\n\n",
                ]) : "🗺️ **Colony Layout Maps:**\n\n";

                foreach ($colonies as $c) {
                    if ($c['available_plots'] > 0) {
                        $mapsLink = $this->getGoogleMapsLink($c);
                        $pageLink = $this->getColonyPageLink($c);
                        $reply .= "🏘️ **{$c['name']}**\n";
                        $reply .= "   📍 " . ($lang === 'hi' ? "Location map" : "Location map") . ": " . $mapsLink . "\n";
                        $reply .= "   📄 " . ($lang === 'hi' ? "Project page (layout dekhein)" : "Project page (view layout)") . ": " . $pageLink . "\n\n";
                    }
                }

                $reply .= $lang === 'hi' ? $this->pick([
                    "Konsi colony ka layout dekhna hai? Ya detailed plot map chahiye? 🗺️\n\n📞 **+91 92771 21112** par call karein — PDF layout bhej denge!",
                    "Har colony ka detailed layout hamare project pages pe mil jayega! 📄\nYa call karein: **+91 92771 21112**",
                ]) : "View detailed layout on each project page! Or call **+91 92771 21112** for PDF layouts.";
                return $reply;

            case 'thanks':
                $nameSuffix = $this->context['name'] ? ', ' . $this->context['name'] : '';
                return $lang === 'hi' ? $this->pick([
                    "🙏 Aapka swagat hai{$nameSuffix}! Aur kuch puchna ho toh bataiye.\n\n🏠 APS Dream Homes — Aapka sapno ka ghar!",
                    "🤝 Koi baat nahi{$nameSuffix}! Main yahan hoon hamesha.\n\nAur koi sawaal hai? 😊",
                    "😊 Aapke liye toh hamesha hain{$nameSuffix}!\n\nKabhi bhi aaiye, bulaiye! 🏠",
                ]) : "🙏 You're welcome{$nameSuffix}! Ask me anytime.\n\n🏠 APS Dream Homes — Your dream home!";

            case 'bye':
                $nameSuffix = $this->context['name'] ? ', ' . $this->context['name'] : '';
                return $lang === 'hi' ? $this->pick([
                    "Alvida{$nameSuffix}! 🙏 Aapka din shubh ho!\n📞 Jab bhi zaroorat ho: +91 92771 21112",
                    "Bye bye{$nameSuffix}! 🙏 Phir milte hain!\nAur haan — sapno ka ghar APS ke saath! 🏠",
                    "Take care{$nameSuffix}! 🙏 Site visit ka plan banaiyega!\n📞 +91 92771 21112",
                ]) : "Goodbye{$nameSuffix}! 🙏 Have a great day!\n📞 +91 92771 21112";

            case 'give_budget':
                $this->context['budget'] = $raw;
                $this->context['step'] = 'active';
                $plots = $this->getAvailablePlots('', 5);
                $reply = $lang === 'hi' ? $this->pick([
                    "Accha, budget {$raw}! Chaliye dekhte hain kya milta hai:\n\n",
                    "{$raw} mein bahut acche options hain! Dekhiye:\n\n",
                    "Budget {$raw} — smart choice! Ye plots dekhiye:\n\n",
                ]) : "Budget {$raw}! Here are your options:\n\n";

                foreach ($plots as $p) {
                    $reply .= "🏘️ **{$p['plot_number']}** — {$p['colony_name']}\n";
                    $reply .= "   " . $this->inr($p['total_price']) . " | {$p['area_sqft']} sqft\n\n";
                }

                $reply .= $lang === 'hi' ? $this->pick([
                    "Konsa pasand aaya? Site visit karna hai? 😊",
                    "Inmein se best kaunsa hai? Main detail mein bata sakta hoon! 🏘️",
                    "Budget se zyada wala bhi dekhna hai? Ya inmein se choose karein! 💰",
                ]) : "Which one? Want a site visit? 😊";
                return $reply;

            case 'give_location':
                $this->context['location'] = $raw;
                $this->context['step'] = 'active';
                $plots = $this->getAvailablePlots($raw, 5);
                if (!empty($plots)) {
                    $reply = $lang === 'hi' ? $this->pick([
                        "🏘️ **{$raw}** mein yeh plots available hain:\n\n",
                        "**{$raw}** — accha choice! Dekhiye plots:\n\n",
                    ]) : "🏘️ Available plots in {$raw}:\n\n";

                    foreach ($plots as $p) {
                        $reply .= "• **{$p['plot_number']}** — " . $this->inr($p['total_price']) . " ({$p['area_sqft']} sqft)\n";
                    }
                    $reply .= $lang === 'hi' ? $this->pick([
                        "\nInmein se koi pasand aaya? 😊",
                        "\nBest kaunsa laga? Site visit karein! 📅",
                    ]) : "\nInterested in any? Let's schedule a visit! 📅";
                } else {
                    $reply = $lang === 'hi' ? $this->pick([
                        "Hmm, {$raw} mein abhi available plots limited hain.\n📞 Call karein for latest info: **+91 92771 21112**",
                        " {$raw} mein abhi stock kam hai.\nLekin hamari aur bhi colonies hain! 🏘️\nCall: **+91 92771 21112**",
                    ]) : "Limited availability in {$raw}. Call for latest: **+91 92771 21112**";
                }
                return $reply;

            case 'help':
                return $lang === 'hi' ? $this->pick([
                    "🤔 **Main ye sab kar sakta hoon:**\n\n"
                    . "💰 \"Kitne ka hai plot?\" — Live price\n"
                    . "📅 \"Site visit karna hai\" — FREE visit plan\n"
                    . "📐 \"Kitna bada hai?\" — Plot sizes\n"
                    . "💳 \"EMI kya hai?\" — Monthly plan\n"
                    . "🏘️ \"Kya projects hain?\" — All colonies\n"
                    . "🗺️ \"Layout dikhao\" — Colony maps\n"
                    . "📞 \"Phone number\" — Contact details\n"
                    . "✅ \"Legal hai?\" — RERA info\n\n"
                    . "Ya seedha call karein: **+91 92771 21112**",

                    "📋 **Quick Options:**\n\n"
                    . "1. Price dekho 💰\n"
                    . "2. Site visit plan karo 📅\n"
                    . "3. EMI check karo 💳\n"
                    . "4. Projects jaano 🏘️\n"
                    . "5. Layout dekho 🗺️\n"
                    . "6. Call karo 📞 **+91 92771 21112**\n\n"
                    . "Bas bol dijiye, main handle kar lunga! 😊",
                ]) : "🤔 **I can help with:**\n\n• 💰 Prices & EMI\n• 📅 Site visits\n• 📐 Plot sizes\n• 🏘️ Projects\n• 🗺️ Layout maps\n• 📞 Contact info\n\nCall: **+91 92771 21112**";

            default:
                $name = $this->context['name'] ? $this->context['name'] . ' ' : '';
                $interest = $this->context['interest'];
                if ($interest === 'price') {
                    return $lang === 'hi'
                        ? " {$name}Price ke baare mein aur kuch jaanna hai? Ya kisi specific colony ka rate chahiye? 🏘️"
                        : " Want more pricing details? Or a specific colony's rates? 🏘️";
                }
                if ($interest === 'emi') {
                    return $lang === 'hi'
                        ? "{$name}EMI ke baare mein aur kuch? Ya booking ka plan banayein? 💳"
                        : "More about EMI? Or shall we plan the booking? 💳";
                }
                return $lang === 'hi' ? $this->pick([
                    "🤔 {$name}Samajh nahi aaya, lekin main madad karna chahta hoon!\n\n"
                    . "Ye pooch sakte hain:\n"
                    . "• 💰 \"Kitne ka hai plot?\"\n"
                    . "• 📅 \"Site visit karna hai\"\n"
                    . "• 📐 \"Kitna bada hai?\"\n"
                    . "• 💳 \"EMI kya hai?\"\n"
                    . "• 🗺️ \"Layout dikhao\"\n"
                    . "• 📞 \"Phone number\"\n\n"
                    . "Ya call karein: **+91 92771 21112**",

                    "🤔 {$name}Thoda aur bataiye — kya dhoondh rahe hain?\n\n"
                    . "Property, price, booking, ya kuch aur? Main sab bata sakta hoon! 😊\n\n"
                    . "📞 **+91 92771 21112**",
                ]) : "🤔 I want to help! Ask about:\n\n• 💰 Prices\n• 📅 Site visits\n• 📐 Sizes\n• 💳 EMI\n• 🗺️ Layouts\n• 📞 Contact\n\nOr call: **+91 92771 21112**";
        }
    }

    private function getContextualQuickReplies(string $intent, string $lang): array
    {
        $name = $this->context['name'];
        $interest = $this->context['interest'];

        $base = $lang === 'hi' ? [
            'greeting'  => ['Property dekhni hai', 'Price dekho', 'Layout dekho', 'Site visit karna hai', 'Contact karo'],
            'price'     => ['EMI calculator', 'Budget plot batao', 'Site visit karo'],
            'emi'       => ['Booking karo', 'Payment plan', 'Site visit'],
            'visit'     => ['Suryoday nagar', 'Raghunath nagri', 'Braj radha', 'Call karo'],
            'projects'  => ['Price dekho', 'Layout dekho', 'Available plots', 'Site visit'],
            'layout'    => ['Suryoday layout', 'Raghunath layout', 'Braj Radha layout', 'Price dekho'],
            'booking'   => ['Site visit karo', 'Call karo', 'WhatsApp karo'],
            'contact'   => ['WhatsApp', 'Site visit karo', 'Back'],
            'smalltalk' => ['Property dekhni hai', 'Price dekho', 'Layout dekho', 'Site visit karo'],
        ] : [
            'greeting'  => ['View Properties', 'Check Prices', 'Layout Maps', 'Site Visit', 'Contact Us'],
            'price'     => ['EMI Calculator', 'Show Budget Plots', 'Book Visit'],
            'emi'       => ['Book Now', 'Payment Plan', 'Site Visit'],
            'visit'     => ['Suryoday Nagar', 'Raghunath', 'Braj Radha', 'Call Now'],
            'projects'  => ['Prices', 'Layout Maps', 'Available Plots', 'Site Visit'],
            'layout'    => ['Suryoday Layout', 'Raghunath Layout', 'Braj Radha Layout', 'Prices'],
            'booking'   => ['Book Visit', 'Call Us', 'WhatsApp'],
            'contact'   => ['WhatsApp', 'Book Visit', 'Back'],
            'smalltalk' => ['View Properties', 'Check Prices', 'Layout Maps', 'Site Visit'],
        ];

        return $base[$intent] ?? ($lang === 'hi'
            ? ['Property dekhni hai', 'Price dekho', 'Layout dekho', 'Site visit karo', 'Contact karo']
            : ['View Properties', 'Check Prices', 'Layout Maps', 'Site Visit', 'Contact Us']
        );
    }

    public function getQuickReplies(): array
    {
        return ['Property dekhni hai', 'Price dekho', 'Site visit karo', 'Contact karo'];
    }

    public function saveConversation($userId, $message, $response)
    {
        try {
            $tid = $this->tenantId();
            $columns = "user_id, user_message, bot_response, intent, created_at";
            $values = "?, ?, ?, ?, NOW()";
            $params = [$userId, $message, $response['reply'], $response['intent']];
            if ($tid > 1) {
                $columns .= ", tenant_id";
                $values .= ", ?";
                $params[] = $tid;
            }
            $this->db->execute(
                "INSERT INTO chatbot_conversations ($columns) VALUES ($values)",
                $params
            );
        } catch (\Exception $e) {
            error_log("Chatbot save error: " . $e->getMessage());
        }
    }
}
