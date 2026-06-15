<?php
$en = include 'C:\xampp\htdocs\apsdreamhome\lang\en.php';
$hi = include 'C:\xampp\htdocs\apsdreamhome\lang\hi.php';

$enKeys = array_keys($en);
$hiKeys = array_keys($hi);

echo "en.php keys: " . count($enKeys) . "\n";
echo "hi.php keys: " . count($hiKeys) . "\n\n";

$missingInHi = array_diff(array_keys($en), array_keys($hi));
echo "Missing in hi.php: " . count($missingInHi) . "\n";
foreach ($missingInHi as $k) echo "  $k\n";