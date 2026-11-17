<?php
header('Content-Type: text/plain');
$files = [
    'c:/xampp/htdocs/apsdreamhome/restore.php',
    'c:/xampp/htdocs/apsdreamhome/public/restore.php',
    'c:/xampp/htdocs/apsdreamhome/public/test_restore.php',
    'c:/xampp/htdocs/apsdreamhome/public/cleanup.php'
];
foreach ($files as $file) {
    $real = realpath($file);
    if ($real && file_exists($real)) {
        if (unlink($real)) {
            echo "Successfully deleted: $real\n";
        } else {
            echo "Failed to delete: $real\n";
        }
    } else {
        echo "Not found or invalid: $file (resolved: " . ($real ?: 'false') . ")\n";
    }
}?>