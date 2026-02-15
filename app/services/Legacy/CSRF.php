<?php

namespace App\Services\Legacy;
class CSRFProtection {
    public static function generateToken($form = 'default') {
        require_once __DIR__ . '/session_helpers.php';
        ensureSessionStarted();
        $token = bin2hex(\App\Helpers\SecurityHelper::secureRandomBytes(32));
        $_SESSION['csrf_token_' . $form] = $token;
        return $token;
    }
    public static function validateToken($token, $form = 'default') {
        require_once __DIR__ . '/session_helpers.php';
        ensureSessionStarted();
        if (isset($_SESSION['csrf_token_' . $form]) && hash_equals($_SESSION['csrf_token_' . $form], $token)) {
            unset($_SESSION['csrf_token_' . $form]);
            return true;
        }
        return false;
    }
}
