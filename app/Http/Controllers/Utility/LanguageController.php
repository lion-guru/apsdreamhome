<?php
/**
 * Multi-Language Support Controller
 * Handles internationalization and localization
 */

namespace App\Controllers;

class LanguageController extends BaseController {

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
    public function setLanguage() {
        $lang_code = $_GET['lang'] ?? $_POST['lang'] ?? '';

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
    public function getCurrentLanguage() {
        return $_SESSION['user_language'] ??
               $_COOKIE['user_language'] ??
               $this->detectLanguage();
    }

    /**
     * Auto-detect user language from browser
     */
    private function detectLanguage() {
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
    public function translate($key, $parameters = []) {
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
    private function loadTranslations($language) {
        static $translations = [];

        if (isset($translations[$language])) {
            return $translations[$language];
        }

        $translation_file = __DIR__ . '/../languages/' . $language . '.php';

        if (file_exists($translation_file)) {
            $translations[$language] = include $translation_file;
        } else {
            // Fallback to English
            $translation_file = __DIR__ . '/../languages/en.php';
            $translations[$language] = file_exists($translation_file) ? include $translation_file : [];
        }

        return $translations[$language];
    }

    /**
     * Language selection interface
     */
    public function languageSelector() {
        $this->data['page_title'] = 'Select Language - ' . APP_NAME;
        $this->data['supported_languages'] = $this->supported_languages;
        $this->data['current_language'] = $this->getCurrentLanguage();

        $this->render('language/selector');
    }

    /**
     * Translation management (admin)
     */
    public function adminTranslations() {
        if (!$this->isAdmin()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->saveTranslation($_POST['language'], $_POST['key'], $_POST['translation']);
        }

        $languages = $this->getAvailableLanguages();
        $translation_stats = $this->getTranslationStats();

        $this->data['page_title'] = 'Translation Management - ' . APP_NAME;
        $this->data['languages'] = $languages;
        $this->data['translation_stats'] = $translation_stats;
        $this->data['supported_languages'] = $this->supported_languages;

        $this->render('admin/translation_management');
    }

    /**
     * Export translations for editing
     */
    public function exportTranslations() {
        if (!$this->isAdmin()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        $language = $_GET['lang'] ?? 'en';

        if (!isset($this->supported_languages[$language])) {
            $this->setFlashMessage('error', 'Invalid language');
            $this->redirect(BASE_URL . 'admin/translations');
            return;
        }

        $translations = $this->loadTranslations($language);

        if ($language === 'en') {
            // Export English as template for new languages
            $export_data = $translations;
        } else {
            // Export with English keys for reference
            $english_translations = $this->loadTranslations('en');
            $export_data = [];

            foreach ($english_translations as $key => $english_text) {
                $export_data[$key] = [
                    'english' => $english_text,
                    'translated' => $translations[$key] ?? $english_text
                ];
            }
        }

        $filename = 'translations_' . $language . '_' . date('Y-m-d') . '.json';
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo json_encode($export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Import translations from file
     */
    public function importTranslations() {
        if (!$this->isAdmin()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['translation_file'])) {
            $this->setFlashMessage('error', 'Please select a translation file');
            $this->redirect(BASE_URL . 'admin/translations');
            return;
        }

        $file = $_FILES['translation_file'];
        $language = $_POST['language'] ?? '';

        if (empty($language) || !isset($this->supported_languages[$language])) {
            $this->setFlashMessage('error', 'Please select a valid language');
            $this->redirect(BASE_URL . 'admin/translations');
            return;
        }

        try {
            $content = file_get_contents($file['tmp_name']);
            $translations = json_decode($content, true);

            if (!$translations) {
                throw new \Exception('Invalid JSON file');
            }

            $imported_count = 0;
            foreach ($translations as $key => $translation) {
                if (is_array($translation)) {
                    // Handle format with English reference
                    $this->saveTranslation($language, $key, $translation['translated']);
                } else {
                    // Handle direct translation format
                    $this->saveTranslation($language, $key, $translation);
                }
                $imported_count++;
            }

            $this->setFlashMessage('success', "Successfully imported {$imported_count} translations");
            $this->redirect(BASE_URL . 'admin/translations');

        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Import failed: ' . $e->getMessage());
            $this->redirect(BASE_URL . 'admin/translations');
        }
    }

    /**
     * Save individual translation
     */
    private function saveTranslation($language, $key, $translation) {
        try {
            $translation_file = __DIR__ . '/../languages/' . $language . '.php';

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

        } catch (\Exception $e) {
            error_log('Translation save error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get available languages with stats
     */
    private function getAvailableLanguages() {
        $languages = [];

        foreach ($this->supported_languages as $code => $info) {
            $translation_file = __DIR__ . '/../languages/' . $code . '.php';

            if (file_exists($translation_file)) {
                $translations = include $translation_file;
                $total_keys = count($translations);
                $completed_keys = count(array_filter($translations, function($value) {
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
    private function getTranslationStats() {
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
     * Generate missing translations report
     */
    public function missingTranslations() {
        if (!$this->isAdmin()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        $language = $_GET['lang'] ?? '';
        $english_translations = $this->loadTranslations('en');

        if (empty($language) || !isset($this->supported_languages[$language])) {
            $this->setFlashMessage('error', 'Please select a valid language');
            $this->redirect(BASE_URL . 'admin/translations');
            return;
        }

        $target_translations = $this->loadTranslations($language);
        $missing_translations = [];

        foreach ($english_translations as $key => $english_text) {
            if (empty($target_translations[$key]) || $target_translations[$key] === $english_text) {
                $missing_translations[$key] = $english_text;
            }
        }

        $this->data['page_title'] = 'Missing Translations - ' . APP_NAME;
        $this->data['language'] = $this->supported_languages[$language];
        $this->data['missing_translations'] = $missing_translations;

        $this->render('admin/missing_translations');
    }

    /**
     * Auto-translate using external service (placeholder)
     */
    public function autoTranslate() {
        if (!$this->isAdmin()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        header('Content-Type: application/json');

        $target_language = $_POST['language'] ?? '';
        $text_to_translate = $_POST['text'] ?? '';

        if (empty($target_language) || empty($text_to_translate)) {
            sendJsonResponse(['success' => false, 'error' => 'Language and text are required'], 400);
        }

        // In production, integrate with Google Translate API or similar service
        // For now, return placeholder
        $translated_text = "[AUTO-TRANSLATED] " . $text_to_translate;

        sendJsonResponse([
            'success' => true,
            'translated_text' => $translated_text
        ]);
    }

    /**
     * Language pack management
     */
    public function languagePacks() {
        if (!$this->isAdmin()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        $this->data['page_title'] = 'Language Packs - ' . APP_NAME;
        $this->data['supported_languages'] = $this->supported_languages;
        $this->data['language_stats'] = $this->getAvailableLanguages();

        $this->render('admin/language_packs');
    }

    /**
     * Create new language pack
     */
    public function createLanguagePack() {
        if (!$this->isAdmin()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $language_code = $_POST['language_code'] ?? '';
            $language_name = $_POST['language_name'] ?? '';
            $native_name = $_POST['native_name'] ?? '';

            if (empty($language_code) || empty($language_name)) {
                $this->setFlashMessage('error', 'Language code and name are required');
                $this->redirect(BASE_URL . 'admin/language-packs');
                return;
            }

            // Add to supported languages
            $this->supported_languages[$language_code] = [
                'name' => $language_name,
                'native_name' => $native_name,
                'flag' => $_POST['flag'] ?? '🌐',
                'rtl' => isset($_POST['rtl']) ? true : false
            ];

            // Create empty translation file based on English
            $english_translations = $this->loadTranslations('en');
            $empty_translations = [];

            foreach ($english_translations as $key => $value) {
                $empty_translations[$key] = ''; // Empty for translation
            }

            $translation_file = __DIR__ . '/../languages/' . $language_code . '.php';
            $php_content = "<?php\nreturn " . var_export($empty_translations, true) . ";\n";
            file_put_contents($translation_file, $php_content);

            $this->setFlashMessage('success', 'Language pack created successfully');
            $this->redirect(BASE_URL . 'admin/language-packs');
        }

        $this->data['page_title'] = 'Create Language Pack - ' . APP_NAME;
        $this->render('admin/create_language_pack');
    }

    /**
     * Delete language pack
     */
    public function deleteLanguagePack() {
        if (!$this->isAdmin()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        $language = $_GET['lang'] ?? '';

        if (empty($language) || !isset($this->supported_languages[$language]) || $language === 'en') {
            $this->setFlashMessage('error', 'Cannot delete this language');
            $this->redirect(BASE_URL . 'admin/language-packs');
            return;
        }

        // Remove from supported languages array (in production, save to config file)
        unset($this->supported_languages[$language]);

        // Delete translation file
        $translation_file = __DIR__ . '/../languages/' . $language . '.php';
        if (file_exists($translation_file)) {
            unlink($translation_file);
        }

        $this->setFlashMessage('success', 'Language pack deleted successfully');
        $this->redirect(BASE_URL . 'admin/language-packs');
    }

    /**
     * Get language-specific content
     */
    public function getLocalizedContent() {
        header('Content-Type: application/json');

        $content_type = $_GET['type'] ?? '';
        $language = $this->getCurrentLanguage();

        switch ($content_type) {
            case 'navigation':
                $content = $this->getLocalizedNavigation($language);
                break;
            case 'footer':
                $content = $this->getLocalizedFooter($language);
                break;
            case 'forms':
                $content = $this->getLocalizedForms($language);
                break;
            default:
                $content = [];
        }

        sendJsonResponse([
            'success' => true,
            'language' => $language,
            'content' => $content
        ]);
    }

    /**
     * Get localized navigation
     */
    private function getLocalizedNavigation($language) {
        return [
            'home' => $this->translate('nav_home'),
            'properties' => $this->translate('nav_properties'),
            'about' => $this->translate('nav_about'),
            'contact' => $this->translate('nav_contact'),
            'login' => $this->translate('nav_login'),
            'register' => $this->translate('nav_register')
        ];
    }

    /**
     * Get localized footer content
     */
    private function getLocalizedFooter($language) {
        return [
            'company_info' => $this->translate('footer_company_info'),
            'quick_links' => $this->translate('footer_quick_links'),
            'contact_info' => $this->translate('footer_contact_info'),
            'social_media' => $this->translate('footer_social_media')
        ];
    }

    /**
     * Get localized form labels
     */
    private function getLocalizedForms($language) {
        return [
            'name' => $this->translate('form_name'),
            'email' => $this->translate('form_email'),
            'phone' => $this->translate('form_phone'),
            'message' => $this->translate('form_message'),
            'submit' => $this->translate('form_submit')
        ];
    }

    /**
     * Language detection and redirection
     */
    public function detectAndRedirect() {
        $detected_language = $this->detectLanguage();

        if ($detected_language !== $this->default_language) {
            $this->setLanguage($detected_language);
        }

        $this->redirect(BASE_URL);
    }

    /**
     * Get language statistics for analytics
     */
    public function getLanguageStats() {
        header('Content-Type: application/json');

        $stats = [];

        foreach ($this->supported_languages as $code => $info) {
            $stats[$code] = [
                'name' => $info['name'],
                'users' => $this->getLanguageUsageCount($code),
                'completion' => $this->getLanguageCompletion($code)
            ];
        }

        sendJsonResponse([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get language usage count
     */
    private function getLanguageUsageCount($language) {
        try {
            global $pdo;

            $sql = "SELECT COUNT(*) as count FROM user_language_preferences WHERE language_code = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$language]);

            return (int)$stmt->fetch()['count'];

        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get language completion percentage
     */
    private function getLanguageCompletion($language) {
        $english_translations = $this->loadTranslations('en');
        $target_translations = $this->loadTranslations($language);

        $total_keys = count($english_translations);
        $translated_keys = 0;

        foreach ($english_translations as $key => $english_text) {
            if (!empty($target_translations[$key]) && $target_translations[$key] !== $english_text) {
                $translated_keys++;
            }
        }

        return $total_keys > 0 ? round(($translated_keys / $total_keys) * 100, 1) : 0;
    }

    /**
     * Set language (helper method)
     */
    private function setLanguage($lang_code) {
        $_SESSION['user_language'] = $lang_code;
        setcookie('user_language', $lang_code, time() + (30 * 24 * 60 * 60), '/');
    }
}
