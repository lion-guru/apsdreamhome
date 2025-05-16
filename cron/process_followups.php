<?php
/**
 * Cron job to process automated follow-ups
 * Recommended to run every hour
 */

require_once __DIR__ . '/../api/automated_followup.php';

// Set unlimited execution time for long-running processes
set_time_limit(0);

// Initialize follow-up system
try {
    $followup = new AutomatedFollowup();
    $followup->processFollowups();
    
    // Log successful execution
    file_put_contents(
        __DIR__ . '/followup.log',
        date('Y-m-d H:i:s') . " - Follow-ups processed successfully\n",
        FILE_APPEND
    );
    
    exit(0);
} catch (Exception $e) {
    // Log error
    file_put_contents(
        __DIR__ . '/followup.log',
        date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n",
        FILE_APPEND
    );
    
    exit(1);
}
?>
