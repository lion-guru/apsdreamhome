<?php
/**
 * Overdue Demand Reminders Cron
 * Runs daily at 07:00
 */

require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Database\Database;
use App\Services\PropertyTaxService;
use App\Services\NotificationService;

echo "=== Overdue Demand Reminders Cron ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";

try {
    $db = Database::getInstance()->getConnection();
    $taxService = new PropertyTaxService();
    $notifications = new NotificationService();
    
    // Find overdue maintenance dues
    $stmt = $db->prepare("
        SELECT md.*, u.id as user_id, u.email, u.name, p.plot_number, c.name as colony_name
        FROM maintenance_dues md
        JOIN users u ON u.id = md.customer_id
        JOIN plots p ON p.id = md.plot_id
        JOIN colonies c ON c.id = md.colony_id
        WHERE md.status IN ('pending', 'partially_paid')
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
        $balance = $due['amount_due'] - $due['paid_amount'];
        
        $notifications->notify([
            'user_id' => $due['user_id'],
            'title' => 'Maintenance Due Overdue',
            'message' => "Your maintenance due for plot {$due['plot_number']} in {$due['colony_name']} is overdue. Outstanding amount: ₹" . number_format($balance, 2),
            'type' => 'maintenance_overdue',
            'data' => [
                'due_id' => $due['id'],
                'plot_id' => $due['plot_id'],
                'amount' => $balance,
                'due_date' => $due['due_date'],
            ],
            'channels' => ['in_app', 'email', 'sms'],
            'priority' => 'high',
        ]);
        
        // Update reminder_sent_at
        $stmt = $db->prepare("UPDATE maintenance_dues SET reminder_sent_at = NOW() WHERE id = ?");
        $stmt->execute([$due['id']]);
        
        $notified++;
    }
    
    echo "Notifications sent: $notified\n";
    echo "✅ Overdue demand reminders completed\n";
    
} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

echo "=== Overdue Demand Reminders Completed ===\n";