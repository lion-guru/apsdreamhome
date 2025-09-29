<?php
/**
 * Comprehensive Validation and Sanitization Library
 * Provides robust input validation, sanitization, and data integrity checks
 */

// require_once __DIR__ . '/logger.php';
require_once __DIR__ . '/config_manager.php';

class Validator {
    private $errors = [];
    private $logger;
    private $config; // lazy-loaded

    // Helper to get config manager instance
    protected function getConfig() {
        if ($this->config === null) {
            $this->config = \ConfigManager::getInstance();
        }
        return $this->config;
    }

    // Predefined validation rules
    private $rules = [
        'required' => '/\S+/',
        'email' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
        'phone' => '/^(\+\d{1,3}[- ]?)?\d{10}$/',
        'url' => '/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/',
        'alpha' => '/^[a-zA-Z]+$/',
        'alphanumeric' => '/^[a-zA-Z0-9]+$/',
        'numeric' => '/^[0-9]+$/',
        'date' => '/^\d{4}-\d{2}-\d{2}$/',
    ];

    /**
     * Constructor
     */
    public function __construct() {
        $this->logger = null;
        // Removed ConfigManager::getInstance() to break recursion
    }

    /**
     * Validate input against multiple rules
     * 
     * @param mixed $input Input to validate
     * @param array $validationRules Validation rules
     * @return bool Whether input passes all validations
     */
    public function validate($input, $validationRules) {
        // Reset errors
        $this->errors = [];

        // Handle array inputs
        if (is_array($input)) {
            $isValid = true;
            foreach ($input as $key => $value) {
                if (isset($validationRules[$key])) {
                    $fieldValid = $this->validateField($key, $value, $validationRules[$key]);
                    $isValid = $isValid && $fieldValid;
                }
            }
            return $isValid;
        }

        // Single value validation
        return $this->validateField('input', $input, $validationRules);
    }

    /**
     * Validate a single field
     * 
     * @param string $fieldName Field name
     * @param mixed $value Field value
     * @param array|string $rules Validation rules
     * @return bool Whether field passes validation
     */
    private function validateField($fieldName, $value, $rules) {
        // Normalize rules to array
        $rules = is_string($rules) ? explode('|', $rules) : $rules;

        foreach ($rules as $rule) {
            // Parse rule with parameters
            $parsedRule = $this->parseRule($rule);
            $ruleName = $parsedRule['rule'];
            $params = $parsedRule['params'];

            // Skip validation for null values in non-required fields
            if ($ruleName !== 'required' && ($value === null || $value === '')) {
                continue;
            }

            // Validate based on rule type
            $isValid = match($ruleName) {
                'required' => $this->validateRequired($value),
                'email' => $this->validateEmail($value),
                'phone' => $this->validatePhone($value),
                'url' => $this->validateUrl($value),
                'alpha' => $this->validateAlpha($value),
                'alphanumeric' => $this->validateAlphanumeric($value),
                'numeric' => $this->validateNumeric($value),
                'min' => $this->validateMin($value, $params[0]),
                'max' => $this->validateMax($value, $params[0]),
                'between' => $this->validateBetween($value, $params[0], $params[1]),
                'regex' => $this->validateRegex($value, $params[0]),
                'date' => $this->validateDate($value),
                default => $this->validateCustomRule($ruleName, $value, $params)
            };

            // Add error if validation fails
            if (!$isValid) {
                $this->addError($fieldName, $ruleName, $params);
                return false;
            }
        }

        return true;
    }

    /**
     * Parse validation rule with potential parameters
     * 
     * @param string $rule Rule to parse
     * @return array Parsed rule details
     */
    private function parseRule($rule) {
        $parts = explode(':', $rule, 2);
        return [
            'rule' => $parts[0],
            'params' => isset($parts[1]) ? explode(',', $parts[1]) : []
        ];
    }

    /**
     * Add validation error
     * 
     * @param string $field Field name
     * @param string $rule Validation rule
     * @param array $params Rule parameters
     */
    private function addError($field, $rule, $params = []) {
        $this->errors[] = [
            'field' => $field,
            'rule' => $rule,
            'params' => $params
        ];

        // Log validation error
        // $this->logger->warning('Validation Error', [...]);
    }

    /**
     * Get validation errors
     * 
     * @return array Validation errors
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Specific validation methods
     */
    private function validateRequired($value) {
        return $value !== null && $value !== '';
    }

    private function validateEmail($value) {
        return preg_match($this->rules['email'], $value) === 1;
    }

    private function validatePhone($value) {
        return preg_match($this->rules['phone'], $value) === 1;
    }

    private function validateUrl($value) {
        return preg_match($this->rules['url'], $value) === 1;
    }

    private function validateAlpha($value) {
        return preg_match($this->rules['alpha'], $value) === 1;
    }

    private function validateAlphanumeric($value) {
        return preg_match($this->rules['alphanumeric'], $value) === 1;
    }

    private function validateNumeric($value) {
        return is_numeric($value);
    }

    private function validateMin($value, $min) {
        return is_numeric($value) && $value >= $min;
    }

    private function validateMax($value, $max) {
        return is_numeric($value) && $value <= $max;
    }

    private function validateBetween($value, $min, $max) {
        return is_numeric($value) && $value >= $min && $value <= $max;
    }

    private function validateRegex($value, $pattern) {
        return preg_match($pattern, $value) === 1;
    }

    private function validateDate($value) {
        return preg_match($this->rules['date'], $value) === 1 && strtotime($value) !== false;
    }

    /**
     * Custom rule validation (extensible)
     * 
     * @param string $ruleName Custom rule name
     * @param mixed $value Value to validate
     * @param array $params Rule parameters
     * @return bool Validation result
     */
    private function validateCustomRule($ruleName, $value, $params) {
        // Allow custom validation via callback or predefined rules
        $customRules = $this->config->get('CUSTOM_VALIDATION_RULES', []);
        
        if (isset($customRules[$ruleName])) {
            $callback = $customRules[$ruleName];
            return $callback($value, ...$params);
        }

        // Log unknown validation rule
        // $this->logger->warning('Unknown Validation Rule', [
        //     'rule' => $ruleName
        // ]);

        return false;
    }

    /**
     * Sanitization methods
     */
    public function sanitize($input, $type = 'string') {
        return match($type) {
            'email' => filter_var($input, FILTER_SANITIZE_EMAIL),
            'url' => filter_var($input, FILTER_SANITIZE_URL),
            'int' => filter_var($input, FILTER_SANITIZE_NUMBER_INT),
            'float' => filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
            'string' => htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8'),
            default => $input
        };
    }

    /**
     * Advanced data type conversion
     * 
     * @param mixed $value Value to convert
     * @param string $type Target type
     * @return mixed Converted value
     */
    public function convert($value, $type) {
        return match($type) {
            'int' => (int)$value,
            'float' => (float)$value,
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'string' => (string)$value,
            'array' => (array)$value,
            default => $value
        };
    }
}

// Global validator function
function validator() {
    static $validatorInstance = null;
    if ($validatorInstance === null) {
        $validatorInstance = new Validator();
    }
    return $validatorInstance;
}

