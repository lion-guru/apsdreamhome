<?php
$isDryRun = in_array('--dry-run', $argv);
$baseDir = dirname(__DIR__) . '/app/views';
$fixedFiles = 0;
$totalEdits = 0;

$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($it as $f) {
    if ($f->getExtension() !== 'php') continue;
    $fp = $f->getPathname();
    $c = file_get_contents($fp);
    $o = $c;

    if (strpos($c, 'echo $') === false) continue;

    $lines = explode("\n", $c);
    $newLines = array();
    $lineEdits = 0;

    foreach ($lines as $line) {
        if (strpos($line, 'echo $') === false) {
            $newLines[] = $line;
            continue;
        }
        if (strpos($line, 'echo e(') !== false) {
            $newLines[] = $line;
            continue;
        }
        if (strpos($line, 'echo htmlspecialchars') !== false) {
            $newLines[] = $line;
            continue;
        }
        if (strpos($line, 'echo __') !== false) {
            $newLines[] = $line;
            continue;
        }
        if (strpos($line, 'echo $_SESSION') !== false) {
            $newLines[] = $line;
            continue;
        }
        if (strpos($line, 'echo $GLOBALS') !== false) {
            $newLines[] = $line;
            continue;
        }
        if (strpos($line, 'echo defined') !== false) {
            $newLines[] = $line;
            continue;
        }
        if (strpos($line, 'echo count(') !== false) {
            $newLines[] = $line;
            continue;
        }
        if (strpos($line, 'echo date(') !== false) {
            $newLines[] = $line;
            continue;
        }
        if (strpos($line, 'echo number_format') !== false) {
            $newLines[] = $line;
            continue;
        }

        $origLine = $line;

        $found = false;
        if (preg_match('/<\?php\s+echo\s+(\$[\w]+(?:\[[^\]]+\])*)\s*;\s*\?>/', $line, $m)) {
            $expr = $m[1];
            $new = '<?php echo e(' . $expr . '); ?>';
            $line = str_replace($m[0], $new, $line);
            $lineEdits++;
            $found = true;
        }

        if (!$found && preg_match('/<\?=\s*(\$[\w]+(?:\[[^\]]+\])*)\s*\?>/', $line, $m2)) {
            $expr2 = $m2[1];
            $new2 = '<?php echo e(' . $expr2 . '); ?>';
            $line = str_replace($m2[0], $new2, $line);
            $lineEdits++;
        }

        $newLines[] = $line;
    }

    $newContent = implode("\n", $newLines);

    if ($newContent !== $o) {
        if (!$isDryRun) {
            file_put_contents($fp, $newContent);
        }
        $fixedFiles++;
        $totalEdits += $lineEdits;
        $rel = str_replace($baseDir . DIRECTORY_SEPARATOR, '', $fp);
        echo "  " . $rel . " (" . $lineEdits . ")\n";
    }
}

echo "\nFiles: " . $fixedFiles . " | Edits: " . $totalEdits . " | Mode: " . ($isDryRun ? "DRY RUN" : "LIVE") . "\n";