<?php
/**
 * API Authentication Middleware
 * 
 * This file provides middleware functions for API authentication and security.
 * Include this file at the beginning of API endpoints that require authentication.
 */

// Include API key functions
require_once __DIR__ . '/api_keys.php';

/**
 * Authenticates an API request using API key
 * 
 * @param bool $required Whether authentication is required (true) or optional (false)
 * @return array Authentication result with user ID if successful
 */
function authenticateApiRequest($required = true) {
    // Get the current endpoint
    $endpoint = getRequestEndpoint();
    
    // Get API key from header or query parameter
    $apiKey = getApiKey();
    
    // If no API key is provided and authentication is optional, return success
    if (empty($apiKey) && !$required) {
        return [
            'authenticated' => false,
            'user_id' => null,
            'public_access' => true
        ];
    }
    
    // Validate API key
    $validation = validateApiKey($apiKey, $endpoint);
    
    // If validation failed and authentication is required, send error response
    if (!$validation['valid'] && $required) {
        sendErrorResponse($validation['status_code'], $validation['message']);
        exit;
    }
    
    // If validation is successful, add rate limit headers
    if ($validation['valid']) {
        addRateLimitHeaders($validation);
        
        // Update last used timestamp
        updateApiKeyUsage($validation['key_id']);
        
        return [
            'authenticated' => true,
            'user_id' => $validation['user_id'],
            'key_id' => $validation['key_id'],
            'public_access' => false
        ];
    }
    
    // Default return for optional authentication that failed
    return [
        'authenticated' => false,
        'user_id' => null,
        'public_access' => true
    ];
}

/**
 * Gets the current request endpoint
 * 
 * @return string The current endpoint path
 */
function getRequestEndpoint() {
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $baseDir = '/api/';
    
    // Extract the endpoint path relative to the API directory
    $pos = strpos($scriptName, $baseDir);
    if ($pos !== false) {
        return substr($scriptName, $pos);
    }
    
    return $scriptName;
}

/**
 * Gets the API key from the request
 * 
 * @return string|null The API key or null if not found
 */
function getApiKey() {
    // Check for API key in header (preferred method)
    $headers = getallheaders();
    if (isset($headers['X-API-Key'])) {
        return $headers['X-API-Key'];
    }
    
    // Check for API key in query parameter (fallback)
    if (isset($_GET['api_key'])) {
        return $_GET['api_key'];
    }
    
    return null;
}

/**
 * Adds rate limit headers to the response
 * 
 * @param array $validation The validation result from validateApiKey()
 * @return void
 */
function addRateLimitHeaders($validation) {
    header('X-Rate-Limit-Limit: ' . $validation['rate_limit']);
    header('X-Rate-Limit-Remaining: ' . $validation['requests_remaining']);
    header('X-Rate-Limit-Reset: ' . (time() + DEFAULT_RATE_WINDOW));
}

/**
 * Sends a JSON error response
 * 
 * @param int $statusCode HTTP status code
 * @param string $message Error message
 * @return void
 */
function sendErrorResponse($statusCode, $message) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => $message
    ]);
}

/**
 * Validates CORS preflight requests and sets appropriate headers
 * 
 * @param array $allowedOrigins Array of allowed origins, or ['*'] for any origin
 * @param array $allowedMethods Array of allowed HTTP methods
 * @param array $allowedHeaders Array of allowed headers
 * @param int $maxAge Max age for preflight results in seconds
 * @return bool True if this was a preflight request that was handled
 */
function handleCors($allowedOrigins = ['*'], $allowedMethods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'], 
                   $allowedHeaders = ['Content-Type', 'X-API-Key', 'Authorization'], $maxAge = 86400) {
    
    // Get the origin of the request
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    
    // Check if the origin is allowed
    $allowOrigin = in_array('*', $allowedOrigins) ? '*' : '';
    if (empty($allowOrigin) && in_array($origin, $allowedOrigins)) {
        $allowOrigin = $origin;
    }
    
    // Set CORS headers
    if (!empty($allowOrigin)) {
        header("Access-Control-Allow-Origin: $allowOrigin");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Max-Age: $maxAge");
        header("Access-Control-Allow-Methods: " . implode(', ', $allowedMethods));
        header("Access-Control-Allow-Headers: " . implode(', ', $allowedHeaders));
    }
    
    // Handle preflight requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204); // No content
        exit;
    }
    
    return false;
}

/**
 * Validates and sanitizes request parameters
 * 
 * @param array $rules Validation rules for parameters
 * @param string $method Request method to check (GET, POST, JSON)
 * @return array Validated and sanitized parameters
 */
