<?php
/**
 * Admin Mail Handler - Refactored to use NotificationManager
 */
require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/email_service.php';
require_once __DIR__ . '/../includes/notification_manager.php';

// Assuming variables $name, $phone, $email, $msg, $file_tmp, $file_name are provided by the caller
// This file seems to be included from another context (e.g., job application handler)

try {
    $emailService = new EmailService();
    $notificationManager = new NotificationManager(null, $emailService);

    // Prepare attachments if present
    $attachments = [];
    if (!empty($file_tmp) && !empty($file_name)) {
        $attachments[] = [
            'path' => $file_tmp,
            'name' => $file_name
        ];
    }

    $result = $notificationManager->send([
        'email' => 'techguruabhay@gmail.com',
        'template' => 'JOB_APPLICATION',
        'data' => [
            'name' => $name ?? 'Unknown',
            'phone' => $phone ?? 'N/A',
            'email' => $email ?? 'N/A',
            'message' => $msg ?? 'No comments provided'
        ],
        'attachments' => $attachments,
        'channels' => ['email']
    ]);

    if ($result) {
        echo "Form submitted successfully! We will review your application and get back to you soon.";
    } else {
        echo "Error sending email. Please try again later.";
    }

} catch (Exception $e) {
    error_log("Mail Handler Error: " . $e->getMessage());
    echo "Error: " . $e->getMessage();
}
