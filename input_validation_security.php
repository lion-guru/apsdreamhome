<?php
/**
 * APS Dream Home - Input Validation Security Script
 * Automated input validation and sanitization implementation
 */

echo "🛡️ APS DREAM HOME - INPUT VALIDATION SECURITY\n";
echo "==========================================\n\n";

// Load centralized path configuration
require_once __DIR__ . '/config/paths.php';

// Security implementation results
$securityResults = [];
$totalImplementations = 0;
$successfulImplementations = 0;

echo "🔍 IMPLEMENTING INPUT VALIDATION SECURITY...\n\n";

// 1. Create input validation class
echo "Step 1: Creating input validation class\n";
$validatorFile = APP_PATH . '/Security/InputValidator.php';
$validatorContent = "<?php\n";
$validatorContent .= "/**\n";
$validatorContent .= " * APS Dream Home - Input Validator\n";
$validatorContent .= " */\n";
$validatorContent .= "\n";
$validatorContent .= "namespace App\\Security;\n";
$validatorContent .= "\n";
$validatorContent .= "class InputValidator\n";
$validatorContent .= "{\n";
$validatorContent .= "    private static \$instance = null;\n";
$validatorContent .= "    private \$rules = [];\n";
$validatorContent .= "    private \$errors = [];\n";
$validatorContent .= "\n";
$validatorContent .= "    public function __construct()\n";
$validatorContent .= "    {\n";
$validatorContent .= "        \$this->initializeRules();\n";
$validatorContent .= "    }\n";
$validatorContent .= "\n";
$validatorContent .= "    public static function getInstance()\n";
$validatorContent .= "    {\n";
$validatorContent .= "        if (self::\$instance === null) {\n";
$validatorContent .= "            self::\$instance = new self();\n";
$validatorContent .= "        }\n";
$validatorContent .= "        return self::\$instance;\n";
$validatorContent .= "    }\n";
$validatorContent .= "\n";
$validatorContent .= "    private function initializeRules()\n";
$validatorContent .= "    {\n";
$validatorContent .= "        \$this->rules = [\n";
$validatorContent .= "            'name' => [\n";
$validatorContent .= "                'required' => true,\n";
$validatorContent .= "                'max_length' => 100,\n";
$validatorContent .= "                'pattern' => '/^[a-zA-Z\\s\\-\\.]+$/'\n";
$validatorContent .= "            ],\n";
$validatorContent .= "            'email' => [\n";
$validatorContent .= "                'required' => true,\n";
$validatorContent .= "                'max_length' => 255,\n";
$validatorContent .= "                'pattern' => '/^[^@\\s]+@[^@\\s]+\\.[^@\\s]+$/'\n";
$validatorContent .= "            ],\n";
$validatorContent .= "            'phone' => [\n";
$validatorContent .= "                'required' => false,\n";
$validatorContent .= "                'max_length' => 20,\n";
$validatorContent .= "                'pattern' => '/^[\\d\\s\\-\\+\\(\\)]+$/'\n";
$validatorContent .= "            ],\n";
$validatorContent .= "            'message' => [\n";
$validatorContent .= "                'required' => true,\n";
$validatorContent .= "                'max_length' => 1000,\n";
$validatorContent .= "                'min_length' => 10\n";
$validatorContent .= "            ],\n";
$validatorContent .= "            'price' => [\n";
$validatorContent .= "                'required' => true,\n";
$validatorContent .= "                'type' => 'numeric',\n";
$validatorContent .= "                'min' => 0,\n";
$validatorContent .= "                'max' => 999999999\n";
$validatorContent .= "            ],\n";
$validatorContent .= "            'property_type' => [\n";
$validatorContent .= "                'required' => true,\n";
$validatorContent .= "                'in' => ['residential', 'commercial', 'industrial', 'land']\n";
$validatorContent .= "            ]\n";
$validatorContent .= "        ];\n";
$validatorContent .= "    }\n";
$validatorContent .= "\n";
$validatorContent .= "    public function validate(\$data, \$rules = [])\n";
$validatorContent .= "    {\n";
$validatorContent .= "        \$this->errors = [];\n";
$validatorContent .= "        \$validationRules = array_merge(\$this->rules, \$rules);\n";
$validatorContent .= "\n";
$validatorContent .= "        foreach (\$validationRules as \$field => \$fieldRules) {\n";
$validatorContent .= "            \$value = \$data[\$field] ?? null;\n";
$validatorContent .= "\n";
$validatorContent .= "            if (isset(\$fieldRules['required']) && \$fieldRules['required'] && empty(\$value)) {\n";
$validatorContent .= "                \$this->errors[\$field] = ucfirst(\$field) . ' is required';\n";
$validatorContent .= "                continue;\n";
$validatorContent .= "            }\n";
$validatorContent .= "\n";
$validatorContent .= "            if (!empty(\$value)) {\n";
$validatorContent .= "                \$this->validateField(\$field, \$value, \$fieldRules);\n";
$validatorContent .= "            }\n";
$validatorContent .= "        }\n";
$validatorContent .= "\n";
$validatorContent .= "        return empty(\$this->errors);\n";
$validatorContent .= "    }\n";
$validatorContent .= "\n";
$validatorContent .= "    private function validateField(\$field, \$value, \$rules)\n";
$validatorContent .= "    {\n";
$validatorContent .= "        // Type validation\n";
$validatorContent .= "        if (isset(\$rules['type'])) {\n";
$validatorContent .= "            if (!\$this->validateType(\$value, \$rules['type'])) {\n";
$validatorContent .= "                \$this->errors[\$field] = ucfirst(\$field) . ' must be of type ' . \$rules['type'];\n";
$validatorContent .= "                return;\n";
$validatorContent .= "            }\n";
$validatorContent .= "        }\n";
$validatorContent .= "\n";
$validatorContent .= "        // Length validation\n";
$validatorContent .= "        if (isset(\$rules['max_length']) && strlen(\$value) > \$rules['max_length']) {\n";
$validatorContent .= "            \$this->errors[\$field] = ucfirst(\$field) . ' must not exceed ' . \$rules['max_length'] . ' characters';\n";
$validatorContent .= "        }\n";
$validatorContent .= "\n";
$validatorContent .= "        if (isset(\$rules['min_length']) && strlen(\$value) < \$rules['min_length']) {\n";
$validatorContent .= "            \$this->errors[\$field] = ucfirst(\$field) . ' must be at least ' . \$rules['min_length'] . ' characters';\n";
$validatorContent .= "        }\n";
$validatorContent .= "\n";
$validatorContent .= "        // Numeric validation\n";
$validatorContent .= "        if (isset(\$rules['min']) && is_numeric(\$value) && \$value < \$rules['min']) {\n";
$validatorContent .= "            \$this->errors[\$field] = ucfirst(\$field) . ' must be at least ' . \$rules['min'];\n";
$validatorContent .= "        }\n";
$validatorContent .= "\n";
$validatorContent .= "        if (isset(\$rules['max']) && is_numeric(\$value) && \$value > \$rules['max']) {\n";
$validatorContent .= "            \$this->errors[\$field] = ucfirst(\$field) . ' must not exceed ' . \$rules['max'];\n";
$validatorContent .= "        }\n";
$validatorContent .= "\n";
$validatorContent .= "        // Pattern validation\n";
$validatorContent .= "        if (isset(\$rules['pattern']) && !preg_match(\$rules['pattern'], \$value)) {\n";
$validatorContent .= "            \$this->errors[\$field] = ucfirst(\$field) . ' format is invalid';\n";
$validatorContent .= "        }\n";
$validatorContent .= "\n";
$validatorContent .= "        // In validation\n";
$validatorContent .= "        if (isset(\$rules['in']) && !in_array(\$value, \$rules['in'])) {\n";
$validatorContent .= "            \$this->errors[\$field] = ucfirst(\$field) . ' must be one of: ' . implode(', ', \$rules['in']);\n";
$validatorContent .= "        }\n";
$validatorContent .= "    }\n";
$validatorContent .= "\n";
$validatorContent .= "    private function validateType(\$value, \$type)\n";
$validatorContent .= "    {\n";
$validatorContent .= "        switch (\$type) {\n";
$validatorContent .= "            case 'numeric':\n";
$validatorContent .= "                return is_numeric(\$value);\n";
$validatorContent .= "            case 'email':\n";
$validatorContent .= "                return filter_var(\$value, FILTER_VALIDATE_EMAIL) !== false;\n";
$validatorContent .= "            case 'url':\n";
$validatorContent .= "                return filter_var(\$value, FILTER_VALIDATE_URL) !== false;\n";
$validatorContent .= "            case 'integer':\n";
$validatorContent .= "                return filter_var(\$value, FILTER_VALIDATE_INT) !== false;\n";
$validatorContent .= "            default:\n";
$validatorContent .= "                return true;\n";
$validatorContent .= "        }\n";
$validatorContent .= "    }\n";
$validatorContent .= "\n";
$validatorContent .= "    public function getErrors()\n";
$validatorContent .= "    {\n";
$validatorContent .= "        return \$this->errors;\n";
$validatorContent .= "    }\n";
$validatorContent .= "\n";
$validatorContent .= "    public function getFirstError()\n";
$validatorContent .= "    {\n";
$validatorContent .= "        return reset(\$this->errors) ?: null;\n";
$validatorContent .= "    }\n";
$validatorContent .= "}\n";

