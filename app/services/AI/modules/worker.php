<?php

namespace App\Services\AI\Legacy;
/**
 * AI Background Worker
 * Run this from CLI: php includes/ai/worker.php
 */

require_once __DIR__ . '/../../app/core/App.php';
require_once __DIR__ . '/JobManager.php';

// Prevent multiple instances if possible (optional)
$lockFile = sys_get_temp_dir() . '/ai_worker.lock';
$lock = fopen($lockFile, 'w');
if (!flock($lock, LOCK_EX | LOCK_NB)) {
    die("Worker is already running.\n");
}

echo "AI Worker started at " . date('Y-m-d H:i:s') . "\n";
echo "Press Ctrl+C to stop.\n\n";

$db = \App\Core\App::database();
$jobManager = new JobManager($db);

$idleCount = 0;
$maxIdleBeforeSleep = 5;

while (true) {
    $job = $jobManager->getNextJob();
    
    if ($job) {
        $idleCount = 0;
        echo "[" . date('H:i:s') . "] Processing job ID: {$job['id']} (Type: {$job['task_type']})... ";
        
        $success = $jobManager->processJob($job['id']);
        
        if ($success) {
            echo "SUCCESS\n";
        } else {
            echo "FAILED\n";
        }
    } else {
        $idleCount++;
        if ($idleCount >= $maxIdleBeforeSleep) {
            // No jobs found, sleep for a bit to save CPU
            usleep(2000000); // 2 seconds
        } else {
            usleep(500000); // 0.5 seconds
        }
    }
    
    // Optional: Memory limit check
    if (memory_get_usage() > 128 * 1024 * 1024) { // 128MB
        echo "Memory limit reached. Restarting worker...\n";
        break;
    }
}

flock($lock, LOCK_UN);
fclose($lock);
unlink($lockFile);
?>
