<?php

/**
 * Messenger Queue Worker
 * Run: php scripts/messenger_worker.php [transport_name]
 */

require_once __DIR__ . '/../config/bootstrap.php';

use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Bridge\Redis\Transport\RedisTransport;
use Symfony\Component\Messenger\Worker;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use App\Message\SendNotificationMessage;
use App\Message\SendEmailMessage;
use App\Message\SendSmsMessage;
use App\Message\GeneratePdfMessage;
use App\Handler\SendNotificationHandler;
use App\Handler\SendEmailHandler;
use App\Handler\SendSmsHandler;
use App\Handler\GeneratePdfHandler;

$transportName = $argv[1] ?? 'async';

echo "=== Starting Messenger Worker for transport: {$transportName} ===\n";

$config = require __DIR__ . '/../config/messenger/messenger.php';
$messengerConfig = $config['messenger'];

$transportConfig = $messengerConfig['transports'][$transportName] ?? null;
if (!$transportConfig) {
    die("Transport '{$transportName}' not configured\n");
}

$dsn = $transportConfig['dsn'];
$retryStrategy = $transportConfig['retry_strategy'] ?? [];

// Create transport
$transport = new \Symfony\Component\Messenger\Bridge\Redis\Transport\RedisTransport($dsn, [
    'stream' => $transportConfig['options']['stream'] ?? [],
]);

$transport->setup();

// Handlers
$handlers = [
    SendNotificationMessage::class => [new SendNotificationHandler()],
    SendEmailMessage::class => [new SendEmailHandler()],
    SendSmsMessage::class => [new SendSmsHandler()],
    GeneratePdfMessage::class => [new GeneratePdfHandler()],
];

$handlerLocator = new HandlersLocator($handlers);
$bus = new MessageBus([
    new HandleMessageMiddleware($handlerLocator),
]);

$worker = new Worker(
    [$transport => $bus],
    $bus,
    new PhpSerializer(),
    [
        'max_retries' => $retryStrategy['max_retries'] ?? 3,
        'delay' => $retryStrategy['delay'] ?? 10,
        'multiplier' => $retryStrategy['multiplier'] ?? 2,
        'max_delay' => $retryStrategy['max_delay'] ?? 300,
    ]
);

echo "Worker started. Listening on {$transportName}...\n";
echo "Press Ctrl+C to stop\n\n";

// Handle signals
$running = true;
pcntl_signal(SIGTERM, function () use (&$running) {
    echo "\nShutting down...\n";
    $running = false;
});
pcntl_signal(SIGINT, function () use (&$running) {
    echo "\nShutting down...\n";
    $running = false;
});

while ($running) {
    try {
        $worker->run();
    } catch (\Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
        sleep(5); // Wait before retry
    }
}

echo "Worker stopped.\n";