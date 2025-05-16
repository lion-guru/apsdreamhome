<?php
/**
 * Advanced Code Quality and Style Analysis Tool
 * Provides comprehensive analysis of code quality, style, and potential improvements
 */
class CodeQualityAnalyzer {
    private $projectRoot;
    private $configFile;
    private $logDirectory;
    private $reportFile;
    private $config;
    private $analysisResults = [];

    /**
     * Constructor initializes code quality analysis
     * @param string $projectRoot Root directory of the project
     */
    public function __construct($projectRoot = null) {
        $this->projectRoot = $projectRoot ?? __DIR__;
        $this->configFile = $this->projectRoot . '/config/code_quality_config.json';
        $this->logDirectory = $this->projectRoot . '/logs/code_quality';
        $this->reportFile = $this->logDirectory . '/code_quality_report_' . date('Y-m-d_H-i-s') . '.json';

        $this->setupDirectories();
        $this->loadConfiguration();
    }

    /**
     * Create necessary directories
     */
    private function setupDirectories() {
        $directories = [
            dirname($this->configFile),
            $this->logDirectory,
            $this->projectRoot . '/config'
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        // Create default config if not exists
        $defaultConfigPath = $this->projectRoot . '/config/code_quality_config.json';
        if (!file_exists($defaultConfigPath)) {
            $defaultConfig = [
                'file_extensions' => ['.php', '.js', '.css', '.html'],
                'ignore_directories' => [
                    'vendor', 'node_modules', '.git', 'logs', 'backups', 'config'
                ],
                'code_style_checks' => [
                    'max_line_length' => 120,
                    'max_function_length' => 50,
                    'indentation' => 4,
                    'naming_conventions' => [
                        'class_names' => '^[A-Z][a-zA-Z0-9]*$',
                        'method_names' => '^[a-z][a-zA-Z0-9]*$',
                        'variable_names' => '^[a-z][a-zA-Z0-9]*$'
                    ]
                ],
                'complexity_checks' => [
                    'max_cyclomatic_complexity' => 10,
                    'max_nesting_depth' => 5
                ],
                'security_checks' => [
                    'check_input_validation' => true,
                    'check_output_escaping' => true,
                    'check_sql_injection' => true,
                    'check_xss' => true
                ]
            ];
            
            file_put_contents(
                $defaultConfigPath, 
                json_encode($defaultConfig, JSON_PRETTY_PRINT)
            );
        }
    }

    /**
     * Load or create configuration
     */
    private function loadConfiguration() {
        $defaultConfig = [
            'file_extensions' => ['.php', '.js', '.css', '.html'],
            'ignore_directories' => [
                'vendor', 'node_modules', '.git', 'logs', 'backups'
            ],
            'code_style_checks' => [
                'max_line_length' => 120,
                'max_function_length' => 50,
                'indentation' => 4,
                'naming_conventions' => [
                    'class_names' => '/^[A-Z][a-zA-Z0-9]*$/',
                    'method_names' => '/^[a-z][a-zA-Z0-9]*$/',
                    'variable_names' => '/^[a-z][a-zA-Z0-9]*$/'
                ]
            ],
            'complexity_checks' => [
                'max_cyclomatic_complexity' => 10,
                'max_nesting_depth' => 5
            ],
            'security_checks' => [
                'check_input_validation' => true,
                'check_output_escaping' => true,
                'check_sql_injection' => true,
                'check_xss' => true
            ]
        ];

        if (!file_exists($this->configFile)) {
            file_put_contents(
                $this->configFile, 
                json_encode($defaultConfig, JSON_PRETTY_PRINT)
            );
            $this->config = $defaultConfig;
        } else {
            $this->config = json_decode(file_get_contents($this->configFile), true);
            $this->config = array_merge_recursive($defaultConfig, $this->config);
        }
    }

    /**
     * Perform comprehensive code quality analysis
     */
    public function analyzeCodeQuality() {
        $this->log('Starting code quality analysis');

        // Find files to analyze
        $files = $this->findFilesToAnalyze();

        // Analyze each file
        foreach ($files as $file) {
            $fileAnalysis = $this->analyzeFile($file);
            if (!empty($fileAnalysis['issues'])) {
                $this->analysisResults[$file] = $fileAnalysis;
            }
        }

        // Generate report
        $this->generateReport();

        $this->log('Code quality analysis completed', [
            'total_files_analyzed' => count($files),
            'files_with_issues' => count($this->analysisResults)
        ]);
    }

    /**
     * Find files to analyze
     * @return array List of files to analyze
     */
    private function findFilesToAnalyze() {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->projectRoot, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            // Skip ignored directories
            $skipDirectory = array_reduce(
                $this->config['ignore_directories'], 
                function($carry, $dir) use ($file) {
                    return $carry || strpos($file->getPathname(), $dir) !== false;
                }, 
                false
            );

            if ($skipDirectory) {
                continue;
            }

            // Check file extensions
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array('.' . $extension, $this->config['file_extensions'])) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /**
     * Analyze individual file
     * @param string $filePath Path to file
     * @return array Analysis results
     */
    private function analyzeFile($filePath) {
        $fileContents = file_get_contents($filePath);
        $lines = explode("\n", $fileContents);
        $issues = [];

        // Line length check
        $issues = array_merge(
            $issues, 
            $this->checkLineLength($lines)
        );

        // Code style checks for PHP files
        if (pathinfo($filePath, PATHINFO_EXTENSION) === 'php') {
            $issues = array_merge(
                $issues,
                $this->checkPHPCodeStyle($fileContents, $filePath)
            );
        }

        // Security checks
        $issues = array_merge(
            $issues,
            $this->performSecurityChecks($fileContents, $filePath)
        );

        return [
            'file' => $filePath,
            'issues' => $issues
        ];
    }

    /**
     * Check line length
     * @param array $lines File lines
     * @return array Line length issues
     */
    private function checkLineLength($lines) {
        $issues = [];
        $maxLength = $this->config['code_style_checks']['max_line_length'];

        foreach ($lines as $lineNumber => $line) {
            if (strlen(rtrim($line)) > $maxLength) {
                $issues[] = [
                    'type' => 'style',
                    'severity' => 'warning',
                    'message' => "Line exceeds maximum length of {$maxLength} characters",
                    'line' => $lineNumber + 1
                ];
            }
        }

        return $issues;
    }

    /**
     * Perform PHP-specific code style checks
     * @param string $fileContents File contents
     * @param string $filePath File path
     * @return array Code style issues
     */
    private function checkPHPCodeStyle($fileContents, $filePath) {
        $issues = [];
        $tokens = token_get_all($fileContents);

        // Naming convention checks
        $issues = array_merge(
            $issues,
            $this->checkNamingConventions($tokens)
        );

        // Complexity checks
        $issues = array_merge(
            $issues,
            $this->checkCodeComplexity($fileContents, $filePath)
        );

        return $issues;
    }

    /**
     * Check naming conventions
     * @param array $tokens PHP tokens
     * @return array Naming convention issues
     */
    private function checkNamingConventions($tokens) {
        $issues = [];
        $conventions = $this->config['code_style_checks']['naming_conventions'];

        foreach ($tokens as $token) {
            if (is_array($token)) {
                switch ($token[0]) {
                    case T_CLASS:
                        $nextToken = next($tokens);
                        if (is_array($nextToken) && !preg_match($conventions['class_names'], $nextToken[1])) {
                            $issues[] = [
                                'type' => 'style',
                                'severity' => 'warning',
                                'message' => "Class name does not follow naming convention",
                                'details' => $nextToken[1]
                            ];
                        }
                        break;
                    case T_FUNCTION:
                        $nextToken = next($tokens);
                        if (is_array($nextToken) && !preg_match($conventions['method_names'], $nextToken[1])) {
                            $issues[] = [
                                'type' => 'style',
                                'severity' => 'warning',
                                'message' => "Method name does not follow naming convention",
                                'details' => $nextToken[1]
                            ];
                        }
                        break;
                }
            }
        }

        return $issues;
    }

    /**
     * Check code complexity
     * @param string $fileContents File contents
     * @param string $filePath File path
     * @return array Complexity issues
     */
    private function checkCodeComplexity($fileContents, $filePath) {
        $issues = [];
        $maxComplexity = $this->config['complexity_checks']['max_cyclomatic_complexity'];
        $maxNestingDepth = $this->config['complexity_checks']['max_nesting_depth'];

        // Use PHP-Parser for advanced analysis
        if (!class_exists('PhpParser\ParserFactory')) {
            return $issues;
        }

        $parser = (new PhpParser\ParserFactory)->create(PhpParser\ParserFactory::PREFER_PHP7);
        try {
            $ast = $parser->parse($fileContents);
            $traverser = new PhpParser\NodeTraverser();
            
            $complexityVisitor = new class extends PhpParser\NodeVisitorAbstract {
                public $complexity = 0;
                public $nestingDepth = 0;
                public $maxNestingDepth = 0;

                public function enterNode(PhpParser\Node $node) {
                    if ($node instanceof PhpParser\Node\Stmt\If_) {
                        $this->complexity++;
                    }

                    if ($node instanceof PhpParser\Node\Stmt\Else_) {
                        $this->complexity++;
                    }

                    if ($node instanceof PhpParser\Node\Stmt\ElseIf_) {
                        $this->complexity++;
                    }

                    if ($node instanceof PhpParser\Node\Stmt\For_) {
                        $this->complexity++;
                    }

                    if ($node instanceof PhpParser\Node\Stmt\Foreach_) {
                        $this->complexity++;
                    }

                    if ($node instanceof PhpParser\Node\Stmt\While_) {
                        $this->complexity++;
                    }

                    if ($node instanceof PhpParser\Node\Stmt\Do_) {
                        $this->complexity++;
                    }

                    if ($node instanceof PhpParser\Node\Stmt\Switch_) {
                        $this->complexity++;
                    }

                    $this->nestingDepth++;
                    $this->maxNestingDepth = max($this->maxNestingDepth, $this->nestingDepth);
                }

                public function leaveNode(PhpParser\Node $node) {
                    $this->nestingDepth--;
                }
            };

            $traverser->addVisitor($complexityVisitor);
            $traverser->traverse($ast);

            // Check cyclomatic complexity
            if ($complexityVisitor->complexity > $maxComplexity) {
                $issues[] = [
                    'type' => 'complexity',
                    'severity' => 'warning',
                    'message' => "High cyclomatic complexity",
                    'details' => "Complexity: {$complexityVisitor->complexity} (max: {$maxComplexity})"
                ];
            }

            // Check nesting depth
            if ($complexityVisitor->maxNestingDepth > $maxNestingDepth) {
                $issues[] = [
                    'type' => 'complexity',
                    'severity' => 'warning',
                    'message' => "Excessive nesting depth",
                    'details' => "Nesting depth: {$complexityVisitor->maxNestingDepth} (max: {$maxNestingDepth})"
                ];
            }
        } catch (PhpParser\Error $e) {
            // Parser error, log but continue
            $issues[] = [
                'type' => 'parser',
                'severity' => 'error',
                'message' => "Failed to parse file",
                'details' => $e->getMessage()
            ];
        }

        return $issues;
    }

    /**
     * Perform security checks
     * @param string $fileContents File contents
     * @param string $filePath File path
     * @return array Security issues
     */
    private function performSecurityChecks($fileContents, $filePath) {
        $issues = [];
        $securityChecks = $this->config['security_checks'];

        // Input validation check
        if ($securityChecks['check_input_validation']) {
            $inputValidationIssues = $this->checkInputValidation($fileContents);
            $issues = array_merge($issues, $inputValidationIssues);
        }

        // Output escaping check
        if ($securityChecks['check_output_escaping']) {
            $outputEscapingIssues = $this->checkOutputEscaping($fileContents);
            $issues = array_merge($issues, $outputEscapingIssues);
        }

        // SQL injection check
        if ($securityChecks['check_sql_injection']) {
            $sqlInjectionIssues = $this->checkSQLInjection($fileContents);
            $issues = array_merge($issues, $sqlInjectionIssues);
        }

        // XSS check
        if ($securityChecks['check_xss']) {
            $xssIssues = $this->checkXSS($fileContents);
            $issues = array_merge($issues, $xssIssues);
        }

        return $issues;
    }

    /**
     * Check for input validation
     * @param string $fileContents File contents
     * @return array Input validation issues
     */
    private function checkInputValidation($fileContents) {
        $issues = [];
        $patterns = [
            '/\$_(?:GET|POST|REQUEST|COOKIE)// SECURITY: Removed potentially dangerous code/' => 'Direct input access without validation',
            '/filter_var\s*\(.*\)/' => 'Potential unfiltered input'
        ];

        foreach ($patterns as $pattern => $message) {
            if (preg_match($pattern, $fileContents)) {
                $issues[] = [
                    'type' => 'security',
                    'severity' => 'warning',
                    'message' => $message
                ];
            }
        }

        return $issues;
    }

    /**
     * Check for output escaping
     * @param string $fileContents File contents
     * @return array Output escaping issues
     */
    private function checkOutputEscaping($fileContents) {
        $issues = [];
        $patterns = [
            '/echo\s+\$/' => 'Potential unescaped output',
            '/print\s+\$/' => 'Potential unescaped output'
        ];

        foreach ($patterns as $pattern => $message) {
            if (preg_match($pattern, $fileContents)) {
                $issues[] = [
                    'type' => 'security',
                    'severity' => 'warning',
                    'message' => $message
                ];
            }
        }

        return $issues;
    }

    /**
     * Check for SQL injection risks
     * @param string $fileContents File contents
     * @return array SQL injection issues
     */
    private function checkSQLInjection($fileContents) {
        $issues = [];
        $patterns = [
            '/\$.*\s*\.\s*[\'"]SELECT/' => 'Potential SQL injection risk',
            '/query\s*\(.*\$/' => 'Potential SQL injection risk'
        ];

        foreach ($patterns as $pattern => $message) {
            if (preg_match($pattern, $fileContents)) {
                $issues[] = [
                    'type' => 'security',
                    'severity' => 'warning',
                    'message' => $message
                ];
            }
        }

        return $issues;
    }

    /**
     * Check for XSS risks
     * @param string $fileContents File contents
     * @return array XSS issues
     */
    private function checkXSS($fileContents) {
        $issues = [];
        $patterns = [
            '/echo\s+htmlspecialchars\s*\(/' => 'Potential XSS risk',
            '/print\s+htmlspecialchars\s*\(/' => 'Potential XSS risk'
        ];

        foreach ($patterns as $pattern => $message) {
            if (!preg_match($pattern, $fileContents)) {
                $issues[] = [
                    'type' => 'security',
                    'severity' => 'warning',
                    'message' => $message
                ];
            }
        }

        return $issues;
    }

    /**
     * Generate analysis report
     */
    private function generateReport() {
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'project_root' => $this->projectRoot,
            'total_files_analyzed' => count($this->findFilesToAnalyze()),
            'files_with_issues' => count($this->analysisResults),
            'issues' => $this->analysisResults
        ];

        // Save JSON report
        file_put_contents(
            $this->reportFile, 
            json_encode($report, JSON_PRETTY_PRINT)
        );

        // Generate HTML report
        $this->generateHTMLReport($report);
    }

