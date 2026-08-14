<?php
/**
 * CI Test Runner
 *
 * Discovers and runs every `testing/test_*.php` script, then writes a
 * JUnit XML report at `tests/reports/junit.xml` so GitHub Actions can
 * render the failures in the Checks tab.
 *
 * Why a custom runner (and not bare PHPUnit):
 *   - The project has 39+ standalone test scripts in testing/ that
 *     exit-code themselves (0 = pass, 1 = fail). PHPUnit 9 would require
 *     converting every one of them to a TestCase class.
 *   - This runner is the actual canonical CI entry point that the
 *     GitHub Actions workflows call.
 *   - It still emits JUnit XML so the Actions UI gets per-test feedback.
 *
 * Usage:
 *   php tests/run_all_tests.php [--suite=unit|integration|load|all] [--no-junit]
 *
 * Exit code 0 = all suites green, 1 = at least one failed.
 */

declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));

$opts = parseArgs($argv);
$suite = $opts['suite'] ?? 'all';
$emitJunit = !($opts['no-junit'] ?? false);

$root = APP_ROOT;
$reportsDir = $root . '/tests/reports';
if (!is_dir($reportsDir)) {
    @mkdir($reportsDir, 0775, true);
}
$logFile = $reportsDir . '/run.log';
@unlink($logFile);

$banner = static function (string $msg) use ($logFile): void {
    echo $msg . PHP_EOL;
    @file_put_contents($logFile, $msg . PHP_EOL, FILE_APPEND);
};

$banner('');
$banner('â•”â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•—');
$banner('â•‘       APS DREAM HOME - CI TEST RUNNER                       â•‘');
$banner('â•šâ•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�');
$banner('');
$banner('Started:  ' . date('c'));
$banner('Suite:    ' . $suite);
$banner('PHP:      ' . PHP_VERSION);
$banner('DB host:  ' . (getenv('DB_HOST') ?: '127.0.0.1'));
$banner('');

// Discover tests by suite
$suites = [
    'unit' => collectScripts($root . '/testing', ['test_*.php']),
    'integration' => collectScripts($root . '/tests/integration', ['*.php']),
    'load' => collectScripts($root . '/testing/load', ['*.php']),
];

$toRun = [];
if ($suite === 'all') {
    foreach ($suites as $name => $list) {
        if ($name === 'load') continue;
        $toRun[$name] = $list;
    }
} else {
    $toRun[$suite] = $suites[$suite] ?? [];
}

$totalTests = 0;
$totalPassed = 0;
$totalFailed = 0;
$totalSkipped = 0;
$totalDuration = 0.0;
$junitSuites = [];
$failedScripts = [];

foreach ($toRun as $suiteName => $scripts) {
    $banner("â”€â”€ Suite: $suiteName (" . count($scripts) . " script" . (count($scripts) === 1 ? '' : 's') . ") â”€â”€");
    $suiteStart = microtime(true);
    $suiteStartCount = $totalTests;
    $suitePass = 0;
    $suiteFail = 0;
    $suiteSkipped = 0;
    $suiteCases = [];

    foreach ($scripts as $script) {
        $base = basename($script);
        $rel = ltrim(str_replace($root, '', $script), '/');
        $scriptStart = microtime(true);

        $banner("  â–¸ $base");

        $exit = runScript($script, $banner);
        $scriptElapsed = round(microtime(true) - $scriptStart, 3);

        if ($exit === 0) {
            $banner("    âœ“ PASS  (" . $scriptElapsed . 's)');
            $suitePass++;
        } elseif ($exit === 2) {
            $banner("    âŠ˜ SKIP  (test opted out)");
            $suiteSkipped++;
        } else {
            $banner("    âœ— FAIL  (exit $exit, " . $scriptElapsed . 's)');
            $suiteFail++;
            $failedScripts[] = $rel;
        }

        $suiteCases[] = [
            'name' => $base,
            'classname' => 'APS.' . $suiteName,
            'file' => $rel,
            'time' => $scriptElapsed,
            'failure' => $exit !== 0 && $exit !== 2 ? "exit code $exit" : null,
            'skipped' => $exit === 2,
        ];
    }

    $suiteElapsed = round(microtime(true) - $suiteStart, 3);
    $junitSuites[] = [
        'name' => $suiteName,
        'time' => $suiteElapsed,
        'tests' => $suitePass + $suiteFail + $suiteSkipped,
        'failures' => $suiteFail,
        'skipped' => $suiteSkipped,
        'cases' => $suiteCases,
    ];

    $totalTests += $suitePass + $suiteFail + $suiteSkipped;
    $totalPassed += $suitePass;
    $totalFailed += $suiteFail;
    $totalSkipped += $suiteSkipped;
    $totalDuration += $suiteElapsed;

    $banner(sprintf(
        "  Suite %s: %d passed, %d failed, %d skipped (%ss)",
        $suiteName,
        $suitePass,
        $suiteFail,
        $suiteSkipped,
        $suiteElapsed
    ));
    $banner('');
}

