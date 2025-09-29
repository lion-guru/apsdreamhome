<?php
/**
 * Advanced Event System
 * Provides a robust publish-subscribe (pub/sub) event management system
 * with support for wildcards, priorities, async events, and middleware
 */

class EventDispatcher {
    private static $instance = null;
    private $listeners = [];
    private $wildcardListeners = [];
    private $middleware = [];
    private $asyncQueue = [];
    private $logger;
    private $config;
    private $eventHistory = [];
    private $maxHistorySize = 1000;

    // Event states
    const EVENT_STATE_PENDING = 'pending';
    const EVENT_STATE_PROCESSING = 'processing';
    const EVENT_STATE_COMPLETED = 'completed';
    const EVENT_STATE_FAILED = 'failed';

    /**
     * Private constructor to enforce singleton pattern
     */
    private function __construct() {
        $this->logger = new Logger();
        $this->config = ConfigManager::getInstance();

        // Initialize event system
        $this->initializeEventSystem();
    }

    /**
     * Initialize event system
     */
    private function initializeEventSystem() {
        // Register core event listeners
        $this->registerCoreListeners();

        // Set up async processing if enabled
        if ($this->config->get('events.async_processing', false)) {
            $this->setupAsyncProcessing();
        }
    }

    /**
     * Register core event listeners
     */
    private function registerCoreListeners() {
        // User authentication events
        $this->on('user.login', [$this, 'handleUserLogin'], 10);
        $this->on('user.logout', [$this, 'handleUserLogout'], 10);
        $this->on('user.register', [$this, 'handleUserRegister'], 10);

        // Security events
        $this->on('security.breach', [$this, 'handleSecurityBreach'], 1);
        $this->on('security.suspicious_activity', [$this, 'handleSuspiciousActivity'], 1);

        // Performance events
        $this->on('performance.slow_query', [$this, 'handleSlowQuery'], 5);
        $this->on('performance.high_memory', [$this, 'handleHighMemory'], 5);

        // Database events
        $this->on('database.error', [$this, 'handleDatabaseError'], 1);
        $this->on('database.slow_query', [$this, 'handleDatabaseSlowQuery'], 5);
    }

    /**
     * Get singleton instance of EventDispatcher
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register an event listener
     */
    public function on($eventName, callable $callback, $priority = 0) {
        $listenerId = uniqid('listener_', true);

        // Handle wildcard events
        if (strpos($eventName, '*') !== false || strpos($eventName, '#') !== false) {
            $this->wildcardListeners[] = [
                'pattern' => $this->convertPatternToRegex($eventName),
                'callback' => $callback,
                'priority' => $priority,
                'id' => $listenerId,
                'event_name' => $eventName
            ];

            // Sort wildcard listeners by priority
            usort($this->wildcardListeners, function($a, $b) {
                return $b['priority'] - $a['priority'];
            });
        } else {
            // Regular event listeners
            if (!isset($this->listeners[$eventName])) {
                $this->listeners[$eventName] = [];
            }

            $this->listeners[$eventName][] = [
                'callback' => $callback,
                'priority' => $priority,
                'id' => $listenerId
            ];

            // Sort listeners by priority
            usort($this->listeners[$eventName], function($a, $b) {
                return $b['priority'] - $a['priority'];
            });
        }

        $this->logEvent('LISTENER_REGISTERED', [
            'listener_id' => $listenerId,
            'event_name' => $eventName,
            'priority' => $priority
        ]);

        return $listenerId;
    }

    /**
     * Convert wildcard pattern to regex
     */
    private function convertPatternToRegex($pattern) {
        // Convert MQTT-style wildcards to regex
        $pattern = preg_quote($pattern, '/');

        // Replace wildcards
        $pattern = str_replace('\\*', '.*', $pattern);  // * matches any characters
        $pattern = str_replace('\\#', '.*', $pattern);  // # matches any characters (MQTT style)

        return '/^' . $pattern . '$/';
    }

    /**
     * Remove an event listener
     */
    public function off($listenerId) {
        $removed = false;

        // Remove from regular listeners
        foreach ($this->listeners as $eventName => $listeners) {
            foreach ($listeners as $index => $listener) {
                if ($listener['id'] === $listenerId) {
                    unset($this->listeners[$eventName][$index]);
                    $removed = true;
                    break 2;
                }
            }
        }

        // Remove from wildcard listeners
        foreach ($this->wildcardListeners as $index => $listener) {
            if ($listener['id'] === $listenerId) {
                unset($this->wildcardListeners[$index]);
                $removed = true;
                break;
            }
        }

        if ($removed) {
            $this->logEvent('LISTENER_REMOVED', [
                'listener_id' => $listenerId
            ]);
        }

        return $removed;
    }

