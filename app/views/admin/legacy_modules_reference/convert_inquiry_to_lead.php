<?php
// convert_inquiry_to_lead.php: Convert a contact inquiry to a CRM lead
require_once __DIR__ . '/core/init.php';

if (!isAuthenticated() || getAuthRole() !== 'admin') {
    header('Location: login.php');
    exit();
}

$db = \App\Core\App::database();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inquiry_id']) && is_numeric($_POST['inquiry_id'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $inquiry_id = intval($_POST['inquiry_id']);
        // Fetch inquiry
        $inquiry = $db->fetchOne('SELECT * FROM contact WHERE id = :id', ['id' => $inquiry_id]);

        if ($inquiry) {
            // Insert as lead
            $status = 'New';
            $notes = 'Imported from contact inquiry.';
            $sql = 'INSERT INTO leads (name, email, phone, status, notes) VALUES (?, ?, ?, ?, ?)';
            $params = [$inquiry['name'], $inquiry['email'], $inquiry['phone'], $status, $notes];
            
            if ($db->execute($sql, $params)) {
                // --- Begin integration triggers ---
                if (file_exists(__DIR__ . '/includes/integration_helpers.php')) {
                    require_once __DIR__ . '/includes/integration_helpers.php';
                    require_once __DIR__ . '/../includes/notification_manager.php';
                    require_once __DIR__ . '/../includes/email_service.php';
                    
                    $notificationManager = new NotificationManager($db->getConnection(), new EmailService());
                    
                    $lead_data = [
                        'name' => $inquiry['name'],
                        'email' => $inquiry['email'],
                        'phone' => $inquiry['phone'],
                        'status' => $status,
                        'notes' => $notes
                    ];
                    
                    // 1. Send Welcome Notification to Customer
                    if (!empty($inquiry['email']) || !empty($inquiry['phone'])) {
                        $notificationManager->send([
                            'email' => $inquiry['email'],
                            'phone' => $inquiry['phone'],
                            'template' => 'LEAD_WELCOME_CUSTOMER',
                            'data' => $lead_data,
                            'channels' => ['email', 'sms']
                        ]);
                    }
                    
                    // 2. Send Alert to Admin (using integration_helpers fallback or specific admin email if known)
                    // For now, we'll use the existing integration_helpers for WhatsApp as it's specialized
                    if (!empty($inquiry['phone']) && function_exists('send_whatsapp')) {
                        send_whatsapp($inquiry['phone'], 'A new lead has been created for you: ' . $inquiry['name']);
                    }
                    
                    // 3. Google Sheets export
                    if (function_exists('export_to_google_sheets')) {
                        $sheet_row = [$inquiry['name'], $inquiry['email'], $inquiry['phone'], $status, date('Y-m-d H:i:s')];
                        $sheet_result = export_to_google_sheets($sheet_row);
                    }
                    
                    // 4. CRM sync
                    if (function_exists('sync_with_crm')) sync_with_crm($lead_data);
                }
                // --- End integration triggers ---

                // Log activity with integration status
                require_once __DIR__ . '/../includes/log_admin_activity.php';
                $log_msg = 'Converted inquiry ID: ' . $inquiry_id . ' to lead.';
                if (isset($sheet_result)) {
                    $log_msg .= $sheet_result ? ' (Google Sheets synced)' : ' (Google Sheets sync failed)';
                }
                log_admin_activity('convert_inquiry', $log_msg);
                
                header('Location: leads.php?msg=' . urlencode('Lead created from inquiry.'));
                exit();
            } else {
                $error = 'Failed to convert inquiry.';
            }
        } else {
            $error = 'Inquiry not found.';
        }
    }
} else {
    $error = 'Invalid request method or missing inquiry ID.';
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Convert Inquiry to Lead</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="alert alert-danger">
            <?= isset($error) ? $error : 'Unknown error.' ?>
        </div>
        <a href="contactview.php" class="btn btn-secondary">Back to Inquiries</a>
    </div>
</body>
</html>

