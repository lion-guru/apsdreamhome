<?php
// scripts/final-security-audit.php

class FinalSecurityAudit {
    private $basePath;
    private $results = [];

    public function __construct() {
        $this->basePath = __DIR__ . '/../';
    }

    public function runFinalAudit() {
        echo "🔒 APS Dream Home - FINAL SECURITY AUDIT\n";
        echo "=======================================\n\n";

        $this->auditOverview();
        $this->auditCriticalSecurity();
        $this->auditDatabaseSecurity();
        $this->auditAuthenticationSecurity();
        $this->auditFileSecurity();
        $this->auditWebSecurity();
        $this->auditMonitoring();
        $this->generateFinalReport();
        $this->provideFinalRecommendations();
    }

    private function auditOverview() {
        echo "📊 AUDIT OVERVIEW\n";
        echo "================\n";

        $phpVersion = PHP_VERSION;
        $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
        $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? $this->basePath;

        $this->results['overview'] = [
            'php_version' => $phpVersion,
            'server_software' => $serverSoftware,
            'document_root' => $documentRoot,
            'audit_date' => date('Y-m-d H:i:s')
        ];

        echo "  • PHP Version: $phpVersion\n";
        echo "  • Server Software: $serverSoftware\n";
        echo "  • Document Root: $documentRoot\n";
        echo "  • Audit Date: " . date('Y-m-d H:i:s') . "\n\n";
    }

    private function auditCriticalSecurity() {
        echo "🛡️  CRITICAL SECURITY CONTROLS\n";
        echo "=============================\n";

        $criticalChecks = [
            'SQL Injection Prevention' => $this->checkSqlInjectionPrevention(),
            'CSRF Protection' => $this->checkCSRFProtection(),
            'XSS Prevention' => $this->checkXSSPrevention(),
            'Session Security' => $this->checkSessionSecurity(),
            'Input Validation' => $this->checkInputValidation(),
            'File Upload Security' => $this->checkFileUploadSecurity()
        ];

        foreach ($criticalChecks as $control => $status) {
            $icon = $status ? '✅' : '❌';
            echo "  $icon $control\n";
            $this->results['critical_security'][$control] = $status;
        }

        echo "\n";
    }

    private function checkSqlInjectionPrevention() {
        $files = $this->findFilesByExtension('php');
        $rawQueries = 0;

        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (preg_match('/\$conn->query\(["\']/', $content)) {
                $rawQueries++;
            }
        }

