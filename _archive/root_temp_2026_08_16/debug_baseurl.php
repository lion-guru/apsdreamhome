<?php
require_once 'vendor/autoload.php';
require_once 'app/Core/Database/Database.php';

// Check if BASE_URL is defined
echo "BASE_URL defined: " . (defined('BASE_URL') ? 'YES' : 'NO') . "\n";
if (defined('BASE_URL')) {
    echo "BASE_URL value: " . BASE_URL . "\n";
}

// Check if bootstrap.php defines it
require_once 'config/bootstrap.php';

echo "After bootstrap.php:\n";
echo "BASE_URL defined: " . (defined('BASE_URL') ? 'YES' : 'NO') . "\n";
if (defined('BASE_URL')) {
    echo "BASE_URL value: " . BASE_URL . "\n";
}