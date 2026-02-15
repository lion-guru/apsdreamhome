<?php
/**
 * APS Dream Home - TRUE MAX LEVEL A-Z COMPLETE SCAN
 * Every single file, folder, subfolder - complete microscopic analysis
 */

echo "🏠 APS Dream Home - TRUE MAX LEVEL A-Z COMPLETE SCAN\n";
echo "===================================================\n\n";

$startTime = microtime(true);
$projectRoot = 'c:\\xampp\\htdocs\\apsdreamhome';
$completeResults = [];

// 1. COMPLETE DIRECTORY TREE SCAN
echo "1. 🌳 COMPLETE DIRECTORY TREE SCAN (A-Z)\n";
echo "========================================\n";

function scanDirectory($dir, $prefix = '') {
    global $projectRoot, $completeResults;
    
    $items = scandir($dir);
    $items = array_diff($items, ['.', '..']);
    sort($items, SORT_STRING | SORT_FLAG_CASE);
    
    foreach ($items as $item) {
        $fullPath = $dir . '/' . $item;
        $relativePath = str_replace($projectRoot . '/', '', $fullPath);
        
        if (is_dir($fullPath)) {
            echo $prefix . "📁 " . $item . "/\n";
            $completeResults['directories'][] = $relativePath;
            scanDirectory($fullPath, $prefix . "   ");
        } else {
            $size = filesize($fullPath);
            $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
            $icon = getFileIcon($ext);
            echo $prefix . $icon . " " . $item . " (" . formatBytes($size) . ")\n";
            $completeResults['files'][$ext][] = [
                'path' => $relativePath,
                'size' => $size,
                'name' => $item
            ];
        }
    }
}

function getFileIcon($ext) {
    $icons = [
        'php' => '🐘',
        'js' => '📜',
        'css' => '🎨',
        'html' => '🌐',
        'htm' => '🌐',
        'json' => '📋',
        'md' => '📝',
        'txt' => '📄',
        'sql' => '🗄️',
        'xml' => '📰',
        'yml' => '⚙️',
        'yaml' => '⚙️',
        'ini' => '⚙️',
        'env' => '🔐',
        'gitignore' => '🚫',
        'htaccess' => '🔒',
        'jpg' => '🖼️',
        'jpeg' => '🖼️',
        'png' => '🖼️',
        'gif' => '🖼️',
        'svg' => '🎨',
        'ico' => '🖼️',
        'pdf' => '📕',
        'zip' => '📦',
        'tar' => '📦',
        'gz' => '📦',
        'ttf' => '🔤',
        'woff' => '🔤',
        'woff2' => '🔤',
        'eot' => '🔤'
    ];
    return $icons[$ext] ?? '📄';
}

