<?php
/**
 * APS Dream Home - Project Path & Routing Analyzer
 * Comprehensive analysis of all paths, routes, and links in the project
 */

echo "🔍 APS DREAM HOME - PROJECT PATH & ROUTING ANALYZER\n";
echo "===================================================\n\n";

// Load centralized path configuration
require_once __DIR__ . '/config/paths.php';

// Analysis results
$analysisResults = [];
$totalFiles = 0;
$issuesFound = 0;

echo "🔍 SCANNING PROJECT FOR PATH & ROUTING ISSUES...\n\n";

// 1. Check BASE_URL configuration
echo "Step 1: Checking BASE_URL configuration\n";
$baseUrlCheck = [
    'base_url_definition' => function() {
        $pathsFile = BASE_PATH . '/config/paths.php';
        if (!file_exists($pathsFile)) {
            return ['status' => 'ERROR', 'message' => 'paths.php not found'];
        }
        
        $content = file_get_contents($pathsFile);
        if (strpos($content, 'BASE_URL') === false) {
            return ['status' => 'ERROR', 'message' => 'BASE_URL not defined'];
        }
        
        // Extract BASE_URL value
        preg_match("/define\(['\"]BASE_URL['\"],\s*['\"]([^'\"]+)['\"]\)/", $content, $matches);
        if (isset($matches[1])) {
            $baseUrl = $matches[1];
            return ['status' => 'SUCCESS', 'base_url' => $baseUrl, 'message' => "BASE_URL: $baseUrl"];
        }
        
        return ['status' => 'ERROR', 'message' => 'Could not extract BASE_URL'];
    },
    'base_url_consistency' => function() {
        $baseUrl = defined('BASE_URL') ? BASE_URL : 'NOT_DEFINED';
        $currentProtocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $currentHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $expectedUrl = $currentProtocol . '://' . $currentHost . '/apsdreamhome';
        
        if ($baseUrl === $expectedUrl) {
            return ['status' => 'SUCCESS', 'message' => 'BASE_URL matches expected URL'];
        } else {
            return [
                'status' => 'WARNING', 
                'current' => $baseUrl,
                'expected' => $expectedUrl,
                'message' => 'BASE_URL may not match current environment'
            ];
        }
    }
];

foreach ($baseUrlCheck as $checkName => $checkFunction) {
    echo "   🔍 Checking $checkName...\n";
    $result = $checkFunction();
    $status = $result['status'] === 'SUCCESS' ? '✅' : ($result['status'] === 'WARNING' ? '⚠️' : '❌');
    echo "      $status {$result['message']}\n";
    
    $analysisResults['base_url'][$checkName] = $result;
    if ($result['status'] !== 'SUCCESS') {
        $issuesFound++;
    }
}

echo "\n";

// 2. Scan all PHP files for path issues
echo "Step 2: Scanning PHP files for path issues\n";
$phpFiles = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(BASE_PATH));

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $phpFiles[] = $file->getPathname();
    }
}

$totalFiles = count($phpFiles);
echo "   📁 Found $totalFiles PHP files\n";

$pathIssues = [];
$brokenLinks = [];

foreach ($phpFiles as $file) {
    $content = file_get_contents($file);
    $relativePath = str_replace(BASE_PATH . '/', '', $file);
    
    // Check for hardcoded paths
    if (strpos($content, 'apsdreamhome') !== false && strpos($content, 'BASE_URL') === false) {
        preg_match_all('/[\'"](\/?apsdreamhome\/[^\'"]+)[\'"]/', $content, $matches);
        if (!empty($matches[1])) {
            $pathIssues[$relativePath] = $matches[1];
        }
    }
    
    // Check for href attributes without BASE_URL
    preg_match_all('/href\s*=\s*[\'"]([^\'"]*)[\'"]/', $content, $hrefMatches);
    if (!empty($hrefMatches[1])) {
        foreach ($hrefMatches[1] as $href) {
            if (strpos($href, 'http') !== 0 && strpos($href, 'BASE_URL') === false && !empty($href)) {
                $brokenLinks[$relativePath][] = $href;
            }
        }
    }
    
    // Check for action attributes without BASE_URL
    preg_match_all('/action\s*=\s*[\'"]([^\'"]*)[\'"]/', $content, $actionMatches);
    if (!empty($actionMatches[1])) {
        foreach ($actionMatches[1] as $action) {
            if (strpos($action, 'http') !== 0 && strpos($action, 'BASE_URL') === false && !empty($action)) {
                $brokenLinks[$relativePath][] = $action;
            }
        }
    }
}

