<?php
/**
 * Open-tag scan — catches the corruption class php -l CANNOT see:
 * a PHP file missing its opening `<?php` executes as plain text
 * (entire source echoes into the HTTP response + fatal 500).
 *
 * Scopes to EXECUTABLE php (controllers/services/core/traits/helpers/
 * models/middleware/routes/config/scripts). View partials starting with
 * HTML are legitimately tag-less and are skipped.
 *
 * Usage: php testing/open_tag_scan.php [--fix]
 *   --fix  prepends the missing `<` ONLY to files starting with `?php`.
 */
$roots = ['app/Http', 'app/Services', 'app/Core', 'app/Traits', 'app/Helpers', 'app/Models', 'app/Middleware', 'routes', 'config', 'scripts'];
$skipDirs = ['app/Services/Archives']; // dead archived junk, never autoloaded
$skipFiles = ['scripts/import_db.php']; // UTF-16 terminal transcript, not PHP code
$base = dirname(__DIR__);
$bad = [];
$n = 0;
$fix = in_array('--fix', $argv ?? []);
foreach ($roots as $rel) {
    $dir = $base . '/' . $rel;
    if (!is_dir($dir)) continue;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
        if (substr($file, -4) !== '.php') continue;
        $path = str_replace('\\', '/', (string)$file);
        $rel = substr($path, strlen($base) + 1);
        foreach ($skipDirs as $sd) {
            if ($rel === $sd || strpos($rel, $sd . '/') === 0) continue 2;
        }
        if (in_array($rel, $skipFiles, true)) continue;
        $n++;
        // First meaningful line: skip BOM, blank lines, shebang (#!/...) and
        // '#' comment lines — all legitimate before <?php in CLI scripts.
        $lines = @file($path);
        $first = '';
        if (is_array($lines)) {
            foreach ($lines as $ln) {
                $t = ltrim($ln, "\xEF\xBB\xBF \t\r\n");
                if ($t === '' || strncmp($t, '#!', 2) === 0 || strncmp($t, '#', 1) === 0) continue;
                $first = $t;
                break;
            }
        }
        if (strncmp($first, '<?php', 5) !== 0) {
            $bad[] = substr($file, strlen($base) + 1);
            if ($fix && strncmp($h, '?php', 4) === 0) {
                $body = file_get_contents($file);
                file_put_contents($file, '<' . $body);
            }
        }
    }
}
echo "checked=$n bad=" . count($bad) . "\n";
foreach ($bad as $b) echo "BAD: $b\n";
exit(count($bad) > 0 ? 1 : 0);
