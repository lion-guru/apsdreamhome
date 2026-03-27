<?php
namespace App\Services;

use App\Core\Database;
use Exception;

/**
 * API Key Service - Securely manages API keys from database
 * 
 * This service loads API keys from database instead of .env file
 * to prevent API key leaks in version control
 */
class ApiKeyService
{
    private static ?self $instance = null;
    private array $keys = [];
    private $db;
    
    private function __construct()
    {
        $this->db = Database::getInstance();
        $this->loadKeys();
    }
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Load all API keys from database
     */
    private function loadKeys(): void
    {
        try {
            $sql = "SELECT key_name, key_value, is_encrypted FROM api_keys WHERE is_active = 1";
            $result = $this->db->query($sql);
            
            while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
                $value = $row['key_value'];
                
                // Decrypt if encrypted
                if ($row['is_encrypted']) {
                    $value = $this->decrypt($value);
                }
                
                $this->keys[$row['key_name']] = $value;
            }
        } catch (Exception $e) {
            error_log("[ApiKeyService] Error loading keys: " . $e->getMessage());
        }
    }
    
    /**
     * Get API key by name
     */
    public function get(string $keyName, string $default = ''): string
    {
        return $this->keys[$keyName] ?? $default;
    }
    
    /**
     * Check if key exists
     */
    public function has(string $keyName): bool
    {
        return isset($this->keys[$keyName]);
    }
    
    /**
     * Get all keys
     */
    public function getAll(): array
    {
        return $this->keys;
    }
    
    /**
     * Save API key to database
     */
    public function save(string $keyName, string $keyValue, bool $encrypt = true): bool
    {
        try {
            $encryptedValue = $encrypt ? $this->encrypt($keyValue) : $keyValue;
            
            $sql = "INSERT INTO api_keys (key_name, key_value, is_encrypted, is_active, created_at, updated_at) 
                    VALUES (:key_name, :key_value, :is_encrypted, 1, NOW(), NOW())
                    ON DUPLICATE KEY UPDATE 
                    key_value = :key_value2, 
                    is_encrypted = :is_encrypted2, 
                    updated_at = NOW()";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':key_name' => $keyName,
                ':key_value' => $encryptedValue,
                ':is_encrypted' => $encrypt ? 1 : 0,
                ':key_value2' => $encryptedValue,
                ':is_encrypted2' => $encrypt ? 1 : 0
            ]);
            
            $this->keys[$keyName] = $keyValue;
            return true;
            
        } catch (Exception $e) {
            error_log("[ApiKeyService] Error saving key: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete API key
     */
    public function delete(string $keyName): bool
    {
        try {
            $sql = "DELETE FROM api_keys WHERE key_name = :key_name";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':key_name' => $keyName]);
            
            unset($this->keys[$keyName]);
            return true;
            
        } catch (Exception $e) {
            error_log("[ApiKeyService] Error deleting key: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Simple encryption (base64 + salt)
     * For production, use proper encryption like OpenSSL
     */
    private function encrypt(string $data): string
    {
        $salt = $this->getSalt();
        return base64_encode($salt . $data);
    }
    
    /**
     * Decrypt data
     */
    private function decrypt(string $data): string
    {
        $salt = $this->getSalt();
        $decoded = base64_decode($data);
        return str_replace($salt, '', $decoded);
    }
    
    /**
     * Get encryption salt from server environment
     */
    private function getSalt(): string
    {
        // Use a combination of server-specific values as salt
        $salt = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $salt .= $_SERVER['SERVER_SOFTWARE'] ?? 'apache';
        return substr(md5($salt), 0, 8);
    }
    
    /**
     * Get Gemini API Key
     */
    public function getGeminiKey(): string
    {
        return $this->get('GEMINI_API_KEY');
    }
    
    /**
     * Get Google Maps API Key
     */
    public function getGoogleMapsKey(): string
    {
        return $this->get('GOOGLE_MAPS_API_KEY');
    }
    
    /**
     * Get Recaptcha Site Key
     */
    public function getRecaptchaSiteKey(): string
    {
        return $this->get('RECAPTCHA_SITE_KEY');
    }
    
    /**
     * Get Recaptcha Secret Key
     */
    public function getRecaptchaSecretKey(): string
    {
        return $this->get('RECAPTCHA_SECRET_KEY');
    }
    
    /**
     * Get OpenRouter API Key
     */
    public function getOpenRouterKey(): string
    {
        return $this->get('OPENROUTER_API_KEY');
    }
    
    /**
     * Get WhatsApp Access Token
     */
    public function getWhatsAppToken(): string
    {
        return $this->get('WHATSAPP_ACCESS_TOKEN');
    }
    
    /**
     * Initialize default API keys from environment (one-time setup)
     */
    public function initializeFromEnv(): array
    {
        $keys = [
            'GEMINI_API_KEY' => $_ENV['GEMINI_API_KEY'] ?? '',
            'GEMINI_PROJECT_ID' => $_ENV['GEMINI_PROJECT_ID'] ?? '',
            'GOOGLE_MAPS_API_KEY' => $_ENV['GOOGLE_MAPS_API_KEY'] ?? '',
            'RECAPTCHA_SITE_KEY' => $_ENV['RECAPTCHA_SITE_KEY'] ?? '',
            'RECAPTCHA_SECRET_KEY' => $_ENV['RECAPTCHA_SECRET_KEY'] ?? '',
            'OPENROUTER_API_KEY' => $_ENV['OPENROUTER_API_KEY'] ?? '',
            'OPENROUTER_MODEL' => $_ENV['OPENROUTER_MODEL'] ?? 'gpt-4',
            'WHATSAPP_PHONE' => $_ENV['WHATSAPP_PHONE'] ?? '',
            'WHATSAPP_COUNTRY_CODE' => $_ENV['WHATSAPP_COUNTRY_CODE'] ?? '',
            'WHATSAPP_ACCESS_TOKEN' => $_ENV['WHATSAPP_ACCESS_TOKEN'] ?? '',
            'WHATSAPP_BUSINESS_ACCOUNT_ID' => $_ENV['WHATSAPP_BUSINESS_ACCOUNT_ID'] ?? '',
            'WHATSAPP_WEBHOOK_VERIFY_TOKEN' => $_ENV['WHATSAPP_WEBHOOK_VERIFY_TOKEN'] ?? ''
        ];
        
        $saved = [];
        foreach ($keys as $name => $value) {
            if (!empty($value) && $value !== 'YOUR_REAL_' . $name . '_HERE') {
                $this->save($name, $value);
                $saved[] = $name;
            }
        }
        
        return $saved;
    }
}
