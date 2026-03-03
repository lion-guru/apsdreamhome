<?php
namespace App\Microservices;

use Redis;

class QueueManager
{
    private $redis;
    private $queues = [];
    private $workers = [];
    private $config;
    
    public function __construct()
    {
        $this->config = [
            'redis_host' => 'localhost',
            'redis_port' => 6379,
            'redis_db' => 0,
            'max_retries' => 3,
            'retry_delay' => 5,
            'visibility_timeout' => 30,
            'max_message_size' => 1024 * 1024 // 1MB
        ];
        
        $this->connect();
        $this->initializeQueues();
    }
    
    /**
     * Connect to Redis
     */
    private function connect()
    {
        $this->redis = new Redis();
        $this->redis->connect($this->config['redis_host'], $this->config['redis_port']);
        $this->redis->select($this->config['redis_db']);
    }
    
    /**
     * Initialize queues
     */
    private function initializeQueues()
    {
        $this->queues = [
            'email_queue' => [
                'name' => 'email_queue',
                'max_size' => 1000,
                'retry_policy' => 'exponential_backoff',
                'dead_letter_queue' => 'email_dlq'
            ],
            'notification_queue' => [
                'name' => 'notification_queue',
                'max_size' => 500,
                'retry_policy' => 'fixed_delay',
                'dead_letter_queue' => 'notification_dlq'
            ],
            'analytics_queue' => [
                'name' => 'analytics_queue',
                'max_size' => 2000,
                'retry_policy' => 'exponential_backoff',
                'dead_letter_queue' => 'analytics_dlq'
            ],
            'payment_queue' => [
                'name' => 'payment_queue',
                'max_size' => 100,
                'retry_policy' => 'immediate',
                'dead_letter_queue' => 'payment_dlq'
            ],
            'search_queue' => [
                'name' => 'search_queue',
                'max_size' => 500,
                'retry_policy' => 'exponential_backoff',
                'dead_letter_queue' => 'search_dlq'
            ]
        ];
    }
    
    /**
     * Publish message to queue
     */
    public function publish($queueName, $message, $priority = 'normal', $delay = 0)
    {
        if (!isset($this->queues[$queueName])) {
            throw new Exception("Queue {$queueName} not found");
        }
        
        $queue = $this->queues[$queueName];
        
        // Validate message size
        if (strlen(json_encode($message)) > $this->config['max_message_size']) {
            throw new Exception('Message too large');
        }
        
        $messageData = [
            'id' => $this->generateMessageId(),
            'queue' => $queueName,
            'payload' => $message,
            'priority' => $priority,
            'attempts' => 0,
            'created_at' => time(),
            'delay_until' => time() + $delay,
            'visibility_timeout' => $this->config['visibility_timeout']
        ];
        
        // Add to queue
        if ($delay > 0) {
            $this->addToDelayedQueue($queueName, $messageData);
        } else {
            $this->addToQueue($queueName, $messageData);
        }
        
        return $messageData['id'];
    }
    
    /**
     * Subscribe to queue
     */
    public function subscribe($queueName, $callback, $options = [])
    {
        if (!isset($this->queues[$queueName])) {
            throw new Exception("Queue {$queueName} not found");
        }
        
        $worker = new QueueWorker($queueName, $callback, $options);
        $this->workers[$queueName][] = $worker;
        
        return $worker;
    }
    
    /**
     * Add message to queue
     */
    private function addToQueue($queueName, $messageData)
    {
        $key = $this->getQueueKey($queueName);
        
        // Use priority queue based on message priority
        switch ($messageData['priority']) {
            case 'high':
                $this->redis->lpush($key . ':high', json_encode($messageData));
                break;
            case 'normal':
                $this->redis->lpush($key . ':normal', json_encode($messageData));
                break;
            case 'low':
                $this->redis->lpush($key . ':low', json_encode($messageData));
                break;
            default:
                $this->redis->lpush($key . ':normal', json_encode($messageData));
        }
        
        // Update queue size
        $this->updateQueueSize($queueName);
    }
    
    /**
     * Add message to delayed queue
     */
    private function addToDelayedQueue($queueName, $messageData)
    {
        $key = $this->getDelayedQueueKey($queueName);
        $score = $messageData['delay_until'];
        
        $this->redis->zadd($key, $score, json_encode($messageData));
    }
    
