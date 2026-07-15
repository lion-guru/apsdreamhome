#!/usr/bin/php -q
<?php
/**
 * APS Dream Home - AGI Script for AI Voice Agent
 * 
 * Called by Asterisk when a call connects to the AI agent context.
 * Reads AGI environment from stdin, processes conversation, returns commands.
 * 
 * Flow:
 * 1. Read AGI env vars (channel, callerid, etc.)
 * 2. Play greeting
 * 3. Loop: Listen → STT → LLM → TTS → Play response
 * 4. Log conversation to database
 * 5. Hangup
 */

// Read AGI environment variables from stdin
$agi = [];
while (!feof(STDIN)) {
    $line = trim(fgets(STDIN));
    if ($line === '') break;
    if (preg_match('/^agi_(\w+):\s*(.*)$/', $line, $m)) {
        $agi[$m[1]] = $m[2];
    }
}

// Read AGI command-line arguments
$argc = $_SERVER['argc'] ?? 0;
$argv = $_SERVER['argv'] ?? [];
$callId = $argv[1] ?? ($agi['argument_1'] ?? 'unknown');
$customerPhone = $argv[2] ?? ($agi['argument_2'] ?? 'unknown');
$sessionId = $argv[3] ?? ($agi['argument_3'] ?? '0');

$channel = $agi['channel'] ?? 'unknown';
$callerId = $agi['callerid'] ?? 'unknown';

// Config
$apiBridge = getenv('TELEPHONY_API_URL') ?: 'http://telephony-api:8080';
$logFile = '/var/log/asterisk/agi_ai_agent.log';

function agiLog(string $msg): void {
    global $logFile, $callId;
    $line = date('Y-m-d H:i:s') . " [{$callId}] {$msg}\n";
    @file_put_contents($logFile, $line, FILE_APPEND);
    error_log($line);
}

function agiCommand(string $cmd): string {
    // Write AGI command and read response
    echo $cmd . "\n";
    flush();
    $response = '';
    while (!feof(STDIN)) {
        $line = fgets(STDIN);
        if ($line === false) break;
        $response .= $line;
        if (preg_match('/^(\d+)\s/', $line, $m) && (int)$m[1] >= 200) break;
    }
    return trim($response);
}

agiLog("AGI started: Phone={$customerPhone}, Channel={$channel}");

// ── Step 1: Play greeting ──
agiLog("Playing greeting");
agiCommand("STREAM FILE /var/lib/asterisk/sounds/aps/hello_hi \"\"");
sleep(1);

// ── Step 2: Main conversation loop ──
$maxTurns = 10;
$turnCount = 0;
$conversationActive = true;