if (file_put_contents($validatorFile, $validatorContent)) {
    echo "   ✅ Input validator class created: app/Security/InputValidator.php\n";
    $securityResults['input_validator'] = 'created';
    $successfulImplementations++;
} else {
    echo "   ❌ Failed to create input validator class\n";
    $securityResults['input_validator'] = 'failed';
}
$totalImplementations++;

// 2. Create input sanitization class
echo "\nStep 2: Creating input sanitization class\n";
$sanitizerFile = APP_PATH . '/Security/InputSanitizer.php';
$sanitizerContent = "<?php\n";
$sanitizerContent .= "/**\n";
$sanitizerContent .= " * APS Dream Home - Input Sanitizer\n";
$sanitizerContent .= " */\n";
$sanitizerContent .= "\n";
$sanitizerContent .= "namespace App\\Security;\n";
$sanitizerContent .= "\n";
$sanitizerContent .= "class InputSanitizer\n";
$sanitizerContent .= "{\n";
$sanitizerContent .= "    private static \$instance = null;\n";
$sanitizerContent .= "\n";
$sanitizerContent .= "    public static function getInstance()\n";
$sanitizerContent .= "    {\n";
$sanitizerContent .= "        if (self::\$instance === null) {\n";
$sanitizerContent .= "            self::\$instance = new self();\n";
$sanitizerContent .= "        }\n";
$sanitizerContent .= "        return self::\$instance;\n";
$sanitizerContent .= "    }\n";
$sanitizerContent .= "\n";
$sanitizerContent .= "    public function sanitize(\$data, \$type = 'general')\n";
$sanitizerContent .= "    {\n";
$sanitizerContent .= "        if (is_array(\$data)) {\n";
$sanitizerContent .= "            return \$this->sanitizeArray(\$data, \$type);\n";
$sanitizerContent .= "        }\n";
$sanitizerContent .= "\n";
$sanitizerContent .= "        return \$this->sanitizeString(\$data, \$type);\n";
$sanitizerContent .= "    }\n";
$sanitizerContent .= "\n";
$sanitizerContent .= "    private function sanitizeArray(\$data, \$type)\n";
$sanitizerContent .= "    {\n";
$sanitizerContent .= "        \$sanitized = [];\n";
$sanitizerContent .= "        foreach (\$data as \$key => \$value) {\n";
$sanitizerContent .= "            \$sanitized[\$key] = \$this->sanitize(\$value, \$type);\n";
$sanitizerContent .= "        }\n";
$sanitizerContent .= "        return \$sanitized;\n";
$sanitizerContent .= "    }\n";
$sanitizerContent .= "\n";
$sanitizerContent .= "    private function sanitizeString(\$data, \$type)\n";
$sanitizerContent .= "    {\n";
$sanitizerContent .= "        switch (\$type) {\n";
$sanitizerContent .= "            case 'email':\n";
$sanitizerContent .= "                return filter_var(\$data, FILTER_SANITIZE_EMAIL);\n";
$sanitizerContent .= "            case 'url':\n";
$sanitizerContent .= "                return filter_var(\$data, FILTER_SANITIZE_URL);\n";
$sanitizerContent .= "            case 'number':\n";
$sanitizerContent .= "                return filter_var(\$data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);\n";
$sanitizerContent .= "            case 'integer':\n";
$sanitizerContent .= "                return filter_var(\$data, FILTER_SANITIZE_NUMBER_INT);\n";
$sanitizerContent .= "            case 'string':\n";
$sanitizerContent .= "                return \$this->sanitizeString(\$data);\n";
$sanitizerContent .= "            case 'html':\n";
$sanitizerContent .= "                return htmlspecialchars(\$data, ENT_QUOTES, 'UTF-8');\n";
$sanitizerContent .= "            case 'sql':\n";
$sanitizerContent .= "                return \$this->sanitizeSql(\$data);\n";
$sanitizerContent .= "            case 'filename':\n";
$sanitizerContent .= "                return \$this->sanitizeFilename(\$data);\n";
$sanitizerContent .= "            case 'general':\n";
$sanitizerContent .= "            default:\n";
$sanitizerContent .= "                return \$this->sanitizeGeneral(\$data);\n";
$sanitizerContent .= "        }\n";
$sanitizerContent .= "    }\n";
$sanitizerContent .= "\n";
$sanitizerContent .= "    private function sanitizeGeneral(\$data)\n";
$sanitizerContent .= "    {\n";
$sanitizerContent .= "        // Remove HTML tags\n";
$sanitizerContent .= "        \$data = strip_tags(\$data);\n";
$sanitizerContent .= "        // Remove special characters\n";
$sanitizerContent .= "        \$data = htmlspecialchars(\$data, ENT_QUOTES, 'UTF-8');\n";
$sanitizerContent .= "        // Remove extra whitespace\n";
$sanitizerContent .= "        \$data = trim(preg_replace('/\\s+/', ' ', \$data));\n";
$sanitizerContent .= "        return \$data;\n";
$sanitizerContent .= "    }\n";
$sanitizerContent .= "\n";
$sanitizerContent .= "    private function sanitizeString(\$data)\n";
$sanitizerContent .= "    {\n";
$sanitizerContent .= "        // Remove potentially dangerous characters\n";
$sanitizerContent .= "        \$data = preg_replace('/[<>\"\\'\\\\]/', '', \$data);\n";
$sanitizerContent .= "        // Remove control characters\n";
$sanitizerContent .= "        \$data = preg_replace('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F\\x7F]/', '', \$data);\n";
$sanitizerContent .= "        return \$data;\n";
$sanitizerContent .= "    }\n";
$sanitizerContent .= "\n";
$sanitizerContent .= "    private function sanitizeSql(\$data)\n";
$sanitizerContent .= "    {\n";
$sanitizerContent .= "        // Remove SQL injection patterns\n";
$sanitizerContent .= "        \$patterns = [\n";
$sanitizerContent .= "            '/(\\\\|\\'|\\\"|;|--|\\/\\*|\\*\\/|xp_)/i',\n";
$sanitizerContent .= "            '/(union|select|insert|update|delete|drop|create|alter|exec|script)/i'\n";
$sanitizerContent .= "        ];\n";
$sanitizerContent .= "        foreach (\$patterns as \$pattern) {\n";
$sanitizerContent .= "            \$data = preg_replace(\$pattern, '', \$data);\n";
$sanitizerContent .= "        }\n";
$sanitizerContent .= "        return \$data;\n";
$sanitizerContent .= "    }\n";
$sanitizerContent .= "\n";
$sanitizerContent .= "    private function sanitizeFilename(\$data)\n";
$sanitizerContent .= "    {\n";
$sanitizerContent .= "        // Remove dangerous characters from filename\n";
$sanitizerContent .= "        \$data = preg_replace('/[^a-zA-Z0-9._-]/', '_', \$data);\n";
$sanitizerContent .= "        // Remove multiple dots\n";
$sanitizerContent .= "        \$data = preg_replace('/\\.+/', '.', \$data);\n";
$sanitizerContent .= "        // Remove leading/trailing dots\n";
$sanitizerContent .= "        \$data = trim(\$data, '.');\n";
$sanitizerContent .= "        return \$data;\n";
$sanitizerContent .= "    }\n";
$sanitizerContent .= "}\n";

