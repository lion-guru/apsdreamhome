<?php
/**
 * Language Helper
 * Provides simple translation functions
 */

if (!function_exists('__')) {
    /**
     * Translate a string
     * 
     * @param string $key
     * @param array $replace
     * @return string
     */
    function __($key, $replace = [])
    {
        // Get language from session, cookie, or default to English
        $lang = $_SESSION['lang'] ?? ($_COOKIE['lang'] ?? 'en');
        
        // Load language file
        $file = APP_PATH . "/lang/{$lang}.php";
        if (!file_exists($file)) {
            // Fallback to English
            $file = APP_PATH . '/lang/en.php';
            if (!file_exists($file)) {
                // Last resort: return the key
                return $key;
            }
        }
        
        $translations = include $file;
        
        // Get translation
        $translation = $translations[$key] ?? $key;
        
        // Replace placeholders
        foreach ($replace as $placeholder => $value) {
            $translation = str_replace("{{{$placeholder}}}", $value, $translation);
        }
        
        return $translation;
    }
}

if (!function_exists('set_lang')) {
    /**
     * Set language for current user
     * 
     * @param string $lang
     * @return void
     */
    function set_lang($lang)
    {
        // Validate language
        $allowed = ['en', 'hi']; // Add more as needed
        if (in_array($lang, $allowed)) {
            $_SESSION['lang'] = $lang;
            // Set cookie for 1 year
            setcookie('lang', $lang, [
                'expires' => time() + 31536000,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
    }
}

if (!function_exists('get_lang')) {
    /**
     * Get current language
     * 
     * @return string
     */
    function get_lang()
    {
        return $_SESSION['lang'] ?? ($_COOKIE['lang'] ?? 'en');
    }
}

if (!function_exists('lang_switcher')) {
    /**
     * Generate language switcher HTML
     * 
     * @return string
     */
    function lang_switcher()
    {
        $current = get_lang();
        $langs = [
            'en' => 'English',
            'hi' => 'हिंदी',
        ];
        
        $options = '';
        foreach ($langs as $code => $name) {
            $selected = ($code === $current) ? 'selected' : '';
            $options .= "<option value=\"{$code}\" {$selected}>{$name}</option>";
        }
        
        return <<<HTML
<select onchange="location.href = '{$_SERVER['PHP_SELF']}?lang=' + this.value">
    {$options}
</select>
HTML;
    }
}