    /**
     * Process delayed messages
     */
    public function processDelayedMessages()
    {
        foreach ($this->queues as $queueName => $queue) {
            $key = $this->getDelayedQueueKey($queueName);
            $currentTime = time();
            
            // Get messages ready to be processed
            $messages = $this->redis->zrangebyscore($key, 0, $currentTime, 0, 10);
            
            foreach ($messages as $message) {
                $messageData = json_decode($message, true);
                
                // Move to regular queue
                $this->addToQueue($queueName, $messageData);
                
                // Remove from delayed queue
                $this->redis->zrem($key, $message);
            }
        }
    }
    
    /**
     * Get next message from queue
     */
    public function getNextMessage($queueName, $timeout = 0)
    {
        if (!isset($this->queues[$queueName])) {
            throw new Exception("Queue {$queueName} not found");
        }
        
        // Try high priority first
        $key = $this->getQueueKey($queueName) . ':high';
        $message = $this->redis->brpop($key, $timeout);
        
        if ($message) {
            return json_decode($message[1], true);
        }
        
        // Try normal priority
        $key = $this->getQueueKey($queueName) . ':normal';
        $message = $this->redis->brpop($key, $timeout);
        
        if ($message) {
            return json_decode($message[1], true);
        }
        
        // Try low priority
        $key = $this->getQueueKey($queueName) . ':low';
        $message = $this->redis->brpop($key, $timeout);
        
        if ($message) {
            return json_decode($message[1], true);
        }
        
        return null;
    }
    
    /**
     * Acknowledge message
     */
    public function ack($queueName, $messageId)
    {
        // Message is already removed from queue when processed
        // This is for tracking purposes
        $this->logMessageProcessed($queueName, $messageId);
        
        return true;
    }
    
    /**
     * Reject message (retry or move to dead letter queue)
     */
    public function reject($queueName, $messageData, $reason = '')
    {
        $queue = $this->queues[$queueName];
        $messageData['attempts']++;
        
        if ($messageData['attempts'] >= $this->config['max_retries']) {
            // Move to dead letter queue
            $this->moveToDeadLetterQueue($queueName, $messageData, $reason);
        } else {
            // Retry message
            $this->retryMessage($queueName, $messageData);
        }
    }
    
    /**
     * Retry message
     */
    private function retryMessage($queueName, $messageData)
    {
        $queue = $this->queues[$queueName];
        $delay = $this->calculateRetryDelay($queue['retry_policy'], $messageData['attempts']);
        
        $messageData['delay_until'] = time() + $delay;
        $messageData['last_error'] = $reason ?? 'Unknown error';
        
        $this->addToDelayedQueue($queueName, $messageData);
    }
    
    /**
     * Move to dead letter queue
     */
    private function moveToDeadLetterQueue($queueName, $messageData, $reason)
    {
        $queue = $this->queues[$queueName];
        $dlqKey = $queue['dead_letter_queue'];
        
        $messageData['dead_letter_reason'] = $reason;
        $messageData['dead_letter_at'] = time();
        
        $this->redis->lpush($dlqKey, json_encode($messageData));
        
        // Log dead letter
        $this->logDeadLetter($queueName, $messageData, $reason);
    }
    
    /**
     * Calculate retry delay
     */
    private function calculateRetryDelay($policy, $attempt)
    {
        switch ($policy) {
            case 'exponential_backoff':
                return min(300, pow(2, $attempt - 1) * $this->config['retry_delay']);
            case 'fixed_delay':
                return $this->config['retry_delay'];
            case 'immediate':
                return 0;
            default:
                return $this->config['retry_delay'];
        }
    }
    
    /**
     * Get queue statistics
     */
    public function getQueueStats($queueName)
    {
        if (!isset($this->queues[$queueName])) {
            throw new Exception("Queue {$queueName} not found");
        }
        
        $stats = [
            'name' => $queueName,
            'size' => 0,
            'delayed_size' => 0,
            'processed' => 0,
            'failed' => 0,
            'workers' => count($this->workers[$queueName] ?? [])
        ];
        
        // Count messages in queue
        foreach (['high', 'normal', 'low'] as $priority) {
            $key = $this->getQueueKey($queueName) . ':' . $priority;
            $stats['size'] += $this->redis->llen($key);
        }
        
        // Count delayed messages
        $delayedKey = $this->getDelayedQueueKey($queueName);
        $stats['delayed_size'] = $this->redis->zcard($delayedKey);
        
        return $stats;
    }
    
