<?php

namespace App\Http\Controllers\Traits;

/**
 * Trait HasInputHandling
 *
 * Provides input validation, sanitization, and request data access methods.
 * Include this trait in controllers that handle form data.
 */
trait HasInputHandling
{
    /**
     * Create InputValidator for form data.
     */
    protected function validate(array $data = null): \App\Helpers\InputValidator
    {
        return \App\Helpers\InputValidator::make($data ?? $_POST);
    }

    /**
     * Sanitize all POST data.
     */
    protected function sanitizePost(): array
    {
        return \App\Helpers\InputValidator::sanitizePost();
    }

    /**
     * Get sanitized input value.
     */
    protected function input(string $key, $default = null)
    {
        $value = $_POST[$key] ?? $default;
        if (is_string($value)) {
            return \App\Helpers\InputValidator::sanitizeString($value);
        }
        return $value;
    }

    /**
     * Get integer input.
     */
    protected function inputInt(string $key, int $default = 0): int
    {
        return isset($_POST[$key]) ? (int) $_POST[$key] : $default;
    }

    /**
     * Get float input.
     */
    protected function inputFloat(string $key, float $default = 0.0): float
    {
        return isset($_POST[$key]) ? (float) $_POST[$key] : $default;
    }

    /**
     * Get request method.
     */
    protected function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    /**
     * Get POST data.
     */
    protected function post($key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }

    /**
     * Get GET data.
     */
    protected function getLocal($key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }

    /**
     * Get request files.
     */
    protected function files($key = null)
    {
        if ($key === null) {
            return $_FILES;
        }
        return $_FILES[$key] ?? null;
    }

    /**
     * Sanitize input (alias for security).
     */
    protected function sanitize($input)
    {
        if (is_string($input)) {
            return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }
        return $input;
    }

    /**
     * Sanitize input (alias).
     */
    protected function sanitizeInput($input)
    {
        return $this->sanitize($input);
    }

    /**
     * Sanitize input (alias).
     */
    protected function sanitizeLocal($input)
    {
        return $this->sanitize($input);
    }

    /**
     * Validate email.
     */
    protected function validateEmail($email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate phone (10 digits).
     */
    protected function validatePhone($phone): bool
    {
        return preg_match('/^[0-9]{10}$/', $phone);
    }

    /**
     * Validate and sanitize input data against rules.
     * Returns [$sanitized, $errors].
     */
    protected function validateInput(array $data, array $rules): array
    {
        $errors = [];
        $sanitized = [];

        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            $rulesList = is_string($rule) ? explode('|', $rule) : $rule;
            $valid = true;

            foreach ($rulesList as $r) {
                if ($r === 'required' && ($value === null || $value === '')) {
                    $errors[$field][] = "The {$field} field is required.";
                    $valid = false;
                    break;
                }
                if ($r === 'email' && $value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = "The {$field} must be a valid email address.";
                    $valid = false;
                    break;
                }
                if ($r === 'numeric' && $value !== null && $value !== '' && !is_numeric($value)) {
                    $errors[$field][] = "The {$field} must be a number.";
                    $valid = false;
                    break;
                }
                if ($r === 'integer' && $value !== null && $value !== '' && !ctype_digit((string)$value)) {
                    $errors[$field][] = "The {$field} must be an integer.";
                    $valid = false;
                    break;
                }
                if (preg_match('/min:(\d+)/', $r, $m) && $value !== null && strlen($value) < (int)$m[1]) {
                    $errors[$field][] = "The {$field} must be at least {$m[1]} characters.";
                    $valid = false;
                    break;
                }
                if (preg_match('/max:(\d+)/', $r, $m) && $value !== null && strlen($value) > (int)$m[1]) {
                    $errors[$field][] = "The {$field} may not exceed {$m[1]} characters.";
                    $valid = false;
                    break;
                }
            }

            if ($valid) {
                if (in_array('email', $rulesList) && $value !== null && $value !== '') {
                    $sanitized[$field] = filter_var($value, FILTER_SANITIZE_EMAIL);
                } elseif (in_array('integer', $rulesList) && $value !== null) {
                    $sanitized[$field] = (int)$value;
                } elseif (in_array('numeric', $rulesList) && $value !== null) {
                    $sanitized[$field] = (float)$value;
                } elseif ($value !== null && is_string($value)) {
                    $sanitized[$field] = \App\Core\Security::sanitize($value);
                } else {
                    $sanitized[$field] = $value;
                }
            }
        }

        return [$sanitized, $errors];
    }

    /**
     * Return JSON validation error and exit.
     */
    protected function validationError(array $errors): void
    {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Validation failed', 'errors' => $errors]);
        exit;
    }
}