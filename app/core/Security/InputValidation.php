<?php

namespace App\Core\Security;

/**
 * Input Validation
 *
 * NOTE (S453): this class was referenced by
 * App\Services\Business\FinancialService as
 * `use App\Core\Security\InputValidation;` + `new InputValidation()`, but the
 * file never existed - so constructing that service was an instant fatal
 * ("Class App\Core\Security\InputValidation not found").
 *
 * It is implemented here against the exact contract the caller uses:
 *
 *   $validation = $inputValidation->validate($data, $rules);
 *   if (!$validation['valid']) { ... $validation['errors'] ... }
 *
 * Rule shape (per field):
 *   'amount' => ['required' => true, 'type' => 'numeric', 'min' => 0],
 *   'type'   => ['required' => true, 'type' => 'string', 'in' => ['a', 'b']],
 *   'date'   => ['required' => true, 'type' => 'date'],
 *
 * Supported keys: required, type (string|numeric|integer|email|date|bool),
 * min, max, in.
 */
class InputValidation
{
    /**
     * Validate a payload against a rule set.
     *
     * @param  array  $data
     * @param  array  $rules   field => rule array
     * @return array{valid: bool, errors: array<string,string>, sanitized: array}
     */
    public function validate(array $data, array $rules = []): array
    {
        $errors   = [];
        $sanitized = [];

        foreach ($rules as $field => $rule) {
            $rule = is_array($rule) ? $rule : [];
            $value = $data[$field] ?? null;

            $error = $this->checkField($field, $value, $rule);
            if ($error !== null) {
                $errors[$field] = $error;
                continue;
            }

            if ($value !== null) {
                $sanitized[$field] = is_string($value)
                    ? htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8')
                    : $value;
            }
        }

        return [
            'valid'     => empty($errors),
            'errors'    => $errors,
            'sanitized' => $sanitized,
        ];
    }

    /**
     * Check a single field, returning an error message or null.
     *
     * @param  array  $rule
     */
    private function checkField(string $field, $value, array $rule): ?string
    {
        $isEmpty = $value === null || $value === '' || $value === [];

        if (!empty($rule['required']) && $isEmpty) {
            return "The {$field} field is required.";
        }

        if ($isEmpty) {
            return null;
        }

        if (isset($rule['type'])) {
            $type = strtolower((string) $rule['type']);
            $ok = match ($type) {
                'numeric', 'number', 'float' => is_numeric($value),
                'integer', 'int'             => is_numeric($value) && (int) $value == $value,
                'bool', 'boolean'            => is_bool($value) || in_array($value, [0, 1, '0', '1', true, false], true),
                'email'                      => (bool) filter_var($value, FILTER_VALIDATE_EMAIL),
                'date'                       => $this->isDate((string) $value),
                'string'                     => is_string($value),
                default                      => true,
            };
            if (!$ok) {
                return "The {$field} field must be of type {$rule['type']}.";
            }
        }

        if (isset($rule['min']) && is_numeric($value) && $value < $rule['min']) {
            return "The {$field} field must be at least {$rule['min']}.";
        }
        if (isset($rule['max']) && is_numeric($value) && $value > $rule['max']) {
            return "The {$field} field may not be greater than {$rule['max']}.";
        }
        if (isset($rule['max']) && is_string($value) && mb_strlen($value) > (int) $rule['max']) {
            return "The {$field} field may not be greater than {$rule['max']} characters.";
        }
        if (isset($rule['min']) && is_string($value) && mb_strlen($value) < (int) $rule['min']) {
            return "The {$field} field must be at least {$rule['min']} characters.";
        }
        if (isset($rule['in']) && is_array($rule['in']) && !in_array($value, $rule['in'], true)) {
            return "The {$field} field must be one of: " . implode(', ', $rule['in']) . '.';
        }

        return null;
    }

    private function isDate(string $value): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $value);
        return $d !== false && $d->format('Y-m-d') === $value;
    }
}
