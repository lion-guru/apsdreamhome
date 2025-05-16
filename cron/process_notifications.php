<?php
/**
 * Automated Notification Processing Cron Job
 * Recommended to run every 5 minutes
 */

require_once __DIR__ . '/../includes/config/config.php';
require_once __DIR__ . '/../includes/db_settings.php';
require_once __DIR__ . '/../includes/classes/AutomatedNotifier.php';
require_once __DIR__ . '/../includes/classes/NotificationTemplate.php';

// Set unlimited execution time for long-running processes
set_time_limit(0);

try {
    // Get database connection
    $conn = get_db_connection();
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Initialize automated notifier
    $notifier = new AutomatedNotifier($conn);

    // Process notifications
    $notifier->processAutomatedNotifications();

    // Log successful execution
    file_put_contents(
        __DIR__ . '/notification.log',
        date('Y-m-d H:i:s') . " - Notification processing completed successfully\n",
        FILE_APPEND
    );

    exit(0);
} catch (Exception $e) {
    // Log error
    file_put_contents(
        __DIR__ . '/notification.log',
        date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n",
        FILE_APPEND
    );

    exit(1);
}
?>
