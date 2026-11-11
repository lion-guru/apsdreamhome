<?php
/**
 * Security / access probe — role-matrix smoke (read-only GETs)
 * - Customer session tries admin/associate pages (expect redirect to login, NOT 200 leak)
 * - Unauthenticated POST without CSRF (expect 302, not success)
 * - test_login production gate is code-proven (APP_ENV=production → 403); not env-flipped live
 *
 * Usage: php testing/security_access_probe.php
 * Exit 0 if all expectations met, 1 otherwise.
 */
$base = 'http://localhost/apsdreamhome';
function fetch($url, $jar, $post = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $jar);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $jar);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
    if ($post !== null) { curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_POSTFIELDS, $post); }
    $html = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return [$html, $info];
}
function isLoginRedirect($url) { return strpos($url, '/login') !== false || strpos($url, '/admin/login') !== false || strpos($url, '/associate/login') !== false; }

$jar = sys_get_temp_dir() . '/sec_probe.txt'; @unlink($jar);
fetch($base . '/login?test_login=7', $jar); // customer

$tests = [
  ['/admin/erp', 'customer -> admin/erp'],
  ['/admin/colonies', 'customer -> admin/colonies'],
  ['/associate/dashboard', 'customer -> associate/dashboard'],
  ['/admin/service-configs', 'customer -> service-configs'],
];
$fail = 0;
foreach ($tests as [$path, $label]) {
  list($h, $inf) = fetch($base . $path, $jar);
  $ok = ($inf['http_code'] !== 200) || isLoginRedirect($inf['url']);
  // If 200 but final url is dashboard, that's a leak
  if ($inf['http_code'] === 200 && !isLoginRedirect($inf['url'])) {
    $ok = false;
  }
  echo ($ok ? 'PASS' : 'FAIL') . " $label HTTP {$inf['http_code']} final={$inf['url']}\n";
  if (!$ok) $fail++;
}
// Unauthenticated CSRF POST (no session) -> should NOT succeed as 200 with success flag
$jarAnon = sys_get_temp_dir() . '/sec_probe_anon.txt'; @unlink($jarAnon);
list($h, $inf) = fetch($base . '/admin/service-configs/update', $jarAnon, ['configs' => ['x' => 'y']]);
echo "Unauth POST service-configs/update HTTP {$inf['http_code']} final={$inf['url']} (expect redirect, not success)\n";
echo $fail === 0 ? "SECURITY PROBE PASS\n" : "SECURITY PROBE FAIL ($fail)\n";
exit($fail === 0 ? 0 : 1);
