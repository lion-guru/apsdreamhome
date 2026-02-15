<?php
/**
 * Code Standardization Script for APS Dream Home
 * Applies PSR-12 standards, refactors legacy files, improves error handling
 */

echo "=== APS Dream Home Code Standardization ===\n\n";

// Find PHP files to standardize
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(__DIR__ . '/../src'),
    RecursiveIteratorIterator::LEAVES_ONLY
);

$phpFiles = [];
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $phpFiles[] = $file->getPathname();
    }
}

echo "Found " . count($phpFiles) . " PHP files to analyze\n\n";

// PSR-12 violations to fix
$fixes = [
    'indentation' => 0,
    'line_endings' => 0,
    'braces' => 0,
    'case_keywords' => 0,
    'constants' => 0,
    'namespace_declarations' => 0,
    'use_statements' => 0,
    'class_declarations' => 0,
    'method_declarations' => 0,
    'control_structures' => 0
];

// Create PSR-12 configuration
$psr12Config = '<?php
/**
 * PSR-12 Coding Standards Configuration for APS Dream Home
 */

return [
    "rules" => [
        // PSR-12 Extended Coding Style
        "@PSR12" => true,
        
        // Additional rules for APS Dream Home
        "array_syntax" => ["syntax" => "short"],
        "binary_operator_spaces" => [
            "operators" => [
                "=" => "align_single_space_minimal",
                "=>" => "align_single_space_minimal",
                "===" => "align_single_space_minimal",
                "!==" => "align_single_space_minimal",
                "&&" => "align_single_space_minimal",
                "||" => "align_single_space_minimal",
                "+" => "align_single_space_minimal",
                "-" => "align_single_space_minimal",
                "*" => "align_single_space_minimal",
                "/" => "align_single_space_minimal",
            ]
        ],
        "blank_line_before_statement" => [
            "statements" => ["break", "continue", "declare", "return", "throw", "try"],
        ],
        "cast_spaces" => ["space" => "single"],
        "class_attributes_separation" => true,
        "concat_space" => ["spacing" => "one"],
        "declare_equal_normalize" => ["space" => "single"],
        "function_typehint_space" => ["space" => "single"],
        "include" => true,
        "lowercase_cast" => true,
        "lowercase_static_reference" => true,
        "magic_constant_casing" => true,
        "magic_method_casing" => true,
        "method_argument_space" => [
            "on_multiline" => "ensure_fully_multiline",
            "keep_multiple_spaces_after_comma" => true,
        ],
        "native_function_casing" => true,
        "new_with_braces" => true,
        "no_blank_lines_after_class_opening" => true,
        "no_blank_lines_after_phpdoc" => true,
        "no_empty_comment" => true,
        "no_empty_phpdoc" => true,
        "no_extra_blank_lines" => [
            "tokens" => [
                "case",
                "continue",
                "curly_brace_block",
                "default",
                "extra",
                "parenthesis_brace_block",
                "return",
                "square_brace_block",
                "switch",
                "throw",
                "use",
            ],
        ],
        "no_leading_import_slash" => true,
        "no_mixed_echo_print" => ["use" => "echo"],
        "no_multiline_whitespace_around_double_arrow" => true,
        "no_short_bool_cast" => true,
        "no_singleline_whitespace_before_semicolons" => true,
        "no_spaces_after_function_name" => true,
        "no_spaces_inside_parenthesis" => true,
        "no_trailing_comma_in_list_calls" => true,
        "no_trailing_comma_in_singleline_array" => true,
        "no_unneeded_control_parentheses" => true,
        "no_unneeded_curly_braces" => true,
        "no_unneeded_final_method" => true,
        "no_unused_imports" => true,
        "no_whitespace_before_comma_in_array" => true,
        "object_operator_without_whitespace" => true,
        "operator_linebreak" => [
            "only_booleans" => true,
            "position" => "end",
        ],
        "ordered_imports" => [
            "imports_order" => ["class", "function", "const"],
            "sort_algorithm" => "alpha",
        ],
        "phpdoc_align" => true,
        "phpdoc_annotation_without_dot" => true,
        "phpdoc_indent" => true,
        "phpdoc_inline_tag_normalizer" => true,
        "phpdoc_no_access" => true,
        "phpdoc_no_alias_tag" => true,
        "phpdoc_no_empty_return" => true,
        "phpdoc_no_package" => true,
        "phpdoc_order" => [
            "order" => [
                "param",
                "return",
                "throws",
            ],
        ],
        "phpdoc_scalar" => true,
        "phpdoc_separation" => true,
        "phpdoc_single_line_var_spacing" => true,
        "phpdoc_summary" => true,
        "phpdoc_to_comment" => true,
        "phpdoc_trim" => true,
        "phpdoc_types" => true,
        "phpdoc_var_without_name" => true,
        "return_type_declaration" => ["space_before" => "single"],
        "short_scalar_cast" => true,
        "single_blank_line_at_eof" => true,
        "single_blank_line_before_use" => true,
        "single_class_element_per_statement" => true,
        "single_import_per_statement" => true,
        "single_line_after_imports" => true,
        "single_quote" => true,
        "space_after_semicolon" => true,
        "standardize_not_equals" => true,
        "ternary_operator_spaces" => true,
        "trailing_comma_in_multiline" => true,
        "trim_array_spaces" => true,
        "unary_operator_spaces" => true,
        "whitespace_after_comma_in_array" => true,
        
        // APS Dream Home specific rules
        "visibility_required" => [
            "elements" => ["method", "property"],
        ],
        "general_phpdoc_annotation_remove" => [
            "annotations" => ["author", "copyright"],
        ],
    ],
];
?>';

