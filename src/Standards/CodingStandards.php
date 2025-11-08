<?php
/**
 * APS Dream Home - Coding Standards and Organization Guide
 *
 * This document defines the coding standards, naming conventions,
 * and organizational structure for the APS Dream Home project.
 */

namespace ApsDreamHome\Standards;

/**
 * Class CodingStandards
 * Defines project-wide coding standards and best practices
 */
class CodingStandards {

    // File naming conventions
    const FILE_NAMING = [
        'php_classes' => 'PascalCase.php',           // UserController.php
        'php_interfaces' => 'PascalCase.php',        // DatabaseInterface.php
        'php_traits' => 'PascalCase.php',            // LoggableTrait.php
        'php_functions' => 'snake_case.php',         // helper_functions.php
        'php_config' => 'snake_case.php',            // database_config.php
        'js_files' => 'kebab-case.js',               // user-profile.js
        'css_files' => 'kebab-case.css',             // admin-panel.css
        'test_files' => 'PascalCaseTest.php',        // UserControllerTest.php
    ];

    // Directory structure standards
    const DIRECTORY_STRUCTURE = [
        'src' => [
            'Controllers' => 'Application controllers',
            'Models' => 'Data models and business logic',
            'Views' => 'Template files',
            'Services' => 'Business logic services',
            'Repositories' => 'Data access layer',
            'Middleware' => 'HTTP middleware',
            'Traits' => 'Reusable traits',
            'Interfaces' => 'Contract definitions',
            'Exceptions' => 'Custom exceptions',
            'Helpers' => 'Utility functions',
            'Config' => 'Configuration files',
        ],
        'tests' => [
            'Unit' => 'Unit tests',
            'Integration' => 'Integration tests',
            'Functional' => 'End-to-end tests',
            'Fixtures' => 'Test data',
        ],
        'public' => [
            'assets' => [
                'css' => 'Stylesheets',
                'js' => 'JavaScript files',
                'images' => 'Image assets',
                'fonts' => 'Font files',
            ],
            'api' => 'Public API endpoints',
        ],
    ];

    // Variable naming conventions
    const VARIABLE_NAMING = [
        'private_properties' => '_camelCase',
        'protected_properties' => '_camelCase',
        'public_properties' => 'camelCase',
        'method_parameters' => 'camelCase',
        'local_variables' => 'camelCase',
        'constants' => 'UPPER_SNAKE_CASE',
        'global_variables' => '$GLOBALS[\'camelCase\']',
    ];

    // Method naming conventions
    const METHOD_NAMING = [
        'private_methods' => '_camelCase()',
        'protected_methods' => '_camelCase()',
        'public_methods' => 'camelCase()',
        'static_methods' => 'camelCase()',
        'magic_methods' => '__camelCase()',
        'accessor_methods' => 'get/set/is/hasCamelCase()',
    ];

    // Database naming conventions
    const DATABASE_NAMING = [
        'tables' => 'snake_case',
        'columns' => 'snake_case',
        'primary_keys' => 'table_name_id',
        'foreign_keys' => 'related_table_name_id',
        'indexes' => 'idx_table_column',
        'unique_indexes' => 'uk_table_column',
        'pivot_tables' => 'table1_table2',
    ];

    // API naming conventions
    const API_NAMING = [
        'endpoints' => '/api/v1/resource/{id}',
        'parameters' => 'camelCase',
        'response_fields' => 'camelCase',
        'error_codes' => 'UPPER_SNAKE_CASE',
        'http_methods' => 'RESTful conventions',
    ];

    // Code formatting standards
    const FORMATTING = [
        'indentation' => '4 spaces (no tabs)',
        'line_length' => 120,
        'brace_style' => 'PSR-12 (same line)',
        'spacing' => 'PSR-12 standards',
        'comments' => 'PHPDoc format',
    ];

    // Error handling standards
    const ERROR_HANDLING = [
        'use_exceptions' => true,
        'custom_exceptions' => 'ApsDreamHome\Exceptions\ namespace',
        'error_reporting' => 'E_ALL in development, E_ERROR in production',
        'logging' => 'Monolog or custom logging',
    ];

    // Security standards
    const SECURITY = [
        'input_validation' => 'Always validate and sanitize',
        'sql_injection' => 'Use prepared statements only',
        'xss_protection' => 'HTML escape all output',
        'csrf_protection' => 'Required for state-changing operations',
        'password_hashing' => 'PASSWORD_ARGON2ID',
        'session_security' => 'HttpOnly, Secure, SameSite flags',
    ];
}

