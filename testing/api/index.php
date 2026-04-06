<?php

/**
 * API Tests Index
 */

echo '<h1>API Tests Index</h1>';
echo '<p>Directory: testing/api</p>';

// List all PHP files in this directory
$files = glob('*.php');
foreach ($files as $file) {
    if ($file !== 'index.php') {
        echo '<a href="' . $file . '">' . $file . '</a><br>';
    }
}
