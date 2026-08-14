<?php
require_once __DIR__ . '/../vendor/autoload.php';

$en = []; $hi = [];
require __DIR__ . '/../lang/en.php';
require __DIR__ . '/../lang/hi.php';

// Flatten nested arrays like the test does
function flatten($arr, $prefix = '') {
    $out = [];
    foreach ($arr as $k => $v) {
        $key = $prefix ? $prefix.'.'.$k : $k;
        if (is_array($v)) $out += flatten($v, $key);
        else $out[$key] = $v;
    }
    return $out;
}
$enFlat = flatten($en);
$hiFlat = flatten($hi);

$same = [];
foreach ($enFlat as $k => $v) {
    if (isset($hiFlat[$k]) && $hiFlat[$k] === $v && strlen($v) > 3 && !preg_match('/^[0-9â‚¹$%. ,]+$/', $v)) {
        $same[$k] = $v;
    }
}
echo "Identical en/hi values (flat): " . count($same) . "\n";
foreach ($same as $k => $v) echo "  $k => $v\n";?>