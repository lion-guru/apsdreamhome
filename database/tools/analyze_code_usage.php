<?php
// Scans the codebase for SQL table usage and generates a report.
// Usage: php database/tools/analyze_code_usage.php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

$projectRoot = dirname(__DIR__, 2);
$scanDirs = [
    $projectRoot . '/admin',
    $projectRoot . '/api',
    $projectRoot . '/app',
    $projectRoot . '/includes',
    $projectRoot . '/src',
];

function collectPhpFiles(array $dirs): array {
    $files = [];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) continue;
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        foreach ($it as $file) {
            if ($file->isFile()) {
                $ext = strtolower(pathinfo($file->getPathname(), PATHINFO_EXTENSION));
                if ($ext === 'php') {
                    $files[] = $file->getPathname();
                }
            }
        }
    }
    return $files;
}

// Regex patterns to identify table usage
$patterns = [
    '/\bFROM\s+`?([a-zA-Z0-9_]+)`?/i',
    '/\bJOIN\s+`?([a-zA-Z0-9_]+)`?/i',
    '/\bINSERT\s+INTO\s+`?([a-zA-Z0-9_]+)`?/i',
    '/\bUPDATE\s+`?([a-zA-Z0-9_]+)`?/i',
    '/\bDELETE\s+FROM\s+`?([a-zA-Z0-9_]+)`?/i',
    '/\bCREATE\s+TABLE\s+`?([a-zA-Z0-9_]+)`?/i',
    '/\bALTER\s+TABLE\s+`?([a-zA-Z0-9_]+)`?/i',
];

$usage = []; // table => ['count' => n, 'files' => set]
$files = collectPhpFiles($scanDirs);
foreach ($files as $path) {
    $content = @file_get_contents($path);
    if ($content === false) continue;
    foreach ($patterns as $pat) {
        if (preg_match_all($pat, $content, $m)) {
            foreach ($m[1] as $tbl) {
                $tbl = strtolower($tbl);
                if (!isset($usage[$tbl])) {
                    $usage[$tbl] = ['count' => 0, 'files' => []];
                }
                $usage[$tbl]['count']++;
                $usage[$tbl]['files'][$path] = true;
            }
        }
    }
}

// Sort by usage count desc
uasort($usage, function ($a, $b) {
    return $b['count'] <=> $a['count'];
});

$out = [];
$out[] = '# Code Usage: SQL Tables';
$out[] = 'Generated: ' . date('Y-m-d H:i:s');
$out[] = '';
$out[] = '## Top Tables By References';
foreach ($usage as $table => $info) {
    $out[] = sprintf('- `%s` (references: %d, files: %d)', $table, $info['count'], count($info['files']));
}
$out[] = '';
$out[] = '## Detailed Files Per Table';
foreach ($usage as $table => $info) {
    $out[] = '### `' . $table . '`';
    foreach (array_keys($info['files']) as $f) {
        $rel = str_replace($projectRoot . DIRECTORY_SEPARATOR, '', $f);
        $out[] = '- ' . $rel;
    }
    $out[] = '';
}

$outPath = __DIR__ . '/code_usage_report.md';
file_put_contents($outPath, implode("\n", $out));
echo "Code usage report written to: {$outPath}\n";

