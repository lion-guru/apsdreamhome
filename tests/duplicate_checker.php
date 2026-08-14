<?php
/**
 * Duplicate Checker - Find duplicate files, routes, and functions
 */

echo "â•”â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•—\n";
echo "â•‘              DUPLICATE CHECKER - APS DREAM HOME                â•‘\n";
echo "â•šâ•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�\n\n";

$issues = [];

// 1. Check for duplicate route definitions
echo "ðŸ”� Checking for Duplicate Routes...\n";
$routesFile = __DIR__ . '/../routes/web.php';
if (file_exists($routesFile)) {
    $content = file_get_contents($routesFile);
    
    // Extract all route patterns
    preg_match_all('/\$router->(get|post|put|delete)\s*\(\s*[\'"]([^\'"]+)[\'"]/', $content, $matches);
    
    $routes = $matches[2];
    $duplicates = array_diff_assoc($routes, array_unique($routes));
    
    if (!empty($duplicates)) {
        echo "â�Œ Found " . count($duplicates) . " duplicate routes:\n";
        foreach (array_unique($duplicates) as $route) {
            echo "   - $route\n";
            $issues[] = "Duplicate route: $route";
        }
    } else {
        echo "âœ… No duplicate routes found\n";
    }
}

// 2. Check for duplicate function definitions
echo "\nðŸ”� Checking for Duplicate Functions...\n";
$functionFiles = [
    __DIR__ . '/../app/Helpers/functions.php',
    __DIR__ . '/../app/Core/Helper.php'
];

$definedFunctions = [];
foreach ($functionFiles as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        preg_match_all('/function\s+(\w+)\s*\(/', $content, $matches);
        foreach ($matches[1] as $func) {
            if (in_array($func, $definedFunctions)) {
                echo "â�Œ Duplicate function: $func\n";
                $issues[] = "Duplicate function: $func";
            } else {
                $definedFunctions[] = $func;
            }
        }
    }
}
echo "âœ… Checked " . count($definedFunctions) . " functions\n";

// 3. Check for duplicate view files
echo "\nðŸ”� Checking for Duplicate View Files...\n";
$viewsDir = __DIR__ . '/../app/views';
$viewFiles = [];

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir));
foreach ($rii as $file) {
    if ($file->isDir()) continue;
    if ($file->getExtension() !== 'php') continue;
    
    $filename = $file->getFilename();
    if (isset($viewFiles[$filename])) {
        echo "â�Œ Duplicate view file: $filename\n";
        echo "   - " . $viewFiles[$filename] . "\n";
        echo "   - " . $file->getPathname() . "\n";
        $issues[] = "Duplicate view: $filename";
    } else {
        $viewFiles[$filename] = $file->getPathname();
    }
}
echo "âœ… Checked " . count($viewFiles) . " view files\n";

// 4. Check for duplicate service classes
echo "\nðŸ”� Checking for Duplicate Service Names...\n";
$servicesDir = __DIR__ . '/../app/Services';
$serviceClasses = [];

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($servicesDir));
foreach ($rii as $file) {
    if ($file->isDir()) continue;
    if ($file->getExtension() !== 'php') continue;
    
    $filename = $file->getFilename();
    if (isset($serviceClasses[$filename])) {
        echo "â�Œ Duplicate service: $filename\n";
        echo "   - " . $serviceClasses[$filename] . "\n";
        echo "   - " . $file->getPathname() . "\n";
        $issues[] = "Duplicate service: $filename";
    } else {
        $serviceClasses[$filename] = $file->getPathname();
    }
}
echo "âœ… Checked " . count($serviceClasses) . " services\n";

// 5. Check for duplicate table definitions
echo "\nðŸ”� Checking Migration Files for Duplicate Table Names...\n";
$migrationsDir = __DIR__ . '/../database/migrations';
$tableNames = [];

if (is_dir($migrationsDir)) {
    foreach (glob($migrationsDir . '/*.php') as $file) {
        $content = file_get_contents($file);
        preg_match_all('/CREATE TABLE(?: IF NOT EXISTS)?\s+(\w+)/i', $content, $matches);
        foreach ($matches[1] as $table) {
            if (isset($tableNames[$table])) {
                echo "â�Œ Table '$table' defined in multiple migrations:\n";
                echo "   - " . basename($tableNames[$table]) . "\n";
                echo "   - " . basename($file) . "\n";
                $issues[] = "Duplicate table definition: $table";
            } else {
                $tableNames[$table] = $file;
            }
        }
    }
}
echo "âœ… Checked " . count($tableNames) . " table definitions\n";

// Summary
echo "\n";
echo "â•”â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•—\n";
echo "â•‘                      CHECK SUMMARY                             â•‘\n";
echo "â•šâ•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�\n\n";

if (empty($issues)) {
    echo "ðŸŽ‰ NO DUPLICATES FOUND! Codebase is clean.\n";
} else {
    echo "âš ï¸�  Found " . count($issues) . " potential duplicates:\n";
    foreach ($issues as $i => $issue) {
        echo "   " . ($i + 1) . ". $issue\n";
    }
}

echo "\nâœ… Duplicate check complete\n";?>