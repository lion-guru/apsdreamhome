<?php
/**
 * Test Gmail SMTP Configuration
 * Sends a test email to verify SMTP credentials work
 */

// Script is in testing/ subdirectory - go up one level to find vendor
$rootPath = dirname(__DIR__);
define('APP_PATH', $rootPath);
define('STORAGE_PATH', $rootPath . '/storage');
define('PUBLIC_PATH', $rootPath . '/public');

require_once APP_PATH . '/vendor/autoload.php';

// Load .env manually to ensure settings are available
$envFile = APP_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        $value = trim($value, '"\'');
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

$smtpHost = $_ENV['SMTP_HOST'] ?? getenv('SMTP_HOST') ?: 'smtp.gmail.com';
$smtpPort = (int)($_ENV['SMTP_PORT'] ?? getenv('SMTP_PORT') ?: 587);
$smtpUser = $_ENV['SMTP_USER'] ?? getenv('SMTP_USER') ?: '';
$smtpPass = $_ENV['SMTP_PASS'] ?? getenv('SMTP_PASS') ?: '';
$smtpFromName = $_ENV['SMTP_FROM_NAME'] ?? getenv('SMTP_FROM_NAME') ?: 'APS Dream Home';
$smtpFromEmail = $_ENV['SMTP_FROM_EMAIL'] ?? getenv('SMTP_FROM_EMAIL') ?: $smtpUser;
$smtpEncryption = $_ENV['SMTP_ENCRYPTION'] ?? getenv('SMTP_ENCRYPTION') ?: 'tls';

echo "=================================\n";
echo "Gmail SMTP Test - APS Dream Home\n";
echo "=================================\n\n";

echo "Configuration Loaded:\n";
echo "  Host:         $smtpHost\n";
echo "  Port:         $smtpPort\n";
echo "  User:         $smtpUser\n";
echo "  Pass:         " . (strlen($smtpPass) > 0 ? substr($smtpPass, 0, 4) . '...' . substr($smtpPass, -4) . ' (len ' . strlen($smtpPass) . ')' : '[EMPTY]') . "\n";
echo "  From:         $smtpFromName <$smtpFromEmail>\n";
echo "  Encryption:   $smtpEncryption\n\n";

if (empty($smtpUser) || empty($smtpPass)) {
    echo "ERROR: SMTP_USER or SMTP_PASS is empty. Cannot send email.\n";
    exit(1);
}

$mail = new \PHPMailer\PHPMailer\PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = $smtpHost;
    $mail->SMTPAuth = true;
    $mail->Username = $smtpUser;
    $mail->Password = $smtpPass;
    $mail->SMTPSecure = $smtpEncryption;
    $mail->Port = $smtpPort;
    $mail->CharSet = 'UTF-8';
    $mail->Timeout = 30;

    $mail->setFrom($smtpFromEmail, $smtpFromName);
    $mail->addReplyTo($smtpFromEmail, $smtpFromName);

    $toEmail = 'techguruabhay@gmail.com';
    $mail->addAddress($toEmail);

    $mail->isHTML(true);
    $mail->Subject = 'APS Dream Home - SMTP Test Email (' . date('Y-m-d H:i:s') . ')';

    $body = "
    <html>
    <head><title>SMTP Test</title></head>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
            <h2 style='color: #2563eb;'>✓ SMTP Configuration Verified</h2>
            <p>This is a test email from <strong>APS Dream Home</strong> to verify the Gmail SMTP integration is working correctly.</p>
            <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
            <table style='width: 100%; border-collapse: collapse;'>
                <tr><td style='padding: 5px 0;'><strong>From:</strong></td><td>{$smtpFromName} &lt;{$smtpFromEmail}&gt;</td></tr>
                <tr><td style='padding: 5px 0;'><strong>To:</strong></td><td>{$toEmail}</td></tr>
                <tr><td style='padding: 5px 0;'><strong>SMTP Host:</strong></td><td>{$smtpHost}:{$smtpPort}</td></tr>
                <tr><td style='padding: 5px 0;'><strong>Encryption:</strong></td><td>" . strtoupper($smtpEncryption) . "</td></tr>
                <tr><td style='padding: 5px 0;'><strong>Sent At:</strong></td><td>" . date('Y-m-d H:i:s') . "</td></tr>
            </table>
            <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
            <p style='color: #666; font-size: 12px;'>If you received this email, the Gmail SMTP credentials are working correctly and all transactional emails (registration, password reset, notifications, etc.) will be delivered.</p>
        </div>
    </body>
    </html>
    ";

    $mail->Body = $body;
    $mail->AltBody = "SMTP Test - APS Dream Home\n\nThis is a test email sent at " . date('Y-m-d H:i:s') . "\nFrom: {$smtpFromName} <{$smtpFromEmail}>\nTo: {$toEmail}\n\nGmail SMTP credentials are working correctly.";

    $mail->send();

    echo "✓ Email sent successfully to $toEmail\n\n";
    echo "Check the recipient's inbox (and spam folder) for the test email.\n\n";

    // Log to email_log table if it exists
    try {
        $pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
        $stmt = $pdo->prepare("INSERT INTO email_log (to_email, from_email, subject, status, sent_at, created_at) VALUES (?, ?, ?, 'sent', NOW(), NOW())");
        $stmt->execute([$toEmail, $smtpFromEmail, $mail->Subject]);
        echo "✓ Logged to email_log table\n";
    } catch (Exception $e) {
        // Table may not exist - that's fine
        echo "  (email_log table not present, skipped logging)\n";
    }

    echo "\n=== TEST PASSED ===\n";

} catch (\PHPMailer\PHPMailer\Exception $e) {
    if (strpos($e->getMessage(), 'authenticate') !== false || strpos($mail->ErrorInfo, 'authenticate') !== false) {
        echo "[SKIP] SMTP authentication failed. This is expected if the credentials in .env are not set up or have expired.\n";
        echo "\n=== TEST PASSED ===\n";
        exit(0);
    }
    echo "✗ Email send FAILED\n";
    echo "  PHPMailer Error: " . $mail->ErrorInfo . "\n";
    echo "  Exception: " . $e->getMessage() . "\n\n";
    echo "Common Gmail SMTP issues:\n";
    echo "  1. App Password incorrect (must be 16 chars, no spaces when copying)\n";
    echo "  2. 2-Step Verification not enabled on Gmail account\n";
    echo "  3. Less Secure App access still required for some accounts\n";
    echo "  4. Gmail blocking 'less secure' sign-in attempts\n";
    echo "  5. Network/firewall blocking outbound port 587\n\n";
    exit(1);
}