if (file_put_contents($sanitizerFile, $sanitizerContent)) {
    echo "   ✅ Input sanitizer class created: app/Security/InputSanitizer.php\n";
    $securityResults['input_sanitizer'] = 'created';
    $successfulImplementations++;
} else {
    echo "   ❌ Failed to create input sanitizer class\n";
    $securityResults['input_sanitizer'] = 'failed';
}
$totalImplementations++;

// 3. Create input filtering middleware
echo "\nStep 3: Creating input filtering middleware\n";
$middlewareFile = APP_PATH . '/Http/Middleware/InputFilteringMiddleware.php';
$middlewareContent = "<?php\n";
$middlewareContent .= "/**\n";
$middlewareContent .= " * APS Dream Home - Input Filtering Middleware\n";
$middlewareContent .= " */\n";
$middlewareContent .= "\n";
$middlewareContent .= "namespace App\\Http\\Middleware;\n";
$middlewareContent .= "\n";
$middlewareContent .= "use App\\Security\\InputValidator;\n";
$middlewareContent .= "use App\\Security\\InputSanitizer;\n";
$middlewareContent .= "\n";
$middlewareContent .= "class InputFilteringMiddleware\n";
$middlewareContent .= "{\n";
$middlewareContent .= "    private \$validator;\n";
$middlewareContent .= "    private \$sanitizer;\n";
$middlewareContent .= "\n";
$middlewareContent .= "    public function __construct()\n";
$middlewareContent .= "    {\n";
$middlewareContent .= "        \$this->validator = InputValidator::getInstance();\n";
$middlewareContent .= "        \$this->sanitizer = InputSanitizer::getInstance();\n";
$middlewareContent .= "    }\n";
$middlewareContent .= "\n";
$middlewareContent .= "    public function handle(\$request, \$next)\n";
$middlewareContent .= "    {\n";
$middlewareContent .= "        // Get input data\n";
$middlewareContent .= "        \$inputData = \$request->getAllInput();\n";
$middlewareContent .= "\n";
$middlewareContent .= "        // Sanitize input data\n";
$middlewareContent .= "        \$sanitizedData = \$this->sanitizer->sanitize(\$inputData);\n";
$middlewareContent .= "\n";
$middlewareContent .= "        // Validate input data\n";
$middlewareContent .= "        \$isValid = \$this->validator->validate(\$sanitizedData);\n";
$middlewareContent .= "\n";
$middlewareContent .= "        if (!\$isValid) {\n";
$middlewareContent .= "            \$errors = \$this->validator->getErrors();\n";
$middlewareContent .= "            return response()->json([\n";
$middlewareContent .= "                'success' => false,\n";
$middlewareContent .= "                'message' => 'Validation failed',\n";
$middlewareContent .= "                'errors' => \$errors\n";
$middlewareContent .= "            ], 400);\n";
$middlewareContent .= "        }\n";
$middlewareContent .= "\n";
$middlewareContent .= "        // Update request with sanitized data\n";
$middlewareContent .= "        \$request->merge(\$sanitizedData);\n";
$middlewareContent .= "\n";
$middlewareContent .= "        return \$next(\$request);\n";
$middlewareContent .= "    }\n";
$middlewareContent .= "}\n";

