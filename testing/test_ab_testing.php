<?php
/**
 * A/B Testing framework test suite.
 *
 * Pure-PHP, runs from CLI: php testing/test_ab_testing.php
 * Exit 0 = all pass, 1 = any fail.
 *
 * Tests:
 *   1. createExperiment + idempotency
 *   2. getVariant consistency (same user -> same variant)
 *   3. getVariant traffic split (1000 users -> ~50/50)
 *   4. trackEvent inserts + getStats computes
 *   5. chiSquare sanity (independent vs identical distributions)
 *   6. endExperiment + deleteExperiment lifecycle
 */

define('APP_ROOT', dirname(__DIR__));
require_once __DIR__ . '/../app/Core/Autoloader.php';

use App\Services\Experimentation\ExperimentService;

$pass = 0;
$fail = 0;
$errors = [];

function ok($name, $cond, $detail = '') {
    global $pass, $fail, $errors;
    if ($cond) {
        $pass++;
        echo "  PASS  $name\n";
    } else {
        $fail++;
        $errors[] = "$name  $detail";
        echo "  FAIL  $name  $detail\n";
    }
}

function section($title) {
    echo "\n=== $title ===\n";
}

$svc = new ExperimentService();

// Cleanup: drop any leftovers from previous runs
$db = \App\Core\Database\Database::getInstance();
$db->execute("DELETE FROM ab_events WHERE experiment_id IN (SELECT id FROM ab_experiments WHERE name LIKE 'test\\_%' ESCAPE '\\\\')");
$db->execute("DELETE FROM ab_experiments WHERE name LIKE 'test\\_%' ESCAPE '\\\\'");

// ----------------------------------------------------------------------------
section('1. createExperiment + idempotency');

$id1 = $svc->createExperiment('test_basic', [
    ['name' => 'control',   'weight' => 50],
    ['name' => 'treatment', 'weight' => 50],
], 100, 'basic test');
ok('createExperiment returns positive id', $id1 > 0, "got $id1");

$row = $db->fetchOne("SELECT * FROM ab_experiments WHERE id = ?", [$id1]);
ok('experiment row stored',     $row !== null);
ok('name matches',              ($row['name'] ?? null) === 'test_basic');
ok('status starts as running',  ($row['status'] ?? '') === 'running');
ok('traffic_allocation = 100',  (int)($row['traffic_allocation'] ?? 0) === 100);
$variants = is_string($row['variants'] ?? null) ? json_decode($row['variants'], true) : [];
ok('variants json stored',      is_array($variants) && count($variants) === 2);

// Idempotency: try creating the same name again
try {
    $svc->createExperiment('test_basic', [['name'=>'a','weight'=>1],['name'=>'b','weight'=>1]]);
    ok('duplicate name throws', false, 'expected exception');
} catch (Throwable $e) {
    ok('duplicate name throws', true);
}

// ----------------------------------------------------------------------------
section('2. getVariant consistency');

$v1 = $svc->getVariant('test_basic', 12345);
$v2 = $svc->getVariant('test_basic', 12345);
ok('variant is not null',     $v1 !== null);
ok('variant is deterministic', $v1 === $v2, "got '$v1' then '$v2'");
ok('variant is one of two',   in_array($v1, ['control', 'treatment'], true));

// Test the "outside traffic_allocation" branch
$id2 = $svc->createExperiment('test_lowtraffic', [
    ['name' => 'a', 'weight' => 50],
    ['name' => 'b', 'weight' => 50],
], 1, 'low traffic test'); // only 1% bucketed in

// Picked a user that the hash bins OUTSIDE 1% window
$outside = null;
for ($u = 1000; $u < 5000; $u++) {
    if ($svc->getVariant('test_lowtraffic', $u) === null) {
        $outside = $u;
        break;
    }
}
ok('low-traffic experiment excludes most users', $outside !== null, "could not find excluded user");

// ----------------------------------------------------------------------------
section('3. traffic split (1000 users, 50/50 weights)');

$id3 = $svc->createExperiment('test_split', [
    ['name' => 'A', 'weight' => 50],
    ['name' => 'B', 'weight' => 50],
], 100);
$counts = ['A' => 0, 'B' => 0];
for ($u = 100000; $u < 101000; $u++) {
    $v = $svc->getVariant('test_split', $u);
    if (isset($counts[$v])) $counts[$v]++;
}
$total = $counts['A'] + $counts['B'];
$ratio = $total > 0 ? $counts['A'] / $total : 0;
echo "  info  A={$counts['A']}  B={$counts['B']}  total={$total}  A_ratio=" . round($ratio, 3) . "\n";
ok('total users ~ 1000', abs($total - 1000) < 5, "got $total");
ok('split is roughly even (45-55%)', $ratio > 0.45 && $ratio < 0.55, "A_ratio=$ratio");

