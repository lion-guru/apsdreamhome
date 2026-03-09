<?php

namespace App\Services\Events;

use App\Core\Database;
use Psr\Log\LoggerInterface;

/**
 * Modern Event Dispatcher Service
 * Handles advanced event dispatching with middleware and monitoring
 */
class EventDispatcherService
{
    private Database $db;
    private LoggerInterface $logger;
    private array $listeners = [];
    private array $wildcardListeners = [];
    private array $middleware = [];
    private array $asyncQueue = [];
    private bool $processingAsync = false;

    // Event priorities
    public const PRIORITY_HIGH = 3;
    public const PRIORITY_NORMAL = 2;
    public const PRIORITY_LOW = 1;

    public function __construct(Database $db, LoggerInterface $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->initializeEventTables();
    }

    /**
     * Register event listener
     */
    public function listen(string $eventName, callable $listener, int $priority = self::PRIORITY_NORMAL): void
    {
        if (!isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = [];
        }

        $this->listeners[$eventName][] = [
            'listener' => $listener,
            'priority' => $priority,
            'registered_at' => time()
        ];

        // Sort by priority (highest first)
        usort($this->listeners[$eventName], function ($a, $b) {
            return $b['priority'] - $a['priority'];
        });

        $this->logger->info("Event listener registered", [
            'event' => $eventName,
            'priority' => $priority
        ]);
    }

    /**
     * Register wildcard event listener
     */
    public function listenWildcard(callable $listener, int $priority = self::PRIORITY_NORMAL): void
    {
        $this->wildcardListeners[] = [
            'listener' => $listener,
            'priority' => $priority,
            'registered_at' => time()
        ];

        // Sort by priority
        usort($this->wildcardListeners, function ($a, $b) {
            return $b['priority'] - $a['priority'];
        });

        $this->logger->info("Wildcard event listener registered", ['priority' => $priority]);
    }

    /**
     * Add middleware
     */
    public function addMiddleware(callable $middleware, int $priority = self::PRIORITY_NORMAL): void
    {
        $this->middleware[] = [
            'middleware' => $middleware,
            'priority' => $priority,
            'registered_at' => time()
        ];

        // Sort by priority
        usort($this->middleware, function ($a, $b) {
            return $b['priority'] - $a['priority'];
        });
    }

