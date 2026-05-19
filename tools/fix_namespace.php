<?php
// Fix namespace errors: move any comments before <?php

$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app/Http/Controllers'));
$fixed = 0;

foreach ($dir as $file) {
    if ($file->getExtension() !== 'php') continue;
    if ($file->isDir()) continue;

    $path = $file->getPathname();
    $content = file_get_contents($path);

    // Check if file starts with comment before <?php
    if (preg_match('/^(\s*)(\/\/|\/\*)/', $content)) {
        // Find where <?php starts
        if (preg_match('/<\?php/', $content)) {
            // Insert <?php at the start if missing
            if (!preg_match('/^<\?php/', $content)) {
                $content = "<?php\n" . $content;
                file_put_contents($path, $content);
                echo "Fixed: $path\n";
                $fixed++;
            }
        }
    }
}

echo "\nTotal fixed: $fixed files\n";