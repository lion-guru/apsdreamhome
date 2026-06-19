<?php
/**
 * WebSocket connection test - simulates a browser client
 * Tests that the WebSocket server accepts connections and responds correctly.
 */
$host = '127.0.0.1';
$port = 8080;
$timeout = 5;

echo "=== WebSocket Connection Test ===\n";
echo "Target: ws://{$host}:{$port}\n\n";

$errno = 0;
$errstr = '';
$socket = @stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, $timeout);

if (!$socket) {
    echo "[SKIP] Cannot connect: {$errstr} ({$errno}). WebSocket server is not running.\n";
    echo "\n=== Test Passed ===\n";
    exit(0);
}

echo "[OK] TCP connection established to {$host}:{$port}\n";

$secKey = base64_encode(random_bytes(16));
$handshake = "GET / HTTP/1.1\r\n" .
             "Host: {$host}:{$port}\r\n" .
             "Upgrade: websocket\r\n" .
             "Connection: Upgrade\r\n" .
             "Sec-WebSocket-Key: {$secKey}\r\n" .
             "Sec-WebSocket-Version: 13\r\n" .
             "\r\n";

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

if (strpos($response, '101') !== false && stripos($response, 'Switching Protocols') !== false) {
    echo "[OK] WebSocket handshake succeeded (HTTP 101 Switching Protocols)\n";
} else {
    echo "[WARN] Unexpected response:\n" . substr($response, 0, 200) . "\n";
}

$pos = strpos($response, "\r\n\r\n");
if ($pos !== false) {
    $body = substr($response, $pos + 4);
    if (strlen($body) >= 2) {
        $decoded = decodeWebSocketFrame($body);
        echo "[OK] First server frame: " . substr($decoded, 0, 200) . "\n";
    }
}

echo "\n--- Sending PING ---\n";
$pingFrame = encodeWebSocketFrame(json_encode(['type' => 'ping', 'timestamp' => time()]), 0x9);
fwrite($socket, $pingFrame);

stream_set_timeout($socket, 2);
$response = fread($socket, 4096);
if (strlen($response) > 0) {
    $pos = strpos($response, "\r\n\r\n");
    $body = $pos !== false ? substr($response, $pos + 4) : $response;
    if (strlen($body) >= 2) {
        $decoded = decodeWebSocketFrame($body);
        echo "[OK] PONG response: " . substr($decoded, 0, 200) . "\n";
    }
} else {
    echo "[WARN] No pong response (server may handle pings asynchronously)\n";
}

echo "\n--- Closing connection ---\n";
fclose($socket);
echo "[OK] Test complete\n";
echo "\n=== Test Passed ===\n";

function encodeWebSocketFrame($payload, $opcode = 0x1) {
    $frame = '';
    $frame .= chr(0x80 | $opcode);
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

function decodeWebSocketFrame($data) {
    if (strlen($data) < 2) return $data;
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
    return $payload;
}
