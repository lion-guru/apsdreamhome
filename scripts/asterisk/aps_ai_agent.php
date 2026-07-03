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
 */

// Guard: STDIN only exists inside Asterisk AGI context
if (!defined('STDIN') || !defined('STDOUT')) {
    fwrite(STDERR, "Error: This script must be run by Asterisk AGI, not directly.\n");
    exit(1);
}

// Read Asterisk AGI environment variables from STDIN
$agi = [];
while ($line = fgets(STDIN)) {
    $line = trim($line);
    if ($line === '') break;
    if (preg_match('/^AGI (\w[\w\s]*?):\s*(.*)$/', $line, $m)) {
        $agi[trim($m[1])] = trim($m[2]);
    }
}

// AGI arguments from dialplan
$callId = $argv[1] ?? 'unknown';
$customerPhone = $argv[2] ?? 'unknown';

$apiBase = 'http://127.0.0.1/apsdreamhome';
$chatbotEndpoint = $apiBase . '/api/ai/chat';

error_log("[APS-AGI] Call started: ID={$callId}, Phone={$customerPhone}");

function agi_exec(string $cmd): string
{
    echo $cmd . "\n";
    fflush(STDOUT);
    $response = fgets(STDIN);
    return trim($response ?? '');
}

function agi_stream_file(string $file, string $digits = ''): string
{
    $digits = escapeshellarg($digits);
    return agi_exec("STREAM FILE {$file} {$digits}");
}

function agi_record_file(string $file, string $format = 'wav', string $timeout = '8000'): string
{
    return agi_exec("RECORD FILE {$file} {$format} \"#\" {$timeout} 0 120000 1");
}

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
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        return "Sorry, I'm having technical issues. Please call 919277121112.";
    }

    $data = json_decode($response, true);
    return $data['response'] ?? $data['reply'] ?? "I didn't understand. Please try again.";
}

function textToSpeech(string $text, string $outputFile): bool
{
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
    return false;
}

// ═══════════════════════════════════════════════════
// MAIN CONVERSATION LOOP
// ═══════════════════════════════════════════════════

$turnCount = 0;
$maxTurns = 20;
$recordingDir = '/tmp/aps_voice/';
if (!is_dir($recordingDir)) mkdir($recordingDir, 0777, true);

agi_exec("VERBOSE APS Voice Bot starting for call {$callId}", 3);

// Greeting
$greetingText = "Namaste! Main APS Dream Homes ka assistant hoon. Aapko property ke baare mein kya jaanna hai?";
$greetingFile = $recordingDir . "greeting_{$callId}.mp3";
if (textToSpeech($greetingText, $greetingFile)) {
    agi_stream_file($greetingFile);
}

while ($turnCount < $maxTurns) {
    $turnCount++;

    $recordingFile = $recordingDir . "speech_{$callId}_{$turnCount}";
    agi_record_file($recordingFile, 'wav', '8000');

    $wavFile = $recordingFile . '.wav';
    $speechText = '';
    if (file_exists($wavFile) && filesize($wavFile) > 1000) {
        $dtmfFile = $recordingFile . '.txt';
        if (file_exists($dtmfFile)) {
            $speechText = file_get_contents($dtmfFile);
        }
    }

    if (empty($speechText)) continue;

    $lower = strtolower($speechText);
    if (preg_match('/(bye|goodbye|tata|alvida|khuda)/', $lower)) {
        $byeFile = $recordingDir . "bye_{$callId}.mp3";
        if (textToSpeech("Dhanyavaad! APS Dream Homes ki taraf se shukriya. Aapka din shubh ho!", $byeFile)) {
            agi_stream_file($byeFile);
        }
        break;
    }

    $aiResponse = getAIResponse($speechText);
    $responseFile = $recordingDir . "response_{$callId}_{$turnCount}.mp3";
    if (textToSpeech($aiResponse, $responseFile)) {
        agi_stream_file($responseFile);
    }
}

agi_exec("VERBOSE APS Voice Bot ended for call {$callId}", 3);
