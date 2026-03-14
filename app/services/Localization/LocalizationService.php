<?php

namespace App\Services\Localization;

use App\Core\Database\Database;
use Psr\Log\LoggerInterface;

/**
 * Modern Localization Service
 * Provides robust multi-language support, translation management, and regional customization
 */
class LocalizationService
{
    // Localization Modes
    public const MODE_SIMPLE = 'simple';
    public const MODE_ADVANCED = 'advanced';
    public const MODE_DYNAMIC = 'dynamic';

    // Translation Storage Types
    public const STORAGE_FILE = 'file';
    public const STORAGE_DATABASE = 'database';
    public const STORAGE_REMOTE = 'remote';

    // Fallback Strategies
    public const FALLBACK_DEFAULT = 'default_language';
    public const FALLBACK_ENGLISH = 'english';
    public const FALLBACK_NONE = 'none';

    // Localization Configuration
    private string $currentLocale;
    private string $defaultLocale;
    private array $supportedLocales;
    private array $translationCache;

    // System Dependencies
    private Database $db;
    private LoggerInterface $logger;

    // Advanced Configuration
    private string $localizationMode;
    private string $storageType;
    private string $fallbackStrategy;
    private array $translationSources;

    /**
     * Singleton instance
     */
    private static ?self $instance = null;

    /**
     * Get singleton instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            $db = Database::getInstance();
            // We need a logger, but since we don't have a global one easily available here 
            // without a container, we'll use a simple error-log based logger or null logger for now
            // For now, let's assume one is provided or we use a fallback.
            // This is a minimal implementation to satisfy the BaseController.
            throw new \RuntimeException("LocalizationService must be initialized with dependencies first using initialize()");
        }
        return self::$instance;
    }

    /**
     * Initialize the singleton instance
     */
    public static function initialize(Database $db, LoggerInterface $logger, string $locale = 'en_US'): self
    {
        if (self::$instance === null) {
            self::$instance = new self($db, $logger, $locale);
        }
        return self::$instance;
    }

    public function __construct(
        Database $db,
        LoggerInterface $logger,
        string $locale = 'en_US',
        string $mode = self::MODE_ADVANCED
    ) {
        $this->db = $db;
        $this->logger = $logger;
        $this->currentLocale = $locale;
        $this->defaultLocale = 'en_US';
        $this->supportedLocales = ['en_US', 'hi_IN', 'gu_IN', 'mr_IN'];
        $this->translationCache = [];
        $this->localizationMode = $mode;
        $this->storageType = self::STORAGE_DATABASE;
        $this->fallbackStrategy = self::FALLBACK_DEFAULT;
        $this->translationSources = [];

        $this->initializeLocalization();
    }

