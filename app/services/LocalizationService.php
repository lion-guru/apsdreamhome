<?php

namespace App\Services;

use App\Core\Database;
use App\Core\Config;
use Exception;

/**
 * Custom Localization Service
 * Pure PHP implementation for APS Dream Home Custom MVC
 */
class LocalizationService
{
    private $db;
    private $currentLocale;
    private $supportedLocales;
    private $translations = [];
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->supportedLocales = ['en', 'hi', 'es', 'fr', 'ar'];
        $this->currentLocale = $this->detectLocale();
        $this->loadTranslations();
    }
    
    /**
     * Detect user's preferred locale
     */
    private function detectLocale(): string
    {
        // Check session first
        if (isset($_SESSION['locale']) && in_array($_SESSION['locale'], $this->supportedLocales)) {
            return $_SESSION['locale'];
        }
        
        // Check user preference
        if (isset($_SESSION['user_id'])) {
            try {
                $sql = "SELECT locale FROM users WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();
                
                if ($user && in_array($user['locale'], $this->supportedLocales)) {
                    return $user['locale'];
                }
            } catch (Exception $e) {
                // Continue to other detection methods
            }
        }
        
        // Check browser language
        $browserLang = $this->getBrowserLanguage();
        if ($browserLang && in_array($browserLang, $this->supportedLocales)) {
            return $browserLang;
        }
        
        // Default to English
        return 'en';
    }
    
    /**
     * Get browser language
     */
    private function getBrowserLanguage(): ?string
    {
        $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        
        if (empty($acceptLanguage)) {
            return null;
        }
        
        // Parse Accept-Language header
        $languages = [];
        $parts = explode(',', $acceptLanguage);
        
        foreach ($parts as $part) {
            $part = trim($part);
            if (empty($part)) continue;
            
            $segments = explode(';q=', $part);
            $lang = trim($segments[0]);
            $quality = isset($segments[1]) ? (float)$segments[1] : 1.0;
            
            // Convert to our supported locales
            $lang = strtolower(substr($lang, 0, 2));
            if (in_array($lang, $this->supportedLocales)) {
                $languages[$lang] = $quality;
            }
        }
        
        // Sort by quality and return the best match
        arsort($languages);
        return key($languages) ?: null;
    }
    
    /**
     * Load translations for current locale
     */
    private function loadTranslations(): void
    {
        try {
            // Load from database
            $sql = "SELECT key_name, value FROM translations WHERE locale = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$this->currentLocale]);
            
            foreach ($stmt->fetchAll() as $row) {
                $this->translations[$row['key_name']] = $row['value'];
            }
            
            // Also load from language files as fallback
            $this->loadFromFile();
            
        } catch (Exception $e) {
            // Fallback to file-based translations only
            $this->loadFromFile();
        }
    }
    
    /**
     * Load translations from file
     */
    private function loadFromFile(): void
    {
        $langFile = "lang/{$this->currentLocale}.php";
        
        if (file_exists($langFile)) {
            $translations = include $langFile;
            if (is_array($translations)) {
                $this->translations = array_merge($this->translations, $translations);
            }
        }
    }
    
    /**
     * Translate a key
     */
    public function translate(string $key, array $params = []): string
    {
        $translation = $this->translations[$key] ?? $key;
        
        // Replace parameters
        foreach ($params as $param => $value) {
            $translation = str_replace(':' . $param, $value, $translation);
        }
        
        return $translation;
    }
    
    /**
     * Get current locale
     */
    public function getCurrentLocale(): string
    {
        return $this->currentLocale;
    }
    
    /**
     * Set locale
     */
    public function setLocale(string $locale): bool
    {
        if (!in_array($locale, $this->supportedLocales)) {
            return false;
        }
        
        $this->currentLocale = $locale;
        $_SESSION['locale'] = $locale;
        $this->loadTranslations();
        
        return true;
    }
    
    /**
     * Get supported locales
     */
    public function getSupportedLocales(): array
    {
        return $this->supportedLocales;
    }
    
    /**
     * Format date according to locale
     */
    public function formatDate($date, string $format = 'medium'): string
    {
        if (is_string($date)) {
            $date = new DateTime($date);
        }
        
        $formats = [
            'short' => $this->getDateFormat('short'),
            'medium' => $this->getDateFormat('medium'),
            'long' => $this->getDateFormat('long'),
            'full' => $this->getDateFormat('full')
        ];
        
        $dateFormat = $formats[$format] ?? $formats['medium'];
        return $date->format($dateFormat);
    }
    
    /**
     * Format time according to locale
     */
    public function formatTime($time, string $format = 'medium'): string
    {
        if (is_string($time)) {
            $time = new DateTime($time);
        }
        
        $formats = [
            'short' => $this->getTimeFormat('short'),
            'medium' => $this->getTimeFormat('medium'),
            'long' => $this->getTimeFormat('long'),
            'full' => $this->getTimeFormat('full')
        ];
        
        $timeFormat = $formats[$format] ?? $formats['medium'];
        return $time->format($timeFormat);
    }
    
    /**
     * Format currency according to locale
     */
    public function formatCurrency(float $amount, string $currency = 'USD'): string
    {
        $formats = [
            'en' => ['symbol' => '$', 'position' => 'before', 'decimal' => '.', 'thousands' => ','],
            'hi' => ['symbol' => '₹', 'position' => 'before', 'decimal' => '.', 'thousands' => ','],
            'es' => ['symbol' => '€', 'position' => 'after', 'decimal' => ',', 'thousands' => '.'],
            'fr' => ['symbol' => '€', 'position' => 'after', 'decimal' => ',', 'thousands' => ' '],
            'ar' => ['symbol' => 'ر.س', 'position' => 'before', 'decimal' => '.', 'thousands' => ',']
        ];
        
        $format = $formats[$this->currentLocale] ?? $formats['en'];
        
        // Format number
        $amount = number_format($amount, 2, $format['decimal'], $format['thousands']);
        
        // Add currency symbol
        if ($format['position'] === 'before') {
            return $format['symbol'] . $amount;
        } else {
            return $amount . ' ' . $format['symbol'];
        }
    }
    
    /**
     * Format number according to locale
     */
    public function formatNumber(float $number, int $decimals = 2): string
    {
        $formats = [
            'en' => ['decimal' => '.', 'thousands' => ','],
            'hi' => ['decimal' => '.', 'thousands' => ','],
            'es' => ['decimal' => ',', 'thousands' => '.'],
            'fr' => ['decimal' => ',', 'thousands' => ' '],
            'ar' => ['decimal' => '.', 'thousands' => ',']
        ];
        
        $format = $formats[$this->currentLocale] ?? $formats['en'];
        return number_format($number, $decimals, $format['decimal'], $format['thousands']);
    }
    
    /**
     * Get date format for locale
     */
    private function getDateFormat(string $format): string
    {
        $formats = [
            'en' => ['short' => 'm/d/Y', 'medium' => 'M d, Y', 'long' => 'F d, Y', 'full' => 'l, F d, Y'],
            'hi' => ['short' => 'd/m/Y', 'medium' => 'd M Y', 'long' => 'd F Y', 'full' => 'l, d F Y'],
            'es' => ['short' => 'd/m/Y', 'medium' => 'd M Y', 'long' => 'd F Y', 'full' => 'l, d F Y'],
            'fr' => ['short' => 'd/m/Y', 'medium' => 'd M Y', 'long' => 'd F Y', 'full' => 'l d F Y'],
            'ar' => ['short' => 'd/m/Y', 'medium' => 'd M Y', 'long' => 'd F Y', 'full' => 'l، d F Y']
        ];
        
        return $formats[$this->currentLocale][$format] ?? $formats['en'][$format];
    }
    
    /**
     * Get time format for locale
     */
    private function getTimeFormat(string $format): string
    {
        $formats = [
            'en' => ['short' => 'h:i A', 'medium' => 'h:i:s A', 'long' => 'h:i:s A', 'full' => 'h:i:s A'],
            'hi' => ['short' => 'H:i', 'medium' => 'H:i:s', 'long' => 'H:i:s', 'full' => 'H:i:s'],
            'es' => ['short' => 'H:i', 'medium' => 'H:i:s', 'long' => 'H:i:s', 'full' => 'H:i:s'],
            'fr' => ['short' => 'H:i', 'medium' => 'H:i:s', 'long' => 'H:i:s', 'full' => 'H:i:s'],
            'ar' => ['short' => 'H:i', 'medium' => 'H:i:s', 'long' => 'H:i:s', 'full' => 'H:i:s']
        ];
        
        return $formats[$this->currentLocale][$format] ?? $formats['en'][$format];
    }
    
    /**
     * Add translation
     */
    public function addTranslation(string $key, string $value, string $locale = null): bool
    {
        $locale = $locale ?? $this->currentLocale;
        
        try {
            $sql = "INSERT INTO translations (locale, key_name, value, created_at) 
                    VALUES (?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = NOW()";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$locale, $key, $value]);
            
            if ($result) {
                // Update in-memory translations if current locale
                if ($locale === $this->currentLocale) {
                    $this->translations[$key] = $value;
                }
                return true;
            }
            
        } catch (Exception $e) {
            error_log("Failed to add translation: " . $e->getMessage());
        }
        
        return false;
    }
    
    /**
     * Get all translations for locale
     */
    public function getTranslations(string $locale = null): array
    {
        $locale = $locale ?? $this->currentLocale;
        
        try {
            $sql = "SELECT key_name, value FROM translations WHERE locale = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$locale]);
            
            $translations = [];
            foreach ($stmt->fetchAll() as $row) {
                $translations[$row['key_name']] = $row['value'];
            }
            
            return $translations;
            
        } catch (Exception $e) {
            error_log("Failed to get translations: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Import translations from file
     */
    public function importFromFile(string $filePath, string $locale = null): array
    {
        $locale = $locale ?? $this->currentLocale;
        $results = ['success' => 0, 'failed' => 0];
        
        if (!file_exists($filePath)) {
            return $results;
        }
        
        $translations = include $filePath;
        if (!is_array($translations)) {
            return $results;
        }
        
        foreach ($translations as $key => $value) {
            if ($this->addTranslation($key, $value, $locale)) {
                $results['success']++;
            } else {
                $results['failed']++;
            }
        }
        
        return $results;
    }
    
    /**
     * Export translations to file
     */
    public function exportToFile(string $filePath, string $locale = null): bool
    {
        $locale = $locale ?? $this->currentLocale;
        $translations = $this->getTranslations($locale);
        
        $content = "<?php\n\nreturn " . var_export($translations, true) . ";\n";
        
        return file_put_contents($filePath, $content) !== false;
    }
    
    /**
     * Check if locale is RTL
     */
    public function isRTL(): bool
    {
        return $this->currentLocale === 'ar';
    }
    
    /**
     * Get text direction
     */
    public function getTextDirection(): string
    {
        return $this->isRTL() ? 'rtl' : 'ltr';
    }
    
    /**
     * Magic method for translation
     */
    public function __get(string $key): string
    {
        return $this->translate($key);
    }
}