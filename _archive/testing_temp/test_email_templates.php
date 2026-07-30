<?php
/**
 * Smoke test for EmailTemplateController + TemplateService.
 *
 * Verifies:
 *   1. Service loads all 4 catalog templates
 *   2. Placeholders get replaced
 *   3. Unknown code returns a clean error
 *   4. HTML output starts with <!DOCTYPE html> for every template
 *   5. Subject line is returned and looks right
 */

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

require_once __DIR__ . '/../app/Core/Autoloader.php';

use App\Services\Communication\TemplateService;

$svc = new TemplateService();

$results = [];
$failed  = 0;

// 1. List templates
$list = $svc->list();
$results[] = ['list_count_is_4', count($list) === 4, 'count=' . count($list)];

$codes = array_column($list, 'code');
foreach (['welcome', 'password_reset', 'booking_confirmation', 'property_approved'] as $code) {
    $results[] = ["list_has_{$code}", in_array($code, $codes, true), ''];
}

// 2. Render each template
$sampleVars = [
    'welcome'              => ['name' => 'Ravi Kumar', 'login_url' => 'http://localhost/apsdreamhome/login'],
    'password_reset'       => ['user_name' => 'Anita', 'reset_url' => 'http://localhost/apsdreamhome/reset?token=xyz', 'expires_in' => '30 minutes'],
    'booking_confirmation' => ['customer_name' => 'Suresh', 'booking_id' => 'BK-2026-0042',
                               'property_name' => 'Suryoday Heights Phase 2',
                               'property_location' => 'Gorakhpur, UP',
                               'booking_date' => '05 Jun 2026', 'amount' => '25,00,000'],
    'property_approved'    => ['user_name' => 'Priya', 'property_name' => 'Braj Radha Enclave',
                               'property_location' => 'Lucknow, UP',
                               'property_type' => 'Plot', 'property_area' => '1200',
                               'property_price' => '32,00,000'],
];

foreach ($sampleVars as $code => $vars) {
    $r = $svc->renderHtmlTemplate($code, $vars);
    $results[] = ["{$code}_ok",            $r['ok'] === true, json_encode($r)];
    $results[] = ["{$code}_has_html",      !empty($r['html']) && is_string($r['html']), ''];
    $results[] = ["{$code}_has_subject",   !empty($r['subject']) && is_string($r['subject']), ''];
    $results[] = ["{$code}_starts_doctype", str_starts_with(trim($r['html'] ?? ''), '<!DOCTYPE html>'), ''];
    // Make sure at least one supplied var is actually present in the rendered HTML
    $firstVar = reset($vars);
    $firstKey = array_key_first($vars);
    $results[] = ["{$code}_var_{$firstKey}_present",
                  str_contains($r['html'] ?? '', htmlspecialchars((string)$firstVar, ENT_QUOTES, 'UTF-8')),
                  "value='{$firstVar}' expected in html"];
    // Make sure no leftover {{var}} tokens remain for the var we provided
    $results[] = ["{$code}_no_leftover_{$firstKey}",
                  !preg_match('/\{\{\s*' . preg_quote($firstKey, '/') . '\s*\}\}/i', $r['html'] ?? ''),
                  "found leftover {{$firstKey}} in rendered HTML"];
}

// 3. Unknown code
$unknown = $svc->renderHtmlTemplate('does_not_exist');
$results[] = ['unknown_code_returns_error', $unknown['ok'] === false && !empty($unknown['error']), ''];

// 4. Code is sanitized
$malicious = $svc->renderHtmlTemplate("welcome'; DROP TABLE users;--");
$results[] = ['malicious_code_sanitized', $malicious['ok'] === false, ''];

// 5. Missing file is reported (use a code that exists in catalog but force a bad path)
$reflection = new ReflectionClass(TemplateService::class);
$prop = $reflection->getProperty('templatesPath');
$prop->setAccessible(true);
$bad = new TemplateService();
$prop->setValue($bad, __DIR__ . '/no-such-dir');
$badResult = $bad->renderHtmlTemplate('welcome');
$results[] = ['missing_dir_reported', $badResult['ok'] === false, ''];

// 6. Default values work when no vars supplied
$noVars = $svc->renderHtmlTemplate('welcome');
$results[] = ['defaults_applied', $noVars['ok'] === true
                                   && str_contains($noVars['html'], 'Customer')
                                   && str_contains($noVars['html'], date('Y')),
              ''];

// 7. XSS escaping works
$xssResult = $svc->renderHtmlTemplate('welcome', ['name' => '<script>alert(1)</script>']);
$results[] = ['xss_escaped', str_contains($xssResult['html'] ?? '', '&lt;script&gt;')
                            && !str_contains($xssResult['html'] ?? '', '<script>alert(1)</script>'), ''];

// Report
$pass = 0;
$fail = 0;
foreach ($results as [$name, $ok, $detail]) {
    if ($ok) { $pass++; echo "  PASS  $name\n"; }
    else     { $fail++; echo "  FAIL  $name  $detail\n"; }
}

echo "\n========================================\n";
echo "Email Template Smoke Test: $pass passed, $fail failed\n";
echo "========================================\n";
exit($fail === 0 ? 0 : 1);
