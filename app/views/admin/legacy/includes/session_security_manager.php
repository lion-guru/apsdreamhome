<?php
/**
 * Session Security Manager
 * Provides comprehensive session security enhancements for the admin panel
 */

class SessionSecurityManager {
    private $sessionName;
    private $sessionLifetime;
    private $regenerateInterval;
    private $maxConcurrentSessions;
    private $ipBinding;
    private $userAgentBinding;
    private $securityLogFile;

    public function __construct() {
        $this->sessionName = 'secure_admin_session_' . substr(md5(__DIR__), 0, 8);
        $this->sessionLifetime = 1800; // 30 minutes
        $this->regenerateInterval = 300; // 5 minutes
        $this->maxConcurrentSessions = 3;
        $this->ipBinding = true;
        $this->userAgentBinding = true;
        $this->securityLogFile = __DIR__ . '/../logs/session_security.log';

        $this->initializeSecurityLogging();
    }

    /**
     * Initialize security logging
     */
    private function initializeSecurityLogging() {
        $logDir = dirname($this->securityLogFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    /**
     * Log security events
     */
    public function logSecurityEvent($event, $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $ip = $this->getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $sessionId = session_id();

        $contextStr = '';
        if (!empty($context)) {
            foreach ($context as $key => $value) {
                $contextStr .= " | $key: " . (is_array($value) || is_object($value) ? json_encode($value) : $value);
            }
        }

        $logMessage = "[{$timestamp}] [{$ip}] [{$sessionId}] {$event}{$contextStr}\n";

        // Write to log file
        file_put_contents($this->securityLogFile, $logMessage, FILE_APPEND | LOCK_EX);

        // Also log to PHP error log for critical events
        if (strpos($event, 'CRITICAL') !== false || strpos($event, 'HIJACKING') !== false) {
            error_log("SESSION SECURITY: {$event} - IP: {$ip} - Session: {$sessionId}{$contextStr}");
        }
    }

    /**
     * Get client IP address with proxy detection
     */
    private function getClientIP() {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return '0.0.0.0';
    }

    /**
     * Initialize secure session
     */
    public function initializeSecureSession() {
        if (session_status() === PHP_SESSION_NONE) {
            // Set secure session cookie parameters
            $cookieParams = [
                'lifetime' => $this->sessionLifetime,
                'path' => '/admin',
                'domain' => $this->getSessionDomain(),
                'secure' => $this->isHTTPS(),
                'httponly' => true,
                'samesite' => 'Strict'
            ];

            require_once __DIR__ . '/../../../../includes/session_helpers.php';
            ensureSessionStarted();

            // Initialize session security data
            $this->initializeSessionSecurity();

            $this->logSecurityEvent('Session Initialized', [
                'session_id' => session_id(),
                'ip' => $this->getClientIP(),
                'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 50)
            ]);
        }
    }

    /**
     * Get session domain
     */
    private function getSessionDomain() {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        // Remove port if present
        $host = explode(':', $host)[0];
        return $host;
    }

    /**
     * Check if HTTPS is being used
     */
    private function isHTTPS() {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
               (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ||
               (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }

    /**
     * Initialize session security data
     */
    private function initializeSessionSecurity() {
        if (!isset($_SESSION['_session_security'])) {
            $_SESSION['_session_security'] = [
                'created_at' => time(),
                'last_activity' => time(),
                'last_regeneration' => time(),
                'ip_address' => $this->getClientIP(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                'request_count' => 0,
                'failed_attempts' => 0,
                'security_level' => 'high'
            ];
        }
    }

    /**
     * Validate session security
     */
    public function validateSessionSecurity() {
        if (!isset($_SESSION['_session_security'])) {
            $this->logSecurityEvent('CRITICAL: Missing session security data');
            return false;
        }

        $security = $_SESSION['_session_security'];
        $currentTime = time();
        $currentIP = $this->getClientIP();
        $currentUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        // Check session lifetime
        if (($currentTime - $security['created_at']) > $this->sessionLifetime) {
            $this->logSecurityEvent('Session Expired', [
                'created_at' => $security['created_at'],
                'current_time' => $currentTime,
                'lifetime' => $this->sessionLifetime
            ]);
            return false;
        }

        // Check idle timeout
        if (($currentTime - $security['last_activity']) > $this->sessionLifetime) {
            $this->logSecurityEvent('Session Idle Timeout', [
                'last_activity' => $security['last_activity'],
                'current_time' => $currentTime
            ]);
            return false;
        }

        // IP address validation
        if ($this->ipBinding && $security['ip_address'] !== $currentIP) {
            $this->logSecurityEvent('HIJACKING: IP Address Mismatch', [
                'session_ip' => $security['ip_address'],
                'current_ip' => $currentIP
            ]);
            return false;
        }

        // User agent validation
        if ($this->userAgentBinding && $security['user_agent'] !== $currentUserAgent) {
            $this->logSecurityEvent('HIJACKING: User Agent Mismatch', [
                'session_user_agent' => substr($security['user_agent'], 0, 50),
                'current_user_agent' => substr($currentUserAgent, 0, 50)
            ]);
            return false;
        }

        // Check for too many failed attempts
        if ($security['failed_attempts'] >= 5) {
            $this->logSecurityEvent('CRITICAL: Too Many Failed Attempts', [
                'failed_attempts' => $security['failed_attempts']
            ]);
            return false;
        }

        return true;
    }

    /**
     * Update session activity
     */
    public function updateSessionActivity() {
        if (isset($_SESSION['_session_security'])) {
            $_SESSION['_session_security']['last_activity'] = time();
            $_SESSION['_session_security']['request_count']++;
        }
    }

    /**
     * Regenerate session ID with security checks
     */
    public function regenerateSessionID() {
        $currentTime = time();
        $security = $_SESSION['_session_security'] ?? [];

        // Check if enough time has passed since last regeneration
        if (($currentTime - ($security['last_regeneration'] ?? 0)) >= $this->regenerateInterval) {
            $oldSessionId = session_id();
            session_regenerate_id(true);
            $newSessionId = session_id();

            if (isset($_SESSION['_session_security'])) {
                $_SESSION['_session_security']['last_regeneration'] = $currentTime;
            }

            $this->logSecurityEvent('Session ID Regenerated', [
                'old_session_id' => substr($oldSessionId, 0, 8) . '...',
                'new_session_id' => substr($newSessionId, 0, 8) . '...'
            ]);

            return true;
        }

        return false;
    }

    /**
     * Record failed attempt
     */
    public function recordFailedAttempt() {
        if (isset($_SESSION['_session_security'])) {
            $_SESSION['_session_security']['failed_attempts']++;

            $this->logSecurityEvent('Failed Attempt Recorded', [
                'failed_attempts' => $_SESSION['_session_security']['failed_attempts']
            ]);
        }
    }

    /**
     * Reset failed attempts
     */
    public function resetFailedAttempts() {
        if (isset($_SESSION['_session_security'])) {
            $_SESSION['_session_security']['failed_attempts'] = 0;
        }
    }

    /**
     * Get session security info
     */
    public function getSessionSecurityInfo() {
        return $_SESSION['_session_security'] ?? [];
    }

    /**
     * Check if session is secure
     */
    public function isSessionSecure() {
        return $this->validateSessionSecurity();
    }

    /**
     * Destroy session securely
     */
    public function destroySession() {
        $this->logSecurityEvent('Session Destroyed', [
            'session_id' => session_id(),
            'reason' => 'User logout or security violation'
        ]);

        // Unset all session variables
        $_SESSION = [];

        // Destroy the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Destroy the session
        session_destroy();
    }

    /**
     * Get session statistics
     */
    public function getSessionStatistics() {
        $security = $_SESSION['_session_security'] ?? [];

        return [
            'session_id' => substr(session_id(), 0, 8) . '...',
            'created_at' => isset($security['created_at']) ? date('Y-m-d H:i:s', $security['created_at']) : 'Unknown',
            'last_activity' => isset($security['last_activity']) ? date('Y-m-d H:i:s', $security['last_activity']) : 'Unknown',
            'request_count' => $security['request_count'] ?? 0,
            'failed_attempts' => $security['failed_attempts'] ?? 0,
            'ip_address' => $security['ip_address'] ?? 'Unknown',
            'security_level' => $security['security_level'] ?? 'Unknown',
            'time_until_expiry' => isset($security['last_activity']) ?
                max(0, $this->sessionLifetime - (time() - $security['last_activity'])) : 0
        ];
    }

    /**
     * Check for session anomalies
     */
    public function checkSessionAnomalies() {
        $anomalies = [];
        $security = $_SESSION['_session_security'] ?? [];

        // Check for rapid request pattern (potential bot)
        if ($security['request_count'] > 100 &&
            (time() - $security['created_at']) < 60) {
            $anomalies[] = 'Rapid request pattern detected';
        }

        // Check for failed attempt pattern
        if ($security['failed_attempts'] > 3) {
            $anomalies[] = 'Multiple failed attempts detected';
        }

        // Check for unusual activity pattern
        if ($security['request_count'] > 1000) {
            $anomalies[] = 'Unusually high request count';
        }

        if (!empty($anomalies)) {
            $this->logSecurityEvent('Session Anomalies Detected', [
                'anomalies' => $anomalies,
                'request_count' => $security['request_count'],
                'failed_attempts' => $security['failed_attempts']
            ]);
        }

        return $anomalies;
    }

    /**
     * Get security recommendations
     */
    public function getSecurityRecommendations() {
        $recommendations = [];
        $security = $_SESSION['_session_security'] ?? [];

        if ($security['failed_attempts'] > 2) {
            $recommendations[] = 'Consider implementing CAPTCHA for this session';
        }

        if ($security['request_count'] > 500) {
            $recommendations[] = 'High activity detected - monitor for suspicious behavior';
        }

        if (!$this->isHTTPS()) {
            $recommendations[] = 'Enable HTTPS for enhanced session security';
        }

        return $recommendations;
    }
}

/**
 * Global session security initialization function
 */
function initializeSecureAdminSession() {
    $sessionManager = new SessionSecurityManager();
    $sessionManager->initializeSecureSession();

    // Validate session security
    if (!$sessionManager->validateSessionSecurity()) {
        $sessionManager->destroySession();
        header('Location: /admin/index.php?error=session_security_violation');
        exit();
    }

    // Update session activity
    $sessionManager->updateSessionActivity();

    // Regenerate session ID periodically
    $sessionManager->regenerateSessionID();

    // Check for anomalies
    $anomalies = $sessionManager->checkSessionAnomalies();
    if (!empty($anomalies)) {
        // Log anomalies but don't destroy session immediately
        // This allows for monitoring without disrupting legitimate users
    }

    return $sessionManager;
}

/**
 * Check if current session is secure
 */
function isAdminSessionSecure() {
    if (isset($_SESSION['_session_security'])) {
        $sessionManager = new SessionSecurityManager();
        return $sessionManager->isSessionSecure();
    }
    return false;
}

/**
 * Get current session security info
 */
function getAdminSessionSecurityInfo() {
    $sessionManager = new SessionSecurityManager();
    return $sessionManager->getSessionSecurityInfo();
}

/**
 * Destroy admin session securely
 */
function destroyAdminSession() {
    $sessionManager = new SessionSecurityManager();
    $sessionManager->destroySession();
}