    /**
     * Generate HTML report
     * @param array $report Analysis report
     */
    private function generateHTMLReport($report) {
        $htmlReportFile = str_replace('.json', '.html', $this->reportFile);
        
        $html = "<html><body>";
        $html .= "<h1>Code Quality Analysis Report</h1>";
        $html .= "<p>Timestamp: {$report['timestamp']}</p>";
        $html .= "<p>Project Root: {$report['project_root']}</p>";
        
        $html .= "<h2>Overview</h2>";
        $html .= "<ul>";
        $html .= "<li>Total Files Analyzed: {$report['total_files_analyzed']}</li>";
        $html .= "<li>Files with Issues: {$report['files_with_issues']}</li>";
        $html .= "</ul>";

        $html .= "<h2>Detailed Issues</h2>";
        foreach ($report['issues'] as $file => $fileAnalysis) {
            $html .= "<h3>" . htmlspecialchars($file) . "</h3>";
            $html .= "<table border='1'>";
            $html .= "<tr><th>Type</th><th>Severity</th><th>Message</th><th>Details</th></tr>";
            
            foreach ($fileAnalysis['issues'] as $issue) {
                $html .= "<tr>";
                $html .= "<td>" . htmlspecialchars($issue['type']) . "</td>";
                $html .= "<td>" . htmlspecialchars($issue['severity']) . "</td>";
                $html .= "<td>" . htmlspecialchars($issue['message']) . "</td>";
                $html .= "<td>" . htmlspecialchars(json_encode($issue['details'] ?? '')) . "</td>";
                $html .= "</tr>";
            }
            
            $html .= "</table>";
        }

        $html .= "</body></html>";

        file_put_contents($htmlReportFile, $html);
    }

