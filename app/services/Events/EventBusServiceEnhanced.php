<?php

namespace App\Services\Events;

use App\Core\Database\Database;
use App\Services\LoggingService;
use Exception;
use InvalidArgumentException;
use RuntimeException;

/**
 * Advanced Event-Driven Architecture and Pub/Sub Service - APS Dream Home
 * Provides robust event management, decoupled communication, and real-time processing
 * Custom MVC implementation without Laravel dependencies
 */
class EventBusServiceEnhanced
{
    // Event Types
    public const TYPE_SYSTEM = 'system';
    public const TYPE_USER = 'user';
    public const TYPE_DOMAIN = 'domain';
    public const TYPE_INFRASTRUCTURE = 'infrastructure';

    // Event Priorities
    public const PRIORITY_LOW = 1;
    public const PRIORITY_NORMAL = 2;
    public const PRIORITY_HIGH = 3;
    public const PRIORITY_CRITICAL = 4;

    // Event Processing Modes
    public const MODE_SYNC = 'sync';
    public const MODE_ASYNC = 'async';
    public const MODE_DISTRIBUTED = 'distributed';

    // Event Storage and Routing
    private $eventSubscriptions = [];
    private $eventQueue = [];
    private $eventHistory = [];

    // System Dependencies
    private $database;
    private $logger;

    // Configuration Parameters
    private $maxEventQueueSize = 1000;
    private $eventRetentionDays = 7;
    private $eventProcessingMode;

    // Advanced Event Handling
    private $wildcardSubscriptions = [];
    private $eventTransformers = [];
    private $eventMiddleware = [];

    public function __construct($processingMode = self::MODE_ASYNC, $database = null, $logger = null)
    {
        $this->database = $database ?: \App\Core\Database\Database::getInstance();
        $this->logger = $logger ?: new \App\Services\LoggingService();
        $this->eventProcessingMode = $processingMode;
        $this->createEventTables();
        $this->loadSubscriptions();
    }

