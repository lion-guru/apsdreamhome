<?php
$BASE = 'http://localhost/apsdreamhome';

function chatMsg($msg, $sid) {
    global $BASE;
    $ch = curl_init("{$BASE}/api/ai/chat");
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode(['message' => $msg, 'session_id' => $sid]),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
    ]);
    $r = curl_exec($ch);
    curl_close($ch);
    return json_decode($r, true);
}

echo "=== Search Property Flow ===\n";
$sid = 'search_' . time();

$r = chatMsg('property chahiye Gorakhpur mein', $sid);
echo "[1] Start: action=" . ($r['conversation_state']['action'] ?? 'none') . "\n";
echo "    " . substr($r['response'], 0, 100) . "\n";

$r = chatMsg('Gorakhpur', $sid);
echo "[2] Location: step=" . ($r['conversation_state']['step'] ?? '?') . "\n";

$r = chatMsg('50', $sid);
echo "[3] Budget: step=" . ($r['conversation_state']['step'] ?? '?') . "\n";

$r = chatMsg('Plot', $sid);
echo "[4] Type: step=" . ($r['conversation_state']['step'] ?? '?') . "\n";
echo "    Model: " . ($r['model'] ?? 'none') . "\n";
echo "    Response:\n" . $r['response'] . "\n";
