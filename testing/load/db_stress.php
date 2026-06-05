<?php
/**
 * APS Dream Home — Database Stress Test
 *
 * 1000 SELECTs on hot tables, 100 INSERTs/UPDATEs, EXPLAIN to surface missing indexes.
 * No external deps. Uses PDO directly.
 *
 * Usage:
 *   php testing/load/db_stress.php
 */

declare(strict_types=1);

@set_time_limit(0);

$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbPort = getenv('DB_PORT') ?: '3307';
$dbName = getenv('DB_NAME') ?: 'apsdreamhome';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║         APS Dream Home — DB Stress Test                      ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";
echo "DB: {$dbUser}@{$dbHost}:{$dbPort}/{$dbName}\n\n";

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT => false]
    );
} catch (PDOException $e) {
    fwrite(STDERR, "❌ DB connect failed: " . $e->getMessage() . "\n");
    exit(1);
}

// -------- Pick hot tables that are likely to exist --------
$candidateTables = [
    'user_properties'   => 'SELECT id, title, price, location FROM user_properties WHERE status = :s ORDER BY id DESC LIMIT 20',
    'leads'             => 'SELECT id, name, email, status, source FROM leads WHERE status = :s ORDER BY id DESC LIMIT 20',
    'users'             => 'SELECT id, name, email, role FROM users WHERE role = :s ORDER BY id DESC LIMIT 20',
    'projects'          => 'SELECT id, name, status, city FROM projects WHERE status = :s ORDER BY id DESC LIMIT 20',
    'bookings'          => 'SELECT id, plot_id, customer_id, status, amount FROM bookings WHERE status = :s LIMIT 20',
    'colonies'          => 'SELECT id, name, district_id FROM colonies WHERE is_active = :s LIMIT 20',
    'plots'             => 'SELECT id, colony_id, status, area_sqft FROM plots WHERE status = :s LIMIT 20',
    'properties'        => 'SELECT id, title, status, price FROM properties WHERE status = :s LIMIT 20',
    'inquiries'         => 'SELECT id, name, email, status FROM inquiries WHERE status = :s LIMIT 20',
    'notifications'     => 'SELECT id, user_id, type, read_at FROM notifications WHERE user_id = :s LIMIT 20',
    'payments'          => 'SELECT id, booking_id, amount, status FROM payments WHERE status = :s LIMIT 20',
    'commissions'       => 'SELECT id, agent_id, amount, status FROM commissions WHERE status = :s LIMIT 20',
];

// Detect which tables exist
$available = [];
foreach ($candidateTables as $t => $sql) {
    try {
        $pdo->query("SELECT 1 FROM `{$t}` LIMIT 1");
        $available[$t] = $sql;
    } catch (PDOException $e) {
        // skip
    }
}
echo "Tables present: " . count($available) . "/" . count($candidateTables) . "\n";
echo "  " . implode(', ', array_keys($available)) . "\n\n";

// -------- Run SELECT stress --------
$SELECT_PER_TABLE = 1000;
$insertCount      = 100;
$updateCount      = 100;
$slowThresholdMs  = 100;

$allSelectTimes = [];   // ms
$tableStats     = [];   // table => [count, avg, p95, p99, slow_count]
$slowQueries    = [];   // [table, sql, ms]

