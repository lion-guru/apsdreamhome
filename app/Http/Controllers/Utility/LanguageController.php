<?php

/**
 * Multi-Language Support Controller
 * Handles internationalization and localization
 */

namespace App\Http\Controllers\Utility;

use App\Http\Controllers\BaseController;
use App\Core\Security;
use Exception;

class LanguageController extends BaseController
{
    private $supported_languages = [
        'en' => [
            'name' => 'English',
            'native_name' => 'English',
            'flag' => '🇺🇸',
            'rtl' => false
        ],
        'hi' => [
            'name' => 'Hindi',
            'native_name' => 'हिन्दी',
            'flag' => '🇮🇳',
            'rtl' => false
        ],
        'es' => [
            'name' => 'Spanish',
            'native_name' => 'Español',
            'flag' => '🇪🇸',
            'rtl' => false
        ],
        'fr' => [
            'name' => 'French',
            'native_name' => 'Français',
            'flag' => '🇫🇷',
            'rtl' => false
        ],
        'de' => [
            'name' => 'German',
            'native_name' => 'Deutsch',
            'flag' => '🇩🇪',
            'rtl' => false
        ],
        'zh' => [
            'name' => 'Chinese',
            'native_name' => '中文',
            'flag' => '🇨🇳',
            'rtl' => false
        ],
        'ar' => [
            'name' => 'Arabic',
            'native_name' => 'العربية',
            'flag' => '🇸🇦',
            'rtl' => true
        ],
        'pt' => [
            'name' => 'Portuguese',
            'native_name' => 'Português',
            'flag' => '🇵🇹',
            'rtl' => false
        ],
        'ru' => [
            'name' => 'Russian',
            'native_name' => 'Русский',
            'flag' => '🇷🇺',
            'rtl' => false
        ],
        'ja' => [
            'name' => 'Japanese',
            'native_name' => '日本語',
            'flag' => '🇯🇵',
            'rtl' => false
        ]
    ];

    private $default_language = 'en';

    /**
     * Set user language preference
     */
    public function setLanguage()
    {
        $lang_code = $_GET['lang'] ?? (isset($_POST['lang']) ? Security::sanitize($_POST['lang']) : '');

        if (empty($lang_code) || !isset($this->supported_languages[$lang_code])) {
            $lang_code = $this->detectLanguage();
        }

        // Set language in session
        $_SESSION['user_language'] = $lang_code;

        // Set language cookie (30 days)
        setcookie('user_language', $lang_code, time() + (30 * 24 * 60 * 60), '/');

        // Redirect back to referring page or home
        $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL;
        header('Location: ' . $referer);
        exit;
    }

    /**
     * Get current language
     */
    public function getCurrentLanguage()
    {
        return $_SESSION['user_language'] ??
            $_COOKIE['user_language'] ??
            $this->detectLanguage();
    }

    /**
     * Auto-detect user language from browser
     */
    private function detectLanguage()
    {
        $accept_language = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';

        if (empty($accept_language)) {
            return $this->default_language;
        }

        // Parse Accept-Language header
        $languages = [];
        foreach (explode(',', $accept_language) as $lang) {
            $parts = explode(';', $lang);
            $lang_code = trim($parts[0]);

            // Check for exact match first
            if (isset($this->supported_languages[$lang_code])) {
                return $lang_code;
            }

            // Check for language prefix (e.g., 'en-US' -> 'en')
            $lang_prefix = substr($lang_code, 0, 2);
            if (isset($this->supported_languages[$lang_prefix])) {
                return $lang_prefix;
            }
        }

        return $this->default_language;
    }

    /**
     * Translate text
     */
    public function translate($key, $parameters = [])
    {
        $language = $this->getCurrentLanguage();
        $translations = $this->loadTranslations($language);

        $translation = $translations[$key] ?? $key;

        // Replace parameters in translation
        foreach ($parameters as $param => $value) {
            $translation = str_replace("{{$param}}", $value, $translation);
        }

        return $translation;
    }

