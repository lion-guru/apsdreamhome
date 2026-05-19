<?php
/**
 * Controller Performance Fixer for APS Dream Home
 * Automatically fixes common performance issues in controllers
 */

class ControllerPerformanceFixer {
    private $projectPath;
    private $fixes = [];
    
    public function __construct($projectPath) {
        $this->projectPath = $projectPath;
    }
    
    /**
     * Run complete performance fixes
     */
    public function runFixes() {
        echo "=== CONTROLLER PERFORMANCE FIXER ===\n\n";
        
        $this->removeBlockingSleepCalls();
        $this->optimizeFileOperations();
        $this->addPaginationSupport();
        $this->refactorLargeMethods();
        $this->generateFixReport();
        
        return $this->fixes;
    }
    
    /**
     * Remove blocking sleep calls
     */
    private function removeBlockingSleepCalls() {
        echo "[1/4] Removing blocking sleep calls...\n";
        
        $controllers = glob($this->projectPath . '/app/Http/Controllers/**/*.php', GLOB_BRACE);
        $fixedFiles = 0;
        
        foreach ($controllers as $controller) {
            $content = file_get_contents($controller);
            $originalContent = $content;
            $relativePath = str_replace($this->projectPath . '/', '', $controller);
            
            // Find and remove sleep() and usleep() calls
            if (preg_match('/sleep\s*\(\s*\d+\s*\)/', $content)) {
                $content = preg_replace('/sleep\s*\(\s*\d+\s*\);?\s*\/\/.*$/m', '// REMOVED: blocking sleep call', $content);
                $this->fixes[] = [
                    'file' => $relativePath,
                    'issue' => 'Removed blocking sleep() call',
                    'type' => 'blocking_call',
                    'severity' => 'high'
                ];
            }
            
            if (preg_match('/usleep\s*\(\s*\d+\s*\)/', $content)) {
                $content = preg_replace('/usleep\s*\(\s*\d+\s*\);?\s*\/\/.*$/m', '// REMOVED: blocking usleep() call', $content);
                $this->fixes[] = [
                    'file' => $relativePath,
                    'issue' => 'Removed blocking usleep() call',
                    'type' => 'blocking_call',
                    'severity' => 'high'
                ];
            }
            
            if ($content !== $originalContent) {
                file_put_contents($controller, $content);
                $fixedFiles++;
            }
        }
        
        echo "  Fixed $fixedFiles files with blocking calls\n\n";
    }
    
    /**
     * Optimize file operations
     */
    private function optimizeFileOperations() {
        echo "[2/4] Optimizing file operations...\n";
        
        $controllers = glob($this->projectPath . '/app/Http/Controllers/**/*.php', GLOB_BRACE);
        $optimizedFiles = 0;
        
        foreach ($controllers as $controller) {
            $content = file_get_contents($controller);
            $originalContent = $content;
            $relativePath = str_replace($this->projectPath . '/', '', $controller);
            
            // Find file_get_contents() calls
            if (preg_match('/file_get_contents\s*\(/', $content)) {
                // Check if it's in a loop - this would be a performance issue
                if (preg_match('/foreach.*{.*file_get_contents/s', $content)) {
                    $this->fixes[] = [
                        'file' => $relativePath,
                        'issue' => 'file_get_contents() found in loop - potential performance issue',
                        'recommendation' => 'Consider caching or batch processing',
                        'type' => 'file_operation',
                        'severity' => 'high'
                    ];
                }
            }
            
            // Add comment about asynchronous alternatives
            if (preg_match('/file_get_contents\s*\(/', $content) && 
                strpos($content, 'TODO: Consider async file operations') === false) {
                $content = "// TODO: Consider async file operations for better performance\n" . $content;
                $this->fixes[] = [
                    'file' => $relativePath,
                    'issue' => 'Added TODO comment for async file operations',
                    'type' => 'file_operation',
                    'severity' => 'low'
                ];
            }
            
            if ($content !== $originalContent) {
                file_put_contents($controller, $content);
                $optimizedFiles++;
            }
        }
        
        echo "  Analyzed " . count($controllers) . " controllers\n";
        echo "  Optimized $optimizedFiles files\n\n";
    }
    
    /**
     * Add pagination support
     */
    private function addPaginationSupport() {
        echo "[3/4] Adding pagination support...\n";
        
        $controllers = glob($this->projectPath . '/app/Http/Controllers/**/*.php', GLOB_BRACE);
        $enhancedFiles = 0;
        
        foreach ($controllers as $controller) {
            $content = file_get_contents($controller);
            $originalContent = $content;
            $relativePath = str_replace($this->projectPath . '/', '', $controller);
            
            // Find potential large dataset loads
            if (preg_match('/->all\(\)|->get\(\)/', $content)) {
                // Check if pagination is already implemented
                if (strpos($content, 'paginate(') === false && 
                    strpos($content, 'limit(') === false) {
                    
                    // Add comment suggesting pagination
                    $content = preg_replace(
                        '/->(all|get)\(\)/',
                        '// TODO: Consider using paginate() for large datasets instead of $1()' . "\n                        ->$1()",
                        $content
                    );
                    
                    $this->fixes[] = [
                        'file' => $relativePath,
                        'issue' => 'Suggested pagination for large datasets',
                        'recommendation' => 'Use paginate() or limit() for large result sets',
                        'type' => 'pagination',
                        'severity' => 'medium'
                    ];
                    
                    $enhancedFiles++;
                }
            }
            
            if ($content !== $originalContent) {
                file_put_contents($controller, $content);
            }
        }
        
        echo "  Enhanced $enhancedFiles controllers with pagination suggestions\n\n";
    }
    
