<?php

/**
 * Advanced Security Scanner for APS Dream Home
 * Performs comprehensive security vulnerability assessment
 */

class SecurityScanner
{
    private $projectRoot;
    private $results = [];
    private $vulnerabilities = [];
    private $warnings = [];
    private $recommendations = [];

    private $scanConfig = [
        'max_file_size' => 10485760, // 10MB
        'exclude_dirs' => ['vendor', 'node_modules', '.git', 'storage/logs'],
        'risk_levels' => [
            'critical' => 9,
            'high' => 7,
            'medium' => 5,
            'low' => 3,
            'info' => 1
        ]
    ];

    public function __construct($projectRoot)
    {
        $this->projectRoot = rtrim($projectRoot, '/');
        $this->results = [
            'timestamp' => date('Y-m-d H:i:s'),
            'scan_type' => 'comprehensive_security_audit',
            'total_files_scanned' => 0,
            'vulnerabilities_found' => 0,
            'warnings_found' => 0,
            'risk_score' => 0,
            'severity_breakdown' => [
                'critical' => 0,
                'high' => 0,
                'medium' => 0,
                'low' => 0,
                'info' => 0
            ]
        ];
    }

    /**
     * Run comprehensive security scan
     */
    public function runFullSecurityScan()
    {
        echo "🔒 Starting Comprehensive Security Scan\n";
        echo "=======================================\n\n";

        $this->scanInputValidation();
        $this->scanAuthenticationSecurity();
        $this->scanAuthorizationChecks();
        $this->scanSQLInjectionVulnerabilities();
        $this->scanXSSVulnerabilities();
        $this->scanCSRFProtection();
        $this->scanFileUploadSecurity();
        $this->scanSessionSecurity();
        $this->scanPasswordSecurity();
        $this->scanEncryptionUsage();
        $this->scanDependencyVulnerabilities();
        $this->scanConfigurationSecurity();
        $this->scanNetworkSecurity();
        $this->scanErrorHandling();

        $this->calculateRiskScore();
        $this->generateSecurityReport();

        echo "\n✅ Security scan completed!\n";
        return $this->results;
    }

    /**
     * Scan for input validation vulnerabilities
     */
    private function scanInputValidation()
    {
        echo "🔍 Scanning Input Validation...\n";

        $phpFiles = $this->getPHPFiles();

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            // Check for direct use of $_GET, $_POST, etc. without validation
            $patterns = [
                '/\$_(?:GET|POST|REQUEST|COOKIE)\s*\[/',
                '/\$_(?:GET|POST|REQUEST|COOKIE)\[.*?\]\s*[^=]*$/m'
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    // Check if validation/sanitization is present
                    if (!preg_match('/filter_var|htmlspecialchars|strip_tags|mysqli_real_escape_string|PDO::prepare/', $content)) {
                        $this->addVulnerability(
                            'Unvalidated Input Usage',
                            'Direct use of user input without validation or sanitization',
                            'high',
                            $file,
                            'Use proper input validation and sanitization functions'
                        );
                    }
                }
            }

            // Check for dangerous functions with user input
            $dangerousPatterns = [
                '/eval\s*\(\s*\$.*?\s*\)/' => 'Code Injection via eval()',
                '/system\s*\(\s*\$.*?\s*\)/' => 'Command Injection via system()',
                '/exec\s*\(\s*\$.*?\s*\)/' => 'Command Injection via exec()',
                '/shell_exec\s*\(\s*\$.*?\s*\)/' => 'Command Injection via shell_exec()',
                '/passthru\s*\(\s*\$.*?\s*\)/' => 'Command Injection via passthru()',
                '/\`.*?\$.*?\`/' => 'Command Injection via backticks'
            ];

