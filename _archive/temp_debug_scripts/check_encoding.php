<?php
$file = 'C:\xampp\htdocs\apsdreamhome\app\Services\AdminMenuService.php';
$lines = file($file);

// Check lines around 205 (0-indexed 204)
for ($i = 203; $i <= 210; $i++) {
    $line = $lines[$i] ?? '';
    echo "Line " . ($i+1) . " hex (first 120 chars): " . bin2hex(substr($line, 0, 120)) . "\n";
    echo "Line " . ($i+1) . " raw: " . $line;
    echo "\n";
}
