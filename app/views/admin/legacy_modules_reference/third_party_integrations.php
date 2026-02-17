<?php
require_once __DIR__ . '/core/init.php';

// Check if user is logged in and has admin privileges
adminAccessControl(['superadmin', 'admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Token Validation
    if (!validateCsrfToken()) {
        die('Invalid CSRF token. Action blocked.');
    }
    
    // Logic to save integration would go here
    $message = "Integration settings saved successfully!";
}
?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>Third-Party Integrations</title>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'>
</head>
<body>
<div class='container py-4'>
    <h2>Third-Party Integrations</h2>
    
    <?php if (isset($message)): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>

    <ul>
        <li>CRM/ERP (Zoho, Salesforce) - API sync ready</li>
        <li>WhatsApp/SMS Gateway - Configure provider and keys</li>
        <li>Cloud Backup (Google Drive/Dropbox) - Upload/download backup files</li>
    </ul>

    <form method='post'>
        <?php echo getCsrfField(); ?>
        <div class='mb-3'>
            <label>Integration Type</label>
            <select name="integration_type" class='form-control'>
                <option>CRM/ERP</option>
                <option>WhatsApp/SMS</option>
                <option>Cloud Backup</option>
            </select>
        </div>
        <div class='mb-3'>
            <label>API Key/Token</label>
            <input class='form-control' type='text' name='api_token' required>
        </div>
        <button class='btn btn-success'>Save Integration</button>
    </form>
</div>
</body>
</html>
