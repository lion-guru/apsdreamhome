<?php

namespace App\Services;

use \App\Traits\ServiceTenantTrait;

/**
 * Modern Event Bus Service
 * Advanced event-driven architecture with pub/sub system and real-time processing
 */
class EventService
{
    use \App\Traits\ServiceTenantTrait;

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

    private array $eventSubscriptions = [];
    private array $wildcardSubscriptions = [];
    private array $eventTransformers = [];
    private array $eventMiddleware = [];
    private string $processingMode;
    private int $maxEventQueueSize = 1000;
    private int $eventRetentionDays = 7;
    private array $eventHistory = [];

    public function __construct(string $processingMode = self::MODE_ASYNC)
    {
        $this->processingMode = $processingMode;
        $this->loadConfiguration();
        $this->initializeEventSystem();
    }

    /**
     * Subscribe to an event
     */
    public function subscribe(string $eventName, callable $handler, int $priority = self::PRIORITY_NORMAL): string
    {
        $subscriptionId = $this->generateSubscriptionId();
        
        if (!isset($this->eventSubscriptions[$eventName])) {
            $this->eventSubscriptions[$eventName] = [];
        }

        $this->eventSubscriptions[$eventName][] = [
            'id' => $subscriptionId,
            'handler' => $handler,
            'priority' => $priority,
            'created_at' => date('c')
        ];

        // Sort subscriptions by priority (highest first)
        usort($this->eventSubscriptions[$eventName], function ($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });

        $this->logEventOperation('subscribe', $eventName, [
            'subscription_id' => $subscriptionId,
            'priority' => $priority
        ]);

        return $subscriptionId;
    }

    /**
     * Subscribe to wildcard events
     */
    public function subscribeWildcard(string $pattern, callable $handler): string
    {
        $subscriptionId = $this->generateSubscriptionId();
        
        $this->wildcardSubscriptions[] = [
            'id' => $subscriptionId,
            'pattern' => $pattern,
            'handler' => $handler,
            'created_at' => date('c')
        ];

        $this->logEventOperation('subscribe_wildcard', $pattern, [
            'subscription_id' => $subscriptionId
        ]);

        return $subscriptionId;
    }

    /**
     * Unsubscribe from an event
     */
    public function unsubscribe(string $eventName, string $subscriptionId): bool
    {
        if (!isset($this->eventSubscriptions[$eventName])) {
            return false;
        }

        $originalCount = count($this->eventSubscriptions[$eventName]);
        $this->eventSubscriptions[$eventName] = array_filter(
            $this->eventSubscriptions[$eventName],
            fn($sub) => $sub['id'] !== $subscriptionId
        );

        $unsubscribed = count($this->eventSubscriptions[$eventName]) < $originalCount;

        if ($unsubscribed) {
            $this->logEventOperation('unsubscribe', $eventName, [
                'subscription_id' => $subscriptionId
            ]);
        }

        return $unsubscribed;
    }

    /**
     * Unsubscribe from wildcard events
     */
    public function unsubscribeWildcard(string $subscriptionId): bool
    {
        $originalCount = count($this->wildcardSubscriptions);
        $this->wildcardSubscriptions = array_filter(
            $this->wildcardSubscriptions,
            fn($sub) => $sub['id'] !== $subscriptionId
        );

        $unsubscribed = count($this->wildcardSubscriptions) < $originalCount;

        if ($unsubscribed) {
            $this->logEventOperation('unsubscribe_wildcard', '', [
                'subscription_id' => $subscriptionId
            ]);
        }

        return $unsubscribed;
    }

    /**
     * Publish an event
     */
    public function publish(string $eventName, $payload = null, array $metadata = []): string
    {
        $eventId = $this->generateEventId();
        
        $event = [
            'id' => $eventId,
            'name' => $eventName,
            'payload' => $payload,
            'metadata' => array_merge([
                'type' => self::TYPE_USER,
                'timestamp' => time(),
                'priority' => self::PRIORITY_NORMAL,
                'processing_mode' => $this->processingMode
            ], $metadata),
            'published_at' => date('c')
        ];

        // Apply event transformers
        $event = $this->transformEvent($event);

        // Run event middleware
        if (!$this->runEventMiddleware($event)) {
            return $eventId; // Middleware stopped propagation
        }

        // Process event based on mode
        switch ($this->processingMode) {
            case self::MODE_SYNC:
                $this->processEventSync($event);
                break;
            case self::MODE_ASYNC:
                $this->processEventAsync($event);
                break;
            case self::MODE_DISTRIBUTED:
                $this->processEventDistributed($event);
                break;
        }

        // Store event in history
        $this->storeEventHistory($event);

        $this->logEventOperation('publish', $eventName, [
            'event_id' => $eventId,
            'payload_size' => strlen(serialize($payload))
        ]);

        return $eventId;
    }

