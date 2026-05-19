<?php
/**
 * Performance Analysis Tool for APS Dream Home
 * Analyzes code performance and identifies optimization opportunities
 */

class PerformanceAnalyzer {
    private $projectPath;
    private $results = [];
    private $metrics = [];
    
    public function __construct($projectPath) {
        $this->projectPath = $projectPath;
    }
    
    /**
     * Run complete performance analysis
     */
    public function runCompleteAnalysis() {
        echo "=== APS DREAM HOME - PERFORMANCE ANALYSIS ===\n\n";
        
        $this->analyzeDatabaseQueries();
        $this->analyzeControllerPerformance();
        $this->analyzeViewComplexity();
        $this->analyzeImageOptimization();
        $this->analyzeCachingOpportunities();
        $this->generateRecommendations();
        
        return $this->results;
    }
    
    /**
     * Analyze database query patterns
     */
    private function analyzeDatabaseQueries() {
        echo "[1/5] Analyzing database query patterns...\n";
        
        $controllers = glob($this->projectPath . '/app/Http/Controllers/**/*.php', GLOB_BRACE);
        $queryPatterns = [
            'SELECT *' => 'Avoid SELECT *, specify columns instead',
            'ORDER BY RAND()' => 'Avoid RAND() for large tables, use alternative approaches',
            'LIKE %...%' => 'Leading wildcard prevents index use',
            'N+1 Query' => 'Check for N+1 query patterns (queries in loops)'
        ];
        
        $issues = [];
        $totalFiles = count($controllers);
        $filesWithIssues = 0;
        
        foreach ($controllers as $controller) {
            $content = file_get_contents($controller);
            $hasIssue = false;
            
            foreach ($queryPatterns as $pattern => $warning) {
                if (strpos($content, $pattern) !== false) {
                    $issues[] = [
                        'file' => str_replace($this->projectPath . '/', '', $controller),
                        'pattern' => $pattern,
                        'warning' => $warning
                    ];
                    $hasIssue = true;
                }
            }
            
            // Check for loops with database queries (N+1 pattern)
            if (preg_match('/foreach.*DB::|foreach.*\$[a-z_]+->/', $content)) {
                $issues[] = [
                    'file' => str_replace($this->projectPath . '/', '', $controller),
                    'pattern' => 'Potential N+1 Query',
                    'warning' => 'Review loops that might contain database queries'
                ];
                $hasIssue = true;
            }
            
            if ($hasIssue) {
                $filesWithIssues++;
            }
        }
        
        $this->metrics['database_analysis'] = [
            'total_controllers' => $totalFiles,
            'files_with_issues' => $filesWithIssues,
            'issue_percentage' => round(($filesWithIssues / $totalFiles) * 100, 2),
            'issues_found' => count($issues)
        ];
        
        $this->results['database_queries'] = $issues;
        
        echo "  Analyzed $totalFiles controllers\n";
        echo "  Found issues in $filesWithIssues files\n";
        echo "  Issues found: " . count($issues) . "\n\n";
    }
    
