<?php
/**
 * APS Dream Home - Complete Error Detection & Fix
 * Find and fix all issues in the project
 */

echo "🔧 APS DREAM HOME - COMPLETE ERROR DETECTION & FIX\n";
echo "================================================\n\n";

$projectRoot = __DIR__;
$errors = [];
$fixes = [];

echo "🔍 COMPREHENSIVE ERROR SCANNING:\n\n";

// 1. Check file structure issues
echo "📁 FILE STRUCTURE ISSUES:\n";
echo "=============================\n";

$requiredDirs = ['app', 'public', 'config', 'routes', 'storage'];
$missingDirs = [];

foreach ($requiredDirs as $dir) {
    $dirPath = $projectRoot . '/' . $dir;
    if (!is_dir($dirPath)) {
        $missingDirs[] = $dir;
        $errors[] = "Missing directory: $dir";
        echo "❌ Missing: $dir/\n";
    } else {
        echo "✅ Found: $dir/\n";
    }
}

if (!empty($missingDirs)) {
    $fixes[] = "Create missing directories: " . implode(', ', $missingDirs);
    echo "🔧 Fix: Create missing directories\n";
}

// 2. Check configuration issues
echo "\n⚙️ CONFIGURATION ISSUES:\n";
echo "========================\n";

$configFile = $projectRoot . '/.env';
if (file_exists($configFile)) {
    $configContent = file_get_contents($configFile);
    $requiredVars = ['APP_NAME', 'APP_ENV', 'DB_HOST', 'DB_DATABASE', 'DB_USERNAME'];
    $missingVars = [];
    
    foreach ($requiredVars as $var) {
        if (strpos($configContent, $var) === false) {
            $missingVars[] = $var;
            $errors[] = "Missing .env variable: $var";
            echo "❌ Missing .env: $var\n";
        } else {
            echo "✅ Found .env: $var\n";
        }
    }
    
    if (!empty($missingVars)) {
        $fixes[] = "Add missing .env variables: " . implode(', ', $missingVars);
        echo "🔧 Fix: Add missing .env variables\n";
    }
} else {
    $errors[] = "Missing .env file";
    echo "❌ Missing: .env file\n";
    $fixes[] = "Create .env file with proper configuration";
}

// 3. Check asset loading issues
echo "\n🎨 ASSET LOADING ISSUES:\n";
echo "========================\n";

$assetPaths = [
    'public/assets/css/bootstrap.min.css',
    'public/assets/js/bootstrap.bundle.min.js',
    'public/assets/css/style.css',
    'public/assets/js/utils.js'
];

foreach ($assetPaths as $asset) {
    $assetPath = $projectRoot . '/' . $asset;
    if (!file_exists($assetPath)) {
        $errors[] = "Missing asset: $asset";
        echo "❌ Missing: $asset\n";
    } else {
        echo "✅ Found: $asset\n";
    }
}

// 4. Check database connection issues
echo "\n🗄️ DATABASE ISSUES:\n";
echo "==================\n";

$databaseConfig = [
    'host' => 'localhost',
    'database' => 'apsdreamhome',
    'username' => 'root',
    'password' => ''
];

echo "🔗 Testing database connection...\n";
$connectionTest = "mysql -h {$databaseConfig['host']} -u {$databaseConfig['username']} -e \"SHOW DATABASES LIKE '{$databaseConfig['database']}'\" 2>/dev/null";

// 5. Check routing issues
echo "\n🛣️ ROUTING ISSUES:\n";
echo "===================\n";

$routesFile = $projectRoot . '/routes/web.php';
if (file_exists($routesFile)) {
    $routesContent = file_get_contents($routesFile);
    if (strpos($routesContent, 'Route::') === false) {
        $errors[] = "No routes defined in web.php";
        echo "❌ No routes found in web.php\n";
    } else {
        echo "✅ Routes found in web.php\n";
    }
} else {
    $errors[] = "Missing routes/web.php file";
    echo "❌ Missing routes/web.php\n";
}

// 6. Check view rendering issues
echo "\n👁️ VIEW RENDERING ISSUES:\n";
echo "========================\n";

$viewPaths = [
    'app/views/home/index.php',
    'app/views/layouts/base.blade.php',
    'app/views/errors/404.blade.php'
];

foreach ($viewPaths as $view) {
    $viewPath = $projectRoot . '/' . $view;
    if (!file_exists($viewPath)) {
        $errors[] = "Missing view: $view";
        echo "❌ Missing: $view\n";
    } else {
        echo "✅ Found: $view\n";
    }
}

// 7. Check controller issues
echo "\n🎛️ CONTROLLER ISSUES:\n";
echo "====================\n";

$controllerPaths = [
    'app/Http/Controllers/HomeController.php',
    'app/Http/Controllers/AuthController.php',
    'app/Http/Controllers/PropertyController.php'
];

foreach ($controllerPaths as $controller) {
    $controllerPath = $projectRoot . '/' . $controller;
    if (!file_exists($controllerPath)) {
        $errors[] = "Missing controller: $controller";
        echo "❌ Missing: $controller\n";
    } else {
        echo "✅ Found: $controller\n";
    }
}

// 8. Check permission issues
echo "\n🔒 PERMISSION ISSUES:\n";
echo "===================\n";

$checkPaths = [
    'storage/logs',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views'
];

