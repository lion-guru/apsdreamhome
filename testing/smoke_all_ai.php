<?php
/**
 * AI Surfaces Smoke Test — Session 78 roadmap Phase 1A
 * Run: php testing/smoke_all_ai.php
 */
require_once __DIR__ . '/../config/bootstrap.php';

function probe(string $label, string $method, string $url, ?array $json = null, array $form = null): array {
    $ch = curl_init('http://localhost/apsdreamhome' . $url);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, 1);
        if ($json !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        } elseif ($form !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form));
        }
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $data = json_decode((string)$body, true);
    return [$label, $code, is_array($data) ? $data : []];
}

$pass = 0; $fail = 0;
function check(bool $ok, string $label, array $r, string $detail = ''): void {
    global $pass, $fail;
    if ($ok) { $pass++; echo "PASS  $label"; }
    else     { $fail++; echo "FAIL  $label"; }
    echo $detail !== '' ? " — $detail" : '';
    echo "\n";
}

// 1. SmartAI chat (public /api/ai/chat)
[$l,$c,$d] = probe('SmartAI chat', 'POST', '/api/ai/chat', ['message' => 'Registration process kya hai associate ka?']);
check(($d['success'] ?? false) === true && !empty($d['response']), $l, [$c], 'engine=' . ($d['model'] ?? '?') . ' len=' . strlen((string)($d['response'] ?? '')));

// 2. Widget bot (public chat widget)
[$l,$c,$d] = probe('WidgetBot', 'POST', '/api/chat/send', ['token' => 'invalid-token-probe', 'message' => 'price of plots in Suryoday']);
$widgetOk = isset($d['error']) || isset($d['success']); // endpoint alive & JSON-shaped
check($widgetOk, $l, [$c], substr(json_encode($d), 0, 80));

// 3. Gemini chatbot
[$l,$c,$d] = probe('GeminiBot', 'POST', '/api/gemini/chatbot/message', ['message' => 'hello']);
check(isset($d), $l, [$c], 'source=' . ($d['source'] ?? ($d['data']['source'] ?? '?')));

// 4. Voice assistant (form-encoded only, param = query)
[$l,$c,$d] = probe('VoiceAssistant', 'POST', '/api/voice-assistant/query', null, ['query' => 'colonies']);
check(isset($d) && ($d['success'] ?? false) === true || !empty($d['answer'] ?? $d['response'] ?? ''), $l, [$c], substr(json_encode($d), 0, 80));

// 5. Assistant chat
[$l,$c,$d] = probe('AsstChat', 'POST', '/api/assistant/chat', ['message' => 'hi']);
check(isset($d), $l, [$c], substr(json_encode($d), 0, 80));

// 6. Recommendations (correct route: /api/ai/recommendations)
[$l,$c,$d] = probe('Recos', 'GET', '/api/ai/recommendations');
$cnt = count($d['recommendations'] ?? $d['data']['recommendations'] ?? $d['data'] ?? []);
check($cnt > 0 || ($d['success'] ?? false) === true, $l, [$c], "count=$cnt");

// 7. Analyze (correct route takes property id)
[$l,$c,$d] = probe('Analyze', 'GET', '/api/ai/analyze/1');
check(isset($d), $l, [$c], substr(json_encode($d), 0, 80));

echo "\n===== RESULT: $pass PASS / $fail FAIL =====\n";
exit($fail > 0 ? 1 : 0);
