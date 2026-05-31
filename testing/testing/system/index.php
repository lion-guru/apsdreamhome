<?php

/**
 * System Tests Index
 */

echo '<h1>System Tests Index</h1>';
echo '<p>Directory: testing/system</p>';

// List all PHP files in this directory
$files = glob('*.php');
foreach ($files as $file) {
    if ($file !== 'index.php') {
        echo '<a href="' . $file . '">' . $file . '</a><br>';
    }
}
