<?php
/**
 * Testing Suite Index
 */

echo '<h1>Testing Suite Index</h1>';
echo '<p>Directory: testing</p>';

// List all PHP files in this directory
 = glob('*.php');
foreach ($files as $file) {
    if ($file !== 'index.php') {
        echo '<a href="' . $file . '">' . $file . '</a><br>';
    }
}
?>