    /**
     * Dispatch an event
     */
    public function emit($eventName, $data = [], $context = []) {
        $eventId = uniqid('event_', true);

        $event = [
            'id' => $eventId,
            'name' => $eventName,
            'data' => $data,
            'context' => $context,
            'timestamp' => microtime(true),
            'state' => self::EVENT_STATE_PENDING
        ];

        // Add to history
        $this->addToHistory($event);

        // Log event dispatch
        $this->logEvent('EVENT_DISPATCHED', [
            'event_id' => $eventId,
            'event_name' => $eventName,
            'data' => $data
        ]);

        // Process middleware
        $event = $this->processMiddleware($event);

        if ($event['state'] === self::EVENT_STATE_FAILED) {
            return false;
        }

        // Get listeners for this event
        $listeners = $this->getListenersForEvent($eventName);

        // Process event with listeners
        $results = [];
        $event['state'] = self::EVENT_STATE_PROCESSING;

        foreach ($listeners as $listener) {
            try {
                $startTime = microtime(true);

                // Call the listener
                $result = call_user_func($listener['callback'], $event['data'], $event);

                $executionTime = microtime(true) - $startTime;

                $results[] = [
                    'success' => true,
                    'result' => $result,
                    'execution_time' => $executionTime,
                    'listener_id' => $listener['id']
                ];

                // Log successful listener execution
                $this->logEvent('LISTENER_EXECUTED', [
                    'event_id' => $eventId,
                    'event_name' => $eventName,
                    'listener_id' => $listener['id'],
                    'execution_time' => $executionTime
                ]);

            } catch (Exception $e) {
                $results[] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'listener_id' => $listener['id']
                ];

                // Log listener failure
                $this->logEvent('LISTENER_FAILED', [
                    'event_id' => $eventId,
                    'event_name' => $eventName,
                    'listener_id' => $listener['id'],
                    'error' => $e->getMessage()
                ]);
            }
        }

        $event['state'] = self::EVENT_STATE_COMPLETED;
        $event['results'] = $results;
        $event['completion_time'] = microtime(true);

        // Update history
        $this->updateHistory($eventId, $event);

        // Handle async events
        if ($this->config->get('events.async_processing', false)) {
            $this->handleAsyncEvents($event);
        }

