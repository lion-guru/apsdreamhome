<?php
/**
 * Input Validation & Sanitization Helper
 * Prevents XSS, SQL Injection, and other injection attacks
 */
class InputValidator {
    /**
     * Sanitize string for HTML output (prevents XSS)
     */
    public static function sanitize($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Sanitize input for database (SQL Injection prevention)
     * Use with prepared statements
     */
    public static function sanitizeForDB($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeForDB'], $input);
        }
        // Remove dangerous characters
        return filter_var($input, FILTER_SANITIZE_STRING);
    }

    /**
     * Validate email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate integer
     */
    public static function validateInt($value, $min = null, $max = null) {
        $val = filter_var($value, FILTER_VALIDATE_INT);
        if ($val === false) return false;
        if ($min !== null && $val < $min) return false;
        if ($max !== null && $val > $max) return false;
        return $val;
    }

    /**
     * Validate URL
     */
    public static function validateUrl($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validate input with rules
     */
    public static function validate($input, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $input[$field] ?? null;
            $rules_list = explode('|', $rule);
            
            foreach ($rules_list as $r) {
                if ($r === 'required' && (empty($value) || $value === null)) {
                    $errors[$field][] = "The {$field} field is required.";
                }
                if ($r === 'email' && !empty($value) && !self::validateEmail($value)) {
                    $errors[$field][] = "The {$field} must be a valid email.";
                }
                if ($r === 'int' && !empty($value) && !self::validateInt($value)) {
                    $errors[$field][] = "The {$field} must be an integer.";
                }
                if (str_starts_with($r, 'max:') && !empty($value)) {
                    $max = (int)substr($r, 4);
                    if (strlen($value) > $max) {
                        $errors[$field][] = "The {$field} may not be greater than {$max} characters.";
                    }
                }
            }
        }
        
        return $errors;
    }
}
?>
