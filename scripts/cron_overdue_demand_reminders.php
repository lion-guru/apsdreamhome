<?php
/**
 * Overdue Demand Reminders Cron
 * Runs daily at 07:00
 *
 * Finds any `maintenance_dues` line that is overdue and has never been
 * reminded, then sends an in-app reminder.  `reminder_sent_at` is stamped
 * so a line never reminds more than once.
 *
 * Safer by construction than the previous version:
 *  - in_app notification only (email/SMS delivery is an owner decision)
 *  - does NOT touch `paid_amount` / `status` / money fields of any kind
 *  - one reminder per dues line, ever
 */

require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Database\Database;
use App\Services\NotificationService;

echo "=== Overdue Demand Reminders Cron ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";

try {
    $db            = Database::getInstance()->getConnection();
    $notifications = new NotificationService($db);

    $stmt = $db->prepare("
        SELECT md.*, u.id AS user_id, u.email, u.name, p.plot_number, c.name AS colony_name
        FROM maintenance_dues md
        JOIN users u ON u.id = md.customer_id
        JOIN plots p ON p.id = md.plot_id
        JOIN colonies c ON c.id = md.colony_id
        WHERE md.status IN ('pending', 'partially_paid')
          AND md.due_date IS NOT NULL
          AND md.due_date < CURDATE()
          AND md.reminder_sent_at IS NULL
    ");
    $stmt->execute();
    $overdue = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($overdue)) {
        echo "No overdue maintenance dues.\n";
        exit(0);
    }

    echo "Found " . count($overdue) . " overdue maintenance dues\n";

    $notified = 0;
    foreach ($overdue as $due) {
        $balance = (float)$due['amount_due'] - (float)$due['paid_amount'];
        if ($balance <= 0) {
            continue; // nothing outstanding
        }

        $notifications->notify([
            'user_id' => $due['user_id'],
            'title'   => 'Maintenance Due Overdue: Rs. ' . number_format($balance, 2),
            'message' => "Your maintenance due for plot {$due['plot_number']} in {$due['colony_name']} is overdue. Outstanding amount: Rs. " . number_format($balance, 2) . " (due {$due['due_date']}). Please clear it from your customer portal.",
            'type'    => 'maintenance_overdue',
            'data'    => [
                'due_id'  => (int)$due['id'],
                'plot_id' => (int)$due['plot_id'],
                'amount'  => $balance,
                'due_date' => $due['due_date'],
            ],
            'related_id'  => (int)$due['id'],
            'related_type' => 'maintenance_due',
            'channels' => ['in_app'],
            'priority' => 'high',
        ]);

        $stmt = $db->prepare("UPDATE maintenance_dues SET reminder_sent_at = NOW() WHERE id = ?");
        $stmt->execute([(int)$due['id']]);

        $notified++;
    }

    echo "Notifications sent: $notified\n";
    echo "=== Overdue demand reminders completed ===\n";

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

echo "=== Overdue Demand Reminders Completed ===\n";