        return [
            'event_id' => $eventId,
            'event_name' => $eventName,
            'results' => $results,
            'success' => count($results) > 0
        ];
    }

    /**
     * Get listeners for a specific event
     */
    private function getListenersForEvent($eventName) {
        $listeners = [];

        // Get regular listeners
        if (isset($this->listeners[$eventName])) {
            $listeners = array_merge($listeners, $this->listeners[$eventName]);
        }

        // Get wildcard listeners
        foreach ($this->wildcardListeners as $wildcardListener) {
            if (preg_match($wildcardListener['pattern'], $eventName)) {
                $listeners[] = $wildcardListener;
            }
        }

        // Sort by priority
        usort($listeners, function($a, $b) {
            return $b['priority'] - $a['priority'];
        });

        return $listeners;
    }

    /**
     * Process event middleware
     */
    private function processMiddleware($event) {
        foreach ($this->middleware as $middleware) {
            try {
                $event = $middleware->process($event);

                if ($event['state'] === self::EVENT_STATE_FAILED) {
                    break;
                }
            } catch (Exception $e) {
                $event['state'] = self::EVENT_STATE_FAILED;
                $event['error'] = $e->getMessage();

                $this->logEvent('MIDDLEWARE_FAILED', [
                    'event_id' => $event['id'],
                    'event_name' => $event['name'],
                    'error' => $e->getMessage()
                ]);

                break;
            }
        }

        return $event;
    }

    /**
     * Add middleware to the event pipeline
     */
    public function addMiddleware($middleware) {
        $this->middleware[] = $middleware;
    }

    /**
     * Add event to history
     */
    private function addToHistory($event) {
        $this->eventHistory[] = $event;

        // Maintain history size limit
        if (count($this->eventHistory) > $this->maxHistorySize) {
            array_shift($this->eventHistory);
        }
    }

    /**
     * Update event in history
     */
    private function updateHistory($eventId, $event) {
        foreach ($this->eventHistory as &$historyEvent) {
            if ($historyEvent['id'] === $eventId) {
                $historyEvent = array_merge($historyEvent, $event);
                break;
            }
        }
    }

    /**
     * Get event history
     */
    public function getEventHistory($limit = 100, $eventName = null) {
        $history = $this->eventHistory;

        if ($eventName) {
            $history = array_filter($history, function($event) use ($eventName) {
                return $event['name'] === $eventName;
            });
        }

        return array_slice(array_reverse($history), 0, $limit);
    }

    /**
     * Set up async event processing
     */
    private function setupAsyncProcessing() {
        // This would typically use a queue system like Redis or database
        // For now, we'll use a simple file-based queue
        $this->asyncQueue = [];
    }

    /**
     * Handle async events
     */
    private function handleAsyncEvents($event) {
        // Add to async queue for processing
        $this->asyncQueue[] = $event;

        // Process queue in background if needed
        $this->processAsyncQueue();
    }

    /**
     * Process async event queue
     */
    private function processAsyncQueue() {
        // Simple implementation - in production, this would be handled by a worker process
        foreach ($this->asyncQueue as $index => $event) {
            // Process async event
            $this->emit($event['name'], $event['data'], array_merge($event['context'], ['async' => true]));

            // Remove from queue
            unset($this->asyncQueue[$index]);
        }
    }

    /**
     * Log event system activity
     */
    private function logEvent($action, $data = []) {
        $logData = [
            'action' => $action,
            'timestamp' => date('Y-m-d H:i:s'),
            'data' => $data
        ];

        // Log to system logger if available
        if ($this->logger) {
            $this->logger->info("EventSystem: $action", $data);
        }

        // Could also save to database or file
    }

    // Event Handlers

    /**
     * Handle user login event
     */
    public function handleUserLogin($data, $event) {
        // Log successful login
        $this->logEvent('USER_LOGIN', [
            'user_id' => $data['user_id'] ?? null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        // Could trigger additional actions like:
        // - Send welcome notification
        // - Update user activity
        // - Cache user data
    }

    /**
     * Handle user logout event
     */
    public function handleUserLogout($data, $event) {
        // Log logout
        $this->logEvent('USER_LOGOUT', [
            'user_id' => $data['user_id'] ?? null,
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        // Clean up user sessions, cache, etc.
    }

    /**
     * Handle user registration event
     */
    public function handleUserRegister($data, $event) {
        // Log registration
        $this->logEvent('USER_REGISTER', [
            'user_id' => $data['user_id'] ?? null,
            'email' => $data['email'] ?? null,
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        // Could trigger:
        // - Send welcome email
        // - Create user profile
        // - Assign default permissions
    }

    /**
     * Handle security breach event
     */
    public function handleSecurityBreach($data, $event) {
        // Critical security event - take immediate action
        $this->logEvent('SECURITY_BREACH_DETECTED', $data);

        // Could trigger:
        // - Block IP address
        // - Send security alerts
        // - Disable user account
        // - Trigger security protocols
    }

    /**
     * Handle suspicious activity
     */
    public function handleSuspiciousActivity($data, $event) {
        $this->logEvent('SUSPICIOUS_ACTIVITY', $data);

        // Monitor and potentially escalate
    }

    /**
     * Handle slow query event
     */
    public function handleSlowQuery($data, $event) {
        $this->logEvent('SLOW_QUERY_DETECTED', $data);

        // Could trigger query optimization or caching
    }

    /**
     * Handle high memory usage
     */
    public function handleHighMemory($data, $event) {
        $this->logEvent('HIGH_MEMORY_USAGE', $data);

        // Could trigger garbage collection or memory optimization
    }

    /**
     * Handle database errors
     */
    public function handleDatabaseError($data, $event) {
        $this->logEvent('DATABASE_ERROR', $data);

        // Could trigger database maintenance or failover
    }

    /**
     * Handle database slow queries
     */
    public function handleDatabaseSlowQuery($data, $event) {
        $this->logEvent('DATABASE_SLOW_QUERY', $data);

        // Could trigger query optimization
    }

    /**
     * Get event statistics
     */
    public function getStatistics() {
        $totalEvents = count($this->eventHistory);
        $totalListeners = count($this->listeners) + count($this->wildcardListeners);

        $eventCounts = [];
        foreach ($this->eventHistory as $event) {
            $eventCounts[$event['name']] = ($eventCounts[$event['name']] ?? 0) + 1;
        }

        return [
            'total_events' => $totalEvents,
            'total_listeners' => $totalListeners,
            'event_counts' => $eventCounts,
            'wildcard_listeners' => count($this->wildcardListeners),
            'regular_listeners' => count($this->listeners),
            'middleware_count' => count($this->middleware)
        ];
    }

    /**
     * Clear event history
     */
    public function clearHistory() {
        $this->eventHistory = [];
    }

    /**
     * Get listener information
     */
    public function getListeners($eventName = null) {
        if ($eventName) {
            return $this->getListenersForEvent($eventName);
        }

        return [
            'regular' => $this->listeners,
            'wildcard' => $this->wildcardListeners
        ];
    }
}

// Middleware Interface
interface EventMiddleware {
    public function process($event);
}

// Example Middleware Implementation
class SecurityEventMiddleware implements EventMiddleware {
    public function process($event) {
        // Add security checks to events
        if ($this->isSecurityEvent($event['name'])) {
            // Validate event data for security
            $event = $this->validateSecurityEvent($event);
        }

        return $event;
    }

    private function isSecurityEvent($eventName) {
        $securityEvents = ['security.*', 'user.*', 'auth.*'];
        foreach ($securityEvents as $pattern) {
            if (fnmatch($pattern, $eventName)) {
                return true;
            }
        }
        return false;
    }

    private function validateSecurityEvent($event) {
        // Add security validation logic
        // For example, check for suspicious data patterns
        return $event;
    }
}

// Utility Functions
function event($eventName, $data = [], $context = []) {
    return EventDispatcher::getInstance()->emit($eventName, $data, $context);
}

function on($eventName, $callback, $priority = 0) {
    return EventDispatcher::getInstance()->on($eventName, $callback, $priority);
}

function off($listenerId) {
    return EventDispatcher::getInstance()->off($listenerId);
}
?>
