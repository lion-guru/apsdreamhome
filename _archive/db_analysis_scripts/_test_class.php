<?php
// Sample test
$r = "App\\\\Http\\\\Controllers\\\\Front\\\\BlogController@index";
echo "Test: $r\n";
echo "Class: " . explode('@', $r)[0] . "\n";
$class = explode('@', $r)[0];
echo "After str_replace: " . str_replace('\\\\', '\\', $class) . "\n";
echo "Path: " . str_replace('\\', '/', str_replace('\\\\', '\\', $class)) . ".php\n";?>