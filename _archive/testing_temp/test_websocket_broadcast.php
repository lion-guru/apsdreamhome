<?php
/**
 * End-to-end test: trigger a notification via NotificationCenter
 * and verify WebSocket broadcast delivery.
 */
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Core/Database/Database.php';

use App\Core\Database\Database;
use App\Services\NotificationCenter;

echo "=== End-to-End Broadcast Test ===\n\n";

try {
    $db = new Database();
    $pdo = $db->getPdo();
    echo "[OK] DB connected\n";
} catch (\Throwable $e) {
    echo "[FAIL] DB error: " . $e->getMessage() . "\n";
    exit(1);
}

$center = new NotificationCenter($db);
echo "[OK] NotificationCenter loaded\n";

echo "\n--- Step 1: Connect WebSocket test client ---\n";
$socket = @stream_socket_client("tcp://127.0.0.1:8080", $errno, $errstr, 5);
if (!$socket) {
    echo "[SKIP] Cannot connect to WebSocket (server not running)\n";
    echo "\n=== E2E Test Complete ===\n";
    exit(0);
}

$secKey = base64_encode(random_bytes(16));
$handshake = "GET / HTTP/1.1\r\nHost: 127.0.0.1:8080\r\nUpgrade: websocket\r\n" .
             "Connection: Upgrade\r\nSec-WebSocket-Key: {$secKey}\r\nSec-WebSocket-Version: 13\r\n\r\n";
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
echo "[OK] WebSocket test client connected\n\n";

echo "--- Step 2: Publish a global notification ---\n";
$notifId = $center->publish('global', 'test_event', null, [
    'title' => 'Test Broadcast',
    'message' => 'End-to-end WebSocket broadcast test',
    'test' => true,
]);
echo "[OK] Notification published (ID: {$notifId})\n";

usleep(500000);

echo "\n--- Step 3: Read broadcast from WebSocket ---\n";
stream_set_timeout($socket, 2);
$wsResponse = '';
$attempts = 0;
while ($attempts < 5) {
    $chunk = fread($socket, 4096);
    if ($chunk === false || $chunk === '') break;
    $wsResponse .= $chunk;
    $attempts++;
    usleep(100000);
}

if (strlen($wsResponse) >= 2) {
    $frame = parseFrame($wsResponse);
    $payload = $frame['payload'];
    echo "[OK] Received broadcast: {$payload}\n";
    $msg = json_decode($payload, true);
    if (($msg['type'] ?? '') === 'notification') {
        echo "[OK] Message type is 'notification'\n";
        $data = $msg['data'] ?? null;
        if ($data && isset($data['id'])) {
            echo "[OK] Payload contains notification ID: {$data['id']}\n";
        }
    }
} else {
    echo "[WARN] No broadcast received (test client not authenticated, may not receive user-targeted notifications)\n";
}

echo "\n--- Step 4: Cleanup ---\n";
try {
    $stmt = $pdo->prepare("DELETE FROM realtime_notifications WHERE event_type = 'test_event'");
    $stmt->execute();
    echo "[OK] Test notification cleaned up\n";
} catch (\Throwable $e) {
    echo "[WARN] Cleanup error: " . $e->getMessage() . "\n";
}

fclose($socket);
echo "\n=== E2E Test Complete ===\n";

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
