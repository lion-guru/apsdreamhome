<?php
function __($key, $default = null) {
    static $translations = null;
    static $currentLang = null;
    
    $lang = $_SESSION['user_language'] ?? $_COOKIE['user_language'] ?? 'en';
    
    if ($translations === null || $currentLang !== $lang) {
        $currentLang = $lang;
        $file = __DIR__ . '/../views/languages/' . $lang . '.php';
        if (file_exists($file)) {
            $translations = require $file;
        } else {
            $translations = [];
        }
    }
    
    return $translations[$key] ?? $default ?? $key;
}
