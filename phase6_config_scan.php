<?php
/**
 * APS Dream Home - Phase 6 Deep Scan: Configuration Files Review
 * Comprehensive analysis of all configuration files for correctness and security
 */

$projectRoot = dirname(__FILE__);
$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'phase' => 'config_files_scan',
    'summary' => [],
    'issues' => [],
    'security_risks' => [],
    'recommendations' => []
];

echo "⚙️  Phase 6: Configuration Files Deep Review\n";
echo "===========================================\n\n";

// Find all configuration files
function findConfigFiles($directory, $excludePatterns = []) {
    $configFiles = [];

    if (!is_dir($directory)) {
        return $configFiles;
    }

    $items = scandir($directory);
    if ($items === false) {
        return $configFiles;
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;

        $fullPath = $directory . '/' . $item;

        // Check exclusions
        $shouldExclude = false;
        foreach ($excludePatterns as $pattern) {
            if (strpos($fullPath, $pattern) !== false) {
                $shouldExclude = true;
                break;
            }
        }
        if ($shouldExclude) continue;

        if (is_dir($fullPath)) {
            $subFiles = findConfigFiles($fullPath, $excludePatterns);
            $configFiles = array_merge($configFiles, $subFiles);
        } elseif (is_file($fullPath)) {
            $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
            $filename = strtolower($item);

            // Config file patterns
            if ($ext === 'php' && (
                strpos($filename, 'config') !== false ||
                in_array($filename, ['database.php', 'app.php', 'mail.php', 'cache.php', 'session.php', 'auth.php', 'services.php'])
            )) {
                $configFiles[] = $fullPath;
            } elseif (in_array($ext, ['json', 'yaml', 'yml', 'xml', 'ini', 'env'])) {
                $configFiles[] = $fullPath;
            }
        }
    }

    return $configFiles;
}

$excludePatterns = [
    '/vendor/',
    '/node_modules/',
    '/storage/',
    '/cleanup_backup/',
    '/_backup_legacy_files/'
];

echo "🔎 Scanning for configuration files...\n";
$configFiles = findConfigFiles($projectRoot, $excludePatterns);

echo "📄 Found " . count($configFiles) . " configuration files\n\n";

$totalIssues = 0;
$securityRisks = 0;

function analyzeConfigFile($filePath, &$results) {
    global $totalIssues, $securityRisks;

    if (!file_exists($filePath) || !is_readable($filePath)) {
        $results['issues'][] = "Cannot read config file: {$filePath}";
        $totalIssues++;
        return;
    }

    $content = file_get_contents($filePath);
    if ($content === false) {
        $results['issues'][] = "Failed to read content: {$filePath}";
        $totalIssues++;
        return;
    }

    $relativePath = str_replace(dirname(__FILE__) . '/', '', $filePath);
    $filename = basename($filePath);
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

    $issues = [];
    $security = [];

    // PHP config file analysis
    if ($ext === 'php') {
        // Skip syntax check for now - would need external php -l command
        // Check for security issues
        if (strpos($content, 'password') !== false && strpos($content, '123456') !== false) {
            $security[] = "Weak default password found";
        }

        if (strpos($content, 'localhost') !== false && strpos($content, 'password') !== false) {
            $security[] = "Database credentials with localhost - ensure production settings";
        }

        if (preg_match('/debug.*=.*true/i', $content)) {
            $security[] = "Debug mode enabled - disable for production";
        }

        if (preg_match('/APP_KEY.*=.*[\'"]{0,1}[\'"]{0,1}/', $content)) {
            $security[] = "APP_KEY not properly set";
        }

        // Check for hardcoded secrets
        if (preg_match('/(secret|key|token).*[\'"][^\'"]*[\'"]/', $content)) {
            $security[] = "Potential hardcoded secrets found";
        }

        // Check for proper error handling
        if (strpos($content, 'display_errors') !== false && strpos($content, 'On') !== false) {
            $security[] = "Display errors enabled - disable for production";
        }

    }

    // JSON config file analysis
    elseif ($ext === 'json') {
        json_decode($content);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $issues[] = "Invalid JSON syntax: " . json_last_error_msg();
        }
    }

    // ENV file analysis
    elseif ($filename === '.env' || $ext === 'env') {
        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) continue;

            // Check for missing values
            if (strpos($line, '=') === false) {
                $issues[] = "Malformed environment variable: {$line}";
            }

            // Check for empty values
            if (preg_match('/^[^=]+=\s*$/', $line)) {
                $issues[] = "Empty environment variable value: {$line}";
            }

            // Security checks for sensitive data
            if (preg_match('/(password|secret|key|token)=/i', $line)) {
                $security[] = "Sensitive data in environment variable";
            }
        }
    }

    // YAML/XML analysis (basic)
    elseif (in_array($ext, ['yaml', 'yml', 'xml'])) {
        // Basic checks for these formats
        if (empty(trim($content))) {
            $issues[] = "Empty configuration file";
        }
    }

    // Specific config file checks
    switch ($filename) {
        case 'database.php':
            if (!preg_match('/host.*=>/', $content)) {
                $issues[] = "Database host not configured";
            }
            if (!preg_match('/database.*=>/', $content)) {
                $issues[] = "Database name not configured";
            }
            if (!preg_match('/username.*=>/', $content)) {
                $issues[] = "Database username not configured";
            }
            if (preg_match('/password.*=>.*[\'"][\'"]/', $content)) {
                $issues[] = "Database password appears to be empty";
            }
            break;

        case 'app.php':
        case 'application.php':
            if (!preg_match('/timezone.*=>/', $content)) {
                $issues[] = "Application timezone not configured";
            }
            break;

        case 'mail.php':
            if (!preg_match('/host.*=>/', $content)) {
                $issues[] = "Mail host not configured";
            }
            break;

        case 'composer.json':
            $composerData = json_decode($content, true);
            if ($composerData === null) {
                $issues[] = "Invalid composer.json syntax";
            } else {
                if (!isset($composerData['name'])) {
                    $issues[] = "Composer name not specified";
                }
                if (!isset($composerData['require'])) {
                    $issues[] = "No dependencies specified in composer.json";
                }
            }
            break;

        case 'package.json':
            $packageData = json_decode($content, true);
            if ($packageData === null) {
                $issues[] = "Invalid package.json syntax";
            } else {
                if (!isset($packageData['name'])) {
                    $issues[] = "Package name not specified";
                }
                if (!isset($packageData['scripts']) || !isset($packageData['scripts']['dev'])) {
                    $issues[] = "No dev script in package.json";
                }
            }
            break;
    }

    if (!empty($issues)) {
        $results['issues'] = array_merge($results['issues'], array_map(function($issue) use ($relativePath) {
            return "{$relativePath}: {$issue}";
        }, $issues));
        $totalIssues += count($issues);
    }

    if (!empty($security)) {
        $results['security_risks'] = array_merge($results['security_risks'], array_map(function($risk) use ($relativePath) {
            return "{$relativePath}: {$risk}";
        }, $security));
        $securityRisks += count($security);
    }
}