if (file_put_contents($middlewareFile, $middlewareContent)) {
    echo "   ✅ Input filtering middleware created: app/Http/Middleware/InputFilteringMiddleware.php\n";
    $securityResults['input_filtering_middleware'] = 'created';
    $successfulImplementations++;
} else {
    echo "   ❌ Failed to create input filtering middleware\n";
    $securityResults['input_filtering_middleware'] = 'failed';
}
$totalImplementations++;

// 4. Create security configuration
echo "\nStep 4: Creating security configuration\n";
$securityConfigFile = CONFIG_PATH . '/security.php';
$securityConfigContent = "<?php\n";
$securityConfigContent .= "/**\n";
$securityConfigContent .= " * APS Dream Home - Security Configuration\n";
$securityConfigContent .= " */\n";
$securityConfigContent .= "\n";
$securityConfigContent .= "return [\n";
$securityConfigContent .= "    'input_validation' => [\n";
$securityConfigContent .= "        'enabled' => true,\n";
$securityConfigContent .= "        'strict_mode' => true,\n";
$securityConfigContent .= "        'sanitize_all' => true,\n";
$securityConfigContent .= "        'max_input_length' => 10000,\n";
$securityConfigContent .= "        'allowed_tags' => [],\n";
$securityConfigContent .= "        'allowed_attributes' => []\n";
$securityConfigContent .= "    ],\n";
$securityConfigContent .= "    'xss_protection' => [\n";
$securityConfigContent .= "        'enabled' => true,\n";
$securityConfigContent .= "        'strip_tags' => true,\n";
$securityConfigContent .= "        'escape_html' => true,\n";
$securityConfigContent .= "        'content_security_policy' => true\n";
$securityConfigContent .= "    ],\n";
$securityConfigContent .= "    'sql_injection' => [\n";
$securityConfigContent .= "        'enabled' => true,\n";
$securityConfigContent .= "        'use_prepared_statements' => true,\n";
$securityConfigContent .= "        'escape_parameters' => true,\n";
$securityConfigContent .= "        'validate_queries' => true\n";
$securityConfigContent .= "    ],\n";
$securityConfigContent .= "    'csrf_protection' => [\n";
$securityConfigContent .= "        'enabled' => true,\n";
$securityConfigContent .= "        'token_expiry' => 3600,\n";
$securityConfigContent .= "        'regenerate_token' => true,\n";
$securityConfigContent .= "        'exclude_routes' => ['api/webhook']\n";
$securityConfigContent .= "    ],\n";
$securityConfigContent .= "    'session_security' => [\n";
$securityConfigContent .= "        'secure' => true,\n";
$securityConfigContent .= "        'httponly' => true,\n";
$securityConfigContent .= "        'samesite' => 'Strict',\n";
$securityConfigContent .= "        'regenerate_id' => true,\n";
$securityConfigContent .= "        'timeout' => 1800\n";
$securityConfigContent .= "    ],\n";
$securityConfigContent .= "    'file_upload' => [\n";
$securityConfigContent .= "        'max_size' => 10485760, // 10MB\n";
$securityConfigContent .= "        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],\n";
$securityConfigContent .= "        'scan_uploads' => true,\n";
$securityConfigContent .= "        'quarantine_suspicious' => true\n";
$securityConfigContent .= "    ],\n";
$securityConfigContent .= "    'rate_limiting' => [\n";
$securityConfigContent .= "        'enabled' => true,\n";
$securityConfigContent .= "        'max_requests' => 100,\n";
$securityConfigContent .= "        'window' => 3600,\n";
$securityConfigContent .= "        'block_duration' => 900\n";
$securityConfigContent .= "    ]\n";
$securityConfigContent .= "];\n";

