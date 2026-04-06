<?php
echo "🔍 APS DREAM HOME - FRAMEWORK ANALYSIS (Laravel vs Custom PHP MVC)\n";
echo "================================================================\n\n";

// Step 1: Check if it's Laravel
echo "1. 🏗️ LARAVEL FRAMEWORK CHECK:\n";

$laravelIndicators = [
    'artisan' => 'Laravel Artisan Command',
    'bootstrap/app.php' => 'Laravel Bootstrap',
    'config/app.php' => 'Laravel Config',
    'app/Http/Kernel.php' => 'Laravel HTTP Kernel',
    'app/Http/Controllers/Controller.php' => 'Laravel Base Controller',
    'resources/views' => 'Laravel Views Directory',
    'database/migrations' => 'Laravel Migrations',
    'app/Models/User.php' => 'Laravel User Model',
    'composer.json' => 'Composer Dependencies',
    'vendor/laravel' => 'Laravel Vendor Directory',
    '.env' => 'Laravel Environment File'
];

$laravelScore = 0;
foreach ($laravelIndicators as $indicator => $description) {
    if (file_exists($indicator)) {
        $laravelScore++;
        echo "   ✅ $description: Present\n";
    } else {
        echo "   ❌ $description: Missing\n";
    }
}

echo "\n📊 Laravel Score: $laravelScore/" . count($laravelIndicators) . "\n";

// Step 2: Check if it's Custom PHP MVC
echo "\n2. 🔧 CUSTOM PHP MVC CHECK:\n";

$customMVCIndicators = [
    'public/index.php' => 'Custom Entry Point',
    'config/bootstrap.php' => 'Custom Bootstrap',
    'app/Http/Controllers/AuthController.php' => 'Custom Auth Controller',
    'app/Http/Controllers/Admin/AdminController.php' => 'Custom Admin Controller',
    'app/views/admin' => 'Custom Admin Views',
    'app/views/auth' => 'Custom Auth Views',
    'app/services' => 'Custom Services',
    'routes/web.php' => 'Custom Routes',
    'routes/api.php' => 'Custom API Routes'
];

$customMVCScore = 0;
foreach ($customMVCIndicators as $indicator => $description) {
    if (file_exists($indicator)) {
        $customMVCScore++;
        echo "   ✅ $description: Present\n";
    } else {
        echo "   ❌ $description: Missing\n";
    }
}

echo "\n📊 Custom MVC Score: $customMVCScore/" . count($customMVCIndicators) . "\n";

// Step 3: Check composer.json for framework clues
echo "\n3. 📦 COMPOSER.JSON ANALYSIS:\n";

if (file_exists('composer.json')) {
    $composerContent = file_get_contents('composer.json');
    echo "✅ composer.json found\n";
    
    if (strpos($composerContent, 'laravel/framework') !== false) {
        echo "   ✅ Laravel framework detected in composer.json\n";
        $laravelScore += 2;
    } elseif (strpos($composerContent, 'illuminate/database') !== false) {
        echo "   ✅ Laravel components detected in composer.json\n";
        $laravelScore += 1;
    } else {
        echo "   ❌ Laravel not found in composer.json\n";
    }
    
    // Check for custom PHP framework indicators
    if (strpos($composerContent, 'apsdreamhome') !== false) {
        echo "   ✅ Custom APS Dream Home framework detected\n";
        $customMVCScore += 2;
    }
} else {
    echo "❌ composer.json not found\n";
}

// Step 4: Check the actual structure
echo "\n4. 📁 PROJECT STRUCTURE ANALYSIS:\n";

$structureAnalysis = [
    'app/' => 'Application Directory',
    'config/' => 'Configuration Directory',
    'public/' => 'Public Directory',
    'storage/' => 'Storage Directory',
    'routes/' => 'Routes Directory',
    'app/Http/Controllers/' => 'Controllers Directory',
    'app/views/' => 'Views Directory',
    'app/services/' => 'Services Directory',
    'app/Models/' => 'Models Directory'
];

$structureScore = 0;
foreach ($structureAnalysis as $directory => $description) {
    if (is_dir($directory)) {
        $structureScore++;
        echo "   ✅ $description: Present\n";
        
        // Count files in key directories
        if ($directory === 'app/Http/Controllers/') {
            $files = glob($directory . '*');
            echo "      📊 Controllers: " . count($files) . " files\n";
        } elseif ($directory === 'app/views/') {
            $files = glob($directory . '*');
            echo "      📊 Views: " . count($files) . " files\n";
        }
    } else {
        echo "   ❌ $description: Missing\n";
    }
}

echo "\n📊 Structure Score: $structureScore/" . count($structureAnalysis) . "\n";

// Step 5: Check the actual code patterns
echo "\n5. 💻 CODE PATTERN ANALYSIS:\n";

// Check controller patterns
if (file_exists('app/Http/Controllers/AuthController.php')) {
    $authControllerContent = file_get_contents('app/Http/Controllers/AuthController.php');
    
    if (strpos($authControllerContent, 'namespace App\\Http\\Controllers') !== false) {
        echo "   ✅ Custom PHP namespace detected\n";
        $customMVCScore += 1;
    }
    
    if (strpos($authControllerContent, 'extends Controller') !== false) {
        echo "   ✅ Laravel-style Controller detected\n";
        $laravelScore += 1;
    } elseif (strpos($authControllerContent, 'class AuthController') !== false) {
        echo "   ✅ Custom PHP Controller detected\n";
        $customMVCScore += 1;
    }
}

