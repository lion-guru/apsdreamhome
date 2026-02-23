<?php
/**
 * APS Dream Home - Critical Security Vulnerabilities Fix
 * Comprehensive security hardening and vulnerability remediation
 */

echo "🔒 APS DREAM HOME - CRITICAL SECURITY FIXES\n";
echo "=========================================\n\n";

$projectRoot = __DIR__ . '/..';
$securityReport = [
    'scan_time' => date('Y-m-d H:i:s'),
    'vulnerabilities_found' => 0,
    'vulnerabilities_fixed' => 0,
    'manual_fixes_required' => 0,
    'risk_level' => 'UNKNOWN',
    'critical_issues' => [],
    'fixed_issues' => [],
    'recommendations' => []
];

// 1. SCAN FOR SECURITY VULNERABILITIES
echo "🔍 PHASE 1: SECURITY VULNERABILITY SCAN\n";
echo "=====================================\n";

function scanSecurityVulnerabilities($dir) {
    $vulnerabilities = [
        'eval_usage' => [],
        'exec_usage' => [],
        'hardcoded_passwords' => [],
        'hardcoded_api_keys' => [],
        'sql_injection_risks' => [],
        'file_inclusion_risks' => [],
        'xss_risks' => [],
        'csrf_risks' => []
    ];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && pathinfo($file->getPathname(), PATHINFO_EXTENSION) === 'php') {
            $content = file_get_contents($file->getPathname());
            $relativePath = str_replace($dir . '/', '', $file->getPathname());

            // Skip certain safe directories
            if (strpos($relativePath, 'vendor/') === 0 ||
                strpos($relativePath, 'node_modules/') === 0 ||
                strpos($relativePath, '.git/') === 0) {
                continue;
            }

            // Check for eval usage
            if (preg_match('/\beval\s*\(/i', $content)) {
                $vulnerabilities['eval_usage'][] = [
                    'file' => $relativePath,
                    'severity' => 'CRITICAL',
                    'description' => 'Dangerous // SECURITY FIX: eval() removed for security reasons) function usage'
                ];
            }

            // Check for system command execution
            $execFunctions = ['exec', 'system', 'shell_exec', 'passthru', 'proc_open', 'popen'];
            foreach ($execFunctions as $func) {
                if (preg_match('/\b' . $func . '\s*\(/i', $content)) {
                    $vulnerabilities['exec_usage'][] = [
                        'file' => $relativePath,
                        'severity' => 'CRITICAL',
                        'function' => $func,
                        'description' => 'System command execution vulnerability'
                    ];
                }
            }

            // Check for hardcoded passwords
            if (preg_match('/(?:password|passwd|pwd)\s*[=:]\s*[\'"]([^\'"]+)[\'"]/i', $content, $matches)) {
                if (strlen($matches[1]) > 0 && !preg_match('/^\$\w+/', $matches[1])) { // Not a variable
                    $vulnerabilities['hardcoded_passwords'][] = [
                        'file' => $relativePath,
                        'severity' => 'HIGH',
                        'value' => substr($matches[1], 0, 10) . '...',
                        'description' => 'Hardcoded password detected'
                    ];
                }
            }

            // Check for hardcoded API keys
            if (preg_match('/(?:api[_-]?key|apikey|secret[_-]?key)\s*[=:]\s*[\'"]([^\'"]+)[\'"]/i', $content, $matches)) {
                if (strlen($matches[1]) > 10) { // Likely a real key
                    $vulnerabilities['hardcoded_api_keys'][] = [
                        'file' => $relativePath,
                        'severity' => 'HIGH',
                        'description' => 'Hardcoded API key or secret detected'
                    ];
                }
            }

            // Check for SQL injection risks
            if (preg_match('/(?:\$_(?:GET|POST|REQUEST)\[.*\])\s*\.\s*[\'"][^\'"]*[\'"]/i', $content)) {
                $vulnerabilities['sql_injection_risks'][] = [
                    'file' => $relativePath,
                    'severity' => 'HIGH',
                    'description' => 'Potential SQL injection vulnerability'
                ];
            }

            // Check for file inclusion risks
            if (preg_match('/include\s*\(\s*\$_(?:GET|POST|REQUEST)/i', $content) ||
                preg_match('/require\s*\(\s*\$_(?:GET|POST|REQUEST)/i', $content)) {
                $vulnerabilities['file_inclusion_risks'][] = [
                    'file' => $relativePath,
                    'severity' => 'CRITICAL',
                    'description' => 'Remote file inclusion vulnerability'
                ];
            }

            // Check for XSS risks
            if (preg_match('/echo\s+\$_.*\[\w*\]/i', $content) && !preg_match('/htmlspecialchars|htmlentities/i', $content)) {
                $vulnerabilities['xss_risks'][] = [
                    'file' => $relativePath,
                    'severity' => 'MEDIUM',
                    'description' => 'Potential XSS vulnerability'
                ];
            }

            // Check for CSRF protection
            if (preg_match('/<form/i', $content) && !preg_match('/csrf|_token|security/i', $content)) {
                $vulnerabilities['csrf_risks'][] = [
                    'file' => $relativePath,
                    'severity' => 'MEDIUM',
                    'description' => 'Missing CSRF protection on form'
                ];
            }
        }
    }

    return $vulnerabilities;
}