// ----------------------------------------------------------------------------
section('4. trackEvent + getStats');

$expName = 'test_track';
$id4 = $svc->createExperiment($expName, [
    ['name' => 'control',   'weight' => 50],
    ['name' => 'treatment', 'weight' => 50],
], 100, 'tracking test');

$tracker = function() use ($svc, $expName) {
    // Simulate 200 control users and 200 treatment users; half of each convert
    for ($u = 1; $u <= 200; $u++) {
        $v = $svc->getVariant($expName, $u);
        if ($v === null) continue;
        $svc->trackEvent($expName, $v, 'view', $u);
        if ($u % 2 === 0) $svc->trackEvent($expName, $v, 'conversion', $u);
    }
};
$tracker();

$stats = $svc->getStats($expName);
ok('getStats returns array', is_array($stats));
ok('getStats has results', isset($stats['results']) && is_array($stats['results']));
ok('getStats has totals',  isset($stats['totals']));
ok('both variants in results', isset($stats['results']['control']) && isset($stats['results']['treatment']));

$c = $stats['results']['control'];
$t = $stats['results']['treatment'];
ok('control has users',     $c['users'] >= 1, json_encode($c));
ok('treatment has users',   $t['users'] >= 1, json_encode($t));
ok('control has conversions',   $c['conversions'] >= 1);
ok('treatment has conversions', $t['conversions'] >= 1);

// totals
ok('totals.users == sum of variant users',
    (int)$stats['totals']['users'] === ((int)$c['users'] + (int)$t['users']));

// ----------------------------------------------------------------------------
section('5. chiSquare statistical test');

$identical = $svc->chiSquare([
    ['users' => 100, 'conversions' => 50],
    ['users' => 100, 'conversions' => 50],
]);
ok('identical distributions: chi2 ~ 0',     $identical['stat'] < 0.01,  "stat=" . $identical['stat']);
ok('identical distributions: p ~ 1',       $identical['p_value'] > 0.9, "p=" . $identical['p_value']);
ok('identical distributions: not significant', empty($identical['significant']));

$different = $svc->chiSquare([
    ['users' => 100, 'conversions' => 10],
    ['users' => 100, 'conversions' => 90],
]);
ok('very different distributions: chi2 high',   $different['stat'] > 50,   "stat=" . $different['stat']);
ok('very different distributions: p < 0.001',   $different['p_value'] < 0.001, "p=" . $different['p_value']);
ok('very different distributions: significant', !empty($different['significant']));

// ----------------------------------------------------------------------------
section('6. endExperiment + deleteExperiment lifecycle');

$id6 = $svc->createExperiment('test_lifecycle', [
    ['name' => 'a', 'weight' => 50],
    ['name' => 'b', 'weight' => 50],
], 100, 'lifecycle');
$svc->trackEvent('test_lifecycle', 'a', 'view', 1);
$svc->endExperiment('test_lifecycle', 'a');
$end = $db->fetchOne("SELECT status, winner, ended_at FROM ab_experiments WHERE id = ?", [$id6]);
ok('status=ended', ($end['status'] ?? '') === 'ended');
ok('winner stored', ($end['winner'] ?? '') === 'a');
ok('ended_at set',  !empty($end['ended_at']));

// Variant lookup for ended experiment returns null (no longer bucketed)
$endedVariant = $svc->getVariant('test_lifecycle', 99999);
ok('ended experiment no longer assigns', $endedVariant === null);

$deleted = $svc->deleteExperiment($id6);
ok('deleteExperiment returns true', $deleted);
$check = $db->fetchOne("SELECT id FROM ab_experiments WHERE id = ?", [$id6]);
ok('experiment row removed', $check === false);

$evCheck = $db->fetchOne("SELECT id FROM ab_events WHERE experiment_id = ?", [$id6]);
ok('events cascade-removed', $evCheck === false);

// ----------------------------------------------------------------------------
// Final cleanup
$db->execute("DELETE FROM ab_events WHERE experiment_id IN (SELECT id FROM ab_experiments WHERE name LIKE 'test\\_%' ESCAPE '\\\\')");
$db->execute("DELETE FROM ab_experiments WHERE name LIKE 'test\\_%' ESCAPE '\\\\'");

// ----------------------------------------------------------------------------
section('Summary');
echo "PASS: $pass    FAIL: $fail\n";
if ($fail > 0) {
    echo "\nFailures:\n";
    foreach ($errors as $e) echo "  - $e\n";
    exit(1);
}
exit(0);
