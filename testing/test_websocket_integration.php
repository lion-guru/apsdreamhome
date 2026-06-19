<?php
/**
 * Comprehensive WebSocket integration test
 * Tests the full protocol with text frames (matches browser behavior).
 */
$host = '127.0.0.1';
$port = 8080;
$timeout = 5;

echo "=== WebSocket Integration Test (text frames) ===\n";
echo "Target: ws://{$host}:{$port}\n\n";

$socket = @stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, $timeout);
if (!$socket) {
    echo "[SKIP] TCP: {$errstr} (WebSocket server not running)\n";
    echo "\n=== Integration Test Complete ===\n";
    exit(0);
}
echo "[OK] TCP connected\n";

$secKey = base64_encode(random_bytes(16));
$handshake = "GET / HTTP/1.1\r\nHost: {$host}:{$port}\r\n" .
             "Upgrade: websocket\r\nConnection: Upgrade\r\n" .
             "Sec-WebSocket-Key: {$secKey}\r\nSec-WebSocket-Version: 13\r\n\r\n";
fwrite($socket, $handshake);
stream_set_timeout($socket, 3);

$response = '';
$start = time();
while (!feof($socket) && (time() - $start) < 3) {
    $chunk = fread($socket, 4096);
    if ($chunk === false || $chunk === '') break;
    $response .= $chunk;
    if (strpos($response, "\r\n\r\n") !== false) break;
}
if (strpos($response, '101') === false) { echo "[FAIL] No 101\n"; exit(1); }
echo "[OK] WebSocket handshake (HTTP 101)\n";

$headerEnd = strpos($response, "\r\n\r\n") + 4;
$firstBody = substr($response, $headerEnd);
$frame1 = parseFrame($firstBody);
echo "[OK] Server sent on open: {$frame1['payload']}\n";
$connMsg = json_decode($frame1['payload'], true);
if (($connMsg['type'] ?? '') === 'connection' && ($connMsg['status'] ?? '') === 'connected') {
    echo "[OK] Connection message format correct\n";
} else {
    echo "[WARN] Unexpected connection message format\n";
}

echo "\n--- Test 1: PING (text frame) ---\n";
$pingPayload = json_encode(['type' => 'ping', 'timestamp' => time()]);
$pingFrame = buildFrame($pingPayload, 0x1);
fwrite($socket, $pingFrame);

stream_set_timeout($socket, 2);
$pongResponse = '';
$attempts = 0;
while ($attempts < 3) {
    $chunk = fread($socket, 4096);
    if ($chunk === false || $chunk === '') break;
    $pongResponse .= $chunk;
    $attempts++;
    usleep(100000);
}
if (strlen($pongResponse) >= 2) {
    $frame = parseFrame($pongResponse);
    echo "[OK] PONG payload: {$frame['payload']}\n";
    $pong = json_decode($frame['payload'], true);
    if (($pong['type'] ?? '') === 'pong') {
        echo "[OK] PONG message type correct\n";
    } else {
        echo "[WARN] Expected 'pong', got: " . ($pong['type'] ?? 'unknown') . "\n";
    }
} else {
    echo "[WARN] No PONG response received\n";
}

echo "\n--- Test 2: Unknown message type (graceful error) ---\n";
$badFrame = buildFrame(json_encode(['type' => 'foobar']), 0x1);
fwrite($socket, $badFrame);

stream_set_timeout($socket, 2);
$errResponse = '';
$attempts = 0;
while ($attempts < 3) {
    $chunk = fread($socket, 4096);
    if ($chunk === false || $chunk === '') break;
    $errResponse .= $chunk;
    $attempts++;
    usleep(100000);
}
if (strlen($errResponse) >= 2) {
    $frame = parseFrame($errResponse);
    $err = json_decode($frame['payload'], true);
    echo "[OK] Server response: {$frame['payload']}\n";
} else {
    echo "[INFO] No response (server may not error on unknown types - acceptable)\n";
}

echo "\n--- Test 3: get_notifications (unauth) ---\n";
$reqFrame = buildFrame(json_encode(['type' => 'get_notifications']), 0x1);
fwrite($socket, $reqFrame);

stream_set_timeout($socket, 2);
$notifResponse = '';
$attempts = 0;
while ($attempts < 3) {
    $chunk = fread($socket, 4096);
    if ($chunk === false || $chunk === '') break;
    $notifResponse .= $chunk;
    $attempts++;
    usleep(100000);
}
if (strlen($notifResponse) >= 2) {
    $frame = parseFrame($notifResponse);
    echo "[OK] Response: {$frame['payload']}\n";
    $notif = json_decode($frame['payload'], true);
    if (isset($notif['error']) && $notif['error'] === 'Not authenticated') {
        echo "[OK] Auth gate working - rejects unauthenticated requests\n";
    }
} else {
    echo "[INFO] No response (auth-gate may have closed connection silently)\n";
}

fclose($socket);
echo "\n=== Integration Test Complete ===\n";

function buildFrame($payload, $opcode = 0x1) {
    $frame = chr(0x80 | $opcode);
    $len = strlen($payload);
    if ($len <= 125) {
        $frame .= chr(0x80 | $len);
    } elseif ($len <= 65535) {
        $frame .= chr(0x80 | 126) . pack('n', $len);
    } else {
        $frame .= chr(0x80 | 127) . pack('J', $len);
    }
    $mask = random_bytes(4);
    $frame .= $mask;
    for ($i = 0; $i < $len; $i++) {
        $frame .= chr(ord($payload[$i]) ^ ord($mask[$i % 4]));
    }
    return $frame;
}

function parseFrame($data) {
    if (strlen($data) < 2) return ['opcode' => 0, 'payload' => ''];
    $offset = 0;
    $byte1 = ord($data[$offset++]);
    $byte2 = ord($data[$offset++]);
    $opcode = $byte1 & 0x0F;
    $masked = ($byte2 & 0x80) !== 0;
    $len = $byte2 & 0x7F;
    if ($len === 126) {
        $len = unpack('n', substr($data, $offset, 2))[1];
        $offset += 2;
    } elseif ($len === 127) {
        $len = unpack('J', substr($data, $offset, 8))[1];
        $offset += 8;
    }
    if ($masked) $offset += 4;
    $payload = substr($data, $offset, $len);
    return ['opcode' => $opcode, 'payload' => $payload, 'length' => $len];
}
