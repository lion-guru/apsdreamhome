<?php
$content = file_get_contents(dirname(__DIR__) . '/routes/web.php');
$lines = explode("\n", $content);

// Check exact bytes of line 34 (the first real route)
$line34 = $lines[33];
echo "Line 34 raw: " . $line34 . "\n";
echo "Line 34 hex: " . bin2hex(substr($line34, 0, 80)) . "\n";

// Test different regex patterns
$patterns = [
    "simple" => "/'([^']+)'@(\w+)/",
    "escaped" => "/'([^'\\\\]*(?:\\\\.[^'\\\\]*)*)'@(\w+)/",
    "anything" => "/'([^']*)\\\\\\\\([^']*)'@(\w+)/",
    "raw" => "/(\w+(?:\\\\\\\\)+\w+)@(\w+)/",
    "simple2" => "/(\w+\\\\\\\\\w+)@(\w+)/",
];

foreach ($patterns as $name => $p) {
    if (preg_match_all($p, $line34, $m)) {
        echo "Pattern '$name': MATCHED " . count($m[0]) . " times\n";
        echo "  m[0][0]=" . $m[0][0] . "\n";
        echo "  m[1][0]=" . $m[1][0] . "\n";
        if (isset($m[2][0])) echo "  m[2][0]=" . $m[2][0] . "\n";
    } else {
        echo "Pattern '$name': NO MATCH\n";
    }
}

// Now test the simple pattern on ALL lines
echo "\n--- Testing simple pattern on full file ---\n";
$allMatches = [];
foreach ($lines as $i => $line) {
    if (preg_match_all("/'([^']+)'@(\w+)/", $line, $m)) {
        foreach ($m[0] as $idx) {
            $full = $m[1][$idx];
            $method = $m[2][$idx];
            $parts = explode('\\\\', $full);
            $short = end($parts);
            $allMatches[] = $short . '::' . $method;
        }
    }
}
echo "Total matches: " . count($allMatches) . "\n";
echo "First 5: " . implode(', ', array_slice($allMatches, 0, 5)) . "\n";
echo "Unique classes: " . count(array_unique(array_map(function($m) { return explode('::', $m)[0]; }, $allMatches))) . "\n";
