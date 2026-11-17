<?php
/**
 * audit_content_claims.php - Fabrication-claim inventory over DB content + source views.
 *
 * Scans (a) every row of blog_posts (title/excerpt/content) and (b) all tracked
 * public PHP views + lang stacks for unverifiable marketing/legal claims:
 *
 *   RERA/DTCP/ISO approvals, "100% legal/safe", "guaranteed", "No. 1",
 *   big round-number social-proof (500+, 1000+, 5000+ happy families etc.).
 *
 * Read-only. Always exits 0 (cron-safe). Prints a deterministic report; use
 * --json for machine-readable output and --exit=1 to make CI fail on hits.
 *
 * Usage:
 *   php scripts/audit_content_claims.php
 *   php scripts/audit_content_claims.php --json
 *   php scripts/audit_content_claims.php --exit=1   # CI gate
 */

$opts = getopt('', ['json', 'exit::', 'root::']);
$root = rtrim($opts['root'] ?? 'C:/xampp/htdocs/apsdreamhome', '/\\');
$asJson = isset($opts['json']);
$exitOnHits = isset($opts['exit']) ? (int)$opts['exit'] : 0;

$needles = [
    'rera_approval_claim'   => ['label' => 'RERA approved/registered claim',   'rx' => '/RERA\s+(approved|registered|certified|compliant)/i'],
    'dtcp_approval_claim'   => ['label' => 'DTCP approved claim',              'rx' => '/DTCP\s+approved/i'],
    'iso_claim'             => ['label' => 'ISO claim',                        'rx' => '/ISO\s*(9001|14001|45001|certified)/i'],
    'no_1_claim'            => ['label' => 'No.1 / best / most trusted claim', 'rx' => '/\bNo\.?\s*1\b|\bbest real estate\b|\bmost trusted\b/i'],
    'hundred_percent'       => ['label' => '100% legal/safe/guarantee claim',  'rx' => '/100%\s*(legal|safe|guarantee|verified|genuine)/i'],
    'guaranteed'            => ['label' => 'guaranteed claim',                 'rx' => '/guarantee[sd]?/i'],
    'big_number_plus'       => ['label' => 'Round-number social proof',        'rx' => '/(1,?000\+|2,?000\+|5,?000\+|200\+|250\+|500\+|1000\+)[^a-zA-Z]{0,8}(happy|families|clients|customers|projects|plots|properties|teams|units|sold)\b/i'],
    'unlimited_claim'       => ['label' => 'unlimited/lifetime claim',         'rx' => '/unlimited|lifetime warranty|lifetime guarantee/i'],
    'since_years'           => ['label' => 'Since-\d{4} claim (verify)',       'rx' => '/since\s+(19|20)\d{2}/i'],
];

// ---- Part A: blog_posts ---------------------------------------------------
require_once $root . '/config/bootstrap.php';
$db = App\Core\Database\Database::getInstance();
$skipPathRx = '#(assets[\\/]plugins|license\.txt|/tinymce/)#i';   // 3rd-party bundled assets - not our copy
$skipLineRx = '#RERA Not Registered Yet|No RERA registered#i';           // honest empty states / comments
$blogHits = [];
try {
    $posts = $db->fetchAll("SELECT id, slug, status, title, excerpt, content FROM blog_posts ORDER BY id");
} catch (Throwable $e) {
    $posts = [];
}
foreach ($posts as $p) {
    $text = $p['title'] . "\n" . $p['excerpt'] . "\n" . $p['content'];
    foreach ($needles as $key => $n) {
        if (preg_match_all($n['rx'], $text, $m, PREG_OFFSET_CAPTURE)) {
            foreach ($m[0] as $hit) {
                $blogHits[] = [
                    'where' => 'blog#' . $p['id'] . ' (' . ($p['status'] ?? '?') . ') ' . ($p['slug'] ?? ''),
                    'needle' => $key,
                    'label' => $n['label'],
                    'match' => $hit[0],
                ];
            }
        }
    }
}

