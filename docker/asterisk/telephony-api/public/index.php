<?php
/**
 * APS Dream Home - Telephony API Bridge
 * 
 * Lightweight PHP API that bridges:
 * - Asterisk AMI (for call control)
 * - Whisper (for speech-to-text)
 * - Ollama (for AI conversations)
 * - MySQL (for call logging)
 * 
 * Endpoints:
 * POST /api/call/initiate    — Make outbound call
 * POST /api/call/hangup      — Hangup call
 * GET  /api/call/status      — Check call status
 * POST /api/ai/conversation  — Process AI conversation turn
 * POST /api/whisper/transcribe — Transcribe audio
 * GET  /api/health           — Health check
 */

header('Content-Type: application/json');

// Config
$config = [
    'asterisk_host' => getenv('ASTERISK_HOST') ?: 'asterisk',
    'asterisk_port' => (int)(getenv('ASTERISK_PORT') ?: 5038),
    'asterisk_user' => getenv('ASTERISK_USERNAME') ?: 'admin',
    'asterisk_pass' => getenv('ASTERISK_SECRET') ?: 'ApsDreamHome2026!',
    'ollama_url' => getenv('OLLAMA_URL') ?: 'http://ollama:11434',
    'ollama_model' => getenv('OLLAMA_MODEL') ?: 'llama3.2:3b',
    'whisper_url' => getenv('WHISPER_URL') ?: 'http://whisper:8080',
    'db_host' => getenv('DB_HOST') ?: 'db',
    'db_port' => (int)(getenv('DB_PORT') ?: 3306),
    'db_name' => getenv('DB_DATABASE') ?: 'apsdreamhome',
    'db_user' => getenv('DB_USERNAME') ?: 'apsdream',
    'db_pass' => getenv('DB_PASSWORD') ?: 'changeme',
];

// Database connection
try {
    $pdo = new PDO(
        "mysql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_name']}",
        $config['db_user'],
        $config['db_pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed', 'details' => $e->getMessage()]);
    exit;
}

// Parse request
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/');

// Route requests
switch ($uri) {
    case '/api/health':
        echo json_encode(['status' => 'ok', 'time' => date('Y-m-d H:i:s')]);
        break;

    case '/api/call/initiate':
        handleCallInitiate($config, $pdo);
        break;

    case '/api/call/hangup':
        handleCallHangup($config, $pdo);
        break;

    case '/api/call/status':
        handleCallStatus($config, $pdo);
        break;

    case '/api/ai/conversation':
        handleAIConversation($config, $pdo);
        break;

    case '/api/whisper/transcribe':
        handleWhisperTranscribe($config);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
}

// ── Handler Functions ──