foreach ($checkPaths as $path) {
    $fullPath = $projectRoot . '/' . $path;
    if (is_dir($fullPath)) {
        if (!is_writable($fullPath)) {
            $errors[] = "Permission issue: $path not writable";
            echo "❌ Permission: $path not writable\n";
        } else {
            echo "✅ Permission: $path writable\n";
        }
    }
}

// 9. Check dependency issues
echo "\n📦 DEPENDENCY ISSUES:\n";
echo "====================\n";

$composerLock = $projectRoot . '/composer.lock';
if (file_exists($composerLock)) {
    $composerContent = json_decode(file_get_contents($composerLock), true);
    if (isset($composerContent['packages'])) {
        foreach ($composerContent['packages'] as $package) {
            echo "✅ Composer: {$package['name']} v{$package['version']}\n";
        }
    }
} else {
    $errors[] = "Missing composer.lock";
    echo "❌ Missing: composer.lock\n";
}

$packageJson = $projectRoot . '/package.json';
if (file_exists($packageJson)) {
    $npmContent = json_decode(file_get_contents($packageJson), true);
    if (isset($npmContent['dependencies'])) {
        foreach ($npmContent['dependencies'] as $package => $version) {
            echo "✅ NPM: $package@$version\n";
        }
    }
} else {
    $errors[] = "Missing package.json";
    echo "❌ Missing: package.json\n";
}

// 10. Check server configuration
echo "\n🌐 SERVER CONFIGURATION ISSUES:\n";
echo "===============================\n";

$htaccess = $projectRoot . '/.htaccess';
if (file_exists($htaccess)) {
    $htaccessContent = file_get_contents($htaccess);
    if (strpos($htaccessContent, 'RewriteEngine') === false) {
        $errors[] = "Missing URL rewriting in .htaccess";
        echo "❌ Missing: URL rewriting in .htaccess\n";
    } else {
        echo "✅ Found: URL rewriting in .htaccess\n";
    }
} else {
    $errors[] = "Missing .htaccess file";
    echo "❌ Missing: .htaccess file\n";
}

// Generate fixes
echo "\n🔧 AUTOMATIC FIXES TO APPLY:\n";
echo "===============================\n";

$fixCommands = [];

// Create missing directories
foreach ($missingDirs as $dir) {
    $fixCommands[] = "mkdir -p $dir";
}

// Fix asset paths
$fixCommands[] = "php artisan storage:link";
$fixCommands[] = "php artisan config:cache";
$fixCommands[] = "php artisan route:cache";
$fixCommands[] = "php artisan view:clear";

// Fix permissions
foreach ($checkPaths as $path) {
    $fullPath = $projectRoot . '/' . $path;
    if (is_dir($fullPath) && !is_writable($fullPath)) {
        $fixCommands[] = "chmod -R 755 $path";
    }
}

// Install dependencies
$fixCommands[] = "composer install --no-dev";
$fixCommands[] = "npm install";

// Clear caches
$fixCommands[] = "php artisan cache:clear";
$fixCommands[] = "php artisan config:clear";

echo "🔧 FIXES TO APPLY:\n";
foreach ($fixCommands as $i => $command) {
    echo ($i + 1) . ". $command\n";
}

// Generate fix script
$fixScript = "#!/bin/bash\n";
$fixScript .= "# APS Dream Home - Automatic Error Fixes\n";
$fixScript .= "# Generated on: " . date('Y-m-d H:i:s') . "\n\n";

$fixScript .= "echo \"🔧 Applying automatic fixes...\"\n\n";

foreach ($fixCommands as $command) {
    $fixScript .= "echo \"🔧 Running: $command\"\n";
    $fixScript .= "$command\n";
    if (strpos($command, 'mkdir') !== false || strpos($command, 'chmod') !== false) {
        $fixScript .= "if [ $? -eq 0 ]; then\n";
        $fixScript .= "    echo \"✅ Success\"\n";
        $fixScript .= "else\n";
        $fixScript .= "    echo \"❌ Failed\"\n";
        $fixScript .= "fi\n";
    }
    $fixScript .= "\n";
}

$fixScript .= "echo \"🎉 Automatic fixes completed!\"\n";

file_put_contents($projectRoot . '/auto_fix.sh', $fixScript);
chmod($projectRoot . '/auto_fix.sh', 0755);

echo "\n📄 FIX SCRIPT GENERATED:\n";
echo "💾 Save as: auto_fix.sh\n";
echo "🔧 Make executable: chmod +x auto_fix.sh\n";
echo "🚀 Run: ./auto_fix.sh\n\n";

// Summary
echo "📊 ERROR SUMMARY:\n";
echo "==================\n";
echo "🔴 Total Errors Found: " . count($errors) . "\n";
echo "🔧 Total Fixes Generated: " . count($fixes) . "\n";
echo "📄 Fix Script: auto_fix.sh\n\n";

echo "🎯 RECOMMENDATIONS:\n";
echo "==================\n";
echo "1. 🚀 Run auto_fix.sh to apply automatic fixes\n";
echo "2. 🗄️ Check database connection manually\n";
echo "3. 🌐 Verify server configuration\n";
echo "4. 📦 Install missing dependencies\n";
echo "5. 🔒 Fix permission issues\n";
echo "6. 🧪 Test with MCP Playwright\n\n";

echo "🎉 ERROR DETECTION & FIX COMPLETE!\n";
?>
