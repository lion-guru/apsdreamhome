<?php
/**
 * Max Level Deep Scan for APS Dream Home Project
 * Identifies duplicates, pathing issues, configuration errors
 */

echo "🔍 MAX LEVEL DEEP SCAN STARTING...\n";
echo str_repeat("=", 60) . "\n\n";

$projectPath = 'C:/xampp/htdocs/apsdreamhome';

// Scan 1: Duplicate Routes Analysis
echo "1. 📋 SCANNING DUPLICATE ROUTES...\n";
$routesFile = $projectPath . '/routes/web.php';
if (file_exists($routesFile)) {
    $routesContent = file_get_contents($routesFile);
    $routes = [];
    preg_match_all('/\$router->(get|post|any|put|delete|patch)\([\'"](.*?)[\'"]\s*,/', $routesContent, $matches);
    
    foreach ($matches[2] as $index => $route) {
        $method = $matches[1][$index];
        if (!isset($routes[$route])) {
            $routes[$route] = [];
        }
        $routes[$route][] = $method;
    }
    
    $duplicateRoutes = [];
    foreach ($routes as $route => $methods) {
        if (count($methods) > 1) {
            $duplicateRoutes[$route] = $methods;
        }
    }
    
    if (empty($duplicateRoutes)) {
        echo "✅ No duplicate routes found\n";
    } else {
        echo "❌ Found " . count($duplicateRoutes) . " duplicate routes:\n";
        foreach ($duplicateRoutes as $route => $methods) {
            echo "   - $route: " . implode(', ', $methods) . "\n";
        }
    }
} else {
    echo "❌ routes/web.php not found\n";
}

echo "\n";

// Scan 2: Duplicate Controllers Analysis
echo "2. 📂 SCANNING DUPLICATE CONTROLLERS...\n";
$controllersPath = $projectPath . '/app/Http/Controllers';
$controllerFiles = [];

if (is_dir($controllersPath)) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($controllersPath));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() == 'php') {
            $fileName = $file->getFilename();
            $relativePath = str_replace($projectPath . '/', '', $file->getPathname());
            
            if (!isset($controllerFiles[$fileName])) {
                $controllerFiles[$fileName] = [];
            }
            $controllerFiles[$fileName][] = $relativePath;
        }
    }
    
    $duplicateControllers = [];
    foreach ($controllerFiles as $fileName => $paths) {
        if (count($paths) > 1) {
            $duplicateControllers[$fileName] = $paths;
        }
    }
    
    if (empty($duplicateControllers)) {
        echo "✅ No duplicate controllers found\n";
        echo "   Total controllers: " . count($controllerFiles) . "\n";
    } else {
        echo "❌ Found " . count($duplicateControllers) . " duplicate controllers:\n";
        foreach ($duplicateControllers as $fileName => $paths) {
            echo "   - $fileName:\n";
            foreach ($paths as $path) {
                echo "     $path\n";
            }
        }
    }
} else {
    echo "❌ Controllers directory not found\n";
}

echo "\n";

// Scan 3: Duplicate Views Analysis
echo "3. 🎨 SCANNING DUPLICATE VIEWS...\n";
$viewsPath = $projectPath . '/app/views';
$viewFiles = [];

if (is_dir($viewsPath)) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsPath));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() == 'php') {
            $fileName = $file->getFilename();
            $relativePath = str_replace($projectPath . '/', '', $file->getPathname());
            
            if (!isset($viewFiles[$fileName])) {
                $viewFiles[$fileName] = [];
            }
            $viewFiles[$fileName][] = $relativePath;
        }
    }
    
    $duplicateViews = [];
    foreach ($viewFiles as $fileName => $paths) {
        if (count($paths) > 1) {
            $duplicateViews[$fileName] = $paths;
        }
    }
    
    if (empty($duplicateViews)) {
        echo "✅ No duplicate views found\n";
        echo "   Total views: " . count($viewFiles) . "\n";
    } else {
        echo "❌ Found " . count($duplicateViews) . " duplicate views:\n";
        foreach ($duplicateViews as $fileName => $paths) {
            echo "   - $fileName:\n";
            foreach ($paths as $path) {
                echo "     $path\n";
            }
        }
    }
} else {
    echo "❌ Views directory not found\n";
}