/**
 * PSR-4 Autoloading Configuration
 *
 * @see https://www.php-fig.org/psr/psr-4/
 */
class AutoloadingConfig {

    const NAMESPACES = [
        'ApsDreamHome\\' => 'src/',
        'ApsDreamHome\\Controllers\\' => 'src/Controllers/',
        'ApsDreamHome\\Models\\' => 'src/Models/',
        'ApsDreamHome\\Services\\' => 'src/Services/',
        'ApsDreamHome\\Repositories\\' => 'src/Repositories/',
        'ApsDreamHome\\Middleware\\' => 'src/Middleware/',
        'ApsDreamHome\\Traits\\' => 'src/Traits/',
        'ApsDreamHome\\Interfaces\\' => 'src/Interfaces/',
        'ApsDreamHome\\Exceptions\\' => 'src/Exceptions/',
        'ApsDreamHome\\Tests\\' => 'tests/',
    ];

    const TEST_NAMESPACES = [
        'ApsDreamHome\\Tests\\Unit\\' => 'tests/Unit/',
        'ApsDreamHome\\Tests\\Integration\\' => 'tests/Integration/',
        'ApsDreamHome\\Tests\\Functional\\' => 'tests/Functional/',
    ];
}

/**
 * Function to validate if code follows standards
 */
function validateCodeStandards($filePath) {
    $issues = [];
    $content = file_get_contents($filePath);

    // Check for PSR-4 compliance
    if (!preg_match('/^<\?php\s*namespace\s+[A-Za-z\\\\]+;\s*$/m', $content)) {
        $issues[] = 'Missing namespace declaration';
    }

    // Check for proper class naming
    if (preg_match('/^class\s+([A-Za-z_][A-Za-z0-9_]*)\s*\{/m', $content, $matches)) {
        $className = $matches[1];
        if (!preg_match('/^[A-Z][a-zA-Z0-9]*$/', $className)) {
            $issues[] = "Class name '$className' should be PascalCase";
        }
    }

    // Check for proper method naming
    if (preg_match_all('/function\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\(/', $content, $matches)) {
        foreach ($matches[1] as $methodName) {
            if (strpos($methodName, '__') === 0) {
                // Magic method
                if (!preg_match('/^__[a-z][a-zA-Z0-9]*__$/', $methodName)) {
                    $issues[] = "Magic method '$methodName' should follow __methodName__ format";
                }
            } elseif (strpos($methodName, '_') === 0) {
                // Private/Protected method
                if (!preg_match('/^_[a-z][a-zA-Z0-9]*$/', $methodName)) {
                    $issues[] = "Private method '$methodName' should be _camelCase";
                }
            } else {
                // Public method
                if (!preg_match('/^[a-z][a-zA-Z0-9]*$/', $methodName)) {
                    $issues[] = "Public method '$methodName' should be camelCase";
                }
            }
        }
    }

    // Check for proper indentation (4 spaces)
    $lines = explode("\n", $content);
    foreach ($lines as $lineNum => $line) {
        if (preg_match('/^(\s+)/', $line, $matches)) {
            $indentLength = strlen($matches[1]);
            if ($indentLength > 0 && $indentLength % 4 !== 0) {
                $issues[] = "Line " . ($lineNum + 1) . " has incorrect indentation (should be multiples of 4 spaces)";
            }
        }
    }

    return $issues;
}

/**
 * Function to automatically fix common code style issues
 */
function fixCodeStyle($filePath) {
    $content = file_get_contents($filePath);

    // Fix indentation issues (convert tabs to spaces, fix inconsistent spaces)
    $content = preg_replace('/^\t+/m', '    ', $content); // Tabs to 4 spaces
    $content = preg_replace('/^( {1,3})\S/m', '    $1', $content); // 1-3 spaces to 4

    // Fix method naming (convert snake_case to camelCase for public methods)
    $content = preg_replace_callback('/function\s+([a-z]+_[a-z0-9_]+)\s*\(/i', function($matches) {
        $methodName = $matches[1];
        if (strpos($methodName, '_') !== 0) { // Don't fix private methods
            $camelCase = lcfirst(str_replace('_', '', ucwords($methodName, '_')));
            return 'function ' . $camelCase . '(';
        }
        return $matches[0];
    }, $content);

    return file_put_contents($filePath, $content);
}
?>