echo "   📊 Path issues found in " . count($pathIssues) . " files\n";
echo "   🔗 Broken links found in " . count($brokenLinks) . " files\n";

$analysisResults['path_issues'] = $pathIssues;
$analysisResults['broken_links'] = $brokenLinks;
$issuesFound += count($pathIssues) + count($brokenLinks);

echo "\n";

// 3. Check .htaccess configuration
echo "Step 3: Checking .htaccess configuration\n";
$htaccessCheck = [
    'public_htaccess' => function() {
        $htaccessPath = BASE_PATH . '/public/.htaccess';
        if (!file_exists($htaccessPath)) {
            return ['status' => 'ERROR', 'message' => 'public/.htaccess not found'];
        }
        
        $content = file_get_contents($htaccessPath);
        $issues = [];
        
        // Check for RewriteEngine
        if (strpos($content, 'RewriteEngine On') === false) {
            $issues[] = 'RewriteEngine not enabled';
        }
        
        // Check for RewriteBase
        if (strpos($content, 'RewriteBase') === false) {
            $issues[] = 'RewriteBase not set';
        }
        
        // Check for proper rewrite rules
        if (strpos($content, 'RewriteRule') === false) {
            $issues[] = 'No rewrite rules found';
        }
        
        if (empty($issues)) {
            return ['status' => 'SUCCESS', 'message' => '.htaccess properly configured'];
        } else {
            return ['status' => 'WARNING', 'issues' => $issues, 'message' => '.htaccess has issues'];
        }
    },
    'root_htaccess' => function() {
        $htaccessPath = BASE_PATH . '/.htaccess';
        if (!file_exists($htaccessPath)) {
            return ['status' => 'WARNING', 'message' => 'Root .htaccess not found (may be needed)'];
        }
        
        return ['status' => 'SUCCESS', 'message' => 'Root .htaccess exists'];
    }
];

foreach ($htaccessCheck as $checkName => $checkFunction) {
    echo "   🔍 Checking $checkName...\n";
    $result = $checkFunction();
    $status = $result['status'] === 'SUCCESS' ? '✅' : ($result['status'] === 'WARNING' ? '⚠️' : '❌');
    echo "      $status {$result['message']}\n";
    
    $analysisResults['htaccess'][$checkName] = $result;
    if ($result['status'] !== 'SUCCESS') {
        $issuesFound++;
    }
}

echo "\n";

// 4. Check routing configuration
echo "Step 4: Checking routing configuration\n";
$routingCheck = [
    'app_routing' => function() {
        $appFile = BASE_PATH . '/app/Core/App.php';
        if (!file_exists($appFile)) {
            return ['status' => 'ERROR', 'message' => 'App.php not found'];
        }
        
        $content = file_get_contents($appFile);
        
        // Check for routing methods
        $hasRouting = strpos($content, 'route') !== false || strpos($content, 'handleRequest') !== false;
        
        if ($hasRouting) {
            return ['status' => 'SUCCESS', 'message' => 'Routing system found'];
        } else {
            return ['status' => 'WARNING', 'message' => 'No clear routing system detected'];
        }
    },
    'controller_existence' => function() {
        $controllerDir = BASE_PATH . '/app/Http/Controllers';
        if (!is_dir($controllerDir)) {
            return ['status' => 'ERROR', 'message' => 'Controllers directory not found'];
        }
        
        $controllers = glob($controllerDir . '/*.php');
        return [
            'status' => 'SUCCESS',
            'count' => count($controllers),
            'controllers' => array_map(function($c) { return basename($c, '.php'); }, $controllers),
            'message' => "Found " . count($controllers) . " controllers"
        ];
    },
    'view_existence' => function() {
        $viewDir = BASE_PATH . '/app/views';
        if (!is_dir($viewDir)) {
            return ['status' => 'ERROR', 'message' => 'Views directory not found'];
        }
        
        $views = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewDir));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $views[] = str_replace($viewDir . '/', '', $file->getPathname());
            }
        }
        
        return [
            'status' => 'SUCCESS',
            'count' => count($views),
            'views' => $views,
            'message' => "Found " . count($views) . " view files"
        ];
    }
];

foreach ($routingCheck as $checkName => $checkFunction) {
    echo "   🔍 Checking $checkName...\n";
    $result = $checkFunction();
    $status = $result['status'] === 'SUCCESS' ? '✅' : ($result['status'] === 'WARNING' ? '⚠️' : '❌');
    echo "      $status {$result['message']}\n";
    
    $analysisResults['routing'][$checkName] = $result;
    if ($result['status'] !== 'SUCCESS') {
        $issuesFound++;
    }
}