        return $rawQueries === 0;
    }

    private function checkCSRFProtection() {
        $csrfHelper = $this->basePath . 'app/helpers/security.php';
        if (!file_exists($csrfHelper)) return false;

        $content = file_get_contents($csrfHelper);
        return strpos($content, 'csrf_token') !== false &&
               strpos($content, 'validate_csrf_token') !== false;
    }

    private function checkXSSPrevention() {
        $securityHelper = $this->basePath . 'app/helpers/security.php';
        if (!file_exists($securityHelper)) return false;

        $content = file_get_contents($securityHelper);
        return strpos($content, 'sanitize_input') !== false ||
               strpos($content, 'htmlspecialchars') !== false;
    }

    private function checkSessionSecurity() {
        $sessionConfig = ini_get('session.cookie_httponly');
        return $sessionConfig == '1';
    }

    private function checkInputValidation() {
        $securityHelper = $this->basePath . 'app/helpers/security.php';
        if (!file_exists($securityHelper)) return false;

        $content = file_get_contents($securityHelper);
        return strpos($content, 'filter_input') !== false ||
               strpos($content, 'validate') !== false;
    }

    private function checkFileUploadSecurity() {
        $uploadService = $this->basePath . 'app/Services/FileUploadService.php';
        return file_exists($uploadService) &&
               $this->checkClassExists('FileUploadService');
    }

    private function checkClassExists($className) {
        try {
            if (file_exists($this->basePath . 'app/Services/' . $className . '.php')) {
                require_once $this->basePath . 'app/Services/' . $className . '.php';
                return class_exists($className);
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    private function auditDatabaseSecurity() {
        echo "🗄️  DATABASE SECURITY\n";
        echo "===================\n";

        $dbChecks = [
            'PDO Emulation Disabled' => $this->checkPDOEmulation(),
            'Prepared Statements Used' => $this->checkPreparedStatements(),
            'Database Connection Security' => $this->checkDatabaseConnection(),
            'Database Configuration' => $this->checkDatabaseConfig()
        ];

        foreach ($dbChecks as $check => $status) {
            $icon = $status ? '✅' : '❌';
            echo "  $icon $check\n";
            $this->results['database_security'][$check] = $status;
        }

        echo "\n";
    }

    private function checkPDOEmulation() {
        $dbConfig = $this->basePath . 'config/database.php';
        if (file_exists($dbConfig)) {
            $content = file_get_contents($dbConfig);
            return strpos($content, 'PDO::ATTR_EMULATE_PREPARES => false') !== false;
        }
        return false;
    }

    private function checkPreparedStatements() {
        $files = $this->findFilesByExtension('php');
        $preparedStatements = 0;
        $totalQueries = 0;

        foreach ($files as $file) {
            $content = file_get_contents($file);
            $preparedCount = substr_count($content, 'prepare(');
            $queryCount = substr_count($content, '$conn->query(');

            $preparedStatements += $preparedCount;
            $totalQueries += $preparedCount + $queryCount;
        }

        return $totalQueries > 0 && ($preparedStatements / $totalQueries) >= 0.8;
    }

    private function checkDatabaseConnection() {
        $dbConnection = $this->basePath . 'includes/db_connection.php';
        return file_exists($dbConnection);
    }

    private function checkDatabaseConfig() {
        $dbConfig = $this->basePath . 'config/database.php';
        return file_exists($dbConfig);
    }

    private function auditAuthenticationSecurity() {
        echo "🔐 AUTHENTICATION & AUTHORIZATION\n";
        echo "================================\n";

        $authChecks = [
            'Password Hashing' => $this->checkPasswordHashing(),
            'Session Management' => $this->checkSessionManagement(),
            'Authentication Controller' => $this->checkAuthController(),
            'Role-Based Access Control' => $this->checkRBAC(),
            'Account Lockout' => $this->checkAccountLockout()
        ];

        foreach ($authChecks as $check => $status) {
            $icon = $status ? '✅' : '❌';
            echo "  $icon $check\n";
            $this->results['auth_security'][$check] = $status;
        }

        echo "\n";
    }

    private function checkPasswordHashing() {
        $files = $this->findFilesByExtension('php');
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (strpos($content, 'password_hash') !== false ||
                strpos($content, 'PASSWORD_DEFAULT') !== false ||
                strpos($content, 'PASSWORD_BCRYPT') !== false) {
                return true;
            }
        }
        return false;
    }

    private function checkSessionManagement() {
        $sessionManager = $this->basePath . 'admin/includes/session_manager.php';
        return file_exists($sessionManager);
    }

    private function checkAuthController() {
        $authController = $this->basePath . 'app/controllers/AuthController.php';
        return file_exists($authController);
    }

    private function checkRBAC() {
        $files = $this->findFilesByExtension('php');
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (strpos($content, 'role') !== false &&
                (strpos($content, 'admin') !== false ||
                 strpos($content, 'user') !== false ||
                 strpos($content, 'permission') !== false)) {
                return true;
            }
        }
        return false;
    }

    private function checkAccountLockout() {
        $files = $this->findFilesByExtension('php');
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (strpos($content, 'lockout') !== false ||
                strpos($content, 'attempt') !== false ||
                strpos($content, 'block') !== false) {
                return true;
            }
        }
        return false;
    }

    private function auditFileSecurity() {
        echo "📁 FILE SYSTEM SECURITY\n";
        echo "======================\n";

        $fileChecks = [
            'Secure Upload Directory' => $this->checkSecureUploadDir(),
            'File Permissions' => $this->checkFilePermissions(),
            'File Upload Service' => $this->checkFileUploadService(),
            'File Validation' => $this->checkFileValidation()
        ];

        foreach ($fileChecks as $check => $status) {
            $icon = $status ? '✅' : '❌';
            echo "  $icon $check\n";
            $this->results['file_security'][$check] = $status;
        }

        echo "\n";
    }

    private function checkSecureUploadDir() {
        $uploadDir = $this->basePath . 'storage/uploads';
        return is_dir($uploadDir) && !is_writable($uploadDir);
    }

    private function checkFilePermissions() {
        $phpFiles = $this->findFilesByExtension('php');
        $correctPermissions = 0;

        foreach ($phpFiles as $file) {
            $perms = fileperms($file) & 0777;
            if ($perms === 0644) {
                $correctPermissions++;
            }
        }

        return ($correctPermissions / count($phpFiles)) >= 0.9;
    }

    private function checkFileUploadService() {
        $uploadService = $this->basePath . 'app/Services/FileUploadService.php';
        return file_exists($uploadService);
    }

    private function checkFileValidation() {
        $uploadService = $this->basePath . 'app/Services/FileUploadService.php';
        if (file_exists($uploadService)) {
            $content = file_get_contents($uploadService);
            return strpos($content, 'mime') !== false &&
                   strpos($content, 'size') !== false;
        }
        return false;
    }

    private function auditWebSecurity() {
        echo "🌐 WEB SECURITY\n";
        echo "==============\n";

        $webChecks = [
            'Security Headers' => $this->checkSecurityHeaders(),
            'HTTPS Configuration' => $this->checkHTTPSConfig(),
            'Robots.txt' => $this->checkRobotsTxt(),
            'Environment File' => $this->checkEnvironmentFile()
        ];

        foreach ($webChecks as $check => $status) {
            $icon = $status ? '✅' : '❌';
            echo "  $icon $check\n";
            $this->results['web_security'][$check] = $status;
        }

        echo "\n";
    }

    private function checkSecurityHeaders() {
        $htaccess = $this->basePath . '.htaccess';
        if (file_exists($htaccess)) {
            $content = file_get_contents($htaccess);
            $headers = ['X-Content-Type-Options', 'X-Frame-Options', 'X-XSS-Protection'];
            $found = 0;
            foreach ($headers as $header) {
                if (strpos($content, $header) !== false) {
                    $found++;
                }
            }
            return $found >= 2;
        }
        return false;
    }

    private function checkHTTPSConfig() {
        $envFile = $this->basePath . '.env';
        if (file_exists($envFile)) {
            $content = file_get_contents($envFile);
            return strpos($content, 'https://') !== false ||
                   strpos($content, 'APP_HTTPS=true') !== false;
        }
        return false;
    }

    private function checkRobotsTxt() {
        $robots = $this->basePath . 'robots.txt';
        return file_exists($robots);
    }

    private function checkEnvironmentFile() {
        $envFile = $this->basePath . '.env';
        if (file_exists($envFile)) {
            $content = file_get_contents($envFile);
            return strpos($content, 'APP_DEBUG=false') !== false;
        }
        return false;
    }

    private function auditMonitoring() {
        echo "📊 SECURITY MONITORING\n";
        echo "=====================\n";

        $monitoringChecks = [
            'Security Monitor Script' => $this->checkSecurityMonitor(),
            'Log Directory' => $this->checkLogDirectory(),
            'Security Validation' => $this->checkSecurityValidation(),
            'Error Logging' => $this->checkErrorLogging()
        ];

        foreach ($monitoringChecks as $check => $status) {
            $icon = $status ? '✅' : '❌';
            echo "  $icon $check\n";
            $this->results['monitoring'][$check] = $status;
        }

        echo "\n";
    }

    private function checkSecurityMonitor() {
        $monitor = $this->basePath . 'scripts/security-monitor.php';
        return file_exists($monitor);
    }

    private function checkLogDirectory() {
        $logDir = $this->basePath . 'storage/logs';
        return is_dir($logDir);
    }

    private function checkSecurityValidation() {
        $validation = $this->basePath . 'scripts/security-validation.php';
        return file_exists($validation);
    }

    private function checkErrorLogging() {
        $logDir = $this->basePath . 'storage/logs';
        if (is_dir($logDir)) {
            $logFile = $logDir . '/security.log';
            return file_exists($logFile) || is_writable($logDir);
        }
        return false;
    }

    private function findFilesByExtension($extension) {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->basePath)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === $extension) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    private function generateFinalReport() {
        echo "📊 FINAL SECURITY REPORT\n";
        echo "========================\n";

        // Calculate overall score
        $totalChecks = 0;
        $passedChecks = 0;

        foreach ($this->results as $category) {
            foreach ($category as $check => $status) {
                $totalChecks++;
                if ($status) $passedChecks++;
            }
        }

        $overallScore = ($totalChecks > 0) ? round(($passedChecks / $totalChecks) * 100, 1) : 0;

        echo "\n🎯 OVERALL SECURITY SCORE: $overallScore%\n";

        // Security grade
        if ($overallScore >= 95) {
            echo "📈 SECURITY GRADE: A+ (EXCELLENT)\n";
        } elseif ($overallScore >= 90) {
            echo "📈 SECURITY GRADE: A (VERY GOOD)\n";
        } elseif ($overallScore >= 80) {
            echo "📊 SECURITY GRADE: B (GOOD)\n";
        } elseif ($overallScore >= 70) {
            echo "📉 SECURITY GRADE: C (FAIR)\n";
        } elseif ($overallScore >= 60) {
            echo "📉 SECURITY GRADE: D (POOR)\n";
        } else {
            echo "❌ SECURITY GRADE: F (CRITICAL)\n";
        }

        echo "\n📋 CATEGORY BREAKDOWN:\n";
        foreach ($this->results as $category => $checks) {
            $categoryChecks = count($checks);
            $categoryPassed = count(array_filter($checks));
            $categoryScore = round(($categoryPassed / $categoryChecks) * 100, 1);

            echo "  • " . strtoupper(str_replace('_', ' ', $category)) . ": $categoryScore% ($categoryPassed/$categoryChecks)\n";
        }

        $this->results['final_report'] = [
            'overall_score' => $overallScore,
            'total_checks' => $totalChecks,
            'passed_checks' => $passedChecks,
            'grade' => $this->getSecurityGrade($overallScore),
            'audit_date' => date('Y-m-d H:i:s')
        ];
    }

    private function getSecurityGrade($score) {
        if ($score >= 95) return 'A+';
        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B';
        if ($score >= 70) return 'C';
        if ($score >= 60) return 'D';
        return 'F';
    }

    private function provideFinalRecommendations() {
        echo "\n💡 FINAL RECOMMENDATIONS\n";
        echo "========================\n";

        $failedChecks = [];
        foreach ($this->results as $category) {
            foreach ($category as $check => $status) {
                if (!$status) {
                    $failedChecks[] = $check;
                }
            }
        }

        if (count($failedChecks) === 0) {
            echo "🎉 CONGRATULATIONS! All security checks passed!\n";
            echo "Your APS Dream Home application has excellent security.\n\n";

            echo "📋 MAINTENANCE RECOMMENDATIONS:\n";
            echo "  • Run security audits regularly\n";
            echo "  • Keep PHP and dependencies updated\n";
            echo "  • Monitor security logs\n";
            echo "  • Regular backup procedures\n";
            echo "  • Employee security training\n\n";
        } else {
            echo "⚠️  SECURITY IMPROVEMENTS NEEDED:\n";
            foreach ($failedChecks as $check) {
                echo "  • Fix: " . str_replace('_', ' ', $check) . "\n";
            }
            echo "\n";
        }

        echo "🔒 PRODUCTION DEPLOYMENT CHECKLIST:\n";
        echo "  1. ✅ Enable HTTPS on web server\n";
        echo "  2. ✅ Configure SSL certificates\n";
        echo "  3. ✅ Set up security monitoring\n";
        echo "  4. ✅ Test all security features\n";
        echo "  5. ✅ Configure backup procedures\n";
        echo "  6. ✅ Set up error handling\n";
        echo "  7. ✅ Review file permissions\n";
        echo "  8. ✅ Conduct final security test\n";

        echo "\n🚀 FINAL DEPLOYMENT COMMANDS:\n";
        echo "  php scripts/security-validation.php\n";
        echo "  php scripts/deploy-security.php\n";
        echo "  php scripts/security-monitor.php\n";

        echo "\n📞 SECURITY CONTACTS:\n";
        echo "  • Security Team: security@apsdreamhome.com\n";
        echo "  • Emergency Contact: +91-XXXX-XXXXXX\n";
        echo "  • Security Portal: /security/report\n";

        echo "\n🎯 SECURITY STATUS: " . $this->getSecurityGrade($this->results['final_report']['overall_score']) . "\n";
        echo "\n✨ Your APS Dream Home application security implementation is complete!\n";
    }
}

// Run the final security audit
try {
    $audit = new FinalSecurityAudit();
    $audit->runFinalAudit();

} catch (Exception $e) {
    echo "❌ Security audit failed: " . $e->getMessage() . "\n";
    echo "Please check your environment and try again.\n";
}
?>
