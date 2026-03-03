<?php
namespace App\Services\Security;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Defuse\Crypto\KeyProtectedByPassword;

class EncryptionService
{
    private $masterKey;
    
    public function __construct()
    {
        $this->masterKey = $this->loadOrGenerateMasterKey();
    }
    
    /**
     * Encrypt sensitive data
     */
    public function encrypt($data, $additionalData = '')
    {
        try {
            if (is_array($data)) {
                $data = json_encode($data);
            }
            
            $encrypted = Crypto::encrypt($data . '|' . $additionalData, $this->masterKey);
            return $encrypted;
        } catch (\Exception $e) {
            throw new \Exception('Encryption failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Decrypt sensitive data
     */
    public function decrypt($encryptedData)
    {
        try {
            $decrypted = Crypto::decrypt($encryptedData, $this->masterKey);
            
            // Split data and additional data
            $parts = explode('|', $decrypted, 2);
            
            if (count($parts) === 2) {
                return [
                    'data' => $parts[0],
                    'additional_data' => $parts[1]
                ];
            }
            
            return $parts[0];
        } catch (\Exception $e) {
            throw new \Exception('Decryption failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Encrypt file
     */
    public function encryptFile($inputFile, $outputFile)
    {
        if (!file_exists($inputFile)) {
            throw new \Exception('Input file does not exist');
        }
        
        $fileData = file_get_contents($inputFile);
        $encryptedData = $this->encrypt($fileData);
        
        return file_put_contents($outputFile, $encryptedData) !== false;
    }
    
    /**
     * Decrypt file
     */
    public function decryptFile($inputFile, $outputFile)
    {
        if (!file_exists($inputFile)) {
            throw new \Exception('Input file does not exist');
        }
        
        $encryptedData = file_get_contents($inputFile);
        $decryptedData = $this->decrypt($encryptedData);
        
        $data = is_array($decryptedData) ? $decryptedData['data'] : $decryptedData;
        
        return file_put_contents($outputFile, $data) !== false;
    }
    
    /**
     * Generate user-specific encryption key
     */
    public function generateUserKey($userId, $password)
    {
        $userKey = KeyProtectedByPassword::createRandomPasswordProtectedKey($password);
        $this->storeUserKey($userId, $userKey->saveToAsciiSafeString());
        
        return $userKey;
    }
    
    /**
     * Encrypt data with user key
     */
    public function encryptForUser($userId, $data, $password)
    {
        $userKey = $this->getUserKey($userId);
        if (!$userKey) {
            throw new \Exception('User key not found');
        }
        
        try {
            $protectedKey = KeyProtectedByPassword::loadFromAsciiSafeString($userKey);
            $unprotectedKey = $protectedKey->unlockKey($password);
            
            if (is_array($data)) {
                $data = json_encode($data);
            }
            
            return Crypto::encrypt($data, $unprotectedKey);
        } catch (\Exception $e) {
            throw new \Exception('User encryption failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Decrypt data with user key
     */
    public function decryptForUser($userId, $encryptedData, $password)
    {
        $userKey = $this->getUserKey($userId);
        if (!$userKey) {
            throw new \Exception('User key not found');
        }
        
        try {
            $protectedKey = KeyProtectedByPassword::loadFromAsciiSafeString($userKey);
            $unprotectedKey = $protectedKey->unlockKey($password);
            
            $decrypted = Crypto::decrypt($encryptedData, $unprotectedKey);
            
            // Try to decode as JSON
            $decoded = json_decode($decrypted, true);
            return $decoded !== null ? $decoded : $decrypted;
        } catch (\Exception $e) {
            throw new \Exception('User decryption failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Hash password securely
     */
    public function hashPassword($password)
    {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
    }
    
    /**
     * Verify password
     */
    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
    
    /**
     * Generate secure random token
     */
    public function generateToken($length = 32)
    {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Generate API key
     */
    public function generateApiKey($userId, $permissions = [])
    {
        $keyData = [
            'user_id' => $userId,
            'permissions' => $permissions,
            'created_at' => time(),
            'expires_at' => time() + (365 * 24 * 60 * 60) // 1 year
        ];
        
        $apiKey = $this->generateToken(64);
        $encryptedKeyData = $this->encrypt(json_encode($keyData));
        
        $this->storeApiKey($apiKey, $encryptedKeyData);
        
        return $apiKey;
    }
    
    /**
     * Validate API key
     */
    public function validateApiKey($apiKey)
    {
        $encryptedKeyData = $this->getApiKey($apiKey);
        if (!$encryptedKeyData) {
            return false;
        }
        
        try {
            $keyData = json_decode($this->decrypt($encryptedKeyData), true);
            
            if (time() > $keyData['expires_at']) {
                $this->revokeApiKey($apiKey);
                return false;
            }
            
            return $keyData;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Load or generate master key
     */
    private function loadOrGenerateMasterKey()
    {
        $keyFile = BASE_PATH . '/storage/encryption.key';
        
        if (file_exists($keyFile)) {
            try {
                return Key::loadFromAsciiSafeString(file_get_contents($keyFile));
            } catch (\Exception $e) {
                // Key file corrupted, generate new one
            }
        }
        
        // Generate new master key
        $masterKey = Key::createNewRandomKey();
        
        // Save master key
        file_put_contents($keyFile, $masterKey->saveToAsciiSafeString());
        
        // Set secure permissions
        chmod($keyFile, 0600);
        
        return $masterKey;
    }
    
    /**
     * Store user key
     */
    private function storeUserKey($userId, $key)
    {
        $sql = "INSERT INTO user_encryption_keys (user_id, encrypted_key, created_at) 
                VALUES (?, ?, NOW()) 
                ON DUPLICATE KEY UPDATE encrypted_key = ?, updated_at = NOW()";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId, $key, $key]);
    }
    
    /**
     * Get user key
     */
    private function getUserKey($userId)
    {
        $sql = "SELECT encrypted_key FROM user_encryption_keys WHERE user_id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        
        $result = $stmt->fetch();
        return $result ? $result['encrypted_key'] : null;
    }
    
    /**
     * Store API key
     */
    private function storeApiKey($apiKey, $encryptedData)
    {
        $sql = "INSERT INTO api_keys (api_key, encrypted_data, created_at) VALUES (?, ?, NOW())";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$apiKey, $encryptedData]);
    }
    
    /**
     * Get API key
     */
    private function getApiKey($apiKey)
    {
        $sql = "SELECT encrypted_data FROM api_keys WHERE api_key = ? AND active = 1 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$apiKey]);
        
        $result = $stmt->fetch();
        return $result ? $result['encrypted_data'] : null;
    }
    
    /**
     * Revoke API key
     */
    private function revokeApiKey($apiKey)
    {
        $sql = "UPDATE api_keys SET active = 0, revoked_at = NOW() WHERE api_key = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$apiKey]);
    }
}
