<?php
// Automated Email Alert Script
// Integrated with NotificationManager
require_once dirname(__DIR__, 2) . '/includes/notification_manager.php';
require_once dirname(__DIR__, 2) . '/includes/email_service.php';

function send_admin_alert($subject, $message, $to = null) {
    try {
        $db = \App\Core\App::database();
        $emailService = new EmailService();
        $notificationManager = new NotificationManager($db, $emailService);

        // If specific recipient provided, use it
        if ($to) {
            return $notificationManager->send([
                'email' => $to,
                'template' => 'SYSTEM_ALERT',
                'data' => [
                    'alert_title' => $subject,
                    'alert_message' => $message
                ],
                'channels' => ['email']
            ]);
        }

        // Otherwise, notify all admin users
        $adminUsers = $db->fetchAll("SELECT id, email FROM admin WHERE role = 'admin'");
        $results = [];
        foreach ($adminUsers as $admin) {
            $results[] = $notificationManager->send([
                'user_id' => $admin['id'],
                'template' => 'SYSTEM_ALERT',
                'data' => [
                    'alert_title' => $subject,
                    'alert_message' => $message
                ],
                'channels' => ['db', 'email']
            ]);
        }
        return !empty($results);
    } catch (Exception $e) {
        error_log('Admin Alert Error: ' . $e->getMessage());
        return false;
    }
}
// Example usage:
// send_admin_alert('New Booking Received', "A new booking has been made by Ravi.");

