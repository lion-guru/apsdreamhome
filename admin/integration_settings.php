<?php
// integration_settings.php: GUI for super admin to manage external integrations (WhatsApp, Google Sheets, Email, SMS, CRM)
session_start();
require_once __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['role'] !== 'super_admin') {
    header('Location: login.php');
    exit();
}
global $con;
$conn = $con;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $whatsapp_api = $_POST['whatsapp_api'] ?? '';
    $google_sheets_key = $_POST['google_sheets_key'] ?? '';
    $google_drive_client_id = $_POST['google_drive_client_id'] ?? '';
    $google_drive_client_secret = $_POST['google_drive_client_secret'] ?? '';
    $google_drive_refresh_token = $_POST['google_drive_refresh_token'] ?? '';
    $email_host = $_POST['email_host'] ?? '';
    $email_user = $_POST['email_user'] ?? '';
    $email_pass = $_POST['email_pass'] ?? '';
    $sms_api = $_POST['sms_api'] ?? '';
    $crm_api = $_POST['crm_api'] ?? '';
    $stmt = $conn->prepare("REPLACE INTO integration_settings (id, whatsapp_api, google_sheets_key, google_drive_client_id, google_drive_client_secret, google_drive_refresh_token, email_host, email_user, email_pass, sms_api, crm_api) VALUES (1,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param('sssssssssss', $whatsapp_api, $google_sheets_key, $google_drive_client_id, $google_drive_client_secret, $google_drive_refresh_token, $email_host, $email_user, $email_pass, $sms_api, $crm_api);
    $stmt->execute();
    $msg = 'Integration settings updated!';
}
// Fetch current settings
$settings = $conn->query("SELECT * FROM integration_settings WHERE id=1")->fetch_assoc() ?: [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Integration Settings</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background: #f8f9fa; }
        .container { max-width: 700px; margin-top: 40px; }
    </style>
</head>
<body>
<div class="container">
    <h2 class="mb-4">External Integration Settings</h2>
    <?php if (!empty($msg)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label class="form-label">WhatsApp API Key</label>
            <input type="text" name="whatsapp_api" class="form-control" value="<?= htmlspecialchars($settings['whatsapp_api'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Google Sheets API Key</label>
            <input type="text" name="google_sheets_key" class="form-control" value="<?= htmlspecialchars($settings['google_sheets_key'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Google Drive Client ID</label>
            <input type="text" name="google_drive_client_id" class="form-control" value="<?= htmlspecialchars($settings['google_drive_client_id'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Google Drive Client Secret</label>
            <input type="text" name="google_drive_client_secret" class="form-control" value="<?= htmlspecialchars($settings['google_drive_client_secret'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Google Drive Refresh Token</label>
            <input type="text" name="google_drive_refresh_token" class="form-control" value="<?= htmlspecialchars($settings['google_drive_refresh_token'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Email Host</label>
            <input type="text" name="email_host" class="form-control" value="<?= htmlspecialchars($settings['email_host'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Email Username</label>
            <input type="text" name="email_user" class="form-control" value="<?= htmlspecialchars($settings['email_user'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Email Password</label>
            <input type="password" name="email_pass" class="form-control" value="<?= htmlspecialchars($settings['email_pass'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">SMS API Key</label>
            <input type="text" name="sms_api" class="form-control" value="<?= htmlspecialchars($settings['sms_api'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">CRM API Key</label>
            <input type="text" name="crm_api" class="form-control" value="<?= htmlspecialchars($settings['crm_api'] ?? '') ?>">
        </div>
        <button class="btn btn-primary" type="submit">Save Settings</button>
    </form>
    <p class="mt-4 text-muted">Only super admins can update integration settings. These credentials will be used by automation scripts for external connections.</p>
</div>
</body>
</html>
