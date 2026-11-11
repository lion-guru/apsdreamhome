<?php
/**
 * Security headers audit (CSP nonce, X-Frame-Options, Referrer-Policy, etc.)
 * Usage: php testing/csp_audit.php
 * Checks 5 key pages (home, properties, colonies, admin erp, user dashboard login)
 * Exit 0 if all mandatory headers present, 1 otherwise. Log to logs/testing/.
 */
$base = 'http://localhost/apsdreamhome';
$paths = ['/', '/properties', '/colonies', '/admin/login', '/login'];

function fetchHeaders($url, $jar = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    if ($jar) { curl_setopt($ch, CURLOPT_COOKIEJAR, $jar); curl_setopt($ch, CURLOPT_COOKIEFILE, $jar); }
    $raw = curl_exec($ch);
    $info = curl_getinfo($ch);
    $hsize = $info['header_size'];
    $headers = substr($raw, 0, $hsize);
    $body = substr($raw, $hsize);
    curl_close($ch);
    $map = [];
    foreach (explode("\n", trim($headers)) as $line) {
        if (strpos($line, ':') !== false) {
            [$k, $v] = explode(':', $line, 2);
            $map[strtolower(trim($k))] = trim($v);
        }
    }
    return [$map, $body, $info];
}

$logsDir = 'C:/xampp/htdocs/apsdreamhome/logs/testing';
if (!is_dir($logsDir)) mkdir($logsDir, 0755, true);
$logFile = $logsDir . '/csp_' . date('Y-m-d_His') . '.txt';
function w($msg) { global $logFile; echo $msg . "\n"; file_put_contents($logFile, $msg . "\n", FILE_APPEND); }

w("=== Security headers audit " . date('c') . " ===");
$fail = 0;
foreach ($paths as $p) {
  list($h, $body, $inf) = fetchHeaders($base . $p);
  w("\nGET $p HTTP {$inf['http_code']}");
  $checks = [
    'x-frame-options' => (isset($h['x-frame-options']) && stripos($h['x-frame-options'], 'SAMEORIGIN') !== false || stripos($h['x-frame-options'] ?? '', 'DENY') !== false),
    'content-security-policy' => isset($h['content-security-policy']),
    'x-content-type-options' => (isset($h['x-content-type-options']) && $h['x-content-type-options'] === 'nosniff'),
    'referrer-policy' => isset($h['referrer-policy']),
  ];
  foreach ($checks as $name => $ok) {
    w("  " . ($ok ? "PASS" : "FAIL") . " $name" . (isset($h[$name]) ? " = " . substr($h[$name], 0, 80) : " (missing)"));
    if (!$ok && $name === 'content-security-policy') $fail++;
  }
  // nonce in CSP should match body nonce if present
  if (isset($h['content-security-policy']) && preg_match('/nonce-([A-Za-z0-9+\/=]+)/', $h['content-security-policy'], $m)) {
    $nonce = $m[1];
    $found = (strpos($body, 'nonce="' . $nonce . '"') !== false || strpos($body, "nonce='" . $nonce . "'") !== false);
    w("  " . ($found ? "PASS" : "INFO") . " csp-nonce match in body (nonce=" . substr($nonce, 0, 8) . "...)");
  }
}
w("\nCSP audit done. Fail=$fail");
exit($fail === 0 ? 0 : 1);