$banner('â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�');
$banner(' TOTAL');
$banner('â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�');
$banner(" Tests run:  $totalTests");
$banner(" Passed:     $totalPassed");
$banner(" Failed:     $totalFailed");
$banner(" Skipped:    $totalSkipped");
$banner(" Duration:   " . round($totalDuration, 3) . 's');
$banner('â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�');

if ($emitJunit) {
    $xmlPath = $reportsDir . '/junit.xml';
    if (writeJunit($xmlPath, $junitSuites, $totalTests, $totalFailed, $totalSkipped, $totalDuration)) {
        $banner("JUnit XML written to $xmlPath");
    } else {
        $banner('! failed to write JUnit XML');
    }
}

$banner('');
$banner('Finished: ' . date('c'));

if ($totalFailed > 0) {
    $banner('RESULT: FAILED');
    foreach ($failedScripts as $f) $banner('  - ' . $f);
    exit(1);
}
$banner('RESULT: PASSED');
exit(0);

// =========================================================================
// Helpers
// =========================================================================

function parseArgs(array $argv): array
{
    $opts = [];
    foreach (array_slice($argv, 1) as $arg) {
        if (str_starts_with($arg, '--')) {
            $eq = strpos($arg, '=');
            if ($eq !== false) {
                $opts[substr($arg, 2, $eq - 2)] = substr($arg, $eq + 1);
            } else {
                $opts[substr($arg, 2)] = true;
            }
        }
    }
    return $opts;
}

function collectScripts(string $dir, array $patterns): array
{
    $out = [];
    if (!is_dir($dir)) return $out;
    foreach ($patterns as $p) {
        foreach (glob($dir . '/' . $p) ?: [] as $f) {
            if (is_file($f) && pathinfo($f, PATHINFO_EXTENSION) === 'php') {
                $out[] = $f;
            }
        }
    }
    sort($out);
    return $out;
}

function runScript(string $script, callable $banner): int
{
    $cmd = escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg($script) . ' 2>&1';
    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];
    $proc = @proc_open($cmd, $descriptors, $pipes, dirname($script));
    if (!is_resource($proc)) {
        $banner('    ! failed to start: ' . $script);
        return 1;
    }
    fclose($pipes[0]);
    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);
    $output = '';
    $deadline = microtime(true) + 300;
    while (true) {
        $status = proc_get_status($proc);
        $output .= (stream_get_contents($pipes[1]) ?: '');
        $output .= (stream_get_contents($pipes[2]) ?: '');
        if (!$status['running']) break;
        if (microtime(true) > $deadline) {
            proc_terminate($proc, 9);
            $output .= "\n[CI: killed after timeout]";
            break;
        }
        usleep(50_000);
    }
    foreach (['1', '2'] as $idx) {
        $output .= stream_get_contents($pipes[(int)$idx]) ?: '';
        fclose($pipes[(int)$idx]);
    }
    $exit = proc_close($proc);

    $lines = explode("\n", trim($output));
    $maxLines = 12;
    if (count($lines) > $maxLines) {
        $shown = array_slice($lines, 0, $maxLines);
        $shown[] = '    ... (' . (count($lines) - $maxLines) . ' more lines)';
        $lines = $shown;
    }
    foreach ($lines as $line) {
        $banner('    | ' . $line);
    }
    return (int)$exit;
}

function writeJunit(
    string $path,
    array $suites,
    int $totalTests,
    int $totalFailures,
    int $totalSkipped,
    float $totalTime
): bool {
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->formatOutput = true;
    $root = $dom->createElement('testsuites');
    $root->setAttribute('name', 'APS Dream Home');
    $root->setAttribute('tests', (string)$totalTests);
    $root->setAttribute('failures', (string)$totalFailures);
    $root->setAttribute('skipped', (string)$totalSkipped);
    $root->setAttribute('time', (string)round($totalTime, 3));
    $dom->appendChild($root);

    foreach ($suites as $suite) {
        $suiteEl = $dom->createElement('testsuite');
        $suiteEl->setAttribute('name', $suite['name']);
        $suiteEl->setAttribute('tests', (string)$suite['tests']);
        $suiteEl->setAttribute('failures', (string)$suite['failures']);
        $suiteEl->setAttribute('skipped', (string)$suite['skipped']);
        $suiteEl->setAttribute('time', (string)round((float)$suite['time'], 3));
        $root->appendChild($suiteEl);
        foreach ($suite['cases'] as $case) {
            $caseEl = $dom->createElement('testcase');
            $caseEl->setAttribute('name', $case['name']);
            $caseEl->setAttribute('classname', $case['classname']);
            $caseEl->setAttribute('file', $case['file']);
            $caseEl->setAttribute('time', (string)$case['time']);
            if (!empty($case['failure'])) {
                $failure = $dom->createElement('failure');
                $failure->setAttribute('message', $case['failure']);
                $failure->appendChild($dom->createTextNode($case['failure']));
                $caseEl->appendChild($failure);
            } elseif (!empty($case['skipped'])) {
                $skipped = $dom->createElement('skipped');
                $caseEl->appendChild($skipped);
            }
            $suiteEl->appendChild($caseEl);
        }
    }
    return $dom->save($path) !== false;
}?>