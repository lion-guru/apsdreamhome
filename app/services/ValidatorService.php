<?php

namespace App\Services;

/**
 * Validator Service
 * Input validation and sanitization service
 */
class ValidatorService {
    private $data;
    private $errors = [];
    
    public function __construct($data = []) {
        $this->data = $data;
    }
    
    public function validateRequired($fields) {
        foreach ($fields as $field) {
            if (empty($this->data[$field])) {
                $this->errors[$field] = ucfirst($field) . ' is required';
            }
        }
        return empty($this->errors);
    }
    
    public function validateEmail($field) {
        if (!empty($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = 'Please enter a valid email address';
        }
        return empty($this->errors);
    }
    
    public function validatePhone($field) {
        if (!empty($this->data[$field]) && !preg_match('/^[0-9]{10}$/', $this->data[$field])) {
            $this->errors[$field] = 'Please enter a valid 10-digit phone number';
        }
        return empty($this->errors);
    }
    
    public function sanitizeInput($input, $type = 'string') {
        switch ($type) {
            case 'email':
                return filter_var(trim($input), FILTER_SANITIZE_EMAIL);
            case 'int':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            case 'float':
                return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            case 'string':
            default:
                return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }
    }
    
    public function getErrors() {
        return $this->errors;
    }
    
    public function hasErrors() {
        return !empty($this->errors);
    }
}

/**
 * Legacy Validator proxy class - redirects calls to the modern Validator Service.
 */
require_once __DIR__ . '/../../../vendor/autoload.php';

class Validator {
    private $validator;
    
    public function __construct($data = []) {
        $this->validator = new ValidatorService($data);
    }
    
    public function validateRequired($fields) {
        return $this->validator->validateRequired($fields);
    }
    
    public function validateEmail($field) {
        return $this->validator->validateEmail($field);
    }
    
    public function validatePhone($field) {
        return $this->validator->validatePhone($field);
    }
    
    public function sanitizeInput($input, $type = 'string') {
        return $this->validator->sanitizeInput($input, $type);
    }
    
    public function getErrors() {
        return $this->validator->getErrors();
    }
    
    public function hasErrors() {
        return $this->validator->hasErrors();
    }
    
    public function __call($name, $arguments) {
        if (method_exists($this->validator, $name)) {
            return call_user_func_array([$this->validator, $name], $arguments);
        }
        throw new Exception("Method {$name} not found in Validator");
    }
    
    public static function __callStatic($name, $arguments) {
        $instance = new self();
        if (method_exists($instance, $name)) {
            return call_user_func_array([$instance, $name], $arguments);
        }
        throw new Exception("Static method {$name} not found in Validator");
    }
}