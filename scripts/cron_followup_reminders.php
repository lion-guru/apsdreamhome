#!/usr/bin/env php
<?php
/**
 * Follow-up Reminder Cron â€” APS Dream Home
 * ==========================================
 * Sends reminders for pending CRM follow-up tasks.
 *
 * Logic:
 *   1. Query crm_tasks WHERE status='pending' AND reminder_at <= NOW() AND reminder_sent=0
 *   2. For each task: log activity to lead_activities
 *   3. Mark reminder_sent=1
 *
 * Schedule: Run every 4 hours via run_all_crons.php or standalone
 *
 * Usage:
 *   php scripts/cron_followup_reminders.php
 *   php scripts/cron_followup_reminders.php --dry-run
 */

$root   = dirname(__DIR__);
$config = require $root . '/config/database.php';

$dryRun = in_array('--dry-run', $argv ?? []);

echo "â•”â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•—" . PHP_EOL;
echo "â•‘  Follow-up Reminder Cron                                 â•‘" . PHP_EOL;
echo "â•‘  " . date('Y-m-d H:i:s') . str_repeat(' ', 38) . "â•‘" . PHP_EOL;
if ($dryRun) {
    echo "â•‘  âš   DRY RUN                                             â•‘" . PHP_EOL;
}
echo "â•šâ•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�" . PHP_EOL . PHP_EOL;

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'], $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "[âœ“] Database connected" . PHP_EOL . PHP_EOL;

    // Set tenant context for TenantContext consumers
    require_once __DIR__ . '/../app/Core/autoload.php';
    $cronTenantId = 1;
    if (class_exists('\App\Core\Middleware\TenantContext')) {
        \App\Core\Middleware\TenantContext::setById($cronTenantId, $pdo);
    }
    $cronTenantSql = $cronTenantId > 1 ? " AND tenant_id = " . (int)$cronTenantId : "";
    $cronTenantCol = $cronTenantId > 1 ? ", tenant_id" : "";
    $cronTenantVal = $cronTenantId > 1 ? ", " . (int)$cronTenantId : "";

    // Find tasks needing reminders
    $stmt = $pdo->prepare("
        SELECT t.id, t.lead_id, t.title, t.task_type, t.due_date, t.due_time,
               t.assigned_to, t.created_by,
               l.name as lead_name, l.phone as lead_phone, l.email as lead_email,
               u.name as assignee_name
        FROM crm_tasks t
        LEFT JOIN leads l ON t.lead_id = l.id
        LEFT JOIN users u ON t.assigned_to = u.id
        WHERE t.status = 'pending'
          AND t.reminder_at IS NOT NULL
          AND t.reminder_at <= NOW()
          AND t.reminder_sent = 0
          {$cronTenantSql}
        ORDER BY t.reminder_at ASC
        LIMIT 50
    ");
    $stmt->execute();
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $count = count($tasks);
    echo "Found {$count} tasks needing reminders" . PHP_EOL . PHP_EOL;

    if ($count === 0) {
        echo "No reminders to send. Done." . PHP_EOL;
        exit(0);
    }

    $sent = 0;
    $errors = 0;

    foreach ($tasks as $task) {
        $taskTitle = $task['title'] ?? 'Follow-up';
        $leadName  = $task['lead_name'] ?? 'Unknown';
        $dueDate   = $task['due_date'] ?? 'N/A';
        $assignee  = $task['assignee_name'] ?? 'Unassigned';

        echo "  Task #{$task['id']}: {$taskTitle}" . PHP_EOL;
        echo "    Lead: {$leadName} | Due: {$dueDate} | Assigned: {$assignee}" . PHP_EOL;

        if ($dryRun) {
            echo "    [DRY RUN] Would send reminder" . PHP_EOL;
            $sent++;
            echo PHP_EOL;
            continue;
        }

        try {
            // Log activity
            $activityStmt = $pdo->prepare("
                INSERT INTO lead_activities (lead_id, activity_type, description, created_by, created_at{$cronTenantCol})
                VALUES (?, 'reminder', ?, ?, NOW(){$cronTenantVal})
            ");
            $reminderDesc = "Follow-up reminder: {$taskTitle} (due: {$dueDate})";
            $activityStmt->execute([
                $task['lead_id'],
                $reminderDesc,
                $task['assigned_to'] ?: $task['created_by']
            ]);

            // Mark reminder as sent
            $updateStmt = $pdo->prepare("
                UPDATE crm_tasks SET reminder_sent = 1, updated_at = NOW() WHERE id = ?{$cronTenantSql}
            ");
            $updateStmt->execute([$task['id']]);

            echo "    âœ… Reminder logged + marked sent" . PHP_EOL;
            $sent++;
        } catch (\Throwable $e) {
            echo "    â�Œ Error: " . $e->getMessage() . PHP_EOL;
            $errors++;
        }
        echo PHP_EOL;
    }

    echo "â•”â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•—" . PHP_EOL;
    echo "â•‘  SUMMARY                                                â•‘" . PHP_EOL;
    echo "â• â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•£" . PHP_EOL;
    echo "â•‘  Tasks processed: {$sent}" . PHP_EOL;
    echo "â•‘  Errors:          {$errors}" . PHP_EOL;
    echo "â•šâ•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�" . PHP_EOL;

} catch (\Throwable $e) {
    echo PHP_EOL . "â�Œ FATAL: " . $e->getMessage() . PHP_EOL;
    exit(1);
}?>