            foreach ($dangerousPatterns as $pattern => $description) {
                if (preg_match($pattern, $content)) {
                    $this->addVulnerability(
                        $description,
                        'Dangerous function called with potential user input',
                        'critical',
                        $file,
                        'Remove or secure dangerous function usage'
                    );
                }
            }
        }

        echo "✅ Input validation scan completed\n\n";
    }

    /**
     * Scan authentication security
     */
    private function scanAuthenticationSecurity()
    {
        echo "🔐 Scanning Authentication Security...\n";

        $phpFiles = $this->getPHPFiles();

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            // Check for weak password policies
            if (preg_match('/password.*length.*[<]\s*\d+/', $content)) {
                if (preg_match('/password.*length.*[<]\s*8/', $content)) {
                    $this->addVulnerability(
                        'Weak Password Policy',
                        'Password length requirement is less than 8 characters',
                        'medium',
                        $file,
                        'Enforce minimum 8-character password length'
                    );
                }
            }

            // Check for plain text password storage
            if (preg_match('/password.*=.*[^hash|bcrypt].*\$_(?:POST|GET)/', $content)) {
                $this->addVulnerability(
                    'Plain Text Password Storage',
                    'Passwords stored without proper hashing',
                    'critical',
                    $file,
                    'Use password_hash() and password_verify() for password storage'
                );
            }

            // Check for session fixation vulnerabilities
            if (preg_match('/session_id\s*\(\s*\$.*?\s*\)/', $content)) {
                $this->addVulnerability(
                    'Session Fixation Vulnerability',
                    'Manual session ID setting can lead to session fixation attacks',
                    'high',
                    $file,
                    'Avoid manual session ID manipulation'
                );
            }

            // Check for brute force protection
            if (!preg_match('/login.*attempt.*limit|brute.*force/i', $content)) {
                $this->addWarning(
                    'No Brute Force Protection',
                    'Login attempts not limited - vulnerable to brute force attacks',
                    $file,
                    'Implement login attempt limiting and account lockout'
                );
            }
        }

        echo "✅ Authentication security scan completed\n\n";
    }

    /**
     * Scan authorization checks
     */
    private function scanAuthorizationChecks()
    {
        echo "🛡️ Scanning Authorization Checks...\n";

        $phpFiles = $this->getPHPFiles();

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            // Check for missing authorization checks
            if (preg_match('/admin|dashboard|manage/i', $file)) {
                if (!preg_match('/permission|authorize|role.*check|access.*control/i', $content)) {
                    $this->addVulnerability(
                        'Missing Authorization Check',
                        'Administrative function without proper authorization verification',
                        'high',
                        $file,
                        'Implement proper role-based access control'
                    );
                }
            }

            // Check for insecure direct object references
            if (preg_match('/\$_(?:GET|POST)\[.*id.*\]/', $content)) {
                if (!preg_match('/ownership|permission|belongs.*to/i', $content)) {
                    $this->addWarning(
                        'Potential IDOR Vulnerability',
                        'Direct object reference without ownership verification',
                        $file,
                        'Implement proper object ownership validation'
                    );
                }
            }
        }

        echo "✅ Authorization checks scan completed\n\n";
    }

    /**
     * Scan for SQL injection vulnerabilities
     */
    private function scanSQLInjectionVulnerabilities()
    {
        echo "💉 Scanning SQL Injection Vulnerabilities...\n";

        $phpFiles = $this->getPHPFiles();

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            // Check for direct SQL query construction
            $sqlPatterns = [
                '/mysql_query\s*\(\s*[\'"].*?\$/i',
                '/mysqli_query\s*\(\s*[\'"].*?\$/i',
                '/\$sql\s*=.*[\'"].*?\$/i',
                '/query\s*\(\s*[\'"].*?\$/i'
            ];

            foreach ($sqlPatterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    // Check if prepared statements are used
                    if (!preg_match('/prepare|bindParam|bindValue|PDO/i', $content)) {
                        $this->addVulnerability(
                            'SQL Injection Vulnerability',
                            'Direct SQL query construction with user input',
                            'critical',
                            $file,
                            'Use prepared statements or parameterized queries'
                        );
                    }
                }
            }

            // Check for unsafe LIKE queries
            if (preg_match('/LIKE\s*[\'"].*?\%.*?\$.*?[\'"]/', $content)) {
                $this->addWarning(
                    'Unsafe LIKE Query',
                    'LIKE query with direct user input may be vulnerable',
                    $file,
                    'Sanitize LIKE query inputs properly'
                );
            }
        }

        echo "✅ SQL injection scan completed\n\n";
    }

    /**
     * Scan for XSS vulnerabilities
     */
    private function scanXSSVulnerabilities()
    {
        echo "🕷️ Scanning XSS Vulnerabilities...\n";

        $phpFiles = $this->getPHPFiles();

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            // Check for unescaped output
            $outputPatterns = [
                '/echo\s+\$.*?;/',
                '/print\s+\$.*?;/',
                '/<?=\s*\$.*?\s*\?>/'
            ];

            foreach ($outputPatterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    // Check if output is escaped
                    if (!preg_match('/htmlspecialchars|htmlentities|strip_tags/', $content)) {
                        $this->addVulnerability(
                            'Cross-Site Scripting (XSS) Vulnerability',
                            'User input output without proper escaping',
                            'high',
                            $file,
                            'Use htmlspecialchars() or appropriate escaping functions'
                        );
                    }
                }
            }

            // Check for dangerous JavaScript inclusion
            if (preg_match('/<script[^>]*>.*?\$.*?<\/script>/is', $content)) {
                $this->addVulnerability(
                    'JavaScript Injection Vulnerability',
                    'Dynamic JavaScript content with user input',
                    'high',
                    $file,
                    'Avoid including user input in JavaScript or properly sanitize'
                );
            }
        }

        echo "✅ XSS scan completed\n\n";
    }

    /**
     * Scan CSRF protection
     */
    private function scanCSRFProtection()
    {
        echo "🔄 Scanning CSRF Protection...\n";

        $phpFiles = $this->getPHPFiles();

        $csrfProtected = false;

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            // Check for CSRF token validation
            if (preg_match('/csrf|token|nonce/i', $content)) {
                if (preg_match('/verify|validate|check/i', $content)) {
                    $csrfProtected = true;
                }
            }

            // Check for state-changing operations without CSRF protection
            $stateChangingOps = ['POST', 'PUT', 'DELETE', 'PATCH'];
            foreach ($stateChangingOps as $method) {
                if (preg_match("/\\\$_(?:{$method}|REQUEST)/", $content)) {
                    if (!preg_match('/csrf|token|nonce/i', $content)) {
                        $this->addVulnerability(
                            'Missing CSRF Protection',
                            "State-changing operation ({$method}) without CSRF protection",
                            'medium',
                            $file,
                            'Implement CSRF tokens for all state-changing operations'
                        );
                    }
                }
            }
        }

        if (!$csrfProtected) {
            $this->addWarning(
                'No CSRF Protection Detected',
                'Application may be vulnerable to Cross-Site Request Forgery attacks',
                'Multiple files',
                'Implement comprehensive CSRF protection'
            );
        }

        echo "✅ CSRF protection scan completed\n\n";
    }

    /**
     * Scan file upload security
     */
    private function scanFileUploadSecurity()
    {
        echo "📁 Scanning File Upload Security...\n";

        $phpFiles = $this->getPHPFiles();

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            // Check for file upload handling
            if (preg_match('/\$_FILES|move_uploaded_file|file.*upload/i', $content)) {

                // Check for file type validation
                if (!preg_match('/mime|extension|type.*check|validate.*file/i', $content)) {
                    $this->addVulnerability(
                        'Unsafe File Upload',
                        'File upload without proper type validation',
                        'high',
                        $file,
                        'Implement file type, size, and content validation'
                    );
                }

                // Check for path traversal protection
                if (preg_match('/\.\.|\/\\\\/', $content)) {
                    if (!preg_match('/basename|realpath|secure.*path/i', $content)) {
                        $this->addWarning(
                            'Path Traversal Risk',
                            'File operations may be vulnerable to path traversal attacks',
                            $file,
                            'Use basename() and validate file paths'
                        );
                    }
                }

                // Check for file size limits
                if (!preg_match('/max.*size|file.*size.*limit/i', $content)) {
                    $this->addWarning(
                        'No File Size Limits',
                        'File uploads without size restrictions',
                        $file,
                        'Implement maximum file size limits'
                    );
                }
            }
        }

        echo "✅ File upload security scan completed\n\n";
    }

    /**
     * Scan session security
     */
    private function scanSessionSecurity()
    {
        echo "🔑 Scanning Session Security...\n";

        $phpFiles = $this->getPHPFiles();

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            // Check for secure session configuration
            if (preg_match('/session_start/', $content)) {
                $issues = [];

                if (!preg_match('/session\.cookie_secure|cookie_secure/i', $content)) {
                    $issues[] = 'Session cookies not marked as secure';
                }

                if (!preg_match('/session\.cookie_httponly|cookie_httponly/i', $content)) {
                    $issues[] = 'Session cookies not marked as HttpOnly';
                }

                if (!preg_match('/session\.gc_maxlifetime|lifetime/i', $content)) {
                    $issues[] = 'Session lifetime not configured';
                }

                if (!empty($issues)) {
                    $this->addVulnerability(
                        'Insecure Session Configuration',
                        'Session security issues: ' . implode(', ', $issues),
                        'medium',
                        $file,
                        'Configure secure session settings'
                    );
                }
            }

            // Check for session data exposure
            if (preg_match('/print_r\s*\(\s*\$_SESSION\s*\)/', $content) ||
                preg_match('/var_dump\s*\(\s*\$_SESSION\s*\)/', $content)) {
                $this->addVulnerability(
                    'Session Data Exposure',
                    'Session data being dumped for debugging',
                    'medium',
                    $file,
                    'Remove debug output of session data'
                );
            }
        }

        echo "✅ Session security scan completed\n\n";
    }

    /**
     * Scan password security
     */
    private function scanPasswordSecurity()
    {
        echo "🔐 Scanning Password Security...\n";

        $phpFiles = $this->getPHPFiles();

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            // Check for weak hashing algorithms
            $weakHashes = ['md5', 'sha1', 'crc32'];
            foreach ($weakHashes as $hash) {
                if (preg_match("/{$hash}\\s*\\(/i", $content)) {
                    $this->addVulnerability(
                        'Weak Password Hashing',
                        "Using weak hashing algorithm: {$hash}",
                        'high',
                        $file,
                        'Use password_hash() with bcrypt or argon2'
                    );
                }
            }

            // Check for password policies
            $passwordIssues = [];

            if (!preg_match('/password.*length.*>=?\s*8/i', $content)) {
                $passwordIssues[] = 'No minimum password length check';
            }

            if (!preg_match('/password.*complexity|uppercase|lowercase|number|special/i', $content)) {
                $passwordIssues[] = 'No password complexity requirements';
            }

            if (!empty($passwordIssues)) {
                $this->addWarning(
                    'Weak Password Policies',
                    'Password security issues: ' . implode(', ', $passwordIssues),
                    $file,
                    'Implement strong password policies'
                );
            }
        }

        echo "✅ Password security scan completed\n\n";
    }

    /**
     * Scan encryption usage
     */
    private function scanEncryptionUsage()
    {
        echo "🔒 Scanning Encryption Usage...\n";

        $phpFiles = $this->getPHPFiles();

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            // Check for sensitive data storage
            if (preg_match('/credit.*card|ssn|social.*security|bank.*account/i', $content)) {
                if (!preg_match('/encrypt|openssl|mcrypt/i', $content)) {
                    $this->addVulnerability(
                        'Unencrypted Sensitive Data',
                        'Sensitive data stored without encryption',
                        'critical',
                        $file,
                        'Implement proper encryption for sensitive data'
                    );
                }
            }

            // Check for hardcoded encryption keys
            if (preg_match('/key.*=.*[\'"][^\'"]{10,}[\'"]/', $content)) {
                $this->addVulnerability(
                    'Hardcoded Encryption Key',
                    'Encryption key stored in source code',
                    'high',
                    $file,
                    'Move encryption keys to secure configuration'
                );
            }
        }

        echo "✅ Encryption usage scan completed\n\n";
    }

    /**
     * Scan dependency vulnerabilities
     */
    private function scanDependencyVulnerabilities()
    {
        echo "📦 Scanning Dependency Vulnerabilities...\n";

        // Check composer.json for known vulnerable packages
        $composerFile = $this->projectRoot . '/composer.json';
        if (file_exists($composerFile)) {
            $composer = json_decode(file_get_contents($composerFile), true);

            if ($composer && isset($composer['require'])) {
                // Check for outdated or vulnerable packages
                $vulnerablePackages = [
                    'laravel/framework' => 'Check for Laravel security updates',
                    'symfony/http-foundation' => 'Check for Symfony security updates',
                    'guzzlehttp/guzzle' => 'Check for Guzzle security updates'
                ];

                foreach ($vulnerablePackages as $package => $message) {
                    if (isset($composer['require'][$package])) {
                        $this->addWarning(
                            'Dependency Security Check Required',
                            "Package {$package} should be checked for security updates",
                            $composerFile,
                            $message
                        );
                    }
                }
            }
        }

        // Check for composer.lock presence
        if (!file_exists($this->projectRoot . '/composer.lock')) {
            $this->addWarning(
                'Missing composer.lock',
                'composer.lock file not found - dependency versions not locked',
                $this->projectRoot,
                'Commit composer.lock to ensure reproducible builds'
            );
        }

        echo "✅ Dependency vulnerabilities scan completed\n\n";
    }

    /**
     * Scan configuration security
     */
    private function scanConfigurationSecurity()
    {
        echo "⚙️ Scanning Configuration Security...\n";

        // Check .env file
        $envFile = $this->projectRoot . '/.env';
        if (file_exists($envFile)) {
            $envContent = file_get_contents($envFile);

            // Check for sensitive data in .env
            $sensitivePatterns = [
                '/PASSWORD.*=.*[^PLACEHOLDER]/i',
                '/KEY.*=.*[^PLACEHOLDER]/i',
                '/SECRET.*=.*[^PLACEHOLDER]/i',
                '/TOKEN.*=.*[^PLACEHOLDER]/i'
            ];

            foreach ($sensitivePatterns as $pattern) {
                if (preg_match($pattern, $envContent)) {
                    $this->addVulnerability(
                        'Sensitive Data in Environment File',
                        'Production secrets found in .env file',
                        'critical',
                        $envFile,
                        'Move sensitive data to secure secret management system'
                    );
                }
            }
        } else {
            $this->addWarning(
                'Missing Environment Configuration',
                '.env file not found',
                $this->projectRoot,
                'Create proper environment configuration'
            );
        }

        // Check for debug mode in production
        $configFiles = glob($this->projectRoot . '/config/*.php');
        foreach ($configFiles as $configFile) {
            $content = file_get_contents($configFile);

            if (preg_match('/debug.*=.*true/i', $content) ||
                preg_match('/APP_DEBUG.*=.*true/i', $content)) {
                $this->addVulnerability(
                    'Debug Mode Enabled',
                    'Debug mode enabled in configuration',
                    'medium',
                    $configFile,
                    'Disable debug mode in production'
                );
            }
        }

        echo "✅ Configuration security scan completed\n\n";
    }

    /**
     * Scan network security
     */
    private function scanNetworkSecurity()
    {
        echo "🌐 Scanning Network Security...\n";

        $phpFiles = $this->getPHPFiles();

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            // Check for HTTP URLs in production code
            if (preg_match('/http:\/\/[^localhost]/', $content)) {
                $this->addWarning(
                    'Insecure HTTP URLs',
                    'HTTP URLs found (should use HTTPS in production)',
                    $file,
                    'Use HTTPS URLs for all external communications'
                );
            }

            // Check for missing HTTPS enforcement
            if (!preg_match('/HTTPS|ssl|secure.*connection/i', $content)) {
                $this->addWarning(
                    'No HTTPS Enforcement',
                    'HTTPS not enforced for secure communications',
                    $file,
                    'Implement HTTPS enforcement and HSTS headers'
                );
            }
        }

        echo "✅ Network security scan completed\n\n";
    }

    /**
     * Scan error handling
     */
    private function scanErrorHandling()
    {
        echo "⚠️ Scanning Error Handling...\n";

        $phpFiles = $this->getPHPFiles();

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            // Check for proper error handling
            $errorPatterns = [
                '/mysql_query|mysqli_query/',
                '/file_get_contents|curl_exec/',
                '/json_decode/',
                '/PDO.*query/'
            ];

            foreach ($errorPatterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    if (!preg_match('/try|catch|error.*handling|if.*error/i', $content)) {
                        $this->addWarning(
                            'Missing Error Handling',
                            'Database or external operations without error handling',
                            $file,
                            'Implement proper error handling and logging'
                        );
                    }
                }
            }

            // Check for error information disclosure
            if (preg_match('/display_errors|error_reporting/i', $content)) {
                if (preg_match('/display_errors.*=.*On|On/i', $content)) {
                    $this->addVulnerability(
                        'Error Information Disclosure',
                        'Error reporting enabled in production',
                        'medium',
                        $file,
                        'Disable error display in production environment'
                    );
                }
            }
        }

        echo "✅ Error handling scan completed\n\n";
    }

    /**
     * Calculate overall risk score
     */
    private function calculateRiskScore()
    {
        $totalRisk = 0;

        foreach ($this->vulnerabilities as $vuln) {
            $totalRisk += $this->scanConfig['risk_levels'][$vuln['severity']];
        }

        // Normalize to 0-100 scale
        $this->results['risk_score'] = min(100, $totalRisk);
        $this->results['vulnerabilities_found'] = count($this->vulnerabilities);
        $this->results['warnings_found'] = count($this->warnings);
    }

    /**
     * Generate comprehensive security report
     */
    private function generateSecurityReport()
    {
        echo "📊 Generating Security Assessment Report...\n";

        $report = [
            'summary' => $this->generateSecuritySummary(),
            'vulnerabilities' => $this->vulnerabilities,
            'warnings' => $this->warnings,
            'recommendations' => $this->generateSecurityRecommendations(),
            'scan_results' => $this->results
        ];

        // Save detailed report
        $reportFile = $this->projectRoot . '/security_audit_report_' . date('Y-m-d_H-i-s') . '.json';
        file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT));

        // Generate HTML report
        $htmlReport = $this->generateHTMLSecurityReport($report);
        $htmlFile = $this->projectRoot . '/security_audit_report_' . date('Y-m-d_H-i-s') . '.html';
        file_put_contents($htmlFile, $htmlReport);

        echo "📄 Detailed JSON report saved: {$reportFile}\n";
        echo "🌐 HTML report saved: {$htmlFile}\n\n";

        $this->displaySecuritySummary($report['summary']);
    }

    /**
     * Generate security summary
     */
    private function generateSecuritySummary()
    {
        return [
            'overall_risk_score' => $this->results['risk_score'],
            'total_vulnerabilities' => $this->results['vulnerabilities_found'],
            'total_warnings' => $this->results['warnings_found'],
            'severity_breakdown' => $this->results['severity_breakdown'],
            'files_scanned' => $this->results['total_files_scanned'],
            'scan_timestamp' => $this->results['timestamp'],
            'risk_level' => $this->getRiskLevel($this->results['risk_score'])
        ];
    }

    /**
     * Display security summary in console
     */
    private function displaySecuritySummary($summary)
    {
        echo "🛡️ SECURITY ASSESSMENT SUMMARY\n";
        echo "===============================\n\n";

        echo "🎯 Overall Risk Score: {$summary['overall_risk_score']}/100\n\n";

        $riskDescriptions = [
            'Very Low' => 'Excellent security posture',
            'Low' => 'Good security with minor issues',
            'Medium' => 'Moderate security risks present',
            'High' => 'Significant security vulnerabilities',
            'Critical' => 'Immediate security action required'
        ];

        echo "📊 Risk Level: {$summary['risk_level']} - {$riskDescriptions[$summary['risk_level']]}\n\n";

        echo "📈 Vulnerability Breakdown:\n";
        echo "---------------------------\n";
        foreach ($summary['severity_breakdown'] as $severity => $count) {
            $icon = $this->getSeverityIcon($severity);
            echo "{$icon} " . ucfirst($severity) . ": {$count}\n";
        }

        echo "\n📋 Statistics:\n";
        echo "---------------\n";
        echo "• Files Scanned: {$summary['files_scanned']}\n";
        echo "• Total Vulnerabilities: {$summary['total_vulnerabilities']}\n";
        echo "• Total Warnings: {$summary['total_warnings']}\n";
        echo "• Assessment Date: {$summary['scan_timestamp']}\n";

        echo "\n🏆 Security Grade: ";
        if ($summary['overall_risk_score'] <= 20) {
            echo "A+ (Excellent) 🏆\n";
        } elseif ($summary['overall_risk_score'] <= 40) {
            echo "A (Very Good) 🥇\n";
        } elseif ($summary['overall_risk_score'] <= 60) {
            echo "B (Good) 🥈\n";
        } elseif ($summary['overall_risk_score'] <= 80) {
            echo "C (Needs Improvement) 🥉\n";
        } else {
            echo "D (Critical Review Needed) ⚠️\n";
        }
    }

    /**
     * Add a vulnerability finding
     */
    private function addVulnerability($title, $description, $severity, $file, $recommendation)
    {
        $this->vulnerabilities[] = [
            'title' => $title,
            'description' => $description,
            'severity' => $severity,
            'file' => $file,
            'recommendation' => $recommendation,
            'line' => $this->getApproximateLine($file, $description)
        ];

        $this->results['severity_breakdown'][$severity]++;
    }

    /**
     * Add a warning
     */
    private function addWarning($title, $description, $file, $recommendation)
    {
        $this->warnings[] = [
            'title' => $title,
            'description' => $description,
            'file' => $file,
            'recommendation' => $recommendation
        ];
    }

    /**
     * Get PHP files for scanning
     */
    private function getPHPFiles()
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->projectRoot, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() &&
                $file->getExtension() === 'php' &&
                $file->getSize() <= $this->scanConfig['max_file_size']) {

                $path = $file->getPathname();

                // Skip excluded directories
                $skip = false;
                foreach ($this->scanConfig['exclude_dirs'] as $exclude) {
                    if (strpos($path, "/{$exclude}/") !== false) {
                        $skip = true;
                        break;
                    }
                }

                if (!$skip) {
                    $files[] = $path;
                    $this->results['total_files_scanned']++;
                }
            }
        }

        return $files;
    }

    /**
     * Get approximate line number for vulnerability
     */
    private function getApproximateLine($file, $description)
    {
        if (!file_exists($file)) return 0;

        $lines = file($file);
        foreach ($lines as $lineNum => $line) {
            if (strpos($line, substr($description, 0, 20)) !== false) {
                return $lineNum + 1;
            }
        }

        return 0;
    }

    /**
     * Get risk level description
     */
    private function getRiskLevel($score)
    {
        if ($score <= 20) return 'Very Low';
        if ($score <= 40) return 'Low';
        if ($score <= 60) return 'Medium';
        if ($score <= 80) return 'High';
        return 'Critical';
    }

    /**
     * Get severity icon
     */
    private function getSeverityIcon($severity)
    {
        $icons = [
            'critical' => '🚨',
            'high' => '🔴',
            'medium' => '🟡',
            'low' => '🟢',
            'info' => 'ℹ️'
        ];

        return $icons[$severity] ?? '❓';
    }

    /**
     * Generate security recommendations
     */
    private function generateSecurityRecommendations()
    {
        $recommendations = [];

        if ($this->results['severity_breakdown']['critical'] > 0) {
            $recommendations[] = "IMMEDIATE ACTION REQUIRED: Address all critical vulnerabilities before deployment";
        }

        if ($this->results['severity_breakdown']['high'] > 0) {
            $recommendations[] = "HIGH PRIORITY: Fix high-severity vulnerabilities within 24-48 hours";
        }

        $recommendations[] = "Implement automated security scanning in CI/CD pipeline";
        $recommendations[] = "Regular security audits and penetration testing";
        $recommendations[] = "Keep dependencies updated and monitor for security advisories";
        $recommendations[] = "Implement proper logging and monitoring for security events";

        return $recommendations;
    }

    /**
     * Generate HTML security report
     */
    private function generateHTMLSecurityReport($report)
    {
        // Simplified HTML report generation
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <title>APS Dream Home Security Audit Report</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { background: #dc3545; color: white; padding: 20px; border-radius: 8px; }
                .summary { background: #f8f9fa; padding: 15px; margin: 20px 0; border-radius: 8px; }
                .vulnerability { background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; margin: 10px 0; border-radius: 4px; }
                .critical { border-left: 4px solid #dc3545; }
                .high { border-left: 4px solid #fd7e14; }
                .medium { border-left: 4px solid #ffc107; }
                .low { border-left: 4px solid #28a745; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>🔒 APS Dream Home Security Audit Report</h1>
                <p>Generated on: {$report['summary']['scan_timestamp']}</p>
            </div>

            <div class='summary'>
                <h2>📊 Executive Summary</h2>
                <p><strong>Risk Score:</strong> {$report['summary']['overall_risk_score']}/100</p>
                <p><strong>Risk Level:</strong> {$report['summary']['risk_level']}</p>
                <p><strong>Total Vulnerabilities:</strong> {$report['summary']['total_vulnerabilities']}</p>
                <p><strong>Total Warnings:</strong> {$report['summary']['total_warnings']}</p>
            </div>

            <h2>🚨 Vulnerabilities Found</h2>
            " . implode('', array_map(function($vuln) {
                return "<div class='vulnerability {$vuln['severity']}'>
                    <h3>{$vuln['title']} ({$vuln['severity']})</h3>
                    <p><strong>File:</strong> {$vuln['file']}</p>
                    <p><strong>Description:</strong> {$vuln['description']}</p>
                    <p><strong>Recommendation:</strong> {$vuln['recommendation']}</p>
                </div>";
            }, $report['vulnerabilities'])) . "

            <h2>⚠️ Warnings</h2>
            <ul>" . implode('', array_map(function($warning) {
                return "<li><strong>{$warning['title']}:</strong> {$warning['description']}</li>";
            }, $report['warnings'])) . "</ul>
        </body>
        </html>";
    }
}

// Run the security scan if called directly
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    $scanner = new SecurityScanner(__DIR__);
    $results = $scanner->runFullSecurityScan();

    // Save results
    $outputFile = __DIR__ . '/security_scan_results_' . date('Y-m-d_H-i-s') . '.json';
    file_put_contents($outputFile, json_encode($results, JSON_PRETTY_PRINT));

    echo "\n💾 Results saved to: {$outputFile}\n";
}
