<?php
/**
 * APS Dream Home - Public RERA Placeholder Gate
 *
 * Two independent checks, both fatalify (exit 1) on any hit:
 *
 *   A. Source scan  - buyer-facing PHP views and lang files must never contain
 *                     the literal 'PENDING-VERIFICATION' placeholder. Admin views
 *                     and controller source are exempt (admins know the status,
 *                     and guards live in controllers, not in view literals).
 *   B. Live probe   - every buyer-facing route catalog must serve its page
 *                     (2xx/3xx) and never contain the literal placeholder.
 *
 * The placeholder 'PENDING-VERIFICATION' is seeded on colonies.rera_number by
 * migration seed_pending_rera_s692 (awaiting legal-desk NOCs). Public pages must
 * hide values starting with PENDING; admin pages may show them flagged.
 *
 * Usage:  php testing/public_pending_rera_probe.php
 *         php testing/public_pending_rera_probe.php --json
 */

define('APS_ROOT', dirname(__DIR__));

$failures = [];
$json = in_array('--json', $argv, true);

// ── Check A: source literal scan (excludes admin views) ───────────────────
$rii = new RecursiveIteratorIterator(
    new RecursiveCallbackFilterIterator(
        new RecursiveDirectoryIterator(APS_ROOT . '/app/views', FilesystemIterator::SKIP_DOTS),
        function ($entry) {
            $p = str_replace('\\', '/', $entry->getPathname());
            if ($entry->isDir()) {
                return stripos($p, '/admin') === false;
            }
            return $entry->isFile() && strtolower($entry->getExtension()) === 'php';
        }
    )
);
$found = 0;
foreach ($rii as $file) {
    $text = file_get_contents($file->getPathname());
    if ($text === false) continue;
    $n = substr_count($text, 'PENDING-VERIFICATION');
    if ($n > 0) {
        $rel = substr(str_replace('\\', '/', $file->getPathname()), strlen(APS_ROOT) + 1);
        $found += $n;
        $failures[] = "SOURCE {$rel}: {$n}x raw 'PENDING-VERIFICATION' literal";
    }
}
foreach (glob(APS_ROOT . '/lang/*.php') ?: [] as $lang) {
    $text = file_get_contents($lang);
    if ($text && substr_count($text, 'PENDING-VERIFICATION') > 0) {
        $rel = substr(str_replace('\\', '/', $lang), strlen(APS_ROOT) + 1);
        $failures[] = "SOURCE {$rel}: raw 'PENDING-VERIFICATION' literal";
    }
}

// ── Check B: live HTTP probe against buyer-facing pages ──────────────────
$base = 'http://localhost/apsdreamhome';
$routes = [
    '/' => 200, '/properties' => 200, '/projects' => 200, '/emi-calculator' => 301,
    '/become-associate' => 200,
    '/colony/suryoday-colony' => 200, '/colony/braj-radha-nagri' => 200, '/colony/budh-bihar-colony' => 200,
    '/colony/raghunath-nagri-motiram' => 200, '/colony/lakeview-residency' => 200,
    '/colony/suryoday-colony/plots' => 200,
    '/project-locations/budha-city' => 200, '/project-locations/suryoday-colony' => 200,
    '/project-locations/raghunath-nagri' => 200, '/project-locations/bohisawagar' => 200,
    '/project-locations/ram-nagri' => 200, '/project-locations/ganga-nagri' => 200,
    '/blog' => 200, '/blog/colony-plots-vs-individual-land' => 200, '/tools-hub' => 200,
];
$httpMark = 0;
foreach ($routes as $path => $_) {
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 60, CURLOPT_FOLLOWLOCATION => false]);
    $body = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code < 200 || $code >= 400) {
        $failures[] = "HTTP {$path}: expected 2xx/3xx, got {$code}";
        continue;
    }
    if (stripos((string)$body, 'PENDING-VERIFICATION') !== false) {
        $failures[] = "HTTP {$path}: body contains literal 'PENDING-VERIFICATION'";
        continue;
    }
    $httpMark++;
}
echo "  source literals flagged : " . $found . "\n";
echo "  live pages clean        : " . $httpMark . "\n";

if ($json) {
    echo json_encode(['ok' => !$failures, 'failures' => $failures, 'live_clean' => $httpMark, 'source_flagged' => $found], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    exit($failures ? 1 : 0);
}

if ($failures) {
    echo "  FAIL:\n";
    foreach ($failures as $f) echo "    - {$f}\n";
    echo "  PUBLIC RERA PLACEHOLDER PROBE: FAIL\n";
    exit(1);
}
echo "  PUBLIC RERA PLACEHOLDER PROBE: PASS\n";
exit(0);
