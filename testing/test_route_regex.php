<?php
$line = file_get_contents(dirname(__DIR__) . '/routes/web.php');
// Show first few route lines
$lines = explode("\n", $line);
$count = 0;
foreach ($lines as $i => $l) {
    if (strpos($l, '$router->') !== false || strpos($l, 'Route::') !== false) {
        echo "Line " . ($i+1) . ": " . trim($l) . "\n";
        $count++;
        if ($count >= 10) break;
    }
}

echo "\n--- Testing regex against actual route format ---\n";
$testLine = "\$router->get('/', 'Front\\\\PageController@home');";
echo "Test line: $testLine\n";

// Try various patterns
$patterns = [
    "/['\"]([A-Za-z\\\\]+(?:Controller|Service|Repository))@(\w+)['\"]/",
    "/['\"]([A-Za-z\\\\\\\\]+(?:Controller))@(\w+)['\"]/",
    "/(\w+\\\\\w+)@(\w+)/",
    "/([A-Z]\w*(?:Controller))@(\w+)/",
];

foreach ($patterns as $p) {
    echo "Pattern: $p\n";
    if (preg_match_all($p, $testLine, $m)) {
        echo "  MATCH: class=" . $m[1][0] . " method=" . $m[2][0] . "\n";
    } else {
        echo "  NO MATCH\n";
    }
}