    /**
     * Log messages
     * @param string $message Log message
     * @param array $context Additional context
     */
    private function log($message, $context = []) {
        $logFile = $this->logDirectory . '/code_quality_analysis_' . date('Y-m-d') . '.log';
        
        $logEntry = sprintf(
            "[%s] %s\n%s\n\n",
            date('Y-m-d H:i:s'),
            $message,
            json_encode($context, JSON_PRETTY_PRINT)
        );

        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
}

// Execute code quality analysis if run directly
if (php_sapi_name() === 'cli') {
    try {
        $analyzer = new CodeQualityAnalyzer();
        $analyzer->analyzeCodeQuality();
        
        echo "Code quality analysis completed.\n";
        echo "Report generated successfully.\n";
    } catch (Exception $e) {
        echo "Code quality analysis failed: " . $e->getMessage() . "\n";
    }
} else {
    // Web interface for report
    try {
        $analyzer = new CodeQualityAnalyzer();
        $analyzer->analyzeCodeQuality();
        
        // Display the most recent report
        $reportFiles = glob(__DIR__ . '/logs/code_quality/code_quality_report_*.html');
        if (!empty($reportFiles)) {
            $latestReport = max($reportFiles);
            echo file_get_contents($latestReport);
        } else {
            echo "No reports found.";
        }
    } catch (Exception $e) {
        echo "Report generation failed: " . $e->getMessage();
    }
}

