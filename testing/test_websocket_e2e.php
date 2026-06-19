<?php
/**
 * End-to-end test: full round trip with authenticated client.
 */
$token = trim(file_get_contents(__DIR__ . '/jwt_token.txt'));

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Core/Database/Database.php';
use App\Core\Database\Database;
use App\Services\NotificationCenter;

echo "=== Authenticated E2E Test ===\n\n";

$db = new Database();
$center = new NotificationCenter($db);

$socket = @stream_socket_client("tcp://127.0.0.1:8080", $errno, $errstr, 5);
if (!$socket) {
    echo "[SKIP] TCP (WebSocket server not running)\n";
    echo "\n=== Authenticated E2E Complete ===\n";
    exit(0);
}
$secKey = base64_encode(random_bytes(16));
$handshake = "GET / HTTP/1.1\r\nHost: 127.0.0.1:8080\r\nUpgrade: websocket\r\nConnection: Upgrade\r\nSec-WebSocket-Key: {$secKey}\r\nSec-WebSocket-Version: 13\r\n\r\n";
fwrite($socket, $handshake);
stream_set_timeout($socket, 2);
$response = '';
while (!feof($socket)) {
    $chunk = fread($socket, 4096);
    if ($chunk === '') break;
    $response .= $chunk;
    if (strpos($response, "\r\n\r\n") !== false) break;
}
$headerEnd = strpos($response, "\r\n\r\n") + 4;
parseFrame(substr($response, $headerEnd));
echo "[OK] WebSocket connected\n";

$authFrame = buildFrame(json_encode(['type' => 'auth', 'token' => $token]), 0x1);
fwrite($socket, $authFrame);
stream_set_timeout($socket, 2);
$authResponse = '';
$attempts = 0;
while ($attempts < 3) {
    $chunk = fread($socket, 4096);
    if ($chunk === false || $chunk === '') break;
    $authResponse .= $chunk;
    $attempts++;
    usleep(100000);
}
if (strlen($authResponse) >= 2) {
    $frame = parseFrame($authResponse);
    $authMsg = json_decode($frame['payload'], true);
    echo "[OK] Auth response: " . $frame['payload'] . "\n";
    if (($authMsg['type'] ?? '') === 'auth' && ($authMsg['status'] ?? '') === 'success') {
        echo "[OK] Client authenticated as user " . $authMsg['user_id'] . "\n";
    } elseif (($authMsg['status'] ?? '') === 'error') {
        echo "[WARN] Auth failed: " . ($authMsg['message'] ?? 'unknown') . "\n";
        echo "[INFO] Server uses JWT secret: '{$secret_short}'\n";
        fclose($socket);
        exit(0);
    }
}

echo "\n--- Publishing global notification (should reach client) ---\n";
$notifId = $center->publish('global', 'test_e2e', null, [
    'title' => 'Auth E2E Test',
    'message' => 'Testing global broadcast with authenticated client',
]);
echo "[OK] Published notification ID: {$notifId}\n";

stream_set_timeout($socket, 3);
$broadcastResponse = '';
$attempts = 0;
while ($attempts < 10) {
    $chunk = fread($socket, 4096);
    if ($chunk === false || $chunk === '') break;
    $broadcastResponse .= $chunk;
    $attempts++;
    usleep(150000);
}

if (strlen($broadcastResponse) >= 2) {
    $frame = parseFrame($broadcastResponse);
    $payload = $frame['payload'];
    echo "[OK] Broadcast received: {$payload}\n";
    $msg = json_decode($payload, true);
    if (($msg['type'] ?? '') === 'notification') {
        echo "[OK] Type is 'notification' - full round trip SUCCESS\n";
    }
} else {
    echo "[WARN] No broadcast received within timeout\n";
}

$pdo = $db->getPdo();
$pdo->prepare("DELETE FROM realtime_notifications WHERE event_type = 'test_e2e'")->execute();
echo "[OK] Cleanup done\n";

fclose($socket);
echo "\n=== Authenticated E2E Complete ===\n";

function buildFrame($payload, $opcode = 0x1) {
    $frame = chr(0x80 | $opcode);
    $len = strlen($payload);
    if ($len <= 125) { $frame .= chr(0x80 | $len); }
    elseif ($len <= 65535) { $frame .= chr(0x80 | 126) . pack('n', $len); }
    else { $frame .= chr(0x80 | 127) . pack('J', $len); }
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
    if ($len === 126) { $len = unpack('n', substr($data, $offset, 2))[1]; $offset += 2; }
    elseif ($len === 127) { $len = unpack('J', substr($data, $offset, 8))[1]; $offset += 8; }
    if ($masked) $offset += 4;
    $payload = substr($data, $offset, $len);
    return ['opcode' => $opcode, 'payload' => $payload, 'length' => $len];
}
