<?php
/**
 * php_error_probe.php - Nightly php_error.log signature probe.
 *
 * Reads the project's PHP error log (last 24h by default), buckets every line
 * into a known signature class, prints a plain-text report, and optionally
 * creates an in-app admin notification (user_id 1) with the summary.
 *
 * Modes:
 *   php scripts/php_error_probe.php                 # report only, last 24h of lines
 *   php scripts/php_error_probe.php --lines=5000    # last N lines instead of time window
 *   php scripts/php_error_probe.php --notify        # additionally create admin notification
 *   php scripts/php_error_probe.php --json          # machine-readable output
 *
 * Anti-spam: --notify writes at most ONE notification per 12h when zero SQL-class
 * signatures were found; always writes when SQL-class signatures are found.
 * The script NEVER exits non-zero for probe findings (cron-safe).
 */

$opts = getopt('', ['lines::', 'notify', 'json', 'hours::', 'root::']);
$root = rtrim($opts['root'] ?? 'C:/xampp/htdocs/apsdreamhome', '/\\');
$logFile = $root . '/logs/php_error.log';
$hours = isset($opts['hours']) ? (float)$opts['hours'] : 24.0;
$maxLines = isset($opts['lines']) ? (int)$opts['lines'] : 20000;
$notify = isset($opts['notify']);
$asJson = isset($opts['json']);

if (!is_file($logFile)) {
    fwrite(STDERR, "php_error_probe: log file not found: {$logFile}\n");
    exit(0);
}

// ---- read lines (time window by default) --------------------------------
$fh = fopen($logFile, 'r');
if (!$fh) {
    fwrite(STDERR, "php_error_probe: cannot open log\n");
    exit(0);
}

$cutoff = time() - (int)($hours * 3600);
$selected = [];
$allLines = [];
while (($line = fgets($fh)) !== false) {
    $allLines[] = $line;
}
fclose($fh);

// Keep the tail for speed, then filter by timestamp if parseable
$tail = array_slice($allLines, -$maxLines);
foreach ($tail as $line) {
    if (preg_match('/^\[(\d{2}-[A-Za-z]{3}-\d{4} \d{2}:\d{2}:\d{2})/', $line, $m)) {
        $ts = strtotime($m[1]);
        if ($ts !== false && $ts < $cutoff && $hours < 999) continue;
    }
    $selected[] = $line;
}

// ---- bucket signatures ----------------------------------------------------
$buckets = [
    'sql_1054'          => ['label' => 'SQL 1054 (unknown column)',              'rx' => '/1054|Unknown column/i'],
    'sql_1146'          => ['label' => 'SQL 1146 (table not found)',             'rx' => '/1146|Base table.*not found/i'],
    'sql_1265_1364'     => ['label' => 'SQL 1265/1364 (strict enum/no default)', 'rx' => '/1265|1364/'],
    'sql_1052'          => ['label' => 'SQL 1052 (ambiguous column)',            'rx' => '/1052/i'],
    'sql_hy2423'        => ['label' => 'HY2423 (placeholder count mismatch)',    'rx' => '/HY2423/i'],
    'sql_hy093'         => ['label' => 'HY093 (invalid param count)',            'rx' => '/HY093/i'],
    'sql_1052_1146_mix' => ['label' => 'SQLSTATE 42S22/42S02 (general)',        'rx' => '/42S22|42S02/i'],
    'view_not_found'    => ['label' => 'View-not-found renders (empty chrome)',  'rx' => '/View not found/i'],
    'email_smtp_auth'   => ['label' => 'EmailService SMTP auth failure',         'rx' => '/EmailService.*(authenticate|SMTP)/i'],
    'fatal_error'       => ['label' => 'PHP Fatal error',                        'rx' => '/PHP Fatal error|Uncaught|Parse error/i'],
    'warning'           => ['label' => 'PHP Warning',                            'rx' => '/PHP Warning|Warning:/i'],
    'notice'            => ['label' => 'PHP Notice/Deprecated',                'rx' => '/Notice|Deprecated/i'],
    'admin_gate_block'  => ['label' => 'ADMIN-GATE blocks (log noise)',          'rx' => '/ADMIN-GATE/i'],
    'db_constraint'     => ['label' => 'SQL 1216/1217/1227/1451/1452 (FK/uniq)','rx' => '/1216|1217|1227|1451|1452/'],
    'type_error'        => ['label' => 'TypeError / ArgumentCount',              'rx' => '/TypeError|ArgumentCountError/i'],
    'other'             => ['label' => 'Other/unclassified',                     'rx' => '/^(?!.*)$/'], // never matches; fallback bucket
];

$counts = array_fill_keys(array_keys($buckets), 0);
$samples = array_fill_keys(array_keys($buckets), []);

$tsBucketsSql = [];   // track timestamps of every SQL-class line so the report shows the real range, not just the count
$sqlRx = '/1054|Unknown column|1146|Base table.*not found|1265|1364|1052|HY2423|HY093|42S22|42S02|1216|1217|1227|1451|1452/i';