    /**
     * Analyze controller performance
     */
    private function analyzeControllerPerformance() {
        echo "[2/5] Analyzing controller performance...\n";
        
        $controllers = glob($this->projectPath . '/app/Http/Controllers/**/*.php', GLOB_BRACE);
        $performanceIssues = [];
        
        foreach ($controllers as $controller) {
            $content = file_get_contents($controller);
            $relativePath = str_replace($this->projectPath . '/', '', $controller);
            
            // Check for heavy operations in controllers
            if (strpos($content, 'file_get_contents(') !== false) {
                $performanceIssues[] = [
                    'file' => $relativePath,
                    'issue' => 'Synchronous file operations in controller',
                    'severity' => 'high',
                    'recommendation' => 'Use asynchronous operations or caching'
                ];
            }
            
            // Check for sleep() or similar blocking calls
            if (strpos($content, 'sleep(') !== false || strpos($content, 'usleep(') !== false) {
                $performanceIssues[] = [
                    'file' => $relativePath,
                    'issue' => 'Blocking sleep calls in controller',
                    'severity' => 'high',
                    'recommendation' => 'Remove sleep calls or move to background jobs'
                ];
            }
            
            // Check for large arrays being loaded
            if (preg_match('/->all\(\)|->get\(\)/', $content)) {
                $performanceIssues[] = [
                    'file' => $relativePath,
                    'issue' => 'Potential large dataset loading without pagination',
                    'severity' => 'medium',
                    'recommendation' => 'Add pagination or use chunk()'
                ];
            }
            
            // Check controller method complexity
            preg_match_all('/public function (\w+)/', $content, $methods);
            foreach ($methods[1] as $method) {
                $methodPattern = '/public function ' . $method . '\([^)]*\)[^{]*\{([^}]*(?:\{[^}]*\}[^}]*)*)\}/';
                if (preg_match($methodPattern, $content, $matches)) {
                    $methodBody = $matches[1];
                    $lineCount = substr_count($methodBody, "\n");
                    
                    if ($lineCount > 50) {
                        $performanceIssues[] = [
                            'file' => $relativePath,
                            'issue' => "Complex method: $method() ($lineCount lines)",
                            'severity' => 'low',
                            'recommendation' => 'Consider refactoring into smaller methods'
                        ];
                    }
                }
            }
        }
        
        $this->results['controller_performance'] = $performanceIssues;
        
        $highSeverity = count(array_filter($performanceIssues, fn($i) => $i['severity'] === 'high'));
        $mediumSeverity = count(array_filter($performanceIssues, fn($i) => $i['severity'] === 'medium'));
        
        echo "  High severity issues: $highSeverity\n";
        echo "  Medium severity issues: $mediumSeverity\n";
        echo "  Total performance issues: " . count($performanceIssues) . "\n\n";
    }
    
    /**
     * Analyze view complexity
     */
    private function analyzeViewComplexity() {
        echo "[3/5] Analyzing view complexity...\n";
        
        $views = glob($this->projectPath . '/app/views/**/*.php', GLOB_BRACE);
        $complexViews = [];
        
        foreach ($views as $view) {
            $content = file_get_contents($view);
            $relativePath = str_replace($this->projectPath . '/', '', $view);
            $lineCount = substr_count($content, "\n");
            
            // Check for complex views
            if ($lineCount > 200) {
                $complexViews[] = [
                    'file' => $relativePath,
                    'lines' => $lineCount,
                    'issue' => 'Large view file',
                    'recommendation' => 'Consider breaking into partials'
                ];
            }
            
            // Check for inline styles
            $inlineStyles = substr_count($content, 'style=');
            if ($inlineStyles > 5) {
                $complexViews[] = [
                    'file' => $relativePath,
                    'issue' => 'Multiple inline styles found',
                    'count' => $inlineStyles,
                    'recommendation' => 'Move styles to CSS files'
                ];
            }
            
            // Check for inline scripts
            $inlineScripts = substr_count($content, '<script>');
            if ($inlineScripts > 3) {
                $complexViews[] = [
                    'file' => $relativePath,
                    'issue' => 'Multiple inline script tags',
                    'count' => $inlineScripts,
                    'recommendation' => 'Combine scripts or use external JS files'
                ];
            }
            
            // Check for database queries in views
            if (preg_match('/DB::|\$[a-z_]+->/', $content)) {
                $complexViews[] = [
                    'file' => $relativePath,
                    'issue' => 'Database queries detected in view',
                    'severity' => 'high',
                    'recommendation' => 'Move database queries to controllers'
                ];
            }
        }
        
        $this->results['view_complexity'] = $complexViews;
        
        echo "  Analyzed " . count($views) . " view files\n";
        echo "  Complex views identified: " . count($complexViews) . "\n\n";
    }
    
