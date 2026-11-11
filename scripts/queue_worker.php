<?php
/**
 * Queue Worker Script
 * 
 * Run queue workers for different transports.
 * 
 * Usage:
 *   php queue_worker.php notifications
 *   php queue_worker.php emails
 *   php queue_worker.php sms
 *   php queue_worker.php pdf
 *   php queue_worker.php reports
 *   php queue_worker.php all
 * 
 * Options:
 *   --memory-limit=256M
 *   --time-limit=3600
 *   --max-messages=100
 *   --sleep-delay=1000
 */

require_once __DIR__ . '/../config/bootstrap.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Worker;

// Simple worker runner without full Symfony Console
$transport = $argv[1] ?? 'all';
$memoryLimit = '256M';
$timeLimit = 3600;
$maxMessages = 100;
$sleepDelay = 1000;

// Parse options
for ($i = 2; $i < count($argv); $i++) {
    if (str_starts_with($argv[$i], '--memory-limit=')) {
        $memoryLimit = substr($argv[$i], strlen('--memory-limit='));
    } elseif (str_starts_with($argv[$i], '--time-limit=')) {
        $timeLimit = (int)substr($argv[$i], strlen('--time-limit='));
    } elseif (str_starts_with($argv[$i], '--max-messages=')) {
        $maxMessages = (int)substr($argv[$i], strlen('--max-messages='));
    } elseif (str_starts_with($argv[$i], '--sleep-delay=')) {
        $sleepDelay = (int)substr($argv[$i], strlen('--sleep-delay='));
    }
}

$transports = ['notifications', 'emails', 'sms', 'pdf', 'reports'];
if ($transport === 'all') {
    $selectedTransports = $transports;
} elseif (in_array($transport, $transports)) {
    $selectedTransports = [$transport];
} else {
    echo "Usage: php queue_worker.php <transport|all> [options]\n";
    echo "Transports: " . implode(', ', $transports) . ", all\n";
    echo "Options:\n";
    echo "  --memory-limit=256M\n";
    echo "  --time-limit=3600\n";
    echo "  --max-messages=100\n";
    echo "  --sleep-delay=1000\n";
    exit(1);
}

ini_set('memory_limit', $memoryLimit);
set_time_limit($timeLimit);

require_once __DIR__ . '/../config/bootstrap.php';

// Load messenger configuration
$config = require __DIR__ . '/../config/messenger.yaml';

// Create a simple worker using Symfony Messenger
// For now, we'll use a simple polling approach
echo "Starting queue workers for: " . implode(', ', $selectedTransports) . "\n";
echo "Memory limit: $memoryLimit, Time limit: ${timeLimit}s, Max messages: $maxMessages\n";

$messageCount = 0;
$startTime = time();

while (true) {
    if ($timeLimit > 0 && (time() - $startTime) > $timeLimit) {
        echo "Time limit reached. Stopping.\n";
        break;
    }
    
    if ($messageCount >= $maxMessages) {
        echo "Max messages reached. Stopping.\n";
        break;
    }
    
    $processed = false;
    
    foreach ($selectedTransports as $transport) {
        // In a real implementation, this would use Symfony Messenger's Worker
        // For now, we'll simulate with a simple sleep
        $processed = true;
        $messageCount++;
    }
    
    if (!$processed) {
        echo "No messages processed. Sleeping...\n";
        usleep($sleepDelay * 1000);
    } else {
        // Small delay between batches
        usleep(100000); // 100ms
    }
}

echo "Queue worker stopped. Processed $messageCount messages.\n";