foreach ($available as $table => $baseSql) {
    $times = [];
    $errs  = 0;
    for ($i = 0; $i < $SELECT_PER_TABLE; $i++) {
        // Vary the parameter so cache doesn't dominate
        $variants = ['active', 'approved', 'pending', '1', '0'];
        $param = $variants[$i % count($variants)];
        $t0 = microtime(true);
        try {
            $stmt = $pdo->prepare($baseSql);
            $stmt->execute([':s' => $param]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $errs++;
            $rows = [];
        }
        $elapsedMs = (microtime(true) - $t0) * 1000;
        $times[] = $elapsedMs;
        if ($elapsedMs > $slowThresholdMs) {
            $slowQueries[] = ['table' => $table, 'sql' => $baseSql, 'ms' => round($elapsedMs, 2)];
        }
    }
    sort($times);
    $count = count($times);
    $avg = array_sum($times) / $count;
    $p95 = $times[(int)($count * 0.95)] ?? 0;
    $p99 = $times[(int)($count * 0.99)] ?? 0;
    $max = max($times);
    $slowCount = count(array_filter($times, fn($t) => $t > $slowThresholdMs));

    $tableStats[$table] = [
        'count'      => $count,
        'errors'     => $errs,
        'avg_ms'     => round($avg, 2),
        'p95_ms'     => round($p95, 2),
        'p99_ms'     => round($p99, 2),
        'max_ms'     => round($max, 2),
        'slow_queries' => $slowCount,
    ];
    $allSelectTimes = array_merge($allSelectTimes, $times);
}

// -------- INSERT / UPDATE stress --------
$insertTimes = [];
$updateTimes = [];

// Try to insert into a "stress_test" table we create on-the-fly
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `_loadtest_temp` (
        id INT AUTO_INCREMENT PRIMARY KEY,
        k VARCHAR(64),
        v TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
} catch (PDOException $e) {
    // ignore
}

// INSERTs
$insOk = 0; $insErr = 0;
for ($i = 0; $i < $insertCount; $i++) {
    $t0 = microtime(true);
    try {
        $stmt = $pdo->prepare("INSERT INTO `_loadtest_temp` (k, v) VALUES (:k, :v)");
        $stmt->execute([':k' => 'k_' . $i, ':v' => str_repeat('x', 100)]);
        $insOk++;
    } catch (PDOException $e) {
        $insErr++;
    }
    $insertTimes[] = (microtime(true) - $t0) * 1000;
}

// UPDATEs
$updOk = 0; $updErr = 0;
for ($i = 0; $i < $updateCount; $i++) {
    $t0 = microtime(true);
    try {
        $stmt = $pdo->prepare("UPDATE `_loadtest_temp` SET v = :v WHERE k = :k");
        $stmt->execute([':v' => 'updated_' . $i, ':k' => 'k_' . $i]);
        $updOk++;
    } catch (PDOException $e) {
        $updErr++;
    }
    $updateTimes[] = (microtime(true) - $t0) * 1000;
}

// Cleanup
try { $pdo->exec("DROP TABLE IF EXISTS `_loadtest_temp`"); } catch (PDOException $e) {}

// -------- EXPLAIN for slow tables (suggested indexes) --------
$indexSuggestions = [];
foreach (array_slice($tableStats, 0, 8, true) as $table => $st) {
    if ($st['p95_ms'] > 50 || $st['slow_queries'] > 10) {
        try {
            $explainRows = $pdo->query("EXPLAIN " . $candidateTables[$table])->fetchAll(PDO::FETCH_ASSOC);
            $cols = [];
            foreach ($explainRows as $r) {
                if (!empty($r['key']) && $r['key'] !== 'PRIMARY') {
                    $cols[] = $r['key'];
                } elseif (empty($r['key']) && isset($r['Extra']) && stripos($r['Extra'], 'Using filesort') !== false) {
                    $indexSuggestions[$table][] = 'filesort detected — consider composite index on ORDER BY columns';
                }
            }
            if ($cols) {
                $indexSuggestions[$table][] = 'using index: ' . implode(', ', array_unique($cols));
            }
        } catch (PDOException $e) {
            // skip
        }
    }
}

// -------- Output --------
$totalSelects = count($allSelectTimes);
sort($allSelectTimes);
$overallP95 = $totalSelects > 0 ? $allSelectTimes[(int)($totalSelects * 0.95)] : 0;
$overallP99 = $totalSelects > 0 ? $allSelectTimes[(int)($totalSelects * 0.99)] : 0;
$overallAvg = $totalSelects > 0 ? array_sum($allSelectTimes) / $totalSelects : 0;
$totalSlow = count($slowQueries);

$report = [
    'meta' => [
        'test_name'  => 'APS Dream Home DB Stress',
        'timestamp'  => date('c'),
        'db'         => "{$dbUser}@{$dbHost}:{$dbPort}/{$dbName}",
        'php_version'=> PHP_VERSION,
        'pdo_driver' => $pdo->getAttribute(PDO::ATTR_DRIVER_NAME),
    ],
    'select_stress' => [
        'tables_tested'        => count($available),
        'selects_per_table'    => $SELECT_PER_TABLE,
        'total_selects'        => $totalSelects,
        'overall_avg_ms'       => round($overallAvg, 2),
        'overall_p95_ms'       => round($overallP95, 2),
        'overall_p99_ms'       => round($overallP99, 2),
        'slow_queries_overall' => $totalSlow,
        'slow_threshold_ms'    => $slowThresholdMs,
    ],
    'per_table' => $tableStats,
    'write_stress' => [
        'inserts' => [
            'count'   => $insertCount,
            'ok'      => $insOk,
            'errors'  => $insErr,
            'avg_ms'  => $insertTimes ? round(array_sum($insertTimes) / count($insertTimes), 2) : 0,
            'p95_ms'  => $insertTimes ? (function() use ($insertTimes) { $c = count($insertTimes); $t = $insertTimes; sort($t); return round($t[(int)($c * 0.95)], 2); })() : 0,
        ],
        'updates' => [
            'count'   => $updateCount,
            'ok'      => $updOk,
            'errors'  => $updErr,
            'avg_ms'  => $updateTimes ? round(array_sum($updateTimes) / count($updateTimes), 2) : 0,
            'p95_ms'  => $updateTimes ? (function() use ($updateTimes) { $c = count($updateTimes); $t = $updateTimes; sort($t); return round($t[(int)($c * 0.95)], 2); })() : 0,
        ],
    ],
    'slow_queries' => array_slice($slowQueries, 0, 20),
    'index_suggestions' => $indexSuggestions,
];

$jsonFile = __DIR__ . '/db_stress_results.json';
file_put_contents($jsonFile, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// Human output
$hr = str_repeat('─', 72);
echo $hr . "\n";
echo "  SELECT STRESS — {$totalSelects} total across " . count($available) . " tables\n";
echo $hr . "\n";
printf("  %-20s %8s %8s %8s %8s %8s\n", 'table', 'count', 'avg_ms', 'p95_ms', 'p99_ms', 'slow');
echo "  " . str_repeat('·', 60) . "\n";
foreach ($tableStats as $t => $st) {
    printf("  %-20s %8d %8.2f %8.2f %8.2f %8d\n",
        $t, $st['count'], $st['avg_ms'], $st['p95_ms'], $st['p99_ms'], $st['slow_queries']);
}
echo $hr . "\n";
echo "  OVERALL: avg=" . round($overallAvg, 2) . "ms  p95=" . round($overallP95, 2) . "ms  p99=" . round($overallP99, 2) . "ms  slow(>100ms)=" . $totalSlow . "\n";
echo $hr . "\n";
echo "  WRITE STRESS — INSERTs: {$insOk}/{$insertCount} ok, " . round(array_sum($insertTimes) / max(count($insertTimes), 1), 2) . "ms avg\n";
echo "                UPDATEs: {$updOk}/{$updateCount} ok, " . round(array_sum($updateTimes) / max(count($updateTimes), 1), 2) . "ms avg\n";
echo $hr . "\n";
if (!empty($indexSuggestions)) {
    echo "  INDEX SUGGESTIONS (tables with p95>50ms or >10 slow queries):\n";
    foreach ($indexSuggestions as $t => $suggs) {
        foreach ($suggs as $s) {
            echo "    • {$t} → {$s}\n";
        }
    }
    echo $hr . "\n";
}
if ($totalSlow > 0) {
    echo "  Top slow queries (first 10):\n";
    foreach (array_slice($slowQueries, 0, 10) as $sq) {
        echo "    • {$sq['table']} : " . $sq['ms'] . "ms\n";
    }
    echo $hr . "\n";
}
echo "📄 JSON → " . realpath($jsonFile) . "\n";
echo "\n✅ DB stress test complete.\n";
exit(0);