    /**
     * Dispatch event
     */
    public function dispatch(string $eventName, array $data = [], array $options = []): array
    {
        try {
            $startTime = microtime(true);
            $eventId = $this->createEventRecord($eventName, $data, $options);

            // Create event object
            $event = new Event($eventName, $data, $eventId);
            
            // Apply middleware
            foreach ($this->middleware as $middlewareInfo) {
                $result = ($middlewareInfo['middleware'])($event);
                if ($result === false) {
                    // Middleware stopped propagation
                    $this->updateEventRecord($eventId, 'stopped', 'Middleware stopped propagation');
                    return [
                        'success' => true,
                        'message' => 'Event stopped by middleware',
                        'event_id' => $eventId,
                        'listeners_executed' => 0
                    ];
                }
            }

            $listenersExecuted = 0;
            $errors = [];

            // Execute wildcard listeners first
            foreach ($this->wildcardListeners as $listenerInfo) {
                try {
                    ($listenerInfo['listener'])($event);
                    $listenersExecuted++;
                } catch (\Exception $e) {
                    $errors[] = "Wildcard listener error: " . $e->getMessage();
                    $this->logger->error("Wildcard listener error", [
                        'event' => $eventName,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Execute specific event listeners
            if (isset($this->listeners[$eventName])) {
                foreach ($this->listeners[$eventName] as $listenerInfo) {
                    try {
                        ($listenerInfo['listener'])($event);
                        $listenersExecuted++;
                    } catch (\Exception $e) {
                        $errors[] = "Listener error: " . $e->getMessage();
                        $this->logger->error("Event listener error", [
                            'event' => $eventName,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            $executionTime = (microtime(true) - $startTime) * 1000;

            // Update event record
            $status = empty($errors) ? 'completed' : 'completed_with_errors';
            $this->updateEventRecord($eventId, $status, json_encode($errors), $executionTime, $listenersExecuted);

            return [
                'success' => true,
                'message' => 'Event dispatched successfully',
                'event_id' => $eventId,
                'listeners_executed' => $listenersExecuted,
                'execution_time_ms' => round($executionTime, 2),
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            $this->logger->error("Event dispatch failed", [
                'event' => $eventName,
                'error' => $e->getMessage()
            ]);

            if (isset($eventId)) {
                $this->updateEventRecord($eventId, 'failed', $e->getMessage());
            }

            return [
                'success' => false,
                'message' => 'Event dispatch failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Dispatch event asynchronously
     */
    public function dispatchAsync(string $eventName, array $data = [], array $options = []): array
    {
        try {
            // Add to async queue
            $queueId = $this->addToAsyncQueue($eventName, $data, $options);

            $this->logger->info("Event queued for async dispatch", [
                'event' => $eventName,
                'queue_id' => $queueId
            ]);

            return [
                'success' => true,
                'message' => 'Event queued for async dispatch',
                'queue_id' => $queueId
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to queue async event", [
                'event' => $eventName,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to queue async event: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process async queue
     */
    public function processAsyncQueue(int $limit = 10): array
    {
        if ($this->processingAsync) {
            return [
                'success' => false,
                'message' => 'Async queue already being processed'
            ];
        }

        $this->processingAsync = true;

        try {
            $processed = 0;
            $successCount = 0;
            $failureCount = 0;
            $errors = [];

            // Get queued events
            $sql = "SELECT * FROM event_queue 
                    WHERE status = 'pending' 
                    ORDER BY created_at ASC 
                    LIMIT ?";
            
            $queuedEvents = $this->db->fetchAll($sql, [$limit]);

            foreach ($queuedEvents as $queuedEvent) {
                try {
                    // Update status to processing
                    $this->updateQueueStatus($queuedEvent['id'], 'processing');

                    // Dispatch event
                    $result = $this->dispatch(
                        $queuedEvent['event_name'],
                        json_decode($queuedEvent['event_data'], true) ?? [],
                        json_decode($queuedEvent['options'], true) ?? []
                    );

                    if ($result['success']) {
                        $this->updateQueueStatus($queuedEvent['id'], 'completed', json_encode($result));
                        $successCount++;
                    } else {
                        $this->updateQueueStatus($queuedEvent['id'], 'failed', $result['message']);
                        $failureCount++;
                        $errors[] = "Queue ID {$queuedEvent['id']}: {$result['message']}";
                    }

                    $processed++;

                } catch (\Exception $e) {
                    $this->updateQueueStatus($queuedEvent['id'], 'failed', $e->getMessage());
                    $failureCount++;
                    $errors[] = "Queue ID {$queuedEvent['id']}: {$e->getMessage()}";
                    $processed++;
                }
            }

            return [
                'success' => true,
                'message' => "Processed {$processed} async events",
                'processed' => $processed,
                'success_count' => $successCount,
                'failure_count' => $failureCount,
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            $this->logger->error("Async queue processing failed", ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Async queue processing failed: ' . $e->getMessage()
            ];
        } finally {
            $this->processingAsync = false;
        }
    }

    /**
     * Get event listeners
     */
    public function getListeners(string $eventName = null): array
    {
        if ($eventName) {
            return $this->listeners[$eventName] ?? [];
        }

        return $this->listeners;
    }

    /**
     * Get wildcard listeners
     */
    public function getWildcardListeners(): array
    {
        return $this->wildcardListeners;
    }

    /**
     * Get middleware
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * Remove event listener
     */
    public function removeListener(string $eventName, callable $listener): bool
    {
        if (!isset($this->listeners[$eventName])) {
            return false;
        }

        foreach ($this->listeners[$eventName] as $key => $listenerInfo) {
            if ($listenerInfo['listener'] === $listener) {
                unset($this->listeners[$eventName][$key]);
                $this->listeners[$eventName] = array_values($this->listeners[$eventName]);
                return true;
            }
        }

        return false;
    }

    /**
     * Clear all listeners
     */
    public function clearListeners(string $eventName = null): void
    {
        if ($eventName) {
            unset($this->listeners[$eventName]);
        } else {
            $this->listeners = [];
            $this->wildcardListeners = [];
        }
    }

    /**
     * Get event statistics
     */
    public function getEventStats(array $filters = []): array
    {
        try {
            $stats = [];

            // Total events
            $sql = "SELECT COUNT(*) as total FROM event_logs";
            $params = [];
            
            if (!empty($filters['date_from'])) {
                $sql .= " WHERE created_at >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= (empty($params) ? " WHERE" : " AND") . " created_at <= ?";
                $params[] = $filters['date_to'];
            }
            
            $stats['total_events'] = $this->db->fetchOne($sql, $params) ?? 0;

            // Events by status
            $statusSql = "SELECT status, COUNT(*) as count FROM event_logs";
            $statusParams = [];
            
            if (!empty($filters['date_from'])) {
                $statusSql .= " WHERE created_at >= ?";
                $statusParams[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $statusSql .= (empty($statusParams) ? " WHERE" : " AND") . " created_at <= ?";
                $statusParams[] = $filters['date_to'];
            }
            
            $statusSql .= " GROUP BY status";
            
            $statusStats = $this->db->fetchAll($statusSql, $statusParams);
            $stats['by_status'] = [];
            foreach ($statusStats as $stat) {
                $stats['by_status'][$stat['status']] = $stat['count'];
            }

            // Most frequent events
            $frequentSql = "SELECT event_name, COUNT(*) as count FROM event_logs";
            $frequentParams = [];
            
            if (!empty($filters['date_from'])) {
                $frequentSql .= " WHERE created_at >= ?";
                $frequentParams[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $frequentSql .= (empty($frequentParams) ? " WHERE" : " AND") . " created_at <= ?";
                $frequentParams[] = $filters['date_to'];
            }
            
            $frequentSql .= " GROUP BY event_name ORDER BY count DESC LIMIT 10";
            
            $stats['most_frequent'] = $this->db->fetchAll($frequentSql, $frequentParams);

            // Average execution time
            $avgTimeSql = "SELECT AVG(execution_time_ms) as avg_time FROM event_logs WHERE execution_time_ms IS NOT NULL";
            $avgTimeParams = [];
            
            if (!empty($filters['date_from'])) {
                $avgTimeSql .= " AND created_at >= ?";
                $avgTimeParams[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $avgTimeSql .= " AND created_at <= ?";
                $avgTimeParams[] = $filters['date_to'];
            }
            
            $avgTime = $this->db->fetchOne($avgTimeSql, $avgTimeParams);
            $stats['avg_execution_time_ms'] = round($avgTime ?? 0, 2);

            // Queue stats
            $queueStats = $this->db->fetchAll("SELECT status, COUNT(*) as count FROM event_queue GROUP BY status");
            $stats['queue_by_status'] = [];
            foreach ($queueStats as $stat) {
                $stats['queue_by_status'][$stat['status']] = $stat['count'];
            }

            return $stats;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get event stats", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Private helper methods
     */
    private function initializeEventTables(): void
    {
        $tables = [
            "CREATE TABLE IF NOT EXISTS event_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                event_name VARCHAR(255) NOT NULL,
                event_data JSON,
                event_id VARCHAR(255),
                status ENUM('pending', 'processing', 'completed', 'failed', 'stopped') DEFAULT 'pending',
                error_message TEXT,
                execution_time_ms DECIMAL(10,2),
                listeners_executed INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_event_name (event_name),
                INDEX idx_status (status),
                INDEX idx_created_at (created_at)
            )",
            
            "CREATE TABLE IF NOT EXISTS event_queue (
                id INT AUTO_INCREMENT PRIMARY KEY,
                event_name VARCHAR(255) NOT NULL,
                event_data JSON,
                options JSON,
                status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
                error_message TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                processed_at TIMESTAMP NULL,
                INDEX idx_status (status),
                INDEX idx_created_at (created_at)
            )"
        ];

        foreach ($tables as $sql) {
            $this->db->execute($sql);
        }
    }

    private function createEventRecord(string $eventName, array $data, array $options): string
    {
        $eventId = 'event_' . uniqid();
        
        $sql = "INSERT INTO event_logs 
                (event_name, event_data, event_id, status, created_at) 
                VALUES (?, ?, ?, 'pending', NOW())";
        
        $this->db->execute($sql, [$eventName, json_encode($data), $eventId]);
        
        return $eventId;
    }

    private function updateEventRecord(string $eventId, string $status, ?string $errorMessage = null, ?float $executionTime = null, ?int $listenersExecuted = null): void
    {
        $sql = "UPDATE event_logs 
                SET status = ?, error_message = ?, execution_time_ms = ?, listeners_executed = ?, updated_at = NOW() 
                WHERE event_id = ?";
        
        $this->db->execute($sql, [
            $status,
            $errorMessage,
            $executionTime,
            $listenersExecuted,
            $eventId
        ]);
    }

    private function addToAsyncQueue(string $eventName, array $data, array $options): string
    {
        $sql = "INSERT INTO event_queue 
                (event_name, event_data, options, status, created_at) 
                VALUES (?, ?, ?, 'pending', NOW())";
        
        $this->db->execute($sql, [
            $eventName,
            json_encode($data),
            json_encode($options)
        ]);
        
        return $this->db->lastInsertId();
    }

    private function updateQueueStatus(int $queueId, string $status, ?string $result = null): void
    {
        $sql = "UPDATE event_queue 
                SET status = ?, error_message = ?, processed_at = NOW() 
                WHERE id = ?";
        
        $this->db->execute($sql, [$status, $result, $queueId]);
    }
}

/**
 * Event class for event objects
 */
class Event
{
    public string $name;
    public array $data;
    public string $id;
    public bool $propagationStopped = false;

    public function __construct(string $name, array $data = [], string $id = '')
    {
        $this->name = $name;
        $this->data = $data;
        $this->id = $id ?: 'event_' . uniqid();
    }

    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }

    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    public function getData(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->data;
        }

        return $this->data[$key] ?? $default;
    }

    public function setData(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    public function hasData(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }
}
