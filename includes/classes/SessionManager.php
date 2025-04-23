<?php
class SessionManager {
    private const SESSION_TIMEOUT = 1800; // 30 minutes
    private const USER_TYPES = ['user', 'associate', 'agent', 'builder', 'admin', 'super_admin'];

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            $this->initializeSession();
        }
    }

    private function initializeSession(): void {
        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_secure', '1');
        session_start();
    }

    public function login(array $userData): bool {
        if (!isset($userData['uid']) || !isset($userData['utype'])) {
            return false;
        }

        $_SESSION['uid'] = $userData['uid'];
        $_SESSION['user'] = $userData['name'];
        $_SESSION['email'] = $userData['email'];
        $_SESSION['utype'] = $userData['utype'];
        $_SESSION['last_activity'] = time();

        session_regenerate_id(true);
        return true;
    }

    public function logout(): void {
        session_unset();
        session_destroy();
        session_start();
        session_regenerate_id(true);
    }

    public function isLoggedIn(): bool {
        return isset($_SESSION['uid']) && $this->checkSessionTimeout();
    }

    public function getUserType(): ?string {
        return $_SESSION['utype'] ?? null;
    }

    public function getCurrentUserId(): ?string {
        return $_SESSION['uid'] ?? null;
    }

    private function checkSessionTimeout(): bool {
        if (!isset($_SESSION['last_activity'])) {
            return false;
        }

        if (time() - $_SESSION['last_activity'] > self::SESSION_TIMEOUT) {
            $this->logout();
            return false;
        }

        $_SESSION['last_activity'] = time();
        return true;
    }

    public function isValidUserType(string $userType): bool {
        return in_array(strtolower($userType), self::USER_TYPES, true);
    }

    public function requireLogin(): void {
        if (!$this->isLoggedIn()) {
            header('Location: /march2025apssite/login.php');
            exit();
        }
    }

    public function requireAdmin(): void {
        $type = $this->getUserType();
        if (!$this->isLoggedIn() || ($type !== 'admin' && $type !== 'super_admin')) {
            header('Location: /march2025apssite/login.php');
            exit();
        }
    }

    public function requireSuperAdmin(): void {
        if (!$this->isLoggedIn() || $this->getUserType() !== 'super_admin') {
            header('Location: /march2025apssite/login.php');
            exit();
        }
    }
}