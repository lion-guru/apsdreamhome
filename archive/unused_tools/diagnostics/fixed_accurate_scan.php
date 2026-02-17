<?php
/**
 * APS Dream Home - FIXED ACCURATE DEEP SCAN
 * Proper recursive directory scanning for accurate results
 */

echo "🏠 APS Dream Home - FIXED ACCURATE DEEP SCAN\n";
echo "==========================================\n\n";

$projectRoot = 'c:\\xampp\\htdocs\\apsdreamhome';
$scanResults = [
    'directories' => [],
    'files' => [],
    'features' => []
];

// RECURSIVE DIRECTORY SCANNING FUNCTION
function scanAllDirectories($rootDir, &$results) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($rootDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $item) {
        $relativePath = str_replace($rootDir . '/', '', $item->getPathname());
        
        if ($item->isDir()) {
            $results['directories'][] = $relativePath;
        } else {
            $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
            $results['files'][$ext][] = [
                'path' => $relativePath,
                'size' => $item->getSize(),
                'name' => $item->getFilename()
            ];
        }
    }
}

// SCAN ALL DIRECTORIES AND FILES
scanAllDirectories($projectRoot, $scanResults);

// 1. DIRECTORY ANALYSIS
echo "1. 📁 DIRECTORY ANALYSIS (ACCURATE)\n";
echo "===================================\n";

$totalDirs = count($scanResults['directories']);
$totalFiles = array_sum(array_map('count', $scanResults['files']));
$totalSize = 0;

foreach ($scanResults['files'] as $ext => $files) {
    foreach ($files as $file) {
        $totalSize += $file['size'];
    }
}

echo "   Total Directories: $totalDirs\n";
echo "   Total Files: $totalFiles\n";
echo "   Total Size: " . round($totalSize / 1024 / 1024, 2) . " MB\n\n";

// 2. FEATURE DETECTION (ACCURATE)
echo "2. 🚀 FEATURE DETECTION (ACCURATE)\n";
echo "=================================\n";

$features = [
    'Admin Panel' => ['admin', 'Admin', 'administrator', 'backend'],
    'Authentication' => ['auth', 'Auth', 'login', 'register', 'session'],
    'Database' => ['database', 'Database', 'db_', 'DB_', 'mysql', 'mysqli', 'pdo'],
    'API' => ['api', 'Api', 'route', 'Route', 'endpoint'],
    'CRM' => ['crm', 'CRM', 'customer', 'lead', 'contact'],
    'MLM' => ['mlm', 'MLM', 'associate', 'commission', 'network'],
    'Payment' => ['payment', 'Payment', 'razorpay', 'stripe', 'paypal', 'transaction'],
    'Email' => ['email', 'Email', 'mail', 'Mail', 'smtp', 'phpmailer'],
    'Mobile' => ['mobile', 'Mobile', 'app', 'react', 'android', 'ios'],
    'Analytics' => ['analytics', 'Analytics', 'report', 'Report', 'dashboard'],
    'Security' => ['security', 'Security', 'csrf', 'xss', 'encryption', 'hash'],
    'Testing' => ['test', 'Test', 'spec', 'Spec', 'phpunit'],
    'Documentation' => ['doc', 'Doc', 'readme', 'README', 'guide', 'manual']
];

foreach ($features as $feature => $keywords) {
    $featureCount = 0;
    
    // Check in directories
    foreach ($scanResults['directories'] as $dir) {
        foreach ($keywords as $keyword) {
            if (strpos(strtolower($dir), strtolower($keyword)) !== false) {
                $featureCount++;
                break;
            }
        }
    }
    
    // Check in files
    foreach ($scanResults['files'] as $ext => $files) {
        foreach ($files as $file) {
            foreach ($keywords as $keyword) {
                if (strpos(strtolower($file['path']), strtolower($keyword)) !== false || 
                    strpos(strtolower($file['name']), strtolower($keyword)) !== false) {
                    $featureCount++;
                    break;
                }
            }
        }
    }
    
    $status = $featureCount > 0 ? "✅ Found ($featureCount items)" : "❌ Not Found";
    echo "   $feature: $status\n";
}

// 3. FILE TYPE ANALYSIS (ACCURATE)
echo "\n3. 📄 FILE TYPE ANALYSIS (ACCURATE)\n";
echo "=================================\n";

$fileIcons = [
    'php' => '🐘', 'js' => '📜', 'css' => '🎨', 'html' => '🌐',
    'json' => '📋', 'md' => '📝', 'sql' => '🗄️', 'xml' => '📰',
    'env' => '🔐', 'gitignore' => '🚫', 'htaccess' => '🔒',
    'jpg' => '🖼️', 'png' => '🖼️', 'gif' => '🖼️', 'svg' => '🎨',
    'pdf' => '📕', 'zip' => '📦', 'ttf' => '🔤', 'woff' => '🔤'
];

