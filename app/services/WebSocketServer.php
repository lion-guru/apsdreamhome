<?php

namespace App\Services;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class WebSocketServer implements MessageComponentInterface
{
    protected $clients;
    protected $notificationCenter;
    protected $db;
    protected $jwtSecret;

    public function __construct($db)
    {
        $this->clients = new \SplObjectStorage;
        $this->db = $db;
        $this->notificationCenter = new NotificationCenter($db);

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

        $notifications = $this->notificationCenter->fetchPending($conn->userId);
        if (!empty($notifications)) {
            $ids = array_map(fn($n) => (int)$n['id'], $notifications);
            $this->notificationCenter->markDelivered($ids);
            
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

        $count = $this->notificationCenter->markRead($conn->userId, $ids);
        $conn->send(json_encode([
            'type' => 'mark_read_result',
            'count' => $count
        ]));
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
    
    // Static method to get/set instance for broadcasting from NotificationCenter
    private static $instance = null;
    
    public static function setInstance($instance)
    {
        self::$instance = $instance;
    }
    
    public static function getInstance()
    {
        return self::$instance;
    }
}
