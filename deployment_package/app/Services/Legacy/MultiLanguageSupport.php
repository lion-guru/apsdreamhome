<?php

namespace App\Services\Legacy;
/**
 * Multi-Language Support - APS Dream Homes
 * Internationalization and localization system
 */

class MultiLanguageSupport {
    private $db;
    private $currentLanguage = 'en';
    private $supportedLanguages = ['en', 'hi', 'ur', 'bn', 'gu', 'ta', 'te', 'mr', 'kn', 'ml', 'fr', 'es', 'ar', 'de', 'zh'];
    private $translations = [];

    public function __construct($db = null) {
        $this->db = $db ?: \App\Core\App::database();
        $this->initMultiLanguage();
    }

    /**
     * Initialize multi-language system
     */
    private function initMultiLanguage() {
        // Create language tables
        $this->createLanguageTables();

        // Load current language
        $this->loadCurrentLanguage();

        // Load translations
        $this->loadTranslations();
    }

    /**
     * Create language database tables
     */
    private function createLanguageTables() {
        $tables = [
            "CREATE TABLE IF NOT EXISTS languages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                code VARCHAR(10) UNIQUE,
                name VARCHAR(100),
                native_name VARCHAR(100),
                is_active BOOLEAN DEFAULT 1,
                is_default BOOLEAN DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_code (code),
                INDEX idx_active (is_active)
            )",

            "CREATE TABLE IF NOT EXISTS translations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                language_code VARCHAR(10),
                translation_key VARCHAR(255),
                translation_value TEXT,
                context VARCHAR(100),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_translation (language_code, translation_key),
                INDEX idx_language (language_code),
                INDEX idx_key (translation_key)
            )",

            "CREATE TABLE IF NOT EXISTS content_translations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                content_type ENUM('page', 'property', 'project', 'blog', 'faq'),
                content_id INT,
                language_code VARCHAR(10),
                title VARCHAR(500),
                content TEXT,
                meta_description TEXT,
                meta_keywords TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_content_translation (content_type, content_id, language_code),
                INDEX idx_content (content_type, content_id),
                INDEX idx_language (language_code)
            )"
        ];

        foreach ($tables as $sql) {
            $this->db->execute($sql);
        }

        // Insert default languages
        $this->insertDefaultLanguages();
    }

    /**
     * Insert default languages
     */
    private function insertDefaultLanguages() {
        $languages = [
            ['en', 'English', 'English', 1, 1],
            ['hi', 'Hindi', 'हिन्दी', 1, 0],
            ['ur', 'Urdu', 'اردو', 1, 0],
            ['bn', 'Bengali', 'বাংলা', 1, 0],
            ['gu', 'Gujarati', 'ગુજરાતી', 1, 0],
            ['ta', 'Tamil', 'தமிழ்', 1, 0],
            ['te', 'Telugu', 'తెలుగు', 1, 0],
            ['mr', 'Marathi', 'মরাઠી', 1, 0],
            ['kn', 'Kannada', 'ಕನ್ನಡ', 1, 0],
            ['ml', 'Malayalam', 'മലയാളം', 1, 0]
        ];

        foreach ($languages as $lang) {
            $sql = "INSERT IGNORE INTO languages (code, name, native_name, is_active, is_default) VALUES (?, ?, ?, ?, ?)";
            $this->db->execute($sql, $lang);
        }
    }

    /**
     * Load current language
     */
    private function loadCurrentLanguage() {
        // Detect language from URL parameter
        if (isset($_GET['lang']) && in_array($_GET['lang'], $this->supportedLanguages)) {
            $this->currentLanguage = $_GET['lang'];
            $_SESSION['language'] = $this->currentLanguage;
        }
        // Detect from session
        elseif (isset($_SESSION['language']) && in_array($_SESSION['language'], $this->supportedLanguages)) {
            $this->currentLanguage = $_SESSION['language'];
        }
        // Detect from browser
        else {
            $this->currentLanguage = $this->detectBrowserLanguage();
        }
    }

    /**
     * Detect browser language
     */
    private function detectBrowserLanguage() {
        $browserLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'en';
        $browserLang = substr($browserLang, 0, 2);

        return in_array($browserLang, $this->supportedLanguages) ? $browserLang : 'en';
    }

    /**
     * Load translations for current language
     */
    private function loadTranslations() {
        $sql = "SELECT translation_key, translation_value FROM translations WHERE language_code = ?";
        $results = $this->db->fetchAll($sql, [$this->currentLanguage]);

        foreach ($results as $row) {
            $this->translations[$row['translation_key']] = $row['translation_value'];
        }
    }

    /**
     * Translate a key
     * @param string $key
     * @param string|null $default
     * @return string
     */
    public function translate($key, $default = null) {
        return $this->translations[$key] ?? $default ?? $key;
    }

    /**
     * Set language
     * @param string $lang
     */
    public function setLanguage($lang) {
        if (in_array($lang, $this->supportedLanguages)) {
            $this->currentLanguage = $lang;
            $this->loadTranslations();
        }
    }

    /**
     * Get current language
     * @return string
     */
    public function getCurrentLanguage() {
        return $this->currentLanguage;
    }
}
