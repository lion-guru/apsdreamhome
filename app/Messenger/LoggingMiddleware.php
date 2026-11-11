<?php

namespace App\Messenger;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class LoggingMiddleware implements MiddlewareInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();
        $class = get_class($message);
        
        $this->logger->debug('Messenger: Processing message', [
            'message_class' => $class,
            'message_id' => $envelope->all(MessageIdStamp::class)?->current()?->getId(),
        ]);

        $start = microtime(true);
        
        try {
            $envelope = $stack->next()->handle($envelope, $stack);
            
            $duration = round((microtime(true) - $start) * 1000, 2);
            $this->logger->info('Messenger: Message processed', [
                'message_class' => $class,
                'duration_ms' => $duration,
            ]);
            
            return $envelope;
        } catch (\Throwable $e) {
            $duration = round((microtime(true) - $start) * 1000, 2);
            $this->logger->error('Messenger: Message failed', [
                'message_class' => $class,
                'duration_ms' => $duration,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}