while ($conversationActive && $turnCount < $maxTurns) {
    $turnCount++;
    agiLog("Turn {$turnCount}");

    // Listen for customer speech (record audio)
    $audioFile = "/tmp/agi_audio_{$callId}_{$turnCount}.wav";
    agiCommand("RECORD FILE {$audioFile} wav \"\" 5000 15000");
    
    // Check if audio was recorded
    if (!file_exists($audioFile) || filesize($audioFile) < 1000) {
        agiLog("No audio recorded, trying DTMF fallback");
        
        // Try DTMF input as fallback
        $dtmf = agiCommand("WAIT FOR DIGIT 5000");
        $dtmfDigit = preg_replace('/.*?(\d).*/', '$1', $dtmf);
        
        if ($dtmfDigit && $dtmfDigit !== '0') {
            // Map DTMF to common responses
            $dtmfMap = [
                '1' => 'I want to know about prices',
                '2' => 'I want to visit the site',
                '3' => 'I want to book a plot',
                '9' => 'Goodbye',
            ];
            $userInput = $dtmfMap[$dtmfDigit] ?? "I pressed {$dtmfDigit}";
        } else {
            agiLog("No input received, ending call");
            $conversationActive = false;
            break;
        }
    } else {
        // Send audio to Whisper for transcription
        agiLog("Transcribing audio: {$audioFile}");
        $transcribeResult = transcribeAudio($apiBridge, $audioFile);
        
        if ($transcribeResult['success'] && !empty($transcribeResult['text'])) {
            $userInput = $transcribeResult['text'];
            agiLog("Customer said: {$userInput}");
        } else {
            agiLog("Transcription failed, asking to repeat");
            agiCommand("STREAM FILE /var/lib/asterisk/sounds/aps/repeat \"\"");
            continue;
        }
    }

    // Check for goodbye
    if (preg_match('/(bye|goodbye|alvida|chalo|hunga|done)/i', $userInput)) {
        agiLog("Customer said goodbye");
        $conversationActive = false;
    }

    // Get AI response
    agiLog("Getting AI response for: {$userInput}");
    $aiResult = getAIResponse($apiBridge, $sessionId, $userInput);
    
    if ($aiResult['success']) {
        $responseText = $aiResult['response'];
        $intent = $aiResult['intent'] ?? 'general';
        agiLog("AI response: {$responseText} (intent: {$intent})");
        
        // Convert text to speech and play
        $ttsFile = "/tmp/agi_tts_{$callId}_{$turnCount}.wav";
        $ttsSuccess = generateTTS($responseText, $ttsFile);
        
        if ($ttsSuccess && file_exists($ttsFile)) {
            agiCommand("STREAM FILE {$ttsFile} \"\"");
        } else {
            // Fallback: use Asterisk's built-in TTS
            agiCommand("SAY TEXT \"{$responseText}\" \"hi\"");
        }
        
        // Check if AI wants to end call
        if ($intent === 'disinterest' || $intent === 'transfer_to_sales') {
            $conversationActive = false;
        }
    } else {
        agiLog("AI response failed: " . ($aiResult['error'] ?? 'unknown'));
        agiCommand("STREAM FILE /var/lib/asterisk/sounds/aps/fallback \"\"");
    }

    // Small delay between turns
    usleep(500000);
}

// ── Step 3: Ending call ──
agiLog("Call ending after {$turnCount} turns");

// Play goodbye
agiCommand("STREAM FILE /var/lib/asterisk/sounds/aps/goodbye_hi \"\"");
sleep(1);

// Log final status
agiLog("Call completed successfully");
exit(0);

// ── Helper Functions ──

function transcribeAudio(string $apiUrl, string $audioFile): array {
    $ch = curl_init("{$apiUrl}/api/whisper/transcribe");
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => ['audio_file' => new CURLFile($audioFile)],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return json_decode($response, true) ?: ['success' => false];
    }
    return ['success' => false, 'error' => "HTTP {$httpCode}"];
}

function getAIResponse(string $apiUrl, string $sessionId, string $userInput): array {
    $payload = json_encode([
        'session_id' => $sessionId,
        'user_input' => $userInput,
        'input_type' => 'audio',
    ]);
    
    $ch = curl_init("{$apiUrl}/api/ai/conversation");
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return json_decode($response, true) ?: ['success' => false];
    }
    return ['success' => false, 'error' => "HTTP {$httpCode}"];
}

function generateTTS(string $text, string $outputFile): bool {
    // Try Google TTS (free)
    $lang = 'hi-IN';
    $url = "https://translate.google.com/translate_tts?ie=UTF-8&tl={$lang}&client=tw-ob&q=" . urlencode($text);
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_USERAGENT => 'Mozilla/5.0',
    ]);
    $audio = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $audio && strlen($audio) > 100) {
        file_put_contents($outputFile, $audio);
        return true;
    }
    
    // Fallback: try eSpeak
    $escapedText = escapeshellarg($text);
    exec("espeak -v hi -w {$outputFile} {$escapedText} 2>/dev/null", $output, $returnCode);
    return $returnCode === 0 && file_exists($outputFile);
}
