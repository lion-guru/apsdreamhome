<?php

namespace App\Core;

/**
 * Comprehensive Security Audit System
 * Analyzes and reports security vulnerabilities and improvements
 */
class SecurityAudit
{
    private $vulnerabilities = [];
    private $recommendations = [];
    private $scores = [];

    public function __construct()
    {
        $this->runAudit();
    }

    /**
     * Run complete security audit
     */
    public function runAudit(): array
    {
        $this->checkFilePermissions();
        $this->checkDatabaseSecurity();
        $this->checkInputValidation();
        $this->checkSessionSecurity();
        $this->checkPasswordSecurity();
        $this->checkEncryption();
        $this->checkHeaders();
        $this->checkDependencies();
        $this->checkBackupSecurity();

        return [
            'vulnerabilities' => $this->vulnerabilities,
            'recommendations' => $this->recommendations,
            'scores' => $this->calculateScores(),
            'overall_score' => $this->calculateOverallScore()
        ];
    }

    /**
     * Check file and directory permissions
     */
    private function checkFilePermissions(): void
    {
        $criticalPaths = [
            __DIR__ . '/../config',
            __DIR__ . '/../logs',
            __DIR__ . '/../../database',
            __DIR__ . '/../../api'
        ];

        foreach ($criticalPaths as $path) {
            if (is_dir($path)) {
                $permissions = substr(sprintf('%o', fileperms($path)), -4);

                if ($permissions > '0755') {
                    $this->vulnerabilities[] = [
                        'type' => 'file_permissions',
                        'severity' => 'high',
                        'path' => $path,
                        'issue' => "Directory permissions too permissive: {$permissions}",
                        'solution' => 'Set permissions to 0755 or 0750'
                    ];
                } else {
                    $this->recommendations[] = [
                        'type' => 'file_permissions',
                        'status' => 'good',
                        'path' => $path,
                        'message' => "Directory permissions are secure: {$permissions}"
                    ];
                }
            }
        }

        // Check .htaccess file
        $htaccessPath = __DIR__ . '/../../.htaccess';
        if (file_exists($htaccessPath)) {
            $this->recommendations[] = [
                'type' => 'htaccess',
                'status' => 'good',
                'message' => '.htaccess file exists and provides security'
            ];
        } else {
            $this->vulnerabilities[] = [
                'type' => 'htaccess',
                'severity' => 'medium',
                'issue' => '.htaccess file missing',
                'solution' => 'Create .htaccess file with proper security headers'
            ];
        }
    }