function formatBytes($bytes) {
    if ($bytes === 0) return '0 B';
    $k = 1024;
    $sizes = ['B', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

scanDirectory($projectRoot);

// 2. COMPREHENSIVE FILE STATISTICS
echo "\n\n2. 📊 COMPREHENSIVE FILE STATISTICS\n";
echo "==================================\n";

$totalFiles = 0;
$totalSize = 0;
$fileTypeStats = [];

if (isset($completeResults['files'])) {
    foreach ($completeResults['files'] as $ext => $files) {
        $count = count($files);
        $totalFiles += $count;
        $typeSize = array_sum(array_column($files, 'size'));
        $totalSize += $typeSize;
        $fileTypeStats[$ext] = ['count' => $count, 'size' => $typeSize];
        
        echo "   " . getFileIcon($ext) . " .$ext files: $count (" . formatBytes($typeSize) . ")\n";
    }
}

$totalDirectories = isset($completeResults['directories']) ? count($completeResults['directories']) : 0;

echo "\n   📁 Total Directories: $totalDirectories\n";
echo "   📄 Total Files: $totalFiles\n";
echo "   💾 Total Size: " . formatBytes($totalSize) . "\n";

// 3. DETAILED PHP ANALYSIS
echo "\n\n3. 🐘 DETAILED PHP ANALYSIS\n";
echo "==========================\n";

$phpFiles = $completeResults['files']['php'] ?? [];
$phpStats = [
    'total_lines' => 0,
    'functions' => 0,
    'classes' => 0,
    'interfaces' => 0,
    'traits' => 0,
    'namespaces' => 0,
    'uses' => 0,
    'comments' => 0,
    'complexity' => 0
];

foreach ($phpFiles as $file) {
    $fullPath = $projectRoot . '/' . $file['path'];
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        $lines = file($fullPath, FILE_IGNORE_NEW_LINES);
        
        $phpStats['total_lines'] += count($lines);
        $phpStats['functions'] += substr_count($content, 'function ');
        $phpStats['classes'] += substr_count($content, 'class ');
        $phpStats['interfaces'] += substr_count($content, 'interface ');
        $phpStats['traits'] += substr_count($content, 'trait ');
        $phpStats['namespaces'] += substr_count($content, 'namespace ');
        $phpStats['uses'] += substr_count($content, 'use ');
        $phpStats['comments'] += substr_count($content, '//') + substr_count($content, '#') + substr_count($content, '/*');
        
        // Calculate complexity (loops, conditions, etc.)
        $phpStats['complexity'] += substr_count($content, 'if ') + 
                                 substr_count($content, 'for ') + 
                                 substr_count($content, 'while ') + 
                                 substr_count($content, 'foreach ') + 
                                 substr_count($content, 'switch ');
    }
}

echo "   📏 Total Lines: {$phpStats['total_lines']}\n";
echo "   🔧 Functions: {$phpStats['functions']}\n";
echo "   🏗️  Classes: {$phpStats['classes']}\n";
echo "   🔌 Interfaces: {$phpStats['interfaces']}\n";
echo "   🧩 Traits: {$phpStats['traits']}\n";
echo "   📦 Namespaces: {$phpStats['namespaces']}\n";
echo "   📥 Use Statements: {$phpStats['uses']}\n";
echo "   💬 Comments: {$phpStats['comments']}\n";
echo "   🔄 Complexity Score: {$phpStats['complexity']}\n";

// 4. FEATURE DETECTION ANALYSIS
echo "\n\n4. 🚀 FEATURE DETECTION ANALYSIS\n";
echo "===============================\n";

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
    $featureFiles = 0;
    $featureDirs = 0;
    
    // Check in files
    foreach ($phpFiles as $file) {
        foreach ($keywords as $keyword) {
            if (strpos($file['path'], $keyword) !== false || strpos($file['name'], $keyword) !== false) {
                $featureFiles++;
                break;
            }
        }
    }
    
    // Check in directories
    if (isset($completeResults['directories'])) {
        foreach ($completeResults['directories'] as $dir) {
            foreach ($keywords as $keyword) {
                if (strpos($dir, $keyword) !== false) {
                    $featureDirs++;
                    break;
                }
            }
        }
    }
    
    $total = $featureFiles + $featureDirs;
    $status = $total > 0 ? "✅ Found ($total items)" : "❌ Not Found";
    echo "   $feature: $status\n";
}

// 5. ARCHITECTURE PATTERN ANALYSIS
echo "\n\n5. 🏗️ ARCHITECTURE PATTERN ANALYSIS\n";
echo "=================================\n";

$patterns = [
    'MVC' => ['Controller', 'Model', 'View', 'controllers', 'models', 'views'],
    'Repository' => ['Repository', 'repository'],
    'Service' => ['Service', 'service'],
    'Factory' => ['Factory', 'factory'],
    'Singleton' => ['Singleton', 'getInstance'],
    'Observer' => ['Observer', 'Subject', 'listener'],
    'Dependency Injection' => ['__construct', 'inject', 'container'],
    'Middleware' => ['Middleware', 'middleware'],
    'REST API' => ['GET', 'POST', 'PUT', 'DELETE', '@Route', 'api'],
    'Microservices' => ['microservice', 'service', 'endpoint']
];