function handleCallInitiate(array $config, PDO $pdo): void
{
    $input = json_decode(file_get_contents('php://input'), true);
    $phone = $input['phone'] ?? '';
    $script = $input['script'] ?? 'default';
    $agentId = $input['agent_id'] ?? null;
    $leadId = $input['lead_id'] ?? null;

    if (empty($phone)) {
        http_response_code(400);
        echo json_encode(['error' => 'Phone number required']);
        return;
    }

    // Sanitize phone
    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($cleanPhone) === 10) $cleanPhone = '91' . $cleanPhone;

    // Create schedule entry
    $stmt = $pdo->prepare("INSERT INTO ai_calling_schedule 
        (lead_id, phone, priority, scheduled_date, scheduled_time, ai_agent_id, script_template, max_attempts, status, created_by, created_at, updated_at)
        VALUES (?, ?, 'high', CURDATE(), DATE_FORMAT(NOW(), '%H:%i:%s'), ?, ?, 3, 'pending', 0, NOW(), NOW())");
    $stmt->execute([$leadId, $cleanPhone, $agentId, $script]);
    $scheduleId = $pdo->lastInsertId();

    // Create session
    $stmt = $pdo->prepare("INSERT INTO ai_call_sessions 
        (lead_id, phone, call_type, status, ai_agent_id, script_template, started_at, created_at, updated_at)
        VALUES (?, ?, 'outbound', 'in_progress', ?, ?, NOW(), NOW(), NOW())");
    $stmt->execute([$leadId, $cleanPhone, $agentId, $script]);
    $sessionId = $pdo->lastInsertId();

    // Initiate via Asterisk AMI
    $callId = 'API-' . date('YmdHis') . '-' . substr(md5($cleanPhone . microtime()), 0, 6);
    $amiResult = asteriskOriginate($config, $cleanPhone, $script, $sessionId, $callId);

    if ($amiResult['success']) {
        // Update session with call ID
        $stmt = $pdo->prepare("UPDATE ai_call_sessions SET call_sid = ? WHERE id = ?");
        $stmt->execute([$callId, $sessionId]);

        echo json_encode([
            'success' => true,
            'call_id' => $callId,
            'session_id' => $sessionId,
            'schedule_id' => $scheduleId,
            'phone' => $cleanPhone,
        ]);
    } else {
        // Mark as failed
        $stmt = $pdo->prepare("UPDATE ai_call_sessions SET status = 'failed' WHERE id = ?");
        $stmt->execute([$sessionId]);

        http_response_code(500);
        echo json_encode(['error' => 'Asterisk originate failed', 'details' => $amiResult['message'] ?? '']);
    }
}

function handleCallHangup(array $config, PDO $pdo): void
{
    $input = json_decode(file_get_contents('php://input'), true);
    $channel = $input['channel'] ?? '';
    $sessionId = $input['session_id'] ?? null;

    if (empty($channel)) {
        http_response_code(400);
        echo json_encode(['error' => 'Channel required']);
        return;
    }

    $result = asteriskHangup($config, $channel);

    if ($sessionId) {
        $stmt = $pdo->prepare("UPDATE ai_call_sessions SET status = 'completed', ended_at = NOW(), updated_at = NOW() WHERE id = ?");
        $stmt->execute([$sessionId]);
    }

    echo json_encode(['success' => $result]);
}

function handleCallStatus(array $config, PDO $pdo): void
{
    $sessionId = $_GET['session_id'] ?? null;

    if ($sessionId) {
        $stmt = $pdo->prepare("SELECT * FROM ai_call_sessions WHERE id = ?");
        $stmt->execute([$sessionId]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'session' => $session]);
    } else {
        // Get active channels from Asterisk
        $channels = asteriskGetChannels($config);
        echo json_encode(['success' => true, 'active_channels' => $channels]);
    }
}

