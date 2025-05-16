<?php
// convert_inquiry_to_lead.php: Convert a contact inquiry to a CRM lead
require_once __DIR__ . '/includes/session_manager.php';
require_once __DIR__ . '/../includes/db_config.php';
initAdminSession();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
$conn = getDbConnection();

if (isset($_GET['inquiry_id']) && is_numeric($_GET['inquiry_id'])) {
    $inquiry_id = intval($_GET['inquiry_id']);
    // Fetch inquiry
    $stmt = $conn->prepare('SELECT * FROM contact WHERE id = ?');
    $stmt->bind_param('i', $inquiry_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $inquiry = $result->fetch_assoc();

    if ($inquiry) {
        // Insert as lead
        $stmt2 = $conn->prepare('INSERT INTO leads (name, email, phone, status, notes) VALUES (?, ?, ?, ?, ?)');
        $status = 'New';
        $notes = 'Imported from contact inquiry.';
        $stmt2->bind_param('sssss', $inquiry['1'], $inquiry['2'], $inquiry['3'], $status, $notes);
        if ($stmt2->execute()) {
            // --- Begin integration triggers ---
            require_once __DIR__ . '/includes/integration_helpers.php';
            $lead_data = [
                'name' => $inquiry['1'],
                'email' => $inquiry['2'],
                'phone' => $inquiry['3'],
                'status' => $status,
                'notes' => $notes
            ];
            // WhatsApp (if phone present)
            if (!empty($inquiry['3'])) send_whatsapp($inquiry['3'], 'A new lead has been created for you: ' . $inquiry['1']);
            // Email (if email present)
            if (!empty($inquiry['2'])) send_email($inquiry['2'], 'You have a new lead!', 'Lead details: ' . print_r($lead_data, true));
            // SMS (if phone present)
            if (!empty($inquiry['3'])) send_sms($inquiry['3'], 'New lead: ' . $inquiry['1']);
            // Google Sheets export (append this lead)
            export_to_google_sheets([$lead_data]);
            // CRM sync
            sync_with_crm($lead_data);
            // --- End integration triggers ---
            // Optionally delete inquiry after conversion
            //$conn->query('DELETE FROM contact WHERE id = ' . $inquiry_id);
            header('Location: leads.php?msg=Lead+created+from+inquiry.');
            exit();
        } else {
            $error = 'Failed to convert inquiry: ' . htmlspecialchars($stmt2->error);
        }
    } else {
        $error = 'Inquiry not found.';
    }
} else {
    $error = 'Invalid request.';
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
