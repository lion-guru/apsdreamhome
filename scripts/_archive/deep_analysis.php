<?php
/**
 * Project Deep Analysis Script
 * Scans for duplicates in controllers, models, routes, and database tables
 */

echo "=== APS Dream Home - Deep Analysis Report ===\n\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

// 1. CONTROLLERS
echo "=== 1. CONTROLLERS ===\n\n";
$controllers = glob('app/Http/Controllers/**/*.php');
$controllerNames = [];
$duplicateControllers = [];

foreach ($controllers as $c) {
    $name = basename($c, '.php');
    if (isset($controllerNames[$name])) {
        $duplicateControllers[$name][] = $c;
    } else {
        $controllerNames[$name] = $c;
    }
}

if (!empty($duplicateControllers)) {
    echo "⚠️  DUPLICATE CONTROLLER NAMES:\n";
    foreach ($duplicateControllers as $name => $files) {
        echo "   $name:\n";
        foreach ($files as $f) {
            echo "     - $f\n";
        }
    }
} else {
    echo "✅ No duplicate controller names\n";
}

echo "\n   Total controllers: " . count($controllers) . "\n";

// 2. MODELS
echo "\n=== 2. MODELS ===\n\n";
$models = glob('app/Models/**/*.php');
$modelNames = [];
$duplicateModels = [];

foreach ($models as $m) {
    $name = basename($m, '.php');
    if (isset($modelNames[$name])) {
        $duplicateModels[$name][] = $m;
    } else {
        $modelNames[$name] = $m;
    }
}

if (!empty($duplicateModels)) {
    echo "⚠️  DUPLICATE MODEL NAMES:\n";
    foreach ($duplicateModels as $name => $files) {
        echo "   $name:\n";
        foreach ($files as $f) {
            echo "     - $f\n";
        }
    }
} else {
    echo "✅ No duplicate model names\n";
}

echo "\n   Total models: " . count($models) . "\n";

// 3. DATABASE TABLES
echo "\n=== 3. DATABASE TABLES ===\n\n";
try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "   Total tables: " . count($tables) . "\n";
    
    // Check for similar table names
    $similar = [];
    $keywords = ['lead', 'user', 'customer', 'property', 'plot', 'booking', 'payment', 'inquiry', 'project'];
    
    foreach ($keywords as $kw) {
        $matches = array_filter($tables, fn($t) => stripos($t, $kw) !== false);
        if (count($matches) > 1) {
            $similar[$kw] = $matches;
        }
    }
    
    if (!empty($similar)) {
        echo "\n⚠️  POTENTIAL DUPLICATE TABLES (by keyword):\n";
        foreach ($similar as $kw => $matches) {
            echo "   '$kw': " . implode(', ', $matches) . "\n";
        }
    }
    
    // List all tables
    echo "\n   All tables:\n";
    sort($tables);
    foreach ($tables as $t) {
        echo "     - $t\n";
    }
    
} catch (PDOException $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

// 4. ROUTES - Check for duplicate paths
echo "\n=== 4. ROUTES ===\n\n";
$routeFile = file_get_contents('routes/web.php');
preg_match_all('/\\$router->(get|post|put|delete)\([\'"]([^\'"]+)[\'"]/', $routeFile, $matches, PREG_SET_ORDER);

$routes = [];
$duplicateRoutes = [];

foreach ($matches as $m) {
    $method = $m[1];
    $path = $m[2];
    $key = strtoupper($method) . ':' . $path;
    
    if (isset($routes[$key])) {
        if (!isset($duplicateRoutes[$key])) {
            $duplicateRoutes[$key] = [$routes[$key]];
        }
        $duplicateRoutes[$key][] = $m[0];
    } else {
        $routes[$key] = $m[0];
    }
}

if (!empty($duplicateRoutes)) {
    echo "⚠️  DUPLICATE ROUTES:\n";
    foreach ($duplicateRoutes as $route => $files) {
        echo "   $route:\n";
        foreach ($files as $f) {
            echo "     - $f\n";
        }
    }
} else {
    echo "✅ No duplicate routes\n";
}

echo "\n   Total routes: " . count($routes) . "\n";

// 5. VIEWS - Check for duplicates
echo "\n=== 5. VIEWS ===\n\n";
$views = glob('app/views/pages/**/*.php');
$viewNames = [];
$duplicateViews = [];

foreach ($views as $v) {
    $name = basename($v, '.php');
    if (isset($viewNames[$name])) {
        $duplicateViews[$name][] = $v;
    } else {
        $viewNames[$name] = $v;
    }
}

if (!empty($duplicateViews)) {
    echo "⚠️  DUPLICATE VIEW NAMES:\n";
    foreach ($duplicateViews as $name => $files) {
        echo "   $name:\n";
        foreach ($files as $f) {
            echo "     - $f\n";
        }
    }
} else {
    echo "✅ No duplicate view names\n";
}

echo "\n   Total views: " . count($views) . "\n";

// 6. Orphaned files (no route, no controller render)
echo "\n=== 6. ORPHANED VIEW FILES ===\n\n";
$routeReferences = [];
preg_match_all('/render\([\'"]([^\'"]+)[\'"]/', file_get_contents('app/Http/Controllers/Front/PageController.php'), $pcMatches);
foreach ($pcMatches[1] as $ref) {
    $routeReferences[$ref] = true;
}

// Check views without any reference
$orphaned = [];
foreach ($views as $v) {
    $relative = str_replace('app/views/pages/', '', $v);
    $name = basename($v, '.php');
    
    // Skip directories
    if (strpos($name, '.') === false) continue;
    
    if (!isset($routeReferences[$relative]) && !isset($routeReferences['pages/' . $relative])) {
        $orphaned[] = $v;
    }
}

if (!empty($orphaned)) {
    echo "⚠️  ORPHANED VIEW FILES (no route reference):\n";
    foreach ($orphaned as $f) {
        echo "     - $f\n";
    }
} else {
    echo "✅ All views have route references\n";
}

echo "\n=== ANALYSIS COMPLETE ===\n";
