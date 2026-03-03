<?php
/**
 * APS Dream Home - Verify Paths Fix
 * Verify that all path and routing issues have been resolved
 */

echo "🔍 APS DREAM HOME - VERIFY PATHS FIX\n";
echo "===================================\n\n";

// Load centralized path configuration
require_once __DIR__ . '/config/paths.php';

// Verification results
$verificationResults = [];
$totalChecks = 0;
$passedChecks = 0;

echo "🔍 VERIFYING ALL PATH & ROUTING FIXES...\n\n";

// 1. Verify BASE_URL configuration
echo "Step 1: Verifying BASE_URL configuration\n";
$baseUrlVerification = [
    'base_url_defined' => function() {
        if (!defined('BASE_URL')) {
            return ['status' => 'ERROR', 'message' => 'BASE_URL not defined'];
        }
        
        $baseUrl = BASE_URL;
        if (empty($baseUrl)) {
            return ['status' => 'ERROR', 'message' => 'BASE_URL is empty'];
        }
        
        // Check if BASE_URL is properly formatted
        if (!filter_var($baseUrl, FILTER_VALIDATE_URL)) {
            return ['status' => 'WARNING', 'message' => 'BASE_URL may not be a valid URL'];
        }
        
        return ['status' => 'SUCCESS', 'base_url' => $baseUrl, 'message' => "BASE_URL properly defined: $baseUrl"];
    },
    'base_url_consistency' => function() {
        $baseUrl = BASE_URL;
        $currentProtocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $currentHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $expectedUrl = $currentProtocol . '://' . $currentHost . '/apsdreamhome';
        
        if ($baseUrl === $expectedUrl) {
            return ['status' => 'SUCCESS', 'message' => 'BASE_URL matches expected URL'];
        } else {
            return [
                'status' => 'INFO',
                'current' => $baseUrl,
                'expected' => $expectedUrl,
                'message' => 'BASE_URL: ' . $baseUrl . ' (Expected: ' . $expectedUrl . ')'
            ];
        }
    }
];

foreach ($baseUrlVerification as $checkName => $checkFunction) {
    echo "   🔍 Checking $checkName...\n";
    $result = $checkFunction();
    $status = $result['status'] === 'SUCCESS' ? '✅' : ($result['status'] === 'INFO' ? 'ℹ️' : ($result['status'] === 'WARNING' ? '⚠️' : '❌'));
    echo "      $status {$result['message']}\n";
    
    $verificationResults['base_url'][$checkName] = $result;
    if ($result['status'] === 'SUCCESS') {
        $passedChecks++;
    }
    $totalChecks++;
}

echo "\n";

// 2. Verify .htaccess configuration
echo "Step 2: Verifying .htaccess configuration\n";
$htaccessVerification = [
    'public_htaccess_exists' => function() {
        $htaccessPath = BASE_PATH . '/public/.htaccess';
        if (!file_exists($htaccessPath)) {
            return ['status' => 'ERROR', 'message' => 'public/.htaccess not found'];
        }
        
        $content = file_get_contents($htaccessPath);
        $checks = [];
        
        // Check for essential directives
        $checks[] = ['RewriteEngine On', strpos($content, 'RewriteEngine On') !== false];
        $checks[] = ['RewriteBase', strpos($content, 'RewriteBase') !== false];
        $checks[] = ['RewriteRule', strpos($content, 'RewriteRule') !== false];
        
        $failedChecks = array_filter($checks, function($check) { return !$check[1]; });
        
        if (empty($failedChecks)) {
            return ['status' => 'SUCCESS', 'message' => 'public/.htaccess properly configured'];
        } else {
            $missing = array_map(function($check) { return $check[0]; }, $failedChecks);
            return ['status' => 'WARNING', 'missing' => $missing, 'message' => 'Missing directives: ' . implode(', ', $missing)];
        }
    },
    'root_htaccess_exists' => function() {
        $htaccessPath = BASE_PATH . '/.htaccess';
        if (!file_exists($htaccessPath)) {
            return ['status' => 'WARNING', 'message' => 'Root .htaccess not found (optional)'];
        }
        
        $content = file_get_contents($htaccessPath);
        if (strpos($content, 'RewriteEngine On') !== false) {
            return ['status' => 'SUCCESS', 'message' => 'Root .htaccess configured'];
        } else {
            return ['status' => 'WARNING', 'message' => 'Root .htaccess may need configuration'];
        }
    }
];

