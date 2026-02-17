<?php
// auto_lead_conversion_cron.php: Automatically convert qualified leads to 'Converted' after 10 days if not already converted
require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/notification_manager.php';
require_once __DIR__ . '/../includes/email_service.php';

$db = \App\Core\App::database();
$nm = new NotificationManager($db->getConnection(), new EmailService());

// Find qualified leads older than 10 days, not yet converted
$leads = $db->fetchAll("SELECT * FROM leads WHERE status = 'Qualified' AND created_at < (NOW() - INTERVAL 10 DAY)");

foreach ($leads as $lead) {
    $sql = "UPDATE leads SET status = 'Converted', notes = CONCAT(IFNULL(notes, ''), '\n[Auto] Converted after 10 days') WHERE id = ?";
    if ($db->execute($sql, [$lead['id']])) {
        // Notify admin about auto-conversion
        $admin_id = $lead['assigned_to'] ?: 1;
        $nm->send([
            'user_id' => $admin_id,
            'template' => 'AUTO_CONVERSION',
            'data' => [
                'name' => $lead['name']
            ],
            'channels' => ['db', 'email']
        ]);
    }
}
?>

