<?php
/**
 * Health Checks Index
 */

echo '<h1>Health Checks Index</h1>';
echo '<p>Directory: testing/checks</p>';

// List all PHP files in this directory
 = glob('*.php');
foreach ($files as $file) {
    if ($file !== 'index.php') {
        echo '<a href="' . $file . '">' . $file . '</a><br>';
    }
}
?>
