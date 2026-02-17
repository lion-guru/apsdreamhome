<?php
echo "session.cookie_httponly: " . ini_get('session.cookie_httponly') . "\n";
echo "File permissions check:\n";
$file = dirname(__DIR__, 2) . '/includes/config.php';
echo "Checking $file\n";
if (file_exists($file)) {
    echo "File exists.\n";
    echo "Perms: " . decoct(fileperms($file)) . "\n";
} else {
    echo "File does not exist.\n";
}
