<?php
/**
 * WebSocket Server for Real-time HR Dashboard Updates
 * Uses Ratchet (cboden/ratchet) for WebSocket server
 * 
 * Run: php scripts/websocket_server.php
 * 
 * Connects to the existing notification system to push real-time updates
 * to connected clients (admin dashboards, employee portals, etc.)
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Core/Config.php';

// Bootstrap the application to access DB and services
$appRoot = __DIR__ . '/..';
require_once $appRoot . '/app/Core/Database/Database.php';
require_once $appRoot . '/app/Core/Config.php';

class HRWebSocketServer implements MessageComponentInterface {
    protected $clients;
    protected $userConnections; // userId => [connectionId => ConnectionInterface]
    protected $db;
    protected $tenantId = 1;

    public function __construct() {
        $this->clients = new \SplObjectStorage();
        $this->userConnections = [];
        
        // Initialize database connection
        $this->db = \App\Core\Database\Database::getInstance()->getConnection();
        
        echo "HR WebSocket Server initialized\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the connection
        $this->clients->attach($conn);
        echo "New connection: {$conn->resourceId}\n";
        
        // Send welcome message
        $conn->send(json_encode([
            'type' => 'welcome',
            'message' => 'Connected to APS Dream Home HR WebSocket Server',
            'timestamp' => date('c'),
            'connection_id' => $conn->resourceId
        ]));
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        
        if (!$data) {
            $from->send(json_encode(['type' => 'error', 'message' => 'Invalid JSON']));
            return;
        }

        switch ($data['type'] ?? '') {
            case 'authenticate':
                $this->handleAuthentication($from, $data);
                break;
            case 'subscribe':
                $this->handleSubscription($from, $data);
                break;
            case 'unsubscribe':
                $this->handleUnsubscription($from, $data);
                break;
            case 'ping':
                $from->send(json_encode(['type' => 'pong', 'timestamp' => date('c')]));
                break;
            case 'get_notifications':
                $this->handleGetNotifications($from, $data);
                break;
            case 'mark_read':
                $this->handleMarkRead($from, $data);
                break;
            default:
                $from->send(json_encode(['type' => 'error', 'message' => 'Unknown message type']));
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        
        // Remove from user connections
        foreach ($this->userConnections as $userId => $connections) {
            foreach ($connections as $connId => $connObj) {
                if ($connObj === $conn) {
                    unset($this->userConnections[$userId][$connId]);
                    if (empty($this->userConnections[$userId])) {
                        unset($this->userConnections[$userId]);
                    }
                    break 2;
                }
            }
        }
        
        echo "Connection closed: {$conn->resourceId}\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error on connection {$conn->resourceId}: {$e->getMessage()}\n";
        $conn->close();
    }

    private function handleAuthentication(ConnectionInterface $conn, array $data) {
        $token = $data['token'] ?? '';
        $userType = $data['user_type'] ?? 'employee'; // employee, admin, associate, customer, agent
        
        if (!$token) {
            $from->send(json_encode([
                'type' => 'auth_result',
                'success' => false,
                'message' => 'Token required'
            ]));
            return;
        }

        // Validate token against database
        $user = $this->validateToken($token, $userType);
        
        if (!$user) {
            $from->send(json_encode([
                'type' => 'auth_result',
                'success' => false,
                'message' => 'Invalid or expired token'
            ]));
            return;
        }

        // Store user association with connection
        $userId = $user['id'];
        $this->userConnections[$userId][$from->resourceId] = $from;
        
        // Store user info on connection for later use
        $from->userId = $user['id'];
        $from->userType = $userType;
        $from->userName = $user['name'] ?? $user['email'] ?? 'Unknown';
        
        // Send success response
        $from->send(json_encode([
            'type' => 'auth_result',
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'] ?? '',
                'email' => $user['email'] ?? '',
                'type' => $userType
            ],
            'message' => 'Authenticated successfully'
        ]));
        
        // Send unread notification count
        $this->sendUnreadCount($from, $user['id']);
        
        echo "User authenticated: {$user['id']} ({$userType})\n";
    }

    private function handleSubscription(ConnectionInterface $conn, array $data) {
        if (!isset($conn->userId)) {
            $from->send(json_encode([
                'type' => 'error',
                'message' => 'Not authenticated'
            ]));
            return;
        }

        $channel = $data['channel'] ?? 'general';
        $channelKey = "{$conn->userId}:{$channel}";
        
        // Store subscription (in a real app, you'd store this in Redis or DB)
        if (!isset($this->subscriptions[$channelKey])) {
            $this->subscriptions[$channelKey] = [];
        }
        $this->subscriptions[$channelKey][$from->resourceId] = $from;
        
        $from->send(json_encode([
            'type' => 'subscribed',
            'channel' => $channel,
            'message' => "Subscribed to {$channel}"
        ]));
    }

    private function handleUnsubscription(ConnectionInterface $conn, array $data) {
        if (!isset($conn->userId)) return;
        
        $channel = $data['channel'] ?? 'general';
        $channelKey = "{$conn->userId}:{$channel}";
        
        if (isset($this->subscriptions[$channelKey])) {
            unset($this->subscriptions[$channelKey][$from->resourceId]);
        }
        
        $from->send(json_encode([
            'type' => 'unsubscribed',
            'channel' => $channel
        ]));
    }

    private function handleGetNotifications(ConnectionInterface $conn, array $data) {
        if (!isset($conn->userId)) {
            $from->send(json_encode(['type' => 'error', 'message' => 'Not authenticated']));
            return;
        }

        $limit = $data['limit'] ?? 20;
        $offset = $data['offset'] ?? 0;
        
        $notifications = $this->getNotifications($conn->userId, $limit, $offset);
        $unreadCount = $this->getUnreadCount($conn->userId);
        
        $from->send(json_encode([
            'type' => 'notifications',
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
            'has_more' => count($notifications) === $limit
        ]));
    }

    private function handleMarkRead(ConnectionInterface $conn, array $data) {
        if (!isset($conn->userId)) {
            $from->send(json_encode(['type' => 'error', 'message' => 'Not authenticated']));
            return;
        }

        $notificationId = $data['notification_id'] ?? null;
        
        if ($notificationId) {
            $this->markNotificationRead($conn->userId, $notificationId);
        } else {
            $this->markAllNotificationsRead($conn->userId);
        }
        
        $from->send(json_encode([
            'type' => 'marked_read',
            'notification_id' => $notificationId
        ]));
        
        // Send updated unread count
        $this->sendUnreadCount($from, $conn->userId);
    }

    private function validateToken(string $token, string $userType): ?array {
        // Check against appropriate table based on user type
        $tableMap = [
            'admin' => 'users',
            'admin' => 'users', // admin uses users table with role
            'employee' => 'users', // employee uses users table
            'associate' => 'users',
            'agent' => 'users',
            'customer' => 'users',
            'customer' => 'users',
        ];

        $table = $tableMap[$userType] ?? 'users';
        
        // For simplicity, check users table with role filtering
        $stmt = $this->db->prepare("
            SELECT id, name, email, role, status 
            FROM users 
            WHERE (remember_token = ? OR api_token = ?) 
            AND status = 'active'
            LIMIT 1
        ");
        $stmt->execute([$token, $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Verify role matches expected type
            $roleMap = [
                'admin' => ['admin', 'super_admin'],
                'employee' => ['employee'],
                'associate' => ['associate', 'freelancer'],
                'agent' => ['agent'],
                'customer' => ['customer'],
            ];
            
            $allowedRoles = $roleMap[$userType] ?? [];
            if (!empty($allowedRoles) && !in_array($user['role'], $allowedRoles)) {
                return null;
            }
            
            return $user;
        }
        
        return null;
    }

    private function getNotifications(int $userId, int $limit, int $offset): array {
        $stmt = $this->db->prepare("
            SELECT n.*, 
                   CASE 
                       WHEN n.type = 'lead' THEN 'Lead'
                       WHEN n.type = 'payment' THEN 'Payment'
                       WHEN n.type = 'leave' THEN 'Leave'
                       WHEN n.type = 'expense' THEN 'Expense'
                       WHEN n.type = 'task' THEN 'Task'
                       WHEN n.type = 'system' THEN 'System'
                       ELSE ucfirst(n.type)
                   END as type_label
            FROM notifications n
            WHERE n.user_id = ? AND n.tenant_id = ?
            ORDER BY n.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$userId, $this->tenantId, $limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function getUnreadCount(int $userId): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM notifications 
            WHERE user_id = ? AND tenant_id = ? AND read_at IS NULL
        ");
        $stmt->execute([$userId, $this->tenantId]);
        return (int)$stmt->fetchColumn();
    }

    private function markNotificationRead(int $userId, int $notificationId): void {
        $stmt = $this->db->prepare("
            UPDATE notifications SET read_at = NOW() 
            WHERE id = ? AND user_id = ? AND tenant_id = ? AND read_at IS NULL
        ");
        $stmt->execute([$notificationId, $userId, $this->tenantId]);
    }

    private function markAllNotificationsRead(int $userId): void {
        $stmt = $this->db->prepare("
            UPDATE notifications SET read_at = NOW() 
            WHERE user_id = ? AND tenant_id = ? AND read_at IS NULL
        ");
        $stmt->execute([$userId, $this->tenantId]);
    }

    private function sendUnreadCount(ConnectionInterface $conn, int $userId): void {
        $count = $this->getUnreadCount($userId);
        $conn->send(json_encode([
            'type' => 'unread_count',
            'count' => $count
        ]));
    }

    // Broadcast notification to specific user
    public function broadcastToUser(int $userId, array $notification): void {
        if (!isset($this->userConnections[$userId])) return;
        
        $message = json_encode([
            'type' => 'notification',
            'notification' => $notification
        ]);
        
        foreach ($this->userConnections[$userId] as $conn) {
            if ($conn->connected) {
                $conn->send($message);
            }
        }
    }

    // Broadcast to all connected clients
    public function broadcastToAll(array $notification): void {
        $message = json_encode([
            'type' => 'broadcast',
            'notification' => $notification
        ]);
        
        foreach ($this->clients as $conn) {
            if ($conn->connected) {
                $conn->send($message);
            }
        }
    }

    // Broadcast to specific channel
    public function broadcastToChannel(string $channel, array $notification): void {
        // Implementation for channel-based broadcasting
    }
}

// Run the server
$port = (int)($_ENV['WS_PORT'] ?? 8080);
$host = $_ENV['WS_HOST'] ?? '0.0.0.0';

echo "Starting HR WebSocket Server on {$host}:{$port}\n";

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new HRWebSocketServer()
        )
    ),
    $port,
    $host
);

$server->run();