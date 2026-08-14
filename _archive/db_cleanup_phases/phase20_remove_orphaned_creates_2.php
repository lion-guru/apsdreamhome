<?php
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$existingTables = array_flip($pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN));

$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/../app'));
$fixed = 0;
$filesFixed = 0;

foreach ($iter as $f) {
    if (!$f->isFile() || $f->getExtension() !== 'php') continue;
    $path = $f->getPathname();
    $content = file_get_contents($path);
    $original = $content;

    // Find all CREATE TABLE IF NOT EXISTS blocks for missing tables
    $pattern = '/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+`?(\w+)`?/i';
    if (!preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) continue;

    // Process in reverse order to preserve offsets
    $removals = [];
    foreach (array_reverse($matches[0]) as $idx => $match) {
        $tableName = $matches[1][$idx][0];
        if (isset($existingTables[$tableName])) continue;

        $startPos = $match[1];
        $depth = 0;
        $endPos = $startPos;
        $inString = false;
        for ($i = strpos($content, '(', $startPos); $i < strlen($content); $i++) {
            $c = $content[$i];
            if ($c === "'" && $content[$i-1] !== '\\') { $inString = !$inString; continue; }
            if (in_array($c, ['"', '`']) && $content[$i-1] !== '\\') { $inString = !$inString; continue; }
            if ($inString) continue;
            if ($c === '(') $depth++;
            if ($c === ')') { $depth--; if ($depth === 0) { $endPos = $i + 1; break; } }
        }
        while ($endPos < strlen($content) && in_array($content[$endPos], [';', ' ', "\n", "\r", "\t"])) $endPos++;
        $lineStart = strrpos($content, "\n", $startPos - 1) + 1;
        if (preg_match('/\/\/.*$|\/\*.*\*\//s', substr($content, $lineStart, $startPos - $lineStart))) {
            $startPos = $lineStart;
        }

        $removals[] = ['start' => $startPos, 'end' => $endPos, 'table' => $tableName];
    }

    if (empty($removals)) continue;

    foreach ($removals as $r) {
        $content = substr_replace($content, '', $r['start'], $r['end'] - $r['start']);
        $fixed++;
    }

    if ($content !== $original) {
        file_put_contents($path, $content);
        $relPath = str_replace('\\', '/', str_replace(__DIR__ . '/../', '', $path));
        $tables = array_map(fn($r) => $r['table'], $removals);
        echo "FIXED: $relPath (" . implode(', ', $tables) . ")\n";
        $filesFixed++;
    }
}

echo "\nRemoved $fixed orphaned CREATE TABLE statements from $filesFixed files\n";?>