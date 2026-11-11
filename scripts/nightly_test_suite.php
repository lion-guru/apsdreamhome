<?php
/**
 * Nightly test suite runner (scheduled via setup_test_scheduler.ps1)
 * - Runs php testing/master_test_runner.php
 * - Captures output to logs/testing/nightly_YYYY-MM-DD.log
 * - DB counts snapshot to same log
 * - Exit 1 on failure (Task Scheduler shows failure)
 * - Best-effort email alert via EmailService if configured
 */
$root = dirname(__DIR__);
$logsDir = $root . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'testing';
if (!is_dir($logsDir)) mkdir($logsDir, 0755, true);
$date = date('Y-m-d');
$stamp = date('Y-m-d_His');
$logFile = $logsDir . DIRECTORY_SEPARATOR . 'nightly_' . $stamp . '.log';

function logLine($msg) {
    global $logFile;
    $line = "[" . date('Y-m-d H:i:s') . "] " . $msg . "\n";
    echo $line;
    file_put_contents($logFile, $line, FILE_APPEND);
}

logLine("=== APS Nightly Test Suite ===");
logLine("PHP: " . PHP_VERSION . " | OS: " . PHP_OS);

// 1. Master test runner
logLine("Running master_test_runner.php ...");
$master = $root . DIRECTORY_SEPARATOR . 'testing' . DIRECTORY_SEPARATOR . 'master_test_runner.php';
$out = [];
$code = 0;
exec('php "' . escapeshellarg($master) . '" 2>&1', $out, $code);
foreach ($out as $l) logLine($l);
$masterPass = ($code === 0 && strpos(implode("\n", $out), '10') !== false);
logLine("Master exit code: $code => " . ($code === 0 ? "PASS" : "FAIL"));

// 2. DB counts snapshot
try {
    require $root . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
    $db = \App\Core\Database\Database::getInstance();
    $u = $db->fetchAll('SELECT COUNT(*) as cnt FROM users');
    $p = $db->fetchAll('SELECT COUNT(*) as cnt FROM plots');
    $l = $db->fetchAll('SELECT COUNT(*) as cnt FROM leads');
    logLine("DB counts: users=" . ($u[0]['cnt'] ?? '?') . " plots=" . ($p[0]['cnt'] ?? '?') . " leads=" . ($l[0]['cnt'] ?? '?'));
} catch (\Throwable $e) {
    logLine("DB snapshot failed: " . $e->getMessage());
}

// 3. Alert on failure (best-effort)
if ($code !== 0) {
    logLine("ALERT: Master suite failed, see log $logFile");
    try {
        $to = $_ENV['DEAD_LETTER_ALERT_EMAIL'] ?? null;
        if ($to) {
            $svc = new \App\Services\Communication\EmailService();
            $svc->send($to, '[ALERT] APS Nightly tests failed', 'Master suite exit ' . $code . ' on ' . date('c'), null, []);
        }
    } catch (\Throwable $e) {
        logLine("Alert send failed (non-fatal): " . $e->getMessage());
    }
    exit(1);
}

logLine("=== Nightly suite PASS ===");
exit(0);
