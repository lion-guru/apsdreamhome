<?php
// Admin Two-Factor Authentication Setup - Scaffold
include(__DIR__ . '/../includes/templates/dynamic_header.php'); ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<div class="container mt-4">
    <h2>Two-Factor Authentication (2FA) Setup</h2>
    <div class="alert alert-info">Protect your account with an extra layer of security.</div>
    <form method="post" action="">
        <div class="mb-3">
            <label for="enable2fa" class="form-label">Enable 2FA</label>
            <select class="form-select" id="enable2fa" name="enable2fa">
                <option value="off">Off</option>
                <option value="on">On (Email OTP)</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>
    <div class="mt-4">
        <h5>How 2FA Works:</h5>
        <ul>
            <li>When enabled, you will receive a one-time password (OTP) on your registered email after logging in.</li>
            <li>You must enter the OTP to access the admin dashboard.</li>
            <li>2FA helps prevent unauthorized access even if your password is compromised.</li>
        </ul>
    </div>
</div>
<?php include(__DIR__ . '/../includes/templates/new_footer.php'); ?>
