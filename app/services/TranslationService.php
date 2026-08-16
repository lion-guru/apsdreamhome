<?php
namespace App\Services;

/**
 * Translation Service
 * Loads language files, supports nested keys, parameters, pluralization,
 * fallback to English, and in-memory caching.
 */
class TranslationService
{
    private static $instance = null;

    /** @var array<string,array<string,mixed>> */
    private $cache = [];

    /** @var string */
    private $currentLang = 'en';

    /** @var array<int,string> */
    private $available = ['en', 'hi'];

    /** @var string */
    private $langDir;

    public function __construct()
    {
        $this->langDir = defined('APP_ROOT')
            ? APP_ROOT . '/lang'
            : dirname(dirname(__DIR__)) . '/lang';

        if (session_status() === PHP_SESSION_NONE && php_sapi_name() !== 'cli') {
            @session_start();
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Detect current language using URL ?lang=, session, cookie, browser header.
     */
    public function detectLanguage(): string
    {
        if (php_sapi_name() === 'cli') {
            return 'en';
        }

        // 1. URL param ?lang=hi (highest priority)
        if (!empty($_GET['lang']) && $this->isAvailable($_GET['lang'])) {
            $this->setLanguage($_GET['lang']);
            return $this->currentLang;
        }

        // 2. URL path /hi/ or /language/set/hi
        $path = $_SERVER['REQUEST_URI'] ?? '';
        if (preg_match('#/language/set/([a-z]{2})#i', $path, $m) && $this->isAvailable($m[1])) {
            $this->setLanguage($m[1]);
            return $this->currentLang;
        }

        // 3. Session
        if (!empty($_SESSION['user_language']) && $this->isAvailable($_SESSION['user_language'])) {
            $this->currentLang = $_SESSION['user_language'];
            return $this->currentLang;
        }

        // 4. Cookie
        if (!empty($_COOKIE['user_language']) && $this->isAvailable($_COOKIE['user_language'])) {
            $this->currentLang = $_COOKIE['user_language'];
            $_SESSION['user_language'] = $this->currentLang;
            return $this->currentLang;
        }

        // 5. Browser Accept-Language header
        if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $browser = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 5));
            if (strpos($browser, 'hi') === 0 && $this->isAvailable('hi')) {
                $this->currentLang = 'hi';
            } else {
                $this->currentLang = 'en';
            }
        }

        return $this->currentLang;
    }

    public function getCurrentLanguage(): string
    {
        if (empty($this->currentLang)) {
            $this->detectLanguage();
        }
        return $this->currentLang;
    }

    public function setLanguage(string $lang): bool
    {
        if (!$this->isAvailable($lang)) {
            return false;
        }
        $this->currentLang = $lang;
        if (php_sapi_name() !== 'cli') {
            $_SESSION['user_language'] = $lang;
            if (!headers_sent()) {
                setcookie('user_language', $lang, [
                    'expires' => time() + 86400 * 30,
                    'path' => '/',
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
            }
            $this->cache = []; // clear cache for safety
        }
        return true;
    }

    public function isAvailable(string $lang): bool
    {
        return in_array($lang, $this->available, true);
    }

    public function getAvailable(): array
    {
        return $this->available;
    }

    /**
     * Get a translation.
     *
     * @param string $key       Flat ("home") or nested ("nav.menu.home") key.
     * @param array  $params    Placeholder substitutions: ['name' => 'Rajesh'].
     * @param string|null $lang Optional language override.
     * @return string
     */
    public function get(string $key, array $params = [], ?string $lang = null): string
    {
        $lang = $lang ?: $this->getCurrentLanguage();
        $value = $this->lookup($key, $lang);

        if ($value === null && $lang !== 'en') {
            $value = $this->lookup($key, 'en');
        }

        if ($value === null) {
            $value = $key; // Fallback to key
        }

        if (!empty($params)) {
            $value = $this->replaceParams($value, $params);
        }

        return (string) $value;
    }

    /**
     * Pluralization helper: picks singular/plural form based on $count.
     * Pattern: "item" => "1 item|2 items" — use | to separate singular and plural.
     */
    public function choice(string $key, int $count, array $params = [], ?string $lang = null): string
    {
        $value = $this->get($key, $params, $lang);
        if (strpos($value, '|') !== false) {
            [$singular, $plural] = explode('|', $value, 2);
            $value = ($count === 1) ? $singular : $plural;
        }
        $params['count'] = $count;
        return $this->replaceParams($value, $params);
    }

    /**
     * Format a date in the current language's locale.
     */
    public function formatDate(string $date, string $format = 'd M Y'): string
    {
        $ts = is_numeric($date) ? (int) $date : strtotime($date);
        if ($ts === false) {
            return $date;
        }
        $months = [
            'en' => ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
            'hi' => ['जन','फर','मार्च','अप्रैल','मई','जून','जुल','अग','सित','अक्टू','नव','दिस'],
        ];
        $lang = $this->getCurrentLanguage();
        $m = $months[$lang] ?? $months['en'];
        $out = $format;
        $out = str_replace('M', $m[(int) date('n', $ts) - 1], $out);
        return date(str_replace(['d','Y','m'], [date('d', $ts), date('Y', $ts), date('m', $ts)], $out), $ts);
    }

    /**
     * Internal lookup supporting nested keys (dot notation) and flat keys.
     */
    private function lookup(string $key, string $lang): ?string
    {
        $translations = $this->load($lang);
        if (isset($translations[$key])) {
            $val = $translations[$key];
            return is_string($val) ? $val : null;
        }
        // nested
        $parts = explode('.', $key);
        $cursor = $translations;
        foreach ($parts as $p) {
            if (!is_array($cursor) || !array_key_exists($p, $cursor)) {
                return null;
            }
            $cursor = $cursor[$p];
        }
        return is_string($cursor) ? $cursor : null;
    }

    private function load(string $lang): array
    {
        if (isset($this->cache[$lang])) {
            return $this->cache[$lang];
        }
        $file = rtrim($this->langDir, '/\\') . DIRECTORY_SEPARATOR . $lang . '.php';
        if (!file_exists($file)) {
            $this->cache[$lang] = [];
            return [];
        }
        $data = require $file;
        if (!is_array($data)) {
            $data = [];
        }
        $this->cache[$lang] = $data;
        return $data;
    }

    private function replaceParams(string $value, array $params): string
    {
        $keys = array_map(function ($k) { return '{' . $k . '}'; }, array_keys($params));
        return strtr($value, array_combine($keys, array_values($params)));
    }

    /**
     * Return all loaded keys (for diagnostics).
     */
    public function all(?string $lang = null): array
    {
        return $this->load($lang ?: $this->getCurrentLanguage());
    }
}