foreach ($htaccessVerification as $checkName => $checkFunction) {
    echo "   🔍 Checking $checkName...\n";
    $result = $checkFunction();
    $status = $result['status'] === 'SUCCESS' ? '✅' : ($result['status'] === 'INFO' ? 'ℹ️' : ($result['status'] === 'WARNING' ? '⚠️' : '❌'));
    echo "      $status {$result['message']}\n";
    
    $verificationResults['htaccess'][$checkName] = $result;
    if ($result['status'] === 'SUCCESS') {
        $passedChecks++;
    }
    $totalChecks++;
}

echo "\n";

// 3. Verify URL helper functions
echo "Step 3: Verifying URL helper functions\n";
$helperVerification = [
    'url_helper_exists' => function() {
        $helperFile = BASE_PATH . '/app/Helpers/UrlHelper.php';
        if (!file_exists($helperFile)) {
            return ['status' => 'ERROR', 'message' => 'UrlHelper.php not found'];
        }
        
        $content = file_get_contents($helperFile);
        $functions = ['base_url', 'asset_url', 'route_url', 'current_url'];
        $missingFunctions = [];
        
        foreach ($functions as $func) {
            if (strpos($content, 'function ' . $func) === false) {
                $missingFunctions[] = $func;
            }
        }
        
        if (empty($missingFunctions)) {
            return ['status' => 'SUCCESS', 'message' => 'All URL helper functions present'];
        } else {
            return ['status' => 'WARNING', 'missing' => $missingFunctions, 'message' => 'Missing functions: ' . implode(', ', $missingFunctions)];
        }
    },
    'composer_autoload_updated' => function() {
        $composerFile = BASE_PATH . '/composer.json';
        if (!file_exists($composerFile)) {
            return ['status' => 'ERROR', 'message' => 'composer.json not found'];
        }
        
        $content = file_get_contents($composerFile);
        $data = json_decode($content, true);
        
        if (isset($data['autoload']['files']) && in_array('app/Helpers/UrlHelper.php', $data['autoload']['files'])) {
            return ['status' => 'SUCCESS', 'message' => 'Composer autoload includes URL helper'];
        } else {
            return ['status' => 'WARNING', 'message' => 'URL helper not in composer autoload'];
        }
    }
];

foreach ($helperVerification as $checkName => $checkFunction) {
    echo "   🔍 Checking $checkName...\n";
    $result = $checkFunction();
    $status = $result['status'] === 'SUCCESS' ? '✅' : ($result['status'] === 'INFO' ? 'ℹ️' : ($result['status'] === 'WARNING' ? '⚠️' : '❌'));
    echo "      $status {$result['message']}\n";
    
    $verificationResults['helpers'][$checkName] = $result;
    if ($result['status'] === 'SUCCESS') {
        $passedChecks++;
    }
    $totalChecks++;
}

echo "\n";

