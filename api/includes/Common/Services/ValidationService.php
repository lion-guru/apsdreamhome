<?php
namespace App\Common\Services;

class ValidationService {
    private array $errors = [];
    
    public function __construct(private array $data, private array $rules) {}
    
    public function validate(): bool {
        foreach ($this->rules as $field => $rules) {
            $value = $this->data[$field] ?? null;
            $this->validateField($field, $value, $rules);
        }
        return empty($this->errors);
    }
    
    private function validateField(string $field, $value, array $rules): void {
        foreach ($rules as $rule) {
            switch ($rule) {
                case 'required':
                    if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                        $this->addError($field, "The $field field is required");
                    }
                    break;
                case 'email':
                    if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $this->addError($field, "The $field must be a valid email address");
                    }
                    break;
                case 'numeric':
                    if ($value !== null && $value !== '' && !is_numeric($value)) {
                        $this->addError($field, "The $field must be a number");
                    }
                    break;
            }
        }
    }
    
    private function addError(string $field, string $message): void {
        $this->errors[$field][] = $message;
    }
    
    public function getErrors(): array {
        return $this->errors;
    }
}
