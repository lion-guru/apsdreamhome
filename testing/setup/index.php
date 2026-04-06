<?php
/**
 * Setup Scripts Index
 */

echo '<h1>Setup Scripts Index</h1>';
echo '<p>Directory: testing/setup</p>';

// List all PHP files in this directory
 = glob('*.php');
foreach ($files as $file) {
    if ($file !== 'index.php') {
        echo '<a href="' . $file . '">' . $file . '</a><br>';
    }
}
?>
