<?php
/**
 * BACKUP <-> ORIGINAL LOCATION MAPPING REPORT
 * Har backup file dikhata hai + uska asli location + existence check
 */
$root = 'app/views';
$backupRoot = '_archive/class_attr_repair_backup';

$out = [];
$out[] = str_repeat('=', 100);
$out[] = 'BACKUP FILE -> ORIGINAL LOCATION MAP (671 files)';
$out[] = 'Backup: _archive/class_attr_repair_backup/  |  Asli: app/views/';
$out[] = 'Status: [OK] = file sahi jagah par hai (fixed version)';
$out[] = str_repeat('=', 100);

$total = 0; $okCount = 0; $missing = [];
$byDir = [];

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($backupRoot));
foreach ($rii as $f) {
    if ($f->getExtension() !== 'php') continue;
    $total++;
    $rel = str_replace('\\', '/', substr($f->getPathname(), strlen($backupRoot) + 1));
    $curPath = $root . '/' . $rel;

    $exists = file_exists($curPath);
    if ($exists) { $okCount++; $mark = '[OK]'; }
    else { $missing[] = $rel; $mark = '[MISSING!]'; }

    $dir = dirname($rel);
    $byDir[$dir] = ($byDir[$dir] ?? 0) + 1;
    $out[] = sprintf("%s  %s\n      -> %s", $mark, '_archive/class_attr_repair_backup/' . $rel, $curPath);
}

$out[] = '';
$out[] = str_repeat('=', 100);
$out[] = 'SUMMARY';
$out[] = str_repeat('=', 100);
$out[] = "Total backup files: $total";
$out[] = "Original location par maujood (sahi jagah): $okCount";
$out[] = 'Missing: ' . count($missing);
foreach ($missing as $m) $out[] = '   !! MISSING: app/views/' . $m;
$out[] = '';
$out[] = 'Folder-wise count:';
krsort($byDir);
arsort($byDir);
foreach ($byDir as $d => $c) {
    $out[] = sprintf('   %-55s %3d files', $d . '/', $c);
}
file_put_contents('testing/repair_mapping_report.txt', implode("\n", $out));
echo "Report saved: testing/repair_mapping_report.txt\n";
echo "Total: $total | OK: $okCount | Missing: ", count($missing), "\n";
