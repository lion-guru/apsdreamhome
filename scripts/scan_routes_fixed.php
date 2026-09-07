<?php
$projectRoot = dirname(__DIR__);
$routes = file($projectRoot . '/routes/web.php');
$missing = 0;
$total = 0;
$missingClasses = [];

foreach ($routes as $line) {
    // Match string-based routes: 'ClassName@method' or 'Namespace\ClassName@method'
    if (preg_match('/->(?:get|post|put|delete|patch)\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([A-Za-z\\\\]+)@([a-zA-Z]+)/', $line, $m)) {
        $route = $m[1];
        $class = $m[2];
        $method = $m[3];
        $total++;
        
        // Handle both full FQCN and short class names
        // Full FQCN: App\Http\Controllers\Media\MediaLibraryController
        // Short: LocalizationController
        if (str_starts_with($class, 'App\\Http\\Controllers\\')) {
            // Full namespace - strip prefix
            $relativeClass = substr($class, strlen('App\\Http\\Controllers\\'));
            $file = $projectRoot . '/app/Http/Controllers/' . str_replace('\\', '/', $relativeClass) . '.php';
        } else {
            // Short name - assume root Controllers
            $file = $projectRoot . '/app/Http/Controllers/' . $class . '.php';
        }
        
        if (!file_exists($file)) {
            $missingClasses[$class] = true;
            echo "MISSING CLASS: {$class}@{$method} => {$route}\n";
            $missing++;
            continue;
        }
        
        $content = file_get_contents($file);
        if (strpos($content, 'function ' . $method) === false) {
            echo "MISSING METHOD: {$class}@{$method} => {$route}\n";
            $missing++;
        }
    }
    
    // Also match array syntax: [ClassName::class, 'method'] or [Controller::class, 'method']
    if (preg_match('/->(?:get|post|put|delete|patch)\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*\[\s*([A-Za-z\\\\]+)::class\s*,\s*[\'"]([a-zA-Z]+)[\'"]\s*\]/', $line, $m)) {
        $route = $m[1];
        $class = $m[2];
        $method = $m[3];
        $total++;
        
        // ClassName::class syntax - resolve the full class name
        // Could be App\Http\Controllers\Admin\AdminController::class
        if (str_starts_with($class, 'App\\Http\\Controllers\\')) {
            $relativeClass = substr($class, strlen('App\\Http\\Controllers\\'));
            $file = $projectRoot . '/app/Http/Controllers/' . str_replace('\\', '/', $relativeClass) . '.php';
        } else {
            // Short name like AdminController::class - need to search
            $file = $projectRoot . '/app/Http/Controllers/' . $class . '.php';
            if (!file_exists($file)) {
                // Search in Admin subdir
                $file = $projectRoot . '/app/Http/Controllers/Admin/' . $class . '.php';
            }
        }
        
        if (!file_exists($file)) {
            $missingClasses[$class] = true;
            echo "MISSING CLASS (array): {$class}@{$method} => {$route}\n";
            $missing++;
            continue;
        }
        
        $content = file_get_contents($file);
        if (strpos($content, 'function ' . $method) === false) {
            echo "MISSING METHOD (array): {$class}@{$method} => {$route}\n";
            $missing++;
        }
    }
}

echo "\nTotal routes scanned: {$total}\n";
echo "Missing: {$missing}\n";
echo "Unique missing classes: " . count($missingClasses) . "\n";
if (count($missingClasses) > 0) {
    echo "Classes:\n";
    foreach (array_keys($missingClasses) as $cls) {
        echo "  - {$cls}\n";
    }
}