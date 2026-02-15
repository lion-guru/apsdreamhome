<?php
/**
 * API Key Management
 * 
 * This file handles API key validation, rate limiting, and access control.
 * It should be included at the beginning of all API endpoints that require authentication.
 */

// Define constants
define('API_KEY_TABLE', 'api_keys');
define('API_REQUEST_LOG_TABLE', 'api_request_logs');
define('DEFAULT_RATE_LIMIT', 100); // Default requests per hour
define('DEFAULT_RATE_WINDOW', 3600); // 1 hour in seconds

/**
 * Validates an API key and checks rate limits
 * 
 * @param string $apiKey The API key to validate
 * @param string $endpoint The endpoint being accessed
 * @return array Status of validation with any error messages
 */
function validateApiKey($apiKey, $endpoint) {
    $db = \App\Core\App::database();
    
    // Check if API key is provided
    if (empty($apiKey)) {
        return [
            'valid' => false,
            'status_code' => 401,
            'message' => 'API key is required'
        ];
    }
    
    // Hash the API key for secure comparison (consistent with Auth.php and ApiKeyManager.php)
    $hashedApiKey = hash('sha256', $apiKey);
    
    try {
        // Check if API key exists and is active
        $query = "SELECT * FROM " . API_KEY_TABLE . " WHERE api_key = :api_key AND status = 'active' LIMIT 1";
        $keyData = $db->fetch($query, ['api_key' => $hashedApiKey], false);
        
        if (!$keyData) {
            return [
                'valid' => false,
                'status_code' => 401,
                'message' => 'Invalid or inactive API key'
            ];
        }
        
        $keyId = $keyData['id'];
        $userId = $keyData['user_id'];
        $rateLimit = $keyData['rate_limit'] ?: DEFAULT_RATE_LIMIT;
        $permissions = json_decode($keyData['permissions'], true) ?: [];
        
        // Check permissions for this endpoint
        $hasPermission = false;
        
        // If no specific permissions are set, allow all endpoints
        if (empty($permissions)) {
            $hasPermission = true;
        } else {
            foreach ($permissions as $permission) {
                // Check if permission matches the endpoint or uses a wildcard
                if ($permission === '*' || $permission === $endpoint || 
                    (substr($permission, -2) === '/*' && strpos($endpoint, substr($permission, 0, -2)) === 0)) {
                    $hasPermission = true;
                    break;
                }
            }
        }
        
        if (!$hasPermission) {
            return [
                'valid' => false,
                'status_code' => 403,
                'message' => 'API key does not have permission to access this endpoint'
            ];
        }
        
        // Check rate limit
        $timeWindow = time() - DEFAULT_RATE_WINDOW;
        $query = "SELECT COUNT(*) as request_count FROM " . API_REQUEST_LOG_TABLE . " 
                  WHERE api_key_id = :key_id AND request_time > FROM_UNIXTIME(:time_window)";
        $row = $db->fetch($query, ['key_id' => $keyId, 'time_window' => $timeWindow], false);
        $requestCount = $row['request_count'];
        
        if ($requestCount >= $rateLimit) {
            return [
                'valid' => false,
                'status_code' => 429,
                'message' => 'Rate limit exceeded. Try again later.'
            ];
        }
        
        // Log this request
        $query = "INSERT INTO " . API_REQUEST_LOG_TABLE . " 
                  (api_key_id, endpoint, request_time, ip_address, user_agent) 
                  VALUES (:key_id, :endpoint, NOW(), :ip_address, :user_agent)";
        $db->execute($query, [
            'key_id' => $keyId,
            'endpoint' => $endpoint,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
        ]);
        
        // Return success with user ID
        return [
            'valid' => true,
            'user_id' => $userId,
            'key_id' => $keyId,
            'rate_limit' => $rateLimit,
            'requests_made' => $requestCount + 1,
            'requests_remaining' => $rateLimit - $requestCount - 1
        ];
    } catch (Exception $e) {
        error_log("API Key validation error: " . $e->getMessage());
        return [
            'valid' => false,
            'status_code' => 500,
            'message' => 'Internal server error during authentication'
        ];
    }
}

/**
 * Generates a new API key
 * 
 * @param int $userId User ID to associate with the key
 * @param string $name Name/description of the key
 * @param array $permissions Array of endpoint permissions
 * @param int $rateLimit Optional custom rate limit
 * @return string|false The generated API key or false on failure
 */
function generateApiKey($userId, $name, $permissions = [], $rateLimit = DEFAULT_RATE_LIMIT) {
    $db = \App\Core\App::database();
    
    // Generate a secure random API key
    $plainApiKey = bin2hex(random_bytes(16)); // 32 character hex string
    
    // Hash the API key for secure storage (consistent with Auth.php and ApiKeyManager.php)
    $hashedApiKey = hash('sha256', $plainApiKey);
    
    // Convert permissions array to JSON
    $permissionsJson = json_encode($permissions);
    
    try {
        // Insert new API key (store the hashed version)
        $query = "INSERT INTO " . API_KEY_TABLE . " 
                  (user_id, api_key, name, permissions, rate_limit, status, created_at) 
                  VALUES (:user_id, :api_key, :name, :permissions, :rate_limit, 'active', NOW())";
        
        $db->execute($query, [
            'user_id' => $userId,
            'api_key' => $hashedApiKey,
            'name' => $name,
            'permissions' => $permissionsJson,
            'rate_limit' => $rateLimit
        ]);
        
        return $plainApiKey;
    } catch (Exception $e) {
        error_log("Error generating API key: " . $e->getMessage());
        return false;
    }
}