foreach ($patterns as $pattern => $keywords) {
    $patternCount = 0;
    foreach ($phpFiles as $file) {
        $fullPath = $projectRoot . '/' . $file['path'];
        if (file_exists($fullPath)) {
            $content = file_get_contents($fullPath);
            foreach ($keywords as $keyword) {
                $patternCount += substr_count($content, $keyword);
            }
        }
    }
    $status = $patternCount > 0 ? "✅ $patternCount occurrences" : "❌ Not Found";
    echo "   $pattern: $status\n";
}

// 6. SECURITY ANALYSIS
echo "\n\n6. 🔒 SECURITY ANALYSIS\n";
echo "====================\n";

$securityChecks = [
    'SQL Injection Protection' => ['prepared', 'PDO', 'mysqli_prepare', 'bind_param'],
    'XSS Protection' => ['htmlspecialchars', 'strip_tags', 'filter_var'],
    'CSRF Protection' => ['csrf', 'token', 'CSRF'],
    'Password Security' => ['password_hash', 'password_verify', 'bcrypt'],
    'Session Security' => ['session_regenerate', 'session_destroy', 'secure'],
    'Input Validation' => ['filter_input', 'validate', 'sanitize'],
    'File Upload Security' => ['move_uploaded_file', 'file_type', 'mime'],
    'Encryption' => ['encrypt', 'decrypt', 'openssl', 'hash']
];

foreach ($securityChecks as $check => $keywords) {
    $checkCount = 0;
    foreach ($phpFiles as $file) {
        $fullPath = $projectRoot . '/' . $file['path'];
        if (file_exists($fullPath)) {
            $content = file_get_contents($fullPath);
            foreach ($keywords as $keyword) {
                $checkCount += substr_count(strtolower($content), strtolower($keyword));
            }
        }
    }
    $status = $checkCount > 0 ? "✅ $checkCount protections" : "⚠️  Check Needed";
    echo "   $check: $status\n";
}

// 7. PERFORMANCE ANALYSIS
echo "\n\n7. ⚡ PERFORMANCE ANALYSIS\n";
echo "========================\n";

$performanceMetrics = [
    'Cache Implementation' => ['cache', 'Cache', 'redis', 'memcached'],
    'Database Optimization' => ['index', 'optimize', 'query', 'JOIN'],
    'Lazy Loading' => ['lazy', 'defer', 'async'],
    'Minification' => ['minify', 'compress', 'optimize'],
    'CDN Usage' => ['cdn', 'CDN', 'cloudflare'],
    'Image Optimization' => ['image', 'optimize', 'resize', 'compress'],
    'Code Optimization' => ['optimize', 'performance', 'speed']
];

foreach ($performanceMetrics as $metric => $keywords) {
    $metricCount = 0;
    foreach ($phpFiles as $file) {
        $fullPath = $projectRoot . '/' . $file['path'];
        if (file_exists($fullPath)) {
            $content = file_get_contents($fullPath);
            foreach ($keywords as $keyword) {
                $metricCount += substr_count(strtolower($content), strtolower($keyword));
            }
        }
    }
    $status = $metricCount > 0 ? "✅ $metricCount implementations" : "⚠️  Not Found";
    echo "   $metric: $status\n";
}

// 8. DEPENDENCY ANALYSIS
echo "\n\n8. 📦 DEPENDENCY ANALYSIS\n";
echo "========================\n";

$dependencies = [
    'Composer Packages' => [],
    'NPM Packages' => [],
    'External Libraries' => [],
    'PHP Extensions' => []
];

// Check composer.json
if (file_exists($projectRoot . '/composer.json')) {
    $composer = json_decode(file_get_contents($projectRoot . '/composer.json'), true);
    if (isset($composer['require'])) {
        $dependencies['Composer Packages'] = array_keys($composer['require']);
    }
}

// Check package.json
if (file_exists($projectRoot . '/package.json')) {
    $package = json_decode(file_get_contents($projectRoot . '/package.json'), true);
    if (isset($package['dependencies'])) {
        $dependencies['NPM Packages'] = array_keys($package['dependencies']);
    }
}

