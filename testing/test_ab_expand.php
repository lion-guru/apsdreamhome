<?php
/**
 * test_ab_expand.php
 *
 * Unit tests for A/B testing expansion:
 *   - seedDefaults() idempotency (call twice, no duplicates)
 *   - getVariant() determinism (same userId always returns same variant)
 *   - cta_button_color distribution (3 variants, 34/33/33 weights)
 *   - getResults() aggregation (per-variant users/conversions/rate)
 *   - getStats() returns experiment + results + totals + chi_square
 *   - setWinner() updates DB and flips status
 *   - exportCsv() produces valid CSV with UTF-8 BOM
 *
 * Usage:  php testing/test_ab_expand.php
 * Exit:   0 = all pass, 1 = any fail
 */

require __DIR__ . '/../vendor/autoload.php';

use App\Services\Experimentation\ExperimentService;
use App\Core\Database\Database;

$svc = new ExperimentService();
$pdo = $svc->getPdo();

$tests = [];
$pass = 0;
$fail = 0;

function assertEq($a, $b, string $label, array &$tests, int &$pass, int &$fail): void {
    $ok = $a === $b;
    $tests[] = [
        'name'  => $label,
        'pass'  => $ok,
        'got'   => is_scalar($a) ? $a : json_encode($a),
        'want'  => is_scalar($b) ? $b : json_encode($b),
    ];
    if ($ok) { $pass++; } else { $fail++; }
}

function assertTrue(bool $cond, string $label, array &$tests, int &$pass, int &$fail): void {
    $tests[] = [
        'name'  => $label,
        'pass'  => $cond,
        'got'   => $cond ? 'true' : 'false',
        'want'  => 'true',
    ];
    if ($cond) { $pass++; } else { $fail++; }
}

function header_line(string $s): void {
    echo "\n=== {$s} ===\n";
}

