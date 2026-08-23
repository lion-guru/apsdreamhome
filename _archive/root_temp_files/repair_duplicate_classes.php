<?php
$root = 'app/views';
$backupRoot = '_archive/class_attr_repair_backup';
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
// \s+ spans newlines safely: two class= attrs separated only by whitespace are always the same tag
$pattern = '/class=("([^"]*)")\s+class=("([^"]*)")/s';
$totalFiles = 0;
$totalReplacements = 0;
$failed = [];

foreach ($rii as $f) {
    if ($f->getExtension() !== 'php') continue;
    $path = $f->getPathname();
    $s = file_get_contents($path);
    $count = 0;
    $new = preg_replace_callback($pattern, function ($m) use (&$count) {
        $count++;
        return 'class="' . trim($m[2] . ' ' . $m[4]) . '"';
    }, $s, -1, $pc);
    if ($count > 0 && $new !== null && $new !== $s) {
        // backup
        $rel = str_replace('\\', '/', substr($path, strlen($root) + 1));
        $dest = $backupRoot . '/' . $rel;
        $dir = dirname($dest);
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        copy($path, $dest);
        file_put_contents($path, $new);
        $totalFiles++;
        $totalReplacements += $count;
        // syntax check
        exec('php -l ' . escapeshellarg($path) . ' 2>&1', $out, $code);
        if ($code !== 0) {
            $failed[] = $path;
            echo "SYNTAX FAIL: $path\n", implode("\n", $out), "\n";
            copy($dest, $path);
            echo "  -> restored from backup\n";
        }
        $out = [];
    }
}
echo "files repaired (pass 2): $totalFiles\n";
echo "attributes merged (pass 2): $totalReplacements\n";
echo "failures: ", count($failed), "\n";

// final verification scan
$remaining = 0;
$rii2 = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
foreach ($rii2 as $f) {
    if ($f->getExtension() !== 'php') continue;
    if (preg_match('/class="[^"]*"\s+class="/s', file_get_contents($f->getPathname()))) $remaining++;
}
echo "files still containing dup-class: $remaining\n";
