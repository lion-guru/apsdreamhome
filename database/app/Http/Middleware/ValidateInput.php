<?php
namespace App\Http\Middleware;

class ValidateInput
{
    protected $rules = [
        'email' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
        'phone' => '/^[\+]?[1-9][\d]{0,15}$/',
        'name' => '/^[a-zA-Z\s]{2,50}$/',
        'password' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{8,}$/',
        'url' => '/^https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&//=]*)$/',
        'number' => '/^\d+$/',
        'decimal' => '/^\d+(\.\d{1,2})?$/',
        'date' => '/^\d{4}-\d{2}-\d{2}$/',
        'zipcode' => '/^\d{5,6}$/'
    ];

    public function handle($request, $next)
    {
        $this->validateRequest($request);
        return $next($request);
    }

    protected function validateRequest($request)
    {
        $errors = [];

        // Validate GET parameters
        if (!empty($_GET)) {
            $errors = array_merge($errors, $this->validateData($_GET, 'GET'));
        }

        // Validate POST parameters
        if (!empty($_POST)) {
            $errors = array_merge($errors, $this->validateData($_POST, 'POST'));
        }

        // Check for suspicious patterns
        $suspicious = $this->checkSuspiciousPatterns();
        if (!empty($suspicious)) {
            $errors = array_merge($errors, $suspicious);
        }

        if (!empty($errors)) {
            header('HTTP/1.1 400 Bad Request');
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Validation Failed',
                'message' => 'Invalid input data provided',
                'errors' => $errors
            ]);
            exit;
        }
    }

    protected function validateData($data, $source)
    {
        $errors = [];

        foreach ($data as $key => $value) {
            // Skip validation for known safe fields
            if (in_array($key, ['_token', 'submit', 'action'])) {
                continue;
            }

            // Check field name pattern
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key)) {
                $errors[] = "Invalid field name: $key";
                continue;
            }

            // Validate value based on field name
            if (strpos($key, 'email') !== false) {
                if (!preg_match($this->rules['email'], $value)) {
                    $errors[] = "Invalid email format: $key";
                }
            } elseif (strpos($key, 'phone') !== false || strpos($key, 'mobile') !== false) {
                if (!preg_match($this->rules['phone'], $value)) {
                    $errors[] = "Invalid phone format: $key";
                }
            } elseif (strpos($key, 'password') !== false) {
                if (strlen($value) < 8) {
                    $errors[] = "Password too short: $key";
                }
            }

            // Check for malicious content
            if ($this->containsMaliciousContent($value)) {
                $errors[] = "Suspicious content detected in: $key";
            }
        }

        return $errors;
    }

    protected function checkSuspiciousPatterns()
    {
        $errors = [];

        // Check URL for suspicious patterns
        $url = $_SERVER['REQUEST_URI'] ?? '';
        $suspiciousPatterns = [
            '\.\./', // Directory traversal
            '<script', // XSS
            'javascript:', // JavaScript injection
            'data:', // Data URL injection
            'vbscript:', // VBScript injection
            'onload=', // Event handler injection
            'onerror=', // Event handler injection
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (stripos($url, $pattern) !== false) {
                $errors[] = "Suspicious URL pattern detected: $pattern";
            }
        }

        return $errors;
    }

    protected function containsMaliciousContent($value)
    {
        if (!is_string($value)) return false;

        $maliciousPatterns = [
            '<script', '</script>', 'javascript:', 'vbscript:', 'data:',
            'onload=', 'onerror=', 'onclick=', 'onmouseover=', 'eval(',
            'document.cookie', 'document.location', 'window.location'
        ];

        foreach ($maliciousPatterns as $pattern) {
            if (stripos($value, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }
}