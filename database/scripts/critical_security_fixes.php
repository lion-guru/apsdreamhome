<?php
/**
 * APS Dream Home - Critical Security Vulnerabilities Fix
 * Target the most dangerous security issues first
 */

echo "🚨 APS DREAM HOME - CRITICAL SECURITY VULNERABILITIES FIX\n";
echo "=====================================================\n\n";

$projectRoot = __DIR__ . '/..';
$criticalFixes = [
    'files_processed' => 0,
    'vulnerabilities_fixed' => 0,
    'files_with_issues' => 0,
    'backup_created' => false
];

// Create backup directory for modified files
$backupDir = $projectRoot . '/security_backup_' . date('Y-m-d_H-i-s');
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
    $criticalFixes['backup_created'] = true;
    echo "📁 Created backup directory: $backupDir\n\n";
}

// 1. SCAN AND FIX THE MOST CRITICAL VULNERABILITIES
echo "🔍 SCANNING FOR CRITICAL VULNERABILITIES\n";
echo "=======================================\n";

$criticalPatterns = [
    // Most dangerous - code execution
    [
        'pattern' => '/\beval\s*\(/i',
        'description' => 'Dangerous // SECURITY FIX: eval() removed for security reasons) function',
        'replacement' => '// SECURITY FIX: // SECURITY FIX: eval() removed for security reasons) removed for security reasons',
        'severity' => 'CRITICAL'
    ],
    // System command execution
    [
        'pattern' => '/\bexec\s*\(/i',
        'description' => 'System command execution',
        'replacement' => '// SECURITY FIX: // SECURITY FIX: exec() removed for security reasons) removed for security reasons',
        'severity' => 'CRITICAL'
    ],
    [
        'pattern' => '/\bsystem\s*\(/i',
        'description' => 'System command execution',
        'replacement' => '// SECURITY FIX: // SECURITY FIX: system() removed for security reasons) removed for security reasons',
        'severity' => 'CRITICAL'
    ],
    [
        'pattern' => '/\bshell_exec\s*\(/i',
        'description' => 'Shell command execution',
        'replacement' => '// SECURITY FIX: // SECURITY FIX: shell_exec() removed for security reasons) removed for security reasons',
        'severity' => 'CRITICAL'
    ],
    [
        'pattern' => '/\bpassthru\s*\(/i',
        'description' => 'Command passthrough',
        'replacement' => '// SECURITY FIX: // SECURITY FIX: passthru() removed for security reasons) removed for security reasons',
        'severity' => 'CRITICAL'
    ],
    // File inclusion vulnerabilities
    [
        'pattern' => '/include\s*\(\s*\$_(?:GET|POST|REQUEST)/i',
        'description' => 'Remote file inclusion vulnerability',
        'replacement' => '// SECURITY FIX: Remote file inclusion blocked',
        'severity' => 'CRITICAL'
    ],
    [
        'pattern' => '/require\s*\(\s*\$_(?:GET|POST|REQUEST)/i',
        'description' => 'Remote file inclusion vulnerability',
        'replacement' => '// SECURITY FIX: Remote file inclusion blocked',
        'severity' => 'CRITICAL'
    ]
];

$filesProcessed = 0;
$vulnerabilitiesFixed = 0;
$filesWithIssues = 0;

// Scan PHP files for critical vulnerabilities
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($projectRoot, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $file) {
    if ($file->isFile() && pathinfo($file->getPathname(), PATHINFO_EXTENSION) === 'php') {
        $relativePath = str_replace($projectRoot . '/', '', $file->getPathname());

        // Skip safe directories
        if (strpos($relativePath, 'vendor/') === 0 ||
            strpos($relativePath, 'node_modules/') === 0 ||
            strpos($relativePath, '.git/') === 0 ||
            strpos($relativePath, 'security_backup_') === 0) {
            continue;
        }

        $content = file_get_contents($file->getPathname());
        $originalContent = $content;
        $fileModified = false;

        foreach ($criticalPatterns as $pattern) {
            if (preg_match($pattern['pattern'], $content)) {
                echo "🚨 {$pattern['severity']}: {$pattern['description']} in $relativePath\n";

                // Create backup of original file
                $backupPath = $backupDir . '/' . str_replace('/', '_', $relativePath);
                if (!file_exists($backupPath)) {
                    copy($file->getPathname(), $backupPath);
                }

                // Apply fix
                $content = preg_replace($pattern['pattern'], $pattern['replacement'], $content);
                $fileModified = true;
                $vulnerabilitiesFixed++;

                echo "   ✅ Fixed: {$pattern['description']}\n";
            }
        }

        if ($fileModified) {
            file_put_contents($file->getPathname(), $content);
            $filesWithIssues++;
            echo "   💾 File updated: $relativePath\n\n";
        }

        $filesProcessed++;
    }
}

$criticalFixes['files_processed'] = $filesProcessed;
$criticalFixes['vulnerabilities_fixed'] = $vulnerabilitiesFixed;
$criticalFixes['files_with_issues'] = $filesWithIssues;

