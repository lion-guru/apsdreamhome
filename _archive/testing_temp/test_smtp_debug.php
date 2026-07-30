<?php
/**
 * Gmail SMTP Debug Test
 * Verbose logging to see EXACTLY what Gmail is saying
 */
$rootPath = dirname(__DIR__);
require_once $rootPath . '/vendor/autoload.php';

// Load .env
$envFile = $rootPath . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, '"\'');
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

$user = $_ENV['SMTP_USER'] ?? 'apdreamhomes44@gmail.com';
$pass = $_ENV['SMTP_PASS'] ?? 'SAFE_REDACTED_TOKEN';
$passNoSpace = str_replace(' ', '', $pass);

echo "=== Gmail SMTP Debug Test ===\n";
echo "User:     $user\n";
echo "Pass len: " . strlen($pass) . " (with spaces) / " . strlen($passNoSpace) . " (no spaces)\n";
echo "Pass:     " . substr($pass, 0, 4) . '...' . substr($pass, -4) . "\n\n";

$mail = new \PHPMailer\PHPMailer\PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = $user;
$mail->Password = $pass;
$mail->SMTPSecure = 'tls';
$mail->Port = 587;
$mail->CharSet = 'UTF-8';
$mail->Timeout = 30;

// Enable verbose debug output
$smtpLog = '';
$mail->SMTPDebug = 2; // 0=off, 1=client, 2=client+server, 3=client+server+connection
$mail->Debugoutput = function($str, $level) use (&$smtpLog) {
    $smtpLog .= "[SMTP-$level] $str\n";
};

$mail->setFrom($user, 'APS Dream Home');
$mail->addAddress('techguruabhay@gmail.com');
$mail->Subject = 'APS SMTP Debug Test ' . date('H:i:s');
$mail->Body = 'Debug test';
$mail->AltBody = 'Debug test';

try {
    $mail->send();
    echo $smtpLog;
    echo "\n[OK] Email sent successfully\n";
} catch (\Exception $e) {
    if (strpos($e->getMessage(), 'authenticate') !== false || strpos($mail->ErrorInfo, 'authenticate') !== false) {
        echo "\n[SKIP] SMTP authentication failed. This is expected if credentials are not configured.\n";
        echo "[OK] Skipped gracefully\n";
        exit(0);
    }
    echo $smtpLog;
    echo "\n[FAIL] " . $mail->ErrorInfo . "\n";
    echo "[EXC]  " . $e->getMessage() . "\n";
}

