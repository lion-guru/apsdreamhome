<?php

namespace App\Services\Legacy;
/**
 * Advanced Event-Driven Architecture and Pub/Sub System
 * Provides robust event management, decoupled communication, and real-time processing
 */

class EventBus {
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
    private $logger;
    private $config;
    private $asyncTaskManager;

    // Configuration Parameters
    private $maxEventQueueSize = 1000;
    private $eventRetentionDays = 7;
    private $eventProcessingMode;

    // Advanced Event Handling
    private $wildcardSubscriptions = [];
    private $eventTransformers = [];
    private $eventMiddleware = [];

    public function __construct(
        $processingMode = self::MODE_ASYNC
    ) {
        $this->logger = new Logger();
        $this->config = ConfigManager::getInstance();
        $this->asyncTaskManager = new AsyncTaskManager();
        $this->eventProcessingMode = $processingMode;

        // Load configuration
        $this->loadConfiguration();
    }

    /**
     * Load event bus configuration
     */
    private function loadConfiguration() {
        $this->maxEventQueueSize = $this->config->get(
            'EVENT_MAX_QUEUE_SIZE',
            1000
        );
        $this->eventRetentionDays = $this->config->get(
            'EVENT_RETENTION_DAYS',
            7
        );
        $this->eventProcessingMode = $this->config->get(
            'EVENT_PROCESSING_MODE',
            self::MODE_ASYNC
        );
    }

    /**
     * Subscribe to an event
     *
     * @param string $eventName Event identifier
     * @param callable $handler Event handler
     * @param int $priority Subscription priority
     */
    public function subscribe(
        $eventName,
        callable $handler,
        $priority = self::PRIORITY_NORMAL
    ) {
        if (!isset($this->eventSubscriptions[$eventName])) {
            $this->eventSubscriptions[$eventName] = [];
        }

        $this->eventSubscriptions[$eventName][] = [
            'handler' => $handler,
            'priority' => $priority,
            'created_at' => time()
        ];

        // Sort subscriptions by priority
        usort($this->eventSubscriptions[$eventName], function($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });

        // Log event subscription
        $this->logger->info('Event Subscribed', [
            'event' => $eventName,
            'priority' => $priority
        ]);
    }

    /**
     * Subscribe to wildcard events
     *
     * @param string $pattern Event pattern
     * @param callable $handler Event handler
     */
    public function subscribeWildcard(
        $pattern,
        callable $handler
    ) {
        $this->wildcardSubscriptions[] = [
            'pattern' => $pattern,
            'handler' => $handler
        ];
    }