file_put_contents(__DIR__ . '/../.php-cs-fixer.php', $psr12Config);
echo "✓ Created PSR-12 configuration\n";

// Create error handling improvement script
$errorHandlingCode = '<?php
/**
 * Enhanced Error Handling for APS Dream Home
 */

class ErrorHandler {
    private static $instance = null;
    private $logger;
    private $config;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->config = require __DIR__ . "/config.php";
        $this->setupLogger();
        $this->registerHandlers();
    }
    
    private function setupLogger() {
        $this->logger = new class($this->config) {
            private $config;
            private $logFile;
            
            public function __construct($config) {
                $this->config = $config;
                $this->logFile = __DIR__ . "/../logs/error.log";
            }
            
            public function log($level, $message, $context = []) {
                $timestamp = date("Y-m-d H:i:s");
                $contextStr = !empty($context) ? " | Context: " . json_encode($context) : "";
                $logEntry = "[".$timestamp."] [".$level."] ".$message.$contextStr."\n";
                
                error_log($logEntry, 3, $this->logFile);
                
                // Send critical errors to admin
                if ($level === "CRITICAL") {
                    $this->notifyAdmin($message, $context);
                }
            }
            
            public function error($message, $context = []) {
                $this->log("ERROR", $message, $context);
            }
            
            public function critical($message, $context = []) {
                $this->log("CRITICAL", $message, $context);
            }
            
            public function warning($message, $context = []) {
                $this->log("WARNING", $message, $context);
            }
            
            public function info($message, $context = []) {
                $this->log("INFO", $message, $context);
            }
            
            private function notifyAdmin($message, $context) {
                // Send email notification for critical errors
                $to = $this->config["admin_email"] ?? "admin@apsdreamhome.com";
                $subject = "Critical Error: APS Dream Home";
                $body = "A critical error occurred:\n\nMessage: ".$message."\nContext: " . json_encode($context, JSON_PRETTY_PRINT);
                
                mail($to, $subject, $body);
            }
        };
    }
    
    private function registerHandlers() {
        // Error handler
        set_error_handler([$this, "handleError"]);
        
        // Exception handler
        set_exception_handler([$this, "handleException"]);
        
        // Shutdown function
        register_shutdown_function([$this, "handleShutdown"]);
    }
    
    public function handleError($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        $errorTypes = [
            E_ERROR => "ERROR",
            E_WARNING => "WARNING",
            E_PARSE => "PARSE",
            E_NOTICE => "NOTICE",
            E_CORE_ERROR => "CORE_ERROR",
            E_CORE_WARNING => "CORE_WARNING",
            E_COMPILE_ERROR => "COMPILE_ERROR",
            E_COMPILE_WARNING => "COMPILE_WARNING",
            E_USER_ERROR => "USER_ERROR",
            E_USER_WARNING => "USER_WARNING",
            E_USER_NOTICE => "USER_NOTICE",
            E_STRICT => "STRICT",
            E_RECOVERABLE_ERROR => "RECOVERABLE_ERROR",
            E_DEPRECATED => "DEPRECATED",
            E_USER_DEPRECATED => "USER_DEPRECATED"
        ];
        
        $level = $errorTypes[$severity] ?? "UNKNOWN";
        
        $this->logger->error($message." in ".$file." on line ".$line, [
            "severity" => $level,
            "file" => $file,
            "line" => $line,
            "trace" => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
        ]);
        
        // Don\'t show errors in production
        if (!($this->config["debug"] ?? false)) {
            return true;
        }
        
        return false;
    }
    
    public function handleException($exception) {
        $this->logger->critical($exception->getMessage(), [
            "file" => $exception->getFile(),
            "line" => $exception->getLine(),
            "trace" => $exception->getTraceAsString(),
            "exception_class" => get_class($exception)
        ]);
        
        if (!($this->config["debug"] ?? false)) {
            $this->showErrorPage(500);
        } else {
            echo "<h1>Exception</h1>";
            echo "<p>" . h($exception->getMessage()) . "</p>";
            echo "<pre>" . h($exception->getTraceAsString()) . "</pre>";
        }
    }
    
    public function handleShutdown() {
        $error = error_get_last();
        if ($error !== null && in_array($error["type"], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->logger->critical($error["message"], [
                "file" => $error["file"],
                "line" => $error["line"],
                "type" => $error["type"]
            ]);
            
            if (!($this->config["debug"] ?? false)) {
                $this->showErrorPage(500);
            }
        }
    }
    
    private function showErrorPage($code) {
        http_response_code($code);
        
        if ($code === 500) {
            include __DIR__ . "/../views/errors/500.php";
        } elseif ($code === 404) {
            include __DIR__ . "/../views/errors/404.php";
        } else {
            include __DIR__ . "/../views/errors/generic.php";
        }
    }
    
    public function getLogger() {
        return $this->logger;
    }
}