    /**
     * Analyze image optimization opportunities
     */
    private function analyzeImageOptimization() {
        echo "[4/5] Analyzing image optimization...\n";
        
        $imagesDir = $this->projectPath . '/assets/images';
        $imageIssues = [];
        
        if (is_dir($imagesDir)) {
            $imageFiles = glob($imagesDir . '/**/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
            $totalSize = 0;
            $largeFiles = [];
            
            foreach ($imageFiles as $image) {
                $size = filesize($image);
                $totalSize += $size;
                
                if ($size > 500 * 1024) { // Larger than 500KB
                    $largeFiles[] = [
                        'file' => str_replace($this->projectPath . '/', '', $image),
                        'size' => $this->formatFileSize($size),
                        'recommendation' => 'Consider compression or WebP format'
                    ];
                }
            }
            
            $imageIssues = [
                'total_images' => count($imageFiles),
                'total_size' => $this->formatFileSize($totalSize),
                'large_files' => $largeFiles,
                'recommendations' => [
                    'Convert images to WebP format for better compression',
                    'Implement lazy loading for images',
                    'Use responsive images with srcset',
                    'Consider using a CDN for static assets'
                ]
            ];
        }
        
        $this->results['image_optimization'] = $imageIssues;
        
        echo "  Total images: " . ($imageIssues['total_images'] ?? 0) . "\n";
        echo "  Total size: " . ($imageIssues['total_size'] ?? 'N/A') . "\n";
        echo "  Large files: " . count($imageIssues['large_files'] ?? []) . "\n\n";
    }
    
    /**
     * Analyze caching opportunities
     */
    private function analyzeCachingOpportunities() {
        echo "[5/5] Analyzing caching opportunities...\n";
        
        $cachingOpportunities = [
            'database_queries' => [
                'opportunity' => 'Database query caching',
                'description' => 'Cache frequently accessed data like projects, locations',
                'implementation' => 'Use Redis or Memcached for query results'
            ],
            'view_caching' => [
                'opportunity' => 'View fragment caching',
                'description' => 'Cache static view components like headers, footers',
                'implementation' => 'Implement view caching for layout components'
            ],
            'api_responses' => [
                'opportunity' => 'API response caching',
                'description' => 'Cache API responses that don\'t change frequently',
                'implementation' => 'Add cache headers or cache API responses'
            ],
            'static_assets' => [
                'opportunity' => 'Static asset caching',
                'description' => 'Enable browser caching for CSS, JS, images',
                'implementation' => 'Configure cache headers and versioning'
            ]
        ];
        
        $this->results['caching_opportunities'] = $cachingOpportunities;
        
        echo "  Identified " . count($cachingOpportunities) . " caching opportunities\n\n";
    }
    
    /**
     * Generate optimization recommendations
     */
    private function generateRecommendations() {
        echo "=== GENERATING RECOMMENDATIONS ===\n\n";
        
        $recommendations = [];
        
        // High priority based on issues found
        if (!empty($this->results['database_queries'])) {
            $recommendations[] = [
                'priority' => 'high',
                'category' => 'Database',
                'action' => 'Optimize database queries',
                'details' => 'Address ' . count($this->results['database_queries']) . ' query issues found'
            ];
        }
        
        if (!empty($this->results['controller_performance'])) {
            $highSeverity = count(array_filter($this->results['controller_performance'], fn($i) => $i['severity'] === 'high'));
            if ($highSeverity > 0) {
                $recommendations[] = [
                    'priority' => 'high',
                    'category' => 'Backend',
                    'action' => 'Fix high-severity performance issues',
                    'details' => "$highSeverity critical issues in controllers"
                ];
            }
        }
        
        // Medium priority
        if (!empty($this->results['view_complexity'])) {
            $recommendations[] = [
                'priority' => 'medium',
                'category' => 'Frontend',
                'action' => 'Optimize view complexity',
                'details' => 'Simplify ' . count($this->results['view_complexity']) . ' complex views'
            ];
        }
        
        // Low priority / enhancements
        if (!empty($this->results['image_optimization']['large_files'])) {
            $recommendations[] = [
                'priority' => 'low',
                'category' => 'Assets',
                'action' => 'Optimize images',
                'details' => 'Compress ' . count($this->results['image_optimization']['large_files']) . ' large images'
            ];
        }
        
        $recommendations[] = [
            'priority' => 'medium',
            'category' => 'Caching',
            'action' => 'Implement caching layer',
            'details' => 'Add caching for frequently accessed data'
        ];
        
        $this->results['recommendations'] = $recommendations;
        
        echo "Generated " . count($recommendations) . " prioritized recommendations\n\n";
    }
    
    /**
     * Format file size for display
     */
    private function formatFileSize($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
    
    /**
     * Generate detailed report
     */
    public function generateReport() {
        $report = "\n=== PERFORMANCE ANALYSIS REPORT ===\n\n";
        $report .= "METRICS:\n";
        foreach ($this->metrics as $key => $metric) {
            $report .= "  $key: " . json_encode($metric, JSON_PRETTY_PRINT) . "\n";
        }
        
        $report .= "\nRECOMMENDATIONS:\n";
        foreach ($this->results['recommendations'] ?? [] as $rec) {
            $report .= "  [{$rec['priority']}] {$rec['category']}: {$rec['action']}\n";
            $report .= "    Details: {$rec['details']}\n";
        }
        
        return $report;
    }
    
    /**
     * Save report to file
     */
    public function saveReport($filename = 'performance_analysis_report.md') {
        $content = "# APS Dream Home - Performance Analysis Report\n\n";
        $content .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";
        
        // Executive Summary
        $content .= "## Executive Summary\n\n";
        $content .= "- Total Controllers Analyzed: " . ($this->metrics['database_analysis']['total_controllers'] ?? 0) . "\n";
        $content .= "- Database Issues Found: " . ($this->metrics['database_analysis']['issues_found'] ?? 0) . "\n";
        $content .= "- Controller Performance Issues: " . count($this->results['controller_performance'] ?? []) . "\n";
        $content .= "- Complex Views: " . count($this->results['view_complexity'] ?? []) . "\n";
        
        // Detailed sections
        if (!empty($this->results['database_queries'])) {
            $content .= "\n## Database Query Issues\n\n";
            foreach ($this->results['database_queries'] as $issue) {
                $content .= "- **{$issue['file']}**: {$issue['pattern']}\n";
                $content .= "  Warning: {$issue['warning']}\n\n";
            }
        }
        
        if (!empty($this->results['controller_performance'])) {
            $content .= "\n## Controller Performance Issues\n\n";
            foreach ($this->results['controller_performance'] as $issue) {
                $content .= "- **{$issue['file']}**: {$issue['issue']} [{$issue['severity']}]\n";
                $content .= "  Recommendation: {$issue['recommendation']}\n\n";
            }
        }
        
        if (!empty($this->results['view_complexity'])) {
            $content .= "\n## View Complexity Issues\n\n";
            foreach ($this->results['view_complexity'] as $issue) {
                $content .= "- **{$issue['file']}**: {$issue['issue']}\n";
                if (isset($issue['count'])) {
                    $content .= "  Count: {$issue['count']}\n";
                }
                $content .= "  Recommendation: {$issue['recommendation']}\n\n";
            }
        }
        
        if (!empty($this->results['recommendations'])) {
            $content .= "\n## Prioritized Recommendations\n\n";
            foreach ($this->results['recommendations'] as $rec) {
                $content .= "### [{$rec['priority']}] {$rec['category']}: {$rec['action']}\n";
                $content .= "**Details:** {$rec['details']}\n\n";
            }
        }
        
        file_put_contents($this->projectPath . '/' . $filename, $content);
        echo "Report saved to: $filename\n";
    }
}

// Run analysis if executed directly
if (php_sapi_name() === 'cli') {
    $analyzer = new PerformanceAnalyzer(__DIR__ . '/..');
    $analyzer->runCompleteAnalysis();
    $analyzer->saveReport();
    echo $analyzer->generateReport();
}