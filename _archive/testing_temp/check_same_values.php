<?php
$en = []; $hi = [];
require __DIR__ . '/../lang/en.php';
require __DIR__ . '/../lang/hi.php';

$same = [];
foreach ($en as $k => $v) {
    if (isset($hi[$k]) && $hi[$k] === $v && strlen($v) > 3 && !preg_match('/^[0-9â‚¹$%. ,]+$/', $v)) {
        $same[] = $k . ' => ' . substr($v, 0, 60);
    }
}
echo "Identical en/hi values: " . count($same) . "\n";
foreach ($same as $s) echo "  $s\n";?>