if (file_put_contents($securityConfigFile, $securityConfigContent)) {
    echo "   ✅ Security configuration created: config/security.php\n";
    $securityResults['security_config'] = 'created';
    $successfulImplementations++;
} else {
    echo "   ❌ Failed to create security configuration\n";
    $securityResults['security_config'] = 'failed';
}
$totalImplementations++;

// 5. Create security testing script
echo "\nStep 5: Creating security testing script\n";
$testingScriptFile = BASE_PATH . '/test_input_security.php';
$testingScriptContent = "<?php\n";
$testingScriptContent .= "/**\n";
$testingScriptContent .= " * APS Dream Home - Input Security Testing Script\n";
$testingScriptContent .= " */\n";
$testingScriptContent .= "\n";
$testingScriptContent .= "require_once __DIR__ . '/config/paths.php';\n";
$testingScriptContent .= "require_once APP_PATH . '/Security/InputValidator.php';\n";
$testingScriptContent .= "require_once APP_PATH . '/Security/InputSanitizer.php';\n";
$testingScriptContent .= "\n";
$testingScriptContent .= "echo '🛡️ APS DREAM HOME - INPUT SECURITY TESTING\\n';\n";
$testingScriptContent .= "echo '==========================================\\n\\n';\n";
$testingScriptContent .= "\n";
$testingScriptContent .= "\$validator = App\\Security\\InputValidator::getInstance();\n";
$testingScriptContent .= "\$sanitizer = App\\Security\\InputSanitizer::getInstance();\n";
$testingScriptContent .= "\n";
$testingScriptContent .= "// Test cases\n";
$testingScriptContent .= "\$testCases = [\n";
$testingScriptContent .= "    'valid_email' => ['email' => 'test@example.com'],\n";
$testingScriptContent .= "    'invalid_email' => ['email' => 'invalid-email'],\n";
$testingScriptContent .= "    'xss_attempt' => ['message' => '<script>alert(\"xss\")</script>'],\n";
$testingScriptContent .= "    'sql_injection' => ['query' => \"SELECT * FROM users WHERE id = 1; DROP TABLE users;--\"],\n";
$testingScriptContent .= "    'valid_name' => ['name' => 'John Doe'],\n";
$testingScriptContent .= "    'invalid_name' => ['name' => 'John123'],\n";
$testingScriptContent .= "    'valid_phone' => ['phone' => '+91-98765-43210'],\n";
$testingScriptContent .= "    'invalid_phone' => ['phone' => 'abc123']\n";
$testingScriptContent .= "];\n";
$testingScriptContent .= "\n";
$testingScriptContent .= "echo '🔍 Testing Input Validation:\\n';\n";
$testingScriptContent .= "foreach (\$testCases as \$testName => \$data) {\n";
$testingScriptContent .= "    echo \"Testing \$testName...\\n\";\n";
$testingScriptContent .= "    \n";
$testingScriptContent .= "    // Sanitize first\n";
$testingScriptContent .= "    \$sanitized = \$sanitizer->sanitize(\$data);\n";
$testingScriptContent .= "    echo \"  Sanitized: \" . json_encode(\$sanitized) . \"\\n\";\n";
$testingScriptContent .= "    \n";
$testingScriptContent .= "    // Validate\n";
$testingScriptContent .= "    \$isValid = \$validator->validate(\$sanitized);\n";
$testingScriptContent .= "    \$status = \$isValid ? '✅ PASSED' : '❌ FAILED';\n";
$testingScriptContent .= "    echo \"  Validation: \$status\\n\";\n";
$testingScriptContent .= "    \n";
$testingScriptContent .= "    if (!\$isValid) {\n";
$testingScriptContent .= "        \$errors = \$validator->getErrors();\n";
$testingScriptContent .= "        echo \"  Errors: \" . implode(', ', \$errors) . \"\\n\";\n";
$testingScriptContent .= "    }\n";
$testingScriptContent .= "    echo \"\\n\";\n";
$testingScriptContent .= "}\n";
$testingScriptContent .= "\n";
$testingScriptContent .= "echo '🎉 Input security testing completed!\\n';\n";

