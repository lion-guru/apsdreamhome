<?php
// Comprehensive Login Error Handling

class LoginErrorHandler {
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_DURATION = 1800; // 30 minutes

    public static function handle($exception, $connection = null, $username = "") {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Log error details
        self::logError($exception, $username);

        // Track login attempts
        self::trackAttempts($username);

        // Close database connection if open
        if ($connection && method_exists($connection, "close")) {
            $connection->close();
        }

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
        header("X-Login-Status: Failed");
        header("X-Attempt-Count: " . ($_SESSION["login_attempts"] ?? 1));
        header("Location: login.php");
        exit();
    }

    public static function checkLoginLock() {
        if (isset($_SESSION["login_locked_until"]) && time() < $_SESSION["login_locked_until"]) {
            $_SESSION["login_error"] = "Account temporarily locked.";
            self::secureRedirect();
        }
    }

    public static function resetAttempts() {
        unset($_SESSION["login_attempts"]);
        unset($_SESSION["login_locked_until"]);
    }
}

// Backwards compatibility
function handleLoginError($e, $con = null, $username = "") {
    LoginErrorHandler::handle($e, $con, $username);
}

return true;