    /**
     * Add event transformer
     */
    public function addEventTransformer(callable $transformer): string
    {
        $transformerId = $this->generateTransformerId();
        
        $this->eventTransformers[] = [
            'id' => $transformerId,
            'transformer' => $transformer,
            'created_at' => date('c')
        ];

        $this->logEventOperation('add_transformer', '', [
            'transformer_id' => $transformerId
        ]);

        return $transformerId;
    }

    /**
     * Add event middleware
     */
    public function addEventMiddleware(callable $middleware): string
    {
        $middlewareId = $this->generateMiddlewareId();
        
        $this->eventMiddleware[] = [
            'id' => $middlewareId,
            'middleware' => $middleware,
            'created_at' => date('c')
        ];

        $this->logEventOperation('add_middleware', '', [
            'middleware_id' => $middlewareId
        ]);

        return $middlewareId;
    }

    /**
     * Get event history
     */
    public function getEventHistory(array $filters = [], int $limit = 100): array
    {
        $history = $this->eventHistory;

        // Apply filters
        if (!empty($filters['event_name'])) {
            $history = array_filter($history, fn($event) => 
                str_contains($event['name'], $filters['event_name'])
            );
        }

        if (!empty($filters['event_type'])) {
            $history = array_filter($history, fn($event) => 
                $event['metadata']['type'] === $filters['event_type']
            );
        }

        if (!empty($filters['start_date'])) {
                $startDate = strtotime($filters['start_date']);
            $history = array_filter($history, fn($event) => 
                $event['metadata']['timestamp'] >= $startDate
            );
        }

        if (!empty($filters['end_date'])) {
            $endDate = strtotime($filters['end_date']);
            $history = array_filter($history, fn($event) => 
                $event['metadata']['timestamp'] <= $endDate
            );
        }

        // Sort by timestamp (newest first)
        usort($history, function ($a, $b) {
            return $b['metadata']['timestamp'] <=> $a['metadata']['timestamp'];
        });

        return array_slice($history, 0, $limit);
    }

    /**
     * Get subscriptions
     */
    public function getSubscriptions(): array
    {
        $subscriptions = [];

        foreach ($this->eventSubscriptions as $eventName => $eventSubs) {
            foreach ($eventSubs as $sub) {
                $subscriptions[] = [
                    'event_name' => $eventName,
                    'subscription_id' => $sub['id'],
                    'priority' => $sub['priority'],
                    'created_at' => $sub['created_at']
                ];
            }
        }

        return $subscriptions;
    }

    /**
     * Get wildcard subscriptions
     */
    public function getWildcardSubscriptions(): array
    {
        return array_map(fn($sub) => [
            'pattern' => $sub['pattern'],
            'subscription_id' => $sub['id'],
            'created_at' => $sub['created_at']
        ], $this->wildcardSubscriptions);
    }

    /**
     * Generate event bus report
     */
    public function generateReport(): array
    {
        return [
            'timestamp' => date('c'),
            'processing_mode' => $this->processingMode,
            'subscriptions' => [
                'total_direct_subscriptions' => array_reduce(
                    $this->eventSubscriptions,
                    fn($carry, $subs) => $carry + count($subs),
                    0
                ),
                'total_wildcard_subscriptions' => count($this->wildcardSubscriptions),
                'event_types' => array_keys($this->eventSubscriptions)
            ],
            'event_history' => [
                'total_events' => count($this->eventHistory),
                'retention_days' => $this->eventRetentionDays,
                'max_queue_size' => $this->maxEventQueueSize
            ],
            'middleware' => [
                'total_transformers' => count($this->eventTransformers),
                'total_middleware' => count($this->eventMiddleware)
            ],
            'performance_metrics' => $this->getPerformanceMetrics()
        ];
    }

