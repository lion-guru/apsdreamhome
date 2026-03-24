
php

namespace App\Services\Events;

use App\Core\Database;

/**
 * Modern Event Service
 * Handles event-driven architecture with proper MVC patterns
 */
class EventService
{
    private Database $db;
    private array $subscribers = [];
    private array $eventQueue = [];
    private bool $asyncProcessing = false;

    // Event Types
    public const TYPE_SYSTEM = 'system';
    public const TYPE_USER = 'user';
    public const TYPE_DOMAIN = 'domain';
    public const TYPE_BUSINESS = 'business';

    // Event Priorities
    public const PRIORITY_LOW = 1;
    public const PRIORITY_NORMAL = 2;
    public const PRIORITY_HIGH = 3;
    public const PRIORITY_CRITICAL = 4;

    public function __construct(Database $db = null, bool $asyncProcessing = false)
    {
        $this->db = $db ?: Database::getInstance();
        $this->asyncProcessing = $asyncProcessing;
    }

    /**
     * Subscribe to events
     */
    public function subscribe(string $eventName, callable $handler, int $priority = self::PRIORITY_NORMAL): void
    {
        if (!isset($this->subscribers[$eventName])) {
            $this->subscribers[$eventName] = [];
        }

        $this->subscribers[$eventName][] = [
            'handler' => $handler,
            'priority' => $priority,
            'subscribed_at' => time()
        ];

        // Sort by priority (highest first)
        usort($this->subscribers[$eventName], function ($a, $b) {
            return $b['priority'] - $a['priority'];
        });

        error_log("Event subscription added: {$eventName}, Priority: {$priority}");
    }

    /**
     * Publish an event
     */
    public function publish(string $eventName, array $data = [], string $type = self::TYPE_USER, int $priority = self::PRIORITY_NORMAL): void
    {
        $event = [
            'id' => $this->generateEventId(),
            'name' => $eventName,
            'data' => $data,
            'type' => $type,
            'priority' => $priority,
            'timestamp' => time(),
            'published_at' => date('Y-m-d H:i:s')
        ];

        // Log event
        $this->logEvent($event);

        if ($this->asyncProcessing) {
            $this->addToQueue($event);
        } else {
            $this->processEvent($event);
        }
    }