function validateRequestParams($rules, $method = 'AUTO') {
    // Determine method if AUTO
    if ($method === 'AUTO') {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $method = 'GET';
        } else if ($_SERVER['REQUEST_METHOD'] === 'POST' || 
                  $_SERVER['REQUEST_METHOD'] === 'PUT' || 
                  $_SERVER['REQUEST_METHOD'] === 'DELETE') {
            
            $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
            if (strpos($contentType, 'application/json') !== false) {
                $method = 'JSON';
            } else {
                $method = 'POST';
            }
        }
    }
    
    // Get parameters based on method
    $params = [];
    $errors = [];
    
    switch ($method) {
        case 'GET':
            $params = $_GET;
            break;
        case 'POST':
            $params = $_POST;
            break;
        case 'JSON':
            $jsonData = file_get_contents('php://input');
            $params = json_decode($jsonData, true) ?: [];
            break;
    }
    
    // Validate and sanitize each parameter
    $validatedParams = [];
    
    foreach ($rules as $param => $rule) {
        $required = isset($rule['required']) ? $rule['required'] : false;
        $type = isset($rule['type']) ? $rule['type'] : 'string';
        $default = isset($rule['default']) ? $rule['default'] : null;
        $min = isset($rule['min']) ? $rule['min'] : null;
        $max = isset($rule['max']) ? $rule['max'] : null;
        $enum = isset($rule['enum']) ? $rule['enum'] : null;
        $regex = isset($rule['regex']) ? $rule['regex'] : null;
        
        // Check if parameter exists
        if (!isset($params[$param])) {
            if ($required) {
                $errors[$param] = "Parameter '$param' is required";
            } else {
                $validatedParams[$param] = $default;
            }
            continue;
        }
        
        $value = $params[$param];
        
        // Validate type and convert
        switch ($type) {
            case 'int':
            case 'integer':
                if (!is_numeric($value)) {
                    $errors[$param] = "Parameter '$param' must be a number";
                    continue;
                }
                $value = (int)$value;
                break;
                
            case 'float':
            case 'double':
                if (!is_numeric($value)) {
                    $errors[$param] = "Parameter '$param' must be a number";
                    continue;
                }
                $value = (float)$value;
                break;
                
            case 'bool':
            case 'boolean':
                if (is_string($value)) {
                    $value = strtolower($value);
                    $value = in_array($value, ['true', '1', 'yes', 'y']) ? true : 
                            (in_array($value, ['false', '0', 'no', 'n']) ? false : $value);
                }
                if (!is_bool($value) && $value !== 0 && $value !== 1) {
                    $errors[$param] = "Parameter '$param' must be a boolean";
                    continue;
                }
                $value = (bool)$value;
                break;
                
            case 'array':
                if (!is_array($value)) {
                    $errors[$param] = "Parameter '$param' must be an array";
                    continue;
                }
                break;
                
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$param] = "Parameter '$param' must be a valid email address";
                    continue;
                }
                break;
                
            case 'date':
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                    $errors[$param] = "Parameter '$param' must be a valid date in YYYY-MM-DD format";
                    continue;
                }
                break;
                
            case 'time':
                if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $value)) {
                    $errors[$param] = "Parameter '$param' must be a valid time in HH:MM or HH:MM:SS format";
                    continue;
                }
                break;
                
            case 'datetime':
                if (!preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}(:\d{2})?$/', $value)) {
                    $errors[$param] = "Parameter '$param' must be a valid datetime in YYYY-MM-DD HH:MM:SS format";
                    continue;
                }
                break;
                
            case 'string':
            default:
                if (!is_string($value) && !is_numeric($value)) {
                    $errors[$param] = "Parameter '$param' must be a string";
                    continue;
                }
                $value = (string)$value;
                break;
        }
        
        // Validate min/max for numbers
        if (($type === 'int' || $type === 'integer' || $type === 'float' || $type === 'double') && 
            (($min !== null && $value < $min) || ($max !== null && $value > $max))) {
            
            if ($min !== null && $max !== null) {
                $errors[$param] = "Parameter '$param' must be between $min and $max";
            } else if ($min !== null) {
                $errors[$param] = "Parameter '$param' must be at least $min";
            } else {
                $errors[$param] = "Parameter '$param' must be at most $max";
            }
            continue;
        }
        
        // Validate min/max length for strings
        if ($type === 'string' && 
            (($min !== null && strlen($value) < $min) || ($max !== null && strlen($value) > $max))) {
            
            if ($min !== null && $max !== null) {
                $errors[$param] = "Parameter '$param' must be between $min and $max characters";
            } else if ($min !== null) {
                $errors[$param] = "Parameter '$param' must be at least $min characters";
            } else {
                $errors[$param] = "Parameter '$param' must be at most $max characters";
            }
            continue;
        }
        
        // Validate enum values
        if ($enum !== null && !in_array($value, $enum)) {
            $enumStr = implode(', ', $enum);
            $errors[$param] = "Parameter '$param' must be one of: $enumStr";
            continue;
        }
        
        // Validate regex pattern
        if ($regex !== null && !preg_match($regex, $value)) {
            $errors[$param] = "Parameter '$param' has an invalid format";
            continue;
        }
        
        // Parameter passed all validations
        $validatedParams[$param] = $value;
    }
    
    // If there are validation errors, send error response
    if (!empty($errors)) {
        sendErrorResponse(400, [
            'message' => 'Invalid request parameters',
            'errors' => $errors
        ]);
        exit;
    }
    
    return $validatedParams;
}
?>
