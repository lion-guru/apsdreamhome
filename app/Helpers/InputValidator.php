<?php

namespace App\Helpers;

/**
 * Input Validation & Sanitization Helper
 * Provides safe methods for handling user input, form validation, and Indian identity formats (PAN, Aadhaar, IFSC, PIN, phone).
 */
class InputValidator
{
    protected array $data = [];
    protected array $errors = [];
    protected array $validatedData = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Create instance for fluent validation
     */
    public static function make(?array $data = null): self
    {
        return new self($data ?? $_POST ?? []);
    }

    /**
     * Sanitize string for HTML output (prevents XSS)
     */
    public static function sanitize($input)
    {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }
        return htmlspecialchars((string)$input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Sanitize string input
     */
    public static function sanitizeString(?string $input): string
    {
        if ($input === null) {
            return '';
        }
        $cleaned = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', trim($input));
        $cleaned = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $cleaned);
        return htmlspecialchars(strip_tags($cleaned), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize POST array
     */
    public static function sanitizePost(): array
    {
        $sanitized = [];
        foreach ($_POST as $key => $val) {
            if (is_string($val)) {
                $sanitized[$key] = self::sanitizeString($val);
            } elseif (is_array($val)) {
                $sanitized[$key] = self::sanitize($val);
            } else {
                $sanitized[$key] = $val;
            }
        }
        return $sanitized;
    }

    /**
     * Sanitize input for database
     */
    public static function sanitizeForDB($input)
    {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeForDB'], $input);
        }
        return trim(strip_tags((string)$input));
    }

    /**
     * Validate email
     */
    public static function validateEmail(?string $email): bool
    {
        return !empty($email) && filter_var(trim($email), FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate integer
     */
    public static function validateInt($value, ?int $min = null, ?int $max = null): bool
    {
        $val = filter_var($value, FILTER_VALIDATE_INT);
        if ($val === false) {
            return false;
        }
        if ($min !== null && $val < $min) {
            return false;
        }
        if ($max !== null && $val > $max) {
            return false;
        }
        return true;
    }

    /**
     * Validate URL
     */
    public static function validateUrl(?string $url): bool
    {
        return !empty($url) && filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validate 10-digit Indian Mobile Phone
     */
    public static function validatePhone(?string $phone): bool
    {
        if (empty($phone)) {
            return false;
        }
        return (bool)preg_match('/^[6-9]\d{9}$/', trim($phone));
    }

    /**
     * Validate Indian PAN card (ABCDE1234F)
     */
    public static function validatePan(?string $pan): bool
    {
        if (empty($pan)) {
            return false;
        }
        return (bool)preg_match('/^[A-Z]{5}\d{4}[A-Z]$/i', trim($pan));
    }

    /**
     * Validate 12-digit Indian Aadhaar number
     */
    public static function validateAadhaar(?string $aadhaar): bool
    {
        if (empty($aadhaar)) {
            return false;
        }
        $cleaned = preg_replace('/\s+/', '', trim($aadhaar));
        return (bool)preg_match('/^\d{12}$/', $cleaned);
    }

    /**
     * Validate Indian PIN code (6 digits)
     */
    public static function validatePin(?string $pin): bool
    {
        if (empty($pin)) {
            return false;
        }
        return (bool)preg_match('/^[1-9]\d{5}$/', trim($pin));
    }

    /**
     * Validate Indian IFSC code (e.g. SBIN0001234)
     */
    public static function validateIfsc(?string $ifsc): bool
    {
        if (empty($ifsc)) {
            return false;
        }
        return (bool)preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/i', trim($ifsc));
    }

    /**
     * Validate input with rules array
     */
    public static function validate(array $input, array $rules): array
    {
        $validator = new self($input);
        $validator->applyRules($rules);
        return $validator->errors();
    }

    /**
     * Apply rules to dataset
     */
    public function applyRules(array $rules): self
    {
        foreach ($rules as $field => $ruleString) {
            $value = $this->data[$field] ?? null;
            $ruleList = is_array($ruleString) ? $ruleString : explode('|', $ruleString);

            foreach ($ruleList as $r) {
                if ($r === 'required' && ($value === null || $value === '' || (is_array($value) && empty($value)))) {
                    $this->addError($field, "The {$field} field is required.");
                } elseif ($value !== null && $value !== '') {
                    if ($r === 'email' && !self::validateEmail($value)) {
                        $this->addError($field, "The {$field} must be a valid email.");
                    } elseif ($r === 'int' && !self::validateInt($value)) {
                        $this->addError($field, "The {$field} must be an integer.");
                    } elseif ($r === 'phone' && !self::validatePhone($value)) {
                        $this->addError($field, "The {$field} must be a valid 10-digit mobile number.");
                    } elseif ($r === 'pan' && !self::validatePan($value)) {
                        $this->addError($field, "The {$field} must be a valid PAN format (e.g. ABCDE1234F).");
                    } elseif ($r === 'aadhaar' && !self::validateAadhaar($value)) {
                        $this->addError($field, "The {$field} must be a valid 12-digit Aadhaar number.");
                    } elseif ($r === 'pin' && !self::validatePin($value)) {
                        $this->addError($field, "The {$field} must be a valid 6-digit postal PIN code.");
                    } elseif ($r === 'ifsc' && !self::validateIfsc($value)) {
                        $this->addError($field, "The {$field} must be a valid IFSC code.");
                    } elseif (str_starts_with($r, 'min:')) {
                        $min = (int)substr($r, 4);
                        if (strlen((string)$value) < $min) {
                            $this->addError($field, "The {$field} must be at least {$min} characters.");
                        }
                    } elseif (str_starts_with($r, 'max:')) {
                        $max = (int)substr($r, 4);
                        if (strlen((string)$value) > $max) {
                            $this->addError($field, "The {$field} may not be greater than {$max} characters.");
                        }
                    }
                }
            }

            if (!isset($this->errors[$field])) {
                $this->validatedData[$field] = $value;
            }
        }

        return $this;
    }

    public function addError(string $field, string $message): self
    {
        $this->errors[$field][] = $message;
        return $this;
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function validated(): array
    {
        return $this->validatedData;
    }
}

if (!class_exists('InputValidator', false)) {
    class_alias(\App\Helpers\InputValidator::class, 'InputValidator');
}
