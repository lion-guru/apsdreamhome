<?php
/**
 * Advanced Validation Example
 * Demonstrates complex validation scenarios for user registration
 */

require_once __DIR__ . '/validator.php';
require_once __DIR__ . '/logger.php';
require_once __DIR__ . '/security_middleware.php';

class AdvancedUserValidator {
    private $validator;
    private $logger;
    private $securityMiddleware;

    public function __construct() {
        $this->validator = validator();
        $this->logger = new Logger();
        $this->securityMiddleware = new SecurityMiddleware();
    }

    /**
     * Comprehensive user registration validation
     * 
     * @param array $userData User registration data
     * @return array Validation result
     */
    public function validateUserRegistration($userData) {
        // Advanced validation rules
        $validationRules = [
            // Personal Information
            'first_name' => 'required|alpha|min:2|max:50',
            'last_name' => 'required|alpha|min:2|max:50',
            
            // Contact Information
            'email' => [
                'rule' => 'required|email',
                'custom' => [$this, 'validateUniqueEmail']
            ],
            'phone' => 'required|phone|unique_phone',
            
            // Account Credentials
            'password' => [
                'rule' => 'required|strong_password',
                'custom' => [$this, 'validatePasswordStrength']
            ],
            'confirm_password' => 'required|match:password',
            
            // Additional Validation
            'age' => 'required|numeric|min:18|max:120',
            'terms_accepted' => 'required|bool|equals:true'
        ];

        // Sanitize and validate input
        $sanitizedData = $this->securityMiddleware->validateInput($userData);
        
        if ($sanitizedData === false) {
            return [
                'success' => false,
                'errors' => $this->validator->getErrors()
            ];
        }

        // Validate each field with custom rules
        $validationResults = $this->performDetailedValidation($sanitizedData, $validationRules);

        // Return validation result
        return $validationResults;
    }

    /**
     * Perform detailed validation with custom rules
     * 
     * @param array $data Sanitized input data
     * @param array $rules Validation rules
     * @return array Validation results
     */
    private function performDetailedValidation($data, $rules) {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            // Normalize rules to array
            $fieldRules = is_array($fieldRules) ? $fieldRules : ['rule' => $fieldRules];
            
            // Standard validation
            $standardRules = $fieldRules['rule'] ?? '';
            if (!$this->validator->validate([$field => $data[$field]], [$field => $standardRules])) {
                $errors[$field] = $this->validator->getErrors();
                continue;
            }

            // Custom validation
            if (isset($fieldRules['custom'])) {
                $customValidator = $fieldRules['custom'];
                $customResult = call_user_func($customValidator, $data[$field], $data);
                
                if ($customResult !== true) {
                    $errors[$field][] = $customResult;
                }
            }
        }

        // Log validation errors
        if (!empty($errors)) {
            $this->logger->warning('User Registration Validation Failed', [
                'errors' => $errors,
                'input' => array_keys($data)
            ]);

            return [
                'success' => false,
                'errors' => $errors
            ];
        }

        return [
            'success' => true,
            'data' => $data
        ];
    }

    /**
     * Validate unique email (custom validation)
     * 
     * @param string $email Email to validate
     * @return bool|string Validation result
     */
    public function validateUniqueEmail($email) {
        // Simulate database check (replace with actual database query)
        $existingEmails = [
            'existing@example.com',
            'admin@apsdreamhomefinal.com'
        ];

        if (in_array(strtolower($email), array_map('strtolower', $existingEmails))) {
            return 'Email already registered';
        }

        return true;
    }

    /**
     * Advanced password strength validation
     * 
     * @param string $password Password to validate
     * @param array $userData Full user data
     * @return bool|string Validation result
     */
    public function validatePasswordStrength($password, $userData) {
        $checks = [
            'length' => strlen($password) >= 12,
            'uppercase' => preg_match('/[A-Z]/', $password),
            'lowercase' => preg_match('/[a-z]/', $password),
            'number' => preg_match('/[0-9]/', $password),
            'special' => preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password),
            'not_common' => !in_array(strtolower($password), [
                '123456', 'password', 'qwerty', 'admin'
            ]),
            'not_personal_info' => (
                strpos(strtolower($password), strtolower($userData['first_name'] ?? '')) === false &&
                strpos(strtolower($password), strtolower($userData['last_name'] ?? '')) === false &&
                strpos(strtolower($password), strtolower($userData['email'] ?? '')) === false
            )
        ];

        $failedChecks = array_keys(array_filter($checks, function($check) {
            return $check === false;
        }));

        if (!empty($failedChecks)) {
            return 'Password does not meet strength requirements: ' . implode(', ', $failedChecks);
        }

        return true;
    }

    /**
     * Demonstrate advanced validation usage
     */
    public function demonstrateValidation() {
        // Example valid registration data
        $validUserData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'johndoe@example.com',
            'phone' => '+1234567890',
            'password' => 'StrongP@ssw0rd2023!',
            'confirm_password' => 'StrongP@ssw0rd2023!',
            'age' => 30,
            'terms_accepted' => true
        ];

        // Example invalid registration data
        $invalidUserData = [
            'first_name' => 'J0hn123',  // Invalid name
            'last_name' => 'D0e',       // Invalid name
            'email' => 'invalid-email', // Invalid email
            'phone' => 'not a phone',   // Invalid phone
            'password' => 'weak',       // Weak password
            'confirm_password' => 'different',  // Mismatched password
            'age' => 15,                // Underage
            'terms_accepted' => false   // Terms not accepted
        ];

        // Validate valid data
        echo "Valid User Data Validation:\n";
        $validResult = $this->validateUserRegistration($validUserData);
        print_r($validResult);

        echo "\nInvalid User Data Validation:\n";
        $invalidResult = $this->validateUserRegistration($invalidUserData);
        print_r($invalidResult);
    }
}

// Run demonstration
$userValidator = new AdvancedUserValidator();
$userValidator->demonstrateValidation();