    /**
     * Check database security
     */
    private function checkDatabaseSecurity(): void
    {
        try {
            $db = Database::getInstance();

            // Check for weak passwords in configuration
            $configFile = __DIR__ . '/../config/database.php';
            if (file_exists($configFile)) {
                $content = file_get_contents($configFile);

                if (preg_match('/password.*=.*["\']([^"\']+)["\']/', $content, $matches)) {
                    $password = $matches[1];

                    if (strlen($password) < 8) {
                        $this->vulnerabilities[] = [
                            'type' => 'database_password',
                            'severity' => 'high',
                            'issue' => 'Database password too short',
                            'solution' => 'Use password with at least 8 characters'
                        ];
                    }

                    if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
                        $this->vulnerabilities[] = [
                            'type' => 'database_password',
                            'severity' => 'medium',
                            'issue' => 'Database password too weak',
                            'solution' => 'Use strong password with uppercase, lowercase, and numbers'
                        ];
                    }
                }
            }

            // Check for SQL injection protection
            $this->checkSqlInjection();

        } catch (\Exception $e) {
            $this->vulnerabilities[] = [
                'type' => 'database_connection',
                'severity' => 'high',
                'issue' => 'Database connection failed',
                'solution' => 'Check database configuration and credentials'
            ];
        }
    }

    /**
     * Check for SQL injection vulnerabilities
     */
    private function checkSqlInjection(): void
    {
        // Check if prepared statements are used
        $modelFiles = glob(__DIR__ . '/../models/*.php');

        foreach ($modelFiles as $file) {
            $content = file_get_contents($file);

            // Check for direct SQL concatenation (potential vulnerability)
            if (preg_match('/\$sql.*=.*["\'](.*?)["\'].*\$/m', $content, $matches)) {
                $this->vulnerabilities[] = [
                    'type' => 'sql_injection',
                    'severity' => 'high',
                    'file' => basename($file),
                    'issue' => 'Potential SQL injection vulnerability',
                    'solution' => 'Use prepared statements instead of string concatenation'
                ];
            }
        }
    }

    /**
     * Check input validation
     */
    private function checkInputValidation(): void
    {
        $controllerFiles = glob(__DIR__ . '/../controllers/*.php');

        foreach ($controllerFiles as $file) {
            $content = file_get_contents($file);

            // Check for unsanitized $_GET, $_POST, $_REQUEST usage
            $patterns = [
                '/\$_GET\[/',
                '/\$_POST\[/',
                '/\$_REQUEST\[/'
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    $this->vulnerabilities[] = [
                        'type' => 'input_validation',
                        'severity' => 'medium',
                        'file' => basename($file),
                        'issue' => 'Potential XSS vulnerability - unsanitized input',
                        'solution' => 'Use proper input validation and sanitization'
                    ];
                    break;
                }
            }
        }
    }

    /**
     * Check session security
     */
    private function checkSessionSecurity(): void
    {
        // Check session configuration
        $sessionConfig = [
            'session.cookie_secure' => ini_get('session.cookie_secure'),
            'session.cookie_httponly' => ini_get('session.cookie_httponly'),
            'session.use_strict_mode' => ini_get('session.use_strict_mode')
        ];

        foreach ($sessionConfig as $key => $value) {
            if (!$value) {
                $this->vulnerabilities[] = [
                    'type' => 'session_security',
                    'severity' => 'medium',
                    'issue' => "Session security setting disabled: {$key}",
                    'solution' => 'Enable session security settings in php.ini or application'
                ];
            }
        }
    }

    /**
     * Check password security
     */
    private function checkPasswordSecurity(): void
    {
        // Check password hashing usage
        $authFiles = [
            __DIR__ . '/../controllers/AuthController.php',
            __DIR__ . '/../../includes/Auth.php'
        ];

        foreach ($authFiles as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);

                if (strpos($content, 'md5') !== false || strpos($content, 'sha1') !== false) {
                    $this->vulnerabilities[] = [
                        'type' => 'password_hashing',
                        'severity' => 'high',
                        'file' => basename($file),
                        'issue' => 'Weak password hashing algorithm detected',
                        'solution' => 'Use password_hash() and password_verify() instead of MD5/SHA1'
                    ];
                }

                if (strpos($content, 'password_hash') !== false) {
                    $this->recommendations[] = [
                        'type' => 'password_hashing',
                        'status' => 'good',
                        'message' => 'Strong password hashing implemented'
                    ];
                }
            }
        }
    }

    /**
     * Check encryption implementation
     */
    private function checkEncryption(): void
    {
        // Check for proper encryption usage
        $files = glob(__DIR__ . '/../../includes/*.php');

        foreach ($files as $file) {
            $content = file_get_contents($file);

            if (strpos($content, 'openssl_encrypt') !== false || strpos($content, 'mcrypt_encrypt') !== false) {
                $this->recommendations[] = [
                    'type' => 'encryption',
                    'status' => 'good',
                    'message' => 'Encryption functions properly implemented'
                ];
            }
        }
    }

    /**
     * Check security headers
     */
    private function checkHeaders(): void
    {
        $requiredHeaders = [
            'X-Frame-Options',
            'X-Content-Type-Options',
            'X-XSS-Protection',
            'Referrer-Policy'
        ];

        $htaccessContent = file_get_contents(__DIR__ . '/../../.htaccess');

        foreach ($requiredHeaders as $header) {
            if (strpos($htaccessContent, $header) !== false) {
                $this->recommendations[] = [
                    'type' => 'security_headers',
                    'status' => 'good',
                    'header' => $header,
                    'message' => "Security header {$header} is configured"
                ];
            } else {
                $this->vulnerabilities[] = [
                    'type' => 'security_headers',
                    'severity' => 'medium',
                    'header' => $header,
                    'issue' => "Security header {$header} is missing",
                    'solution' => "Add {$header} header to .htaccess"
                ];
            }
        }
    }

    /**
     * Check dependencies security
     */
    private function checkDependencies(): void
    {
        // Check for outdated or vulnerable dependencies
        $composerFile = __DIR__ . '/../../composer.json';

        if (file_exists($composerFile)) {
            $composer = json_decode(file_get_contents($composerFile), true);

            if (isset($composer['require'])) {
                foreach ($composer['require'] as $package => $version) {
                    // Check for potentially vulnerable packages
                    $vulnerablePackages = ['guzzlehttp/guzzle', 'symfony/http-foundation'];

                    if (in_array($package, $vulnerablePackages)) {
                        $this->recommendations[] = [
                            'type' => 'dependency_security',
                            'status' => 'warning',
                            'package' => $package,
                            'message' => "Package {$package} should be kept updated"
                        ];
                    }
                }
            }
        }
    }

    /**
     * Check backup security
     */
    private function checkBackupSecurity(): void
    {
        $backupDir = __DIR__ . '/../../database/backups/';

        if (is_dir($backupDir)) {
            $files = scandir($backupDir);

            foreach ($files as $file) {
                if (strpos($file, '.sql') !== false) {
                    $filePath = $backupDir . $file;

                    if (is_readable($filePath)) {
                        $this->vulnerabilities[] = [
                            'type' => 'backup_security',
                            'severity' => 'high',
                            'file' => $file,
                            'issue' => 'Database backup file is readable',
                            'solution' => 'Set proper permissions on backup files or move to secure location'
                        ];
                    }
                }
            }
        }
    }

    /**
     * Calculate security scores
     */
    private function calculateScores(): array
    {
        $totalVulnerabilities = count($this->vulnerabilities);
        $totalRecommendations = count($this->recommendations);

        return [
            'vulnerabilities' => $totalVulnerabilities,
            'recommendations' => $totalRecommendations,
            'security_score' => max(0, 100 - ($totalVulnerabilities * 10)),
            'implementation_score' => min(100, $totalRecommendations * 5)
        ];
    }

    /**
     * Calculate overall security score
     */
    private function calculateOverallScore(): int
    {
        $scores = $this->calculateScores();
        return (int) (($scores['security_score'] + $scores['implementation_score']) / 2);
    }

    /**
     * Generate security report
     */
    public function generateReport(): string
    {
        $audit = $this->runAudit();
        $scores = $audit['scores'];

        $report = "=== APS Dream Home - Security Audit Report ===\n\n";
        $report .= "Overall Security Score: {$audit['overall_score']}/100\n";
        $report .= "Security Score: {$scores['security_score']}/100\n";
        $report .= "Implementation Score: {$scores['implementation_score']}/100\n\n";

        if (!empty($audit['vulnerabilities'])) {
            $report .= "=== VULNERABILITIES FOUND ===\n\n";

            foreach ($audit['vulnerabilities'] as $vulnerability) {
                $severity = strtoupper($vulnerability['severity']);
                $report .= "[{$severity}] {$vulnerability['type']}\n";
                $report .= "Issue: {$vulnerability['issue']}\n";
                $report .= "Solution: {$vulnerability['solution']}\n";
                if (isset($vulnerability['file'])) {
                    $report .= "File: {$vulnerability['file']}\n";
                }
                $report .= "\n";
            }
        }

        if (!empty($audit['recommendations'])) {
            $report .= "=== SECURITY RECOMMENDATIONS ===\n\n";

            foreach ($audit['recommendations'] as $recommendation) {
                $status = strtoupper($recommendation['status']);
                $report .= "[{$status}] {$recommendation['type']}\n";
                $report .= "{$recommendation['message']}\n";
                if (isset($recommendation['file'])) {
                    $report .= "File: {$recommendation['file']}\n";
                }
                $report .= "\n";
            }
        }

        $report .= "=== SUMMARY ===\n";
        if ($audit['overall_score'] >= 80) {
            $report .= "✅ EXCELLENT - Security posture is strong\n";
        } elseif ($audit['overall_score'] >= 60) {
            $report .= "⚠️  GOOD - Some improvements needed\n";
        } else {
            $report .= "❌ POOR - Immediate security improvements required\n";
        }

        return $report;
    }

    /**
     * Get vulnerabilities
     */
    public function getVulnerabilities(): array
    {
        return $this->vulnerabilities;
    }

    /**
     * Get recommendations
     */
    public function getRecommendations(): array
    {
        return $this->recommendations;
    }

    /**
     * Get security scores
     */
    public function getScores(): array
    {
        return $this->calculateScores();
    }
}

/**
 * Global security audit function
 */
function security_audit(): array
{
    $audit = new SecurityAudit();
    return $audit->runAudit();
}

function security_report(): string
{
    $audit = new SecurityAudit();
    return $audit->generateReport();
}

?>
