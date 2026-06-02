<?php
/**
 * Test Gmail SMTP with multiple password formats + ports
 */
$rootPath = dirname(__DIR__);
require_once $rootPath . '/vendor/autoload.php';

$user = 'apdreamhomes44@gmail.com';
$passWithSpaces = 'xuvv bzvz updj wcbn';
$passNoSpaces = 'xuvvbzvzupdjwcbn';

// Try with spaces first
$configs = [
    ['pass' => $passWithSpaces, 'host' => 'smtp.gmail.com', 'port' => 587, 'sec' => 'tls'],
    ['pass' => $passNoSpaces,    'host' => 'smtp.gmail.com', 'port' => 587, 'sec' => 'tls'],
    ['pass' => $passWithSpaces, 'host' => 'smtp.gmail.com', 'port' => 465, 'sec' => 'ssl'],
    ['pass' => $passNoSpaces,    'host' => 'smtp.gmail.com', 'port' => 465, 'sec' => 'ssl'],
];

foreach ($configs as $i => $cfg) {
    $desc = $cfg['host'] . ':' . $cfg['port'] . '/' . strtoupper($cfg['sec']) . ' pass=' . substr($cfg['pass'], 0, 4) . '...' . substr($cfg['pass'], -4);
    echo "\n[Try " . ($i+1) . "] $desc\n";

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $cfg['host'];
    $mail->Port = $cfg['port'];
    $mail->SMTPSecure = $cfg['sec'];
    $mail->SMTPAuth = true;
    $mail->Username = $user;
    $mail->Password = $cfg['pass'];
    $mail->Timeout = 15;
    $mail->SMTPDebug = 0;
    $mail->setFrom($user, 'APS');
    $mail->addAddress('techguruabhay@gmail.com');
    $mail->Subject = 'Test ' . ($i+1);
    $mail->Body = 'Test';
    try {
        $mail->send();
        echo "  ✓ SUCCESS with this config\n";
        echo "  PASS: '" . $cfg['pass'] . "'\n";
        break;
    } catch (Exception $e) {
        echo "  ✗ FAIL: " . $mail->ErrorInfo . "\n";
    }
}