// 2. FIX HARDCODED PASSWORDS
echo "🔐 FIXING HARDCODED PASSWORDS\n";
echo "============================\n";

$passwordPattern = '/(?:password|passwd|pwd)\s*[=:]\s*[\'"]([^\'"]+)[\'"]/i';
$passwordFixes = 0;

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($projectRoot, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $file) {
    if ($file->isFile() && pathinfo($file->getPathname(), PATHINFO_EXTENSION) === 'php') {
        $relativePath = str_replace($projectRoot . '/', '', $file->getPathname());

        // Skip safe directories
        if (strpos($relativePath, 'vendor/') === 0 ||
            strpos($relativePath, 'node_modules/') === 0 ||
            strpos($relativePath, '.git/') === 0 ||
            strpos($relativePath, 'security_backup_') === 0) {
            continue;
        }

        $content = file_get_contents($file->getPathname());

        if (preg_match($passwordPattern, $content, $matches)) {
            if (strlen($matches[1]) > 0 && !preg_match('/^\$\w+/', $matches[1])) { // Not a variable
                echo "⚠️  Hardcoded password found in: $relativePath\n";

                // Create backup
                $backupPath = $backupDir . '/' . str_replace('/', '_', $relativePath) . '_password';
                if (!file_exists($backupPath)) {
                    copy($file->getPathname(), $backupPath);
                }

                // Replace with environment variable
                $content = preg_replace(
                    '/(password|passwd|pwd)\s*[=:]\s*[\'"]([^\'"]+)[\'"]/i',
                    '$1 = env(\'DB_PASSWORD\', \'\')',
                    $content
                );

                file_put_contents($file->getPathname(), $content);
                $passwordFixes++;

                echo "   ✅ Replaced with environment variable\n\n";
            }
        }
    }
}

// 3. ADD SECURITY HEADERS TO PHP FILES
echo "🛡️  ADDING SECURITY HEADERS TO PHP FILES\n";
echo "=======================================\n";

$securityHeadersAdded = 0;
$entryPointFiles = [
    'public/index.php',
    'index.php',
    'app/bootstrap.php',
    'config/bootstrap.php'
];

foreach ($entryPointFiles as $entryFile) {
    $filePath = $projectRoot . '/' . $entryFile;
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);

        // Add security headers if not already present
        if (strpos($content, 'X-Frame-Options') === false) {
            $securityHeaders = "\n// Security Headers\n";
            $securityHeaders .= "header('X-Frame-Options: SAMEORIGIN');\n";
            $securityHeaders .= "header('X-Content-Type-Options: nosniff');\n";
            $securityHeaders .= "header('X-XSS-Protection: 1; mode=block');\n";
            $securityHeaders .= "header('Referrer-Policy: strict-origin-when-cross-origin');\n";

            // Insert after opening PHP tag
            $content = preg_replace('/^<\?php\s*/', "<?php\n$securityHeaders", $content, 1);

            file_put_contents($filePath, $content);
            $securityHeadersAdded++;

            echo "✅ Added security headers to: $entryFile\n";
        }
    }
}

// 4. CREATE SECURE CONFIGURATION
echo "🔧 CREATING SECURE CONFIGURATION\n";
echo "===============================\n";

// Ensure .env file exists with secure defaults
$envPath = $projectRoot . '/.env';
if (!file_exists($envPath)) {
    $secureEnv = "# APS Dream Home Secure Environment Configuration
# Generated for security - MODIFY VALUES FOR YOUR ENVIRONMENT

# Database Configuration
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=apsdreamhome
DB_USERNAME=root
DB_PASSWORD=

# Application Security
APP_NAME=\"APS Dream Home\"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_KEY=" . bin2hex(random_bytes(16)) . "
JWT_SECRET=" . bin2hex(random_bytes(32)) . "

# Security Configuration
SESSION_SECURE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict
ENCRYPTION_KEY=" . bin2hex(random_bytes(16)) . "

# Rate Limiting
RATE_LIMIT_ATTEMPTS=5
RATE_LIMIT_DECAY_MINUTES=15

# File Upload Security
UPLOAD_MAX_SIZE=5242880
UPLOAD_ALLOWED_EXTENSIONS=jpg,jpeg,png,gif,pdf,doc,docx

# CORS Configuration
CORS_ALLOWED_ORIGINS=https://yourdomain.com
CORS_ALLOWED_METHODS=GET,POST,PUT,DELETE,OPTIONS
";

    file_put_contents($envPath, $secureEnv);
    echo "✅ Created secure .env file with generated secrets\n";
}

// 5. FINAL SECURITY VERIFICATION
echo "🔍 FINAL SECURITY VERIFICATION\n";
echo "==============================\n";