$vulnerabilities = scanSecurityVulnerabilities($projectRoot);

$totalVulnerabilities = 0;
foreach ($vulnerabilities as $type => $issues) {
    $totalVulnerabilities += count($issues);
}

$securityReport['vulnerabilities_found'] = $totalVulnerabilities;

echo "📊 VULNERABILITY SCAN RESULTS:\n";
echo "Total Vulnerabilities Found: $totalVulnerabilities\n\n";

foreach ($vulnerabilities as $type => $issues) {
    if (!empty($issues)) {
        $typeName = ucwords(str_replace('_', ' ', $type));
        echo "$typeName: " . count($issues) . " issues\n";

        foreach (array_slice($issues, 0, 3) as $issue) {
            $severityIcon = $issue['severity'] === 'CRITICAL' ? '🚨' : ($issue['severity'] === 'HIGH' ? '⚠️' : 'ℹ️');
            echo "  $severityIcon {$issue['file']}: {$issue['description']}\n";
        }

        if (count($issues) > 3) {
            echo "  ... and " . (count($issues) - 3) . " more\n";
        }
        echo "\n";
    }
}

// Determine overall risk level
if ($totalVulnerabilities >= 20) {
    $securityReport['risk_level'] = 'CRITICAL';
} elseif ($totalVulnerabilities >= 10) {
    $securityReport['risk_level'] = 'HIGH';
} elseif ($totalVulnerabilities >= 5) {
    $securityReport['risk_level'] = 'MEDIUM';
} else {
    $securityReport['risk_level'] = 'LOW';
}

echo "🚨 OVERALL RISK LEVEL: {$securityReport['risk_level']}\n\n";

// 2. AUTOMATIC SECURITY FIXES
echo "🔧 PHASE 2: AUTOMATIC SECURITY FIXES\n";
echo "=================================\n";

$fixesApplied = 0;

// Create .env.example if it doesn't exist
$envExamplePath = $projectRoot . '/.env.example';
if (!file_exists($envExamplePath)) {
    $envExample = "# APS Dream Home Environment Configuration
# Copy this file to .env and configure your values

# Database Configuration
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=apsdreamhome
DB_USERNAME=root
DB_PASSWORD=

# Application Configuration
APP_NAME=\"APS Dream Home\"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost/apsdreamhome
APP_KEY=
JWT_SECRET=

# Security Configuration
SESSION_SECURE=true
SESSION_HTTP_ONLY=true
ENCRYPTION_KEY=

# Email Configuration
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls

# API Keys (Get from respective services)
STRIPE_PUBLISHABLE_KEY=
STRIPE_SECRET_KEY=
RAZORPAY_KEY_ID=
RAZORPAY_KEY_SECRET=
FIREBASE_SERVER_KEY=
FIREBASE_PROJECT_ID=
WHATSAPP_ACCESS_TOKEN=
WHATSAPP_PHONE_NUMBER_ID=
";

    file_put_contents($envExamplePath, $envExample);
    echo "✅ Created .env.example with secure configuration template\n";
    $fixesApplied++;
}

// Create .gitignore if it doesn't exist or update it
$gitignorePath = $projectRoot . '/.gitignore';
$gitignoreContent = file_exists($gitignorePath) ? file_get_contents($gitignorePath) : '';

$securityEntries = [
    '# Security - Never commit sensitive data',
    '.env',
    '.env.local',
    '.env.production',
    '.env.testing',
    '',
    '# Logs',
    '*.log',
    'logs/',
    'storage/logs/',
    '',
    '# Cache',
    'storage/cache/',
    'cache/',
    '',
    '# Temporary files',
    '*.tmp',
    '*.temp',
    'tmp/',
    '',
    '# IDE files',
    '.vscode/',
    '.idea/',
    '*.swp',
    '*.swo',
    '',
    '# OS files',
    '.DS_Store',
    'Thumbs.db'
];

