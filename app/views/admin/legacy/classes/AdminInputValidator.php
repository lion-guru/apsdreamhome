<?php
/**
 * Input Validation Class for Admin Forms
 * Provides comprehensive validation and sanitization for all admin panel inputs
 */

class AdminInputValidator {
    
    /**
     * Validate and sanitize text input
     */
    public static function validateText($input, $minLength = 1, $maxLength = 255, $allowSpecialChars = false) {
        if (empty(trim($input))) {
            return ['valid' => false, 'error' => 'This field is required'];
        }
        
        $input = trim($input);
        
        if (strlen($input) < $minLength) {
            return ['valid' => false, 'error' => "Minimum length is {$minLength} characters"];
        }
        
        if (strlen($input) > $maxLength) {
            return ['valid' => false, 'error' => "Maximum length is {$maxLength} characters"];
        }
        
        if (!$allowSpecialChars) {
            // Remove potentially dangerous characters
            $input = preg_replace('/[<>\'"]/', '', $input);
        }
        
        return ['valid' => true, 'value' => h($input)];
    }
    
    /**
     * Validate email address
     */
    public static function validateEmail($email) {
        if (empty(trim($email))) {
            return ['valid' => false, 'error' => 'Email is required'];
        }
        
        $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'error' => 'Invalid email format'];
        }
        
        // Additional security check for common email attacks
        if (preg_match('/[<>\r\n]/', $email)) {
            return ['valid' => false, 'error' => 'Email contains invalid characters'];
        }
        
        return ['valid' => true, 'value' => $email];
    }
    
    /**
     * Validate phone number
     */
    public static function validatePhone($phone) {
        if (empty(trim($phone))) {
            return ['valid' => false, 'error' => 'Phone number is required'];
        }
        
        // Remove all non-digit characters
        $phone = preg_replace('/\D/', '', $phone);
        
        if (strlen($phone) < 10 || strlen($phone) > 15) {
            return ['valid' => false, 'error' => 'Phone number must be between 10-15 digits'];
        }
        
        return ['valid' => true, 'value' => $phone];
    }
    
    /**
     * Validate numeric input
     */
    public static function validateNumber($input, $min = null, $max = null, $allowDecimal = true) {
        if (empty($input) && $input !== '0') {
            return ['valid' => false, 'error' => 'This field is required'];
        }
        
        if (!is_numeric($input)) {
            return ['valid' => false, 'error' => 'Must be a valid number'];
        }
        
        $value = $allowDecimal ? (float)$input : (int)$input;
        
        if ($min !== null && $value < $min) {
            return ['valid' => false, 'error' => "Minimum value is {$min}"];
        }
        
        if ($max !== null && $value > $max) {
            return ['valid' => false, 'error' => "Maximum value is {$max}"];
        }
        
        return ['valid' => true, 'value' => $value];
    }
    
    /**
     * Validate date input
     */
    public static function validateDate($date, $format = 'Y-m-d') {
        if (empty(trim($date))) {
            return ['valid' => false, 'error' => 'Date is required'];
        }
        
        $d = DateTime::createFromFormat($format, $date);
        if (!$d || $d->format($format) !== $date) {
            return ['valid' => false, 'error' => 'Invalid date format'];
        }
        
        // Check if date is not in the future (for most admin forms)
        if ($d > new DateTime()) {
            return ['valid' => false, 'error' => 'Date cannot be in the future'];
        }
        
        return ['valid' => true, 'value' => $date];
    }
    
    /**
     * Validate file upload
     */
    public static function validateFile($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf'], $maxSize = 5242880) {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'error' => 'File upload failed'];
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'error' => 'File size exceeds maximum allowed size of ' . ($maxSize / 1048576) . 'MB'];
        }
        
        // Check file type
        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension']);
        
        if (!in_array($extension, $allowedTypes)) {
            return ['valid' => false, 'error' => 'File type not allowed. Allowed types: ' . implode(', ', $allowedTypes)];
        }
        
        // Verify MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        if (is_resource($finfo)) { finfo_close($finfo); }
        
        $allowedMimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf'
        ];
        
        if (!isset($allowedMimeTypes[$extension]) || $mimeType !== $allowedMimeTypes[$extension]) {
            return ['valid' => false, 'error' => 'File MIME type does not match extension'];
        }
        
        return ['valid' => true, 'value' => $file];
    }
    
    /**
     * Validate property price
     */
    public static function validatePrice($price) {
        $result = self::validateNumber($price, 0, 999999999.99, true);
        if (!$result['valid']) {
            return $result;
        }
        
        // Ensure proper decimal format
        $price = number_format($result['value'], 2, '.', '');
        
        return ['valid' => true, 'value' => $price];
    }
    
    /**
     * Validate property area
     */
    public static function validateArea($area) {
        $result = self::validateNumber($area, 1, 999999, true);
        if (!$result['valid']) {
            return $result;
        }
        
        return ['valid' => true, 'value' => $result['value']];
    }
    
    /**
     * Validate property type
     */
    public static function validatePropertyType($type) {
        $validTypes = ['house', 'apartment', 'condo', 'townhouse', 'villa', 'plot', 'commercial'];
        
        if (empty($type)) {
            return ['valid' => false, 'error' => 'Property type is required'];
        }
        
        if (!in_array(strtolower($type), $validTypes)) {
            return ['valid' => false, 'error' => 'Invalid property type'];
        }
        
        return ['valid' => true, 'value' => strtolower($type)];
    }
    
    /**
     * Validate property status
     */
    public static function validatePropertyStatus($status) {
        $validStatuses = ['available', 'sold', 'rented', 'under_contract', 'pending'];
        
        if (empty($status)) {
            return ['valid' => false, 'error' => 'Property status is required'];
        }
        
        if (!in_array(strtolower($status), $validStatuses)) {
            return ['valid' => false, 'error' => 'Invalid property status'];
        }
        
        return ['valid' => true, 'value' => strtolower($status)];
    }
    
    /**
     * Validate role permissions
     */
    public static function validatePermissions($permissions) {
        if (!is_array($permissions) || empty($permissions)) {
            return ['valid' => false, 'error' => 'At least one permission is required'];
        }
        
        $validPermissions = ['read', 'write', 'delete', 'admin', 'moderator'];
        
        foreach ($permissions as $permission) {
            if (!in_array(strtolower($permission), $validPermissions)) {
                return ['valid' => false, 'error' => 'Invalid permission: ' . $permission];
            }
        }
        
        return ['valid' => true, 'value' => array_map('strtolower', $permissions)];
    }
    
    /**
     * Validate username
     */
    public static function validateUsername($username) {
        if (empty(trim($username))) {
            return ['valid' => false, 'error' => 'Username is required'];
        }
        
        $username = trim($username);
        
        if (strlen($username) < 3 || strlen($username) > 20) {
            return ['valid' => false, 'error' => 'Username must be between 3-20 characters'];
        }
        
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return ['valid' => false, 'error' => 'Username can only contain letters, numbers, and underscores'];
        }
        
        return ['valid' => true, 'value' => $username];
    }
    
    /**
     * Validate password
     */
    public static function validatePassword($password) {
        if (empty($password)) {
            return ['valid' => false, 'error' => 'Password is required'];
        }
        
        if (strlen($password) < 8) {
            return ['valid' => false, 'error' => 'Password must be at least 8 characters long'];
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            return ['valid' => false, 'error' => 'Password must contain at least one uppercase letter'];
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            return ['valid' => false, 'error' => 'Password must contain at least one lowercase letter'];
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            return ['valid' => false, 'error' => 'Password must contain at least one number'];
        }
        
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            return ['valid' => false, 'error' => 'Password must contain at least one special character'];
        }
        
        return ['valid' => true, 'value' => password_hash($password, PASSWORD_DEFAULT)];
    }
    
    /**
     * Sanitize HTML content
     */
    public static function sanitizeHTML($html) {
        // Remove dangerous tags and attributes
        $allowedTags = '<p><br><strong><em><u><h1><h2><h3><h4><h5><h6><ul><ol><li><blockquote><a>';
        $html = strip_tags($html, $allowedTags);
        
        // Remove javascript: and data: protocols from links
        $html = preg_replace('/href=["\']?(javascript:|data:)[^"\']*["\']?/i', 'href="#"', $html);
        
        return $html;
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateCSRFToken($token) {
        if (empty($token)) {
            return ['valid' => false, 'error' => 'CSRF token is missing'];
        }
        
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            return ['valid' => false, 'error' => 'Invalid CSRF token'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = \bin2hex(\App\Helpers\SecurityHelper::secureRandomBytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validate and process form data
     */
    public static function validateForm($formData, $validationRules) {
        $errors = [];
        $validatedData = [];
        
        foreach ($validationRules as $field => $rules) {
            if (!isset($formData[$field]) && !isset($rules['optional'])) {
                $errors[$field] = 'This field is required';
                continue;
            }
            
            if (isset($formData[$field])) {
                $value = $formData[$field];
                
                switch ($rules['type']) {
                    case 'text':
                        $result = self::validateText(
                            $value, 
                            $rules['min'] ?? 1, 
                            $rules['max'] ?? 255, 
                            $rules['allow_special'] ?? false
                        );
                        break;
                        
                    case 'email':
                        $result = self::validateEmail($value);
                        break;
                        
                    case 'phone':
                        $result = self::validatePhone($value);
                        break;
                        
                    case 'number':
                        $result = self::validateNumber(
                            $value, 
                            $rules['min'] ?? null, 
                            $rules['max'] ?? null, 
                            $rules['decimal'] ?? true
                        );
                        break;
                        
                    case 'date':
                        $result = self::validateDate($value, $rules['format'] ?? 'Y-m-d');
                        break;
                        
                    case 'file':
                        $result = self::validateFile(
                            $value, 
                            $rules['allowed_types'] ?? ['jpg', 'jpeg', 'png', 'gif', 'pdf'], 
                            $rules['max_size'] ?? 5242880
                        );
                        break;
                        
                    case 'price':
                        $result = self::validatePrice($value);
                        break;
                        
                    case 'area':
                        $result = self::validateArea($value);
                        break;
                        
                    case 'property_type':
                        $result = self::validatePropertyType($value);
                        break;
                        
                    case 'property_status':
                        $result = self::validatePropertyStatus($value);
                        break;
                        
                    case 'permissions':
                        $result = self::validatePermissions($value);
                        break;
                        
                    case 'username':
                        $result = self::validateUsername($value);
                        break;
                        
                    case 'password':
                        $result = self::validatePassword($value);
                        break;
                        
                    case 'html':
                        $result = ['valid' => true, 'value' => self::sanitizeHTML($value)];
                        break;
                        
                    default:
                        $result = self::validateText($value);
                }
                
                if (!$result['valid']) {
                    $errors[$field] = $result['error'];
                } else {
                    $validatedData[$field] = $result['value'];
                }
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => $validatedData
        ];
    }
}
