#!/usr/bin/env php
<?php
/**
 * Follow-up Reminder Cron — APS Dream Home
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

echo "╔═══════════════════════════════════════════════════════════╗" . PHP_EOL;
echo "║  Follow-up Reminder Cron                                 ║" . PHP_EOL;
echo "║  " . date('Y-m-d H:i:s') . str_repeat(' ', 38) . "║" . PHP_EOL;
if ($dryRun) {
    echo "║  ⚠  DRY RUN                                             ║" . PHP_EOL;
}
echo "╚═══════════════════════════════════════════════════════════╝" . PHP_EOL . PHP_EOL;

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'], $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "[✓] Database connected" . PHP_EOL . PHP_EOL;

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
                INSERT INTO lead_activities (lead_id, activity_type, description, created_by, created_at)
                VALUES (?, 'reminder', ?, ?, NOW())
            ");
            $reminderDesc = "Follow-up reminder: {$taskTitle} (due: {$dueDate})";
            $activityStmt->execute([
                $task['lead_id'],
                $reminderDesc,
                $task['assigned_to'] ?: $task['created_by']
            ]);

            // Mark reminder as sent
            $updateStmt = $pdo->prepare("
                UPDATE crm_tasks SET reminder_sent = 1, updated_at = NOW() WHERE id = ?
            ");
            $updateStmt->execute([$task['id']]);

            echo "    ✅ Reminder logged + marked sent" . PHP_EOL;
            $sent++;
        } catch (\Throwable $e) {
            echo "    ❌ Error: " . $e->getMessage() . PHP_EOL;
            $errors++;
        }
        echo PHP_EOL;
    }

    echo "╔═══════════════════════════════════════════════════════════╗" . PHP_EOL;
    echo "║  SUMMARY                                                ║" . PHP_EOL;
    echo "╠═══════════════════════════════════════════════════════════╣" . PHP_EOL;
    echo "║  Tasks processed: {$sent}" . PHP_EOL;
    echo "║  Errors:          {$errors}" . PHP_EOL;
    echo "╚═══════════════════════════════════════════════════════════╝" . PHP_EOL;

} catch (\Throwable $e) {
    echo PHP_EOL . "❌ FATAL: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
