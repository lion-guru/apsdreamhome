<?php

namespace App\Services\I18n;

use App\Core\Database\Database;
use \App\Traits\ServiceTenantTrait;

/**
 * Localization Service
 * Multi-language support with i18n
 */
class LocalizationService
{
    use \App\Traits\ServiceTenantTrait;

    private $database;
    private $defaultLocale;
    private $currentLocale;
    private $translations;
    
    public function __construct(string $defaultLocale = 'en')
    {
        $this->database = Database::getInstance();
        $this->defaultLocale = $defaultLocale;
        $this->currentLocale = $defaultLocale;
        $this->translations = [];
        $this->ensureTablesExist();
    }
    
    /**
     * Ensure i18n tables exist
     */
    private function ensureTablesExist(): void
    {
        $pdo = $this->database->getConnection();
        
        // Supported locales
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Translations
        $pdo->exec("CREATE TABLE IF NOT EXISTS translations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            locale VARCHAR(5) NOT NULL,
            namespace VARCHAR(50) DEFAULT 'app',
            key_name VARCHAR(100) NOT NULL,
            value TEXT NOT NULL,
            is_system TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_translation (locale, namespace, key_name),
            INDEX idx_locale (locale),
            INDEX idx_namespace (namespace)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        // User preferences
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Seed default locales
        $this->seedLocales();
    }
    
    /**
     * Seed default locales
     */
    private function seedLocales(): void
    {
        $locales = [
            ['en', 'English', 'English', 0, 1, '🇬🇧'],
            ['hi', 'Hindi', 'हिंदी', 0, 0, '🇮🇳'],
            ['bn', 'Bengali', 'বাংলা', 0, 0, '🇧🇩'],
            ['te', 'Telugu', 'తెలుగు', 0, 0, '🇮🇳'],
            ['mr', 'Marathi', 'मराठी', 0, 0, '🇮🇳'],
            ['ta', 'Tamil', 'தமிழ்', 0, 0, '🇮🇳'],
            ['ur', 'Urdu', 'اردو', 1, 0, '🇵🇰'],
            ['gu', 'Gujarati', 'ગુજરાતી', 0, 0, '🇮🇳'],
            ['kn', 'Kannada', 'ಕನ್ನಡ', 0, 0, '🇮🇳'],
            ['ml', 'Malayalam', 'മലയാളം', 0, 0, '🇮🇳'],
            ['pa', 'Punjabi', 'ਪੰਜਾਬੀ', 0, 0, '🇮🇳'],
        ];
        
        $sql = "INSERT IGNORE INTO supported_locales 
            (code, name, native_name, is_rtl, is_default, flag_icon, sort_order)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->database->prepare($sql);
        
        foreach ($locales as $index => $locale) {
            $stmt->execute([
                $locale[0],
                $locale[1],
                $locale[2],
                $locale[3],
                $locale[4],
                $locale[5],
                $index
            ]);
        }
        
        // Seed basic translations
        $this->seedTranslations();
    }
    
    /**
     * Seed basic translations
     */
    private function seedTranslations(): void
    {
        $translations = [
            // English
            ['en', 'app', 'welcome', 'Welcome to APS Dream Home'],
            ['en', 'app', 'login', 'Login'],
            ['en', 'app', 'register', 'Register'],
            ['en', 'app', 'logout', 'Logout'],
            ['en', 'app', 'search', 'Search Properties'],
            ['en', 'app', 'buy', 'Buy'],
            ['en', 'app', 'rent', 'Rent'],
            ['en', 'app', 'sell', 'Sell'],
            ['en', 'app', 'contact_us', 'Contact Us'],
            ['en', 'app', 'about_us', 'About Us'],
            ['en', 'property', 'bedrooms', 'Bedrooms'],
            ['en', 'property', 'bathrooms', 'Bathrooms'],
            ['en', 'property', 'area', 'Area'],
            ['en', 'property', 'price', 'Price'],
            ['en', 'property', 'location', 'Location'],
            ['en', 'property', 'view_details', 'View Details'],
            ['en', 'property', 'add_to_wishlist', 'Add to Wishlist'],
            ['en', 'property', 'schedule_visit', 'Schedule Visit'],
            ['en', 'finance', 'emi_calculator', 'EMI Calculator'],
            ['en', 'finance', 'loan_amount', 'Loan Amount'],
            ['en', 'finance', 'interest_rate', 'Interest Rate'],
            ['en', 'finance', 'tenure', 'Tenure (Years)'],
            ['en', 'finance', 'calculate', 'Calculate'],
            ['en', 'navigation', 'home', 'Home'],
            ['en', 'navigation', 'properties', 'Properties'],
            ['en', 'navigation', 'projects', 'Projects'],
            ['en', 'navigation', 'services', 'Services'],
            ['en', 'navigation', 'dashboard', 'Dashboard'],
            ['en', 'navigation', 'my_properties', 'My Properties'],
            ['en', 'navigation', 'my_inquiries', 'My Inquiries'],
            ['en', 'navigation', 'profile', 'Profile'],
            ['en', 'navigation', 'settings', 'Settings'],
            ['en', 'messages', 'success', 'Operation completed successfully'],
            ['en', 'messages', 'error', 'An error occurred'],
            ['en', 'messages', 'loading', 'Loading...'],
            ['en', 'messages', 'no_results', 'No results found'],
            ['en', 'messages', 'confirm_delete', 'Are you sure you want to delete?'],
            
            // Hindi
            ['hi', 'app', 'welcome', 'APS Dream Home में आपका स्वागत है'],
            ['hi', 'app', 'login', 'लॉग इन'],
            ['hi', 'app', 'register', 'पंजीकरण'],
            ['hi', 'app', 'logout', 'लॉग आउट'],
            ['hi', 'app', 'search', 'प्रॉपर्टी खोजें'],
            ['hi', 'app', 'buy', 'खरीदें'],
            ['hi', 'app', 'rent', 'किराए पर लें'],
            ['hi', 'app', 'sell', 'बेचें'],
            ['hi', 'app', 'contact_us', 'संपर्क करें'],
            ['hi', 'app', 'about_us', 'हमारे बारे में'],
            ['hi', 'property', 'bedrooms', 'बेडरूम'],
            ['hi', 'property', 'bathrooms', 'बाथरूम'],
            ['hi', 'property', 'area', 'क्षेत्रफल'],
            ['hi', 'property', 'price', 'मूल्य'],
            ['hi', 'property', 'location', 'स्थान'],
            ['hi', 'property', 'view_details', 'विवरण देखें'],
            ['hi', 'property', 'add_to_wishlist', 'विशलिस्ट में जोड़ें'],
            ['hi', 'property', 'schedule_visit', 'दौरा निर्धारित करें'],
            ['hi', 'finance', 'emi_calculator', 'ईएमआई कैलकुलेटर'],
            ['hi', 'finance', 'loan_amount', 'ऋण राशि'],
            ['hi', 'finance', 'interest_rate', 'ब्याज दर'],
            ['hi', 'finance', 'tenure', 'अवधि (वर्ष)'],
            ['hi', 'finance', 'calculate', 'गणना करें'],
            ['hi', 'navigation', 'home', 'होम'],
            ['hi', 'navigation', 'properties', 'प्रॉपर्टी'],
            ['hi', 'navigation', 'projects', 'परियोजनाएं'],
            ['hi', 'navigation', 'services', 'सेवाएं'],
            ['hi', 'navigation', 'dashboard', 'डैशबोर्ड'],
            ['hi', 'navigation', 'my_properties', 'मेरी प्रॉपर्टी'],
            ['hi', 'navigation', 'my_inquiries', 'मेरी पूछताछ'],
            ['hi', 'navigation', 'profile', 'प्रोफाइल'],
            ['hi', 'navigation', 'settings', 'सेटिंग्स'],
            ['hi', 'messages', 'success', 'ऑपरेशन सफलतापूर्वक पूरा हुआ'],
            ['hi', 'messages', 'error', 'एक त्रुटि हुई'],
            ['hi', 'messages', 'loading', 'लोड हो रहा है...'],
            ['hi', 'messages', 'no_results', 'कोई परिणाम नहीं मिला'],
            ['hi', 'messages', 'confirm_delete', 'क्या आप वाकई हटाना चाहते हैं?'],
        ];
        
        $sql = "INSERT IGNORE INTO translations 
            (locale, namespace, key_name, value, is_system)
            VALUES (?, ?, ?, ?, 1)";
        
        $stmt = $this->database->prepare($sql);
        
        foreach ($translations as $trans) {
            $stmt->execute($trans);
        }
    }
    
    /**
     * Set current locale
     */
    public function setLocale(string $locale): bool
    {
        if (!$this->isLocaleSupported($locale)) {
            return false;
        }
        
        $this->currentLocale = $locale;
        $this->loadTranslations($locale);
        
        return true;
    }
    
    /**
     * Get current locale
     */
    public function getLocale(): string
    {
        return $this->currentLocale;
    }
    
    /**
     * Check if locale is supported
     */
    public function isLocaleSupported(string $locale): bool
    {
        $sql = "SELECT 1 FROM supported_locales WHERE code = ? AND is_active = 1";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$locale]);
        return (bool) $stmt->fetch();
    }
    
    /**
     * Load translations
     */
    private function loadTranslations(string $locale): void
    {
        if (isset($this->translations[$locale])) {
            return;
        }
        
        $sql = "SELECT namespace, key_name, value FROM translations WHERE locale = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$locale]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $this->translations[$locale] = [];
        foreach ($rows as $row) {
            $this->translations[$locale][$row['namespace']][$row['key_name']] = $row['value'];
        }
    }
    
    /**
     * Translate key
     */
    public function trans(string $key, array $params = [], ?string $locale = null, string $namespace = 'app'): string
    {
        $locale = $locale ?? $this->currentLocale;
        
        if (!isset($this->translations[$locale])) {
            $this->loadTranslations($locale);
        }
        
        $translation = $this->translations[$locale][$namespace][$key] ?? 
                      $this->translations[$this->defaultLocale][$namespace][$key] ?? 
                      $key;
        
        // Replace parameters
        foreach ($params as $param => $value) {
            $translation = str_replace(':' . $param, $value, $translation);
        }
        
        return $translation;
    }
    
    /**
     * Get all supported locales
     */
    public function getSupportedLocales(): array
    {
        $sql = "SELECT * FROM supported_locales WHERE is_active = 1 ORDER BY sort_order ASC";
        $stmt = $this->database->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get user locale preference
     */
    public function getUserLocale(int $userId, string $userType): array
    {
        $sql = "SELECT * FROM user_locale_preferences WHERE user_id = ? AND user_type = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$userId, $userType]);
        
        $pref = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$pref) {
            // Return default
            return [
                'locale' => $this->defaultLocale,
                'timezone' => 'Asia/Kolkata',
                'currency_code' => 'INR',
                'date_format' => 'd M Y',
                'time_format' => 'h:i A'
            ];
        }
        
        return $pref;
    }
    
    /**
     * Set user locale preference
     */
    public function setUserLocale(int $userId, string $userType, string $locale, array $options = []): bool
    {
        $sql = "INSERT INTO user_locale_preferences 
            (user_id, user_type, locale, timezone, date_format, time_format, currency_code)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            locale = VALUES(locale),
            timezone = VALUES(timezone),
            date_format = VALUES(date_format),
            time_format = VALUES(time_format),
            currency_code = VALUES(currency_code)";
        
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([
            $userId,
            $userType,
            $locale,
            $options['timezone'] ?? 'Asia/Kolkata',
            $options['date_format'] ?? 'd M Y',
            $options['time_format'] ?? 'h:i A',
            $options['currency_code'] ?? 'INR'
        ]);
    }
    
    /**
     * Format currency
     */
    public function formatCurrency(float $amount, ?string $currency = null, ?string $locale = null): string
    {
        $locale = $locale ?? $this->currentLocale;
        $currency = $currency ?? 'INR';
        
        $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($amount, $currency);
    }
    
    /**
     * Format date
     */
    public function formatDate($date, ?string $format = null, ?string $locale = null): string
    {
        $locale = $locale ?? $this->currentLocale;
        $format = $format ?? 'd M Y';
        
        if (is_string($date)) {
            $date = new \DateTime($date);
        }
        
        // Use IntlDateFormatter for locale-aware formatting
        $formatter = new \IntlDateFormatter(
            $locale,
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::NONE
        );
        
        return $formatter->format($date);
    }
    
    /**
     * Format number
     */
    public function formatNumber(float $number, int $decimals = 0, ?string $locale = null): string
    {
        $locale = $locale ?? $this->currentLocale;
        
        $formatter = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);
        $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, $decimals);
        
        return $formatter->format($number);
    }
    
    /**
     * Add translation
     */
    public function addTranslation(string $locale, string $namespace, string $key, string $value): bool
    {
        $sql = "INSERT INTO translations (locale, namespace, key_name, value)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE value = VALUES(value)";
        
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$locale, $namespace, $key, $value]);
    }
    
    /**
     * Get translations for editing
     */
    public function getTranslations(string $locale, string $namespace = 'app'): array
    {
        $sql = "SELECT * FROM translations WHERE locale = ? AND namespace = ? ORDER BY key_name ASC";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$locale, $namespace]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Export translations
     */
    public function exportTranslations(string $locale): array
    {
        $sql = "SELECT namespace, key_name, value FROM translations WHERE locale = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$locale]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $export = [];
        foreach ($rows as $row) {
            $export[$row['namespace']][$row['key_name']] = $row['value'];
        }
        
        return $export;
    }
    
    /**
     * Import translations
     */
    public function importTranslations(string $locale, array $data): int
    {
        $count = 0;
        
        foreach ($data as $namespace => $translations) {
            foreach ($translations as $key => $value) {
                if ($this->addTranslation($locale, $namespace, $key, $value)) {
                    $count++;
                }
            }
        }
        
        return $count;
    }
    
    /**
     * Detect locale from request
     */
    public function detectLocale(): string
    {
        // Check URL parameter
        if (!empty($_GET['lang'])) {
            $lang = $_GET['lang'];
            if ($this->isLocaleSupported($lang)) {
                return $lang;
            }
        }
        
        // Check session
        if (!empty($_SESSION['locale'])) {
            if ($this->isLocaleSupported($_SESSION['locale'])) {
                return $_SESSION['locale'];
            }
        }
        
        // Check browser language
        if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $browserLangs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            foreach ($browserLangs as $lang) {
                $lang = substr($lang, 0, 2);
                if ($this->isLocaleSupported($lang)) {
                    return $lang;
                }
            }
        }
        
        return $this->defaultLocale;
    }
}
