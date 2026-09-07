<?php
/**
 * Debug route parsing
 */
$projectRoot = dirname(__DIR__);
$content = file_get_contents($projectRoot . '/routes/web.php');
$lines = explode("\n", $content);

$matchCount = 0;
$sampleMatches = [];

foreach ($lines as $lineNum => $line) {
    $trimmed = trim($line);
    if (strpos($trimmed, '//') === 0) continue;
    
    // Pattern: 'Namespace\\Controller@method' or Namespace\Controller@method
    if (preg_match_all("/['\"]([A-Za-z_\\\\]+@(\w+))['\"]/", $trimmed, $m)) {
        $matchCount++;
        if ($matchCount <= 10) {
            $sampleMatches[] = "Line " . ($lineNum+1) . ": pattern=" . $m[1][0] . " | class_part=" . preg_replace('/@\w+$/', '', $m[1][0]) . " method=" . $m[2][0];
        }
    }
}

echo "Total matches in web.php: $matchCount\n\n";
foreach ($sampleMatches as $s) echo $s . "\n";

echo "\n--- Now testing class extraction ---\n";
$test = 'Front\\PageController@home';
echo "Input: $test\n";
if (preg_match("/([A-Za-z_\\\\]+)@(\w+)/", $test, $m)) {
    echo "Full match: " . $m[0] . "\n";
    echo "Class part: " . $m[1] . "\n";
    echo "Method part: " . $m[2] . "\n";
    
    // Extract short class name
    $parts = explode('\\', $m[1]);
    echo "Short class: " . end($parts) . "\n";
}
