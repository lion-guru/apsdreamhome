<?php

namespace App\Services\Voice;

/**
 * AI Voice Pipeline — Real-time AI conversation for phone calls
 * 
 * Stack:
 * - STT: Whisper (local)
 * - LLM: Ollama (local/free) → Gemini (PRO quality) → Rule-based fallback
 * - TTS: Google TTS / eSpeak / Coqui TTS (free)
 * 
 * Fallback chain: Ollama → Gemini → Local fallback
 * - Ollama: free, fast, local — used for routine responses
 * - Gemini: PRO member quality — used when Ollama fails or for complex queries
 * - Fallback: rule-based Hindi responses — always works
 */
class AIVoicePipeline
{
    use \App\Traits\ServiceTenantTrait;

    private $ollamaUrl;
    private $ollamaModel;
    private $whisperUrl;
    private $ttsEngine;
    private $geminiApiKey;
    private $geminiUrl;
    private $geminiModel;
    private $knowledgeBase;
    private $db;
    private $groqApiKey;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
        $this->ollamaUrl = getenv('OLLAMA_URL') ?: 'http://localhost:11434';
        $this->ollamaModel = getenv('OLLAMA_MODEL') ?: 'llama3.2:3b';
        $this->whisperUrl = getenv('WHISPER_URL') ?: 'http://localhost:8080';
        $this->ttsEngine = getenv('TTS_ENGINE') ?: 'google';
        $this->geminiApiKey = getenv('GEMINI_API_KEY') ?: '';
        $this->geminiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';
        $this->geminiModel = 'gemini-2.5-flash';
        $this->groqApiKey = getenv('GROQ_API_KEY') ?: '';
        // Cloud-first: pull Groq key + voice engine prefs from ai_settings when env is empty
        if ($this->ttsEngine === 'google') {
            try {
                $row = $this->db->fetch("SELECT groq_api_key, settings FROM ai_settings WHERE id = 1");
                $this->groqApiKey = trim((string)($row['groq_api_key'] ?? ''));
                $cfg = json_decode((string)($row['settings'] ?? '{}'), true) ?: [];
                if (!empty($cfg['tts_engine']) && in_array($cfg['tts_engine'], ['google', 'groq', 'espeak', 'ollama'], true)) {
                    $this->ttsEngine = $cfg['tts_engine'];
                }
            } catch (\Throwable $e) {
                error_log("AIVoicePipeline settings load failed: " . $e->getMessage());
            }
        }
        if ($this->groqApiKey === '') {
            try {
                $row = $this->db->fetch("SELECT groq_api_key FROM ai_settings WHERE id = 1");
                $this->groqApiKey = trim((string)($row['groq_api_key'] ?? ''));
            } catch (\Throwable $e) {
                error_log("AIVoicePipeline groq key load failed: " . $e->getMessage());
            }
        }
        $this->knowledgeBase = $this->loadKnowledgeBase();
    }

    /**
     * Process a conversation turn — given customer audio/text, return AI response
     */
    public function processTurn(int $sessionId, string $userInput, string $inputType = 'text'): array
    {
        // Get conversation history
        $history = $this->getConversationHistory($sessionId);
        
        // Build context prompt
        $context = $this->buildContext($history, $userInput);

        // Enrich voice agent with real-time inventory (colonies, available plots)
        $liveInventory = $this->getLiveInventoryContext();
        if (!empty($liveInventory)) {
            $context .= "\n\nREAL-TIME AVAILABLE INVENTORY (from database — use these exact facts):\n"
                . $liveInventory
                . "\nWhen the customer asks about colonies, plots, availability, or prices, cite this live data briefly.";
        }

        // Get LLM response
        $response = $this->callLLM($context, true);
        
        if (!$response['success']) {
            return [
                'success' => false,
                'response_text' => $this->getFallbackResponse($userInput),
                'intent' => 'fallback',
                'engine' => 'fallback',
            ];
        }

        // Detect intent from user input
        $intent = $this->detectIntent($userInput);

        // Guided booking / site-visit flow: capture details and create a CRM
        // lead (+ optional site visit) so the call converts, not just informs.
        if (in_array($intent, ['booking', 'site_visit'], true)) {
            $bookingReply = $this->handleBookingIntent($sessionId, $userInput, $intent);
            if ($bookingReply !== null) {
                $audioUrl = ($inputType === 'audio') ? $this->textToSpeech($bookingReply) : null;
                $this->saveTurn($sessionId, $userInput, $bookingReply, $intent, $sentiment ?? 'neutral');
                return [
                    'success' => true,
                    'response_text' => $bookingReply,
                    'intent' => $intent,
                    'sentiment' => $sentiment ?? 'neutral',
                    'audio_url' => $audioUrl,
                    'engine' => 'voice_booking',
                    'confidence' => 0.9,
                ];
            }
        }

        // Detect sentiment
        $sentiment = $this->detectSentiment($userInput);
        
        // Generate TTS audio if needed
        $audioUrl = null;
        if ($inputType === 'audio') {
            $audioUrl = $this->textToSpeech($response['text']);
        }

        // Save to conversation history
        $this->saveTurn($sessionId, $userInput, $response['text'], $intent, $sentiment);

        return [
            'success' => true,
            'response_text' => $response['text'],
            'intent' => $intent,
            'sentiment' => $sentiment,
            'audio_url' => $audioUrl,
            'engine' => $response['engine'] ?? 'ollama',
            'confidence' => $response['confidence'] ?? 0.8,
        ];
    }

    /**
     * Guided voice booking flow: collect caller name + phone, create a CRM lead
     * and a site-visit record. Returns the spoken reply, or null to fall through
     * to normal LLM handling (e.g. a generic "tell me about bookings" question).
     */
    private function handleBookingIntent(int $sessionId, string $userInput, string $intent): ?string
    {
        // Extract phone number (10 digits) if the caller spoke one
        preg_match('/\b(\d{10})\b/', $userInput, $m);
        $phone = $m[1] ?? '';
        $name = $this->extractName($userInput);

        try {
            $db = $this->db->getConnection();

            if (!empty($phone)) {
                $fullPhone = '91' . $phone;
                $stmt = $db->prepare("SELECT id FROM leads WHERE phone LIKE ?" . $this->tenantSql());
                $stmt->execute(['%' . $phone]);
                $existing = $stmt->fetch();

                if (!$existing) {
                    $insertSql = "INSERT INTO leads (name, phone, message, source, source_id, status, created_at" . 
                        (empty($this->tenantInsertData()) ? '' : ', tenant_id') . ")
                         VALUES (?, ?, ?, 'voice_agent', 0, 'new', NOW()" . 
                        (empty($this->tenantInsertData()) ? '' : ', ?') . ")";
                    $stmt = $db->prepare($insertSql);
                    $execParams = [$name ?: 'Voice Enquiry', $fullPhone, $userInput];
                    if (!empty($this->tenantInsertData())) $execParams = array_merge($execParams, array_values($this->tenantInsertData()));
                    $stmt->execute($execParams);
                    $leadId = (int)$db->lastInsertId();

                    // Log a site visit intent so the team can schedule
                    try {
                        $db->prepare(
                            "INSERT INTO site_visits (lead_id, visit_date, status, notes, created_at" . 
                            (empty($this->tenantInsertData()) ? '' : ', tenant_id') . ")
                             VALUES (?, DATE_ADD(NOW(), INTERVAL 2 DAY), 'scheduled', 'AI voice agent booking request', NOW()" . 
                            (empty($this->tenantInsertData()) ? '' : ', ?') . ")"
                        )->execute(array_merge([$leadId], array_values($this->tenantInsertData())));
                    } catch (\Throwable $e) { /* table may differ */ error_log($e->getMessage()); }

                    return "Dhanyavaad! Maine aapka booking request note kar liya hai. "
                        . "Hamari team aapko jald call karegi. Aapka reference number hai VOICE-{$leadId}. "
                        . "Kya aap apna naam bata sakte hain taaki hum better help kar sakein?";
                }
                return "Perfect! Humare paas aapka details hain. Hamari team aapko booking ke liye call karegi. "
                    . "Kuch aur jaanna hai?";
            }

            // No phone yet — ask for it (and name) to progress the booking
            if ($intent === 'site_visit') {
                return "Bilkul! Site visit ke liye please apna naam aur mobile number batayein. "
                    . "Hum free transport aur refreshments arrange karte hain.";
            }
            return "Booking ke liye shuruat Rs 21,000 se hoti hai. Please apna naam aur 10-digit mobile number batayein, "
                . "main aapka request register kar dunga.";
        } catch (\Throwable $e) {
            error_log("[AIVoicePipeline] booking intent failed: " . $e->getMessage());
            return null;
        }
    }

    private function extractName(string $text): string
    {
        // Hindi/English "my name is X" / "main X bol raha hoon"
        if (preg_match('/(?:my name is|i am|main|mera naam|name is)\s+([A-Za-z][A-Za-z .]{1,25})/i', $text, $m)) {
            return trim($m[1]);
        }
        return '';
    }

    /**
     * Transcribe audio to text using Whisper
     */
    public function transcribeAudio(string $audioPath): array
    {
        // Cloud-first: Groq whisper-large-v3 (free tier, no local infra needed)
        if (!empty($this->groqApiKey)) {
            $groq = $this->transcribeAudioGroq($audioPath);
            if ($groq['success']) {
                return $groq;
            }
        }

        // Fallback: local Whisper (self-hosted docker stack)
        if ($this->checkWhisperAvailable()) {
            return $this->callWhisperAPI($audioPath);
        }

        return [
            'success' => false,
            'text' => '',
            'error' => 'No STT engine available',
        ];
    }

    /**
     * Convert text to speech
     */
    public function textToSpeech(string $text, string $language = 'hi'): ?string
    {
        switch ($this->ttsEngine) {
            case 'google':
                return $this->googleTTS($text, $language);
            case 'espeak':
                return $this->eSpeakTTS($text, $language);
            case 'ollama':
                return $this->ollamaTTS($text);
            case 'groq':
                // Groq orpheus is English-only; Hindi falls back to Google TTS
                if ($language !== 'hi') {
                    $groqAudio = $this->groqTTS($text);
                    if ($groqAudio) {
                        return $groqAudio;
                    }
                }
                return $this->googleTTS($text, $language);
            default:
                return $this->googleTTS($text, $language);
        }
    }

    /**
     * Call LLM with fallback chain: Ollama (free) → Gemini (PRO) → fallback
     */
    private function callLLM(string $prompt, bool $forPhone = true): array
    {
        // ── Engine 1: Ollama (local, free) ──
        $ollamaResult = $this->callOllama($prompt, $forPhone);
        if ($ollamaResult['success']) {
            return $ollamaResult;
        }

        // ── Engine 2: Gemini (PRO member, high quality) ──
        if ($this->geminiApiKey) {
            $geminiResult = $this->callGemini($prompt);
            if ($geminiResult['success']) {
                if ($forPhone) {
                    $geminiResult['text'] = $this->cleanForPhone($geminiResult['text'], true);
                }
                return $geminiResult;
            }
        }

        // ── Engine 3: Local fallback (always works) ──
        return [
            'success' => false,
            'text' => '',
            'error' => 'All LLM engines failed',
            'engine' => 'none',
        ];
    }

    /**
     * Call Ollama LLM (free, local)
     */
    private function callOllama(string $prompt, bool $forPhone = true): array
    {
        $payload = json_encode([
            'model' => $this->ollamaModel,
            'prompt' => $prompt,
            'stream' => false,
            'keep_alive' => -1,
            'options' => [
                'temperature' => 0.7,
                'top_p' => 0.9,
                'num_predict' => 200,
            ],
        ]);

        $ch = curl_init("{$this->ollamaUrl}/api/generate");
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
        ]);

        $start = microtime(true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $elapsed = round((microtime(true) - $start) * 1000);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            $text = trim($data['response'] ?? '');
            $text = $this->cleanForPhone($text, $forPhone);

            if (!empty($text) && mb_strlen($text) > 5) {
                return [
                    'success' => true,
                    'text' => $text,
                    'engine' => 'ollama',
                    'confidence' => 0.85,
                    'latency_ms' => $elapsed,
                ];
            }
        }

        return [
            'success' => false,
            'text' => '',
            'error' => 'Ollama failed: HTTP ' . $httpCode,
            'engine' => 'ollama',
        ];
    }

    /**
     * Call Gemini API (PRO member, high quality)
     * Uses google/gemini-2.0-flash for fast, smart Hindi responses
     */
    private function callGemini(string $prompt): array
    {
        $payload = json_encode([
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'topP' => 0.9,
                'maxOutputTokens' => 150,
                'stopSequences' => ['Customer:', '\n\n'],
            ],
        ]);

        $url = $this->geminiUrl . '?key=' . $this->geminiApiKey;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);

        $start = microtime(true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $elapsed = round((microtime(true) - $start) * 1000);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            $text = '';
            
            if (!empty($data['candidates'][0]['content']['parts'][0]['text'])) {
                $text = trim($data['candidates'][0]['content']['parts'][0]['text']);
            }
            
            $text = $this->cleanForPhone($text);

            if (!empty($text) && mb_strlen($text) > 5) {
                return [
                    'success' => true,
                    'text' => $text,
                    'engine' => 'gemini',
                    'confidence' => 0.92,
                    'latency_ms' => $elapsed,
                ];
            }
        }

        return [
            'success' => false,
            'text' => '',
            'error' => 'Gemini failed: HTTP ' . $httpCode,
            'engine' => 'gemini',
        ];
    }

    /**
     * Check which engines are available
     */
    public function getEngineStatus(): array
    {
        // Check Ollama
        $ollamaOk = false;
        $ch = curl_init("{$this->ollamaUrl}/api/tags");
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $ollamaOk = curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200;
        curl_close($ch);

        // Check Gemini
        $geminiOk = !empty($this->geminiApiKey);

        // Check Whisper
        $whisperOk = false;
        $ch = curl_init("{$this->whisperUrl}/health");
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $whisperOk = curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200;
        curl_close($ch);

        return [
            'ollama' => ['available' => $ollamaOk, 'url' => $this->ollamaUrl, 'model' => $this->ollamaModel],
            'gemini' => ['available' => $geminiOk, 'model' => $this->geminiModel, 'is_pro' => true],
            'whisper' => ['available' => $whisperOk, 'url' => $this->whisperUrl],
            'groq_stt' => ['available' => !empty($this->groqApiKey), 'model' => 'whisper-large-v3', 'primary' => true],
            'groq_tts' => ['available' => !empty($this->groqApiKey), 'model' => 'canopylabs/orpheus-v1-english', 'note' => 'English-only; needs console terms acceptance'],
            'tts' => ['engine' => $this->ttsEngine],
            'fallback_chain' => $this->getFallbackChain(),
        ];
    }

    /**
     * Get current fallback chain description
     */
    private function getFallbackChain(): array
    {
        $chain = ['1. Ollama (free, local)'];
        if ($this->geminiApiKey) {
            $chain[] = '2. Gemini 2.0 Flash (PRO, fast)';
        }
        $chain[] = count($chain) + 1 . '. Rule-based Hindi responses';
        return $chain;
    }

    /**
     * Build context prompt for LLM
     */
    private function buildContext(array $history, string $userInput): string
    {
        $systemPrompt = $this->knowledgeBase['system_prompt'] ?? '';
        
        // Add conversation history
        $historyText = '';
        foreach (array_slice($history, -10) as $turn) {
            $historyText .= "Customer: {$turn['user']}\n";
            $historyText .= "Agent: {$turn['ai']}\n";
        }

        // Add current user input
        $fullPrompt = "{$systemPrompt}\n\n";
        if ($historyText) {
            $fullPrompt .= "Conversation so far:\n{$historyText}\n";
        }
        $fullPrompt .= "Customer: {$userInput}\nAgent:";

        return $fullPrompt;
    }

    /**
     * Clean LLM response for phone conversation
     */
    private function cleanForPhone(string $text, bool $forPhone = true): string
    {
        // Remove markdown formatting
        $text = preg_replace('/\*\*.*?\*\*/', '', $text);
        $text = preg_replace('/\*.*?\*/', '', $text);
        $text = preg_replace('/#{1,6}\s/', '', $text);
        
        // Remove code blocks
        $text = preg_replace('/```.*?```/s', '', $text);
        
        // Remove URLs
        $text = preg_replace('/https?:\/\/\S+/', '', $text);
        
        // Clean up whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        // Limit length for phone calls (max ~30 seconds of speech).
        // Chat mode allows longer, natural answers.
        if ($forPhone && mb_strlen($text) > 300) {
            $text = mb_substr($text, 0, 297) . '...';
        }

        return $text;
    }

    /**
     * Detect intent from user input
     */
    private function detectIntent(string $input): string
    {
        $input = mb_strtolower($input);
        
        $intents = [
            'price_inquiry' => ['price', 'cost', 'kitna', 'rate', 'budget', 'dam', 'keemat', 'rates', 'कीमत', 'कितना', 'दाम', 'रेट', 'बजट'],
            'site_visit' => ['visit', 'dekhna', 'dikhana', 'site', 'location', 'aana', 'milna', 'विजिट', 'विज़िट', 'देखना', 'दिखाना', 'साइट', 'मिलना', 'आना'],
            'booking' => ['booking', 'book', 'register', 'buy', 'kharid', 'lena', 'purchase', 'बुक', 'बुकिंग', 'खरीद', 'खरीदना', 'रजिस्टर', 'रजिस्ट्रेशन', 'लेना'],
            'loan_inquiry' => ['loan', 'finance', 'emi', 'installment', 'bank', 'karj', 'लोन', 'फाइनेंस', 'ईएमआई', 'कर्ज'],
            'disinterest' => ['bye', 'goodbye', 'nhi chahiye', 'no', 'not interested', 'baad', 'baad mein', 'नहीं', 'नही', 'नहि', 'बाद', 'मत'],
            'complaint' => ['complaint', 'shikayat', 'problem', 'issue', 'galti', 'शिकायत', 'समस्या', 'परेशानी'],
            'location' => ['location', 'address', 'kahan', 'kidhar', 'map', 'direction', 'पता', 'कहां', 'किधर', 'मैप', 'दिशा'],
            'timing' => ['time', 'timing', 'kab', 'open', 'close', 'hours', 'समय', 'कब', 'खुला', 'बंद'],
        ];

        foreach ($intents as $intent => $keywords) {
            foreach ($keywords as $keyword) {
                if (mb_strpos($input, $keyword) !== false) {
                    return $intent;
                }
            }
        }

        return 'general';
    }

    /**
     * Detect sentiment from user input
     */
    private function detectSentiment(string $input): string
    {
        $input = mb_strtolower($input);
        
        $positive = ['accha', 'great', 'good', 'best', 'pasand', 'interested', 'chahiye', 'batao', 'haan', 'yes', 'theek'];
        $negative = ['nhi', 'no', 'bad', 'worst', 'nahi', 'mat', 'boring', 'waste', 'useless'];
        $angry = ['angry', 'gussa', 'kharab', 'panic', 'complaint', 'shikayat', 'frustrated'];
        
        foreach ($angry as $word) {
            if (mb_strpos($input, $word) !== false) return 'angry';
        }
        foreach ($negative as $word) {
            if (mb_strpos($input, $word) !== false) return 'negative';
        }
        foreach ($positive as $word) {
            if (mb_strpos($input, $word) !== false) return 'positive';
        }
        
        return 'neutral';
    }

    /**
     * Get fallback response when LLM is unavailable
     */
    private function getFallbackResponse(string $input): string
    {
        $intent = $this->detectIntent($input);
        
        $fallbacks = [
            'price_inquiry' => 'Humari properties ki starting price 5 lakh se hai. Aapko kitne budget mein chahiye? Main aapko best options bata sakta hoon.',
            'site_visit' => 'Bilkul! Aap site visit ke liye aa sakte hain. Humara office Raghunath Nagri, Gorakhpur mein hai. Kab aana chahte hain?',
            'booking' => 'Booking ke liye dhanyavaad! Aapko 21,000 se booking start ho jayegi. Kya aap office aana chahte hain details ke liye?',
            'loan_inquiry' => 'Humare paas bank loan ki suvidha hai. 80% tak loan mil sakta hai. Kya aapko loan ke baare mein detail chahiye?',
            'disinterest' => 'Koi baat nhi. Jab bhi zaroorat ho, humse contact karein. Dhanyavaad!',
            'general' => 'APS Dream Home mein aapka swagat hai! Hum plots aur properties offer karte hain Gorakhpur mein. Aapko kya chahiye?',
        ];

        return $fallbacks[$intent] ?? $fallbacks['general'];
    }

    /**
     * Generate a property-aware chat reply from the LLM (Ollama → Gemini → fallback).
     * Reused by the website customer chatbot so it gives real Hindi/English answers
     * using LIVE database inventory instead of canned pattern responses.
     * Runs in chat mode (no 300-char phone truncation).
     */
    public function generateChatReply(string $userInput): string
    {
        $context = $this->buildContext([], $userInput);

        $liveInventory = $this->getLiveInventoryContext();
        if (!empty($liveInventory)) {
            $context .= "\n\nREAL-TIME AVAILABLE INVENTORY (from database — use these exact facts):\n"
                . $liveInventory
                . "\nWhen the customer asks about colonies, plots, availability, or prices, cite this live data.";
        }

        $response = $this->callLLM($context, false);
        if (!empty($response['success']) && !empty($response['text'])) {
            return $response['text'];
        }
        return $this->getFallbackResponse($userInput);
    }

    /**
     * Build a short real-time inventory brief from the database for the LLM to cite.
     */
    private function getLiveInventoryContext(): string
    {
        try {
            $colonies = $this->db->fetchAll(
                "SELECT c.name, c.starting_price, c.min_price_per_sqft,
                        (SELECT COUNT(*) FROM plots p WHERE p.colony_id = c.id AND p.status='available' AND p.is_active=1) AS avail
                 FROM colonies c
                 WHERE c.is_active = 1
                 ORDER BY c.is_featured DESC, c.id
                 LIMIT 8"
            );
            if (empty($colonies)) {
                return '';
            }

            $lines = [];
            foreach ($colonies as $c) {
                if (!empty($c['starting_price']) && $c['starting_price'] > 0) {
                    $price = 'Rs ' . number_format((float)$c['starting_price']) . ' onwards';
                } elseif (!empty($c['min_price_per_sqft']) && $c['min_price_per_sqft'] > 0) {
                    $price = 'Rs ' . number_format((float)$c['min_price_per_sqft']) . ' per sqft';
                } else {
                    $price = 'price on request';
                }
                $lines[] = "- {$c['name']}: {$c['avail']} plots available, starting {$price}";
            }

            $samples = $this->db->fetchAll(
                "SELECT c.name AS colony, p.plot_number, p.area_sqft, p.total_price, p.facing
                 FROM plots p
                 JOIN colonies c ON c.id = p.colony_id
                 WHERE p.status='available' AND p.is_active=1
                 ORDER BY p.is_featured DESC, p.id
                 LIMIT 5"
            );
            if (!empty($samples)) {
                $lines[] = "Sample available plots:";
                foreach ($samples as $s) {
                    $pr = (!empty($s['total_price']) && $s['total_price'] > 0)
                        ? 'Rs ' . number_format((float)$s['total_price'])
                        : 'price on request';
                    $facing = !empty($s['facing']) ? ", facing {$s['facing']}" : '';
                    $lines[] = "  - {$s['colony']} Plot {$s['plot_number']}: {$s['area_sqft']} sqft, {$pr}{$facing}";
                }
            }

            return implode("\n", $lines);
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Get conversation history from database
     */
    private function getConversationHistory(int $sessionId): array
    {
        try {
             $transcript = $this->db->fetch(
                "SELECT call_transcript FROM ai_call_sessions WHERE id = ?" . $this->tenantSql(),
                [$sessionId]
            );
            
            if (!$transcript || empty($transcript['call_transcript'])) {
                return [];
            }

            $lines = explode("\n", $transcript['call_transcript']);
            $history = [];
            $currentRole = '';
            $currentText = '';

            foreach ($lines as $line) {
                if (preg_match('/^\[(USER|AI)\]:\s*(.*)$/', trim($line), $m)) {
                    if ($currentRole) {
                        $history[] = [
                            'role' => strtolower($currentRole),
                            'user' => $currentRole === 'USER' ? $currentText : '',
                            'ai' => $currentRole === 'AI' ? $currentText : '',
                        ];
                    }
                    $currentRole = $m[1];
                    $currentText = $m[2];
                } else {
                    $currentText .= ' ' . trim($line);
                }
            }
            if ($currentRole) {
                $history[] = [
                    'role' => strtolower($currentRole),
                    'user' => $currentRole === 'USER' ? $currentText : '',
                    'ai' => $currentRole === 'AI' ? $currentText : '',
                ];
            }

            return $history;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Save conversation turn to database
     */
    private function saveTurn(int $sessionId, string $userInput, string $aiResponse, string $intent, string $sentiment): void
    {
        try {
            $session = $this->db->fetch(
                "SELECT call_transcript FROM ai_call_sessions WHERE id = ?" . $this->tenantSql(),
                [$sessionId]
            );
            
            $transcript = $session['call_transcript'] ?? '';
            $transcript .= "\n[USER]: " . $userInput;
            $transcript .= "\n[AI]: " . $aiResponse;

            $this->db->execute(
                "UPDATE ai_call_sessions SET call_transcript = ?, sentiment_score = ?, ai_summary = ?, updated_at = NOW() WHERE id = ?" . $this->tenantSql(),
                [$transcript, $sentiment, $aiResponse, $sessionId]
            );
        } catch (\Exception $e) {
            error_log("AIVoicePipeline saveTurn error: " . $e->getMessage());
        }
    }

    /**
     * Check if Whisper is available
     */
    private function checkWhisperAvailable(): bool
    {
        $ch = curl_init("{$this->whisperUrl}/health");
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code === 200;
    }

    /**
     * Call Whisper API for transcription
     */
    private function callWhisperAPI(string $audioPath): array
    {
        $ch = curl_init("{$this->whisperUrl}/inference");
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => [
                'audio_file' => new \CURLFile($audioPath),
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            return [
                'success' => true,
                'text' => $data['text'] ?? '',
                'language' => $data['language'] ?? 'hi',
            ];
        }

        return ['success' => false, 'text' => ''];
    }

    /**
     * Groq cloud STT (whisper-large-v3, free tier) — auto-detects Hindi/English
     */
    private function transcribeAudioGroq(string $audioPath): array
    {
        if (!is_file($audioPath)) {
            return ['success' => false, 'text' => '', 'error' => 'Audio file missing'];
        }

        $ch = curl_init('https://api.groq.com/openai/v1/audio/transcriptions');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ["Authorization: Bearer {$this->groqApiKey}"],
            CURLOPT_POSTFIELDS => [
                'file' => new \CURLFile($audioPath),
                'model' => 'whisper-large-v3',
                'response_format' => 'verbose_json',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            return [
                'success' => true,
                'text' => trim((string)($data['text'] ?? '')),
                'language' => $data['language'] ?? 'hi',
                'engine' => 'groq_whisper',
            ];
        }

        error_log("Groq STT failed (HTTP {$httpCode}): " . substr((string)$response, 0, 200));
        return ['success' => false, 'text' => '', 'error' => 'HTTP ' . $httpCode];
    }

    /**
     * Google TTS (free tier)
     */
    private function googleTTS(string $text, string $language = 'hi'): ?string
    {
        $langCode = $language === 'hi' ? 'hi-IN' : 'en-US';
        $url = "https://translate.google.com/translate_tts?ie=UTF-8&tl={$langCode}&client=tw-ob&q=" . urlencode($text);
        
        $audioDir = __DIR__ . '/../../storage/tts_audio';
        if (!is_dir($audioDir)) mkdir($audioDir, 0755, true);
        
        $audioFile = $audioDir . '/tts_' . md5($text) . '.mp3';
        
        if (!file_exists($audioFile)) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_USERAGENT => 'Mozilla/5.0',
            ]);
            $audio = curl_exec($ch);
            curl_close($ch);
            
            if ($audio) {
                file_put_contents($audioFile, $audio);
            }
        }

        return file_exists($audioFile) ? $audioFile : null;
    }

    /**
     * Groq cloud TTS (canopylabs/orpheus-v1-english, free tier).
     * Requires one-time terms acceptance in the Groq console; falls back to null on any failure
     * (caller then uses Google TTS).
     */
    private function groqTTS(string $text): ?string
    {
        if (empty($this->groqApiKey)) {
            return null;
        }

        $ch = curl_init('https://api.groq.com/openai/v1/audio/speech');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ["Authorization: Bearer {$this->groqApiKey}", 'Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode([
                'model' => 'canopylabs/orpheus-v1-english',
                'input' => mb_substr($text, 0, 900),
                'voice' => 'autumn',
                'response_format' => 'mp3',
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 90,
        ]);
        $audio = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && is_string($audio) && strlen($audio) > 1000) {
            $audioDir = __DIR__ . '/../../storage/tts_audio';
            if (!is_dir($audioDir)) mkdir($audioDir, 0755, true);
            $audioFile = $audioDir . '/tts_groq_' . md5($text) . '.mp3';
            file_put_contents($audioFile, $audio);
            return $audioFile;
        }

        error_log("Groq TTS failed (HTTP {$httpCode}) — falling back to Google TTS");
        return null;
    }

    /**
     * Ollama TTS (via Piper or similar local TTS)
     */
    private function ollamaTTS(string $text): ?string
    {
        $audioDir = __DIR__ . '/../../storage/tts_audio';
        if (!is_dir($audioDir)) mkdir($audioDir, 0755, true);
        
        $audioFile = $audioDir . '/tts_' . md5($text) . '.wav';
        
        // Try using piper-tts if available
        $piperPath = getenv('PIPER_PATH') ?: '/usr/local/bin/piper';
        if (file_exists($piperPath)) {
            $model = getenv('PIPER_MODEL') ?: '/opt/piper/voices/hi_IN-libritts_r-medium.onnx';
            $cmd = "echo " . escapeshellarg($text) . " | " . escapeshellarg($piperPath) . " --model " . escapeshellarg($model) . " --output_file " . escapeshellarg($audioFile) . " 2>/dev/null";
            exec($cmd, $output, $returnCode);
            if ($returnCode === 0 && file_exists($audioFile)) {
                return $audioFile;
            }
        }
        
        // Fallback to Google TTS
        return $this->googleTTS($text, 'hi');
    }

    /**
     * eSpeak TTS (local, offline)
     */
    private function eSpeakTTS(string $text, string $language = 'hi'): ?string
    {
        $audioDir = __DIR__ . '/../../storage/tts_audio';
        if (!is_dir($audioDir)) mkdir($audioDir, 0755, true);
        
        $audioFile = $audioDir . '/tts_' . md5($text) . '.wav';
        $lang = $language === 'hi' ? 'hi' : 'en';
        
        exec("espeak -v " . escapeshellarg($lang) . " -w " . escapeshellarg($audioFile) . " " . escapeshellarg($text), $output, $returnCode);
        
        return ($returnCode === 0 && file_exists($audioFile)) ? $audioFile : null;
    }

    /**
     * Load knowledge base for property conversations
     */
    private function loadKnowledgeBase(): array
    {
        return [
            'system_prompt' => <<<'PROMPT'
You are Riya, a friendly and professional property consultant at APS Dream Home in Gorakhpur, UP.

KEY RULES:
- Always respond in Hindi (Hinglish is OK)
- Keep responses SHORT (1-3 sentences max) - this is a phone call
- Be warm, friendly, and helpful
- Never argue with customers
- If unsure, offer to connect with a human agent
- Always try to schedule a site visit or get commitment

PROPERTY KNOWLEDGE:
- APS Dream Home sells residential plots in Gorakhpur
- Colonies: Suryoday, Braj Radha Nagri, Raghunath Nagri, Budh Bihar
- Plot sizes: 1000-5000 sqft
- Starting price: Rs 5 lakh onwards
- EMI options available: 12-60 months
- Location: Raghunath Nagri, Gorakhpur, UP
- Contact: 7007444842

PRICING:
- Basic plots: Rs 5-10 lakh
- Premium plots: Rs 10-25 lakh
- Commercial: Rs 15-50 lakh
- Booking amount: Rs 21,000

COMMON RESPONSES:
- Price inquiry: Share starting price, offer EMI options
- Site visit: Welcome them, suggest time, share address
- Booking: Explain process, booking amount, documents needed
- Loan: 80% bank financing available, EMI from Rs 8,000/month
- Complaint: Apologize, assure resolution, connect to manager

NEVER:
- Share competitor names
- Make false promises about ROI
- Promise guaranteed returns
- Share confidential internal data
PROMPT,
        ];
    }
}