foreach ($scanResults['files'] as $ext => $files) {
    if (!empty($files)) {
        $count = count($files);
        $size = array_sum(array_column($files, 'size'));
        $icon = $fileIcons[$ext] ?? '📄';
        echo "   $icon .$ext files: $count (" . round($size / 1024, 2) . " KB)\n";
    }
}

// 4. CORE SYSTEM FILES CHECK (ACCURATE)
echo "\n4. 🎯 CORE SYSTEM FILES CHECK (ACCURATE)\n";
echo "=====================================\n";

$coreFiles = [
    'Entry Points' => ['index.php', 'admin.php', 'api/index.php'],
    'Configuration' => ['.env', 'config/database.php', 'config/app.php', 'bootstrap.php'],
    'Database' => ['includes/db_connection.php', 'includes/db_settings.php'],
    'Security' => ['includes/session_helpers.php', '.htaccess'],
    'Routing' => ['routes/web.php', 'routes/api.php', 'app/core/App.php'],
    'Controllers' => ['app/controllers/', 'app/Http/Controllers/'],
    'Models' => ['app/models/', 'app/Models/'],
    'Views' => ['resources/views/', 'app/views/'],
    'Assets' => ['assets/', 'public/', 'resources/']
];

$foundCount = 0;
$missingCount = 0;

foreach ($coreFiles as $category => $files) {
    echo "\n📋 $category:\n";
    foreach ($files as $file) {
        $path = $projectRoot . '/' . $file;
        
        if (substr($file, -1) === '/') {
            // Check if directory exists
            if (is_dir($path)) {
                $items = glob($path . '*');
                $count = count($items);
                echo "   ✅ $file ($count files)\n";
                $foundCount++;
            } else {
                echo "   ❌ $file (missing)\n";
                $missingCount++;
            }
        } else {
            // Check if file exists
            if (file_exists($path)) {
                echo "   ✅ $file\n";
                $foundCount++;
            } else {
                echo "   ❌ $file (missing)\n";
                $missingCount++;
            }
        }
    }
}

// 5. ACCURATE PROJECT ASSESSMENT
echo "\n\n5. 🏆 ACCURATE PROJECT ASSESSMENT\n";
echo "===============================\n";

$coreCompletion = round(($foundCount / ($foundCount + $missingCount)) * 100);
$directoryScore = min(100, ($totalDirs / 100) * 100);
$fileScore = min(100, ($totalFiles / 1000) * 100);

$overallScore = round(($coreCompletion + $directoryScore + $fileScore) / 3);

echo "   Core Files Completion: $coreCompletion%\n";
echo "   Directory Score: " . round($directoryScore) . "/100\n";
echo "   File Score: " . round($fileScore) . "/100\n";
echo "   🎯 OVERALL SCORE: $overallScore/100\n";

$projectGrade = $overallScore >= 90 ? 'A+ (EXCELLENT)' :
               ($overallScore >= 80 ? 'A (VERY GOOD)' :
               ($overallScore >= 70 ? 'B+ (GOOD)' :
               ($overallScore >= 60 ? 'B (AVERAGE)' :
               ($overallScore >= 50 ? 'C+ (BELOW AVERAGE)' :
               ($overallScore >= 40 ? 'C (POOR)' : 'D (VERY POOR)')))));

echo "   🏅 Project Grade: $projectGrade\n";

// 6. SPECIFIC FEATURES COUNT
echo "\n\n6. 📊 SPECIFIC FEATURES COUNT\n";
echo "===========================\n";

$specificCounts = [
    'Admin Files' => 0,
    'Auth Files' => 0,
    'CSS Files' => 0,
    'Config Files' => 0,
    'Database Files' => 0
];

foreach ($scanResults['files'] as $ext => $files) {
    foreach ($files as $file) {
        $path = strtolower($file['path']);
        
        if (strpos($path, 'admin') !== false) $specificCounts['Admin Files']++;
        if (strpos($path, 'auth') !== false || strpos($path, 'login') !== false || strpos($path, 'session') !== false) $specificCounts['Auth Files']++;
        if ($ext === 'css') $specificCounts['CSS Files']++;
        if (strpos($path, 'config') !== false || $ext === 'env') $specificCounts['Config Files']++;
        if ($ext === 'sql' || strpos($path, 'database') !== false || strpos($path, 'db_') !== false) $specificCounts['Database Files']++;
    }
}

foreach ($specificCounts as $feature => $count) {
    echo "   $feature: $count\n";
}

echo "\n🎉 FIXED ACCURATE SCAN COMPLETED!\n";
echo "================================\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n";
echo "Project: APS Dream Home\n";
echo "Total Directories: $totalDirs\n";
echo "Total Files: $totalFiles\n";
echo "Project Size: " . round($totalSize / 1024 / 1024, 2) . " MB\n";
echo "Overall Score: $overallScore/100\n";
echo "Grade: $projectGrade\n";

?>
