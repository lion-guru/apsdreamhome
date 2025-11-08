<?php

class AuthEndpoint extends BaseEndpoint {
    
    /**
     * Authenticate user and generate API key
     */
    public function login($email, $password) {
        try {
            // Validate input
            if (empty($email) || empty($password)) {
                return $this->error('Email and password are required', 400);
            }
            
            // Get user by email
            $query = "SELECT id, first_name, last_name, email, password, role, status 
                      FROM users 
                      WHERE email = ? AND status = 'active'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return $this->error('Invalid email or password', 401);
            }
            
            $user = $result->fetch_assoc();
            
            // Verify password
            if (!password_verify($password, $user['password'])) {
                return $this->error('Invalid email or password', 401);
            }
            
            // Generate API key
            $apiKey = $this->generateApiKey($user['id'], $user['email']);
            
            // Return user data with API key (don't include password)
            unset($user['password']);
            
            return $this->success([
                'user' => $user,
                'api_key' => $apiKey,
                'expires_in' => 3600 * 24 * 30, // 30 days
                'token_type' => 'Bearer'
            ]);
            
        } catch (Exception $e) {
            return $this->error('Authentication failed: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Generate a new API key for a user
     */
    private function generateApiKey($userId, $email) {
        // Generate a random API key
        $apiKey = bin2hex(random_bytes(32));
        $hashedKey = hash('sha256', $apiKey);
        
        // Set expiration (30 days from now)
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        // Create a friendly name for the API key
        $keyName = 'Web Login - ' . date('Y-m-d H:i:s');
        
        // Store the API key in the database
        $query = "INSERT INTO api_keys (user_id, name, api_key, expires_at, created_at) 
                  VALUES (?, ?, ?, ?, NOW())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('isss', $userId, $keyName, $hashedKey, $expiresAt);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to generate API key');
        }
        
        // Return the plaintext API key (only time it's available)
        return $apiKey;
    }
    
    /**
     * Revoke an API key
     */
    public function logout($apiKey) {
        try {
            if (empty($apiKey)) {
                return $this->error('API key is required', 400);
            }
            
            // Hash the API key for comparison
            $hashedKey = hash('sha256', $apiKey);
            
            // Revoke the API key
            $query = "UPDATE api_keys SET revoked = 1, revoked_at = NOW() 
                      WHERE api_key = ? AND (revoked IS NULL OR revoked = 0)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('s', $hashedKey);
            $stmt->execute();
            
            if ($stmt->affected_rows === 0) {
                return $this->error('Invalid or already revoked API key', 400);
            }
            
            return $this->success(null, 'Successfully logged out');
            
        } catch (Exception $e) {
            return $this->error('Logout failed: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get the current authenticated user
     */
    public function me() {
        try {
            $userId = $this->getUserId();
            
            $query = "SELECT id, first_name, last_name, email, phone, role, status, created_at, updated_at 
                      FROM users 
                      WHERE id = ? AND status = 'active'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return $this->error('User not found', 404);
            }
            
            return $this->success($result->fetch_assoc());
            
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    /**
     * Refresh an API key
     */
    public function refresh() {
        try {
            $userId = $this->getUserId();
            
            // Get user details
            $query = "SELECT id, email FROM users WHERE id = ? AND status = 'active'";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return $this->error('User not found or inactive', 404);
            }
            
            $user = $result->fetch_assoc();
            
            // Generate a new API key
            $apiKey = $this->generateApiKey($user['id'], $user['email']);
            
            return $this->success([
                'api_key' => $apiKey,
                'expires_in' => 3600 * 24 * 30, // 30 days
                'token_type' => 'Bearer'
            ]);
            
        } catch (Exception $e) {
            return $this->error('Failed to refresh API key: ' . $e->getMessage(), 500);
        }
    }
}
