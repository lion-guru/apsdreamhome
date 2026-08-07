<?php
$file = 'C:\\xampp\\htdocs\\apsdreamhome\\logs\\php_error.log';
if (file_exists($file)) {
    $lines = file($file);
    $last_lines = array_slice($lines, -50);
    echo implode("", $last_lines);
} else {
    echo "File not found.";
}