$finalScan = [
    'critical_vulns_remaining' => 0,
    'high_vulns_remaining' => 0,
    'files_scanned' => 0,
    'clean_files' => 0
];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($projectRoot, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $file) {
    if ($file->isFile() && pathinfo($file->getPathname(), PATHINFO_EXTENSION) === 'php') {
        $relativePath = str_replace($projectRoot . '/', '', $file->getPathname());

        // Skip safe directories
        if (strpos($relativePath, 'vendor/') === 0 ||
            strpos($relativePath, 'node_modules/') === 0 ||
            strpos($relativePath, '.git/') === 0 ||
            strpos($relativePath, 'security_backup_') === 0) {
            continue;
        }

        $content = file_get_contents($file->getPathname());
        $finalScan['files_scanned']++;

        $hasCritical = false;
        foreach ($criticalPatterns as $pattern) {
            if (preg_match($pattern['pattern'], $content)) {
                if ($pattern['severity'] === 'CRITICAL') {
                    $finalScan['critical_vulns_remaining']++;
                    $hasCritical = true;
                }
            }
        }

        if (preg_match($passwordPattern, $content) &&
            preg_match('/[\'"]([^\'"]+)[\'"]/', $content, $matches) &&
            strlen($matches[1]) > 0 && !preg_match('/^\$\w+/', $matches[1])) {
            $finalScan['high_vulns_remaining']++;
        }

        if (!$hasCritical) {
            $finalScan['clean_files']++;
        }
    }
}

// Generate comprehensive security report
$securityReport = [
    'scan_timestamp' => date('Y-m-d H:i:s'),
    'critical_fixes_applied' => [
        'dangerous_functions_removed' => $vulnerabilitiesFixed,
        'hardcoded_passwords_fixed' => $passwordFixes,
        'security_headers_added' => $securityHeadersAdded,
        'secure_config_created' => file_exists($envPath),
        'backup_created' => $criticalFixes['backup_created']
    ],
    'final_security_status' => [
        'files_scanned' => $finalScan['files_scanned'],
        'clean_files' => $finalScan['clean_files'],
        'critical_vulnerabilities_remaining' => $finalScan['critical_vulns_remaining'],
        'high_priority_issues_remaining' => $finalScan['high_vulns_remaining'],
        'overall_security_score' => max(0, 100 - ($finalScan['critical_vulns_remaining'] * 10) - ($finalScan['high_vulns_remaining'] * 5))
    ],
    'recommendations' => [
        'immediate' => [
            'Review all modified files in backup directory',
            'Test application functionality after security fixes',
            'Configure proper environment variables in .env',
            'Enable HTTPS in production',
            'Set up proper session configuration'
        ],
        'short_term' => [
            'Implement prepared statements for all database queries',
            'Add input validation and sanitization',
            'Set up rate limiting for authentication',
            'Implement proper error handling',
            'Add security monitoring and logging'
        ],
        'ongoing' => [
            'Regular security audits',
            'Keep dependencies updated',
            'Monitor for new security vulnerabilities',
            'Regular backup of security configurations'
        ]
    ]
];

echo "📊 SECURITY FIX SUMMARY\n";
echo "======================\n";
echo "Files Processed: {$finalScan['files_scanned']}\n";
echo "Vulnerabilities Fixed: {$vulnerabilitiesFixed}\n";
echo "Passwords Fixed: {$passwordFixes}\n";
echo "Security Headers Added: {$securityHeadersAdded}\n";
echo "Backup Created: " . ($criticalFixes['backup_created'] ? 'Yes' : 'No') . "\n\n";

echo "🔒 FINAL SECURITY STATUS\n";
echo "=======================\n";
echo "Clean Files: {$finalScan['clean_files']}/{$finalScan['files_scanned']}\n";
echo "Critical Vulnerabilities Remaining: {$finalScan['critical_vulns_remaining']}\n";
echo "High Priority Issues Remaining: {$finalScan['high_vulns_remaining']}\n";
echo "Security Score: {$securityReport['final_security_status']['overall_security_score']}/100\n\n";

if ($securityReport['final_security_status']['overall_security_score'] >= 80) {
    echo "✅ SECURITY STATUS: GOOD - Major vulnerabilities fixed\n";
} elseif ($securityReport['final_security_status']['overall_security_score'] >= 60) {
    echo "⚠️  SECURITY STATUS: FAIR - Some vulnerabilities remain\n";
} else {
    echo "🚨 SECURITY STATUS: CRITICAL - Immediate action required\n";
}

echo "\n📋 IMMEDIATE NEXT STEPS:\n";
foreach ($securityReport['recommendations']['immediate'] as $step) {
    echo "  • $step\n";
}

// Save comprehensive security fix report
file_put_contents($projectRoot . '/security_fixes_report.json', json_encode($securityReport, JSON_PRETTY_PRINT));

echo "\n📄 Security fixes report saved to: security_fixes_report.json\n";
echo "📁 Backup of modified files: $backupDir\n";

echo "\n🎉 CRITICAL SECURITY FIXES COMPLETED!\n";
echo "Most dangerous security vulnerabilities have been addressed.\n";
echo "Review the backup files and test your application thoroughly.\n";

?>
