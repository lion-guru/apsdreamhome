<?php
/**
 * Translation Helper
 *
 * Global function __($key, $params = [], $default = null) for translations.
 * Backward compatible with the original __($key, $default) signature.
 *
 * Features:
 *  - Loads from /lang/{lang}.php
 *  - Auto-detects language from URL, session, cookie, browser header
 *  - Supports nested keys (e.g., "nav.menu.home")
 *  - Supports {placeholder} parameter substitution
 *  - Falls back to English when a key is missing in the current language
 *  - In-memory caching
 *  - Pluralization via trans_choice() and choice()
 */

require_once __DIR__ . '/../Services/TranslationService.php';

if (!function_exists('__')) {
    /**
     * Translate a key.
     *
     * @param string $key
     * @param array|string $params  Array of placeholders OR a default value (legacy)
     * @param string|null $default  Default value if key is missing
     * @return string
     */
    function __($key, $params = [], $default = null)
    {
        // Legacy support: __($key, $defaultValue)
        if (is_string($params) && $default === null) {
            $default = $params;
            $params = [];
        }

        static $svc = null;
        if ($svc === null) {
            $svc = \App\Services\TranslationService::getInstance();
            $svc->detectLanguage();
        }

        $value = $svc->get($key, is_array($params) ? $params : [], null);

        if ($value === $key && $default !== null && $default !== '') {
            return $default;
        }
        return $value;
    }
}

if (!function_exists('trans_choice')) {
    /**
     * Pick singular or plural form of a translation based on $count.
     * Source value pattern: "1 item|2 items" — {count} placeholder is auto-set.
     */
    function trans_choice(string $key, int $count, array $params = []): string
    {
        static $svc = null;
        if ($svc === null) {
            $svc = \App\Services\TranslationService::getInstance();
            $svc->detectLanguage();
        }
        return $svc->choice($key, $count, $params);
    }
}

if (!function_exists('__choice')) {
    function __choice(string $key, int $count, array $params = []): string
    {
        return trans_choice($key, $count, $params);
    }
}

if (!function_exists('__current_lang')) {
    function __current_lang(): string
    {
        static $svc = null;
        if ($svc === null) {
            $svc = \App\Services\TranslationService::getInstance();
        }
        return $svc->getCurrentLanguage();
    }
}

if (!function_exists('__set_lang')) {
    function __set_lang(string $lang): bool
    {
        static $svc = null;
        if ($svc === null) {
            $svc = \App\Services\TranslationService::getInstance();
        }
        return $svc->setLanguage($lang);
    }
}

if (!function_exists('__date')) {
    function __date(string $date, string $format = 'd M Y'): string
    {
        static $svc = null;
        if ($svc === null) {
            $svc = \App\Services\TranslationService::getInstance();
        }
        return $svc->formatDate($date, $format);
    }
}

if (!function_exists('render_language_switcher')) {
    /**
     * Render a Bootstrap-styled language switcher dropdown.
     *
     * @param string $variant  "dropdown" (default) or "inline" or "footer"
     * @return string HTML
     */
    function render_language_switcher(string $variant = 'dropdown'): string
    {
        $svc = \App\Services\TranslationService::getInstance();
        $current = $svc->getCurrentLanguage();
        $base = defined('BASE_URL') ? BASE_URL : '';
        $available = [
            'en' => ['name' => 'English',   'flag' => '🇬🇧', 'native' => 'English'],
            'hi' => ['name' => 'Hindi',     'flag' => '🇮🇳', 'native' => 'हिन्दी'],
        ];

        $items = '';
        foreach ($available as $code => $info) {
            $active = ($code === $current) ? ' active' : '';
            $items .= '<li><a class="dropdown-item' . $active . '" href="' . htmlspecialchars($base) . '/language/set/' . $code . '">'
                    . '<span class="me-2">' . $info['flag'] . '</span> ' . htmlspecialchars($info['native'])
                    . '</a></li>';
        }

        if ($variant === 'footer') {
            $cur = $available[$current] ?? $available['en'];
            $html = '<div class="language-switcher-footer">'
                  . '<div class="dropdown">'
                  . '<button class="btn btn-outline-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">'
                  . '<i class="fas fa-globe me-1"></i> ' . htmlspecialchars($cur['native'])
                  . '</button>'
                  . '<ul class="dropdown-menu dropdown-menu-end">' . $items . '</ul>'
                  . '</div></div>';
            return $html;
        }

        if ($variant === 'inline') {
            $html = '<ul class="lang-inline list-inline mb-0">';
            foreach ($available as $code => $info) {
                $active = ($code === $current) ? ' active text-primary fw-bold' : '';
                $html .= '<li class="list-inline-item' . $active . '">'
                       . '<a class="text-decoration-none" href="' . htmlspecialchars($base) . '/language/set/' . $code . '">'
                       . $info['flag'] . ' ' . htmlspecialchars($info['native'])
                       . '</a></li>';
            }
            $html .= '</ul>';
            return $html;
        }

        // default: dropdown
        $cur = $available[$current] ?? $available['en'];
        $html = '<li class="nav-item dropdown">'
              . '<a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" role="button" aria-expanded="false">'
              . '<i class="fas fa-globe me-1"></i> ' . strtoupper($current)
              . '</a>'
              . '<ul class="dropdown-menu dropdown-menu-end">' . $items . '</ul>'
              . '</li>';
        return $html;
    }
}