function handleAIConversation(array $config, PDO $pdo): void
{
    $input = json_decode(file_get_contents('php://input'), true);
    $sessionId = $input['session_id'] ?? null;
    $userInput = $input['user_input'] ?? '';
    $inputType = $input['input_type'] ?? 'text';

    if (!$sessionId || empty($userInput)) {
        http_response_code(400);
        echo json_encode(['error' => 'session_id and user_input required']);
        return;
    }

    // Get conversation history
    $stmt = $pdo->prepare("SELECT call_transcript FROM ai_call_sessions WHERE id = ?");
    $stmt->execute([$sessionId]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    $history = $session['call_transcript'] ?? '';

    // Build prompt
    $systemPrompt = "You are Riya, a friendly property consultant at APS Dream Home in Gorakhpur. Respond in Hindi/Hinglish. Keep responses SHORT (1-3 sentences) for phone calls. Properties: plots from ₹5 lakh, colonies in Gorakhpur. EMI available. Contact: 7007444842.";

    $prompt = "{$systemPrompt}\n\n";
    if ($history) {
        $prompt .= "Conversation:\n{$history}\n";
    }
    $prompt .= "Customer: {$userInput}\nAgent:";

    // Call Ollama
    $llmResponse = callOllama($config, $prompt);

    // Detect intent
    $intent = detectIntent($userInput);

    // Save transcript
    $newTranscript = $history . "\n[USER]: " . $userInput . "\n[AI]: " . $llmResponse['text'];
    $stmt = $pdo->prepare("UPDATE ai_call_sessions SET call_transcript = ?, ai_summary = ?, sentiment_score = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$newTranscript, $llmResponse['text'], $intent, $sessionId]);

    echo json_encode([
        'success' => true,
        'response' => $llmResponse['text'],
        'intent' => $intent,
        'engine' => $llmResponse['engine'] ?? 'ollama',
    ]);
}

function handleWhisperTranscribe(array $config): void
{
    // Check if audio file was uploaded
    if (!isset($_FILES['audio'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Audio file required']);
        return;
    }

    $tmpFile = $_FILES['audio']['tmp_name'];

    // Send to Whisper API
    $ch = curl_init("{$config['whisper_url']}/inference");
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => ['audio_file' => new CURLFile($tmpFile)],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        echo json_encode([
            'success' => true,
            'text' => $data['text'] ?? '',
            'language' => $data['language'] ?? 'hi',
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Whisper transcription failed']);
    }
}

// ── Asterisk AMI Functions ──

function asteriskOriginate(array $config, string $phone, string $script, int $sessionId, string $callId): array
{
    $sock = @fsockopen($config['asterisk_host'], $config['asterisk_port'], $errno, $errstr, 5);
    if (!$sock) {
        return ['success' => false, 'message' => "Cannot connect: {$errstr}"];
    }

    // Read banner
    fgets($sock, 1024);

    // Login
    fwrite($sock, "Action: Login\r\nUsername: {$config['asterisk_user']}\r\nSecret: {$config['asterisk_pass']}\r\nEvents: Off\r\n\r\n");
    $response = fgets($sock, 1024);

    if (strpos($response, 'Success') === false && strpos($response, 'Accepted') === false) {
        fclose($sock);
        return ['success' => false, 'message' => 'AMI login failed'];
    }

    // Originate call
    $actionId = uniqid('api_', true);
    $dialString = "PJSIP/{$phone}@gsm-gateway";
    $vars = "CALLID={$callId},SESSION_ID={$sessionId},SCRIPT={$script}";

    $command = "Action: Originate\r\n"
        . "ActionID: {$actionId}\r\n"
        . "Channel: {$dialString}\r\n"
        . "Context: outbound-calls\r\n"
        . "Exten: s\r\n"
        . "Priority: 1\r\n"
        . "Timeout: 30000\r\n"
        . "Variable: {$vars}\r\n"
        . "Async: true\r\n\r\n";

    fwrite($sock, $command);

    // Read response
    stream_set_timeout($sock, 5);
    $resp = '';
    while (($line = fgets($sock, 4096)) !== false) {
        $resp .= $line;
        if ($line === "\r\n") break;
    }

    fclose($sock);

    $success = strpos($resp, 'Success') !== false || strpos($resp, 'Accepted') !== false;
    return ['success' => $success, 'message' => $success ? 'Call initiated' : trim($resp)];
}

function asteriskHangup(array $config, string $channel): bool
{
    $sock = @fsockopen($config['asterisk_host'], $config['asterisk_port'], $errno, $errstr, 5);
    if (!$sock) return false;

    fgets($sock, 1024);
    fwrite($sock, "Action: Login\r\nUsername: {$config['asterisk_user']}\r\nSecret: {$config['asterisk_pass']}\r\nEvents: Off\r\n\r\n");
    fgets($sock, 1024);

    fwrite($sock, "Action: Hangup\r\nChannel: {$channel}\r\n\r\n");
    $response = fgets($sock, 1024);
    fclose($sock);

    return strpos($response, 'Success') !== false;
}

function asteriskGetChannels(array $config): array
{
    $sock = @fsockopen($config['asterisk_host'], $config['asterisk_port'], $errno, $errstr, 3);
    if (!$sock) return [];

    fgets($sock, 1024);
    fwrite($sock, "Action: Login\r\nUsername: {$config['asterisk_user']}\r\nSecret: {$config['asterisk_pass']}\r\nEvents: Off\r\n\r\n");
    fgets($sock, 1024);

    fwrite($sock, "Action: Channels\r\n\r\n");
    stream_set_timeout($sock, 5);
    $response = '';
    while (($line = fgets($sock, 4096)) !== false) {
        $response .= $line;
        if ($line === "\r\n") break;
    }
    fclose($sock);

    $channels = [];
    $current = [];
    foreach (explode("\r\n", $response) as $line) {
        if (trim($line) === '' && !empty($current)) {
            $channels[] = $current;
            $current = [];
            continue;
        }
        if (preg_match('/^(\w[\w\s]*?):\s*(.*)$/', trim($line), $m)) {
            $current[$m[1]] = $m[2];
        }
    }

    return $channels;
}

// ── AI Functions ──

function callLLM(array $config, string $prompt): array
{
    // Engine 1: Ollama (free, local)
    $ollamaResult = callOllama($config, $prompt);
    if ($ollamaResult['success'] && $ollamaResult['engine'] === 'ollama') {
        return $ollamaResult;
    }

    // Engine 2: Gemini (PRO, high quality)
    $geminiKey = getenv('GEMINI_API_KEY') ?: '';
    if (!empty($geminiKey)) {
        $geminiResult = callGemini($config, $prompt, $geminiKey);
        if ($geminiResult['success']) {
            return $geminiResult;
        }
    }

    // Engine 3: Rule-based fallback (always works)
    return ['success' => true, 'text' => 'Humare paas aapke liye best properties hain. Gorakhpur mein plots from 5 lakh. Kya aap site visit karna chahenge? Reply karein ya 7007444842 pe call karein.', 'engine' => 'fallback'];
}

function callOllama(array $config, string $prompt): array
{
    $payload = json_encode([
        'model' => $config['ollama_model'],
        'prompt' => $prompt,
        'stream' => false,
        'options' => ['temperature' => 0.7, 'num_predict' => 150],
    ]);

    $ch = curl_init("{$config['ollama_url']}/api/generate");
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        $text = trim($data['response'] ?? '');
        $text = preg_replace('/\*\*.*?\*\*/', '', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        if (mb_strlen($text) > 300) $text = mb_substr($text, 0, 297) . '...';
        if (mb_strlen($text) > 5) {
            return ['success' => true, 'text' => $text, 'engine' => 'ollama'];
        }
    }

    return ['success' => false, 'text' => '', 'error' => 'Ollama failed', 'engine' => 'ollama'];
}

function callGemini(array $config, string $prompt, string $apiKey): array
{
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}";

    $payload = json_encode([
        'contents' => [
            ['parts' => [['text' => $prompt]]]
        ],
        'generationConfig' => [
            'temperature' => 0.7,
            'maxOutputTokens' => 150,
        ],
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $text = trim($text);
        $text = preg_replace('/\*\*.*?\*\*/', '', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        if (mb_strlen($text) > 300) $text = mb_substr($text, 0, 297) . '...';
        if (mb_strlen($text) > 5) {
            return ['success' => true, 'text' => $text, 'engine' => 'gemini'];
        }
    }

    return ['success' => false, 'text' => '', 'error' => 'Gemini failed', 'engine' => 'gemini'];
}

function detectIntent(string $input): string
{
    $input = mb_strtolower($input);
    $intents = [
        'price_inquiry' => ['price', 'cost', 'kitna', 'rate', 'budget', 'dam'],
        'site_visit' => ['visit', 'dekhna', 'dikhana', 'site', 'location'],
        'booking' => ['booking', 'book', 'register', 'buy', 'kharid'],
        'loan_inquiry' => ['loan', 'finance', 'emi', 'installment'],
        'disinterest' => ['bye', 'goodbye', 'nhi chahiye', 'no', 'not interested'],
    ];
    foreach ($intents as $intent => $keywords) {
        foreach ($keywords as $kw) {
            if (mb_strpos($input, $kw) !== false) return $intent;
        }
    }
    return 'general';
}
