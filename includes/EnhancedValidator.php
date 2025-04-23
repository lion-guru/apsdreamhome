<?php
/**
 * Enhanced Validator Class
 * Provides comprehensive input validation for forms across the APS Dream Homes application.
 * This extends the existing Validator class with additional functionality.
 */
class EnhancedValidator {
    // Validation errors
    private $errors = [];
    
    // Input data
    private $data = [];
    
    // Database connection for unique checks
    private $db = null;
    
    /**
     * Constructor
     * @param array $data Input data to validate
     * @param mysqli $db Database connection for unique checks (optional)
     */
    public function __construct(array $data = [], $db = null) {
        $this->data = $data;
        $this->db = $db ?: DatabaseConfig::getConnection();
    }
    
    /**
     * Set data to validate
     * @param array $data Input data to validate
     * @return EnhancedValidator This instance for method chaining
     */
    public function setData(array $data) {
        $this->data = $data;
        return $this;
    }
    
    /**
     * Get all validation errors
     * @return array Validation errors
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Check if validation passed
     * @return bool True if validation passed, false otherwise
     */
    public function passes() {
        return empty($this->errors);
    }
    
    /**
     * Check if validation failed
     * @return bool True if validation failed, false otherwise
     */
    public function fails() {
        return !$this->passes();
    }
    
    /**
     * Get formatted error message for display
     * @param string $type Error message type (alert-danger, alert-warning, etc.)
     * @return string Formatted error message
     */
    public function getFormattedErrors($type = 'alert-danger') {
        if (empty($this->errors)) {
            return '';
        }
        
        $html = "<div class='alert {$type}'>";
        $html .= "<p><strong>Form Validation Failed!</strong></p>";
        $html .= "<ul>";
        
        foreach ($this->errors as $error) {
            $html .= "<li>{$error}</li>";
        }
        
        $html .= "</ul>";
        $html .= "</div>";
        
        return $html;
    }
    
    /**
     * Validate required fields
     * @param array $fields Fields to validate
     * @return EnhancedValidator This instance for method chaining
     */
    public function required(array $fields) {
        foreach ($fields as $field => $label) {
            if (!isset($this->data[$field]) || trim($this->data[$field]) === '') {
                $this->errors[] = "{$label} is required";
            }
        }
        
        return $this;
    }
    