foreach ($selected as $line) {
    $hit = false;
    foreach ($buckets as $key => $b) {
        if ($key === 'other') continue;
        if (preg_match($b['rx'], $line)) {
            $counts[$key]++;
            if (count($samples[$key]) < 3) $samples[$key][] = trim($line);
            $hit = true;
            break; // one signature per line
        }
    }
    if ($hit && preg_match($sqlRx, $line) && preg_match('/^\[([0-9]{2}-[A-Za-z]{3}-\d{4} \d{2}:\d{2}:\d{2})/', $line, $m3)) {
        $ts = strtotime($m3[1]);
        if ($ts !== false) $tsBucketsSql[] = $ts;
    }
    if (!$hit) $counts['other']++;
}

$sqlClasses = $counts['sql_1054'] + $counts['sql_1146'] + $counts['sql_1265_1364'] + $counts['sql_1052']
            + $counts['sql_hy2423'] + $counts['sql_hy093'] + $counts['sql_1052_1146_mix'] + $counts['db_constraint'];
$emailClasses = $counts['email_smtp_auth'];
$fatalClasses = $counts['fatal_error'] + $counts['type_error'];
$viewClasses = $counts['view_not_found'];

$report = [
    'log_file'      => $logFile,
    'hours'         => $hours,
    'lines_scanned' => count($selected),
    'buckets'       => [],
    'summary'       => [
        'sql_class_total'   => $sqlClasses,
        'email_class_total' => $emailClasses,
        'fatal_class_total' => $fatalClasses,
        'view_class_total'  => $viewClasses,
        'admin_gate_noise'  => $counts['admin_gate_block'],
        'other'             => $counts['other'],
        'sql_class_first'   => $tsBucketsSql ? date('Y-m-d H:i:s', min($tsBucketsSql)) : null,
        'sql_class_last'    => $tsBucketsSql ? date('Y-m-d H:i:s', max($tsBucketsSql)) : null,
    ],
    'samples'       => [],
];
foreach ($buckets as $key => $b) {
    $report['buckets'][$key] = $counts[$key];
    if ($counts[$key] > 0) $report['samples'][$key] = $samples[$key];
}

if ($asJson) {
    echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
} else {
    echo str_repeat('=', 72) . "\n";
    echo "php_error_probe report\n";
    echo str_repeat('=', 72) . "\n";
    echo "log            : {$logFile}\n";
    echo "window         : last {$hours}h (lines scanned: " . count($selected) . ")\n\n";
    foreach ($buckets as $key => $b) {
        $n = $counts[$key];
        if ($n > 0) printf("  %-44s %6d\n", $b['label'], $n);
    }
    echo "\n  SQL-class total        : {$sqlClasses}\n";
    if ($tsBucketsSql) {
        echo "  SQL-class window range : " . date('d-M H:i', min($tsBucketsSql)) . " Europe/Berlin" . " -> " . date('d-M H:i', max($tsBucketsSql)) . " Europe/Berlin\n";
        echo "  (findings all predate today's session log if the latest is older than 01:00 cron - check before paging anyone)\n";
    }
    echo "  Email-class (SMTP)     : {$emailClasses}\n";
    echo "  Fatal/TypeError total  : {$fatalClasses}\n";
    echo "  View-not-found total   : {$viewClasses}\n";
    echo "  Admin-gate noise       : {$counts['admin_gate_block']} (benign)\n";
    echo str_repeat('-', 72) . "\n";
    if ($sqlClasses === 0 && $fatalClasses === 0 && $viewClasses === 0) {
        echo "VERDICT: 0 new SQL/fatal/view signatures in window.\n";
    } else {
        echo "VERDICT: ACTION REQUIRED — signatures found. See samples (--json).\n";
    }
    echo str_repeat('=', 72) . "\n";
}

// ---- optional admin notification (anti-spam guarded) ---------------------
if ($notify) {
    try {
        require_once $root . '/config/bootstrap.php';
        // Anti-spam: if there is an unread same-type notification newer than 12h, skip it.
        $db = App\Core\Database\Database::getInstance();
        $row = $db->fetchOne(
            "SELECT id FROM notifications WHERE user_id = 1 AND type = 'php_error_probe' AND created_at > DATE_SUB(NOW(), INTERVAL 12 HOUR) ORDER BY id DESC LIMIT 1"
        );
        $shouldWrite = $row !== false && !$row
            ? true                         // nothing in 12h → write
            : $sqlClasses > 0 || $fatalClasses > 0 || $viewClasses > 0; // stale row → write only on findings
        if (!$shouldWrite) {
            echo "notify: skipped (unread probe notification < 12h old and 0 findings)\n";
            exit(0);
        }
        $db->query(
            "INSERT INTO notifications (user_id, type, title, message, is_read, status, created_at)
             VALUES (1, 'php_error_probe', ?, ?, 0, 'unread', NOW())",
            [
                'php_error_probe: ' . ($sqlClasses + $fatalClasses + $viewClasses) . ' signature(s) in ' . $hours . 'h',
                'php_error_probe last 24h — SQL:' . $sqlClasses . ' | SMTP:' . $emailClasses . ' | Fatal:' . $fatalClasses
                    . ' | View-not-found:' . $viewClasses . ' | lines scanned: ' . count($selected)
                    . ' — full report: php scripts/php_error_probe.php --json'
            ]
        );
        echo "notify: admin notification created (user_id=1)\n";
    } catch (Throwable $e) {
        fwrite(STDERR, "notify failed (non-fatal): " . $e->getMessage() . "\n");
    }
}
exit(0);
