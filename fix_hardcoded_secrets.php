<?php
/**
 * APS Dream Home - Critical Security Fixes
 * Remove hardcoded passwords and secrets from configuration files
 */

$projectRoot = dirname(__FILE__);
$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'phase' => 'security_fixes',
    'files_fixed' => [],
    'secrets_removed' => 0,
    'issues_found' => [],
    'recommendations' => []
];

echo "🔒 CRITICAL SECURITY FIXES\n";
echo "=========================\n\n";

// Function to safely replace sensitive data in files
function secureReplaceInFile($filePath, $search, $replace, &$results, $projectRoot) {
    if (!file_exists($filePath) || !is_readable($filePath)) {
        return false;
    }

    $content = file_get_contents($filePath);
    if ($content === false) {
        return false;
    }

    $originalContent = $content;
    $content = str_replace($search, $replace, $content);

    if ($content !== $originalContent) {
        $backupPath = $filePath . '.backup.' . date('Y-m-d-H-i-s');
        if (copy($filePath, $backupPath)) {
            if (file_put_contents($filePath, $content) !== false) {
                $results['files_fixed'][] = [
                    'file' => str_replace($projectRoot . '/', '', $filePath),
                    'backup' => str_replace($projectRoot . '/', '', $backupPath),
                    'changes' => "Replaced: " . substr($search, 0, 50) . "..."
                ];
                return true;
            }
        }
    }

    return false;
}

// 1. Fix hardcoded database credentials
echo "🗄️  Fixing Database Configuration\n";
echo "===============================\n";

$dbConfigFiles = [
    'config/database.php',
    'app/config/database.php',
    'database/.env',
    'tests/config/database.php'
];

foreach ($dbConfigFiles as $configFile) {
    $fullPath = $projectRoot . '/' . $configFile;
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);

        // Remove hardcoded passwords
        if (preg_match('/password.*=.*[\'"][^\'"]*[\'"]/', $content)) {
            $newContent = preg_replace('/(password.*=.*[\'"]).*?([\'"])/', '$1YOUR_DB_PASSWORD$2', $content);
            if ($newContent !== $content) {
                secureReplaceInFile($fullPath, $content, $newContent, $results, $projectRoot);
                $results['secrets_removed']++;
                echo "✅ Fixed database password in: {$configFile}\n";
            }
        }

        // Fix localhost references that might contain passwords
        if (strpos($content, 'localhost') !== false && strpos($content, 'password') !== false) {
            echo "⚠️  Found localhost with password reference in: {$configFile} - Manual review needed\n";
            $results['issues_found'][] = "Manual review needed for localhost+password in: {$configFile}";
        }
    }
}

// 2. Fix .env file issues
echo "\n🔐 Fixing Environment Configuration\n";
echo "===================================\n";

$envPath = $projectRoot . '/.env';
if (file_exists($envPath)) {
    $content = file_get_contents($envPath);
    $lines = explode("\n", $content);
    $newLines = [];

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            $newLines[] = $line;
            continue;
        }

        // Check for sensitive data patterns
        if (preg_match('/^(DB_PASSWORD|APP_KEY|MAIL_PASSWORD|API_KEY|SECRET_KEY)=/', $line)) {
            $key = explode('=', $line)[0];
            $newLines[] = $key . '=PLACEHOLDER_NEEDS_CONFIGURATION';
            $results['secrets_removed']++;
            echo "✅ Secured sensitive env var: {$key}\n";
        } else {
            $newLines[] = $line;
        }
    }

    $newContent = implode("\n", $newLines);
    if ($newContent !== $content) {
        secureReplaceInFile($envPath, $content, $newContent, $results, $projectRoot);
    }
}

// 3. Fix hardcoded secrets in PHP files
echo "\n🐘 Scanning PHP Files for Hardcoded Secrets\n";
echo "===========================================\n";

$phpFiles = glob($projectRoot . '/app/**/*.php');
$phpFiles = array_merge($phpFiles, glob($projectRoot . '/config/*.php'));

$secretsFound = 0;
foreach ($phpFiles as $phpFile) {
    $content = file_get_contents($phpFile);
    if ($content === false) continue;

    $relativePath = str_replace($projectRoot . '/', '', $phpFile);

    // Look for hardcoded passwords, API keys, etc.
    $patterns = [
        '/password.*=.*[\'"][^\'"]{3,}[\'"]/',
        '/api_key.*=.*[\'"][^\'"]{5,}[\'"]/',
        '/secret.*=.*[\'"][^\'"]{5,}[\'"]/',
        '/token.*=.*[\'"][^\'"]{10,}[\'"]/',
        '/key.*=.*[\'"][^\'"]{10,}[\'"]/'
    ];

    $fileChanged = false;
    $originalContent = $content;

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $content)) {
            // Replace with placeholder
            $content = preg_replace($pattern, '$1PLACEHOLDER_SECRET_VALUE$2', $content);
            $secretsFound++;
            $fileChanged = true;
        }
    }

    if ($fileChanged) {
        secureReplaceInFile($phpFile, $originalContent, $content, $results, $projectRoot);
        echo "✅ Removed hardcoded secrets from: {$relativePath}\n";
    }
}

echo "\n📊 Security Fixes Summary\n";
echo "========================\n";
echo "🔒 Secrets removed/replaced: " . $results['secrets_removed'] . "\n";
echo "📄 Files secured: " . count($results['files_fixed']) . "\n";

if (!empty($results['files_fixed'])) {
    echo "\n🛡️  Files Modified\n";
    echo "=================\n";
    foreach ($results['files_fixed'] as $file) {
        echo "📁 {$file['file']}\n";
        echo "   💾 Backup: {$file['backup']}\n";
        echo "   🔧 {$file['changes']}\n\n";
    }
}

if (!empty($results['issues_found'])) {
    echo "\n⚠️  Manual Review Required\n";
    echo "=========================\n";
    foreach ($results['issues_found'] as $issue) {
        echo "• {$issue}\n";
    }
}

echo "\n📋 Next Steps\n";
echo "=============\n";
echo "1. ✅ Update database passwords in production environment\n";
echo "2. ✅ Set proper APP_KEY in .env file\n";
echo "3. ✅ Configure mail server credentials\n";
echo "4. ✅ Review all PLACEHOLDER values before deployment\n";
echo "5. 🔄 Run next security fix: SQL injection vulnerabilities\n";

$results['summary'] = [
    'secrets_removed' => $results['secrets_removed'],
    'files_secured' => count($results['files_fixed']),
    'manual_review_needed' => count($results['issues_found'])
];

// Save results
$resultsFile = $projectRoot . '/security_fixes_results.json';
file_put_contents($resultsFile, json_encode($results, JSON_PRETTY_PRINT));

echo "\n💾 Results saved to: {$resultsFile}\n";
echo "\n✅ Critical security fixes completed!\n";

?>
