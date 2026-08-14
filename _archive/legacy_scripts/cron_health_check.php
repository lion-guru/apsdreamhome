<?php
/**
 * Cron health check â€” verifies the 3 daily cron jobs are running on schedule.
 *
 * Run daily (e.g. 11:00, after compliance has finished):
 *   C:\xampp\php\php.exe C:\xampp\htdocs\apsdreamhome\scripts\cron_health_check.php
 *
 * Output (always emitted as the last line, JSON):
 *   {"success":true,"tasks":[ ... ]}
 *
 * Email the admin if any task has no log entry in the last 24 hours.
 * Exit code 0 = all healthy, 1 = at least one task is stale.
 */

use App\Services\EmailService;

$projectRoot = dirname(__DIR__);
$logFile     = $projectRoot . '/logs/cron_health.log';

if (!is_dir(dirname($logFile))) {
    @mkdir(dirname($logFile), 0775, true);
}

// Manual autoloader for CLI mode (no framework bootstrap in cron).
spl_autoload_register(function ($class) {
    $prefix  = 'App\\';
    $baseDir = __DIR__ . '/../app/';
    if (strpos($class, $prefix) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

function logMsg($msg) {
    global $logFile;
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    echo $line;
    @file_put_contents($logFile, $line, FILE_APPEND);
}

function lastLogTimestamp($path) {
    if (!is_file($path) || filesize($path) === 0) {
        return null;
    }
    // Read the first line of the log; daily_alerts_cron.php uses
    // "[YYYY-MM-DD HH:MM:SS]" as the timestamp prefix.
    $handle = @fopen($path, 'r');
    if (!$handle) {
        return null;
    }
    $line = fgets($handle);
    fclose($handle);
    if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $m)) {
        $ts = strtotime($m[1]);
        return $ts ?: null;
    }
    return null;
}

function lastModifiedTimestamp($path) {
    if (!is_file($path)) {
        return null;
    }
    return filemtime($path) ?: null;
}

$cutoff = time() - 86400; // 24 hours

$tasks = [
    [
        'name'        => 'APS_DailySearchAlerts',
        'script'      => 'scripts/daily_alerts_cron.php',
        'log'         => 'logs/alerts_cron.log',
        'description' => 'Saved-search email alerts',
    ],
    [
        'name'        => 'APS_DailyCompliance',
        'script'      => 'scripts/cron_daily_compliance.php',
        'log'         => 'logs/cron_daily_compliance.log',
        'description' => 'Booking token compliance',
    ],
    [
        'name'        => 'APS_DailyBackup',
        'script'      => 'scripts/backup_cron.php',
        'log'         => 'logs/backup_cron.log',
        'description' => 'Full MySQL backup',
    ],
];

$results    = [];
$staleNames = [];

logMsg('=== Cron health check started ===');

foreach ($tasks as $task) {
    $logPath   = $projectRoot . '/' . $task['log'];
    $logTs     = lastLogTimestamp($logPath);
    $modTs     = lastModifiedTimestamp($logPath);
    $hasScript = file_exists($projectRoot . '/' . $task['script']);
    $hasLog    = is_file($logPath);

    $status = 'ok';
    $reason = null;

    if (!$hasScript) {
        $status = 'missing_script';
        $reason = 'Script file not found';
    } elseif (!$hasLog) {
        $status = 'missing';
        $reason = 'No log file yet (has the task ever run?)';
    } elseif ($logTs === null) {
        $status = 'missing';
        $reason = 'Log file exists but no parseable timestamp on first line';
    } elseif ($logTs < $cutoff && $modTs < $cutoff) {
        $status = 'stale';
        $reason = 'Last entry: ' . date('Y-m-d H:i:s', $logTs) . ' (>24h ago)';
    }

    if ($status !== 'ok') {
        $staleNames[] = $task['name'] . ' (' . $reason . ')';
    }

    $results[] = [
        'name'        => $task['name'],
        'description' => $task['description'],
        'script'      => $task['script'],
        'log'         => $task['log'],
        'last_run'    => $logTs ? date('c', $logTs) : null,
        'last_seen'   => $modTs ? date('c', $modTs) : null,
        'status'      => $status,
        'reason'      => $reason,
    ];

    logMsg(sprintf('  %-26s  %s', $task['name'], $status . ($reason ? ' â€” ' . $reason : '')));
}

$ok = empty($staleNames);

if (!$ok) {
    $subject = '[APS Cron] ' . count($staleNames) . ' task(s) overdue at ' . date('Y-m-d H:i:s');
    $body    = '<h2>APS Dream Home Cron Health Check â€” Issues Found</h2>'
             . '<p><strong>Time:</strong> ' . date('Y-m-d H:i:s') . '</p>'
             . '<p>The following scheduled tasks have not run in the last 24 hours:</p><ul>';
    foreach ($staleNames as $name) {
        $body .= '<li><code>' . htmlspecialchars($name) . '</code></li>';
    }
    $body .= '</ul><p>Check that:</p><ul>'
           . '<li>Windows Task Scheduler has the 3 <code>APS_*</code> tasks enabled.</li>'
           . '<li>The user account running the task has permission to execute PHP and write to <code>logs/</code>.</li>'
           . '<li>PHP can reach the database (env vars in <code>.env</code> or system).</li>'
           . '</ul>';

    try {
        $adminEmail = getenv('ADMIN_EMAIL') ?: 'admin@apsdreamhome.com';
        $mailer     = new EmailService();
        $mailer->send($adminEmail, $subject, $body);
        logMsg('Admin notified: ' . $adminEmail);
    } catch (\Throwable $e) {
        logMsg('Admin notification email failed: ' . $e->getMessage());
    }
}

$elapsed  = round((microtime(true) - (defined('HEALTH_START_TS') ? HEALTH_START_TS : microtime(true))), 2);
$response = [
    'success' => $ok,
    'checked_at' => date('c'),
    'cutoff'     => date('c', $cutoff),
    'stale'      => $staleNames,
    'tasks'      => $results,
];

logMsg('=== Cron health check complete: ' . ($ok ? 'OK' : 'ISSUES FOUND') . ' ===');

// Final line is always valid JSON for machine consumers.
echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL;

exit($ok ? 0 : 1);?>