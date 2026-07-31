<?php

namespace App\Services;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class WebSocketServer implements MessageComponentInterface
{
    protected $clients;
    protected $notifier;
    protected $db;
    protected $jwtSecret;

    public function __construct($db)
    {
        $this->clients = new \SplObjectStorage;
        $this->db = $db;
        $this->notifier = new NotificationService($db);

        $secret = $_ENV['JWT_SECRET'] ?? getenv('JWT_SECRET') ?: null;
        if (!$secret || strlen($secret) < 32) {
            $secretFile = __DIR__ . '/../../storage/websocket_jwt_secret';
            if (is_readable($secretFile)) {
                $stored = trim((string)@file_get_contents($secretFile));
                if (strlen($stored) >= 32) {
                    $secret = $stored;
                }
            }
        }
        if (!$secret || strlen($secret) < 32) {
            $newSecret = bin2hex(random_bytes(32));
            $secretDir = __DIR__ . '/../../storage';
            if (is_dir($secretDir) || @mkdir($secretDir, 0775, true)) {
                @file_put_contents($secretDir . '/websocket_jwt_secret', $newSecret);
            }
            error_log("WebSocketServer: JWT_SECRET env not set or too short; generated 64-char fallback. Set JWT_SECRET in .env to a 32+ char string to use a stable secret.");
            $secret = $newSecret;
        }
        $this->jwtSecret = $secret;
        self::setInstance($this);
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // Store the new connection
        $this->clients->attach($conn);
        error_log("New WebSocket connection: {$conn->resourceId}");
        
        // Send connection established message
        $conn->send(json_encode([
            'type' => 'connection',
            'status' => 'connected',
            'message' => 'WebSocket connection established'
        ]));
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);
        if (!$data) {
            $from->send(json_encode(['error' => 'Invalid JSON']));
            return;
        }

        switch ($data['type'] ?? '') {
            case 'auth':
                $this->authenticateConnection($from, $data['token'] ?? '');
                break;
            case 'ping':
                $from->send(json_encode(['type' => 'pong', 'timestamp' => time()]));
                break;
            case 'get_notifications':
                $this->sendNotifications($from);
                break;
            case 'mark_read':
                $this->markNotificationsRead($from, $data['ids'] ?? []);
                break;
            case 'subscribe':
                $this->subscribeChannel($from, $data['channel'] ?? '');
                break;
            case 'unsubscribe':
                $this->unsubscribeChannel($from, $data['channel'] ?? '');
                break;
            default:
                $from->send(json_encode(['error' => 'Unknown message type']));
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        // Remove connection from clients
        $this->clients->detach($conn);
        error_log("WebSocket connection closed: {$conn->resourceId}");
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        error_log("WebSocket error: {$e->getMessage()}");
        $this->clients->detach($conn);
    }

    protected function authenticateConnection(ConnectionInterface $conn, $token)
    {
        try {
            // Decode JWT token to get user info
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            
            // Attach user info to connection
            $conn->userId = (int)$decoded->user_id;
            $conn->userRole = $decoded->role ?? 'user';
            
            $conn->send(json_encode([
                'type' => 'auth',
                'status' => 'success',
                'user_id' => $conn->userId,
                'role' => $conn->userRole
            ]));
        } catch (\Exception $e) {
            error_log("WebSocket auth error: {$e->getMessage()}");
            $conn->send(json_encode([
                'type' => 'auth',
                'status' => 'error',
                'message' => 'Invalid token'
            ]));
            $conn->close();
        }
    }

    protected function sendNotifications(ConnectionInterface $conn)
    {
        if (!isset($conn->userId) || !$conn->userId) {
            $conn->send(json_encode(['error' => 'Not authenticated']));
            return;
        }

        $notifications = $this->notifier->fetchPending($conn->userId);
        if (!empty($notifications)) {
            $ids = array_map(fn($n) => (int)$n['id'], $notifications);
            $this->notifier->markDelivered($ids);
            
            foreach ($notifications as $notification) {
                $conn->send(json_encode([
                    'type' => 'notification',
                    'data' => $notification
                ]));
            }
        }
    }

    protected function markNotificationsRead(ConnectionInterface $conn, $ids)
    {
        if (!isset($conn->userId) || !$conn->userId) {
            $conn->send(json_encode(['error' => 'Not authenticated']));
            return;
        }

        $count = 0;
        foreach ($ids as $id) {
            if ($this->notifier->markRead((int)$id)) $count++;
        }
        $conn->send(json_encode([
            'type' => 'mark_read_result',
            'count' => $count
        ]));
    }

