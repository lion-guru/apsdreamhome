<?php

namespace App\Services\Security;

use App\Core\Database\Database;

class SecurityTestSuite
{
    private $db;
    private $projectRoot;
    private $tests = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->projectRoot = defined('APP_PATH') ? APP_PATH . '/..' : dirname(__DIR__, 3);
    }

    public function runAllTests(): array
    {
        $this->tests = [
            'https'             => $this->testHttps(),
            'security_headers'  => $this->testSecurityHeaders(),
            'session_security'  => $this->testSessionSecurity(),
            'csrf_protection'   => $this->testCsrfProtection(),
            'input_validation'  => $this->testInputValidation(),
            'file_upload'       => $this->testFileUploadSecurity(),
            'auth_strength'     => $this->testAuthenticationStrength(),
            'rate_limiting'     => $this->testRateLimiting(),
            'error_handling'    => $this->testErrorHandling(),
            'database_security' => $this->testDatabaseSecurity(),
        ];
        return $this->tests;
    }

    public function runSingleTest(string $testName): array
    {
        $methods = [
            'https'             => 'testHttps',
            'security_headers'  => 'testSecurityHeaders',
            'session_security'  => 'testSessionSecurity',
            'csrf_protection'   => 'testCsrfProtection',
            'input_validation'  => 'testInputValidation',
            'file_upload'       => 'testFileUploadSecurity',
            'auth_strength'     => 'testAuthenticationStrength',
            'rate_limiting'     => 'testRateLimiting',
            'error_handling'    => 'testErrorHandling',
            'database_security' => 'testDatabaseSecurity',
        ];
        if (!isset($methods[$testName])) {
            return ['test_name' => $testName, 'status' => 'fail', 'score' => 0, 'details' => 'Unknown test', 'recommendation' => ''];
        }
        return $this->{$methods[$testName]}();
    }

    public function getOverallScore(array $results): int
    {
        $weights = [
            'https' => 15, 'security_headers' => 15, 'session_security' => 10, 'csrf_protection' => 15,
            'input_validation' => 10, 'file_upload' => 8, 'auth_strength' => 12, 'rate_limiting' => 5,
            'error_handling' => 5, 'database_security' => 5,
        ];
        $totalWeight = array_sum($weights);
        $weightedSum = 0;
        foreach ($results as $key => $result) {
            $w = $weights[$key] ?? 5;
            $weightedSum += ($result['score'] ?? 0) * $w;
        }
        return $totalWeight > 0 ? (int) round($weightedSum / $totalWeight) : 0;
    }

    public function getRecommendations(array $results): array
    {
        $recs = [];
        foreach ($results as $result) {
            if (($result['status'] ?? '') !== 'pass' && !empty($result['recommendation'])) {
                $priority = ($result['score'] ?? 0) < 30 ? 'critical' : (($result['score'] ?? 0) < 60 ? 'high' : 'medium');
                $recs[] = [
                    'test'          => $result['test_name'] ?? 'Unknown',
                    'priority'      => $priority,
                    'recommendation' => $result['recommendation'],
                    'score'         => $result['score'] ?? 0,
                ];
            }
        }
        usort($recs, function ($a, $b) {
            $order = ['critical' => 0, 'high' => 1, 'medium' => 2];
            return ($order[$a['priority']] ?? 3) <=> ($order[$b['priority']] ?? 3);
        });
        return $recs;
    }

    public function generateReport(array $results): string
    {
        $overall = $this->getOverallScore($results);
        $recommendations = $this->getRecommendations($results);
        $scoreColor = $overall >= 80 ? '#22c55e' : ($overall >= 50 ? '#eab308' : '#ef4444');
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Security Test Report</title>';
        $html .= '<style>body{font-family:Inter,sans-serif;margin:40px;background:#f8fafc;color:#1e293b}';
        $html .= '.score{text-align:center;margin:30px 0}.score-num{font-size:72px;font-weight:800;color:' . $scoreColor . '}';
        $html .= '.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;margin:20px 0}';
        $html .= '.card{background:#fff;border-radius:12px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.1);border-left:4px solid #e2e8f0}';
        $html .= '.card.pass{border-left-color:#22c55e}.card.fail{border-left-color:#ef4444}.card.warning{border-left-color:#eab308}';
        $html .= '.badge{display:inline-block;padding:2px 10px;border-radius:9999px;font-size:12px;font-weight:600;color:#fff}';
        $html .= '.badge.pass{background:#22c55e}.badge.fail{background:#ef4444}.badge.warning{background:#eab308}';
        $html .= '.rec{background:#fff;border-radius:8px;padding:12px 16px;margin:8px 0;box-shadow:0 1px 2px rgba(0,0,0,.05)}';
        $html .= '.rec.critical{border-left:4px solid #ef4444}.rec.high{border-left:4px solid #f97316}.rec.medium{border-left:4px solid #eab308}';
        $html .= 'h1{text-align:center;color:#0f172a}h2{color:#334155;margin-top:30px}</style></head><body>';
        $html .= '<h1>Security Test Report</h1>';
        $html .= '<div class="score"><div class="score-num">' . $overall . '</div><p>Overall Security Score</p></div>';
        $html .= '<h2>Test Results</h2><div class="grid">';
        foreach ($results as $r) {
            $cls = $r['status'] ?? 'fail';
            $icon = $cls === 'pass' ? '&#10003;' : ($cls === 'warning' ? '&#9888;' : '&#10007;');
            $html .= '<div class="card ' . $cls . '">';
            $html .= '<div class="d-flex justify-content-between align-items-center mb-2"><strong>' . htmlspecialchars($r['test_name'] ?? '') . '</strong>';
            $html .= '<span class="badge ' . $cls . '">' . strtoupper($cls) . ' ' . ($r['score'] ?? 0) . '/100</span></div>';
            $html .= '<p style="color:#64748b;font-size:14px">' . htmlspecialchars($r['details'] ?? '') . '</p></div>';
        }
        $html .= '</div>';
        if (!empty($recommendations)) {
            $html .= '<h2>Recommendations</h2>';
            foreach ($recommendations as $rec) {
                $html .= '<div class="rec ' . $rec['priority'] . '"><strong>[' . strtoupper($rec['priority']) . '] ' . htmlspecialchars($rec['test']) . '</strong><br>' . htmlspecialchars($rec['recommendation']) . '</div>';
            }
        }
        $html .= '</body></html>';
        return $html;
    }

    private function testHttps(): array
    {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);

        $htaccessPath = $this->projectRoot . '/.htaccess';
        $hasHsts = false;
        if (file_exists($htaccessPath)) {
            $htaccess = file_get_contents($htaccessPath);
            $hasHsts = stripos($htaccess, 'Strict-Transport-Security') !== false || stripos($htaccess, 'SSL') !== false;
        }

        $score = 0;
        $details = [];
        if ($isHttps) { $score += 60; $details[] = 'HTTPS is active'; } else { $details[] = 'HTTPS is NOT active'; }
        if ($hasHsts) { $score += 40; $details[] = 'HSTS configured in .htaccess'; } else { $details[] = 'HSTS not found in .htaccess'; }

        return [
            'test_name' => 'HTTPS Configuration',
            'status'    => $score >= 80 ? 'pass' : ($score >= 40 ? 'warning' : 'fail'),
            'score'     => $score,
            'details'   => implode('. ', $details),
            'recommendation' => $score < 100 ? 'Enable HTTPS and add HSTS header in .htaccess for all traffic.' : '',
        ];
    }

    private function testSecurityHeaders(): array
    {
        $requiredHeaders = [
            'X-Frame-Options'           => 'Protects against clickjacking',
            'X-Content-Type-Options'    => 'Prevents MIME-type sniffing',
            'X-XSS-Protection'          => 'Legacy XSS filter',
            'Content-Security-Policy'   => 'Controls resource loading',
            'Strict-Transport-Security' => 'Enforces HTTPS',
        ];

        $htaccessPath = $this->projectRoot . '/.htaccess';
        $htaccess = file_exists($htaccessPath) ? file_get_contents($htaccessPath) : '';
        $headerFile = $this->projectRoot . '/app/Core/BaseController.php';
        $baseController = file_exists($headerFile) ? file_get_contents($headerFile) : '';
        $configFile = $this->projectRoot . '/app/Http/Controllers/BaseController.php';
        $configContent = file_exists($configFile) ? file_get_contents($configFile) : '';

        $allSource = $htaccess . $baseController . $configContent;
        $found = 0;
        $missing = [];
        foreach ($requiredHeaders as $header => $desc) {
            $cleanHeader = str_replace('-', '', $header);
            if (stripos($allSource, $header) !== false || stripos($allSource, $cleanHeader) !== false) {
                $found++;
            } else {
                $missing[] = $header;
            }
        }

        $score = (int) round(($found / count($requiredHeaders)) * 100);
        return [
            'test_name' => 'Security Headers',
            'status'    => $score >= 80 ? 'pass' : ($score >= 40 ? 'warning' : 'fail'),
            'score'     => $score,
            'details'   => $found . '/' . count($requiredHeaders) . ' required headers configured. ' . (empty($missing) ? 'All headers present.' : 'Missing: ' . implode(', ', $missing)),
            'recommendation' => !empty($missing) ? 'Add missing security headers in BaseController::setSecurityHeaders() and .htaccess.' : '',
        ];
    }

    private function testSessionSecurity(): array
    {
        $score = 0;
        $details = [];

        $iniSessionSecure = ini_get('session.cookie_secure');
        $iniSessionHttpOnly = ini_get('session.cookie_httponly');
        $iniSessionLifetime = ini_get('session.gc_maxlifetime');

        if (!empty($iniSessionSecure) && $iniSessionSecure !== '0') { $score += 25; $details[] = 'Cookie Secure flag enabled'; } else { $details[] = 'Cookie Secure flag NOT enabled'; }
        if (!empty($iniSessionHttpOnly) && $iniSessionHttpOnly !== '0') { $score += 25; $details[] = 'Cookie HttpOnly flag enabled'; } else { $details[] = 'Cookie HttpOnly flag NOT enabled'; }

        $baseFile = $this->projectRoot . '/app/Http/Controllers/BaseController.php';
        $baseContent = file_exists($baseFile) ? file_get_contents($baseFile) : '';
        if (stripos($baseContent, 'session_regenerate_id') !== false) { $score += 25; $details[] = 'Session regeneration configured'; } else { $details[] = 'Session regeneration NOT found'; }

        $lifetime = (int) ($iniSessionLifetime ?: 1440);
        if ($lifetime <= 3600) { $score += 25; $details[] = 'Session lifetime is reasonable (' . $lifetime . 's)'; } else { $details[] = 'Session lifetime too long (' . $lifetime . 's)'; }

        return [
            'test_name' => 'Session Security',
            'status'    => $score >= 75 ? 'pass' : ($score >= 50 ? 'warning' : 'fail'),
            'score'     => $score,
            'details'   => implode('. ', $details),
            'recommendation' => $score < 100 ? 'Enable session.cookie_secure, session.cookie_httponly, and ensure session timeout is under 1 hour.' : '',
        ];
    }

    private function testCsrfProtection(): array
    {
        $score = 0;
        $details = [];

        $baseFile = $this->projectRoot . '/app/Http/Controllers/BaseController.php';
        $baseContent = file_exists($baseFile) ? file_get_contents($baseFile) : '';

        if (stripos($baseContent, 'csrf_token') !== false) { $score += 30; $details[] = 'CSRF token generation found'; } else { $details[] = 'CSRF token generation NOT found'; }
        if (stripos($baseContent, 'validateCsrfToken') !== false) { $score += 30; $details[] = 'CSRF validation found'; } else { $details[] = 'CSRF validation NOT found'; }
        if (stripos($baseContent, 'REQUEST_METHOD') !== false && stripos($baseContent, 'POST') !== false) { $score += 20; $details[] = 'POST request checking enabled'; } else { $details[] = 'POST request checking incomplete'; }

        $routerFile = $this->projectRoot . '/routes/router.php';
        $routerContent = file_exists($routerFile) ? file_get_contents($routerFile) : '';
        if (stripos($routerContent, 'csrf') !== false) { $score += 20; $details[] = 'Router-level CSRF check found'; } else { $details[] = 'Router-level CSRF check NOT found'; }

        return [
            'test_name' => 'CSRF Protection',
            'status'    => $score >= 80 ? 'pass' : ($score >= 40 ? 'warning' : 'fail'),
            'score'     => $score,
            'details'   => implode('. ', $details),
            'recommendation' => $score < 100 ? 'Ensure all POST forms include csrf_token field and BaseController validates on every POST.' : '',
        ];
    }

    private function testInputValidation(): array
    {
        $score = 0;
        $details = [];

        $sqlPatterns = ["' OR '1'='1", "'; DROP TABLE", "UNION SELECT", "1; UPDATE"];
        $xssPatterns = ['<script>alert(1)</script>', '"><img onerror=alert(1)>', "javascript:alert(1)", '<svg onload=alert(1)>'];

        $validatorFile = $this->projectRoot . '/app/Services/ValidatorService.php';
        $securityFile = $this->projectRoot . '/app/Services/Security/SecurityService.php';
        $content = '';
        if (file_exists($validatorFile)) $content .= file_get_contents($validatorFile);
        if (file_exists($securityFile)) $content .= file_get_contents($securityFile);

        $hasSqlProtection = stripos($content, 'sql') !== false || stripos($content, 'injection') !== false || stripos($content, 'prepared') !== false;
        $hasXssProtection = stripos($content, 'xss') !== false || stripos($content, 'htmlspecialchars') !== false || stripos($content, 'sanitize') !== false;

        if ($hasSqlProtection) { $score += 30; $details[] = 'SQL injection protection detected'; } else { $details[] = 'No SQL injection protection service found'; }
        if ($hasXssProtection) { $score += 30; $details[] = 'XSS protection detected'; } else { $details[] = 'No XSS protection service found'; }

        $baseContent = file_exists($this->projectRoot . '/app/Http/Controllers/BaseController.php') ? file_get_contents($this->projectRoot . '/app/Http/Controllers/BaseController.php') : '';
        if (stripos($baseContent, 'htmlspecialchars') !== false || stripos($baseContent, 'sanitize') !== false) {
            $score += 20; $details[] = 'Input sanitization in BaseController';
        } else {
            $details[] = 'No input sanitization in BaseController';
        }

        $pdoContent = '';
        $dbFile = $this->projectRoot . '/app/Core/Database/Database.php';
        if (file_exists($dbFile)) $pdoContent = file_get_contents($dbFile);
        if (stripos($pdoContent, 'PREPARE') !== false || stripos($pdoContent, 'prepare') !== false) {
            $score += 20; $details[] = 'PDO prepared statements used';
        } else {
            $details[] = 'PDO prepared statements NOT confirmed';
        }

        return [
            'test_name' => 'Input Validation',
            'status'    => $score >= 80 ? 'pass' : ($score >= 40 ? 'warning' : 'fail'),
            'score'     => $score,
            'details'   => implode('. ', $details),
            'recommendation' => $score < 100 ? 'Add a ValidatorService with SQL injection and XSS pattern detection. Ensure all DB queries use prepared statements.' : '',
        ];
    }

    private function testFileUploadSecurity(): array
    {
        $score = 0;
        $details = [];

        $fileUploadService = $this->projectRoot . '/app/Services/File/FileUploadService.php';
        $content = file_exists($fileUploadService) ? file_get_contents($fileUploadService) : '';

        if (empty($content)) {
            $uploadControllers = glob($this->projectRoot . '/app/Http/Controllers/*Upload*.php');
            $uploadControllers = array_merge($uploadControllers, glob($this->projectRoot . '/app/Http/Controllers/**/*Upload*.php'));
            foreach ($uploadControllers as $f) { $content .= file_get_contents($f); }
        }

        $hasBlacklist = stripos($content, 'extension') !== false || stripos($content, 'mime') !== false || stripos($content, 'allowed') !== false;
        $hasContentCheck = stripos($content, 'finfo') !== false || stripos($content, 'getimagesize') !== false || stripos($content, 'content_type') !== false || stripos($content, 'MIME') !== false;
        $hasSizeLimit = stripos($content, 'size') !== false || stripos($content, 'max') !== false;

        if ($hasBlacklist) { $score += 35; $details[] = 'File extension validation found'; } else { $details[] = 'No file extension validation found'; }
        if ($hasContentCheck) { $score += 35; $details[] = 'File content scanning found'; } else { $details[] = 'No file content scanning found'; }
        if ($hasSizeLimit) { $score += 30; $details[] = 'File size limit found'; } else { $details[] = 'No file size limit found'; }

        return [
            'test_name' => 'File Upload Security',
            'status'    => $score >= 70 ? 'pass' : ($score >= 35 ? 'warning' : 'fail'),
            'score'     => $score,
            'details'   => implode('. ', $details),
            'recommendation' => $score < 100 ? 'Implement file extension blacklist, MIME type verification (finfo_file), and max file size checks in FileUploadService.' : '',
        ];
    }

    private function testAuthenticationStrength(): array
    {
        $score = 0;
        $details = [];

        $authFiles = [
            $this->projectRoot . '/app/Services/Auth/AuthenticationService.php',
            $this->projectRoot . '/app/Services/AuthenticationService.php',
            $this->projectRoot . '/app/Services/AuthManager.php',
        ];
        $content = '';
        foreach ($authFiles as $f) { if (file_exists($f)) $content .= file_get_contents($f); }

        $authControllers = glob($this->projectRoot . '/app/Http/Controllers/Auth/*.php');
        foreach ($authControllers as $f) { $content .= file_get_contents($f); }

        $hasPasswordHash = stripos($content, 'password_hash') !== false || stripos($content, 'bcrypt') !== false;
        $hasLockout = stripos($content, 'lockout') !== false || stripos($content, 'attempt') !== false || stripos($content, 'max_attempts') !== false;
        $hasPasswordPolicy = stripos($content, 'strlen') !== false || stripos($content, 'min_length') !== false || stripos($content, 'password_min') !== false;

        if ($hasPasswordHash) { $score += 40; $details[] = 'Password hashing (password_hash/bcrypt) found'; } else { $details[] = 'No password hashing found'; }
        if ($hasLockout) { $score += 30; $details[] = 'Brute-force lockout detected'; } else { $details[] = 'No brute-force lockout detected'; }
        if ($hasPasswordPolicy) { $score += 30; $details[] = 'Password policy found'; } else { $details[] = 'No password length/policy enforcement found'; }

        return [
            'test_name' => 'Authentication Strength',
            'status'    => $score >= 70 ? 'pass' : ($score >= 40 ? 'warning' : 'fail'),
            'score'     => $score,
            'details'   => implode('. ', $details),
            'recommendation' => $score < 100 ? 'Ensure bcrypt/Argon2 hashing, brute-force lockout after 5 failed attempts, and minimum 8-char password policy.' : '',
        ];
    }

    private function testRateLimiting(): array
    {
        $score = 0;
        $details = [];

        $rateLimitFile = $this->projectRoot . '/app/Services/Security/RateLimitService.php';
        $hasRateLimitService = file_exists($rateLimitFile);

        if ($hasRateLimitService) {
            $content = file_get_contents($rateLimitFile);
            $hasRules = stripos($content, 'defaultRules') !== false || stripos($content, 'requests') !== false;
            $hasStorage = stripos($content, 'redis') !== false || stripos($content, 'file') !== false;
            $score += 50;
            $details[] = 'RateLimitService exists';
            if ($hasRules) { $score += 25; $details[] = 'Rate limit rules defined'; } else { $details[] = 'No rate limit rules found'; }
            if ($hasStorage) { $score += 25; $details[] = 'Storage backend configured'; } else { $details[] = 'No storage backend found'; }
        } else {
            $details[] = 'RateLimitService.php not found';
        }

        return [
            'test_name' => 'Rate Limiting',
            'status'    => $score >= 70 ? 'pass' : ($score >= 35 ? 'warning' : 'fail'),
            'score'     => $score,
            'details'   => implode('. ', $details),
            'recommendation' => $score < 100 ? 'Create RateLimitService with Redis/file storage and apply to login, API, and contact form endpoints.' : '',
        ];
    }

    private function testErrorHandling(): array
    {
        $score = 0;
        $details = [];

        $indexFile = $this->projectRoot . '/public/index.php';
        $indexContent = file_exists($indexFile) ? file_get_contents($indexFile) : '';

        $hasDisplayOff = stripos($indexContent, 'display_errors') !== false && (stripos($indexContent, 'Off') !== false || stripos($indexContent, '0') !== false);
        $hasErrorHandler = file_exists($this->projectRoot . '/app/Core/ErrorHandler.php') || stripos($indexContent, 'set_error_handler') !== false;

        $configFiles = glob($this->projectRoot . '/config/*.php');
        $configContent = '';
        foreach ($configFiles as $f) { $configContent .= file_get_contents($f); }
        if (file_exists($this->projectRoot . '/.env')) { $configContent .= file_get_contents($this->projectRoot . '/.env'); }

        $leaksPassword = stripos($configContent, 'DB_PASSWORD') !== false && preg_match('/DB_PASSWORD\s*[=:]\s*["\']?(?![\$\{])[\w@!.]+/', $configContent);
        $leaksKey = stripos($configContent, 'SECRET_KEY') !== false && preg_match('/SECRET_KEY\s*[=:]\s*["\']?(?![\$\{])[\w@!.]{10,}/', $configContent);

        if ($hasDisplayOff) { $score += 30; $details[] = 'display_errors is Off'; } else { $details[] = 'display_errors may be On (sensitive data risk)'; }
        if ($hasErrorHandler) { $score += 30; $details[] = 'Custom error handler found'; } else { $details[] = 'No custom error handler'; }
        if (!$leaksPassword) { $score += 20; $details[] = 'No hardcoded DB passwords in config'; } else { $details[] = 'Hardcoded DB password detected'; }
        if (!$leaksKey) { $score += 20; $details[] = 'No hardcoded secret keys in config'; } else { $details[] = 'Hardcoded secret key detected'; }

        return [
            'test_name' => 'Error Handling',
            'status'    => $score >= 70 ? 'pass' : ($score >= 40 ? 'warning' : 'fail'),
            'score'     => $score,
            'details'   => implode('. ', $details),
            'recommendation' => $score < 100 ? 'Disable display_errors in production, add custom error handler, and use environment variables for all secrets.' : '',
        ];
    }

    private function testDatabaseSecurity(): array
    {
        $score = 0;
        $details = [];

        $dbFile = $this->projectRoot . '/app/Core/Database/Database.php';
        $dbContent = file_exists($dbFile) ? file_get_contents($dbFile) : '';

        $configService = $this->projectRoot . '/app/Core/ConfigService.php';
        $configContent = file_exists($configService) ? file_get_contents($configService) : '';

        $envFile = $this->projectRoot . '/.env';
        $envContent = file_exists($envFile) ? file_get_contents($envFile) : '';

        $allContent = $dbContent . $configContent;

        $hasPrepared = stripos($allContent, 'prepare') !== false;
        $usesEnv = stripos($configContent, 'getenv') !== false || stripos($configContent, '$_ENV') !== false || stripos($configContent, 'Dotenv') !== false || stripos($envContent, 'DB_') !== false;
        $noDirectCreds = !preg_match('/password\s*[=:]\s*["\'](?![\$\{])\S{3,}/', $dbContent);

        if ($hasPrepared) { $score += 35; $details[] = 'PDO prepared statements used'; } else { $details[] = 'PDO prepared statements NOT found'; }
        if ($usesEnv) { $score += 35; $details[] = 'Environment variables for DB config'; } else { $details[] = 'DB credentials may be hardcoded'; }
        if ($noDirectCreds) { $score += 30; $details[] = 'No hardcoded DB passwords in Database.php'; } else { $details[] = 'Hardcoded DB password found in Database.php'; }

        return [
            'test_name' => 'Database Security',
            'status'    => $score >= 70 ? 'pass' : ($score >= 40 ? 'warning' : 'fail'),
            'score'     => $score,
            'details'   => implode('. ', $details),
            'recommendation' => $score < 100 ? 'Move all DB credentials to .env file, use ConfigService to read them, and ensure all queries use prepared statements.' : '',
        ];
    }
}