    /**
     * Initialize localization system
     */
    private function initializeLocalization(): void
    {
        try {
            $this->createLocalizationTables();
            $this->loadSupportedLocales();
            $this->loadTranslationSources();
            $this->logger->info('Localization system initialized', [
                'locale' => $this->currentLocale,
                'mode' => $this->localizationMode
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to initialize localization', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Localization initialization failed: ' . $e->getMessage());
        }
    }

    /**
     * Create localization tables
     */
    private function createLocalizationTables(): void
    {
        $tables = [
            'translations' => "
                CREATE TABLE IF NOT EXISTS translations (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    key_name VARCHAR(255) NOT NULL,
                    locale VARCHAR(10) NOT NULL,
                    translation TEXT NOT NULL,
                    context VARCHAR(255),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_translation (key_name, locale),
                    INDEX idx_key (key_name),
                    INDEX idx_locale (locale)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            'localization_config' => "
                CREATE TABLE IF NOT EXISTS localization_config (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    config_key VARCHAR(255) NOT NULL UNIQUE,
                    config_value TEXT NOT NULL,
                    description TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            'translation_sources' => "
                CREATE TABLE IF NOT EXISTS translation_sources (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    source_name VARCHAR(255) NOT NULL UNIQUE,
                    source_type VARCHAR(50) NOT NULL,
                    source_path VARCHAR(500),
                    is_active BOOLEAN DEFAULT TRUE,
                    priority INT DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            "
        ];

        foreach ($tables as $tableName => $sql) {
            try {
                $this->db->execute($sql);
                $this->logger->info("Localization table created or verified", ['table' => $tableName]);
            } catch (\Exception $e) {
                $this->logger->error("Failed to create localization table", [
                    'table' => $tableName,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->insertDefaultConfiguration();
    }

    /**
     * Insert default configuration
     */
    private function insertDefaultConfiguration(): void
    {
        $defaultConfig = [
            'default_locale' => 'en_US',
            'supported_locales' => json_encode(['en_US', 'hi_IN', 'gu_IN', 'mr_IN']),
            'fallback_strategy' => self::FALLBACK_DEFAULT,
            'storage_type' => self::STORAGE_DATABASE,
            'localization_mode' => self::MODE_ADVANCED,
            'cache_enabled' => 'true',
            'cache_ttl' => '3600'
        ];

        foreach ($defaultConfig as $key => $value) {
            try {
                $this->db->execute(
                    "INSERT IGNORE INTO localization_config (config_key, config_value, description) VALUES (?, ?, ?)",
                    [$key, $value, "Default configuration for {$key}"]
                );
            } catch (\Exception $e) {
                $this->logger->warning("Failed to insert default config", [
                    'key' => $key,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Load supported locales
     */
    private function loadSupportedLocales(): void
    {
        try {
            $result = $this->db->fetchOne(
                "SELECT config_value FROM localization_config WHERE config_key = 'supported_locales'"
            );
            
            if ($result) {
                $this->supportedLocales = json_decode($result['config_value'], true) ?: $this->supportedLocales;
            }
        } catch (\Exception $e) {
            $this->logger->warning("Failed to load supported locales", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Load translation sources
     */
    private function loadTranslationSources(): void
    {
        try {
            $sources = $this->db->fetchAll(
                "SELECT * FROM translation_sources WHERE is_active = TRUE ORDER BY priority DESC"
            );
            
            $this->translationSources = $sources ?: [];
        } catch (\Exception $e) {
            $this->logger->warning("Failed to load translation sources", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Translate a key to the current locale
     */
    public function translate(string $key, array $params = [], ?string $locale = null): string
    {
        $targetLocale = $locale ?: $this->currentLocale;
        
        // Check cache first
        $cacheKey = "{$targetLocale}.{$key}";
        if (isset($this->translationCache[$cacheKey])) {
            return $this->interpolateParams($this->translationCache[$cacheKey], $params);
        }

        // Try to get translation from database
        $translation = $this->getTranslationFromDatabase($key, $targetLocale);
        
        if (!$translation && $this->fallbackStrategy !== self::FALLBACK_NONE) {
            $translation = $this->getFallbackTranslation($key, $targetLocale);
        }

        if (!$translation) {
            $translation = $key; // Return key as fallback
            $this->logger->warning("Translation not found", [
                'key' => $key,
                'locale' => $targetLocale
            ]);
        }

        // Cache the translation
        $this->translationCache[$cacheKey] = $translation;
        
        return $this->interpolateParams($translation, $params);
    }

    /**
     * Get translation from database
     */
    private function getTranslationFromDatabase(string $key, string $locale): ?string
    {
        try {
            $result = $this->db->fetchOne(
                "SELECT translation FROM translations WHERE key_name = ? AND locale = ?",
                [$key, $locale]
            );
            
            return $result ? $result['translation'] : null;
        } catch (\Exception $e) {
            $this->logger->error("Failed to get translation from database", [
                'key' => $key,
                'locale' => $locale,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get fallback translation
     */
    private function getFallbackTranslation(string $key, string $locale): ?string
    {
        switch ($this->fallbackStrategy) {
            case self::FALLBACK_DEFAULT:
                return $this->getTranslationFromDatabase($key, $this->defaultLocale);
            
            case self::FALLBACK_ENGLISH:
                return $this->getTranslationFromDatabase($key, 'en_US');
            
            default:
                return null;
        }
    }

    /**
     * Interpolate parameters in translation
     */
    private function interpolateParams(string $translation, array $params): string
    {
        if (empty($params)) {
            return $translation;
        }

        foreach ($params as $key => $value) {
            $translation = str_replace(":{$key}", $value, $translation);
        }

        return $translation;
    }

    /**
     * Add or update translation
     */
    public function addTranslation(string $key, string $translation, string $locale, ?string $context = null): bool
    {
        try {
            $this->db->execute(
                "INSERT INTO translations (key_name, locale, translation, context) 
                 VALUES (?, ?, ?, ?) 
                 ON DUPLICATE KEY UPDATE translation = VALUES(translation), context = VALUES(context), updated_at = CURRENT_TIMESTAMP",
                [$key, $locale, $translation, $context]
            );

            // Clear cache for this key and locale
            $this->clearTranslationCache($key, $locale);

            $this->logger->info("Translation added/updated", [
                'key' => $key,
                'locale' => $locale
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error("Failed to add translation", [
                'key' => $key,
                'locale' => $locale,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Set current locale
     */
    public function setLocale(string $locale): bool
    {
        if (!in_array($locale, $this->supportedLocales)) {
            $this->logger->warning("Unsupported locale", ['locale' => $locale]);
            return false;
        }

        $this->currentLocale = $locale;
        $this->translationCache = []; // Clear cache

        $this->logger->info("Locale changed", ['locale' => $locale]);
        return true;
    }

    /**
     * Get current locale
     */
    public function getCurrentLocale(): string
    {
        return $this->currentLocale;
    }

    /**
     * Get supported locales
     */
    public function getSupportedLocales(): array
    {
        return $this->supportedLocales;
    }

    /**
     * Add supported locale
     */
    public function addSupportedLocale(string $locale): bool
    {
        if (in_array($locale, $this->supportedLocales)) {
            return true;
        }

        $this->supportedLocales[] = $locale;

        try {
            $this->db->execute(
                "UPDATE localization_config SET config_value = ? WHERE config_key = 'supported_locales'",
                [json_encode($this->supportedLocales)]
            );

            $this->logger->info("Locale added to supported list", ['locale' => $locale]);
            return true;
        } catch (\Exception $e) {
            $this->logger->error("Failed to add supported locale", [
                'locale' => $locale,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get all translations for a locale
     */
    public function getAllTranslations(string $locale): array
    {
        try {
            $translations = $this->db->fetchAll(
                "SELECT key_name, translation, context FROM translations WHERE locale = ?",
                [$locale]
            );

            $result = [];
            foreach ($translations as $translation) {
                $result[$translation['key_name']] = [
                    'translation' => $translation['translation'],
                    'context' => $translation['context']
                ];
            }

            return $result;
        } catch (\Exception $e) {
            $this->logger->error("Failed to get all translations", [
                'locale' => $locale,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Delete translation
     */
    public function deleteTranslation(string $key, string $locale): bool
    {
        try {
            $this->db->execute(
                "DELETE FROM translations WHERE key_name = ? AND locale = ?",
                [$key, $locale]
            );

            $this->clearTranslationCache($key, $locale);

            $this->logger->info("Translation deleted", [
                'key' => $key,
                'locale' => $locale
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error("Failed to delete translation", [
                'key' => $key,
                'locale' => $locale,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Clear translation cache
     */
    private function clearTranslationCache(string $key, string $locale): void
    {
        $cacheKey = "{$locale}.{$key}";
        unset($this->translationCache[$cacheKey]);
    }

    /**
     * Get localization statistics
     */
    public function getStatistics(): array
    {
        try {
            $stats = [];

            // Total translations by locale
            $localeStats = $this->db->fetchAll(
                "SELECT locale, COUNT(*) as count FROM translations GROUP BY locale"
            );
            
            $stats['translations_by_locale'] = [];
            foreach ($localeStats as $stat) {
                $stats['translations_by_locale'][$stat['locale']] = (int)$stat['count'];
            }

            // Total unique keys
            $uniqueKeys = $this->db->fetchOne(
                "SELECT COUNT(DISTINCT key_name) as count FROM translations"
            );
            $stats['total_unique_keys'] = (int)$uniqueKeys['count'];

            // Coverage percentage
            $totalPossible = count($this->supportedLocales) * $stats['total_unique_keys'];
            $actualTotal = array_sum($stats['translations_by_locale']);
            $stats['coverage_percentage'] = $totalPossible > 0 ? round(($actualTotal / $totalPossible) * 100, 2) : 0;

            // Current configuration
            $config = $this->db->fetchAll("SELECT config_key, config_value FROM localization_config");
            $stats['configuration'] = [];
            foreach ($config as $item) {
                $stats['configuration'][$item['config_key']] = $item['config_value'];
            }

            return $stats;
        } catch (\Exception $e) {
            $this->logger->error("Failed to get localization statistics", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Import translations from array
     */
    public function importTranslations(array $translations, string $locale): bool
    {
        $success = true;
        
        foreach ($translations as $key => $translation) {
            if (is_array($translation)) {
                $text = $translation['text'] ?? $key;
                $context = $translation['context'] ?? null;
            } else {
                $text = $translation;
                $context = null;
            }

            if (!$this->addTranslation($key, $text, $locale, $context)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Export translations to array
     */
    public function exportTranslations(string $locale): array
    {
        return $this->getAllTranslations($locale);
    }

    /**
     * Validate locale format
     */
    public function isValidLocale(string $locale): bool
    {
        return preg_match('/^[a-z]{2}_[A-Z]{2}$/', $locale) === 1;
    }

    /**
     * Get locale display name
     */
    public function getLocaleDisplayName(string $locale): string
    {
        $displayNames = [
            'en_US' => 'English (United States)',
            'hi_IN' => 'हिन्दी (भारत)',
            'gu_IN' => 'ગુજરાતી (ભારત)',
            'mr_IN' => 'मराठी (भारत)'
        ];

        return $displayNames[$locale] ?? $locale;
    }

    /**
     * Clear all translation cache
     */
    public function clearAllCache(): void
    {
        $this->translationCache = [];
        $this->logger->info("All translation cache cleared");
    }
}
