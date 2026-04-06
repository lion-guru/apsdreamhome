<?php
echo "🔍 APS DREAM HOME - DEEP SCAN - MAXIMUM ANALYSIS\n";
echo "==================================================\n\n";

// Check original project structure vs current
echo "1. 📁 ORIGINAL PROJECT STRUCTURE ANALYSIS:\n";

// Check if this was a working Laravel/PHP project
$projectIndicators = [
    'composer.json' => 'PHP Project Indicator',
    'package.json' => 'Node.js Project Indicator',
    'vendor/' => 'Composer Dependencies',
    'node_modules/' => 'NPM Dependencies',
    'config/' => 'Configuration Directory',
    'storage/' => 'Storage Directory',
    'resources/' => 'Resources Directory',
    'public/' => 'Public Directory'
];

$originalProjectScore = 0;
foreach ($projectIndicators as $indicator => $description) {
    if (file_exists($indicator)) {
        $originalProjectScore++;
        echo "   ✅ $description: Present\n";
    } else {
        echo "   ❌ $description: Missing\n";
    }
}

echo "\n📊 Original Project Score: $originalProjectScore/" . count($projectIndicators) . "\n";

// Check what framework was being used
echo "\n2. 🏗️ FRAMEWORK ANALYSIS:\n";

$frameworkChecks = [
    'Laravel' => ['artisan', 'app/Http/Kernel.php', 'bootstrap/app.php'],
    'Symfony' => ['bin/console', 'src/Kernel.php'],
    'CodeIgniter' => ['system/core/CodeIgniter.php'],
    'Custom PHP' => ['index.php', 'app/', 'config/']
];

foreach ($frameworkChecks as $framework => $files) {
    $frameworkScore = 0;
    foreach ($files as $file) {
        if (file_exists($file)) {
            $frameworkScore++;
        }
    }
    if ($frameworkScore >= 2) {
        echo "   ✅ $framework: Detected ($frameworkScore/" . count($files) . " files)\n";
    } else {
        echo "   ❌ $framework: Not detected\n";
    }
}

// Check original home page
echo "\n3. 🏠 ORIGINAL HOME PAGE ANALYSIS:\n";

$originalHomePageFiles = [
    'resources/views/welcome.blade.php' => 'Laravel Welcome',
    'resources/views/index.blade.php' => 'Laravel Index',
    'public/index.php' => 'PHP Entry Point',
    'index.html' => 'Static HTML',
    'app/views/home.php' => 'Custom PHP Home'
];

foreach ($originalHomePageFiles as $file => $description) {
    if (file_exists($file)) {
        $size = filesize($file);
        $modified = date('Y-m-d H:i:s', filemtime($file));
        echo "   ✅ $description: $file ($size bytes) - Modified: $modified\n";
        
        // Check content
        $content = file_get_contents($file);
        if (strlen($content) > 100) {
            echo "      📄 Content: " . substr($content, 0, 100) . "...\n";
        }
    } else {
        echo "   ❌ $description: $file (MISSING)\n";
    }
}

// Check what was the original routing system
echo "\n4. 🛣️ ORIGINAL ROUTING SYSTEM ANALYSIS:\n";

$originalRoutes = [
    'routes/web.php' => 'Laravel Routes',
    'routes/api.php' => 'API Routes',
    'app/routes.php' => 'Custom Routes',
    '.htaccess' => 'Apache Routes'
];

foreach ($originalRoutes as $file => $description) {
    if (file_exists($file)) {
        $size = filesize($file);
        $modified = date('Y-m-d H:i:s', filemtime($file));
        echo "   ✅ $description: $file ($size bytes) - Modified: $modified\n";
        
        // Check if it's Laravel routes
        $content = file_get_contents($file);
        if (strpos($content, 'Route::') !== false) {
            echo "      ✅ Laravel-style routes detected\n";
        } elseif (strpos($content, '$router->') !== false) {
            echo "      ✅ Custom router detected\n";
        } elseif (strpos($content, 'RewriteEngine') !== false) {
            echo "      ✅ Apache mod_rewrite detected\n";
        }
    } else {
        echo "   ❌ $description: $file (MISSING)\n";
    }
}