// Analyze each config file
$analyzed = 0;
foreach ($configFiles as $file) {
    $analyzed++;
    if ($analyzed % 5 === 0) {
        echo "  ⚙️  Analyzed {$analyzed}/" . count($configFiles) . " config files...\n";
    }
    analyzeConfigFile($file, $results);
}

echo "\n📊 Analysis Results\n";
echo "==================\n";
echo "⚙️  Configuration files analyzed: " . count($configFiles) . "\n";
echo "⚠️  General issues found: {$totalIssues}\n";
echo "🔒 Security risks found: {$securityRisks}\n";

if (!empty($results['issues'])) {
    echo "\n⚠️  Configuration Issues\n";
    echo "========================\n";
    $displayCount = min(15, count($results['issues']));
    for ($i = 0; $i < $displayCount; $i++) {
        echo "• {$results['issues'][$i]}\n";
    }
    if (count($results['issues']) > 15) {
        echo "... and " . (count($results['issues']) - 15) . " more issues\n";
    }
}

if (!empty($results['security_risks'])) {
    echo "\n🚨 Security Risks\n";
    echo "=================\n";
    $displayCount = min(10, count($results['security_risks']));
    for ($i = 0; $i < $displayCount; $i++) {
        echo "• {$results['security_risks'][$i]}\n";
    }
    if (count($results['security_risks']) > 10) {
        echo "... and " . (count($results['security_risks']) - 10) . " more risks\n";
    }
}

echo "\n📋 Recommendations\n";
echo "=================\n";
if ($securityRisks > 0) {
    echo "• Address all security risks immediately\n";
    echo "• Never commit sensitive data to version control\n";
    echo "• Use environment variables for secrets\n";
}
echo "• Fix all configuration syntax errors\n";
echo "• Complete missing configuration settings\n";
echo "• Create .env.example file for team members\n";
echo "• Validate configurations in staging before production\n";
echo "• Run Phase 7: Dependencies analysis\n";

$results['summary'] = [
    'config_files_analyzed' => count($configFiles),
    'total_issues' => $totalIssues,
    'security_risks' => $securityRisks
];

// Save results
$resultsFile = $projectRoot . '/deep_scan_phase6_results.json';
file_put_contents($resultsFile, json_encode($results, JSON_PRETTY_PRINT));

echo "\n💾 Results saved to: {$resultsFile}\n";
echo "\n✅ Phase 6 Complete - Ready for Phase 7!\n";

?>