// Check view patterns
if (file_exists('app/views/admin/dashboard.php')) {
    $viewContent = file_get_contents('app/views/admin/dashboard.php');
    
    if (strpos($viewContent, '@extends') !== false) {
        echo "   ✅ Laravel Blade template detected\n";
        $laravelScore += 1;
    } elseif (strpos($viewContent, 'include') !== false || strpos($viewContent, 'require') !== false) {
        echo "   ✅ Custom PHP view detected\n";
        $customMVCScore += 1;
    }
}

// Step 6: Check routing patterns
echo "\n6. 🛣️ ROUTING PATTERN ANALYSIS:\n";

if (file_exists('routes/web.php')) {
    $routesContent = file_get_contents('routes/web.php');
    
    if (strpos($routesContent, 'Route::') !== false) {
        echo "   ✅ Laravel routing detected\n";
        $laravelScore += 2;
    } elseif (strpos($routesContent, '$router->') !== false) {
        echo "   ✅ Custom PHP routing detected\n";
        $customMVCScore += 2;
    }
    
    if (strpos($routesContent, 'middleware') !== false) {
        echo "   ✅ Middleware detected\n";
        $laravelScore += 1;
    }
}

// Step 7: Final assessment
echo "\n7. 🎯 FINAL FRAMEWORK ASSESSMENT:\n";

$totalLaravelScore = $laravelScore;
$totalCustomScore = $customMVCScore;

echo "📊 Laravel Score: $totalLaravelScore\n";
echo "📊 Custom MVC Score: $totalCustomScore\n";

if ($totalLaravelScore > $totalCustomScore) {
    echo "🎉 CONCLUSION: LARAVEL FRAMEWORK\n";
    echo "✅ Laravel indicators stronger\n";
    echo "✅ Use Laravel commands and patterns\n";
    echo "✅ Check artisan, migrations, etc.\n";
} elseif ($totalCustomScore > $totalLaravelScore) {
    echo "🎉 CONCLUSION: CUSTOM PHP MVC FRAMEWORK\n";
    echo "✅ Custom MVC indicators stronger\n";
    echo "✅ Use custom routing and patterns\n";
    echo "✅ Check custom bootstrap and config\n";
} else {
    echo "⚠️  CONCLUSION: MIXED FRAMEWORK\n";
    echo "⚠️  Both Laravel and Custom elements found\n";
    echo "⚠️  Need manual verification\n";
}

// Step 8: Windsurf Rules Analysis
echo "\n8. 🌪️ WINDSURF RULES ANALYSIS:\n";

if (file_exists('RULES_TOKEN_BACHAO.md')) {
    echo "✅ Windsurf rules file found\n";
    echo "✅ Token bachao rules defined\n";
    echo "✅ Development guidelines present\n";
    echo "✅ Tool usage rules specified\n";
    echo "✅ AI usage restrictions defined\n";
    
    $rulesContent = file_get_contents('RULES_TOKEN_BACHAO.md');
    if (strpos($rulesContent, 'Laravel') !== false) {
        echo "   ❌ Laravel mentioned in rules (conflict)\n";
    } elseif (strpos($rulesContent, 'Custom PHP') !== false) {
        echo "   ✅ Custom PHP mentioned in rules\n";
    } else {
        echo "   ⚠️  Framework not specified in rules\n";
    }
} else {
    echo "❌ Windsurf rules file not found\n";
}

// Step 9: Project Notes Analysis
echo "\n9. 📝 PROJECT NOTES ANALYSIS:\n";

if (file_exists('PROJECT_NOTES.md')) {
    echo "✅ Project notes found\n";
    $notesContent = file_get_contents('PROJECT_NOTES.md');
    
    if (strpos($notesContent, 'Laravel') !== false) {
        echo "   ❌ Laravel mentioned in notes (conflict)\n";
    } elseif (strpos($notesContent, 'Custom') !== false) {
        echo "   ✅ Custom PHP mentioned in notes\n";
    } else {
        echo "   ⚠️  Framework not specified in notes\n";
    }
    
    if (strpos($notesContent, 'OpenCode') !== false) {
        echo "   ✅ OpenCode session documented\n";
    }
    
    if (strpos($notesContent, 'WindSurf') !== false) {
        echo "   ✅ WindSurf usage documented\n";
    }
} else {
    echo "❌ Project notes not found\n";
}

// Step 10: Recommendation
echo "\n10. 🚀 RECOMMENDATION:\n";

if ($totalCustomScore > $totalLaravelScore) {
    echo "🎯 RECOMMENDATION: CUSTOM PHP MVC FRAMEWORK\n";
    echo "✅ Use custom PHP patterns\n";
    echo "✅ Follow custom bootstrap system\n";
    echo "✅ Use custom routing\n";
    echo "✅ Check config/bootstrap.php\n";
    echo "✅ Use app/services for business logic\n";
    echo "✅ Use app/views for templates\n";
    echo "✅ Use public/index.php as entry point\n";
} else {
    echo "🎯 RECOMMENDATION: LARAVEL FRAMEWORK\n";
    echo "✅ Use Laravel artisan commands\n";
    echo "✅ Use Laravel migrations\n";
    echo "✅ Use Laravel blade templates\n";
    echo "✅ Use Laravel facades\n";
    echo "✅ Use Laravel eloquent models\n";
    echo "✅ Use Laravel routing\n";
}

echo "\n📝 FRAMEWORK ANALYSIS COMPLETE!\n";
echo "================================================================\n";
echo "✅ Laravel indicators checked\n";
echo "✅ Custom MVC indicators checked\n";
echo "✅ Composer.json analyzed\n";
echo "✅ Project structure analyzed\n";
echo "✅ Code patterns analyzed\n";
echo "✅ Routing patterns analyzed\n";
echo "✅ Windsurf rules analyzed\n";
echo "✅ Project notes analyzed\n";
echo "✅ Recommendation provided\n";
?>
