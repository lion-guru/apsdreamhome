<?php
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
$bugs = [];
foreach ($iter as $f) {
    if (!$f->isFile() || $f->getExtension() !== 'php') continue;
    $content = file_get_contents($f->getPathname());
    $relPath = str_replace('\\', '/', str_replace(__DIR__ . '/../', '', $f->getPathname()));

    // Find pattern: [key] without leading $
    // e.g., $arr['foo'] is fine, but arr['foo'] or $arr[bar] is a bug
    // Pattern: (non-$) word followed by [ followed by single-quoted string
    $lines = explode("\n", $content);
    foreach ($lines as $i => $line) {
        $lineNo = $i + 1;
        // Skip comments
        $trimmed = trim($line);
        if (str_starts_with($trimmed, '//') || str_starts_with($trimmed, '#') || str_starts_with($trimmed, '*')) continue;

        // Match patterns like "stats['key']" without $
        // Or "foo[bar]" (no quotes)
        if (preg_match("/(stats|rows|data|results|item|items|row|records|config|settings|options|props|params|args|req|res|response|request|user|users|page|pages|user_data|input|outputs)\[(\"|')/i", $line, $m)) {
            // Check it's not preceded by $
            $pos = strpos($line, $m[0]);
            if ($pos > 0 && $line[$pos-1] !== '$') {
                $bugs[] = "$relPath:$lineNo - " . trim($line);
            }
        }
    }
}

echo "Found " . count($bugs) . " potential missing \$ prefix bugs\n\n";
foreach (array_slice($bugs, 0, 30) as $b) echo "  $b\n";?>