echo "\n";

// 5. Generate detailed report
echo "Step 5: Generating detailed analysis report\n";
$report = [
    'timestamp' => date('Y-m-d H:i:s'),
    'total_files_scanned' => $totalFiles,
    'total_issues_found' => $issuesFound,
    'base_url_analysis' => $analysisResults['base_url'],
    'path_issues' => $analysisResults['path_issues'],
    'broken_links' => $analysisResults['broken_links'],
    'htaccess_analysis' => $analysisResults['htaccess'],
    'routing_analysis' => $analysisResults['routing'],
    'summary' => [
        'critical_issues' => 0,
        'warnings' => 0,
        'recommendations' => []
    ]
];

// Count issues by severity
foreach ($analysisResults as $category) {
    foreach ($category as $check) {
        if ($check['status'] === 'ERROR') {
            $report['summary']['critical_issues']++;
        } elseif ($check['status'] === 'WARNING') {
            $report['summary']['warnings']++;
        }
    }
}

// Add recommendations
if ($report['summary']['critical_issues'] > 0) {
    $report['summary']['recommendations'][] = 'Fix critical path and routing issues immediately';
}
if (!empty($pathIssues)) {
    $report['summary']['recommendations'][] = 'Replace hardcoded paths with BASE_URL constants';
}
if (!empty($brokenLinks)) {
    $report['summary']['recommendations'][] = 'Fix broken links and action attributes';
}
if ($report['summary']['warnings'] > 0) {
    $report['summary']['recommendations'][] = 'Review and address warning-level issues';
}

// Save report
$reportFile = BASE_PATH . '/logs/project_path_routing_analysis.json';
file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT));
echo "   ✅ Report saved to: $reportFile\n";

echo "\n";

// 6. Display summary
echo "====================================================\n";
echo "🔍 PROJECT PATH & ROUTING ANALYSIS SUMMARY\n";
echo "====================================================\n";

echo "📊 FILES SCANNED: $totalFiles\n";
echo "🚨 ISSUES FOUND: $issuesFound\n";
echo "❌ CRITICAL ISSUES: {$report['summary']['critical_issues']}\n";
echo "⚠️  WARNINGS: {$report['summary']['warnings']}\n\n";

if (!empty($pathIssues)) {
    echo "📁 FILES WITH PATH ISSUES:\n";
    foreach ($pathIssues as $file => $issues) {
        echo "   🔗 $file:\n";
        foreach ($issues as $issue) {
            echo "      - $issue\n";
        }
    }
    echo "\n";
}

if (!empty($brokenLinks)) {
    echo "🔗 FILES WITH BROKEN LINKS:\n";
    foreach ($brokenLinks as $file => $links) {
        echo "   🔗 $file:\n";
        foreach ($links as $link) {
            echo "      - $link\n";
        }
    }
    echo "\n";
}

echo "📋 RECOMMENDATIONS:\n";
foreach ($report['summary']['recommendations'] as $rec) {
    echo "   • $rec\n";
}

echo "\n";

if ($issuesFound === 0) {
    echo "🎉 ANALYSIS RESULT: EXCELLENT!\n";
    echo "✅ No path or routing issues found\n";
} elseif ($report['summary']['critical_issues'] === 0) {
    echo "✅ ANALYSIS RESULT: GOOD!\n";
    echo "⚠️  Some issues found but none critical\n";
} else {
    echo "❌ ANALYSIS RESULT: NEEDS ATTENTION!\n";
    echo "🚨 Critical issues found that need immediate fixing\n";
}

echo "\n🎯 NEXT STEPS:\n";
echo "1. Review detailed report in logs/project_path_routing_analysis.json\n";
echo "2. Fix critical path issues first\n";
echo "3. Update hardcoded paths to use BASE_URL\n";
echo "4. Fix broken links and navigation\n";
echo "5. Test all navigation links\n";
echo "6. Verify routing functionality\n";

echo "\n🔧 AUTO-FIX SUGGESTIONS:\n";
echo "1. Run: php AUTO_FIX_PATHS.php (will be created)\n";
echo "2. Update .htaccess configuration\n";
echo "3. Fix BASE_URL in config/paths.php\n";
echo "4. Test navigation functionality\n";

echo "\n🎊 PATH & ROUTING ANALYSIS COMPLETE! 🎊\n";
?>
