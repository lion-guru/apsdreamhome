<?php
// Comprehensive Login Error Handling - Updated with Session Management

class LoginErrorHandler {
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_DURATION = 1800; // 30 minutes

    public static function handle($exception, $username = "") {
        // Ensure session is started with new helpers
        require_once __DIR__ . "/core/init.php";

        // Log error details
        self::logError($exception, $username);

        // Track login attempts
        self::trackAttempts($username);

        // Secure redirect
        self::secureRedirect();
    }

    private static function logError($exception, $username) {
        $errorLog = [
            "timestamp" => date("Y-m-d H:i:s"),
            "error_type" => get_class($exception),
            "error_message" => $exception->getMessage(),
            "username" => $username,
            "ip_address" => $_SERVER["REMOTE_ADDR"] ?? "Unknown"
        ];

        error_log(json_encode($errorLog, JSON_PRETTY_PRINT));
    }

    private static function trackAttempts($username) {
        $_SESSION["login_attempts"] = ($_SESSION["login_attempts"] ?? 0) + 1;

        if ($_SESSION["login_attempts"] >= self::MAX_ATTEMPTS) {
            $_SESSION["login_locked_until"] = time() + self::LOCKOUT_DURATION;
            $_SESSION["login_error"] = "Too many failed attempts. Account locked.";
        } else {
            $_SESSION["login_error"] = "Invalid login credentials.";
        }
    }

    private static function secureRedirect() {
        // Implement secure redirect logic
        header("Location: login.php");
        exit();
    }
}
