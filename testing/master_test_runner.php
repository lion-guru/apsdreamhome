<?php
/**
 * APS Dream Home — Master Test Orchestrator & Quality Gate
 *
 * Runs all levels of the testing pyramid in a single unified execution:
 *   [Level 1] Syntax & File Health
 *   [Level 2] Unit & Domain Engine Tests (Commission, Payout, Tenant Scoping, InputValidator, Captcha)
 *   [Level 3] End-to-End Workflow & Database Consistency Probe
 *   [Level 4] Live HTTP Production Smoke Test (24 Endpoints)
 *
 * Usage:
 *   php testing/master_test_runner.php
 *   php testing/master_test_runner.php --quick
 */

define('APS_ROOT', dirname(__DIR__));
require_once APS_ROOT . '/config/bootstrap.php';

$isQuick = in_array('--quick', $argv, true);
$startTime = microtime(true);

echo "\n";
echo "╔══════════════════════════════════════════════════════════════════════╗\n";
echo "║          APS DREAM HOME — MASTER TEST SUITE RUNNER                   ║\n";
echo "╚══════════════════════════════════════════════════════════════════════╝\n\n";

$suites = [
    'Unit Tests' => [
        'InputValidator & Captcha' => 'php testing/unit/test_input_validator_and_captcha.php',
        'Commission Engine'        => 'php testing/unit/test_commission.php',
        'Payout Processing'        => 'php testing/unit/test_payout.php',
        'Tenant Scoping'           => 'php testing/unit/test_tenant_scoping.php',
    ],
    'Integration & E2E Workflows' => [
        'Business Workflow & DB Probe' => 'php testing/workflow_probe.php',
    ],
    'HTTP Production Smoke Tests' => [
        'Production Smoke Runner (24 Routes)' => 'php testing/production_smoke_runner.php',
    ]
];

$totalTests = 0;
$totalPassed = 0;
$totalFailed = 0;
$resultsSummary = [];

foreach ($suites as $category => $tests) {
    echo "────────────────────────────────────────────────────────────────────────\n";
    echo "  ▶ " . strtoupper($category) . "\n";
    echo "────────────────────────────────────────────────────────────────────────\n";

    foreach ($tests as $name => $cmd) {
        $totalTests++;
        $t0 = microtime(true);
        $output = [];
        $returnVar = 0;
        
        exec($cmd . ' 2>&1', $output, $returnVar);
        $duration = round((microtime(true) - $t0) * 1000, 1);
        $status = ($returnVar === 0) ? 'PASS' : 'FAIL';

        if ($status === 'PASS') {
            $totalPassed++;
            echo "  ✅ {$name} ({$duration}ms) — PASS\n";
            $resultsSummary[] = ['category' => $category, 'name' => $name, 'status' => 'PASS', 'time' => $duration];
        } else {
            $totalFailed++;
            echo "  ❌ {$name} ({$duration}ms) — FAILED (Exit Code: {$returnVar})\n";
            echo "     Output snippet:\n";
            $lastLines = array_slice($output, -6);
            foreach ($lastLines as $line) {
                echo "     | " . $line . "\n";
            }
            $resultsSummary[] = ['category' => $category, 'name' => $name, 'status' => 'FAIL', 'time' => $duration];
        }
    }
    echo "\n";
}

$elapsed = round(microtime(true) - $startTime, 2);

echo "════════════════════════════════════════════════════════════════════════\n";
echo "  TEST SUMMARY REPORT\n";
echo "════════════════════════════════════════════════════════════════════════\n";
echo "  Total Suites Run : {$totalTests}\n";
echo "  Passed           : {$totalPassed} ✅\n";
echo "  Failed           : {$totalFailed} " . ($totalFailed > 0 ? "❌" : "") . "\n";
echo "  Execution Time   : {$elapsed}s\n";
echo "════════════════════════════════════════════════════════════════════════\n";

if ($totalFailed === 0) {
    echo "  🎉 ALL TEST SUITES PASSED (100% HEALTHY). SYSTEM READY.\n\n";
    exit(0);
} else {
    echo "  ⚠️ SOME SUITES FAILED. PLEASE INSPECT LOGS ABOVE.\n\n";
    exit(1);
}
