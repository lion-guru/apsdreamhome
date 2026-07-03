#!/usr/bin/php
<?php
/**
 * APS Dream Homes — Asterisk AGI Script for AI Voice Bot
 *
 * Place at: /var/lib/asterisk/agi/aps_ai_agent.php
 * Make executable: chmod +x aps_ai_agent.php
 *
 * Called by Asterisk dialplan after customer answers:
 *   same => n,AGI(/var/lib/asterisk/agi/aps_ai_agent.php,${CALLID},${CUSTOMER_PHONE})
 *
 * Flow:
 * 1. Read AGI arguments (call_id, customer_phone)
 * 2. Send greeting TTS to customer
 * 3. Listen for customer speech (STT via Web Speech API or Whisper)
 * 4. Send speech to APS AI chatbot API
 * 5. Play AI response as TTS back to customer
 * 6. Loop until conversation ends or hangup
 */

// Asterisk AGI environment
$agi = [];
while ($line = fgets(STDIN)) {
    $line = trim($line);
    if ($line === '') break;
    if (preg_match('/^AGI (\w+): (.*)$/', $line, $m)) {
        $agi[$m[1]] = $m[2];
    }
}

// AGI arguments
$argc_arr = $argv;
$callId = $argc_arr[1] ?? 'unknown';
$customerPhone = $argc_arr[2] ?? 'unknown';

// APS Dream Homes API config
$apiBase = 'http://127.0.0.1/apsdreamhome';
$chatbotEndpoint = $apiBase . '/api/ai/chat';

// Log the call
error_log("[APS-AGI] Call started: ID={$callId}, Phone={$customerPhone}");

/**
 * Send AGI command to Asterisk
 */
function agi_exec(string $cmd): string
{
    echo $cmd . "\n";
    fflush(STDOUT);
    $response = fgets(STDIN);
    return trim($response ?? '');
}

/**
 * Play audio file to caller
 */
function agi_stream_file(string $file, string $digits = ''): string
{
    return agi_exec("STREAM FILE {$file} {$digits}");
}

/**
 * Say text using Asterisk TTS (festival or external)
 */
function agi_say_number(int $number): string
{
    return agi_exec("SAY NUMBER {$number} \"\"");
}

/**
 * Record audio from caller (for STT)
 */
function agi_record_file(string $file, string $format = 'wav', string $timeout = '5000'): string
{
    return agi_exec("RECORD FILE {$file} {$format} \"#\" {$timeout} 0 120000 1");
}

/**
 * Send speech text to APS AI chatbot and get response
 */
function getAIResponse(string $speech): string
{
    global $chatbotEndpoint, $callId;

    $payload = json_encode([
        'message' => $speech,
        'session_id' => 'asterisk_' . $callId,
        'language' => 'hi',
    ]);

    $ch = curl_init($chatbotEndpoint);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-Forwarded-For: 127.0.0.1',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        error_log("[APS-AGI] Chatbot API failed: HTTP {$httpCode}");
        return "Sorry, I'm having some technical issues. Please call us at 919277121112.";
    }

    $data = json_decode($response, true);
    return $data['response'] ?? $data['reply'] ?? "I didn't understand. Please try again.";
}

/**
 * Convert text to speech file (using festival or external TTS)
 */
function textToSpeech(string $text, string $outputFile): bool
{
    // Method 1: Google Translate TTS (free, Hindi supported)
    $encoded = urlencode($text);
    $url = "https://translate.google.com/translate_tts?ie=UTF-8&q={$encoded}&tl=hi&client=tw-ob";

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0',
        CURLOPT_TIMEOUT => 10,
    ]);

    $audio = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $audio && strlen($audio) > 1000) {
        file_put_contents($outputFile, $audio);
        return true;
    }

    // Method 2: Festival TTS (if installed)
    $escaped = escapeshellarg($text);
    exec("echo {$escaped} | festival --tts --output_format wav 2>/dev/null && echo OK", $output, $returnCode);
    if ($returnCode === 0) {
        return true;
    }

    return false;
}

// ═══════════════════════════════════════════════════
// MAIN CONVERSATION LOOP
// ═══════════════════════════════════════════════════

$turnCount = 0;
$maxTurns = 20;
$recordingDir = '/tmp/aps_voice/';

if (!is_dir($recordingDir)) {
    mkdir($recordingDir, 0777, true);
}

// Greeting
$greetingText = "Namaste! Main APS Dream Homes ka assistant hoon. Aapko property ke baare mein kya jaanna hai?";
$greetingFile = $recordingDir . "greeting_{$callId}.mp3";

agi_exec("VERBOSE APS Voice Bot starting for call {$callId}", 3);

if (textToSpeech($greetingText, $greetingFile)) {
    agi_stream_file($greetingFile);
}

// Conversation loop
while ($turnCount < $maxTurns) {
    $turnCount++;
    agi_exec("VERBOSE Turn {$turnCount}", 3);

    // Record customer speech
    $recordingFile = $recordingDir . "speech_{$callId}_{$turnCount}";
    agi_record_file($recordingFile, 'wav', '8000');

    // Convert speech to text (using Whisper or external STT)
    $speechText = '';
    $wavFile = $recordingFile . '.wav';

    if (file_exists($wavFile) && filesize($wavFile) > 1000) {
        // Try using Whisper API (or any STT service)
        // For now, we'll use DTMF as fallback
        $dtmfFile = $recordingFile . '.txt';
        if (file_exists($dtmfFile)) {
            $speechText = file_get_contents($dtmfFile);
        }
    }

    if (empty($speechText)) {
        // If no speech detected, play prompt again
        agi_stream_file($recordingDir . 'greeting_{$callId}.mp3');
        continue;
    }

    agi_exec("VERBOSE Customer said: {$speechText}", 3);

    // Check for goodbye
    $lower = strtolower($speechText);
    if (preg_match('/(bye|goodbye|tata|alvida|khuda)/', $lower)) {
        $byeText = "Dhanyavaad! APS Dream Homes ki taraf se shukriya. Aapka din shubh ho!";
        $byeFile = $recordingDir . "bye_{$callId}.mp3";
        if (textToSpeech($byeText, $byeFile)) {
            agi_stream_file($byeFile);
        }
        break;
    }

    // Get AI response
    $aiResponse = getAIResponse($speechText);
    agi_exec("VERBOSE AI: {$aiResponse}", 3);

    // Convert response to speech and play
    $responseFile = $recordingDir . "response_{$callId}_{$turnCount}.mp3";
    if (textToSpeech($aiResponse, $responseFile)) {
        agi_stream_file($responseFile);
    } else {
        // Fallback: say "I'm having issues, call us"
        agi_stream_file('demo-thanks');
    }
}

agi_exec("VERBOSE APS Voice Bot ended for call {$callId}", 3);

// Cleanup old recordings (older than 1 hour)
exec("find {$recordingDir} -name '*.mp3' -o -name '*.wav' -o -name '*.txt' | xargs -r find -mmin +60 -delete 2>/dev/null");

?>
