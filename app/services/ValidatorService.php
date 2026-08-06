<?php

namespace App\Services;

/**
 * Validator Service
 * Input validation and sanitization service
 */
class ValidatorService
{
    private $data;
    private $errors = [];

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    public function validateRequired($fields)
    {
        foreach ($fields as $field) {
            if (empty($this->data[$field])) {
                $this->errors[$field] = ucfirst($field) . ' is required';
            }
        }
        return empty($this->errors);
    }

    public function validateEmail($field)
    {
        if (!empty($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = 'Please enter a valid email address';
        }
        return empty($this->errors);
    }

    public function validatePhone($field)
    {
        if (!empty($this->data[$field]) && !preg_match('/^\d{10}$/', $this->data[$field])) {
            $this->errors[$field] = 'Please enter a valid 10-digit phone number';
        }
        return empty($this->errors);
    }

    public function validateMinLength($field, $length)
    {
        if (!empty($this->data[$field]) && strlen($this->data[$field]) < $length) {
            $this->errors[$field] = ucfirst($field) . " must be at least $length characters";
        }
        return empty($this->errors[$field]);
    }

    public function validateMatch($field1, $field2)
    {
        $val1 = $this->data[$field1] ?? null;
        $val2 = $this->data[$field2] ?? null;
        if ($val1 !== $val2) {
            $this->errors[$field2] = 'Passwords do not match';
        }
        return empty($this->errors[$field2]);
    }

    public function sanitizeInput($input, $type = 'string')
    {
        if ($type === 'email') {
            return filter_var(trim($input), FILTER_SANITIZE_EMAIL);
        }
        if ($type === 'int') {
            return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
        }
        if ($type === 'float') {
            return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate JSON API Payload against a schema of rules
     * e.g., $rules = ['email' => 'required|email', 'phone' => 'required|phone', 'plot_id' => 'required|int']
     */
    public function validatePayloadSchema(array $data, array $rules): array
    {
        $errors = [];
        $sanitized = [];

        foreach ($rules as $field => $ruleString) {
            $ruleList = explode('|', $ruleString);
            $val = $data[$field] ?? null;

            foreach ($ruleList as $rule) {
                $err = $this->checkRule($field, $val, $rule);
                if ($err !== null) {
                    $errors[$field] = $err;
                    break;
                }
            }

            if (!isset($errors[$field]) && !is_null($val)) {
                $sanitized[$field] = is_string($val) ? htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8') : $val;
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'sanitized' => $sanitized
        ];
    }

    private function checkRule(string $field, $val, string $rule): ?string
    {
        if ($rule === 'required' && (is_null($val) || $val === '')) {
            return ucfirst($field) . ' is required';
        }
        if (is_null($val) || $val === '') {
            return null;
        }

        if ($rule === 'email' && !filter_var($val, FILTER_VALIDATE_EMAIL)) {
            return 'Invalid email address';
        }
        if ($rule === 'phone' && !preg_match('/^\d{10}$/', (string)$val)) {
            return 'Invalid 10-digit phone number';
        }
        if ($rule === 'int' && !filter_var($val, FILTER_VALIDATE_INT) && $val !== 0 && $val !== '0') {
            return ucfirst($field) . ' must be an integer';
        }
        if ($rule === 'numeric' && !is_numeric($val)) {
            return ucfirst($field) . ' must be numeric';
        }

        return null;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function hasErrors()
    {
        return !empty($this->errors);
    }
}

/**
 * Legacy Validator proxy class - redirects calls to the modern Validator Service.
 */
require_once __DIR__ . '/../../../vendor/autoload.php';

class Validator
{
    private $validator;

    public function __construct($data = [])
    {
        $this->validator = new ValidatorService($data);
    }

    public function validateRequired($fields)
    {
        return $this->validator->validateRequired($fields);
    }

    public function validateEmail($field)
    {
        return $this->validator->validateEmail($field);
    }

    public function validatePhone($field)
    {
        return $this->validator->validatePhone($field);
    }

    public function validateMinLength($field, $length)
    {
        return $this->validator->validateMinLength($field, $length);
    }

    public function validateMatch($field1, $field2)
    {
        return $this->validator->validateMatch($field1, $field2);
    }

    public function sanitizeInput($input, $type = 'string')
    {
        return $this->validator->sanitizeInput($input, $type);
    }

    public function getErrors()
    {
        return $this->validator->getErrors();
    }

    public function hasErrors()
    {
        return $this->validator->hasErrors();
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this->validator, $name)) {
            return call_user_func_array([$this->validator, $name], $arguments);
        }
        throw new \Exception("Method {$name} not found in Validator");
    }

    public static function __callStatic($name, $arguments)
    {
        $instance = new self();
        if (method_exists($instance, $name)) {
            return call_user_func_array([$instance, $name], $arguments);
        }
        throw new \Exception("Static method {$name} not found in Validator");
    }
}