// Scan for external library usage
foreach ($phpFiles as $file) {
    $fullPath = $projectRoot . '/' . $file['path'];
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        
        // Find use statements
        preg_match_all('/use\s+([^;]+);/', $content, $matches);
        if (!empty($matches[1])) {
            foreach ($matches[1] as $use) {
                if (strpos($use, 'App\\') !== 0 && strpos($use, 'APS\\') !== 0) {
                    $dependencies['External Libraries'][] = trim($use);
                }
            }
        }
    }
}

foreach ($dependencies as $type => $deps) {
    $count = count(array_unique($deps));
    echo "   $type: $count packages\n";
}

// 9. QUALITY METRICS
echo "\n\n9. 📈 QUALITY METRICS\n";
echo "===================\n";

$endTime = microtime(true);
$scanDuration = round($endTime - $startTime, 2);

// Calculate quality scores
$codeComplexity = $phpStats['complexity'] / max($phpStats['total_lines'], 1) * 100;
$commentRatio = $phpStats['comments'] / max($phpStats['total_lines'], 1) * 100;
$functionDensity = $phpStats['functions'] / max($phpStats['total_lines'], 1) * 1000;

echo "   ⏱️  Scan Duration: {$scanDuration} seconds\n";
echo "   📁 Total Directories: $totalDirectories\n";
echo "   📄 Total Files: $totalFiles\n";
echo "   💾 Project Size: " . formatBytes($totalSize) . "\n";
echo "   📏 Code Lines: {$phpStats['total_lines']}\n";
echo "   🔄 Complexity Score: " . round($codeComplexity, 2) . "%\n";
echo "   💬 Comment Ratio: " . round($commentRatio, 2) . "%\n";
echo "   🔧 Function Density: " . round($functionDensity, 2) . " per 1000 lines\n";

// 10. FINAL ASSESSMENT
echo "\n\n10. 🏆 FINAL ASSESSMENT\n";
echo "====================\n";

// Calculate overall score
$directoryScore = min(100, ($totalDirectories / 50) * 100);
$fileScore = min(100, ($totalFiles / 300) * 100);
$codeScore = min(100, ($phpStats['total_lines'] / 50000) * 100);
$featureScore = 0; // Will be calculated based on features found

// Count implemented features
$implementedFeatures = 0;
foreach ($features as $feature => $keywords) {
    $featureFiles = 0;
    foreach ($phpFiles as $file) {
        foreach ($keywords as $keyword) {
            if (strpos($file['path'], $keyword) !== false || strpos($file['name'], $keyword) !== false) {
                $featureFiles++;
                break;
            }
        }
    }
    if ($featureFiles > 0) $implementedFeatures++;
}

$featureScore = ($implementedFeatures / count($features)) * 100;

$overallScore = round(($directoryScore + $fileScore + $codeScore + $featureScore) / 4);

echo "   📊 Directory Score: " . round($directoryScore) . "/100\n";
echo "   📊 File Score: " . round($fileScore) . "/100\n";
echo "   📊 Code Score: " . round($codeScore) . "/100\n";
echo "   📊 Feature Score: " . round($featureScore) . "/100\n";
echo "   🎯 OVERALL SCORE: $overallScore/100\n";

$projectGrade = $overallScore >= 90 ? 'A+ (EXCELLENT)' :
               ($overallScore >= 80 ? 'A (VERY GOOD)' :
               ($overallScore >= 70 ? 'B+ (GOOD)' :
               ($overallScore >= 60 ? 'B (AVERAGE)' :
               ($overallScore >= 50 ? 'C+ (BELOW AVERAGE)' :
               ($overallScore >= 40 ? 'C (POOR)' : 'D (VERY POOR)')))));

echo "   🏅 Project Grade: $projectGrade\n";

echo "\n🎉 TRUE MAX LEVEL A-Z SCAN COMPLETED!\n";
echo "====================================\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n";
echo "Project: APS Dream Home\n";
echo "Total Items Scanned: " . ($totalDirectories + $totalFiles) . "\n";
echo "Scan Duration: {$scanDuration} seconds\n";
echo "Overall Score: $overallScore/100\n";
echo "Grade: $projectGrade\n";

?>
