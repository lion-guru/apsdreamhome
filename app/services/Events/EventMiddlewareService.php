<?php

namespace App\Services\Events;

use App\Core\Database;
use Psr\Log\LoggerInterface;

/**
 * Modern Event Middleware Service
 * Handles event transformations, filtering, and middleware processing
 */
class EventMiddlewareService
{
    private Database $db;
    private LoggerInterface $logger;
    private array $middleware = [];
    private array $transformers = [];
    private array $filters = [];
    private bool $enabled = true;

    // Middleware priorities
    public const PRIORITY_HIGH = 3;
    public const PRIORITY_NORMAL = 2;
    public const PRIORITY_LOW = 1;

    public function __construct(Database $db, LoggerInterface $logger, array $config = [])
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->enabled = $config['enabled'] ?? true;
        $this->initializeMiddlewareTables();
    }

    /**
     * Add middleware
     */
    public function addMiddleware(string $name, callable $middleware, int $priority = self::PRIORITY_NORMAL, array $options = []): void
    {
        $this->middleware[$name] = [
            'middleware' => $middleware,
            'priority' => $priority,
            'options' => $options,
            'enabled' => $options['enabled'] ?? true,
            'registered_at' => time()
        ];

        // Sort middleware by priority
        uasort($this->middleware, function ($a, $b) {
            return $b['priority'] - $a['priority'];
        });

        $this->logger->info("Event middleware added", [
            'name' => $name,
            'priority' => $priority
        ]);
    }

    /**
     * Add event transformer
     */
    public function addTransformer(string $name, callable $transformer, array $options = []): void
    {
        $this->transformers[$name] = [
            'transformer' => $transformer,
            'options' => $options,
            'enabled' => $options['enabled'] ?? true,
            'registered_at' => time()
        ];

        $this->logger->info("Event transformer added", ['name' => $name]);
    }

    /**
     * Add event filter
     */
    public function addFilter(string $name, callable $filter, array $options = []): void
    {
        $this->filters[$name] = [
            'filter' => $filter,
            'options' => $options,
            'enabled' => $options['enabled'] ?? true,
            'registered_at' => time()
        ];

        $this->logger->info("Event filter added", ['name' => $name]);
    }

    /**
     * Process event through middleware chain
     */
    public function processEvent(object $event): object
    {
        if (!$this->enabled) {
            return $event;
        }

        try {
            $startTime = microtime(true);
            $originalEvent = clone $event;

            // Apply filters
            foreach ($this->filters as $filterName => $filterInfo) {
                if (!$filterInfo['enabled']) continue;

                try {
                    $result = ($filterInfo['filter'])($event);
                    if ($result === false) {
                        $this->logger->warning("Event blocked by filter", [
                            'filter' => $filterName,
                            'event' => $event->name
                        ]);
                        return $event; // Filter blocked event
                    }
                } catch (\Exception $e) {
                    $this->logger->error("Event filter error", [
                        'filter' => $filterName,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Apply transformers
            foreach ($this->transformers as $transformerName => $transformerInfo) {
                if (!$transformerInfo['enabled']) continue;

                try {
                    $event = ($transformerInfo['transformer'])($event);
                } catch (\Exception $e) {
                    $this->logger->error("Event transformer error", [
                        'transformer' => $transformerName,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Apply middleware
            foreach ($this->middleware as $middlewareName => $middlewareInfo) {
                if (!$middlewareInfo['enabled']) continue;

                try {
                    $result = ($middlewareInfo['middleware'])($event);
                    if ($result === false) {
                        $this->logger->warning("Event stopped by middleware", [
                            'middleware' => $middlewareName,
                            'event' => $event->name
                        ]);
                        return $event; // Middleware stopped event
                    }
                    $event = $result; // Middleware can modify event
                } catch (\Exception $e) {
                    $this->logger->error("Event middleware error", [
                        'middleware' => $middlewareName,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $processingTime = (microtime(true) - $startTime) * 1000;

            $this->logger->info("Event processed through middleware", [
                'event' => $event->name,
                'filters_applied' => count($this->filters),
                'transformers_applied' => count($this->transformers),
                'middleware_applied' => count($this->middleware),
                'processing_time_ms' => round($processingTime, 2)
            ]);

            return $event;

        } catch (\Exception $e) {
            $this->logger->error("Event middleware processing failed", [
                'event' => $event->name,
                'error' => $e->getMessage()
            ]);

            return $originalEvent;
        }
    }

    /**
     * Get middleware statistics
     */
    public function getMiddlewareStats(): array
    {
        try {
            $stats = [];

            // Total middleware
            $stats['total_middleware'] = count($this->middleware);

            // Total transformers
            $stats['total_transformers'] = count($this->transformers);

            // Total filters
            $stats['total_filters'] = count($this->filters);

            // Enabled counts
            $stats['enabled_middleware'] = count(array_filter($this->middleware, fn($m) => $m['enabled']));
            $stats['enabled_transformers'] = count(array_filter($this->transformers, fn($t) => $t['enabled']));
            $stats['enabled_filters'] = count(array_filter($this->filters, fn($f) => $f['enabled']));

            // Processing statistics
            $processingStats = $this->db->fetchOne("
                SELECT 
                    COUNT(*) as total_events,
                    AVG(processing_time_ms) as avg_processing_time,
                    MAX(processing_time_ms) as max_processing_time
                FROM event_middleware_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ");

            $stats['processing_stats'] = $processingStats ?? [
                'total_events' => 0,
                'avg_processing_time_ms' => 0,
                'max_processing_time_ms' => 0
            ];

            return $stats;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get middleware stats", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Enable/disable middleware
     */
    public function toggleMiddleware(string $name, bool $enabled): bool
    {
        if (!isset($this->middleware[$name])) {
            return false;
        }

        $this->middleware[$name]['enabled'] = $enabled;

        $this->logger->info("Middleware toggled", [
            'name' => $name,
            'enabled' => $enabled
        ]);

        return true;
    }

    /**
     * Enable/disable transformer
     */
    public function toggleTransformer(string $name, bool $enabled): bool
    {
        if (!isset($this->transformers[$name])) {
            return false;
        }

        $this->transformers[$name]['enabled'] = $enabled;

        $this->logger->info("Transformer toggled", [
            'name' => $name,
            'enabled' => $enabled
        ]);

        return true;
    }

    /**
     * Enable/disable filter
     */
    public function toggleFilter(string $name, bool $enabled): bool
    {
        if (!isset($this->filters[$name])) {
            return false;
        }

        $this->filters[$name]['enabled'] = $enabled;

        $this->logger->info("Filter toggled", [
            'name' => $name,
            'enabled' => $enabled
        ]);

        return true;
    }

    /**
     * Get all middleware
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * Get all transformers
     */
    public function getTransformers(): array
    {
        return $this->transformers;
    }

    /**
     * Get all filters
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Clear all middleware
     */
    public function clearMiddleware(): void
    {
        $this->middleware = [];
        $this->logger->info("All middleware cleared");
    }

    /**
     * Clear all transformers
     */
    public function clearTransformers(): void
    {
        $this->transformers = [];
        $this->logger->info("All transformers cleared");
    }

    /**
     * Clear all filters
     */
    public function clearFilters(): void
    {
        $this->filters = [];
        $this->logger->info("All filters cleared");
    }

    /**
     * Create built-in filters
     */
    public function createBuiltInFilters(): void
    {
        // Rate limiting filter
        $this->addFilter('rate_limiter', function ($event) {
            $eventKey = $event->name . '_' . ($event->data['user_id'] ?? 'anonymous');
            
            // Check rate limit (simplified)
            static $eventCounts = [];
            $currentTime = time();
            
            if (!isset($eventCounts[$eventKey])) {
                $eventCounts[$eventKey] = [];
            }
            
            // Clean old events (older than 1 minute)
            $eventCounts[$eventKey] = array_filter($eventCounts[$eventKey], fn($time) => $currentTime - $time < 60);
            
            $eventCounts[$eventKey][] = $currentTime;
            
            // Allow max 10 events per minute
            if (count($eventCounts[$eventKey]) > 10) {
                return false;
            }
            
            return true;
        }, ['enabled' => true]);

        // Data validation filter
        $this->addFilter('data_validator', function ($event) {
            // Validate required event data
            if (empty($event->data) || !is_array($event->data)) {
                return false;
            }
            
            // Validate event name
            if (empty($event->name) || !is_string($event->name)) {
                return false;
            }
            
            return true;
        }, ['enabled' => true]);

        // Security filter
        $this->addFilter('security', function ($event) {
            // Check for suspicious patterns
            $suspiciousPatterns = ['/admin/', '/delete/', '/drop/', 'exec', 'eval'];
            
            foreach ($suspiciousPatterns as $pattern) {
                if (stripos(json_encode($event->data), $pattern) !== false) {
                    $this->logger->warning("Suspicious event pattern detected", [
                        'event' => $event->name,
                        'pattern' => $pattern
                    ]);
                    return false;
                }
            }
            
            return true;
        }, ['enabled' => true]);

        $this->logger->info("Built-in filters created");
    }

    /**
     * Create built-in transformers
     */
    public function createBuiltInTransformers(): void
    {
        // Timestamp transformer
        $this->addTransformer('timestamp', function ($event) {
            $event->timestamp = microtime(true);
            $event->formatted_timestamp = date('Y-m-d H:i:s', $event->timestamp);
            return $event;
        }, ['enabled' => true]);

        // Data enrichment transformer
        $this->addTransformer('data_enrichment', function ($event) {
            if (!isset($event->data)) {
                $event->data = [];
            }
            
            // Add common metadata
            $event->data['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $event->data['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            $event->data['request_id'] = uniqid();
            $event->data['processed_at'] = date('Y-m-d H:i:s');
            
            return $event;
        }, ['enabled' => true]);

        // Priority transformer
        $this->addTransformer('priority', function ($event) {
            if (!isset($event->priority)) {
                // Set priority based on event name
                $highPriorityEvents = ['user_login', 'security_alert', 'system_error'];
                $event->priority = in_array($event->name, $highPriorityEvents) ? 'high' : 'normal';
            }
            
            return $event;
        }, ['enabled' => true]);

        $this->logger->info("Built-in transformers created");
    }

    /**
     * Private helper methods
     */
    private function initializeEventTables(): void
    {
        $tables = [
            "CREATE TABLE IF NOT EXISTS event_middleware_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                event_name VARCHAR(255),
                filters_applied INT DEFAULT 0,
                transformers_applied INT DEFAULT 0,
                processing_time_ms DECIMAL(10,2),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_event_name (event_name),
                INDEX idx_created_at (created_at)
            )"
        ];

        foreach ($tables as $sql) {
            $this->db->execute($sql);
        }
    }

    /**
     * Log middleware processing
     */
    private function logMiddlewareProcessing(string $eventName, array $middleware, array $transformers, float $processingTime): void
    {
        $sql = "INSERT INTO event_middleware_logs 
                (event_name, filters_applied, transformers_applied, processing_time_ms, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        
        $this->db->execute($sql, [
            $eventName,
            count($middleware),
            count($transformers),
            $processingTime
        ]);
    }
}