    /**
     * Validate email fields
     * @param array $fields Fields to validate
     * @return EnhancedValidator This instance for method chaining
     */
    public function email(array $fields) {
        foreach ($fields as $field => $label) {
            if (isset($this->data[$field]) && trim($this->data[$field]) !== '') {
                if (!filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
                    $this->errors[] = "{$label} must be a valid email address";
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Validate phone fields (10 digits)
     * @param array $fields Fields to validate
     * @return EnhancedValidator This instance for method chaining
     */
    public function phone(array $fields) {
        foreach ($fields as $field => $label) {
            if (isset($this->data[$field]) && trim($this->data[$field]) !== '') {
                if (!preg_match('/^[0-9]{10}$/', $this->data[$field])) {
                    $this->errors[] = "{$label} must be a valid 10-digit phone number";
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Validate minimum length
     * @param array $fields Fields to validate with minimum length
     * @return EnhancedValidator This instance for method chaining
     */
    public function minLength(array $fields) {
        foreach ($fields as $field => $config) {
            $label = $config['label'];
            $min = $config['min'];
            
            if (isset($this->data[$field]) && trim($this->data[$field]) !== '') {
                if (strlen($this->data[$field]) < $min) {
                    $this->errors[] = "{$label} must be at least {$min} characters long";
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Validate maximum length
     * @param array $fields Fields to validate with maximum length
     * @return EnhancedValidator This instance for method chaining
     */
    public function maxLength(array $fields) {
        foreach ($fields as $field => $config) {
            $label = $config['label'];
            $max = $config['max'];
            
            if (isset($this->data[$field]) && trim($this->data[$field]) !== '') {
                if (strlen($this->data[$field]) > $max) {
                    $this->errors[] = "{$label} must not exceed {$max} characters";
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Validate numeric fields
     * @param array $fields Fields to validate
     * @return EnhancedValidator This instance for method chaining
     */
    public function numeric(array $fields) {
        foreach ($fields as $field => $label) {
            if (isset($this->data[$field]) && trim($this->data[$field]) !== '') {
                if (!is_numeric($this->data[$field])) {
                    $this->errors[] = "{$label} must be a number";
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Validate numeric range
     * @param array $fields Fields to validate with range
     * @return EnhancedValidator This instance for method chaining
     */
    public function numericRange(array $fields) {
        foreach ($fields as $field => $config) {
            $label = $config['label'];
            $min = $config['min'] ?? null;
            $max = $config['max'] ?? null;
            
            if (isset($this->data[$field]) && trim($this->data[$field]) !== '') {
                if (!is_numeric($this->data[$field])) {
                    $this->errors[] = "{$label} must be a number";
                    continue;
                }
                
                $value = (float) $this->data[$field];
                
                if ($min !== null && $value < $min) {
                    $this->errors[] = "{$label} must be at least {$min}";
                }
                
                if ($max !== null && $value > $max) {
                    $this->errors[] = "{$label} must not exceed {$max}";
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Validate password strength
     * @param array $fields Fields to validate
     * @return EnhancedValidator This instance for method chaining
     */
    public function password(array $fields) {
        foreach ($fields as $field => $config) {
            $label = $config['label'];
            $min = $config['min'] ?? 8;
            
            if (isset($this->data[$field]) && trim($this->data[$field]) !== '') {
                $password = $this->data[$field];
                
                if (strlen($password) < $min) {
                    $this->errors[] = "{$label} must be at least {$min} characters long";
                }
                
                if (!preg_match('/[A-Z]/', $password)) {
                    $this->errors[] = "{$label} must contain at least one uppercase letter";
                }
                
                if (!preg_match('/[a-z]/', $password)) {
                    $this->errors[] = "{$label} must contain at least one lowercase letter";
                }
                
                if (!preg_match('/[0-9]/', $password)) {
                    $this->errors[] = "{$label} must contain at least one number";
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Validate password confirmation
     * @param string $password Password field
     * @param string $confirmation Confirmation field
     * @param string $label Field label
     * @return EnhancedValidator This instance for method chaining
     */
    public function passwordConfirmation($password, $confirmation, $label = 'Password') {
        if (isset($this->data[$password]) && isset($this->data[$confirmation])) {
            if ($this->data[$password] !== $this->data[$confirmation]) {
                $this->errors[] = "{$label} confirmation does not match";
            }
        }
        
        return $this;
    }
    
    /**
     * Validate date fields
     * @param array $fields Fields to validate
     * @param string $format Date format
     * @return EnhancedValidator This instance for method chaining
     */
    public function date(array $fields, $format = 'Y-m-d') {
        foreach ($fields as $field => $label) {
            if (isset($this->data[$field]) && trim($this->data[$field]) !== '') {
                $date = $this->data[$field];
                $d = DateTime::createFromFormat($format, $date);
                
                if (!$d || $d->format($format) !== $date) {
                    $this->errors[] = "{$label} must be a valid date in format {$format}";
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Validate unique fields in database
     * @param array $fields Fields to validate
     * @return EnhancedValidator This instance for method chaining
     */
    public function unique(array $fields) {
        if (!$this->db) {
            $this->errors[] = "Database connection required for unique validation";
            return $this;
        }
        
        foreach ($fields as $field => $config) {
            $table = $config['table'];
            $column = $config['column'];
            $label = $config['label'];
            $except_id = $config['except_id'] ?? null;
            $except_column = $config['except_column'] ?? 'id';
            
            if (isset($this->data[$field]) && trim($this->data[$field]) !== '') {
                $value = $this->data[$field];
                
                $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";
                $params = [$value];
                $types = 's';
                
                if ($except_id !== null) {
                    $sql .= " AND {$except_column} != ?";
                    $params[] = $except_id;
                    $types .= 's';
                }                
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                
                if ($row['count'] > 0) {
                    $this->errors[] = "{$label} is already taken";
                }
            }
        }
        
        return $this;
    }
}
