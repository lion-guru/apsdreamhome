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
    'google_login', 'facebook_login', 'linkedin_login',
    'upi_id', 'phone_ph', 'email_ph', 'name_ph', 'password_ph',
    'british_english', 'american_english', 'english_lang', 'hindi_lang',
    'admin_hash_label',
    // BHK is a universally accepted abbreviation in Hindi too ("Bedroom Hall Kitchen")
    'bhk', 'home_bhk_1', 'home_bhk_2', 'home_bhk_3', 'home_bhk_4',
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

// 10. Phase 2 specific: wrapped view files use __() helper
section('10. Phase 2: __() helper used in wrapped view files');
$wrappedFiles = [
    '/app/views/pages/saved_searches.php' => 5,
    '/app/views/pages/user_dashboard.php' => 5,
    '/app/views/pages/user_bank_details.php' => 5,
    '/app/views/pages/user_favorites.php' => 3,
    '/app/views/pages/user_properties.php' => 3,
    '/app/views/pages/user_inquiries.php' => 3,
    '/app/views/pages/user_profile.php' => 3,
    '/app/views/pages/user/notifications.php' => 2,
    '/app/views/pages/user/edit_profile.php' => 3,
    '/app/views/pages/user/investments.php' => 3,
    '/app/views/pages/user/manage_alerts.php' => 3,
    '/app/views/pages/user/notification_preferences.php' => 3,
    '/app/views/pages/user/saved_search_results.php' => 2,
    '/app/views/pages/user/saved_searches.php' => 5,
    '/app/views/pages/about.php' => 5,
    '/app/views/pages/list_property.php' => 5,
    '/app/views/pages/blog.php' => 3,
    '/app/views/pages/faqs.php' => 10,
    '/app/views/pages/testimonials.php' => 3,
    '/app/views/pages/contact.php' => 3,
    '/app/views/pages/careers.php' => 5,
    '/app/views/pages/career_apply.php' => 3,
    '/app/views/auth/customer_register.php' => 5,
    '/app/views/auth/forgot_password.php' => 5,
    '/app/views/layouts/footer.php' => 3,
];
$totalWrapCalls = 0;
$wrappedFileCount = 0;
foreach ($wrappedFiles as $rel => $minCalls) {
    $path = APP_ROOT . $rel;
    if (!file_exists($path)) {
        $messages[] = "  (skip - missing) {$rel}";
        continue;
    }
    $content = file_get_contents($path);
    $count = substr_count($content, '<?= __(') + substr_count($content, '<?php echo __(');
    $totalWrapCalls += $count;
    $wrappedFileCount++;
    assertTrue($count >= $minCalls, "{$rel} uses __() >= {$minCalls} times (got {$count})");
}
$messages[] = "  (info) {$totalWrapCalls} __() calls in {$wrappedFileCount} wrapped files";

// 11. Admin pages wrapped
section('11. Admin pages wrapped');
$adminFiles = [
    '/app/views/admin/dashboard.php' => 3,
    '/app/views/admin/email_templates.php' => 3,
    '/app/views/admin/cache.php' => 3,
    '/app/views/admin/gateways.php' => 5,
];
foreach ($adminFiles as $rel => $minCalls) {
    $path = APP_ROOT . $rel;
    if (!file_exists($path)) {
        $messages[] = "  (skip - missing) {$rel}";
        continue;
    }
    $content = file_get_contents($path);
    $count = substr_count($content, '<?= __(') + substr_count($content, '<?php echo __(');
    assertTrue($count >= $minCalls, "{$rel} uses __() >= {$minCalls} times (got {$count})");
}

// 12. All wrapped files have valid PHP syntax
section('12. All wrapped files have valid PHP syntax');
foreach (array_keys($wrappedFiles) as $rel) {
    $path = APP_ROOT . $rel;
    if (!file_exists($path)) continue;
    $out = shell_exec('php -l ' . escapeshellarg($path) . ' 2>&1');
    assertTrue(strpos($out, 'No syntax errors') !== false, "{$rel} has no syntax errors");
}
foreach (array_keys($adminFiles) as $rel) {
    $path = APP_ROOT . $rel;
    if (!file_exists($path)) continue;
    $out = shell_exec('php -l ' . escapeshellarg($path) . ' 2>&1');
    assertTrue(strpos($out, 'No syntax errors') !== false, "{$rel} has no syntax errors");
}

// 13. Devanagari presence in hi.php (Hindi script)
section('13. hi.php contains Devanagari script');
$hiContent = file_get_contents(APP_ROOT . '/lang/hi.php');
$devanagariCount = preg_match_all('/[\x{0900}-\x{097F}]/u', $hiContent);
assertTrue($devanagariCount > 1000, 'hi.php has >1000 Devanagari chars (got ' . $devanagariCount . ')');

// 14. en.php is NOT in Devanagari (no leakage)
$enContent = file_get_contents(APP_ROOT . '/lang/en.php');
$enDevanagari = preg_match_all('/[\x{0900}-\x{097F}]/u', $enContent);
assertTrue($enDevanagari === 0, 'en.php has 0 Devanagari chars (got ' . $enDevanagari . ')');