// 4. Verify path fixes in key files
echo "Step 4: Verifying path fixes in key files\n";
$pathVerification = [
    'index_php_fixed' => function() {
        $indexFile = BASE_PATH . '/public/index.php';
        if (!file_exists($indexFile)) {
            return ['status' => 'ERROR', 'message' => 'index.php not found'];
        }
        
        $content = file_get_contents($indexFile);
        
        // Check for timezone fix
        if (strpos($content, 'date_default_timezone_set') === false) {
            return ['status' => 'WARNING', 'message' => 'Timezone fix not found in index.php'];
        }
        
        // Check for hardcoded paths
        if (strpos($content, 'apsdreamhome') !== false && strpos($content, 'BASE_URL') === false) {
            return ['status' => 'WARNING', 'message' => 'May still have hardcoded paths'];
        }
        
        return ['status' => 'SUCCESS', 'message' => 'index.php properly configured'];
    },
    'config_paths_fixed' => function() {
        $pathsFile = BASE_PATH . '/config/paths.php';
        if (!file_exists($pathsFile)) {
            return ['status' => 'ERROR', 'message' => 'paths.php not found'];
        }
        
        $content = file_get_contents($pathsFile);
        
        if (strpos($content, 'BASE_URL') === false) {
            return ['status' => 'ERROR', 'message' => 'BASE_URL not defined in paths.php'];
        }
        
        return ['status' => 'SUCCESS', 'message' => 'paths.php properly configured'];
    },
    'sample_view_files' => function() {
        $viewDir = BASE_PATH . '/app/views';
        if (!is_dir($viewDir)) {
            return ['status' => 'ERROR', 'message' => 'Views directory not found'];
        }
        
        $issues = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewDir));
        $filesChecked = 0;
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php' && $filesChecked < 10) {
                $content = file_get_contents($file->getPathname());
                
                // Check for hardcoded paths
                if (strpos($content, 'apsdreamhome') !== false && strpos($content, 'BASE_URL') === false) {
                    $issues[] = str_replace(BASE_PATH . '/', '', $file->getPathname());
                }
                
                $filesChecked++;
            }
        }
        
        if (empty($issues)) {
            return ['status' => 'SUCCESS', 'checked' => $filesChecked, 'message' => "Sample view files clean (checked $filesChecked files)"];
        } else {
            return ['status' => 'WARNING', 'issues' => $issues, 'message' => 'Some view files may still have hardcoded paths'];
        }
    }
];

foreach ($pathVerification as $checkName => $checkFunction) {
    echo "   🔍 Checking $checkName...\n";
    $result = $checkFunction();
    $status = $result['status'] === 'SUCCESS' ? '✅' : ($result['status'] === 'INFO' ? 'ℹ️' : ($result['status'] === 'WARNING' ? '⚠️' : '❌'));
    echo "      $status {$result['message']}\n";
    
    $verificationResults['paths'][$checkName] = $result;
    if ($result['status'] === 'SUCCESS') {
        $passedChecks++;
    }
    $totalChecks++;
}

echo "\n";

// 5. Test URL generation
echo "Step 5: Testing URL generation\n";
$urlTest = [
    'base_url_function' => function() {
        if (!function_exists('base_url')) {
            // Try to include the helper
            $helperFile = BASE_PATH . '/app/Helpers/UrlHelper.php';
            if (file_exists($helperFile)) {
                include $helperFile;
            }
        }
        
        if (!function_exists('base_url')) {
            return ['status' => 'ERROR', 'message' => 'base_url function not available'];
        }
        
        $testUrl = base_url('test');
        $expected = BASE_URL . '/test';
        
        if ($testUrl === $expected) {
            return ['status' => 'SUCCESS', 'url' => $testUrl, 'message' => 'base_url() works correctly'];
        } else {
            return [
                'status' => 'ERROR',
                'got' => $testUrl,
                'expected' => $expected,
                'message' => 'base_url() not working correctly'
            ];
        }
    },
    'asset_url_function' => function() {
        if (!function_exists('asset_url')) {
            // Try to include the helper
            $helperFile = BASE_PATH . '/app/Helpers/UrlHelper.php';
            if (file_exists($helperFile)) {
                include $helperFile;
            }
        }
        
        if (!function_exists('asset_url')) {
            return ['status' => 'ERROR', 'message' => 'asset_url function not available'];
        }
        
        $testUrl = asset_url('css/style.css');
        $expected = BASE_URL . '/public/assets/css/style.css';
        
        if ($testUrl === $expected) {
            return ['status' => 'SUCCESS', 'url' => $testUrl, 'message' => 'asset_url() works correctly'];
        } else {
            return [
                'status' => 'ERROR',
                'got' => $testUrl,
                'expected' => $expected,
                'message' => 'asset_url() not working correctly'
            ];
        }
    }
];

