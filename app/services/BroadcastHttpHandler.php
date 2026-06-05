<?php

namespace App\Services;

use Ratchet\Http\HttpServerInterface;
use Ratchet\ConnectionInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Cross-process broadcast HTTP endpoint.
 *
 * Runs on port 8081 inside the same Ratchet event loop as the WebSocket server.
 * Accepts POST /broadcast with {channel, payload, userId?, role?} JSON body
 * and calls WebSocketServer::broadcast() in-process (zero serialization).
 *
 * Auth: shared secret in `X-Broadcast-Key` header (must match `WS_BROADCAST_KEY` env var).
 * Failures here MUST NOT throw - any error is logged and an HTTP response is always written.
 *
 * Follows Ratchet's HttpServerInterface pattern: process request in onOpen, write
 * response to the connection, then close.
 */
class BroadcastHttpHandler implements HttpServerInterface
{
    private $wsServer;
    private $sharedKey;

    public function __construct(WebSocketServer $wsServer)
    {
        $this->wsServer = $wsServer;
        $this->sharedKey = $_ENV['WS_BROADCAST_KEY']
            ?? getenv('WS_BROADCAST_KEY')
            ?: 'dev-broadcast-key';
    }

    public function onOpen(ConnectionInterface $conn, RequestInterface $request = null)
    {
        $method = $request ? $request->getMethod() : 'GET';
        $path = $request ? $request->getUri()->getPath() : '/';
        $rawHeaders = $request ? $request->getHeaders() : [];

        $code = 200;
        $payload = ['success' => true, 'sent' => 0];

        try {
            if ($method !== 'POST' || $path !== '/broadcast') {
                $code = 404;
                $payload = ['error' => 'Not found'];
            } else {
                // Auth check (PSR-7 normalizes header names to lowercase)
                $key = '';
                $keyHeader = $rawHeaders['X-Broadcast-Key'] ?? $rawHeaders['x-broadcast-key'] ?? null;
                if ($keyHeader !== null) {
                    $key = is_array($keyHeader) ? ($keyHeader[0] ?? '') : (string)$keyHeader;
                }
                if (!hash_equals((string)$this->sharedKey, (string)$key)) {
                    if (defined('DEBUG_MODE') && DEBUG_MODE) {
                        error_log("BroadcastHttpHandler auth failed; remote=" . ($conn->remoteAddress ?? '?'));
                    }
                    $code = 401;
                    $payload = ['error' => 'Unauthorized'];
                } else {
                    $body = (string)$request->getBody();
                    $data = json_decode($body, true);
                    if (!is_array($data) || empty($data['channel'])) {
                        $code = 400;
                        $payload = ['error' => 'channel required'];
                    } else {
                        $sent = $this->wsServer->broadcast(
                            (string)$data['channel'],
                            $data['payload'] ?? null,
                            isset($data['userId']) ? (int)$data['userId'] : null,
                            isset($data['role']) ? (string)$data['role'] : null
                        );
                        $payload = ['success' => true, 'sent' => (int)$sent];
                    }
                }
            }
        } catch (\Throwable $e) {
            error_log("BroadcastHttpHandler::onOpen error: " . $e->getMessage());
            $code = 500;
            $payload = ['success' => false, 'error' => 'internal'];
        }

        $body = json_encode($payload);
        $conn->send("HTTP/1.1 {$code} OK\r\n");
        $conn->send("Content-Type: application/json\r\n");
        $conn->send("Content-Length: " . strlen($body) . "\r\n");
        $conn->send("Access-Control-Allow-Origin: *\r\n");
        $conn->send("Connection: close\r\n");
        $conn->send("\r\n");
        $conn->send($body);
        $conn->close();
    }

    public function onMessage(ConnectionInterface $from, $msg) { /* not used */ }
    public function onClose(ConnectionInterface $conn) { /* not used */ }
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        error_log("BroadcastHttpHandler error: " . $e->getMessage());
    }
}
