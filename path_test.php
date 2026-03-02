<?php
/**
 * APS Dream Home - Path Analysis Test
 * Analyze current path structure and identify BASE_PATH issues
 */

echo "<h2>🔍 Path Analysis</h2>";

echo "<h3>📁 Current Path Information</h3>";
echo "<p><strong>__DIR__:</strong> " . __DIR__ . "</p>";
echo "<p><strong>dirname(__DIR__):</strong> " . dirname(__DIR__) . "</p>";
echo "<p><strong>realpath(__DIR__ . '/..'):</strong> " . realpath(__DIR__ . '/..') . "</p>";
echo "<p><strong>realpath(dirname(__DIR__)):</strong> " . realpath(dirname(__DIR__)) . "</p>";

echo "<h3>🌐 Server Information</h3>";
echo "<p><strong>DOCUMENT_ROOT:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Not set') . "</p>";
echo "<p><strong>SCRIPT_NAME:</strong> " . ($_SERVER['SCRIPT_NAME'] ?? 'Not set') . "</p>";
echo "<p><strong>REQUEST_URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'Not set') . "</p>";
echo "<p><strong>HTTP_HOST:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'Not set') . "</p>";

echo "<h3>🔍 Autoloader Path Tests</h3>";
$paths = array(
    'Standard dirname(__DIR__)' => dirname(__DIR__) . '/app/core/autoload.php',
    'Realpath method' => realpath(__DIR__ . '/..') . '/app/core/autoload.php',
    'Document root method' => $_SERVER['DOCUMENT_ROOT'] . '/app/core/autoload.php',
    'Manual path' => 'C:/xampp/htdocs/apsdreamhome/app/core/autoload.php'
);

foreach ($paths as $method => $path) {
    $exists = file_exists($path) ? 'EXISTS ✅' : 'NOT FOUND ❌';
    $color = file_exists($path) ? 'green' : 'red';
    echo "<p style='color: $color;'><strong>$method:</strong> $path - $exists</p>";
}

echo "<h3>🔍 App Class Path Tests</h3>";
$appPaths = array(
    'Standard dirname(__DIR__)' => dirname(__DIR__) . '/app/core/App.php',
    'Realpath method' => realpath(__DIR__ . '/..') . '/app/core/App.php',
    'Document root method' => $_SERVER['DOCUMENT_ROOT'] . '/app/core/App.php',
    'Manual path' => 'C:/xampp/htdocs/apsdreamhome/app/core/App.php'
);

foreach ($appPaths as $method => $path) {
    $exists = file_exists($path) ? 'EXISTS ✅' : 'NOT FOUND ❌';
    $color = file_exists($path) ? 'green' : 'red';
    echo "<p style='color: $color;'><strong>$method:</strong> $path - $exists</p>";
}

echo "<h3>🔍 Config File Tests</h3>";
$configFiles = array(
    'database.php' => 'config/database.php',
    'app.php' => 'config/app.php'
);

foreach ($configFiles as $file => $path) {
    $fullPath = dirname(__DIR__) . '/' . $path;
    $exists = file_exists($fullPath) ? 'EXISTS ✅' : 'NOT FOUND ❌';
    $color = file_exists($fullPath) ? 'green' : 'red';
    echo "<p style='color: $color;'><strong>$file:</strong> $fullPath - $exists</p>";
}

echo "<h3>🔍 Suggested BASE_PATH Values</h3>";
$suggestedPaths = array(
    'dirname(__DIR__)' => dirname(__DIR__),
    'realpath(__DIR__ . "/..")' => realpath(__DIR__ . '/..'),
    'realpath(dirname(__DIR__))' => realpath(dirname(__DIR__)),
    'Manual path' => 'C:/xampp/htdocs/apsdreamhome'
);

foreach ($suggestedPaths as $method => $path) {
    $hasAutoloader = file_exists($path . '/app/core/autoload.php');
    $hasAppClass = file_exists($path . '/app/core/App.php');
    $status = ($hasAutoloader && $hasAppClass) ? 'VALID ✅' : 'INVALID ❌';
    $color = ($hasAutoloader && $hasAppClass) ? 'green' : 'red';
    echo "<p style='color: $color;'><strong>$method:</strong> $path - $status</p>";
}

echo "<h3>🔍 Directory Structure</h3>";
$baseDir = dirname(__DIR__);
if (is_dir($baseDir)) {
    echo "<p><strong>Base directory:</strong> $baseDir</p>";
    $items = scandir($baseDir);
    echo "<p><strong>Contents:</strong></p>";
    echo "<ul>";
    foreach ($items as $item) {
        if ($item !== '.' && $item !== '..') {
            $path = $baseDir . '/' . $item;
            $type = is_dir($path) ? 'DIR' : 'FILE';
            echo "<li><strong>$item</strong> [$type]</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'><strong>Base directory not found:</strong> $baseDir</p>";
}

echo "<hr>";
echo "<p><strong>🎯 Path Analysis Complete!</strong></p>";
echo "<p><small>Use this information to fix BASE_PATH calculation in public/index.php</small></p>";
?>