    /**
     * Create event management tables
     */
    private function createEventTables()
    {
        try {
            // Events table
            $sql = "CREATE TABLE IF NOT EXISTS events (
                id INT AUTO_INCREMENT PRIMARY KEY,
                event_id VARCHAR(100) NOT NULL UNIQUE,
                event_type VARCHAR(50) NOT NULL,
                event_name VARCHAR(100) NOT NULL,
                event_data JSON,
                event_source VARCHAR(100),
                priority INT DEFAULT 2,
                processing_mode VARCHAR(20) DEFAULT 'async',
                status ENUM('pending','processing','completed','failed') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                processed_at TIMESTAMP NULL,
                error_message TEXT,
                retry_count INT DEFAULT 0,
                max_retries INT DEFAULT 3,
                created_by BIGINT(20) UNSIGNED,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            )";
            $this->database->query($sql);

            // Event subscriptions table
            $sql = "CREATE TABLE IF NOT EXISTS event_subscriptions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                event_name VARCHAR(100) NOT NULL,
                subscriber_type ENUM('class','method','callback') NOT NULL,
                subscriber_target VARCHAR(255) NOT NULL,
                priority INT DEFAULT 2,
                is_active BOOLEAN DEFAULT TRUE,
                filter_conditions JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";
            $this->database->query($sql);

            // Event history table
            $sql = "CREATE TABLE IF NOT EXISTS event_history (
                id INT AUTO_INCREMENT PRIMARY KEY,
                event_id VARCHAR(100) NOT NULL,
                event_name VARCHAR(100) NOT NULL,
                subscriber_target VARCHAR(255) NOT NULL,
                processing_time DECIMAL(8,3),
                status ENUM('success','failed','skipped') NOT NULL,
                result_data JSON,
                error_message TEXT,
                processed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_event_history_event_id (event_id),
                INDEX idx_event_history_processed_at (processed_at)
            )";
            $this->database->query($sql);
        } catch (Exception $e) {
            $this->logger->log("Error creating event tables: " . $e->getMessage(), 'error', 'events');
            throw new RuntimeException("Failed to create event tables: " . $e->getMessage());
        }
    }

    /**
     * Load event subscriptions from database
     */
    private function loadSubscriptions()
    {
        try {
            $sql = "SELECT * FROM event_subscriptions WHERE is_active = TRUE ORDER BY priority DESC";
            $subscriptions = $this->database->fetchAll($sql);

            foreach ($subscriptions as $sub) {
                $this->eventSubscriptions[$sub['event_name']][] = [
                    'type' => $sub['subscriber_type'],
                    'target' => $sub['subscriber_target'],
                    'priority' => $sub['priority'],
                    'filter' => json_decode($sub['filter_conditions'] ?? '{}', true)
                ];
            }
        } catch (Exception $e) {
            $this->logger->log("Error loading subscriptions: " . $e->getMessage(), 'error', 'events');
        }
    }

    /**
     * Publish an event
     */
    public function publish($eventName, $eventData = [], $eventType = self::TYPE_DOMAIN, $priority = self::PRIORITY_NORMAL)
    {
        if (empty($eventName)) {
            throw new InvalidArgumentException('Event name is required');
        }

        $eventId = $this->generateEventId();
        $event = [
            'id' => $eventId,
            'name' => $eventName,
            'type' => $eventType,
            'data' => $eventData,
            'priority' => $priority,
            'timestamp' => time(),
            'source' => $this->getEventSource()
        ];

        try {
            // Store event in database
            $this->storeEvent($event);

            // Process based on mode
            switch ($this->eventProcessingMode) {
                case self::MODE_SYNC:
                    $this->processEventSync($event);
                    break;
                case self::MODE_ASYNC:
                    $this->queueEvent($event);
                    break;
                case self::MODE_DISTRIBUTED:
                    $this->publishDistributed($event);
                    break;
            }

            $this->logger->log("Event published: $eventName (ID: $eventId)", 'info', 'events');
            return $eventId;
        } catch (Exception $e) {
            $this->logger->log("Error publishing event $eventName: " . $e->getMessage(), 'error', 'events');
            throw new RuntimeException("Failed to publish event: " . $e->getMessage());
        }
    }

    /**
     * Subscribe to events
     */
    public function subscribe($eventName, $subscriberTarget, $subscriberType = 'class', $priority = self::PRIORITY_NORMAL, $filter = [])
    {
        if (empty($eventName) || empty($subscriberTarget)) {
            throw new InvalidArgumentException('Event name and subscriber target are required');
        }

        try {
            // Store in database
            $sql = "INSERT INTO event_subscriptions (event_name, subscriber_type, subscriber_target, priority, filter_conditions)
                    VALUES (?, ?, ?, ?, ?)";

            $this->database->execute($sql, [
                $eventName,
                $subscriberType,
                $subscriberTarget,
                $priority,
                json_encode($filter)
            ]);

            // Add to memory
            $this->eventSubscriptions[$eventName][] = [
                'type' => $subscriberType,
                'target' => $subscriberTarget,
                'priority' => $priority,
                'filter' => $filter
            ];

            $this->logger->log("Subscription added: $subscriberTarget for event $eventName", 'info', 'events');
            return true;
        } catch (Exception $e) {
            $this->logger->log("Error adding subscription: " . $e->getMessage(), 'error', 'events');
            throw new RuntimeException("Failed to add subscription: " . $e->getMessage());
        }
    }

    /**
     * Process event synchronously
     */
    private function processEventSync($event)
    {
        $startTime = microtime(true);

        try {
            $this->updateEventStatus($event['id'], 'processing');

            $subscribers = $this->getSubscribersForEvent($event['name']);

            foreach ($subscribers as $subscriber) {
                if ($this->shouldProcessEvent($event, $subscriber['filter'])) {
                    $this->dispatchEvent($event, $subscriber);
                }
            }

            $this->updateEventStatus($event['id'], 'completed');
        } catch (Exception $e) {
            $this->updateEventStatus($event['id'], 'failed', $e->getMessage());
            throw $e;
        }
    }

    /**
     * Queue event for async processing
     */
    private function queueEvent($event)
    {
        if (count($this->eventQueue) >= $this->maxEventQueueSize) {
            throw new RuntimeException('Event queue is full');
        }

        $this->eventQueue[] = $event;
        $this->logger->log("Event queued: {$event['name']} (ID: {$event['id']})", 'info', 'events');
    }

    /**
     * Process queued events
     */
    public function processQueuedEvents()
    {
        $processed = 0;

        while (!empty($this->eventQueue) && $processed < 100) {
            $event = array_shift($this->eventQueue);

            try {
                $this->processEventSync($event);
                $processed++;
            } catch (Exception $e) {
                $this->logger->log("Error processing queued event {$event['id']}: " . $e->getMessage(), 'error', 'events');
            }
        }

        return $processed;
    }

    /**
     * Get subscribers for event
     */
    private function getSubscribersForEvent($eventName)
    {
        $subscribers = $this->eventSubscriptions[$eventName] ?? [];

        // Check wildcard subscriptions
        foreach ($this->wildcardSubscriptions as $pattern => $wildcardSubs) {
            if ($this->matchesPattern($eventName, $pattern)) {
                $subscribers = array_merge($subscribers, $wildcardSubs);
            }
        }

        // Sort by priority
        usort($subscribers, function ($a, $b) {
            return $b['priority'] - $a['priority'];
        });

        return $subscribers;
    }

    /**
     * Check if event should be processed by subscriber
     */
    private function shouldProcessEvent($event, $filter)
    {
        if (empty($filter)) {
            return true;
        }

        foreach ($filter as $key => $value) {
            if (!isset($event['data'][$key]) || $event['data'][$key] !== $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * Dispatch event to subscriber
     */
    private function dispatchEvent($event, $subscriber)
    {
        $startTime = microtime(true);

        try {
            switch ($subscriber['type']) {
                case 'class':
                    $this->dispatchToClass($event, $subscriber['target']);
                    break;
                case 'method':
                    $this->dispatchToMethod($event, $subscriber['target']);
                    break;
                case 'callback':
                    $this->dispatchToCallback($event, $subscriber['target']);
                    break;
            }

            $processingTime = microtime(true) - $startTime;
            $this->logEventHistory($event['id'], $event['name'], $subscriber['target'], 'success', $processingTime);
        } catch (Exception $e) {
            $processingTime = microtime(true) - $startTime;
            $this->logEventHistory($event['id'], $event['name'], $subscriber['target'], 'failed', $processingTime, $e->getMessage());
            throw $e;
        }
    }

    /**
     * Dispatch to class
     */
    private function dispatchToClass($event, $className)
    {
        if (!class_exists($className)) {
            throw new RuntimeException("Class $className not found");
        }

        $instance = new $className();

        if (method_exists($instance, 'handle')) {
            $instance->handle($event);
        } else {
            throw new RuntimeException("Class $className must have handle() method");
        }
    }

    /**
     * Dispatch to method
     */
    private function dispatchToMethod($event, $methodTarget)
    {
        if (!strpos($methodTarget, '@')) {
            throw new RuntimeException("Method target must be in format 'Class@method'");
        }

        list($className, $methodName) = explode('@', $methodTarget);

        if (!class_exists($className) || !method_exists($className, $methodName)) {
            throw new RuntimeException("Method $className@$methodName not found");
        }

        call_user_func([$className, $methodName], $event);
    }

    /**
     * Dispatch to callback
     */
    private function dispatchToCallback($event, $callback)
    {
        if (!is_callable($callback)) {
            throw new RuntimeException("Callback is not callable");
        }

        call_user_func($callback, $event);
    }

    /**
     * Publish event in distributed mode
     */
    private function publishDistributed($event)
    {
        // For distributed mode, we'll queue the event and mark it for distributed processing
        $this->queueEvent($event);

        // Mark event as distributed
        $sql = "UPDATE events SET processing_mode = ?, distributed_at = NOW() WHERE event_id = ?";
        $this->database->execute($sql, [self::MODE_DISTRIBUTED, $event['id']]);

        $this->logger->log("Event queued for distributed processing: {$event['name']} (ID: {$event['id']})", 'info', 'events');
    }

    /**
     * Store event in database
     */
    private function storeEvent($event)
    {
        $sql = "INSERT INTO events (event_id, event_type, event_name, event_data, event_source, priority, processing_mode, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $this->database->execute($sql, [
            $event['id'],
            $event['type'],
            $event['name'],
            json_encode($event['data']),
            $event['source'],
            $event['priority'],
            $this->eventProcessingMode,
            null // created_by - can be added later
        ]);
    }

    /**
     * Update event status
     */
    private function updateEventStatus($eventId, $status, $errorMessage = null)
    {
        $sql = "UPDATE events SET status = ?, processed_at = NOW(), error_message = ? WHERE event_id = ?";
        $this->database->execute($sql, [$status, $errorMessage, $eventId]);
    }

    /**
     * Log event history
     */
    private function logEventHistory($eventId, $eventName, $subscriberTarget, $status, $processingTime, $errorMessage = null)
    {
        $sql = "INSERT INTO event_history (event_id, event_name, subscriber_target, processing_time, status, error_message)
                VALUES (?, ?, ?, ?, ?, ?)";

        $this->database->execute($sql, [
            $eventId,
            $eventName,
            $subscriberTarget,
            $processingTime,
            $status,
            $errorMessage
        ]);
    }

    /**
     * Generate unique event ID
     */
    private function generateEventId()
    {
        return 'evt_' . uniqid() . '_' . time();
    }

    /**
     * Get event source
     */
    private function getEventSource()
    {
        return 'aps_dream_home_system';
    }

    /**
     * Check if event name matches pattern
     */
    private function matchesPattern($eventName, $pattern)
    {
        return fnmatch($pattern, $eventName);
    }

    /**
     * Get event statistics
     */
    public function getEventStats()
    {
        $stats = [];

        try {
            // Total events
            $result = $this->database->fetchOne("SELECT COUNT(*) as total FROM events");
            $stats['total_events'] = $result['total'] ?? 0;

            // By status
            $results = $this->database->fetchAll("SELECT status, COUNT(*) as count FROM events GROUP BY status");
            $stats['by_status'] = [];
            foreach ($results as $row) {
                $stats['by_status'][$row['status']] = $row['count'];
            }

            // Recent events
            $result = $this->database->fetchOne("SELECT COUNT(*) as recent FROM events WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
            $stats['recent_events'] = $result['recent'] ?? 0;

            // Queue size
            $stats['queue_size'] = count($this->eventQueue);

            // Active subscriptions
            $result = $this->database->fetchOne("SELECT COUNT(*) as total FROM event_subscriptions WHERE is_active = TRUE");
            $stats['active_subscriptions'] = $result['total'] ?? 0;
        } catch (Exception $e) {
            $this->logger->log("Error fetching event stats: " . $e->getMessage(), 'error', 'events');
        }

        return $stats;
    }

    /**
     * Clean up old events
     */
    public function cleanupOldEvents()
    {
        try {
            $sql = "DELETE FROM events WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
            $this->database->execute($sql, [$this->eventRetentionDays]);

            $sql = "DELETE FROM event_history WHERE processed_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
            $this->database->execute($sql, [$this->eventRetentionDays]);

            $this->logger->log("Old events cleaned up", 'info', 'events');
            return true;
        } catch (Exception $e) {
            $this->logger->log("Error cleaning up old events: " . $e->getMessage(), 'error', 'events');
            return false;
        }
    }
}
