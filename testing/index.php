<?php

/**
 * APS Dream Home - Testing Suite Index
 */

echo '<h1>🧪 APS Dream Home Testing Suite</h1>';
echo '<p>Directory: testing</p>';

// List all PHP files in this directory
$files = glob('*.php');
foreach ($files as $file) {
    if ($file !== 'index.php') {
        echo '<a href="' . $file . '">' . $file . '</a><br>';
    }
}

echo '<hr>';
echo '<h2>🚀 Quick Links</h2>';
echo '<a href="dashboard.php">📊 Testing Dashboard</a><br>';
echo '<a href="unit/">Unit Tests</a><br>';
echo '<a href="integration/">Integration Tests</a><br>';
echo '<a href="system/">System Tests</a><br>';
echo '<a href="database/">Database Tests</a><br>';
echo '<a href="checks/">Health Checks</a><br>';