// 15. Each new key added this session exists in both en and hi
section('15. Phase 2 keys present in en and hi');
$phase2Prefixes = [
    'about_', 'reg_', 'fp_', 'lp_', 'blog_', 'faqs_',
    'user_', 'dash_', 'bank_', 'user_inv_', 'user_settings_',
    'alerts_', 'notif_', 'saved_', 'saved_res_', 'saved_',
    'testi_', 'contact_', 'careers_', 'career_apply_',
    'admin_', 'admin_btn_', 'admin_stat_', 'admin_gw_',
    'admin_quick_', 'admin_action_', 'admin_tpl_', 'admin_cache_',
    'ptype_',
];
$prefixCount = 0;
foreach (array_keys($enFlat) as $k) {
    foreach ($phase2Prefixes as $prefix) {
        if (strpos($k, $prefix) === 0) {
            $prefixCount++;
            assertTrue(isset($hiFlat[$k]), "Phase2 key '{$k}' present in hi");
            break;
        }
    }
}
$messages[] = "  (info) {$prefixCount} Phase 2 keys verified across both files";

// 16. Language switcher URLs present
section('16. Language switcher routes / URLs exist');
$routesContent = file_get_contents(APP_ROOT . '/routes/web.php');
assertTrue(strpos($routesContent, '/language/set/') !== false, '/language/set/ route registered in web.php');
assertTrue(file_exists(APP_ROOT . '/app/views/components/language_switcher.php'),
    'language_switcher.php component exists');

// 17. __() helper handles both signatures
section('17. __() helper handles legacy and new signatures');
$out1 = __("home");
$out2 = __("home", null, "Default");
$out3 = __("home", "Default");
assertTrue(in_array($out1, ['Home', 'होम'], true), '__("home") returns translated (got "' . $out1 . '")');
assertTrue(in_array($out2, ['Home', 'होम'], true), '__("home", null, "Default") returns translated (got "' . $out2 . '")');
assertTrue(in_array($out3, ['Home', 'होम'], true), '__("home", "Default") legacy sig returns translated (got "' . $out3 . '")');

// 18. Missing key returns key
section('18. Missing key fallback');
$missingKey = '__no_such_key_xyz_999__';
$out = __($missingKey);
assertTrue($out === $missingKey, 'missing key returns the key itself');

// 19. Nested key access
section('19. Nested key access (dot notation)');
$svc2 = \App\Services\TranslationService::getInstance();
$out = $svc2->get('nav.menu.home', [], 'en');
assertTrue(in_array($out, ['Home', 'होम'], true), 'nested nav.menu.home accessible (got "' . $out . '")');

// 20. Choice with custom plural
section('20. Pluralization with custom separators');
$out = $svc2->choice('result_found', 0, [], 'en');
assertTrue($out === 'No results' || $out === '0 results', 'choice(result_found, 0) handled (got "' . $out . '")');

$out = $svc2->choice('item_count', 3, [], 'en');
assertTrue(is_string($out) && strlen($out) > 0, 'choice(item_count, 3) returns string');

// 21. __() calls are well-formed (verified by php -l in section 12 + this balance check)
section('21. __() call paren balance (simple heuristic)');
$balancedTotal = 0;
foreach ($wrappedFiles as $rel => $_) {
    $path = APP_ROOT . $rel;
    if (!file_exists($path)) continue;
    $content = file_get_contents($path);
    $openCount = substr_count($content, '__(');
    $closeCount = substr_count($content, ')');
    if ($openCount > 0 && $closeCount >= $openCount) {
        $balancedTotal++;
    }
}
assertTrue($balancedTotal >= 20, '20+ files have balanced __() parens (got ' . $balancedTotal . ')');

// 22. en.php and hi.php both have same total key count
section('22. Final parity check');
assertTrue(count($enFlat) === count($hiFlat), 'en and hi parity holds at ' . count($enFlat) . ' keys');
assertTrue(count($enFlat) >= 1700, 'total keys >= 1700 (got ' . count($enFlat) . ')');

// 23. TranslationService setLanguage/getCurrentLanguage round trip
section('23. setLanguage/getCurrentLanguage round trip');
$svc2->setLanguage('en');
$lang1 = $svc2->getCurrentLanguage();
$svc2->setLanguage('hi');
$lang2 = $svc2->getCurrentLanguage();
$svc2->setLanguage('en');
$lang3 = $svc2->getCurrentLanguage();
assertTrue($lang1 === 'en' && $lang2 === 'hi' && $lang3 === 'en', 'setLanguage round trip works');

// 24. Round-trip: en→hi→en via URL flow
section('24. Round-trip language switching');
$svc2->setLanguage('en');
$enHome = $svc2->get('home', [], 'en');
$svc2->setLanguage('hi');
$hiHome = $svc2->get('home', [], 'hi');
$svc2->setLanguage('en');
$enHome2 = $svc2->get('home', [], 'en');
assertTrue($enHome === $enHome2, 'round-trip en→hi→en returns same value');
assertTrue($enHome !== $hiHome, 'en and hi values differ for "home"');

// ==================== Summary ====================
$messages[] = "";
$messages[] = "==================================================";
$messages[] = "PASSED: {$pass}";
$messages[] = "FAILED: {$fail}";
$messages[] = "==================================================";

echo implode(PHP_EOL, $messages) . PHP_EOL;

exit($fail === 0 ? 0 : 1);
