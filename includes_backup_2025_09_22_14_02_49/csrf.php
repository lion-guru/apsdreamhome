<?php
class CSRFProtection {
    public static function generateToken($form = 'default') {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_' . $form] = $token;
        return $token;
    }
    public static function validateToken($token, $form = 'default') {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (isset($_SESSION['csrf_token_' . $form]) && hash_equals($_SESSION['csrf_token_' . $form], $token)) {
            unset($_SESSION['csrf_token_' . $form]);
            return true;
        }
        return false;
    }
}
