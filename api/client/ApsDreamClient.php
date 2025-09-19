<?php

class ApsDreamClient {
    private $baseUrl;
    private $apiKey;
    private $debug;
    
    /**
     * Initialize the API client
     * 
     * @param string $baseUrl Base URL of the API (e.g., 'https://api.example.com/v1')
     * @param string $apiKey  API key for authentication (optional, can be set later)
     * @param bool   $debug   Enable debug mode to see HTTP requests/responses
     */
    public function __construct($baseUrl, $apiKey = null, $debug = false) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
        $this->debug = $debug;
    }
    
    /**
     * Set the API key
     */
    public function setApiKey($apiKey) {
        $this->apiKey = $apiKey;
        return $this;
    }
    
    /**
     * Make an HTTP request to the API
     */
    private function request($method, $endpoint, $data = null, $requiresAuth = true) {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        $ch = curl_init($url);
        
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
        ];
        
        if ($requiresAuth && $this->apiKey) {
            $headers[] = 'Authorization: Bearer ' . $this->apiKey;
        }
        
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
        ];
        
        if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH']) && $data !== null) {
            $jsonData = json_encode($data);
            $options[CURLOPT_POSTFIELDS] = $jsonData;
            $headers[] = 'Content-Length: ' . strlen($jsonData);
        }
        
        if ($this->debug) {
            $options[CURLOPT_VERBOSE] = true;
            $verbose = fopen('php://temp', 'w+');
            $options[CURLOPT_STDERR] = $verbose;
        }
        
        curl_setopt_array($ch, $options);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if ($this->debug) {
            rewind($verbose);
            $verboseLog = stream_get_contents($verbose);
            error_log("cURL Debug Info:\n" . $verboseLog);
        }
        
        if ($error) {
            throw new Exception("cURL Error: " . $error);
        }
        
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = $this->parseHeaders(substr($response, 0, $headerSize));
        $body = substr($response, $headerSize);
        
        $result = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response: " . json_last_error_msg());
        }
        
        if ($httpCode >= 400) {
            $errorMsg = $result['error'] ?? 'Unknown error';
            throw new Exception($errorMsg, $httpCode);
        }
        
        curl_close($ch);
        
        return [
            'status' => $httpCode,
            'headers' => $headers,
            'data' => $result
        ];
    }
    
    /**
     * Parse HTTP headers
     */
    private function parseHeaders($headerString) {
        $headers = [];
        $lines = explode("\r\n", $headerString);
        
        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $headers[trim($key)] = trim($value);
            }
        }
        
        return $headers;
    }
    
    // ===== AUTHENTICATION =====
    
    /**
     * Login with email and password
     * 
     * @param string $email    User email
     * @param string $password User password
     * @return array Response data with user and API key
     */
    public function login($email, $password) {
        $response = $this->request('POST', '/auth/login', [
            'email' => $email,
            'password' => $password
        ], false);
        
        // Store the API key for future requests
        if (!empty($response['data']['api_key'])) {
            $this->apiKey = $response['data']['api_key'];
        }
        
        return $response['data'];
    }
    
    /**
     * Logout (revoke current API key)
     */
    public function logout() {
        if (!$this->apiKey) {
            throw new Exception('Not authenticated');
        }
        
        $response = $this->request('POST', '/auth/logout');
        $this->apiKey = null;
        
        return $response['data'];
    }
    
    /**
     * Get current user profile
     */
    public function getProfile() {
        $response = $this->request('GET', '/profile');
        return $response['data'];
    }
    
    /**
     * Update current user profile
     */
    public function updateProfile($data) {
        $response = $this->request('PUT', '/profile', $data);
        return $response['data'];
    }
    
    // ===== PROPERTIES =====
    
    /**
     * List properties with optional filters
     */
    public function getProperties($filters = []) {
        $query = !empty($filters) ? '?' . http_build_query($filters) : '';
        $response = $this->request('GET', '/properties' . $query, null, false);
        return $response['data'];
    }
    
    /**
     * Get a single property by ID
     */
    public function getProperty($id) {
        $response = $this->request('GET', "/properties/$id", null, false);
        return $response['data'];
    }
    
    /**
     * Create a new property
     */
    public function createProperty($data) {
        $response = $this->request('POST', '/properties', $data);
        return $response['data'];
    }
    
    /**
     * Update a property
     */
    public function updateProperty($id, $data) {
        $response = $this->request('PUT', "/properties/$id", $data);
        return $response['data'];
    }
    
    /**
     * Delete a property
     */
    public function deleteProperty($id) {
        $response = $this->request('DELETE', "/properties/$id");
        return $response['data'];
    }
    
    // ===== USERS =====
    
    /**
     * List users (admin only)
     */
    public function getUsers($filters = []) {
        $query = !empty($filters) ? '?' . http_build_query($filters) : '';
        $response = $this->request('GET', '/users' . $query);
        return $response['data'];
    }
    
    /**
     * Get a single user by ID (admin only)
     */
    public function getUser($id) {
        $response = $this->request('GET', "/users/$id");
        return $response['data'];
    }
    
    /**
     * Create a new user (admin only)
     */
    public function createUser($data) {
        $response = $this->request('POST', '/users', $data);
        return $response['data'];
    }
    
    /**
     * Update a user (admin only)
     */
    public function updateUser($id, $data) {
        $response = $this->request('PUT', "/users/$id", $data);
        return $response['data'];
    }
    
    /**
     * Delete a user (admin only)
     */
    public function deleteUser($id) {
        $response = $this->request('DELETE', "/users/$id");
        return $response['data'];
    }
}

// Example usage:
/*
$client = new ApsDreamClient('http://localhost/apsdreamhomefinal/api/v1');

// Login
try {
    $login = $client->login('admin@example.com', 'password');
    echo "Logged in as: " . $login['user']['email'] . "\n";
    
    // Get properties
    $properties = $client->getProperties(['status' => 'available']);
    print_r($properties);
    
    // Get current profile
    $profile = $client->getProfile();
    print_r($profile);
    
    // Logout
    $client->logout();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . " (Code: " . $e->getCode() . ")\n";
}
*/
