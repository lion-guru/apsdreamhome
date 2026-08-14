<?php
// Look for common patterns: word[ followed by quotes WITHOUT leading $
// These are typically inside HTML template code, not legitimate PHP
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app/views'));
$bugs = [];
foreach ($iter as $f) {
    if (!$f->isFile() || $f->getExtension() !== 'php') continue;
    $content = file_get_contents($f->getPathname());
    $relPath = str_replace('\\', '/', str_replace(__DIR__ . '/../', '', $f->getPathname()));

    $lines = explode("\n", $content);
    foreach ($lines as $i => $line) {
        $lineNo = $i + 1;

        // Look for: ] [ ' - suggests double-$
        if (strpos($line, '$$') !== false) {
            $bugs[] = "$relPath:$lineNo (double $) - " . trim($line);
        }

        // Look for: $foo[ 'bar' ] -  missing $ on inner
        if (preg_match("/\[\s*['\"]/", $line)) {
            // Make sure no $$ was just a comment
            $clean = preg_replace('/<!--.*?-->/', '', $line);
            $clean = preg_replace('/\/\/.*/', '', $clean);
            // Look for patterns like: $foo[ 'bar' ]  (with extra space)
            if (preg_match('/(\$[a-zA-Z_][a-zA-Z0-9_]*)?\[\s+[\'"]/', $clean)) {
                $bugs[] = "$relPath:$lineNo (space in bracket) - " . trim($line);
            }
        }
    }
}

echo "Found " . count($bugs) . " suspicious patterns in views\n\n";
foreach (array_slice($bugs, 0, 30) as $b) echo "  $b\n";?>