    /**
     * Get all queue statistics
     */
    public function getAllQueueStats()
    {
        $stats = [];
        
        foreach ($this->queues as $queueName => $queue) {
            $stats[$queueName] = $this->getQueueStats($queueName);
        }
        
        return $stats;
    }
    
    /**
     * Purge queue
     */
    public function purgeQueue($queueName)
    {
        if (!isset($this->queues[$queueName])) {
            throw new Exception("Queue {$queueName} not found");
        }
        
        foreach (['high', 'normal', 'low'] as $priority) {
            $key = $this->getQueueKey($queueName) . ':' . $priority;
            $this->redis->del($key);
        }
        
        $delayedKey = $this->getDelayedQueueKey($queueName);
        $this->redis->del($delayedKey);
        
        return true;
    }
    
    /**
     * Get queue key
     */
    private function getQueueKey($queueName)
    {
        return 'queue:' . $queueName;
    }
    
    /**
     * Get delayed queue key
     */
    private function getDelayedQueueKey($queueName)
    {
        return 'delayed_queue:' . $queueName;
    }
    
    /**
     * Generate message ID
     */
    private function generateMessageId()
    {
        return uniqid('msg_', true);
    }
    
    /**
     * Update queue size
     */
    private function updateQueueSize($queueName)
    {
        $key = 'queue_size:' . $queueName;
        $size = 0;
        
        foreach (['high', 'normal', 'low'] as $priority) {
            $queueKey = $this->getQueueKey($queueName) . ':' . $priority;
            $size += $this->redis->llen($queueKey);
        }
        
        $this->redis->set($key, $size);
    }
    
    /**
     * Log message processed
     */
    private function logMessageProcessed($queueName, $messageId)
    {
        $logData = [
            'queue' => $queueName,
            'message_id' => $messageId,
            'action' => 'processed',
            'timestamp' => time()
        ];
        
        $this->logQueueEvent($logData);
    }
    
    /**
     * Log dead letter
     */
    private function logDeadLetter($queueName, $messageData, $reason)
    {
        $logData = [
            'queue' => $queueName,
            'message_id' => $messageData['id'],
            'action' => 'dead_letter',
            'reason' => $reason,
            'attempts' => $messageData['attempts'],
            'timestamp' => time()
        ];
        
        $this->logQueueEvent($logData);
    }
    
    /**
     * Log queue event
     */
    private function logQueueEvent($logData)
    {
        file_put_contents(
            BASE_PATH . '/logs/queue_events.log',
            json_encode($logData) . PHP_EOL,
            FILE_APPEND
        );
    }
}

/**
 * Queue Worker
 */
class QueueWorker
{
    private $queueName;
    private $callback;
    private $options;
    private $queueManager;
    private $running = false;
    
    public function __construct($queueName, $callback, $options = [])
    {
        $this->queueName = $queueName;
        $this->callback = $callback;
        $this->options = array_merge([
            'max_concurrent' => 1,
            'timeout' => 30,
            'retry_on_failure' => true,
            'sleep_time' => 1
        ], $options);
        
        $this->queueManager = new QueueManager();
    }
    
    /**
     * Start worker
     */
    public function start()
    {
        $this->running = true;
        
        while ($this->running) {
            try {
                $message = $this->queueManager->getNextMessage($this->queueName, $this->options['timeout']);
                
                if ($message) {
                    $this->processMessage($message);
                } else {
                    // No message, sleep for a while
                    sleep($this->options['sleep_time']);
                }
            } catch (Exception $e) {
                $this->handleError($e);
                sleep($this->options['sleep_time']);
            }
        }
    }
    
    /**
     * Stop worker
     */
    public function stop()
    {
        $this->running = false;
    }
    
    /**
     * Process message
     */
    private function processMessage($message)
    {
        try {
            $result = call_user_func($this->callback, $message);
            
            if ($result) {
                $this->queueManager->ack($this->queueName, $message['id']);
            } else {
                $this->queueManager->reject($this->queueName, $message, 'Callback returned false');
            }
        } catch (Exception $e) {
            $this->queueManager->reject($this->queueName, $message, $e->getMessage());
        }
    }
    
    /**
     * Handle error
     */
    private function handleError($error)
    {
        file_put_contents(
            BASE_PATH . '/logs/worker_errors.log',
            json_encode([
                'queue' => $this->queueName,
                'error' => $error->getMessage(),
                'timestamp' => time()
            ]) . PHP_EOL,
            FILE_APPEND
        );
    }
}