    /**
     * Clear event history
     */
    public function clearEventHistory(): bool
    {
        $cleared = count($this->eventHistory);
        $this->eventHistory = [];

        $this->logEventOperation('clear_history', '', [
            'cleared_events' => $cleared
        ]);

        return true;
    }

    /**
     * Process event synchronously
     */
    private function processEventSync(array $event): void
    {
        $this->dispatchEvent($event);
    }

    /**
     * Process event asynchronously
     */
    private function processEventAsync(array $event): void
    {
        // Async processing - dispatch via shutdown handler
        $subscriptions = $this->getMatchingSubscriptions($event['name']);
        register_shutdown_function(function () use ($event, $subscriptions) {
            $this->dispatchEvent($event);
        });
    }

    /**
     * Process event in distributed mode
     */
    private function processEventDistributed(array $event): void
    {
        // For distributed processing, we could use Redis pub/sub or message queues
        // For now, we'll log and process locally
        $this->logEventOperation('distributed_process', $event['name'], [
            'event_id' => $event['id']
        ]);

        $this->dispatchEvent($event);
    }

    /**
     * Dispatch event to subscribers
     */
    private function dispatchEvent(array $event): void
    {
        $eventName = $event['name'];
        $payload = $event['payload'];
        $metadata = $event['metadata'];

        // Process direct subscriptions
        if (isset($this->eventSubscriptions[$eventName])) {
            foreach ($this->eventSubscriptions[$eventName] as $subscription) {
                $this->executeHandler($subscription['handler'], $payload, $metadata, $eventName);
            }
        }

        // Process wildcard subscriptions
        foreach ($this->wildcardSubscriptions as $wildcard) {
            if ($this->matchWildcardPattern($wildcard['pattern'], $eventName)) {
                $this->executeHandler($wildcard['handler'], $payload, $metadata, $eventName);
            }
        }
    }

    /**
     * Execute event handler with error handling
     */
    private function executeHandler(callable $handler, $payload, array $metadata, string $eventName): void
    {
        try {
            $handler($payload, $metadata);
        } catch (\Exception $e) {
            error_log("Event handler failed for '{$eventName}': " . $e->getMessage());
        }
    }

    /**
     * Transform event using registered transformers
     */
    private function transformEvent(array $event): array
    {
        foreach ($this->eventTransformers as $transformer) {
            try {
                $event = $transformer['transformer']($event);
            } catch (\Exception $e) {
                error_log("Event transformer {$transformer['id']} failed: " . $e->getMessage());
            }
        }

        return $event;
    }

    /**
     * Run event middleware
     */
    private function runEventMiddleware(array $event): bool
    {
        foreach ($this->eventMiddleware as $middleware) {
            try {
                $result = $middleware['middleware']($event);
                if ($result === false) {
                    return false; // Middleware stopped propagation
                }
            } catch (\Exception $e) {
                error_log("Event middleware {$middleware['id']} failed: " . $e->getMessage());
            }
        }

        return true;
    }

    /**
     * Store event in history
     */
    private function storeEventHistory(array $event): void
    {
        $this->eventHistory[] = $event;
        $this->pruneEventHistory();
    }

    /**
     * Prune event history based on retention policy
     */
    private function pruneEventHistory(): void
    {
        $retentionTimestamp = time() - ($this->eventRetentionDays * 86400);
        
        $this->eventHistory = array_filter(
            $this->eventHistory,
            fn($event) => $event['metadata']['timestamp'] >= $retentionTimestamp
        );
    }

    /**
     * Match wildcard event pattern
     */
    private function matchWildcardPattern(string $pattern, string $eventName): bool
    {
        $pattern = str_replace('*', '.*', preg_quote($pattern, '/'));
        return preg_match("/^{$pattern}$/", $eventName);
    }