// ---- Part B: public views + lang stacks -----------------------------------
$sourceRoots = [$root . '/app/views', $root . '/lang'];
$sourceHits = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/app/views', FilesystemIterator::SKIP_DOTS));
foreach ($iterator as $file) {
    $path = $file->getPathname();
    $rel = substr($path, strlen($root) + 1);
    $relNorm = str_replace('\\', '/', $rel);
    if (preg_match($skipPathRx, $relNorm)) continue;   // 3rd-party bundled assets - not our copy
    $text = file_get_contents($path);
    $textLines = explode("\n", $text);
    foreach ($needles as $key => $n) {
        if (preg_match_all($n['rx'], $text, $m, PREG_OFFSET_CAPTURE)) {
            foreach ($m[0] as $hit) {
                // line number for the first occurrence on this file+needle
                $line = substr_count(substr($text, 0, $hit[1]), "\n") + 1;
                $lineText = isset($textLines[$line - 1]) ? $textLines[$line - 1] : '';
                if (preg_match($skipLineRx, $lineText)) continue;   // honest empty state
                $sourceHits[] = [
                    'where' => $rel . ':' . $line,
                    'needle' => $key,
                    'label' => $n['label'],
                    'match' => trim(preg_replace('/\s+/', ' ', $hit[0])),
                ];
            }
        }
    }
}
foreach (glob($root . '/lang/*.php') ?: [] as $langFile) {
    $rel = substr($langFile, strlen($root) + 1);
    $text = file_get_contents($langFile);
    foreach ($needles as $key => $n) {
        if (preg_match_all($n['rx'], $text, $m, PREG_OFFSET_CAPTURE)) {
            foreach ($m[0] as $hit) {
                $line = substr_count(substr($text, 0, $hit[1]), "\n") + 1;
                $sourceHits[] = ['where' => $rel . ':' . $line, 'needle' => $key, 'label' => $n['label'], 'match' => trim(preg_replace('/\s+/', ' ', $hit[0]))];
            }
        }
    }
}

$byNeedle = [];
$byWhere = [];
foreach (array_merge($blogHits, $sourceHits) as $h) {
    $byNeedle[$h['needle']] = ($byNeedle[$h['needle']] ?? 0) + 1;
    $byWhere[$h['where']] = ($byWhere[$h['where']] ?? 0) + 1;
}
arsort($byNeedle);
arsort($byWhere);

// ---- Report ---------------------------------------------------------------
$total = count($blogHits) + count($sourceHits);
if ($asJson) {
    echo json_encode(['total' => $total, 'blog_hits' => $blogHits, 'source_hits' => $sourceHits, 'counts_by_needle' => $byNeedle], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
} else {
    echo str_repeat('=', 78) . "\n";
    echo "APS Dream Home - Content Fabrication Claims Audit\n";
    echo str_repeat('=', 78) . "\n";
    echo " blog posts scanned   : " . count($posts) . "\n";
    echo " views scanned        : recursive app/views (UTF-8) + lang stacks\n";
    echo " total flags          : {$total}\n";
    echo "\n by claim class:\n";
    foreach ($byNeedle as $k => $n) {
        printf("   %-24s %4d   (%s)\n", $k, $n, $needles[$k]['label']);
    }
    echo "\n top locations:\n";
    foreach (array_slice($byWhere, 0, 15, true) as $where => $n) {
        printf("   %4d  %s\n", $n, $where);
    }
    $examples = array_slice($blogHits, 0, 10);
    if ($examples) {
        echo "\n blog samples:\n";
        foreach ($examples as $e) {
            printf("   [%s] %s\n      -> %s\n", $e['where'], $e['label'], $e['match']);
        }
    }
}
if ($exitOnHits === 1 && $total > 0) exit(1);
exit(0);
