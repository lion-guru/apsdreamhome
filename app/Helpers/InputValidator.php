<?php
/**
 * Input Validation Helper
 * 
 * Centralized input validation for all controllers
 * Provides common validation rules and sanitization
 */

namespace App\Helpers;

class InputValidator
{
    private $errors = [];
    private $data = [];
    private $sanitized = [];

    /**
     * Create validator with input data
     */
    public static function make(array $data): self
    {
        $validator = new self();
        $validator->data = $data;
        return $validator;
    }

    /**
     * Validate required field
     */
    public function required(string $field, string $label = null): self
    {
        $label = $label ?? $field;
        $value = trim($this->data[$field] ?? '');
        
        if (empty($value)) {
            $this->errors[] = "{$label} is required.";
        }
        
        return $this;
    }

    /**
     * Validate email
     */
    public function email(string $field, string $label = null): self
    {
        $label = $label ?? $field;
        $value = trim($this->data[$field] ?? '');
        
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "{$label} must be a valid email address.";
        }
        
        return $this;
    }

    /**
     * Validate phone (Indian format)
     */
    public function phone(string $field, string $label = null): self
    {
        $label = $label ?? $field;
        $value = trim($this->data[$field] ?? '');
        
        if (!empty($value)) {
            $value = preg_replace('/[^0-9]/', '', $value);
            if (strlen($value) < 10 || strlen($value) > 15) {
                $this->errors[] = "{$label} must be a valid phone number.";
            }
        }
        
        return $this;
    }

    /**
     * Validate minimum length
     */
    public function minLength(string $field, int $min, string $label = null): self
    {
        $label = $label ?? $field;
        $value = $this->data[$field] ?? '';
        
        if (strlen($value) < $min) {
            $this->errors[] = "{$label} must be at least {$min} characters.";
        }
        
        return $this;
    }

    /**
     * Validate maximum length
     */
    public function maxLength(string $field, int $max, string $label = null): self
    {
        $label = $label ?? $field;
        $value = $this->data[$field] ?? '';
        
        if (strlen($value) > $max) {
            $this->errors[] = "{$label} must not exceed {$max} characters.";
        }
        
        return $this;
    }

    /**
     * Validate numeric value
     */
    public function numeric(string $field, string $label = null): self
    {
        $label = $label ?? $field;
        $value = $this->data[$field] ?? '';
        
        if (!empty($value) && !is_numeric($value)) {
            $this->errors[] = "{$label} must be a numeric value.";
        }
        
        return $this;
    }

    /**
     * Validate minimum value
     */
    public function minValue(string $field, float $min, string $label = null): self
    {
        $label = $label ?? $field;
        $value = $this->data[$field] ?? 0;
        
        if (is_numeric($value) && $value < $min) {
            $this->errors[] = "{$label} must be at least {$min}.";
        }
        
        return $this;
    }

    /**
     * Validate PAN number (Indian)
     */
    public function pan(string $field, string $label = null): self
    {
        $label = $label ?? $field;
        $value = trim($this->data[$field] ?? '');
        
        if (!empty($value) && !preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', strtoupper($value))) {
            $this->errors[] = "{$label} must be a valid PAN number.";
        }
        
        return $this;
    }

    /**
     * Validate Aadhaar number (Indian)
     */
    public function aadhaar(string $field, string $label = null): self
    {
        $label = $label ?? $field;
        $value = preg_replace('/[^0-9]/', '', $this->data[$field] ?? '');
        
        if (!empty($value) && strlen($value) !== 12) {
            $this->errors[] = "{$label} must be a valid 12-digit Aadhaar number.";
        }
        
        return $this;
    }

    /**
     * Validate IFSC code (Indian bank)
     */
    public function ifsc(string $field, string $label = null): self
    {
        $label = $label ?? $field;
        $value = trim($this->data[$field] ?? '');
        
        if (!empty($value) && !preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/', strtoupper($value))) {
            $this->errors[] = "{$label} must be a valid IFSC code.";
        }
        
        return $this;
    }

    /**
     * Validate date format
     */
    public function date(string $field, string $format = 'Y-m-d', string $label = null): self
    {
        $label = $label ?? $field;
        $value = trim($this->data[$field] ?? '');
        
        if (!empty($value)) {
            $d = \DateTime::createFromFormat($format, $value);
            if (!$d || $d->format($format) !== $value) {
                $this->errors[] = "{$label} must be a valid date ({$format}).";
            }
        }
        
        return $this;
    }

    /**
     * Validate against custom regex
     */
    public function regex(string $field, string $pattern, string $message): self
    {
        $value = $this->data[$field] ?? '';
        
        if (!empty($value) && !preg_match($pattern, $value)) {
            $this->errors[] = $message;
        }
        
        return $this;
    }

    /**
     * Validate value is in allowed list
     */
    public function in(string $field, array $allowed, string $label = null): self
    {
        $label = $label ?? $field;
        $value = $this->data[$field] ?? '';
        
        if (!empty($value) && !in_array($value, $allowed)) {
            $this->errors[] = "{$label} must be one of: " . implode(', ', $allowed) . ".";
        }
        
        return $this;
    }

    /**
     * Validate pincode (Indian)
     */
    public function pincode(string $field, string $label = null): self
    {
        $label = $label ?? $field;
        $value = preg_replace('/[^0-9]/', '', $this->data[$field] ?? '');
        
        if (!empty($value) && strlen($value) !== 6) {
            $this->errors[] = "{$label} must be a valid 6-digit pincode.";
        }
        
        return $this;
    }

    /**
     * Check if validation passed
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Check if validation failed
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Get all errors
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Get first error
     */
    public function firstError(): string
    {
        return $this->errors[0] ?? '';
    }

    /**
     * Get sanitized data
     */
    public function sanitized(): array
    {
        return $this->sanitized;
    }

    /**
     * Sanitize string input
     */
    public static function sanitizeString(string $input): string
    {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize email
     */
    public static function sanitizeEmail(string $input): string
    {
        return filter_var(trim($input), FILTER_SANITIZE_EMAIL);
    }

    /**
     * Sanitize integer
     */
    public static function sanitizeInt($input): int
    {
        return (int) filter_var($input, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Sanitize float
     */
    public static function sanitizeFloat($input): float
    {
        return (float) filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /**
     * Sanitize all POST data
     */
    public static function sanitizePost(): array
    {
        $sanitized = [];
        foreach ($_POST as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = self::sanitizeString($value);
            } elseif (is_array($value)) {
                $sanitized[$key] = array_map([self::class, 'sanitizeString'], $value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }

    /**
     * Get sanitized value from data
     */
    public function getValue(string $field, $default = null)
    {
        $value = $this->data[$field] ?? $default;
        if (is_string($value)) {
            return self::sanitizeString($value);
        }
        return $value;
    }

    /**
     * Get integer value
     */
    public function getInt(string $field, int $default = 0): int
    {
        return isset($this->data[$field]) ? (int) $this->data[$field] : $default;
    }

    /**
     * Get float value
     */
    public function getFloat(string $field, float $default = 0.0): float
    {
        return isset($this->data[$field]) ? (float) $this->data[$field] : $default;
    }
}