foreach ($urlTest as $checkName => $checkFunction) {
    echo "   🔍 Testing $checkName...\n";
    $result = $checkFunction();
    $status = $result['status'] === 'SUCCESS' ? '✅' : ($result['status'] === 'INFO' ? 'ℹ️' : ($result['status'] === 'WARNING' ? '⚠️' : '❌'));
    echo "      $status {$result['message']}\n";
    
    $verificationResults['url_test'][$checkName] = $result;
    if ($result['status'] === 'SUCCESS') {
        $passedChecks++;
    }
    $totalChecks++;
}

echo "\n";

// 6. Check for remaining issues
echo "Step 6: Checking for remaining issues\n";
$remainingIssues = [
    'scan_for_hardcoded_paths' => function() {
        $issues = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(BASE_PATH));
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $relativePath = str_replace(BASE_PATH . '/', '', $file->getPathname());
                
                // Skip backup, vendor, and deployment_package directories
                if (strpos($relativePath, '_backup') !== false || 
                    strpos($relativePath, 'vendor') !== false || 
                    strpos($relativePath, 'deployment_package') !== false) {
                    continue;
                }
                
                $content = file_get_contents($file->getPathname());
                
                // Look for remaining hardcoded paths
                if (preg_match('/[\'"](\/?apsdreamhome\/[^\'"]+)[\'"]/', $content, $matches) && 
                    strpos($content, 'BASE_URL') === false) {
                    $issues[] = [
                        'file' => $relativePath,
                        'issue' => $matches[1]
                    ];
                }
            }
        }
        
        if (empty($issues)) {
            return ['status' => 'SUCCESS', 'message' => 'No hardcoded paths found'];
        } else {
            return [
                'status' => 'WARNING',
                'count' => count($issues),
                'issues' => array_slice($issues, 0, 10), // Show first 10
                'message' => 'Found ' . count($issues) . ' remaining hardcoded paths'
            ];
        }
    },
    'check_broken_links' => function() {
        $brokenLinks = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(BASE_PATH . '/app/views'));
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());
                $relativePath = str_replace(BASE_PATH . '/app/views/', '', $file->getPathname());
                
                // Check for href without BASE_URL
                preg_match_all('/href\s*=\s*([\'"])(?!http|https|\/\/|#|BASE_URL)([a-zA-Z0-9\/\._-]+)([\'"])/', $content, $matches);
                if (!empty($matches[2])) {
                    foreach ($matches[2] as $link) {
                        $brokenLinks[] = [
                            'file' => $relativePath,
                            'link' => $link
                        ];
                    }
                }
            }
        }
        
        if (empty($brokenLinks)) {
            return ['status' => 'SUCCESS', 'message' => 'No broken links found'];
        } else {
            return [
                'status' => 'WARNING',
                'count' => count($brokenLinks),
                'links' => array_slice($brokenLinks, 0, 10), // Show first 10
                'message' => 'Found ' . count($brokenLinks) . ' potentially broken links'
            ];
        }
    }
];

foreach ($remainingIssues as $checkName => $checkFunction) {
    echo "   🔍 Checking $checkName...\n";
    $result = $checkFunction();
    $status = $result['status'] === 'SUCCESS' ? '✅' : ($result['status'] === 'INFO' ? 'ℹ️' : ($result['status'] === 'WARNING' ? '⚠️' : '❌'));
    echo "      $status {$result['message']}\n";
    
    $verificationResults['remaining_issues'][$checkName] = $result;
    if ($result['status'] === 'SUCCESS') {
        $passedChecks++;
    }
    $totalChecks++;
}

echo "\n";

// 7. Generate verification report
echo "Step 7: Generating verification report\n";
$report = [
    'timestamp' => date('Y-m-d H:i:s'),
    'total_checks' => $totalChecks,
    'passed_checks' => $passedChecks,
    'success_rate' => round(($passedChecks / $totalChecks) * 100, 1),
    'base_url' => BASE_URL,
    'verification_results' => $verificationResults,
    'summary' => [
        'critical_issues' => 0,
        'warnings' => 0,
        'info_messages' => 0,
        'recommendations' => []
    ]
];