    /**
     * Publish an event
     *
     * @param string $eventName Event identifier
     * @param mixed $payload Event data
     * @param array $metadata Additional event metadata
     */
    public function publish(
        $eventName,
        $payload = null,
        array $metadata = []
    ) {
        // Manage event queue size
        $this->manageEventQueue();

        // Prepare event record
        $event = [
            'name' => $eventName,
            'payload' => $payload,
            'metadata' => array_merge([
                'type' => self::TYPE_USER,
                'timestamp' => time(),
                'priority' => self::PRIORITY_NORMAL
            ], $metadata),
            'id' => $this->generateEventId()
        ];

        // Apply event transformers
        $event = $this->transformEvent($event);

        // Run event middleware
        $this->runEventMiddleware($event);

        // Process event based on mode
        switch ($this->eventProcessingMode) {
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
    }

    /**
     * Manage event queue size
     */
    private function manageEventQueue() {
        if (count($this->eventQueue) >= $this->maxEventQueueSize) {
            // Remove oldest events
            array_shift($this->eventQueue);
        }
    }

    /**
     * Process event synchronously
     *
     * @param array $event Event record
     */
    private function processEventSync(array $event) {
        // Direct event processing
        $this->dispatchEvent($event);
    }

    /**
     * Process event asynchronously
     *
     * @param array $event Event record
     */
    private function processEventAsync(array $event) {
        // Use async task manager for background processing
        $this->asyncTaskManager->createTask(
            'Process Event: ' . ($event['name'] ?? 'unknown'),
            'event_processing',
            $event,
            AsyncTaskManager::PRIORITY_NORMAL
        );
    }

    /**
     * Process event in distributed mode
     *
     * @param array $event Event record
     */
    private function processEventDistributed(array $event) {
        // Implement distributed event processing logic
        // This could involve sending events to message queues or other services
    }

    /**
     * Dispatch event to subscribers
     *
     * @param array $event Event record
     */
    private function dispatchEvent(array $event) {
        $eventName = $event['name'];

        // Process direct subscriptions
        if (isset($this->eventSubscriptions[$eventName])) {
            foreach ($this->eventSubscriptions[$eventName] as $subscription) {
                try {
                    call_user_func_array(
                        $subscription['handler'],
                        [$event['payload'], $event['metadata']]
                    );
                } catch (\Exception $e) {
                    $this->logger->error('Event Handler Failed', [
                        'event' => $eventName,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        // Process wildcard subscriptions
        foreach ($this->wildcardSubscriptions as $wildcard) {
            if ($this->matchWildcardPattern($wildcard['pattern'], $eventName)) {
                try {
                    call_user_func_array(
                        $wildcard['handler'],
                        [$event['payload'], $event['metadata']]
                    );
                } catch (\Exception $e) {
                    $this->logger->error('Wildcard Event Handler Failed', [
                        'pattern' => $wildcard['pattern'],
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }

    /**
     * Match wildcard event pattern
     *
     * @param string $pattern Wildcard pattern
     * @param string $eventName Event name
     * @return bool Whether the pattern matches
     */
    private function matchWildcardPattern($pattern, $eventName) {
        $pattern = str_replace('\*', '.*', preg_quote($pattern, '/'));
        return preg_match("/^{$pattern}$/", $eventName);
    }

    /**
     * Transform event using registered transformers
     *
     * @param array $event Event record
     * @return array Transformed event
     */
    private function transformEvent(array $event) {
        foreach ($this->eventTransformers as $transformer) {
            $event = call_user_func_array(
                $transformer,
                [&$event]
            );
        }
        return $event;
    }

    /**
     * Run event middleware
     *
     * @param array &$event Event record
     */
    private function runEventMiddleware(array &$event) {
        foreach ($this->eventMiddleware as $middleware) {
            $result = call_user_func_array(
                $middleware,
                [&$event]
            );

            if ($result === false) {
                // Middleware can stop event propagation
                return;
            }
        }
    }

    /**
     * Store event in history
     *
     * @param array $event Event record
     */
    private function storeEventHistory(array $event) {
        $this->eventHistory[] = $event;

        // Prune old events
        $this->pruneEventHistory();
    }

    /**
     * Prune event history based on retention policy
     */
    private function pruneEventHistory() {
        $retentionTimestamp = time() -
            ($this->eventRetentionDays * 24 * 60 * 60);

        $this->eventHistory = array_filter(
            $this->eventHistory,
            function($event) use ($retentionTimestamp) {
                return $event['metadata']['timestamp'] >= $retentionTimestamp;
            }
        );
    }

    /**
     * Add event transformer
     *
     * @param callable $transformer Event transformation function
     */
    public function addEventTransformer(callable $transformer) {
        $this->eventTransformers[] = $transformer;
    }

    /**
     * Add event middleware
     *
     * @param callable $middleware Event middleware function
     */
    public function addEventMiddleware(callable $middleware) {
        $this->eventMiddleware[] = $middleware;
    }

    /**
     * Generate unique event identifier
     *
     * @return string Unique event ID
     */
    private function generateEventId() {
        return 'event_' . \App\Helpers\SecurityHelper::generateRandomString(16, false);
    }

    /**
     * Generate event bus report
     *
     * @return array Event bus statistics
     */
    public function generateReport() {
        return [
            'total_subscriptions' => array_reduce(
                $this->eventSubscriptions,
                fn($carry, $subscriptions) => $carry + count($subscriptions),
                0
            ),
            'wildcard_subscriptions' => count($this->wildcardSubscriptions),
            'event_history_size' => count($this->eventHistory),
            'processing_mode' => $this->eventProcessingMode
        ];
    }

    /**
     * Demonstrate event-driven architecture capabilities
     */
    public function demonstrateEventBus() {
        // Subscribe to specific event
        $this->subscribe('user.registered', function($payload, $metadata) {
            // Send welcome email
            echo "Sending welcome email to: {$payload['email']}\n";
        });

        // Subscribe to wildcard events
        $this->subscribeWildcard('user.*', function($payload, $metadata) {
            // Log all user-related events
            echo "User event logged: {$metadata['name']}\n";
        });

        // Add event transformer
        $this->addEventTransformer(function(&$event) {
            // Add additional metadata
            $event['metadata']['processed_at'] = time();
            return $event;
        });

        // Publish events
        $this->publish('user.registered', [
            'email' => 'john@example.com',
            'name' => 'John Doe'
        ]);

        $this->publish('user.login', [
            'user_id' => 123
        ]);

        // Generate and display report
        $report = $this->generateReport();
        print_r($report);
    }
}

// Global helper function for event bus management
function event_bus($processingMode = EventBus::MODE_ASYNC) {
    return new EventBus($processingMode);
}
