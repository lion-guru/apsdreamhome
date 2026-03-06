<?php

namespace App\Services\Legacy;
// Input Validation and Sanitization Functions

class InputValidator {
    public function __construct() {
        // No longer requires manual database connection
    }

    // Sanitize string input
    public function sanitizeString($input) {
        if (is_string($input)) {
            $sanitized = strip_tags(trim($input));
            // Parameterized queries should be used instead of manual escaping.
            // If manual escaping is absolutely needed, use App::database()->getConnection()->quote()
            return $sanitized;
        }
        return '';
    }

    // Validate email address
    public function validateEmail($email) {
        $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : false;
    }

    // Validate integer input
    public function validateInt($input) {
        $input = filter_var($input, FILTER_SANITIZE_NUMBER_INT);
        return filter_var($input, FILTER_VALIDATE_INT) !== false ? (int)$input : false;
    }

    // Validate float input
    public function validateFloat($input) {
        $input = filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        return filter_var($input, FILTER_VALIDATE_FLOAT) !== false ? (float)$input : false;
    }

    // Validate date format (YYYY-MM-DD)
    public function validateDate($date) {
        $date = trim($date);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $parts = explode('-', $date);
            return checkdate($parts[1], $parts[2], $parts[0]) ? $date : false;
        }
        return false;
    }

    // Validate phone number (basic)
    public function validatePhone($phone) {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        return strlen($phone) >= 10 && strlen($phone) <= 15 ? $phone : false;
    }

    // Validate URL
    public function validateUrl($url) {
        $url = filter_var($url, FILTER_SANITIZE_URL);
        return filter_var($url, FILTER_VALIDATE_URL) ? $url : false;
    }

    // Validate file extension
    public function validateFileExtension($filename, $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']) {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, $allowed_extensions) ? true : false;
    }

    // Validate password strength
    public function validatePassword($password) {
        // At least 8 characters long
        // Contains at least one uppercase letter
        // Contains at least one lowercase letter
        // Contains at least one number
        // Contains at least one special character
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
        return preg_match($pattern, $password) ? true : false;
    }

    // Sanitize array input recursively
    public function sanitizeArray($array) {
        if (!is_array($array)) {
            return $this->sanitizeString($array);
        }
        
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->sanitizeArray($value);
            } else {
                $array[$key] = $this->sanitizeString($value);
            }
        }
        return $array;
    }

    // Validate Indian PAN number
    public function validatePAN($pan) {
        $pattern = '/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/';
        return preg_match($pattern, strtoupper($pan)) ? strtoupper($pan) : false;
    }

    // Validate Indian Aadhaar number
    public function validateAadhaar($aadhaar) {
        $aadhaar = preg_replace('/[^0-9]/', '', $aadhaar);
        return strlen($aadhaar) === 12 ? $aadhaar : false;
    }

    // Validate Indian GST number
    public function validateGST($gst) {
        $pattern = '/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/';
        return preg_match($pattern, strtoupper($gst)) ? strtoupper($gst) : false;
    }
}

// Create global validator instance
try {
    $db = \App\Core\App::database();
    $validator = new InputValidator($db->getConnection());
} catch (Exception $e) {
    // Fallback if needed
}