    // Subscribe an authenticated client to a channel
    protected function subscribeChannel($conn, $channel)
    {
        if (!isset($conn->userId) || !$conn->userId) {
            $conn->send(json_encode(['type' => 'error', 'message' => 'Not authenticated']));
            return;
        }
        if (!is_string($channel) || $channel === '') {
            $conn->send(json_encode(['type' => 'error', 'message' => 'Invalid channel']));
            return;
        }
        if (!isset($conn->channels) || !is_array($conn->channels)) {
            $conn->channels = [];
        }
        if (!in_array($channel, $conn->channels, true)) {
            $conn->channels[] = $channel;
        }
        $conn->send(json_encode(['type' => 'subscribed', 'channel' => $channel]));
    }

    protected function unsubscribeChannel($conn, $channel)
    {
        if (!isset($conn->channels) || !is_array($conn->channels)) return;
        $conn->channels = array_values(array_filter($conn->channels, fn($c) => $c !== $channel));
        $conn->send(json_encode(['type' => 'unsubscribed', 'channel' => $channel]));
    }

    // Broadcast a notification to all connected clients
    public function broadcastNotification($notification)
    {
        foreach ($this->clients as $client) {
            // Skip unauthenticated connections
            if (!isset($client->userId) || !$client->userId) {
                continue;
            }

            // Check if notification is for this user
            if (isset($notification['user_id']) && $notification['user_id'] == $client->userId) {
                $client->send(json_encode([
                    'type' => 'notification',
                    'data' => $notification
                ]));
            }
            // Or if it's a global notification (no specific user)
            elseif (!isset($notification['user_id'])) {
                $client->send(json_encode([
                    'type' => 'notification',
                    'data' => $notification
                ]));
            }
        }
    }

    // Generic channel-based broadcast. Subscribers receive `{type: 'channel', channel, payload}`.
    // Supports:
    //   - 'all'                 → all authenticated clients
    //   - 'admin'               → role === 'admin'
    //   - 'user_{id}'           → specific user
    //   - 'role_{role}'         → specific role
    //   - 'chat_{id}'           → chat session
    //   - 'analytics_global'    → any analytics channel prefix (real-time dashboards)
    //   - 'kanban_global'       → kanban board broadcasts
    //   - anything else         → exact match against client->channels
    public function broadcast($channel, $payload, $targetUserId = null, $targetRole = null)
    {
        if (!is_string($channel) || $channel === '') return 0;
        $sent = 0;
        $envelope = json_encode([
            'type' => 'channel',
            'channel' => $channel,
            'payload' => $payload,
            'ts' => time()
        ]);
        foreach ($this->clients as $client) {
            if (!isset($client->userId) || !$client->userId) continue;
            if (!$this->matchesChannel($client, $channel, $targetUserId, $targetRole)) continue;
            try {
                $client->send($envelope);
                $sent++;
            } catch (\Throwable $e) {
                error_log("WebSocketServer::broadcast send error: " . $e->getMessage());
            }
        }
        return $sent;
    }

    private function matchesChannel($client, $channel, $targetUserId, $targetRole)
    {
        // Direct target overrides
        if ($targetUserId !== null && (int)$client->userId === (int)$targetUserId) return true;
        if ($targetRole !== null && (string)$client->userRole === (string)$targetRole) return true;

        if ($channel === 'all') return true;
        if ($channel === 'admin' && $client->userRole === 'admin') return true;
        if (strpos($channel, 'user_') === 0) {
            $id = (int)substr($channel, 5);
            return $id > 0 && (int)$client->userId === $id;
        }
        if (strpos($channel, 'role_') === 0) {
            $role = substr($channel, 5);
            return $client->userRole === $role;
        }

        // Check explicit subscriptions
        if (isset($client->channels) && is_array($client->channels)) {
            if (in_array($channel, $client->channels, true)) return true;
            // Wildcard match: e.g. 'analytics_*'
            foreach ($client->channels as $sub) {
                if (strpos($sub, '*') !== false) {
                    $regex = '/^' . str_replace('\*', '.*', preg_quote($sub, '/')) . '$/';
                    if (preg_match($regex, $channel)) return true;
                }
            }
        }
        return false;
    }

    // Static method to get/set instance for broadcasting from NotificationService
    private static $instance = null;
    
    public static function setInstance($instance)
    {
        self::$instance = $instance;
    }
    
    public static function getInstance()
    {
        return self::$instance;
    }

    // Test-only accessors (used by testing/test_websocket_full.php)
    public function getClientStorage(): \SplObjectStorage
    {
        return $this->clients;
    }
}