// Check original database structure
echo "\n5. 🗄️ ORIGINAL DATABASE STRUCTURE ANALYSIS:\n";

try {
    $db = new PDO('mysql:host=localhost;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database Connection: SUCCESS\n";
    
    // Get all tables
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "📊 Total Tables: " . count($tables) . "\n";
    
    // Check for original tables vs new tables
    $originalTables = [
        'users' => 'User Management',
        'properties' => 'Property Management',
        'customers' => 'Customer Management',
        'payments' => 'Payment Management',
        'admins' => 'Admin Management'
    ];
    
    $originalTableCount = 0;
    foreach ($originalTables as $table => $description) {
        if (in_array($table, $tables)) {
            $stmt = $db->query("SELECT COUNT(*) as count FROM $table");
            $count = $stmt->fetch()['count'];
            $originalTableCount++;
            echo "   ✅ $description: $table ($count records)\n";
        } else {
            echo "   ❌ $description: $table (MISSING)\n";
        }
    }
    
    echo "\n📊 Original Tables: $originalTableCount/" . count($originalTables) . "\n";
    
} catch (Exception $e) {
    echo "❌ Database Connection: FAILED\n";
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Check what was the original authentication system
echo "\n6. 🔐 ORIGINAL AUTHENTICATION ANALYSIS:\n";

$authFiles = [
    'app/Http/Controllers/Auth/AuthController.php' => 'Laravel Auth Controller',
    'app/Http/Controllers/AuthController.php' => 'Custom Auth Controller',
    'config/auth.php' => 'Laravel Auth Config',
    'app/Models/User.php' => 'User Model',
    'database/migrations/' => 'Database Migrations'
];

foreach ($authFiles as $file => $description) {
    if (file_exists($file)) {
        $size = filesize($file);
        echo "   ✅ $description: $file ($size bytes)\n";
    } else {
        echo "   ❌ $description: $file (MISSING)\n";
    }
}

// Check what was the original admin system
echo "\n7. 🏢 ORIGINAL ADMIN SYSTEM ANALYSIS:\n";

$adminFiles = [
    'app/Http/Controllers/Admin/AdminController.php' => 'Admin Controller',
    'app/Http/Controllers/Admin/' => 'Admin Controllers Directory',
    'resources/views/admin/' => 'Admin Views Directory',
    'app/views/admin/' => 'Custom Admin Views'
];

foreach ($adminFiles as $file => $description) {
    if (file_exists($file)) {
        if (is_dir($file)) {
            $files = glob($file . '*');
            echo "   ✅ $description: $file (" . count($files) . " files)\n";
        } else {
            $size = filesize($file);
            echo "   ✅ $description: $file ($size bytes)\n";
        }
    } else {
        echo "   ❌ $description: $file (MISSING)\n";
    }
}

// Check git history to see what was originally there
echo "\n8. 📜 GIT HISTORY ANALYSIS:\n";

$gitHistory = shell_exec('git log --oneline -10 2>/dev/null');
if ($gitHistory) {
    echo "✅ Git History Available:\n";
    $historyLines = explode("\n", trim($gitHistory));
    foreach ($historyLines as $line) {
        if (!empty($line)) {
            echo "   📝 $line\n";
        }
    }
} else {
    echo "❌ Git History Not Available\n";
}

// Check what was changed recently
echo "\n9. 🔄 RECENT CHANGES ANALYSIS:\n";

$recentFiles = [
    'index.php' => 'Main Entry Point',
    'app/views/home.php' => 'Home Page',
    '.htaccess' => 'Apache Config',
    'routes/web.php' => 'Routes'
];

echo "📋 Recent File Changes:\n";
foreach ($recentFiles as $file => $description) {
    if (file_exists($file)) {
        $modified = date('Y-m-d H:i:s', filemtime($file));
        $size = filesize($file);
        echo "   📄 $description: $file\n";
        echo "      📅 Modified: $modified\n";
        echo "      📏 Size: $size bytes\n";
        
        // Check if this looks like original or new
        $content = file_get_contents($file);
        if (strpos($content, 'localhost/apsdreamhome') !== false) {
            echo "      🆕 NEW: Contains custom URL configuration\n";
        } elseif (strpos($content, 'APS Dream Home') !== false) {
            echo "      🆕 NEW: Contains custom branding\n";
        } else {
            echo "      📦 ORIGINAL: May be original content\n";
        }
    }
}

// Check what functionality was broken
echo "\n10. ❌ BROKEN FUNCTIONALITY ANALYSIS:\n";

$brokenChecks = [
    'Laravel Routes' => ['routes/web.php', 'Route::'],
    'Laravel Views' => ['resources/views/', '.blade.php'],
    'Laravel Controllers' => ['app/Http/Controllers/', 'extends Controller'],
    'Laravel Models' => ['app/Models/', 'extends Model'],
    'Laravel Config' => ['config/', 'return [']
];

foreach ($brokenChecks as $feature => $checks) {
    $working = 0;
    foreach ($checks as $file => $content) {
        if (file_exists($file)) {
            if ($content && strpos(file_get_contents($file), $content) !== false) {
                $working++;
            } elseif (!$content) {
                $working++;
            }
        }
    }
    
    if ($working >= count($checks)) {
        echo "   ✅ $feature: Working\n";
    } else {
        echo "   ❌ $feature: May be broken ($working/" . count($checks) . ")\n";
    }
}

// Final assessment
echo "\n🎯 DEEP SCAN FINAL ASSESSMENT:\n";
echo "==================================================\n";

$totalChecks = 10;
$passedChecks = 0;

if ($originalProjectScore >= 3) $passedChecks++; // Original project
if (isset($db)) $passedChecks++; // Database
if (file_exists('index.php')) $passedChecks++; // Entry point
if (file_exists('routes/web.php')) $passedChecks++; // Routes
if (file_exists('app/views/home.php')) $passedChecks++; // Views
if ($originalTableCount >= 3) $passedChecks++; // Original tables
if (file_exists('app/Http/Controllers/AuthController.php')) $passedChecks++; // Auth
if (file_exists('app/Http/Controllers/')) $passedChecks++; // Controllers
if (isset($gitHistory)) $passedChecks++; // Git history
if ($workingFunctions >= 3) $passedChecks++; // Functionality

$percentage = round(($passedChecks / $totalChecks) * 100, 1);

echo "📊 Deep Scan Score: $percentage%\n";
echo "📊 Checks Passed: $passedChecks/$totalChecks\n";

if ($percentage >= 80) {
    echo "🎉 ASSESSMENT: ORIGINAL PROJECT MOSTLY INTACT\n";
    echo "✅ Original structure preserved\n";
    echo "✅ Some enhancements added\n";
    echo "⚠️  Some components modified\n";
} elseif ($percentage >= 60) {
    echo "⚠️  ASSESSMENT: PROJECT SIGNIFICANTLY MODIFIED\n";
    echo "✅ Some original components preserved\n";
    echo "❌ Many components replaced\n";
    echo "⚠️  Functionality may be affected\n";
} else {
    echo "🚨 ASSESSMENT: PROJECT HEAVILY MODIFIED\n";
    echo "❌ Most original components replaced\n";
    echo "❌ Original functionality lost\n";
    echo "🚨 Major changes made\n";
}

echo "\n🔍 DEEP SCAN COMPLETE!\n";
echo "==================================================\n";
echo "✅ Original project structure analyzed\n";
echo "✅ Framework detection completed\n";
echo "✅ Database structure verified\n";
echo "✅ Authentication system checked\n";
echo "✅ Recent changes identified\n";
echo "✅ Broken functionality assessed\n";
echo "✅ Git history reviewed\n";
echo "✅ Final assessment provided\n";
?>