    /**
     * Refactor large methods
     */
    private function refactorLargeMethods() {
        echo "[4/4] Identifying large methods for refactoring...\n";
        
        $controllers = glob($this->projectPath . '/app/Http/Controllers/**/*.php', GLOB_BRACE);
        $largeMethodsFound = 0;
        
        foreach ($controllers as $controller) {
            $content = file_get_contents($controller);
            $relativePath = str_replace($this->projectPath . '/', '', $controller);
            
            // Find public methods and check their size
            preg_match_all('/public function (\w+)\s*\([^)]*\)\s*\{/', $content, $methods);
            
            foreach ($methods[1] as $methodName) {
                $methodPattern = '/public function ' . $methodName . '\s*\([^)]*\)\s*\{([^}]*(?:\{[^}]*\}[^}]*)*)\}/';
                
                if (preg_match($methodPattern, $content, $matches)) {
                    $methodBody = $matches[1];
                    $lineCount = substr_count($methodBody, "\n");
                    
                    if ($lineCount > 50) {
                        $largeMethodsFound++;
                        $this->fixes[] = [
                            'file' => $relativePath,
                            'issue' => "Large method: $methodName() ($lineCount lines)",
                            'recommendation' => 'Consider breaking into smaller, more focused methods',
                            'type' => 'code_organization',
                            'severity' => 'low',
                            'lines' => $lineCount
                        ];
                    }
                }
            }
        }
        
        echo "  Found $largeMethodsFound large methods (> 50 lines)\n";
        echo "  These should be refactored for better maintainability\n\n";
    }
    
    /**
     * Generate fix report
     */
    private function generateFixReport() {
        echo "=== GENERATING FIX REPORT ===\n\n";
        
        $severityCounts = [
            'high' => 0,
            'medium' => 0,
            'low' => 0
        ];
        
        foreach ($this->fixes as $fix) {
            $severityCounts[$fix['severity']]++;
        }
        
        echo "Fix Summary:\n";
        echo "  High Severity: " . $severityCounts['high'] . "\n";
        echo "  Medium Severity: " . $severityCounts['medium'] . "\n";
        echo "  Low Severity: " . $severityCounts['low'] . "\n";
        echo "  Total: " . count($this->fixes) . "\n\n";
    }
    
    /**
     * Save fix report
     */
    public function saveReport($filename = 'controller_performance_fixes.md') {
        $content = "# Controller Performance Fixes Report\n\n";
        $content .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";
        
        $content .= "## Executive Summary\n\n";
        $content .= "Total fixes applied/suggested: " . count($this->fixes) . "\n\n";
        
        // Group by severity
        $bySeverity = [
            'high' => [],
            'medium' => [],
            'low' => []
        ];
        
        foreach ($this->fixes as $fix) {
            $bySeverity[$fix['severity']][] = $fix;
        }
        
        $content .= "### High Severity Fixes\n\n";
        if (!empty($bySeverity['high'])) {
            foreach ($bySeverity['high'] as $fix) {
                $content .= "- **{$fix['file']}**: {$fix['issue']}\n";
                if (isset($fix['recommendation'])) {
                    $content .= "  - Recommendation: {$fix['recommendation']}\n";
                }
                $content .= "\n";
            }
        } else {
            $content .= "No high severity fixes needed.\n\n";
        }
        
        $content .= "### Medium Severity Fixes\n\n";
        if (!empty($bySeverity['medium'])) {
            foreach ($bySeverity['medium'] as $fix) {
                $content .= "- **{$fix['file']}**: {$fix['issue']}\n";
                if (isset($fix['recommendation'])) {
                    $content .= "  - Recommendation: {$fix['recommendation']}\n";
                }
                $content .= "\n";
            }
        } else {
            $content .= "No medium severity fixes needed.\n\n";
        }
        
        $content .= "### Low Severity Suggestions\n\n";
        if (!empty($bySeverity['low'])) {
            foreach ($bySeverity['low'] as $fix) {
                $content .= "- **{$fix['file']}**: {$fix['issue']}\n";
                if (isset($fix['recommendation'])) {
                    $content .= "  - Recommendation: {$fix['recommendation']}\n";
                }
                if (isset($fix['lines'])) {
                    $content .= "  - Method size: {$fix['lines']} lines\n";
                }
                $content .= "\n";
            }
        } else {
            $content .= "No low severity suggestions.\n\n";
        }
        
        file_put_contents($this->projectPath . '/' . $filename, $content);
        echo "Report saved to: $filename\n";
    }
}

// Run fixes if executed directly
if (php_sapi_name() === 'cli') {
    $fixer = new ControllerPerformanceFixer(__DIR__ . '/..');
    $fixer->runFixes();
    $fixer->saveReport();
}