    /**
     * Process event immediately
     */
    private function processEvent(array $event): void
    {
        try {
            $eventName = $event['name'];

            if (!isset($this->subscribers[$eventName])) {
                error_log("No subscribers for event: {$eventName}");
                return;
            }

            foreach ($this->subscribers[$eventName] as $subscriber) {
                try {
                    $handler = $subscriber['handler'];
                    $handler($event);

                    error_log("Event handler executed: {$eventName}, Priority: {$subscriber['priority']}");
                } catch (\Exception $e) {
                    error_log("Event handler failed: {$eventName}, Error: " . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            error_log("Event processing failed: {$event['name']}, Error: " . $e->getMessage());
        }
    }

    /**
     * Add event to queue for async processing
     */
    private function addToQueue(array $event): void
    {
        $this->eventQueue[] = $event;

        // Store in database for persistence
        $sql = "INSERT INTO event_queue (event_id, event_name, event_data, event_type, priority, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";

        $this->db->execute($sql, [
            $event['id'],
            $event['name'],
            json_encode($event),
            $event['type'],
            $event['priority']
        ]);

        error_log("Event added to queue: {$event['name']}");
    }

    /**
     * Process queued events
     */
    public function processQueue(int $limit = 50): int
    {
        $processed = 0;

        try {
            $sql = "SELECT * FROM event_queue 
                    WHERE processed = 0 
                    ORDER BY priority DESC, created_at ASC 
                    LIMIT ?";

            $events = $this->db->fetchAll($sql, [$limit]);

            foreach ($events as $eventData) {
                $event = json_decode($eventData['event_data'], true);

                if ($event) {
                    $this->processEvent($event);

                    // Mark as processed
                    $updateSql = "UPDATE event_queue SET processed = 1, processed_at = NOW() WHERE id = ?";
                    $this->db->execute($updateSql, [$eventData['id']]);

                    $processed++;
                }
            }
        } catch (\Exception $e) {
            error_log("Queue processing failed: " . $e->getMessage());
        }

        return $processed;
    }

    /**
     * Get event statistics
     */
    public function getEventStats(): array
    {
        try {
            $stats = [];

            // Total events today
            $stats['events_today'] = $this->db->fetchOne(
                "SELECT COUNT(*) FROM event_log WHERE DATE(created_at) = CURDATE()"
            ) ?? 0;

            // Events by type
            $typeStats = $this->db->fetchAll(
                "SELECT event_type, COUNT(*) as count FROM event_log 
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) 
                 GROUP BY event_type"
            );

            $stats['by_type'] = [];
            foreach ($typeStats as $stat) {
                $stats['by_type'][$stat['event_type']] = $stat['count'];
            }

            // Queue size
            $stats['queue_size'] = $this->db->fetchOne(
                "SELECT COUNT(*) FROM event_queue WHERE processed = 0"
            ) ?? 0;

            // Active subscribers
            $stats['active_subscribers'] = count($this->subscribers);

            return $stats;
        } catch (\Exception $e) {
            error_log("Failed to get event stats: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent events
     */
    public function getRecentEvents(int $limit = 20): array
    {
        try {
            $sql = "SELECT * FROM event_log 
                    ORDER BY created_at DESC 
                    LIMIT ?";

            return $this->db->fetchAll($sql, [$limit]);
        } catch (\Exception $e) {
            error_log("Failed to get recent events: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create system event tables if they don't exist
     */
    public function createEventTables(): void
    {
        try {
            // Event log table
            $this->db->execute("
                CREATE TABLE IF NOT EXISTS event_log (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    event_id VARCHAR(36) NOT NULL,
                    event_name VARCHAR(255) NOT NULL,
                    event_data TEXT,
                    event_type VARCHAR(50) NOT NULL,
                    priority INT DEFAULT 2,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_event_name (event_name),
                    INDEX idx_event_type (event_type),
                    INDEX idx_created_at (created_at)
                )
            ");

            // Event queue table
            $this->db->execute("
                CREATE TABLE IF NOT EXISTS event_queue (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    event_id VARCHAR(36) NOT NULL,
                    event_name VARCHAR(255) NOT NULL,
                    event_data TEXT,
                    event_type VARCHAR(50) NOT NULL,
                    priority INT DEFAULT 2,
                    processed BOOLEAN DEFAULT FALSE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    processed_at TIMESTAMP NULL,
                    INDEX idx_processed (processed),
                    INDEX idx_priority (priority),
                    INDEX idx_created_at (created_at)
                )
            ");

            error_log("Event tables created/verified");
        } catch (\Exception $e) {
            error_log("Failed to create event tables: " . $e->getMessage());
        }
    }

    /**
     * Log event to database
     */
    private function logEvent(array $event): void
    {
        try {
            $sql = "INSERT INTO event_log (event_id, event_name, event_data, event_type, priority) 
                    VALUES (?, ?, ?, ?, ?)";

            $this->db->execute($sql, [
                $event['id'],
                $event['name'],
                json_encode($event),
                $event['type'],
                $event['priority']
            ]);
        } catch (\Exception $e) {
            error_log("Failed to log event: " . $e->getMessage());
        }
    }

    /**
     * Generate unique event ID
     */
    private function generateEventId(): string
    {
        return uniqid('evt_', true);
    }

    /**
     * Clear old event logs
     */
    public function clearOldLogs(int $days = 30): int
    {
        try {
            $sql = "DELETE FROM event_log WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
            $deleted = $this->db->execute($sql, [$days]);

            error_log("Old event logs cleared: {$days} days, {$deleted} records");
            return $deleted;
        } catch (\Exception $e) {
            error_log("Failed to clear old logs: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get subscribers for an event
     */
    public function getSubscribers(string $eventName): array
    {
        return $this->subscribers[$eventName] ?? [];
    }

    /**
     * Remove event subscription
     */
    public function unsubscribe(string $eventName, callable $handler): bool
    {
        if (!isset($this->subscribers[$eventName])) {
            return false;
        }

        foreach ($this->subscribers[$eventName] as $key => $subscriber) {
            if ($subscriber['handler'] === $handler) {
                unset($this->subscribers[$eventName][$key]);
                error_log("Event subscription removed: {$eventName}");
                return true;
            }
        }

        return false;
    }
}