    /**
     * Get matching subscriptions for an event
     */
    private function getMatchingSubscriptions(string $eventName): array
    {
        $matching = [];

        if (isset($this->eventSubscriptions[$eventName])) {
            $matching = array_merge($matching, $this->eventSubscriptions[$eventName]);
        }

        foreach ($this->wildcardSubscriptions as $wildcard) {
            if ($this->matchWildcardPattern($wildcard['pattern'], $eventName)) {
                $matching[] = $wildcard;
            }
        }

        return $matching;
    }

    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics(): array
    {
        $totalSubscriptions = array_reduce(
            $this->eventSubscriptions,
            fn($carry, $subs) => $carry + count($subs),
            0
        );

        return [
            'subscriptions_per_event' => count($this->eventSubscriptions) > 0 
                ? round($totalSubscriptions / count($this->eventSubscriptions), 2) 
                : 0,
            'average_priority' => $this->calculateAveragePriority(),
            'event_frequency' => $this->calculateEventFrequency(),
            'system_health' => $this->assessSystemHealth()
        ];
    }

    /**
     * Calculate average subscription priority
     */
    private function calculateAveragePriority(): float
    {
        $allPriorities = [];
        
        foreach ($this->eventSubscriptions as $subscriptions) {
            foreach ($subscriptions as $sub) {
                $allPriorities[] = $sub['priority'];
            }
        }

        return count($allPriorities) > 0 
            ? round(array_sum($allPriorities) / count($allPriorities), 2) 
            : 0;
    }

    /**
     * Calculate event frequency
     */
    private function calculateEventFrequency(): array
    {
        if (empty($this->eventHistory)) {
            return ['events_per_hour' => 0, 'events_per_day' => 0];
        }

        $now = time();
        $oneHourAgo = $now - 3600;
        $oneDayAgo = $now - 86400;

        $eventsLastHour = array_filter(
            $this->eventHistory,
            fn($event) => $event['metadata']['timestamp'] >= $oneHourAgo
        );

        $eventsLastDay = array_filter(
            $this->eventHistory,
            fn($event) => $event['metadata']['timestamp'] >= $oneDayAgo
        );

        return [
            'events_per_hour' => count($eventsLastHour),
            'events_per_day' => count($eventsLastDay)
        ];
    }

    /**
     * Assess system health
     */
    private function assessSystemHealth(): string
    {
        $totalSubscriptions = array_reduce(
            $this->eventSubscriptions,
            fn($carry, $subs) => $carry + count($subs),
            0
        );

        $eventFrequency = $this->calculateEventFrequency();
        $eventsPerHour = $eventFrequency['events_per_hour'];

        if ($totalSubscriptions === 0) {
            return 'No subscriptions';
        } elseif ($eventsPerHour > 1000) {
            return 'High load';
        } elseif ($eventsPerHour > 100) {
            return 'Normal';
        } elseif ($eventsPerHour > 10) {
            return 'Low activity';
        } else {
            return 'Very low activity';
        }
    }

    /**
     * Load configuration
     */
    private function loadConfiguration(): void
    {
        $this->maxEventQueueSize = 1000;
        $this->eventRetentionDays = 7;
        $this->processingMode = $this->processingMode;
    }

    /**
     * Initialize event system
     */
    private function initializeEventSystem(): void
    {
        // Load event history from cache if available
        $cachedHistory = isset($GLOBALS['event_history']) ? $GLOBALS['event_history'] : [];
        if (!empty($cachedHistory)) {
            $this->eventHistory = $cachedHistory;
        }

        error_log('Event system initialized - mode: ' . $this->processingMode);
    }

    /**
     * Log event operation
     */
    private function logEventOperation(string $operation, string $eventName, array $context = []): void
    {
        error_log("Event operation: {$operation} - {$eventName}");

        // Persist event history to cache periodically
        if (count($this->eventHistory) % 10 === 0) {
            // Store in global for basic persistence
            $GLOBALS['event_history'] = $this->eventHistory;
        }
    }

    /**
     * Generate unique subscription ID
     */
    private function generateSubscriptionId(): string
    {
        return 'sub_' . uniqid() . '_' . bin2hex(random_bytes(8));
    }

    /**
     * Generate unique transformer ID
     */
    private function generateTransformerId(): string
    {
        return 'trans_' . uniqid() . '_' . bin2hex(random_bytes(8));
    }

    /**
     * Generate unique middleware ID
     */
    private function generateMiddlewareId(): string
    {
        return 'mid_' . uniqid() . '_' . bin2hex(random_bytes(8));
    }

    /**
     * Generate unique event ID
     */
    private function generateEventId(): string
    {
        return 'event_' . time() . '_' . bin2hex(random_bytes(8));
    }
}
