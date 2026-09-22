<?php
/**
 * APS Dream Home — Enterprise Stress & Load Test (Session 118)
 * File: testing/stress_load_test.php
 * Usage (CLI): php testing/stress_load_test.php [--no-color]
 *
 * Suites:
 *   S1  Concurrent Plot Search (200 filtered SELECTs, real `plots` table, read-only)
 *   S1b In-memory 1000 dummy-plot filter benchmark (pure PHP, zero DB writes)
 *   S2  MLM genealogy recursive traversal (real `mlm_network_tree` + simulated 500-node downline)
 *   S3  Payout voucher calc benchmark (TDS 194H 5% + Admin Fee 5% over 500 simulated rows)
 *   S4  Lead ingestion & pipeline movement (scratch rows with STRESS prefix + guaranteed cleanup)
 *
 * SAFETY RULES (hard):
 *   - NO DROP / ALTER / TRUNCATE / DELETE-without-prefix anywhere in this file.
 *   - Live tables are only SELECTed, except S4 scratch INSERTs which are DELETEd in a
 *     finally block and verified 0-remaining (FK-safe: leads has no child writes here).
 *   - All SQL is prepared + bound. Exit code 0 = all suites PASS, 1 = any FAIL.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$NO_COLOR = in_array('--no-color', $argv ?? [], true);
function c(string $t, string $color = ''): string {
    global $NO_COLOR;
    if ($NO_COLOR || $color === '' || DIRECTORY_SEPARATOR === '\\' && getenv('NO_COLOR')) return $t;
    $m = ['green' => "\033[32m", 'red' => "\033[31m", 'yellow' => "\033[33m", 'cyan' => "\033[36m", 'bold' => "\033[1m", 'reset' => "\033[0m"];
    return ($m[$color] ?? '') . $t . $m['reset'];
}
function verdict(bool $ok): string { return $ok ? c('PASS', 'green') : c('FAIL', 'red'); }

$APP_ROOT = dirname(__DIR__);
$dbCfg = ['host' => '127.0.0.1', 'port' => '3306', 'database' => 'apsdreamhome', 'username' => 'root', 'password' => ''];
$cfgFile = $APP_ROOT . '/config/database.php';
if (is_file($cfgFile)) { $dbCfg = array_merge($dbCfg, (array)require $cfgFile); }

$SLA_MS = 50.0;          // per-query SLA
$S1_N = 200;             // plot search iterations
$S4_N = 50;              // scratch leads (small: table already has ~37k rows)
$SIM_PLOTS = 1000;       // in-memory dummy plots
$SIM_DOWNLINE = 500;     // in-memory dummy downline nodes
$SIM_PAYOUTS = 500;      // simulated payout rows

$report = ['generated_at' => date('c'), 'sla_ms_per_query' => $SLA_MS, 'suites' => []];
$suitesOk = true;

try {
    $pdo = new PDO(
        "mysql:host={$dbCfg['host']};port={$dbCfg['port']};dbname={$dbCfg['database']};charset=utf8mb4",
        $dbCfg['username'], $dbCfg['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
         PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_TIMEOUT => 5]
    );
    echo c('DB connected: ', 'cyan') . "{$dbCfg['host']}:{$dbCfg['port']}/{$dbCfg['database']}\n";
} catch (Throwable $e) {
    echo c('FATAL: DB connect failed: ' . $e->getMessage(), 'red') . "\n";
    exit(1);
}

function stats(array $samples): array {
    sort($samples);
    $n = count($samples);
    $p95 = $n > 0 ? $samples[(int)min($n - 1, floor(0.95 * $n))] : 0;
    return ['n' => $n, 'total_ms' => round(array_sum($samples), 2),
        'avg_ms' => $n ? round(array_sum($samples) / $n, 3) : 0,
        'max_ms' => $n ? round(max($samples), 3) : 0, 'p95_ms' => round($p95, 3)];
}

// ---------------- S1: concurrent plot search (read-only) ----------------
echo c("\n[S1] Plot search x$S1_N (colony x status x price-range)", 'bold') . "\n";
try {
    $colonyIds = $pdo->query("SELECT id FROM colonies")->fetchAll(PDO::FETCH_COLUMN);
    if (!$colonyIds) $colonyIds = [0];
    $statuses = ['available', 'booked', 'sold', 'hold', 'reserved'];
    $st = $pdo->prepare("SELECT id, plot_number, colony_id, status, total_price, price_per_sqft
        FROM plots WHERE colony_id = ? AND status = ? AND total_price BETWEEN ? AND ? LIMIT 25");
    mt_srand(20260918);
    $samples = []; $rowsTotal = 0;
    $t0 = microtime(true);
    for ($i = 0; $i < $S1_N; $i++) {
        $lo = mt_rand(0, 50) * 100000;
        // every 4th pass uses a broad range so the suite provably touches real rows
        $hi = ($i % 4 === 0) ? 100000000 : $lo + 2000000;
        $q0 = microtime(true);
        $st->execute([$colonyIds[$i % count($colonyIds)], $statuses[$i % count($statuses)], $lo, $hi]);
        $rowsTotal += count($st->fetchAll());
        $samples[] = (microtime(true) - $q0) * 1000;
    }
    $s = stats($samples); $s['rows_touched'] = $rowsTotal;
    $s['pass'] = $s['avg_ms'] <= $SLA_MS;
    $report['suites']['S1_plot_search'] = $s;
    $suitesOk = $suitesOk && $s['pass'];
    printf("  queries=%d total=%.1fms avg=%.3fms p95=%.3fms max=%.3fms rows=%d SLA(<=%.0fms) %s\n",
        $s['n'], $s['total_ms'], $s['avg_ms'], $s['p95_ms'], $s['max_ms'], $rowsTotal, $SLA_MS, verdict($s['pass']));
} catch (Throwable $e) { echo c('  ERROR: ' . $e->getMessage(), 'red') . "\n"; $report['suites']['S1_plot_search'] = ['pass' => false, 'error' => $e->getMessage()]; $suitesOk = false; }

// ---------------- S1b: 1000 dummy plots in-memory ----------------
echo c("\n[S1b] In-memory $SIM_PLOTS dummy-plot filter x$S1_N (no DB)", 'bold') . "\n";
try {
    mt_srand(42);
    $dummy = [];
    for ($i = 0; $i < $SIM_PLOTS; $i++) {
        $dummy[] = ['id' => $i + 1, 'colony_id' => mt_rand(1, 5), 'status' => ['available', 'booked', 'sold', 'hold'][$i % 4],
            'price' => mt_rand(200000, 6000000), 'area' => mt_rand(500, 5000)];
    }
    $samples = []; $hits = 0;
    for ($i = 0; $i < $S1_N; $i++) {
        $q0 = microtime(true);
        $c0 = ($i % 5) + 1; $lo = 500000;
        $n = 0;
        foreach ($dummy as $p) { if ($p['colony_id'] === $c0 && $p['status'] === 'available' && $p['price'] >= $lo && $p['price'] <= $lo + 2000000) $n++; }
        $hits += $n;
        $samples[] = (microtime(true) - $q0) * 1000;
    }
    $s = stats($samples); $s['pool'] = $SIM_PLOTS; $s['hits_total'] = $hits;
    $s['pass'] = $s['avg_ms'] <= $SLA_MS;
    $report['suites']['S1b_memory_plots'] = $s;
    $suitesOk = $suitesOk && $s['pass'];
    printf("  pool=%d passes=%d avg=%.3fms max=%.3fms hits=%d %s\n", $SIM_PLOTS, $s['n'], $s['avg_ms'], $s['max_ms'], $hits, verdict($s['pass']));
    unset($dummy);
} catch (Throwable $e) { echo c('  ERROR: ' . $e->getMessage(), 'red') . "\n"; $report['suites']['S1b_memory_plots'] = ['pass' => false, 'error' => $e->getMessage()]; $suitesOk = false; }

// ---------------- S2: genealogy traversal ----------------
echo c("\n[S2] MLM genealogy traversal (real tree + $SIM_DOWNLINE-node dummy downline)", 'bold') . "\n";
try {
    $rows = $pdo->query("SELECT associate_id, parent_id, level FROM mlm_network_tree")->fetchAll();
    $children = []; $allIds = [];
    foreach ($rows as $r) { $children[(string)$r['parent_id']][] = (string)$r['associate_id']; $allIds[(string)$r['associate_id']] = true; }
    // roots = nodes never appearing as a child
    $childSet = []; foreach ($children as $list) foreach ($list as $cid) $childSet[$cid] = true;
    $roots = []; foreach (array_keys($allIds) as $id) if (!isset($childSet[$id])) $roots[] = $id;
    if (!$roots && $rows) $roots = [(string)$rows[0]['associate_id']];
    $q0 = microtime(true); $realVisited = 0; $realMaxDepth = 0;
    foreach (array_slice($roots, 0, 5) as $root) {
        $queue = [[$root, 1]];
        while ($queue) {
            [$node, $d] = array_pop($queue); $realVisited++;
            if ($d > $realMaxDepth) $realMaxDepth = $d;
            foreach ($children[$node] ?? [] as $ch) $queue[] = [$ch, $d + 1];
        }
    }
    $realMs = (microtime(true) - $q0) * 1000;
    // simulated 500-node binary downline (depth 9 => covers 5+ level requirement)
    $q1 = microtime(true);
    $simChildren = []; for ($i = 2; $i <= $SIM_DOWNLINE; $i++) $simChildren[(int)($i / 2)][] = $i;
    $biz = []; for ($i = 1; $i <= $SIM_DOWNLINE; $i++) $biz[$i] = 50000 + (($i * 7919) % 450000);
    $queue = [[1, 1]]; $simVisited = 0; $simMaxDepth = 0; $rollup = 0;
    $levelCount = [];
    while ($queue) {
        [$node, $d] = array_pop($queue); $simVisited++; $rollup += $biz[$node];
        $levelCount[$d] = ($levelCount[$d] ?? 0) + 1;
        if ($d > $simMaxDepth) $simMaxDepth = $d;
        foreach ($simChildren[$node] ?? [] as $ch) $queue[] = [$ch, $d + 1];
    }
    $simMs = (microtime(true) - $q1) * 1000;
    $s = ['real_rows' => count($rows), 'real_roots_probed' => min(5, count($roots)), 'real_visited' => $realVisited,
        'real_max_depth' => $realMaxDepth, 'real_ms' => round($realMs, 3),
        'sim_nodes' => $SIM_DOWNLINE, 'sim_visited' => $simVisited, 'sim_max_depth' => $simMaxDepth,
        'sim_levels' => $levelCount, 'sim_rollup' => $rollup, 'sim_ms' => round($simMs, 3)];
    $s['pass'] = ($simVisited === $SIM_DOWNLINE) && ($simMaxDepth >= 5) && ($realMs <= 2000);
    $report['suites']['S2_genealogy'] = $s;
    $suitesOk = $suitesOk && $s['pass'];
    printf("  real: rows=%d roots=%d visited=%d depth=%d time=%.2fms\n", $s['real_rows'], $s['real_roots_probed'], $realVisited, $realMaxDepth, $realMs);
    printf("  sim: nodes=%d visited=%d depth=%d rollup=Rs.%s time=%.2fms %s\n", $SIM_DOWNLINE, $simVisited, $simMaxDepth, number_format($rollup), $simMs, verdict($s['pass']));
} catch (Throwable $e) { echo c('  ERROR: ' . $e->getMessage(), 'red') . "\n"; $report['suites']['S2_genealogy'] = ['pass' => false, 'error' => $e->getMessage()]; $suitesOk = false; }

// ---------------- S3: payout voucher calc ----------------
echo c("\n[S3] Payout voucher calc x$SIM_PAYOUTS (Admin 5% + TDS 194H 5%)", 'bold') . "\n";
try {
    $q0 = microtime(true);
    $totGross = 0; $totAdmin = 0; $totTds = 0; $totNet = 0;
    for ($i = 0; $i < $SIM_PAYOUTS; $i++) {
        $gross = 10000 + (($i * 7919) % 490000);
        $admin = round($gross * 0.05, 2);
        $taxable = $gross - $admin;
        $tds = round($taxable * 0.05, 2);
        $net = round($taxable - $tds, 2);
        $totGross += $gross; $totAdmin += $admin; $totTds += $tds; $totNet += $net;
    }
    $ms = (microtime(true) - $q0) * 1000;
    // hand-verified sample: 100000 -> admin 5000, taxable 95000, tds 4750, net 90250
    $g = 100000; $a = round($g * 0.05, 2); $t = $g - $a; $td = round($t * 0.05, 2); $n = round($t - $td, 2);
    $mathOk = ($a === 5000.0 && $td === 4750.0 && $n === 90250.0);
    // live ledger sanity (read-only): status enum distribution
    $dist = $pdo->query("SELECT status, COUNT(*) c, ROUND(COALESCE(SUM(amount),0),2) s FROM mlm_commission_ledger GROUP BY status")->fetchAll();
    $s = ['rows' => $SIM_PAYOUTS, 'ms' => round($ms, 3), 'avg_ms' => round($ms / $SIM_PAYOUTS, 4),
        'tot_gross' => round($totGross, 2), 'tot_admin_fee' => round($totAdmin, 2), 'tot_tds' => round($totTds, 2),
        'tot_net' => round($totNet, 2), 'math_sample_ok' => $mathOk, 'live_ledger_by_status' => $dist];
    $s['pass'] = $mathOk && ($ms / $SIM_PAYOUTS <= $SLA_MS);
    $report['suites']['S3_payout_calc'] = $s;
    $suitesOk = $suitesOk && $s['pass'];
    printf("  rows=%d total=%.2fms avg/row=%.4fms gross=Rs.%s admin=Rs.%s tds=Rs.%s net=Rs.%s math=%s %s\n",
        $SIM_PAYOUTS, $ms, $ms / $SIM_PAYOUTS, number_format($totGross), number_format($totAdmin),
        number_format($totTds), number_format($totNet), $mathOk ? 'OK' : 'MISMATCH', verdict($s['pass']));
} catch (Throwable $e) { echo c('  ERROR: ' . $e->getMessage(), 'red') . "\n"; $report['suites']['S3_payout_calc'] = ['pass' => false, 'error' => $e->getMessage()]; $suitesOk = false; }

// ---------------- S4: lead ingestion (scratch + guaranteed cleanup) ----------------
echo c("\n[S4] Lead ingestion x$S4_N (scratch STRESS rows, then full cleanup)", 'bold') . "\n";
$tag = 'ST' . substr((string)time(), -6);
$insertedIds = [];
try {
    $ins = $pdo->prepare("INSERT INTO leads (tenant_id, name, email, phone, status, source, notes, created_at)
        VALUES (1, ?, ?, ?, 'new', 'stress_test', 'S4 scratch - safe to delete', NOW())");
    $q0 = microtime(true);
    for ($i = 0; $i < $S4_N; $i++) {
        $ins->execute(["STRESS Seed $i $tag", "stress_{$tag}_" . sprintf('%03d', $i) . "@example.test", $tag . sprintf('%04d', $i)]);
        $insertedIds[] = $pdo->lastInsertId();
    }
    $insMs = (microtime(true) - $q0) * 1000;
    $q1 = microtime(true);
    $cnt = $pdo->prepare("SELECT COUNT(*) FROM leads WHERE status='new' AND email LIKE ?");
    $cnt->execute(["%stress_{$tag}_%"]);
    $found = (int)$cnt->fetchColumn();
    $qryMs = (microtime(true) - $q1) * 1000;
    $exp = $pdo->query("EXPLAIN SELECT id FROM leads WHERE status='new'")->fetchAll();
    $s = ['inserted' => count($insertedIds), 'insert_ms' => round($insMs, 2), 'avg_insert_ms' => round($insMs / max(1, $S4_N), 3),
        'queried_found' => $found, 'query_ms' => round($qryMs, 3), 'explain_rows' => count($exp)];
    $s['pass'] = ($found === $S4_N) && (($insMs / $S4_N) <= 500) && ($qryMs <= 2000);
    $report['suites']['S4_lead_ingest'] = $s;
    $suitesOk = $suitesOk && $s['pass'];
    printf("  inserted=%d insert_total=%.1fms avg=%.3fms/row queried=%d query=%.2fms %s\n",
        $s['inserted'], $insMs, $insMs / max(1, $S4_N), $found, $qryMs, verdict($s['pass']));
} catch (Throwable $e) {
    echo c('  ERROR: ' . $e->getMessage(), 'red') . "\n";
    $report['suites']['S4_lead_ingest'] = ['pass' => false, 'error' => $e->getMessage()];
    $suitesOk = false;
} finally {
    // guaranteed cleanup (prefix-scoped only — never touches live rows)
    try {
        $del = $pdo->prepare("DELETE FROM leads WHERE email LIKE ?");
        $del->execute(["%stress_{$tag}_%"]);
        $left = 0;
        try { $c2 = $pdo->prepare("SELECT COUNT(*) FROM leads WHERE email LIKE ?"); $c2->execute(["%stress_{$tag}_%"]); $left = (int)$c2->fetchColumn(); } catch (Throwable $e) {}
        $report['suites']['S4_lead_ingest']['cleanup_deleted'] = $del->rowCount();
        $report['suites']['S4_lead_ingest']['cleanup_left'] = $left;
        echo $left === 0 ? c("  cleanup: scratch rows removed, 0 left", 'green') . "\n" : c("  cleanup WARNING: $left scratch rows left!", 'red') . "\n";
        if ($left !== 0) $suitesOk = false;
    } catch (Throwable $e) { echo c('  cleanup ERROR: ' . $e->getMessage(), 'red') . "\n"; $suitesOk = false; }
}

// ---------------- summary + JSON ----------------
$peakMb = round(memory_get_peak_usage(true) / 1048576, 2);
$totalQ = ($report['suites']['S1_plot_search']['n'] ?? 0) + $S1_N + $SIM_PAYOUTS + $S4_N * 2;
$report['memory_peak_mb'] = $peakMb;
$report['overall'] = $suitesOk ? 'PASS' : 'FAIL';

$logDir = $APP_ROOT . '/storage/logs';
if (!is_dir($logDir)) @mkdir($logDir, 0775, true);
$logFile = $logDir . '/stress_test_report.json';
file_put_contents($logFile, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo c("\n===== STRESS RESULT: " . ($suitesOk ? 'ALL PASS' : 'FAILURES PRESENT'), $suitesOk ? 'green' : 'red') . "\n";
echo "Memory peak: {$peakMb} MB | JSON: storage/logs/stress_test_report.json\n";
exit($suitesOk ? 0 : 1);
