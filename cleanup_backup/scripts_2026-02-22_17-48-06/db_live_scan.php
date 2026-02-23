<?php
// scripts/db_live_scan.php
// Live DB scan: lists tables, row counts, engine, collation, PK/AI info
// Safe, read-only diagnostics.

header('Content-Type: text/html; charset=utf-8');

$start = microtime(true);

// Attempt to include existing DB config for connection
$projectRoot = dirname(__DIR__);
$configPhp = $projectRoot . DIRECTORY_SEPARATOR . 'config.php';

// Fallback env/defaults
$DB_HOST = getenv('DB_HOST') ?: 'localhost';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') ?: '';
$DB_NAME = getenv('DB_NAME') ?: 'apsdreamhome';

// Try to load .env softly (no fatal if missing)
$envFile = $projectRoot . DIRECTORY_SEPARATOR . '.env';
if (file_exists($envFile)) {
    // Simple .env loader
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') === false) continue;
        list($k, $v) = explode('=', $line, 2);
        $k = trim($k);
        $v = trim(trim($v), "\"'\"");
        if ($k !== '') putenv("$k=$v");
    }
    $DB_HOST = getenv('DB_HOST') ?: $DB_HOST;
    $DB_USER = getenv('DB_USER') ?: $DB_USER;
    $DB_PASS = getenv('DB_PASS') ?: $DB_PASS;
    $DB_NAME = getenv('DB_NAME') ?: $DB_NAME;
}

// If config.php exists, include to initialize mysqli $con
if (file_exists($configPhp)) {
    require_once $configPhp; // should define $con (mysqli)
}

// If $con not set, create connection
if (!isset($con) || !($con instanceof mysqli)) {
    mysqli_report(MYSQLI_REPORT_OFF);
    $con = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    if ($con->connect_error) {
        echo '<h2>Database Connection Error</h2>';
        echo '<pre>' . htmlspecialchars($con->connect_error) . '</pre>';
        exit;
    }
    $con->set_charset('utf8mb4');
}

