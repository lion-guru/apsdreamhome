<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');

$before = $pdo->query('SHOW TABLES')->rowCount();

// farmers_legacy: 1 row, 2 code refs, 0 FKs
// Check if refs are in try/catch
$file = 'app';
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($file));
$pattern = '/\b(FROM|JOIN|INTO|UPDATE)\s+`?farmers_legacy`?/i';
$refs = [];
foreach ($iter as $f) {
    if (!$f->isFile() || $f->getExtension() !== 'php') continue;
    $lines = file($f->getPathname());
    foreach ($lines as $i => $line) {
        if (preg_match($pattern, $line)) {
            $inTry = false;
            for ($j = max(0, $i - 5); $j < $i; $j++) {
                if (preg_match('/try\s*\{/', $lines[$j])) { $inTry = true; break; }
            }
            $refs[] = ['file' => str_replace('C:\\xampp\\htdocs\\apsdreamhome\\', '', $f->getPathname()), 'line' => $i + 1, 'inTry' => $inTry, 'content' => trim($line)];
        }
    }
}

echo "farmers_legacy references:\n";
foreach ($refs as $r) {
    echo "  {$r['file']}:{$r['line']} " . ($r['inTry'] ? '[try/catch]' : '[UNPROTECTED]') . "\n";
    echo "    " . $r['content'] . "\n";
}
