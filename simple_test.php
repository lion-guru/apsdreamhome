<?php
/**
 * DEPRECATED: This is a redundant basic configuration test
 * Use comprehensive_system_test.php for full testing
 * or basic_test.php for simple PHP functionality checks
 */

// Test constants
echo "APP_ROOT: " . (defined('APP_ROOT') ? APP_ROOT : 'NOT DEFINED') . "\n";

// Test if config directory exists
$configDir = __DIR__ . '/config';
echo "Config dir exists: " . (is_dir($configDir) ? 'YES' : 'NO') . "\n";

// Test if database.php exists
$dbFile = $configDir . '/database.php';
echo "Database config exists: " . (file_exists($dbFile) ? 'YES' : 'NO') . "\n";

// List config files
echo "Config files:\n";
$files = scandir($configDir);
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
        echo "  - $file\n";
    }
}
?>
