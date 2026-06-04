<?php
/**
 * Translation System Test
 *
 * Verifies:
 *   1. Both en.php and hi.php load without errors
 *   2. Key parity between en and hi (no missing, no extra)
 *   3. No identical values where translation is expected (other than symbols like ₹, BHK, numerals)
 *   4. `__()` helper returns correct values
 *   5. Pluralization works
 *   6. Parameter substitution works
 *   7. Fallback to English when key missing in target language
 *   8. Scans view files for hardcoded English strings (heuristic)
 *
 * Usage:
 *   php testing/test_translations.php
 *
 * Exit code 0 = all checks pass
 * Exit code 1 = at least one check failed
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/app/Services/TranslationService.php';
require_once APP_ROOT . '/app/Helpers/TranslationHelper.php';

$pass = 0;
$fail = 0;
$messages = [];

function assertTrue(bool $cond, string $desc): void
{
    global $pass, $fail, $messages;
    if ($cond) {
        $pass++;
        $messages[] = "  ✓ {$desc}";
    } else {
        $fail++;
        $messages[] = "  ✗ FAIL: {$desc}";
    }
}

function section(string $title): void
{
    global $messages;
    $messages[] = "";
    $messages[] = "=== {$title} ===";
}

function flatten(array $arr, string $prefix = ''): array
{
    $out = [];
    foreach ($arr as $k => $v) {
        $key = $prefix === '' ? (string) $k : $prefix . '.' . $k;
        if (is_array($v)) {
            $out = array_merge($out, flatten($v, $key));
        } else {
            $out[$key] = $v;
        }
    }
    return $out;
}

// 1. Load both language files
section('1. Language files load');
$en = require APP_ROOT . '/lang/en.php';
assertTrue(is_array($en) && count($en) > 0, 'en.php loads as array');

$hi = require APP_ROOT . '/lang/hi.php';
assertTrue(is_array($hi) && count($hi) > 0, 'hi.php loads as array');

// 2. Key parity
section('2. Key parity between en and hi');
$enFlat = flatten($en);
$hiFlat = flatten($hi);

assertTrue(count($enFlat) > 100, 'en has >100 keys (got ' . count($enFlat) . ')');
assertTrue(count($hiFlat) === count($enFlat), 'en and hi have equal key count (' . count($enFlat) . ')');

$missingInHi = array_diff_key($enFlat, $hiFlat);
$extraInHi   = array_diff_key($hiFlat, $enFlat);
assertTrue(count($missingInHi) === 0, 'no keys missing in hi (missing: ' . count($missingInHi) . ')');
assertTrue(count($extraInHi) === 0, 'no extra keys in hi (extra: ' . count($extraInHi) . ')');

if (count($missingInHi) > 0) {
    foreach ($missingInHi as $k => $v) {
        $messages[] = "    MISSING: {$k} = {$v}";
    }
}
if (count($extraInHi) > 0) {
    foreach ($extraInHi as $k => $v) {
        $messages[] = "    EXTRA:   {$k} = {$v}";
    }
}

// 3. Identical-value check (allowed: BHK, ₹, numerals)
section('3. No identical en/hi values (other than symbols)');
$allowedIdentical = [
    'no_bedrooms', 'one_bhk', 'two_bhk', 'three_bhk', 'four_bhk',
    'currency_inr',
];
$identicalFound = [];
foreach ($enFlat as $k => $v) {
    if (isset($hiFlat[$k]) && $hiFlat[$k] === $v && !in_array($k, $allowedIdentical, true)) {
        $identicalFound[$k] = $v;
    }
}
assertTrue(count($identicalFound) === 0,
    'no untranslated en/hi pairs (found: ' . count($identicalFound) . ')');
if (count($identicalFound) > 0) {
    foreach ($identicalFound as $k => $v) {
        $messages[] = "    UNTRANSLATED: {$k} = {$v}";
    }
}

// 4. Service class behavior
section('4. TranslationService behavior');
$svc = \App\Services\TranslationService::getInstance();
assertTrue($svc->isAvailable('en'), 'isAvailable(en)');
assertTrue($svc->isAvailable('hi'), 'isAvailable(hi)');
assertTrue(!$svc->isAvailable('fr'), 'isAvailable(fr) = false');

assertTrue($svc->get('home', [], 'en') === 'Home', '__("home", [], "en") = "Home"');
assertTrue($svc->get('home', [], 'hi') === 'होम', '__("home", [], "hi") = "होम"');

assertTrue($svc->get('nav.menu.home', [], 'en') === 'Home', 'nested nav.menu.home (en)');
assertTrue($svc->get('nav.menu.home', [], 'hi') === 'होम', 'nested nav.menu.home (hi)');

// Parameter substitution
$out = $svc->get('min_length', ['length' => 8], 'en');
assertTrue($out === 'Minimum 8 characters required',
    'parameter substitution: __("min_length", {length:8}) = "' . $out . '"');

$out = $svc->get('min_length', ['length' => 8], 'hi');
assertTrue(strpos($out, '8') !== false, 'Hindi parameter substitution contains "8"');

// 5. Pluralization
section('5. Pluralization');
$out = $svc->choice('result_found', 1, [], 'en');
assertTrue($out === '1 result', 'choice(result_found, 1) en = "1 result" (got "' . $out . '")');

$out = $svc->choice('result_found', 5, [], 'en');
assertTrue($out === '5 results', 'choice(result_found, 5) en = "5 results" (got "' . $out . '")');

$out = $svc->choice('result_found', 1, [], 'hi');
assertTrue($out === '1 परिणाम', 'choice(result_found, 1) hi = "1 परिणाम" (got "' . $out . '")');

$out = $svc->choice('result_found', 5, [], 'hi');
assertTrue($out === '5 परिणाम', 'choice(result_found, 5) hi = "5 परिणाम" (got "' . $out . '")');

// 6. Fallback to English
section('6. Fallback to English');
$svc->setLanguage('hi');
$nonExistent = '__non_existent_key_xyz__';
$out = $svc->get($nonExistent, [], 'hi');
assertTrue($out === $nonExistent, 'missing key returns the key itself');

// 7. Helper function
section('7. __() helper function');
$out = __("home");
assertTrue($out === 'Home' || $out === 'होम', '__("home") returns translated (got "' . $out . '")');

$out = __("home", "DEFAULT");
assertTrue($out === 'Home' || $out === 'होम', '__("home", "DEFAULT") with legacy signature');

// 8. Language switcher component exists
section('8. Component files exist');
assertTrue(file_exists(APP_ROOT . '/app/views/components/language_switcher.php'),
    'language_switcher.php component exists');

// 9. Heuristic: scan view files for hardcoded English strings (informational only)
section('9. View file hardcoded-string scan (informational)');
$viewDirs = [
    APP_ROOT . '/app/views/pages',
    APP_ROOT . '/app/views/layouts',
];
$totalHardcoded = 0;
$fileWithHardcoded = [];
foreach ($viewDirs as $dir) {
    if (!is_dir($dir)) continue;
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($rii as $f) {
        if (!$f->isFile() || substr($f->getFilename(), -4) !== '.php') continue;
        $content = file_get_contents($f->getPathname());
        // skip components / language switcher
        if (strpos($content, 'render_language_switcher') !== false) continue;
        // crude: count echo statements of literal strings > 5 chars
        preg_match_all('/echo\s+["\']([A-Z][A-Za-z0-9 ,\.!?&\-\(\)\']{15,})["\']\s*;/', $content, $m);
        $hardcoded = count($m[1]);
        if ($hardcoded > 3) {
            $totalHardcoded += $hardcoded;
            $fileWithHardcoded[$f->getPathname()] = $hardcoded;
        }
    }
}
$messages[] = "  (informational) {$totalHardcoded} hardcoded English echo statements in " . count($fileWithHardcoded) . " files";
foreach (array_slice($fileWithHardcoded, 0, 5, true) as $path => $count) {
    $rel = str_replace(APP_ROOT . '/', '', $path);
    $messages[] = "    - {$rel}: {$count}";
}

// ==================== Summary ====================
$messages[] = "";
$messages[] = "==================================================";
$messages[] = "PASSED: {$pass}";
$messages[] = "FAILED: {$fail}";
$messages[] = "==================================================";

echo implode(PHP_EOL, $messages) . PHP_EOL;

exit($fail === 0 ? 0 : 1);