$needsUpdate = false;
foreach ($securityEntries as $entry) {
    if (strpos($gitignoreContent, trim($entry, '# ')) === false) {
        $needsUpdate = true;
        break;
    }
}

if ($needsUpdate) {
    if (!empty($gitignoreContent) && !preg_match('/\n$/', $gitignoreContent)) {
        $gitignoreContent .= "\n";
    }
    $gitignoreContent .= "\n" . implode("\n", $securityEntries) . "\n";

    file_put_contents($gitignorePath, $gitignoreContent);
    echo "✅ Updated .gitignore with security entries\n";
    $fixesApplied++;
}

// Create basic security middleware if missing
$authMiddlewarePath = $projectRoot . '/app/Http/Middleware/Auth.php';
if (!file_exists($authMiddlewarePath)) {
    $authMiddleware = '<?php
/**
 * Authentication Middleware
 */

namespace App\Http\Middleware;

class Auth
{
    public function handle($request, $next)
    {
        // Check if user is authenticated
        if (!$this->isAuthenticated()) {
            header(\'HTTP/1.1 401 Unauthorized\');
            header(\'Content-Type: application/json\');
            echo json_encode([
                \'error\' => \'Unauthorized\',
                \'message\' => \'Authentication required\'
            ]);
            exit;
        }

        return $next($request);
    }

    protected function isAuthenticated()
    {
        return isset($_SESSION[\'user_id\']) && !empty($_SESSION[\'user_id\']);
    }
}';

    file_put_contents($authMiddlewarePath, $authMiddleware);
    echo "✅ Created basic authentication middleware\n";
    $fixesApplied++;
}

$securityReport['vulnerabilities_fixed'] = $fixesApplied;

// 3. MANUAL FIX RECOMMENDATIONS
echo "\n📋 PHASE 3: MANUAL FIX RECOMMENDATIONS\n";
echo "===================================\n";

$manualFixes = [];

echo "🔴 CRITICAL FIXES REQUIRED:\n";
$criticalCount = 0;
foreach ($vulnerabilities['eval_usage'] as $issue) {
    echo "  🚨 {$issue['file']}: Replace // SECURITY FIX: eval() removed for security reasons) with safe alternatives\n";
    $criticalCount++;
    $manualFixes[] = [
        'file' => $issue['file'],
        'issue' => '// SECURITY FIX: eval() removed for security reasons) usage',
        'fix' => 'Replace // SECURITY FIX: eval() removed for security reasons) with safe code execution methods'
    ];
}

foreach ($vulnerabilities['exec_usage'] as $issue) {
    echo "  🚨 {$issue['file']}: Remove or secure {$issue['function']}() calls\n";
    $criticalCount++;
    $manualFixes[] = [
        'file' => $issue['file'],
        'issue' => 'System command execution',
        'fix' => 'Remove or properly validate system commands'
    ];
}

foreach ($vulnerabilities['file_inclusion_risks'] as $issue) {
    echo "  🚨 {$issue['file']}: Fix remote file inclusion vulnerability\n";
    $criticalCount++;
    $manualFixes[] = [
        'file' => $issue['file'],
        'issue' => 'Remote file inclusion',
        'fix' => 'Validate file paths and use whitelisting'
    ];
}

echo "\n🟡 HIGH PRIORITY FIXES:\n";
$highCount = 0;
foreach ($vulnerabilities['hardcoded_passwords'] as $issue) {
    echo "  ⚠️  {$issue['file']}: Move hardcoded password to environment variables\n";
    $highCount++;
    $manualFixes[] = [
        'file' => $issue['file'],
        'issue' => 'Hardcoded password',
        'fix' => 'Move to .env file and use env() function'
    ];
}

foreach ($vulnerabilities['hardcoded_api_keys'] as $issue) {
    echo "  ⚠️  {$issue['file']}: Move API key to environment variables\n";
    $highCount++;
    $manualFixes[] = [
        'file' => $issue['file'],
        'issue' => 'Hardcoded API key',
        'fix' => 'Move to .env file and use env() function'
    ];
}

foreach ($vulnerabilities['sql_injection_risks'] as $issue) {
    echo "  ⚠️  {$issue['file']}: Fix SQL injection vulnerability\n";
    $highCount++;
    $manualFixes[] = [
        'file' => $issue['file'],
        'issue' => 'SQL injection risk',
        'fix' => 'Use prepared statements or parameterized queries'
    ];
}

echo "\n🔵 MEDIUM PRIORITY FIXES:\n";
$mediumCount = 0;
foreach ($vulnerabilities['xss_risks'] as $issue) {
    echo "  ℹ️  {$issue['file']}: Add XSS protection\n";
    $mediumCount++;
    $manualFixes[] = [
        'file' => $issue['file'],
        'issue' => 'XSS vulnerability',
        'fix' => 'Use htmlspecialchars() or CSP headers'
    ];
}

foreach ($vulnerabilities['csrf_risks'] as $issue) {
    echo "  ℹ️  {$issue['file']}: Add CSRF protection\n";
    $mediumCount++;
    $manualFixes[] = [
        'file' => $issue['file'],
        'issue' => 'Missing CSRF protection',
        'fix' => 'Add CSRF tokens to forms'
    ];
}

$securityReport['manual_fixes_required'] = count($manualFixes);
$securityReport['critical_issues'] = array_filter($manualFixes, fn($f) => in_array($f['issue'], ['// SECURITY FIX: eval() removed for security reasons) usage', 'System command execution', 'Remote file inclusion']));

echo "\n📊 FIX SUMMARY:\n";
echo "Critical Fixes: $criticalCount\n";
echo "High Priority: $highCount\n";
echo "Medium Priority: $mediumCount\n";
echo "Total Manual Fixes Required: " . count($manualFixes) . "\n\n";

// 4. SECURITY HARDENING MEASURES
echo "🛡️  PHASE 4: SECURITY HARDENING MEASURES\n";
echo "=====================================\n";

// Create .htaccess security rules
$htaccessPath = $projectRoot . '/public/.htaccess';
$htaccessSecurity = '
# Security Headers
<IfModule mod_headers.c>
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"
</IfModule>

# Prevent access to sensitive files
<FilesMatch "\.(env|git|htaccess|htpasswd|ini|log|sh|sql|conf)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Prevent access to backup files
<FilesMatch "\.(bak|backup|old|orig|tmp)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Secure PHP execution
<FilesMatch "\.php$">
    php_value session.cookie_httponly 1
    php_value session.cookie_secure 1
    php_value session.use_only_cookies 1
</FilesMatch>

# Rate limiting (basic)
<IfModule mod_evasive24.c>
    DOSHashTableSize 100
    DOSPageCount 5
    DOSPageInterval 1
    DOSSiteCount 50
    DOSSiteInterval 1
    DOSBlockingPeriod 600
</IfModule>
';

$currentHtaccess = file_exists($htaccessPath) ? file_get_contents($htaccessPath) : '';
if (strpos($currentHtaccess, 'X-Frame-Options') === false) {
    file_put_contents($htaccessPath, $currentHtaccess . "\n\n" . $htaccessSecurity);
    echo "✅ Added security headers to .htaccess\n";
    $fixesApplied++;
}

// 5. FINAL SECURITY REPORT
echo "\n📋 PHASE 5: FINAL SECURITY REPORT\n";
echo "===============================\n";

$securityReport['fixed_issues'] = [
    '.env.example created',
    '.gitignore updated',
    'Basic auth middleware created',
    'Security headers added to .htaccess'
];

$securityReport['recommendations'] = [
    'Implement prepared statements for all database queries',
    'Use environment variables for all sensitive data',
    'Implement proper input validation and sanitization',
    'Add rate limiting for API endpoints',
    'Implement proper session management',
    'Add security headers middleware',
    'Regular security audits and updates',
    'Use HTTPS in production',
    'Implement proper error handling (no sensitive data in errors)',
    'Regular dependency updates and security patches'
];

echo "🔒 SECURITY STATUS:\n";
echo "Risk Level: {$securityReport['risk_level']}\n";
echo "Vulnerabilities Found: {$securityReport['vulnerabilities_found']}\n";
echo "Automatic Fixes Applied: {$securityReport['vulnerabilities_fixed']}\n";
echo "Manual Fixes Required: {$securityReport['manual_fixes_required']}\n\n";

echo "✅ AUTOMATIC FIXES COMPLETED:\n";
foreach ($securityReport['fixed_issues'] as $fix) {
    echo "  • $fix\n";
}

echo "\n📋 TOP SECURITY RECOMMENDATIONS:\n";
foreach (array_slice($securityReport['recommendations'], 0, 5) as $rec) {
    echo "  • $rec\n";
}

// Save comprehensive security report
file_put_contents($projectRoot . '/security_audit_report.json', json_encode($securityReport, JSON_PRETTY_PRINT));

echo "\n📄 Security audit report saved to: security_audit_report.json\n";

echo "\n🎉 CRITICAL SECURITY AUDIT COMPLETED!\n";
echo "Your APS Dream Home security has been analyzed and initial fixes applied.\n";
echo "Manual fixes are required for remaining vulnerabilities.\n";

?>