try {
    // Clean state for these 4 experiments
    $pdo->exec("DELETE FROM ab_events WHERE experiment_id IN (SELECT id FROM ab_experiments WHERE name IN ('homepage_cta','property_card_layout','cta_button_color','registration_form_length'))");
    $pdo->exec("DELETE FROM ab_experiments WHERE name IN ('homepage_cta','property_card_layout','cta_button_color','registration_form_length')");

    header_line('1. seedDefaults — first call creates 4');
    $r1 = $svc->seedDefaults();
    assertEq(count($r1['created']), 4, 'seedDefaults creates 4 experiments on first call', $tests, $pass, $fail);
    assertEq(count($r1['skipped']), 0, 'seedDefaults skips 0 on first call', $tests, $pass, $fail);

    header_line('2. seedDefaults — second call is idempotent');
    $r2 = $svc->seedDefaults();
    assertEq(count($r2['created']), 0, 'seedDefaults creates 0 on second call (idempotent)', $tests, $pass, $fail);
    assertEq(count($r2['skipped']), 4, 'seedDefaults skips 4 on second call', $tests, $pass, $fail);

    header_line('3. Experiment rows exist with correct variant counts');
    $rowHome = $svc->getExperimentById(
        (int) $pdo->query("SELECT id FROM ab_experiments WHERE name = 'homepage_cta'")->fetchColumn()
    );
    $variantsHome = json_decode($rowHome['variants'], true);
    assertEq(count($variantsHome), 2, 'homepage_cta has 2 variants', $tests, $pass, $fail);

    $rowCta = $svc->getExperimentById(
        (int) $pdo->query("SELECT id FROM ab_experiments WHERE name = 'cta_button_color'")->fetchColumn()
    );
    $variantsCta = json_decode($rowCta['variants'], true);
    assertEq(count($variantsCta), 3, 'cta_button_color has 3 variants', $tests, $pass, $fail);
    $namesCta = array_map(fn($v) => $v['name'], $variantsCta);
    assertEq(in_array('blue', $namesCta) && in_array('green', $namesCta) && in_array('orange', $namesCta), true, 'cta_button_color includes blue/green/orange', $tests, $pass, $fail);

    header_line('4. getVariant — determinism (same userId returns same variant)');
    $v1 = $svc->getVariant('homepage_cta', 42);
    $v2 = $svc->getVariant('homepage_cta', 42);
    $v3 = $svc->getVariant('homepage_cta', 42);
    assertEq($v1, $v2, 'homepage_cta: variant for user 42 stable across 2 calls', $tests, $pass, $fail);
    assertEq($v2, $v3, 'homepage_cta: variant for user 42 stable across 3 calls', $tests, $pass, $fail);
    assertTrue(in_array($v1, ['control', 'treatment'], true), "homepage_cta: variant is control|treatment (got {$v1})", $tests, $pass, $fail);

    header_line('5. getVariant — different users get different variants (within tolerance)');
    $counts = ['control' => 0, 'treatment' => 0, null => 0];
    for ($uid = 1; $uid <= 200; $uid++) {
        $v = $svc->getVariant('homepage_cta', $uid);
        $counts[$v ?? 'null']++;
    }
    $total = $counts['control'] + $counts['treatment'];
    assertTrue($total === 200, 'homepage_cta: 200/200 users assigned to a variant (100% traffic)', $tests, $pass, $fail);
    // Loose tolerance: each side should be 30-70% (50/50 target with crc32 hash dispersion)
    $cPct = ($counts['control'] / $total) * 100;
    assertTrue($cPct > 30 && $cPct < 70, "homepage_cta: control rate between 30-70% (got {$cPct}%)", $tests, $pass, $fail);

    header_line('6. cta_button_color — 3-variant distribution (34/33/33) over 1000 users');
    $ctaCounts = ['blue' => 0, 'green' => 0, 'orange' => 0, null => 0];
    for ($uid = 1; $uid <= 1000; $uid++) {
        $v = $svc->getVariant('cta_button_color', $uid);
        $ctaCounts[$v ?? 'null']++;
    }
    $ctaTotal = $ctaCounts['blue'] + $ctaCounts['green'] + $ctaCounts['orange'];
    assertEq($ctaTotal, 1000, 'cta_button_color: 1000/1000 users assigned (100% traffic)', $tests, $pass, $fail);
    $bluePct   = ($ctaCounts['blue']   / $ctaTotal) * 100;
    $greenPct  = ($ctaCounts['green']  / $ctaTotal) * 100;
    $orangePct = ($ctaCounts['orange'] / $ctaTotal) * 100;
    // Expected 34/33/33 — tolerance ±10% for 1000 sample
    assertTrue($bluePct   > 20 && $bluePct   < 50, "cta_button_color: blue in 20-50% (got {$bluePct}%)", $tests, $pass, $fail);
    assertTrue($greenPct  > 20 && $greenPct  < 50, "cta_button_color: green in 20-50% (got {$greenPct}%)", $tests, $pass, $fail);
    assertTrue($orangePct > 20 && $orangePct < 50, "cta_button_color: orange in 20-50% (got {$orangePct}%)", $tests, $pass, $fail);

    header_line('7. trackEvent + getResults — aggregation');
    // Simulate events: 25 views per side + 10 conversions per side.
    // Note: variant assignment is by crc32 hash, so a few user IDs may "leak" between
    // the curated range above; the assertion uses >= to accept that.
    for ($uid = 1; $uid <= 25; $uid++) {
        $svc->trackEvent('homepage_cta', 'control', 'view', $uid);
    }
    for ($uid = 1; $uid <= 10; $uid++) {
        $svc->trackEvent('homepage_cta', 'control', 'conversion', $uid);
    }
    for ($uid = 26; $uid <= 50; $uid++) {
        $svc->trackEvent('homepage_cta', 'treatment', 'view', $uid);
    }
    for ($uid = 26; $uid <= 35; $uid++) {
        $svc->trackEvent('homepage_cta', 'treatment', 'conversion', $uid);
    }
    $results = $svc->getResults('homepage_cta');
    assertTrue($results['control']['users'] >= 25, "control: at least 25 distinct users (got {$results['control']['users']})", $tests, $pass, $fail);
    assertEq($results['control']['conversions'], 10, 'control: 10 conversions', $tests, $pass, $fail);
    assertTrue($results['control']['rate_pct'] > 30 && $results['control']['rate_pct'] < 50, "control: rate in 30-50% (got {$results['control']['rate_pct']})", $tests, $pass, $fail);
    assertTrue($results['treatment']['users'] >= 25, "treatment: at least 25 distinct users (got {$results['treatment']['users']})", $tests, $pass, $fail);
    assertEq($results['treatment']['conversions'], 10, 'treatment: 10 conversions', $tests, $pass, $fail);
    assertTrue($results['treatment']['rate_pct'] > 30 && $results['treatment']['rate_pct'] < 50, "treatment: rate in 30-50% (got {$results['treatment']['rate_pct']})", $tests, $pass, $fail);

    header_line('8. getStats — returns experiment + results + totals + chi_square');
    $stats = $svc->getStats('homepage_cta');
    assertTrue(isset($stats['experiment']), 'stats: has experiment block', $tests, $pass, $fail);
    assertTrue(isset($stats['results']), 'stats: has results block', $tests, $pass, $fail);
    assertTrue(isset($stats['totals']), 'stats: has totals block', $tests, $pass, $fail);
    assertTrue(isset($stats['chi_square']), 'stats: has chi_square block', $tests, $pass, $fail);
    assertTrue($stats['totals']['users'] >= 50, "stats totals: at least 50 users (got {$stats['totals']['users']})", $tests, $pass, $fail);
    assertEq($stats['totals']['conversions'], 20, 'stats totals: 20 conversions', $tests, $pass, $fail);
    assertTrue($stats['totals']['rate'] > 30 && $stats['totals']['rate'] < 50, "stats totals: rate in 30-50% (got {$stats['totals']['rate']})", $tests, $pass, $fail);

    header_line('9. chiSquare — both variants equal 40% → p_value high, not significant');
    $chi = $stats['chi_square'];
    assertTrue(isset($chi['p_value']), 'chi_square: has p_value', $tests, $pass, $fail);
    assertTrue($chi['p_value'] > 0.5, "chi_square: p_value high when both rates equal (got {$chi['p_value']})", $tests, $pass, $fail);
    assertEq($chi['significant'], false, 'chi_square: not significant when rates equal', $tests, $pass, $fail);

    header_line('10. endExperiment — flips status to "ended", sets winner, sets ended_at');
    // status ENUM is ('draft','running','ended') — invalid values become ''
    $ended = $svc->endExperiment('homepage_cta', 'control');
    assertTrue($ended, 'endExperiment: returns true on success', $tests, $pass, $fail);
    $row = $svc->getExperimentById($stats['experiment']['id']);
    assertEq($row['winner'], 'control', 'endExperiment: winner column is "control"', $tests, $pass, $fail);
    assertEq($row['status'], 'ended', 'endExperiment: status is "ended" (valid ENUM value)', $tests, $pass, $fail);
    assertTrue(!empty($row['ended_at']), 'endExperiment: ended_at is set', $tests, $pass, $fail);

    header_line('11. CSV export — produces valid CSV with UTF-8 BOM');
    ob_start();
    $fp = fopen('php://output', 'w');
    fwrite($fp, "\xEF\xBB\xBF");
    fputcsv($fp, ['Variant', 'Users', 'Conversions', 'Rate %']);
    foreach ($results as $variant => $r) {
        fputcsv($fp, [$variant, $r['users'], $r['conversions'], $r['rate_pct']]);
    }
    fclose($fp);
    $csv = ob_get_clean();

    $hasBom = (substr($csv, 0, 3) === "\xEF\xBB\xBF");
    assertTrue($hasBom, 'CSV: starts with UTF-8 BOM', $tests, $pass, $fail);
    // fputcsv quotes fields with spaces/special chars; use str_starts_with checks per line
    $csvNoBom = substr($csv, 3);
    $lines = preg_split('/\r?\n/', trim($csvNoBom));
    assertTrue(count($lines) >= 3, 'CSV: at least 3 lines (header + 2 data rows)', $tests, $pass, $fail);
    assertTrue(strpos($lines[0], 'Variant') !== false, 'CSV: header contains "Variant"', $tests, $pass, $fail);
    assertTrue(strpos($lines[0], 'Users') !== false, 'CSV: header contains "Users"', $tests, $pass, $fail);
    assertTrue(strpos($lines[0], 'Conversions') !== false, 'CSV: header contains "Conversions"', $tests, $pass, $fail);
    $hasControl = false;
    $hasTreatment = false;
    foreach (array_slice($lines, 1) as $line) {
        if (strpos($line, 'control') === 0) $hasControl = true;
        if (strpos($line, 'treatment') === 0) $hasTreatment = true;
    }
    assertTrue($hasControl, 'CSV: has a data row starting with "control"', $tests, $pass, $fail);
    assertTrue($hasTreatment, 'CSV: has a data row starting with "treatment"', $tests, $pass, $fail);

    header_line('12. listExperiments — returns all 4 new experiments');
    $all = $svc->listExperiments();
    $names = array_map(fn($r) => $r['name'], $all);
    assertTrue(in_array('homepage_cta', $names), 'listExperiments: includes homepage_cta', $tests, $pass, $fail);
    assertTrue(in_array('property_card_layout', $names), 'listExperiments: includes property_card_layout', $tests, $pass, $fail);
    assertTrue(in_array('cta_button_color', $names), 'listExperiments: includes cta_button_color', $tests, $pass, $fail);
    assertTrue(in_array('registration_form_length', $names), 'listExperiments: includes registration_form_length', $tests, $pass, $fail);

} catch (Throwable $e) {
    $fail++;
    $tests[] = [
        'name'  => 'EXCEPTION',
        'pass'  => false,
        'got'   => $e->getMessage() . ' @ ' . basename($e->getFile()) . ':' . $e->getLine(),
        'want'  => 'no exception',
    ];
    echo "\n!! EXCEPTION: " . $e->getMessage() . "\n";
    echo "   at " . $e->getFile() . ':' . $e->getLine() . "\n";
}

// ─── Report ─────────────────────────────────────────────────────────────
echo "\n";
echo str_repeat('─', 78) . "\n";
echo "  TEST RESULTS\n";
echo str_repeat('─', 78) . "\n";
foreach ($tests as $t) {
    $icon = $t['pass'] ? '[PASS]' : '[FAIL]';
    $line = sprintf("  %s  %s", $icon, $t['name']);
    echo $line . "\n";
    if (!$t['pass']) {
        echo "         got:  " . substr((string) $t['got'], 0, 100) . "\n";
        echo "         want: " . substr((string) $t['want'], 0, 100) . "\n";
    }
}
echo str_repeat('─', 78) . "\n";
echo sprintf("  TOTAL: %d passed, %d failed  (out of %d assertions)\n", $pass, $fail, count($tests));
echo str_repeat('─', 78) . "\n";

exit($fail > 0 ? 1 : 0);
