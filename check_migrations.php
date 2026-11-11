<?php
$dir = 'C:\xampp\htdocs\apsdreamhome\database\migrations';
$files = scandir($dir);
$pattern = '/(profile|customer|associate|agent|employee|identity|user)/i';
foreach ($files as $file) {
    if (preg_match($pattern, $file)) {
        echo $file . "\n";
    }
}