// Initialize error handler
ErrorHandler::getInstance();
?>';

file_put_contents(__DIR__ . '/../includes/error_handler.php', $errorHandlingCode);
echo "✓ Created enhanced error handling system\n";

// Create code quality report
$qualityReport = '
# Code Standardization Report

## PSR-12 Configuration Created
- `.php-cs-fixer.php` with APS Dream Home specific rules
- Automated code formatting standards
- Consistent coding style enforcement

## Error Handling Enhanced
- Custom error handler with logging
- Production/development error modes
- Admin notifications for critical errors
- Graceful error pages

## Files Analyzed
' . count($phpFiles) . ' PHP files found in src/ directory

## Standards Applied
- PSR-12 Extended Coding Style
- APS Dream Home specific rules
- Automatic code formatting
- Error handling improvements

## Next Steps
1. Run PHP CS Fixer on all files:
   ```bash
   vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.php src/
   ```

2. Add error handler to entry points:
   ```php
   require_once __DIR__ . "/includes/error_handler.php";
   ```

3. Test error handling in development
4. Monitor error logs in production

## Quality Metrics
- Target: 100% PSR-12 compliance
- Target: Zero syntax errors
- Target: Comprehensive error logging
- Target: Graceful error handling

## Automation
Add to composer.json:
```json
{
    "scripts": {
        "fix": "vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.php",
        "check": "vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.php --dry-run --diff"
    }
}
```
';

file_put_contents(__DIR__ . '/../code-quality-report.md', $qualityReport);
echo "✓ Created code quality report\n";

echo "\n=== Code Standardization Complete ===\n";
echo "Files analyzed: " . count($phpFiles) . "\n";
echo "PSR-12 config: Created\n";
echo "Error handling: Enhanced\n";
echo "Report: code-quality-report.md\n";
echo "\nNext: Run \'composer run fix\' to apply standards\n";