    /**
     * Load translations for language
     */
    private function loadTranslations($language)
    {
        static $translations = [];

        if (isset($translations[$language])) {
            return $translations[$language];
        }

        $translation_file = __DIR__ . '/../../../views/languages/' . $language . '.php';

        if (file_exists($translation_file)) {
            $translations[$language] = include $translation_file;
        } else {
            // Fallback to English
            $translation_file = __DIR__ . '/../../../views/languages/en.php';
            $translations[$language] = file_exists($translation_file) ? include $translation_file : [];
        }

        return $translations[$language];
    }

    /**
     * Language selection interface
     */
    public function languageSelector()
    {
        $this->data['page_title'] = 'Select Language - APS Dream Home';
        $this->data['supported_languages'] = $this->supported_languages;
        $this->data['current_language'] = $this->getCurrentLanguage();

        return $this->renderView('language/selector', $this->data);
    }

    /**
     * Translation management (admin)
     */
    public function adminTranslations()
    {
        if (!$this->isAdmin()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->saveTranslation(Security::sanitize($_POST['language']), Security::sanitize($_POST['key']), Security::sanitize($_POST['translation']));
        }

        $languages = $this->getAvailableLanguages();
        $translation_stats = $this->getTranslationStats();

        $this->data['page_title'] = 'Translation Management - APS Dream Home';
        $this->data['languages'] = $languages;
        $this->data['translation_stats'] = $translation_stats;
        $this->data['supported_languages'] = $this->supported_languages;

        return $this->renderView('admin/translations', $this->data);
    }

    /**
     * Get available languages with stats
     */
    private function getAvailableLanguages()
    {
        $languages = [];

        foreach ($this->supported_languages as $code => $info) {
            $translation_file = __DIR__ . '/../../../views/languages/' . $code . '.php';

            if (file_exists($translation_file)) {
                $translations = include $translation_file;
                $total_keys = count($translations);
                $completed_keys = count(array_filter($translations, function ($value) {
                    return !empty($value) && $value !== $value; // Check if translated
                }));
            } else {
                $total_keys = 0;
                $completed_keys = 0;
            }

            $languages[$code] = array_merge($info, [
                'code' => $code,
                'total_keys' => $total_keys,
                'completed_keys' => $completed_keys,
                'completion_percentage' => $total_keys > 0 ? round(($completed_keys / $total_keys) * 100, 1) : 0
            ]);
        }

        return $languages;
    }

    /**
     * Get translation statistics
     */
    private function getTranslationStats()
    {
        $english_translations = $this->loadTranslations('en');
        $total_keys = count($english_translations);

        $language_stats = [];

        foreach ($this->supported_languages as $code => $info) {
            if ($code === 'en') continue;

            $translations = $this->loadTranslations($code);
            $translated_keys = 0;

            foreach ($english_translations as $key => $english_text) {
                if (!empty($translations[$key]) && $translations[$key] !== $english_text) {
                    $translated_keys++;
                }
            }

            $language_stats[$code] = [
                'name' => $info['name'],
                'translated_keys' => $translated_keys,
                'total_keys' => $total_keys,
                'completion_percentage' => round(($translated_keys / $total_keys) * 100, 1)
            ];
        }

        return [
            'total_keys' => $total_keys,
            'languages' => $language_stats
        ];
    }

    /**
     * Save individual translation
     */
    private function saveTranslation($language, $key, $translation)
    {
        try {
            $translation_file = __DIR__ . '/../../../views/languages/' . $language . '.php';

            // Load existing translations
            $translations = [];
            if (file_exists($translation_file)) {
                $translations = include $translation_file;
            }

            // Update translation
            $translations[$key] = $translation;

            // Save back to file
            $php_content = "<?php\nreturn " . var_export($translations, true) . ";\n";
            return file_put_contents($translation_file, $php_content) !== false;
        } catch (Exception $e) {
            error_log('Translation save error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send JSON response
     */
    private function sendJsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Set flash message
     */
    private function setFlashMessage($type, $message)
    {
        $_SESSION['flash_message'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    /**
     * Render view
     */
    private function renderView($view, $data = [])
    {
        extract($data);
        $viewPath = __DIR__ . '/../../../views/' . str_replace('.', '/', $view) . '.php';
        
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            echo "<h1>View not found: $view</h1>";
        }
    }
}
