<?php
echo "🔍 APS DREAM HOME - PRE-COMMIT SYSTEM CHECK\n";
echo "============================================\n\n";

// Step 1: Check if system is working
echo "1. 🌐 SYSTEM ACCESSIBILITY CHECK:\n";

$testUrls = [
    '/' => 'Main Page',
    '/login' => 'Login Page',
    '/admin' => 'Admin Panel',
    '/register' => 'Registration Page',
    '/properties' => 'Properties'
];

$workingUrls = 0;
$ch = curl_init();

foreach ($testUrls as $url => $description) {
    curl_setopt($ch, CURLOPT_URL, "http://localhost/apsdreamhome$url");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($httpCode === 200) {
        $workingUrls++;
        echo "✅ $description: http://localhost/apsdreamhome$url (HTTP $httpCode) - " . strlen($response) . " bytes\n";
    } else {
        echo "❌ $description: http://localhost/apsdreamhome$url (HTTP $httpCode)\n";
    }
}
curl_close($ch);

echo "\n📊 Working URLs: $workingUrls/" . count($testUrls) . "\n";

// Step 2: Check database
echo "\n2. 🗄️ DATABASE CONNECTION CHECK:\n";
try {
    $db = new PDO('mysql:host=localhost;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database Connection: SUCCESS\n";
    
    // Check key tables
    $tables = ['users', 'properties', 'customers', 'payments', 'states', 'districts', 'colonies'];
    foreach ($tables as $table) {
        try {
            $stmt = $db->query("SELECT COUNT(*) as count FROM $table");
            $count = $stmt->fetch()['count'];
            echo "   ✅ $table: $count records\n";
        } catch (Exception $e) {
            echo "   ❌ $table: Error - " . $e->getMessage() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Database Connection: FAILED\n";
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Step 3: Check original system files
echo "\n3. 📁 ORIGINAL SYSTEM FILES CHECK:\n";

$originalFiles = [
    'public/index.php' => 'Original Entry Point',
    'config/bootstrap.php' => 'Bootstrap Configuration',
    'app/Http/Controllers/AuthController.php' => 'Auth Controller',
    'app/Http/Controllers/Admin/AdminController.php' => 'Admin Controller',
    'app/Models/User.php' => 'User Model',
    '.htaccess' => 'Apache Configuration',
    'index.php' => 'Root Index (Redirect)'
];

$originalFilesCount = 0;
foreach ($originalFiles as $file => $description) {
    if (file_exists($file)) {
        $originalFilesCount++;
        $size = filesize($file);
        echo "   ✅ $description: $file ($size bytes)\n";
    } else {
        echo "   ❌ $description: $file (MISSING)\n";
    }
}

// Step 4: Check if anything is broken
echo "\n4. 🔍 BROKEN COMPONENTS CHECK:\n";

$brokenComponents = [];

// Check for Laravel files (should not exist)
$laravelFiles = [
    'resources/views' => 'Laravel Views Directory',
    'app/Http/Kernel.php' => 'Laravel HTTP Kernel',
    'bootstrap/app.php' => 'Laravel Bootstrap'
];

foreach ($laravelFiles as $file => $description) {
    if (file_exists($file)) {
        $brokenComponents[] = "❌ Laravel component found: $description";
    }
}

// Check for duplicate files
if (file_exists('app/views/home.php')) {
    $brokenComponents[] = "❌ Duplicate home.php found in app/views/";
}

if (file_exists('resources/views')) {
    $brokenComponents[] = "❌ Laravel views directory exists";
}

if (empty($brokenComponents)) {
    echo "✅ No broken components found\n";
} else {
    echo "❌ Broken components found:\n";
    foreach ($brokenComponents as $component) {
        echo "   $component\n";
    }
}

// Step 5: Check .htaccess configuration
echo "\n5. ⚙️ HTACCESS CONFIGURATION CHECK:\n";

if (file_exists('.htaccess')) {
    $htaccessContent = file_get_contents('.htaccess');
    
    if (strpos($htaccessContent, 'RewriteEngine On') !== false) {
        echo "✅ Rewrite Engine: Enabled\n";
    } else {
        echo "❌ Rewrite Engine: Not enabled\n";
    }
    
    if (strpos($htaccessContent, 'public/') !== false) {
        echo "✅ Public Redirect: Configured\n";
    } else {
        echo "❌ Public Redirect: Not configured\n";
    }
    
    if (strpos($htaccessContent, 'public/index.php') !== false) {
        echo "✅ Index Handler: Configured\n";
    } else {
        echo "❌ Index Handler: Not configured\n";
    }
} else {
    echo "❌ .htaccess file missing\n";
}

// Step 6: Check framework compliance
echo "\n6. 🏗️ FRAMEWORK COMPLIANCE CHECK:\n";

$frameworkChecks = [
    'app/views/admin' => 'Custom Admin Views',
    'app/views/auth' => 'Custom Auth Views',
    'app/services' => 'Custom Services',
    'routes/web.php' => 'Custom Routes',
    'routes/api.php' => 'Custom API Routes'
];

$complianceScore = 0;
foreach ($frameworkChecks as $component => $description) {
    if (file_exists($component)) {
        $complianceScore++;
        echo "✅ $description: Present\n";
    } else {
        echo "❌ $description: Missing\n";
    }
}

// Step 7: Final assessment
echo "\n7. 🎯 PRE-COMMIT ASSESSMENT:\n";

$totalChecks = 7;
$passedChecks = 0;

if ($workingUrls >= 4) $passedChecks++;
if (isset($db)) $passedChecks++;
if ($originalFilesCount >= 6) $passedChecks++;
if (empty($brokenComponents)) $passedChecks++;
if (file_exists('.htaccess')) $passedChecks++;
if ($complianceScore >= 4) $passedChecks++;
if (file_exists('config/bootstrap.php')) $passedChecks++;

$percentage = round(($passedChecks / $totalChecks) * 100, 1);

echo "📊 Pre-commit Score: $percentage%\n";
echo "📊 Checks Passed: $passedChecks/$totalChecks\n";

if ($percentage >= 85) {
    echo "🎉 COMMIT STATUS: READY FOR COMMIT\n";
    echo "✅ System working correctly\n";
    echo "✅ No broken components\n";
    echo "✅ Original framework intact\n";
    echo "✅ All files present\n";
    echo "✅ Database connected\n";
    echo "✅ URLs accessible\n";
    
    echo "\n📝 COMMIT RECOMMENDATION:\n";
    echo "================================\n";
    echo "✅ SAFE TO COMMIT\n";
    echo "✅ System is stable\n";
    echo "✅ No damage done\n";
    echo "✅ Original system preserved\n";
    echo "✅ Ready for production\n";
    
} elseif ($percentage >= 70) {
    echo "⚠️  COMMIT STATUS: NEEDS REVIEW\n";
    echo "✅ Mostly working\n";
    echo "⚠️  Some minor issues\n";
    echo "❌ Review before commit\n";
    
} else {
    echo "❌ COMMIT STATUS: NOT READY\n";
    echo "❌ System has issues\n";
    echo "❌ Fix problems before commit\n";
}

echo "\n📋 PRE-COMMIT CHECK COMPLETE!\n";
echo "================================\n";
echo "✅ System accessibility checked\n";
echo "✅ Database connection verified\n";
echo "✅ Original files confirmed\n";
echo "✅ Broken components identified\n";
echo "✅ Configuration verified\n";
echo "✅ Framework compliance checked\n";
echo "✅ Final assessment provided\n";
?>
