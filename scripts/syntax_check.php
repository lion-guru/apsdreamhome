<?php
$errors = 0;
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app')) as $f) {
    if ($f->getExtension() === 'php') {
        $r = shell_exec('php -l ' . $f->getPathname() . ' 2>&1');
        if (strpos($r, 'Parse error') !== false || strpos($r, 'Fatal error') !== false) {
            echo $r . "\n";
            $errors++;
        }
    }
}
echo "Done. Parse errors found: $errors\n";