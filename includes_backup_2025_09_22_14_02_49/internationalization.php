<?php
namespace APSDreamHome\Core;

/**
 * Internationalization and Localization Management
 * Handles locale configuration, translations, and language-related settings
 */
class Internationalization {
    private $config;
    private $currentLocale;
    private $supportedLocales;
    private $translationStorageType;

    public function __construct($config) {
        $this->config = $config;
        $this->initializeLocaleSettings();
    }

    /**
     * Initialize locale settings from configuration
     */
    private function initializeLocaleSettings() {
        $this->currentLocale = $this->config->get('DEFAULT_LOCALE', 'en_US');
        
        $supportedLocalesStr = $this->config->get('SUPPORTED_LOCALES', 'en_US');
        $this->supportedLocales = array_map('trim', explode(',', $supportedLocalesStr));
        
        $this->translationStorageType = $this->config->get('TRANSLATION_STORAGE_TYPE', 'file');
    }

    /**
     * Get the current locale
     * @return string
     */
    public function getCurrentLocale(): string {
        return $this->currentLocale;
    }

    /**
     * Set the current locale
     * @param string $locale Locale code
     * @return bool Whether locale was successfully set
     */
    public function setLocale(string $locale): bool {
        if (in_array($locale, $this->supportedLocales)) {
            $this->currentLocale = $locale;
            // Set PHP locale
            setlocale(LC_ALL, $locale . '.UTF-8');
            return true;
        }
        return false;
    }

    /**
     * Get supported locales
     * @return array
     */
    public function getSupportedLocales(): array {
        return $this->supportedLocales;
    }

    /**
     * Translate a given string
     * @param string $key Translation key
     * @param array $params Replacement parameters
     * @return string Translated string
     */
    public function translate(string $key, array $params = []): string {
        // Placeholder translation logic
        // In a real implementation, this would load translations from files/database
        $translations = $this->loadTranslations();
        
        $translation = $translations[$key] ?? $key;
        
        // Replace placeholders
        foreach ($params as $k => $v) {
            $translation = str_replace("{{$k}}", $v, $translation);
        }
        
        return $translation;
    }

    /**
     * Load translations based on current locale and storage type
     * @return array Translation dictionary
     */
    private function loadTranslations(): array {
        $translationPath = __DIR__ . "/translations/{$this->currentLocale}.php";
        
        // Fallback strategy
        $fallbackStrategy = $this->config->get('TRANSLATION_FALLBACK_STRATEGY', 'default_language');
        
        if (!file_exists($translationPath) && $fallbackStrategy === 'default_language') {
            $translationPath = __DIR__ . "/translations/{$this->config->get('DEFAULT_LOCALE', 'en_US')}.php";
        }
        
        return file_exists($translationPath) ? require $translationPath : [];
    }

    /**
     * Format number based on current locale
     * @param float $number Number to format
     * @return string Formatted number
     */
    public function formatNumber(float $number): string {
        return number_format($number, 2, 
            $this->getCurrentLocaleInfo('decimal_point'), 
            $this->getCurrentLocaleInfo('thousands_sep')
        );
    }

    /**
     * Get current locale information
     * @param string $key Specific locale info key
     * @return mixed Locale information
     */
    private function getCurrentLocaleInfo(string $key = null) {
        $localeInfo = localeconv();
        return $key ? ($localeInfo[$key] ?? null) : $localeInfo;
    }
}
