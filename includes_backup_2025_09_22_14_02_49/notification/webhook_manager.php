<?php
/**
 * Webhook Manager
 * Manages webhook subscriptions and notifications for system events
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../security_logger.php';

class WebhookManager {
    private $logger;
    private $con;
    private $cache;
    private $config;

    public function __construct($database_connection = null) {
        $this->con = $database_connection ?? getDbConnection();
        $this->logger = new SecurityLogger();
        $this->initializeCache();
        $this->loadConfig();
        $this->ensureWebhookTable();
    }

    /**
     * Initialize Redis cache for webhook delivery tracking
     */
    private function initializeCache() {
        try {
            $this->cache = new Redis();
            $this->cache->connect(
                getenv('REDIS_HOST') ?: 'localhost',
                getenv('REDIS_PORT') ?: 6379
            );
        } catch (Exception $e) {
            $this->logger->warning('Redis connection failed, using database fallback');
            $this->cache = null;
        }
    }

    /**
     * Load configuration
     */
    private function loadConfig() {
        $this->config = [
            'max_retries' => 3,
            'retry_delay' => 60, // seconds
            'timeout' => 10, // seconds
            'batch_size' => 100,
            'max_payload_size' => 1048576, // 1MB
            'signature_header' => 'X-APS-Signature',
            'event_types' => [
                'security.incident',
                'backup.complete',
                'api.error',
                'system.alert',
                'user.action',
                'rate_limit.exceeded',
                'performance.degraded'
            ]
        ];
    }

    /**
     * Ensure webhook tables exist
     */
    private function ensureWebhookTable() {
        $this->con->query("
            CREATE TABLE IF NOT EXISTS webhooks (
                id VARCHAR(36) PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                url VARCHAR(255) NOT NULL,
                secret VARCHAR(255) NOT NULL,
                events JSON NOT NULL,
                headers JSON,
                enabled BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");

        $this->con->query("
            CREATE TABLE IF NOT EXISTS webhook_deliveries (
                id VARCHAR(36) PRIMARY KEY,
                webhook_id VARCHAR(36) NOT NULL,
                event_type VARCHAR(50) NOT NULL,
                payload JSON NOT NULL,
                status_code INT,
                response_body TEXT,
                attempt_count INT DEFAULT 0,
                next_retry TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                completed_at TIMESTAMP NULL,
                FOREIGN KEY (webhook_id) REFERENCES webhooks(id)
            )
        ");
    }

    /**
     * Create new webhook
     */
    public function createWebhook($name, $url, $events, $secret = null, $headers = []) {
        // Validate URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new Exception('Invalid webhook URL');
        }

        // Validate events
        foreach ($events as $event) {
            if (!in_array($event, $this->config['event_types'])) {
                throw new Exception("Invalid event type: {$event}");
            }
        }

        // Generate webhook ID and secret if not provided
        $id = $this->generateUuid();
        $secret = $secret ?? bin2hex(random_bytes(32));

        $stmt = $this->con->prepare("
            INSERT INTO webhooks (id, name, url, secret, events, headers)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $eventsJson = json_encode($events);
        $headersJson = json_encode($headers);
        $stmt->bind_param('ssssss', $id, $name, $url, $secret, $eventsJson, $headersJson);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create webhook');
        }

        return [
            'id' => $id,
            'secret' => $secret
        ];
    }

    /**
     * Update webhook
     */
    public function updateWebhook($id, $data) {
        $updates = [];
        $types = '';
        $params = [];

        if (isset($data['name'])) {
            $updates[] = 'name = ?';
            $types .= 's';
            $params[] = $data['name'];
        }

        if (isset($data['url'])) {
            if (!filter_var($data['url'], FILTER_VALIDATE_URL)) {
                throw new Exception('Invalid webhook URL');
            }
            $updates[] = 'url = ?';
            $types .= 's';
            $params[] = $data['url'];
        }

        if (isset($data['events'])) {
            foreach ($data['events'] as $event) {
                if (!in_array($event, $this->config['event_types'])) {
                    throw new Exception("Invalid event type: {$event}");
                }
            }
            $updates[] = 'events = ?';
            $types .= 's';
            $params[] = json_encode($data['events']);
        }

        if (isset($data['headers'])) {
            $updates[] = 'headers = ?';
            $types .= 's';
            $params[] = json_encode($data['headers']);
        }

        if (isset($data['enabled'])) {
            $updates[] = 'enabled = ?';
            $types .= 'i';
            $params[] = $data['enabled'] ? 1 : 0;
        }

        if (empty($updates)) {
            return false;
        }

        $sql = "UPDATE webhooks SET " . implode(', ', $updates) . " WHERE id = ?";
        $types .= 's';
        $params[] = $id;

        $stmt = $this->con->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        return $stmt->execute();
    }

    /**
     * Delete webhook
     */
    public function deleteWebhook($id) {
        $stmt = $this->con->prepare("DELETE FROM webhooks WHERE id = ?");
        $stmt->bind_param('s', $id);
        return $stmt->execute();
    }

    /**
     * Get webhook by ID
     */
    public function getWebhook($id) {
        $stmt = $this->con->prepare("SELECT * FROM webhooks WHERE id = ?");
        $stmt->bind_param('s', $id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $webhook = $result->fetch_assoc();
        
        if ($webhook) {
            $webhook['events'] = json_decode($webhook['events'], true);
            $webhook['headers'] = json_decode($webhook['headers'], true);
        }
        
        return $webhook;
    }

    /**
     * List all webhooks
     */
    public function listWebhooks() {
        $result = $this->con->query("SELECT * FROM webhooks ORDER BY created_at DESC");
        $webhooks = [];
        
        while ($row = $result->fetch_assoc()) {
            $row['events'] = json_decode($row['events'], true);
            $row['headers'] = json_decode($row['headers'], true);
            $webhooks[] = $row;
        }
        
        return $webhooks;
    }

    /**
     * Trigger webhook event
     */
    public function triggerEvent($eventType, $payload) {
        if (!in_array($eventType, $this->config['event_types'])) {
            throw new Exception("Invalid event type: {$eventType}");
        }

        // Get webhooks subscribed to this event
        $stmt = $this->con->prepare("
            SELECT * FROM webhooks 
            WHERE enabled = 1 
            AND JSON_CONTAINS(events, ?)
        ");
        
        $eventJson = json_encode($eventType);
        $stmt->bind_param('s', $eventJson);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $webhooks = [];
        
        while ($row = $result->fetch_assoc()) {
            $webhooks[] = $row;
        }

        // Create delivery records
        foreach ($webhooks as $webhook) {
            $this->createDelivery($webhook['id'], $eventType, $payload);
        }

        // Process deliveries asynchronously
        $this->processDeliveries();
    }

    /**
     * Create webhook delivery record
     */
    private function createDelivery($webhookId, $eventType, $payload) {
        $id = $this->generateUuid();
        
        $stmt = $this->con->prepare("
            INSERT INTO webhook_deliveries (id, webhook_id, event_type, payload)
            VALUES (?, ?, ?, ?)
        ");
        
        $payloadJson = json_encode($payload);
        $stmt->bind_param('ssss', $id, $webhookId, $eventType, $payloadJson);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create webhook delivery');
        }

        return $id;
    }

    /**
     * Process pending webhook deliveries
     */
    private function processDeliveries() {
        $stmt = $this->con->prepare("
            SELECT d.*, w.url, w.secret, w.headers 
            FROM webhook_deliveries d
            JOIN webhooks w ON d.webhook_id = w.id
            WHERE d.completed_at IS NULL
            AND (d.next_retry IS NULL OR d.next_retry <= NOW())
            AND d.attempt_count < ?
            LIMIT ?
        ");

        $stmt->bind_param('ii', $this->config['max_retries'], $this->config['batch_size']);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $deliveries = [];
        
        while ($row = $result->fetch_assoc()) {
            $deliveries[] = $row;
        }

        foreach ($deliveries as $delivery) {
            $this->sendDelivery($delivery);
        }
    }

    /**
     * Send webhook delivery
     */
    private function sendDelivery($delivery) {
        $payload = json_decode($delivery['payload'], true);
        $headers = json_decode($delivery['headers'] ?? '[]', true);
        
        // Add signature header
        $signature = $this->generateSignature($payload, $delivery['secret']);
        $headers[$this->config['signature_header']] = $signature;

        // Prepare curl request
        $ch = curl_init($delivery['url']);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->config['timeout'],
            CURLOPT_HTTPHEADER => array_map(
                fn($k, $v) => "{$k}: {$v}",
                array_keys($headers),
                $headers
            )
        ]);

        // Send request
        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // Update delivery record
        $this->updateDelivery(
            $delivery['id'],
            $statusCode,
            $response,
            $error,
            $delivery['attempt_count'] + 1
        );
    }

    /**
     * Update delivery record
     */
    private function updateDelivery($id, $statusCode, $response, $error, $attemptCount) {
        $completed = $statusCode >= 200 && $statusCode < 300;
        $nextRetry = !$completed && $attemptCount < $this->config['max_retries'] ?
            date('Y-m-d H:i:s', time() + ($this->config['retry_delay'] * $attemptCount)) :
            null;

        $stmt = $this->con->prepare("
            UPDATE webhook_deliveries 
            SET status_code = ?,
                response_body = ?,
                attempt_count = ?,
                next_retry = ?,
                completed_at = ?
            WHERE id = ?
        ");

        $completedAt = $completed || $attemptCount >= $this->config['max_retries'] ?
            date('Y-m-d H:i:s') :
            null;

        $stmt->bind_param(
            'isisss',
            $statusCode,
            $response,
            $attemptCount,
            $nextRetry,
            $completedAt,
            $id
        );

        if (!$stmt->execute()) {
            $this->logger->error('Failed to update webhook delivery', [
                'delivery_id' => $id,
                'error' => $this->con->error
            ]);
        }

        // Log delivery result
        $this->logger->info('Webhook delivery attempt', [
            'delivery_id' => $id,
            'status_code' => $statusCode,
            'attempt' => $attemptCount,
            'completed' => $completed,
            'error' => $error
        ]);
    }

    /**
     * Generate webhook signature
     */
    private function generateSignature($payload, $secret) {
        return hash_hmac('sha256', json_encode($payload), $secret);
    }

    /**
     * Generate UUID v4
     */
    private function generateUuid() {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Get webhook delivery history
     */
    public function getDeliveryHistory($webhookId = null, $limit = 100) {
        $sql = "
            SELECT d.*, w.name as webhook_name, w.url
            FROM webhook_deliveries d
            JOIN webhooks w ON d.webhook_id = w.id
        ";
        
        if ($webhookId) {
            $sql .= " WHERE d.webhook_id = ?";
        }
        
        $sql .= " ORDER BY d.created_at DESC LIMIT ?";
        
        $stmt = $this->con->prepare($sql);
        
        if ($webhookId) {
            $stmt->bind_param('si', $webhookId, $limit);
        } else {
            $stmt->bind_param('i', $limit);
        }
        
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Clean up old delivery records
     */
    public function cleanupDeliveries($days = 30) {
        $stmt = $this->con->prepare("
            DELETE FROM webhook_deliveries 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        
        $stmt->bind_param('i', $days);
        $stmt->execute();
        
        $this->logger->info('Cleaned up old webhook deliveries', [
            'deleted_rows' => $stmt->affected_rows
        ]);
    }
}

// Create global webhook manager instance
$webhookManager = new WebhookManager($con ?? null);

