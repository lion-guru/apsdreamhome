<?php

namespace App\Services\Legacy;
/**
 * Event System
 * Provides a robust publish-subscribe (pub/sub) event management system
 */

// require_once __DIR__ . '/logger.php';
require_once __DIR__ . '/config_manager.php';

class EventDispatcher {
    private static $instance = null;
    private $listeners = [];
    private $wildcardListeners = [];
    private $logger;
    private $config;
    private $asyncQueue = [];

    /**
     * Private constructor to enforce singleton pattern
     */
    private function __construct() {
        $this->logger = new Logger();
        $this->config = ConfigManager::getInstance();
    }

    /**
     * Get singleton instance of EventDispatcher
     * 
     * @return EventDispatcher
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register an event listener
     * 
     * @param string $eventName Event name (can use wildcards)
     * @param callable $callback Listener callback
     * @param int $priority Listener priority (higher = earlier execution)
     * @return string Unique listener ID
     */
    public function on($eventName, callable $callback, $priority = 0) {
        $listenerId = 'listener_' . \App\Helpers\SecurityHelper::generateRandomString(16, false);

        // Handle wildcard events
        if (strpos($eventName, '*') !== false) {
            $this->wildcardListeners[] = [
                'pattern' => $this->convertWildcardToRegex($eventName),
                'callback' => $callback,
                'priority' => $priority,
                'id' => $listenerId
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

        return $listenerId;
    }

    /**
     * Convert wildcard pattern to regex
     * 
     * @param string $pattern Wildcard pattern
     * @return string Regex pattern
     */
    private function convertWildcardToRegex($pattern) {
        $pattern = preg_quote($pattern, '/');
        $pattern = str_replace('\*', '.*', $pattern);
        return '/^' . $pattern . '$/';
    }

    /**
     * Remove an event listener
     * 
     * @param string $listenerId Listener ID to remove
     * @return bool Whether listener was removed
     */
    public function off($listenerId) {
        // Remove from regular listeners
        foreach ($this->listeners as $eventName => &$eventListeners) {
            foreach ($eventListeners as $key => $listener) {
                if ($listener['id'] === $listenerId) {
                    unset($eventListeners[$key]);
                    return true;
                }
            }
        }

        // Remove from wildcard listeners
        foreach ($this->wildcardListeners as $key => $listener) {
            if ($listener['id'] === $listenerId) {
                unset($this->wildcardListeners[$key]);
                return true;
            }
        }

        return false;
    }

    /**
     * Dispatch an event
     * 
     * @param string $eventName Event name
     * @param mixed $payload Event payload
     * @param bool $async Whether to dispatch asynchronously
     * @return mixed Event results
     */
    public function dispatch($eventName, $payload = null, $async = false) {
        // Async event handling
        if ($async) {
            $this->asyncQueue[] = [
                'name' => $eventName,
                'payload' => $payload,
                'timestamp' => microtime(true)
            ];
            return null;
        }

        $results = [];

        // Regular event listeners
        if (isset($this->listeners[$eventName])) {
            foreach ($this->listeners[$eventName] as $listener) {
                try {
                    $result = call_user_func($listener['callback'], $payload);
                    $results[] = $result;
                } catch (Exception $e) {
                    $this->logger->error('Event Listener Error', [
                        'event' => $eventName,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        // Wildcard event listeners
        foreach ($this->wildcardListeners as $wildcardListener) {
            if (preg_match($wildcardListener['pattern'], $eventName)) {
                try {
                    $result = call_user_func($wildcardListener['callback'], $eventName, $payload);
                    $results[] = $result;
                } catch (Exception $e) {
                    $this->logger->error('Wildcard Event Listener Error', [
                        'event' => $eventName,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        return $results;
    }

    /**
     * Process async event queue
     */
    public function processAsyncQueue() {
        $maxProcessingTime = $this->config->get('ASYNC_EVENT_MAX_TIME', 30);
        $startTime = microtime(true);

        while (!empty($this->asyncQueue)) {
            // Check processing time
            if (microtime(true) - $startTime > $maxProcessingTime) {
                $this->logger->warning('Async event processing timeout');
                break;
            }

            $event = array_shift($this->asyncQueue);
            $this->dispatch($event['name'], $event['payload']);
        }
    }

    /**
     * Create a one-time event listener
     * 
     * @param string $eventName Event name
     * @param callable $callback Listener callback
     * @return string Listener ID
     */
    public function once($eventName, callable $callback) {
        $wrappedCallback = function($payload) use (&$wrappedCallback, $eventName, $callback) {
            // Remove listener after first execution
            $this->off($this->on($eventName, $wrappedCallback));
            return $callback($payload);
        };

        return $this->on($eventName, $wrappedCallback);
    }
}

// Global event dispatcher function
function events() {
    return EventDispatcher::getInstance();
}

// Example predefined events
class EventTypes {
    const USER_REGISTERED = 'user.registered';
    const USER_LOGIN = 'user.login';
    const DATABASE_QUERY = 'database.query';
    const SECURITY_ALERT = 'security.alert';
}

// Example usage
events()->on(EventTypes::USER_REGISTERED, function($user) {
    // Send welcome email
    logger()->info('User registered', ['user_id' => $user->id]);
});

events()->on('user.*', function($eventName, $payload) {
    // Wildcard event listener
    logger()->debug("User-related event: $eventName", ['payload' => $payload]);
});

// Shutdown function to process async events
register_shutdown_function(function() {
    events()->processAsyncQueue();
});