echo "\n";

// Scan 4: Broken/Orphaned Files
echo "4. 🔍 SCANNING BROKEN/ORPHANED FILES...\n";
$brokenFiles = [];
$commonIncludes = [
    'includes/config.php',
    'includes/db_connection.php',
    'init.php',
    'header.php',
    'footer.php'
];

if (is_dir($viewsPath)) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsPath));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() == 'php') {
            $content = file_get_contents($file->getPathname());
            foreach ($commonIncludes as $include) {
                if (strpos($content, $include) !== false) {
                    if (!file_exists($projectPath . '/' . $include)) {
                        $brokenFiles[] = str_replace($projectPath . '/', '', $file->getPathname()) . " (missing: $include)";
                    }
                }
            }
        }
    }
}

if (empty($brokenFiles)) {
    echo "✅ No broken include files found\n";
} else {
    echo "❌ Found " . count($brokenFiles) . " files with broken includes:\n";
    foreach ($brokenFiles as $file) {
        echo "   - $file\n";
    }
}

echo "\n";

// Scan 5: Database Connection Test
echo "5. 🗄️ TESTING DATABASE CONNECTION...\n";
try {
    $host = '127.0.0.1';
    $port = 3307;
    $dbname = 'apsdreamhome';
    $username = 'root';
    $password = '';
    
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connection successful\n";
    echo "   Host: $host:$port\n";
    echo "   Database: $dbname\n";
    
    // Check if key tables exist
    $keyTables = ['users', 'customers', 'properties', 'deals', 'invoices'];
    foreach ($keyTables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->fetch()) {
            echo "   ✅ Table '$table' exists\n";
        } else {
            echo "   ❌ Table '$table' missing\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}

echo "\n";

// Scan 6: Configuration Files Analysis
echo "6. ⚙️ CHECKING CONFIGURATION FILES...\n";
$configFiles = [
    '.env' => 'Environment configuration',
    'config.php' => 'Main configuration',
    'app/config/config.php' => 'App configuration',
    'app/Core/Config.php' => 'Core configuration'
];

foreach ($configFiles as $file => $description) {
    if (file_exists($projectPath . '/' . $file)) {
        echo "✅ $file exists ($description)\n";
    } else {
        echo "❌ $file missing ($description)\n";
    }
}

echo "\n";

// Scan 7: Path Issues in Views
echo "7. 📁 CHECKING PATH ISSUES IN VIEWS...\n";
$pathIssues = [];

if (is_dir($viewsPath)) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsPath));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() == 'php') {
            $content = file_get_contents($file->getPathname());
            $relativePath = str_replace($projectPath . '/', '', $file->getPathname());
            
            // Check for wrong include patterns
            if (strpos($content, 'require_once') !== false || strpos($content, 'include_once') !== false) {
                preg_match_all('/require_once\s+[\'"](.*?)[\'"]|include_once\s+[\'"](.*?)[\'"]/', $content, $matches);
                if (!empty($matches[1])) {
                    foreach ($matches[1] as $includePath) {
                        // Check if the included file exists
                        if ($includePath[0] != '/' && !file_exists(dirname($file->getPathname()) . '/' . $includePath)) {
                            $pathIssues[] = "$relativePath (broken: $includePath)";
                        }
                    }
                }
            }
        }
    }
}

if (empty($pathIssues)) {
    echo "✅ No path issues found in views\n";
} else {
    echo "❌ Found " . count($pathIssues) . " path issues:\n";
    foreach ($pathIssues as $issue) {
        echo "   - $issue\n";
    }
}

echo "\n";