/**
 * Revokes (deactivates) an API key
 * 
 * @param string $apiKey The API key to revoke
 * @param int $userId User ID that owns the key (for verification)
 * @return bool True if revoked successfully, false otherwise
 */
function revokeApiKey($apiKey, $userId) {
    $db = \App\Core\App::database();
    
    // Hash the API key for secure comparison
    $hashedApiKey = hash('sha256', $apiKey);
    
    try {
        $query = "UPDATE " . API_KEY_TABLE . " 
                  SET status = 'revoked', updated_at = NOW() 
                  WHERE api_key = :api_key AND user_id = :user_id";
        
        $db->execute($query, [
            'api_key' => $hashedApiKey,
            'user_id' => $userId
        ]);
        
        return true;
    } catch (Exception $e) {
        error_log("Error revoking API key: " . $e->getMessage());
        return false;
    }
}

/**
 * Gets API key information
 * 
 * @param string $apiKey The API key to get info for
 * @return array|false API key data or false if not found
 */
function getApiKeyInfo($apiKey) {
    $db = \App\Core\App::database();
    
    // Hash the API key for secure comparison
    $hashedApiKey = hash('sha256', $apiKey);
    
    try {
        $query = "SELECT * FROM " . API_KEY_TABLE . " WHERE api_key = :api_key LIMIT 1";
        return $db->fetch($query, ['api_key' => $hashedApiKey], false);
    } catch (Exception $e) {
        error_log("Error getting API key info: " . $e->getMessage());
        return false;
    }
}

/**
 * Lists all API keys for a user
 * 
 * @param int $userId User ID to get keys for
 * @return array Array of API keys
 */
function listApiKeys($userId) {
    $db = \App\Core\App::database();
    
    try {
        $query = "SELECT id, name, api_key, permissions, rate_limit, status, created_at, last_used_at, 
                  (SELECT COUNT(*) FROM " . API_REQUEST_LOG_TABLE . " WHERE api_key_id = " . API_KEY_TABLE . ".id) as usage_count 
                  FROM " . API_KEY_TABLE . " 
                  WHERE user_id = :user_id 
                  ORDER BY created_at DESC";
        
        $rows = $db->fetch($query, ['user_id' => $userId]);
        
        $keys = [];
        foreach ($rows as $row) {
            // Mask API key for security
            $row['api_key'] = substr($row['api_key'], 0, 8) . '...' . substr($row['api_key'], -4);
            $row['permissions'] = json_decode($row['permissions'], true);
            $keys[] = $row;
        }
        
        return $keys;
    } catch (Exception $e) {
        error_log("Error listing API keys: " . $e->getMessage());
        return [];
    }
}

/**
 * Updates the last used timestamp for an API key
 * 
 * @param int $keyId API key ID to update
 * @return void
 */
function updateApiKeyUsage($keyId) {
    $db = \App\Core\App::database();
    
    try {
        $query = "UPDATE " . API_KEY_TABLE . " SET last_used_at = NOW() WHERE id = :key_id";
        $db->execute($query, ['key_id' => $keyId]);
    } catch (Exception $e) {
        error_log("Error updating API key usage: " . $e->getMessage());
    }
}

/**
 * Gets usage statistics for an API key
 * 
 * @param int $keyId API key ID to get stats for
 * @param int $days Number of days to look back
 * @return array Usage statistics
 */
function getApiKeyUsageStats($keyId, $days = 30) {
    $db = \App\Core\App::database();
    
    $stats = [
        'total_requests' => 0,
        'daily_breakdown' => [],
        'endpoint_breakdown' => []
    ];
    
    try {
        // Get total requests
        $query = "SELECT COUNT(*) as count FROM " . API_REQUEST_LOG_TABLE . " 
                  WHERE api_key_id = :key_id AND request_time >= DATE_SUB(NOW(), INTERVAL :days DAY)";
        $row = $db->fetch($query, ['key_id' => $keyId, 'days' => $days], false);
        $stats['total_requests'] = $row['count'] ?? 0;
        
        // Get daily breakdown
        $query = "SELECT DATE(request_time) as date, COUNT(*) as count 
                  FROM " . API_REQUEST_LOG_TABLE . " 
                  WHERE api_key_id = :key_id AND request_time >= DATE_SUB(NOW(), INTERVAL :days DAY) 
                  GROUP BY DATE(request_time) 
                  ORDER BY date";
        $daily_rows = $db->fetch($query, ['key_id' => $keyId, 'days' => $days]);
        
        foreach ($daily_rows as $row) {
            $stats['daily_breakdown'][$row['date']] = $row['count'];
        }
        
        // Get endpoint breakdown
        $query = "SELECT endpoint, COUNT(*) as count 
                  FROM " . API_REQUEST_LOG_TABLE . " 
                  WHERE api_key_id = :key_id AND request_time >= DATE_SUB(NOW(), INTERVAL :days DAY) 
                  GROUP BY endpoint 
                  ORDER BY count DESC";
        $endpoint_rows = $db->fetch($query, ['key_id' => $keyId, 'days' => $days]);
        
        foreach ($endpoint_rows as $row) {
            $stats['endpoint_breakdown'][$row['endpoint']] = $row['count'];
        }
    } catch (Exception $e) {
        error_log("Error getting API key usage stats: " . $e->getMessage());
    }
    
    return $stats;
}
?>