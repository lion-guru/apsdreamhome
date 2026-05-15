<?php

/**
 * Test Reports Index
 */

echo '<h1>Test Reports Index</h1>';
echo '<p>Directory: testing/reports</p>';

// List all PHP files in this directory
$files = glob('*.php');
foreach ($files as $file) {
    if ($file !== 'index.php') {
        echo '<a href="' . $file . '">' . $file . '</a><br>';
    }
}