// Scan 8: Missing Views Referenced by Controllers
echo "8. 🔍 CHECKING MISSING VIEWS REFERENCED BY CONTROLLERS...\n";
$controllersPath = $projectPath . '/app/Http/Controllers';
$missingViews = [];

if (is_dir($controllersPath)) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($controllersPath));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() == 'php') {
            $content = file_get_contents($file->getPathname());
            
            // Look for render() calls
            preg_match_all("/render\s*\(\s*['\"](.*?)['\"]\s*,/", $content, $renderMatches);
            if (!empty($renderMatches[1])) {
                foreach ($renderMatches[1] as $viewPath) {
                    // Convert dot notation to path
                    $viewFile = str_replace('.', '/', $viewPath) . '.php';
                    $fullPath = $projectPath . '/app/views/' . $viewFile;
                    
                    if (!file_exists($fullPath)) {
                        $missingViews[] = $viewPath . " (referenced in: " . $file->getFilename() . ")";
                    }
                }
            }
        }
    }
}

if (empty($missingViews)) {
    echo "✅ No missing views found\n";
} else {
    echo "❌ Found " . count($missingViews) . " missing views:\n";
    foreach ($missingViews as $view) {
        echo "   - $view\n";
    }
}

echo "\n";

// Scan 9: Database Schema Consistency
echo "9. 🗄️ CHECKING DATABASE SCHEMA CONSISTENCY...\n";
try {
    $host = '127.0.0.1';
    $port = 3307;
    $dbname = 'apsdreamhome';
    $username = 'root';
    $password = '';
    
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all tables
    $tables = $conn->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
    echo "   Total tables: " . count($tables) . "\n";
    
    // Check for empty tables
    $emptyTables = [];
    foreach ($tables as $table) {
        $result = $conn->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        if ($result == 0) {
            $emptyTables[] = $table;
        }
    }
    
    if (empty($emptyTables)) {
        echo "✅ All tables have data\n";
    } else {
        echo "⚠️ " . count($emptyTables) . " empty tables:\n";
        foreach ($emptyTables as $table) {
            echo "   - $table\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Database schema check failed: " . $e->getMessage() . "\n";
}

echo "\n";

// Final Summary
echo str_repeat("=", 60) . "\n";
echo "📊 DEEP SCAN SUMMARY\n";
echo str_repeat("=", 60) . "\n";

$issuesFound = count($duplicateRoutes) + count($duplicateControllers) + count($duplicateViews) + count($brokenFiles) + count($pathIssues) + count($missingViews) + count($emptyTables);

if ($issuesFound == 0) {
    echo "✅ NO ISSUES FOUND - Project is healthy!\n";
} else {
    echo "⚠️ TOTAL ISSUES FOUND: $issuesFound\n";
    echo "   Duplicate routes: " . count($duplicateRoutes) . "\n";
    echo "   Duplicate controllers: " . count($duplicateControllers) . "\n";
    echo "   Duplicate views: " . count($duplicateViews) . "\n";
    echo "   Broken includes: " . count($brokenFiles) . "\n";
    echo "   Path issues: " . count($pathIssues) . "\n";
    echo "   Missing views: " . count($missingViews) . "\n";
    echo "   Empty tables: " . count($emptyTables) . "\n";
}

echo "\n💡 RECOMMENDATIONS:\n";
if (!empty($duplicateRoutes)) {
    echo "1. Remove duplicate routes from web.php\n";
}
if (!empty($duplicateControllers)) {
    echo "2. Remove duplicate controllers\n";
}
if (!empty($duplicateViews)) {
    echo "3. Remove duplicate view files\n";
}
if (!empty($brokenFiles)) {
    echo "4. Fix broken include references\n";
}
if (!empty($pathIssues)) {
    echo "5. Fix file path issues in views\n";
}
if (!empty($missingViews)) {
    echo "6. Create missing view files\n";
}

echo "\n🎉 DEEP SCAN COMPLETE!\n";