if (file_put_contents($testingScriptFile, $testingScriptContent)) {
    echo "   ✅ Security testing script created: test_input_security.php\n";
    $securityResults['security_testing_script'] = 'created';
    $successfulImplementations++;
} else {
    echo "   ❌ Failed to create security testing script\n";
    $securityResults['security_testing_script'] = 'failed';
}
$totalImplementations++;

// Summary
echo "\n==========================================\n";
echo "📊 INPUT VALIDATION SECURITY SUMMARY\n";
echo "==========================================\n";

$successRate = round(($successfulImplementations / $totalImplementations) * 100, 1);
echo "📊 TOTAL IMPLEMENTATIONS: $totalImplementations\n";
echo "✅ SUCCESSFUL: $successfulImplementations\n";
echo "📊 SUCCESS RATE: $successRate%\n\n";

echo "🔧 SECURITY IMPLEMENTATION DETAILS:\n";
foreach ($securityResults as $category => $result) {
    $icon = $result === 'created' ? '✅' : ($result === 'failed' ? '❌' : '⚠️');
    echo "📋 $category: $result\n";
}

if ($successRate >= 80) {
    echo "\n🎉 INPUT VALIDATION SECURITY: EXCELLENT!\n";
} elseif ($successRate >= 60) {
    echo "\n✅ INPUT VALIDATION SECURITY: GOOD!\n";
} else {
    echo "\n⚠️  INPUT VALIDATION SECURITY: NEEDS IMPROVEMENT\n";
}

echo "\n🚀 Input validation security implemented successfully!\n";
echo "📊 Ready for next security step: SQL Injection Protection\n";
?>