// Helper to fetch one value
function fetch_one(mysqli $con, string $sql, array $params = []) {
    $stmt = $con->prepare($sql);
    if ($params) {
        $types = '';
        $vals = [];
        foreach ($params as $p) {
            if (is_int($p)) $types .= 'i';
            elseif (is_float($p)) $types .= 'd';
            else { $types .= 's'; }
            $vals[] = $p;
        }
        $stmt->bind_param($types, ...$vals);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    if (!$res) return null;
    $row = $res->fetch_array(MYSQLI_NUM);
    $stmt->close();
    return $row ? $row[0] : null;
}

// Get tables
$tables = [];
$rs = $con->query("SHOW TABLES");
if ($rs) {
    while ($row = $rs->fetch_array(MYSQLI_NUM)) {
        $tables[] = $row[0];
    }
    $rs->close();
}

// Gather info_schema data for all tables in one go
$inList = implode(',', array_map(function($t) use ($con) {
    return "'" . $con->real_escape_string($t) . "'";
}, $tables));

$schema = $con->real_escape_string($DB_NAME);
$tblMeta = [];
if ($inList) {
    // Table engine and collation
    $sqlTables = "SELECT TABLE_NAME, ENGINE, TABLE_COLLATION, TABLE_ROWS FROM information_schema.TABLES WHERE TABLE_SCHEMA=? AND TABLE_NAME IN ($inList)";
    $st = $con->prepare($sqlTables);
    $st->bind_param('s', $schema);
    $st->execute();
    $rt = $st->get_result();
    while ($r = $rt->fetch_assoc()) {
        $tblMeta[$r['TABLE_NAME']] = [
            'engine' => $r['ENGINE'],
            'collation' => $r['TABLE_COLLATION'],
            'approx_rows' => (int)$r['TABLE_ROWS'],
            'pk' => false,
            'ai_col' => null,
            'row_count' => null,
        ];
    }
    $st->close();

    // Primary keys
    $sqlPk = "SELECT tc.TABLE_NAME FROM information_schema.TABLE_CONSTRAINTS tc WHERE tc.TABLE_SCHEMA=? AND tc.CONSTRAINT_TYPE='PRIMARY KEY' AND tc.TABLE_NAME IN ($inList)";
    $st = $con->prepare($sqlPk);
    $st->bind_param('s', $schema);
    $st->execute();
    $rt = $st->get_result();
    while ($r = $rt->fetch_assoc()) {
        $t = $r['TABLE_NAME'];
        if (!isset($tblMeta[$t])) $tblMeta[$t] = [];
        $tblMeta[$t]['pk'] = true;
    }
    $st->close();

    // Auto-increment columns
    $sqlAi = "SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND EXTRA LIKE '%auto_increment%' AND TABLE_NAME IN ($inList)";
    $st = $con->prepare($sqlAi);
    $st->bind_param('s', $schema);
    $st->execute();
    $rt = $st->get_result();
    while ($r = $rt->fetch_assoc()) {
        $t = $r['TABLE_NAME'];
        if (!isset($tblMeta[$t])) $tblMeta[$t] = [];
        $tblMeta[$t]['ai_col'] = $r['COLUMN_NAME'];
    }
    $st->close();
}

// Accurate row counts (may be slow on huge tables)
foreach ($tables as $t) {
    $cnt = fetch_one($con, "SELECT COUNT(*) FROM `{$t}`");
    if (!isset($tblMeta[$t])) $tblMeta[$t] = [];
    $tblMeta[$t]['row_count'] = (int)$cnt;
}

$duration = round((microtime(true) - $start) * 1000);

// Render HTML
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>DB Live Scan</title>
  <style>
    body{font-family:Arial, sans-serif; margin:20px;}
    table{border-collapse:collapse; width:100%;}
    th,td{border:1px solid #ccc; padding:8px; font-size:14px;}
    th{background:#f7f7f7; text-align:left;}
    .bad{color:#b00020; font-weight:bold;}
    .ok{color:#0c7b0c; font-weight:bold;}
    .warn{color:#a36b00; font-weight:bold;}
    .meta{font-size:12px; color:#666;}
  </style>
</head>
<body>
  <h1>Database Live Scan</h1>
  <div class="meta">Host: <?=htmlspecialchars($DB_HOST)?> | DB: <?=htmlspecialchars($DB_NAME)?> | Time: <?=$duration?> ms | Tables: <?=count($tables)?></div>
  <?php if (!count($tables)): ?>
    <p class="bad">No tables found. Is the database empty?</p>
  <?php else: ?>
  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Table</th>
        <th>Rows</th>
        <th>Approx Rows</th>
        <th>Engine</th>
        <th>Collation</th>
        <th>Primary Key</th>
        <th>Auto-Increment Column</th>
      </tr>
    </thead>
    <tbody>
      <?php $i=1; foreach ($tables as $t): $m = $tblMeta[$t] ?? []; ?>
      <tr>
        <td><?= $i++ ?></td>
        <td><?= htmlspecialchars($t) ?></td>
        <td><?= isset($m['row_count']) ? (int)$m['row_count'] : '-' ?></td>
        <td><?= isset($m['approx_rows']) ? (int)$m['approx_rows'] : '-' ?></td>
        <td><?= htmlspecialchars($m['engine'] ?? '-') ?></td>
        <td><?= htmlspecialchars($m['collation'] ?? '-') ?></td>
        <td><?= !empty($m['pk']) ? '<span class="ok">Yes</span>' : '<span class="bad">No</span>' ?></td>
        <td><?= htmlspecialchars($m['ai_col'] ?? '-') ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>

  <h2>Notes</h2>
  <ul>
    <li>If any table shows <strong class="bad">Primary Key: No</strong>, it should be fixed (add a primary key and auto-increment id where applicable).</li>
    <li>Approx rows are from information_schema and can be off; exact counts are listed in Rows.</li>
    <li>This scan is read-only and safe.</li>
  </ul>
</body>
</html>