// Count issues by severity
foreach ($verificationResults as $category) {
    foreach ($category as $check) {
        if ($check['status'] === 'ERROR') {
            $report['summary']['critical_issues']++;
        } elseif ($check['status'] === 'WARNING') {
            $report['summary']['warnings']++;
        } elseif ($check['status'] === 'INFO') {
            $report['summary']['info_messages']++;
        }
    }
}

// Add recommendations
if ($report['summary']['critical_issues'] > 0) {
    $report['summary']['recommendations'][] = 'Fix critical issues immediately';
}
if ($report['summary']['warnings'] > 0) {
    $report['summary']['recommendations'][] = 'Review and address warnings';
}
if ($passedChecks === $totalChecks) {
    $report['summary']['recommendations'][] = 'All checks passed - system ready';
}

// Save report
$reportFile = BASE_PATH . '/logs/verify_paths_fix_report.json';
file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT));
echo "   ✅ Report saved to: $reportFile\n";

echo "\n";

// 8. Display final summary
echo "====================================================\n";
echo "🔍 PATHS FIX VERIFICATION SUMMARY\n";
echo "====================================================\n";

echo "📊 TOTAL CHECKS: $totalChecks\n";
echo "✅ PASSED CHECKS: $passedChecks\n";
echo "📊 SUCCESS RATE: " . round(($passedChecks / $totalChecks) * 100, 1) . "%\n";
echo "❌ CRITICAL ISSUES: {$report['summary']['critical_issues']}\n";
echo "⚠️  WARNINGS: {$report['summary']['warnings']}\n";
echo "ℹ️  INFO: {$report['summary']['info_messages']}\n\n";

echo "📋 VERIFICATION DETAILS:\n";
foreach ($verificationResults as $category => $checks) {
    echo "🔍 $category:\n";
    foreach ($checks as $checkName => $result) {
        $status = $result['status'] === 'SUCCESS' ? '✅' : ($result['status'] === 'INFO' ? 'ℹ️' : ($result['status'] === 'WARNING' ? '⚠️' : '❌'));
        echo "   $status $checkName: {$result['message']}\n";
    }
    echo "\n";
}

echo "🎯 CURRENT STATUS:\n";
if ($passedChecks === $totalChecks) {
    echo "🎉 VERIFICATION RESULT: PERFECT!\n";
    echo "✅ All checks passed - path fixes completely successful\n";
    echo "🚀 System ready for production use\n";
} elseif ($report['summary']['critical_issues'] === 0) {
    echo "✅ VERIFICATION RESULT: GOOD!\n";
    echo "⚠️  No critical issues, some warnings may need attention\n";
    echo "🚀 System mostly ready, minor tweaks recommended\n";
} else {
    echo "❌ VERIFICATION RESULT: NEEDS ATTENTION!\n";
    echo "🚨 Critical issues found that need immediate fixing\n";
    echo "⚠️  System not ready until critical issues are resolved\n";
}

echo "\n📋 RECOMMENDATIONS:\n";
foreach ($report['summary']['recommendations'] as $rec) {
    echo "   • $rec\n";
}

echo "\n🎯 NEXT STEPS:\n";
echo "1. Test navigation in browser\n";
echo "2. Verify all pages load correctly\n";
echo "3. Test form submissions\n";
echo "4. Check image and asset loading\n";
echo "5. Test user authentication flows\n";
echo "6. Verify mobile responsiveness\n";

echo "\n🎊 PATHS FIX VERIFICATION COMPLETE! 🎊\n";
echo "📊 Status: " . ($passedChecks === $totalChecks ? 'PERFECT' : ($report['summary']['critical_issues'] === 0 ? 'GOOD' : 'NEEDS_ATTENTION')) . "\n";
?>
