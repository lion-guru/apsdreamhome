<?php
/**
 * Mobile Responsiveness Analyzer for APS Dream Home
 * Analyzes and suggests improvements for mobile responsiveness
 */

class MobileResponsivenessAnalyzer {
    private $projectPath;
    private $issues = [];
    
    public function __construct($projectPath) {
        $this->projectPath = $projectPath;
    }
    
    /**
     * Run complete mobile responsiveness analysis
     */
    public function runAnalysis() {
        echo "=== MOBILE RESPONSIVENESS ANALYZER ===\n\n";
        
        $this->analyzeCSSFiles();
        $this->analyzeViewFiles();
        $this->analyzeViewportMeta();
        $this->analyzeTouchTargets();
        $this->analyzeImageResponsiveness();
        $this->generateRecommendations();
        
        return $this->issues;
    }
    
    /**
     * Analyze CSS files for responsiveness
     */
    private function analyzeCSSFiles() {
        echo "[1/5] Analyzing CSS files...\n";
        
        $cssFiles = glob($this->projectPath . '/assets/css/**/*.css', GLOB_BRACE);
        $issuesFound = 0;
        
        foreach ($cssFiles as $cssFile) {
            $content = file_get_contents($cssFile);
            $relativePath = str_replace($this->projectPath . '/', '', $cssFile);
            
            // Check for fixed widths (bad for mobile)
            if (preg_match('/width:\s*\d+px/', $content)) {
                $this->issues[] = [
                    'file' => $relativePath,
                    'type' => 'fixed_width',
                    'issue' => 'Fixed pixel widths found',
                    'recommendation' => 'Use percentages, max-width, or responsive units like rem/em',
                    'severity' => 'high'
                ];
                $issuesFound++;
            }
            
            // Check for media queries
            if (strpos($content, '@media') === false) {
                $this->issues[] = [
                    'file' => $relativePath,
                    'type' => 'no_media_queries',
                    'issue' => 'No media queries found',
                    'recommendation' => 'Add responsive breakpoints for mobile devices',
                    'severity' => 'high'
                ];
                $issuesFound++;
            }
            
            // Check for font sizes in pixels (prefer rem/em)
            if (preg_match('/font-size:\s*\d+px/', $content)) {
                $this->issues[] = [
                    'file' => $relativePath,
                    'type' => 'fixed_font_size',
                    'issue' => 'Fixed font sizes in pixels',
                    'recommendation' => 'Use rem or em for scalable text',
                    'severity' => 'medium'
                ];
                $issuesFound++;
            }
            
            // Check for overflow issues
            if (strpos($content, 'overflow-x') === false && preg_match('/width:\s*100%/', $content)) {
                $this->issues[] = [
                    'file' => $relativePath,
                    'type' => 'potential_overflow',
                    'issue' => 'Potential horizontal overflow on mobile',
                    'recommendation' => 'Add overflow-x: hidden or use max-width: 100%',
                    'severity' => 'medium'
                ];
                $issuesFound++;
            }
        }
        
        echo "  Analyzed " . count($cssFiles) . " CSS files\n";
        echo "  Issues found: $issuesFound\n\n";
    }
    
    /**
     * Analyze view files for mobile issues
     */
    private function analyzeViewFiles() {
        echo "[2/5] Analyzing view files...\n";
        
        $viewFiles = glob($this->projectPath . '/app/views/**/*.php', GLOB_BRACE);
        $issuesFound = 0;
        
        foreach ($viewFiles as $viewFile) {
            $content = file_get_contents($viewFile);
            $relativePath = str_replace($this->projectPath . '/', '', $viewFile);
            
            // Check for tables without responsive wrappers
            if (strpos($content, '<table') !== false && strpos($content, 'table-responsive') === false) {
                $this->issues[] = [
                    'file' => $relativePath,
                    'type' => 'unwrapped_table',
                    'issue' => 'HTML table without responsive wrapper',
                    'recommendation' => 'Wrap tables in responsive container div',
                    'severity' => 'high'
                ];
                $issuesFound++;
            }
            
            // Check for large fixed widths
            if (preg_match('/width\s*=\s*["\']\d{3,}/', $content)) {
                $this->issues[] = [
                    'file' => $relativePath,
                    'type' => 'fixed_html_width',
                    'issue' => 'Fixed HTML width attributes',
                    'recommendation' => 'Remove width attributes or use responsive CSS',
                    'severity' => 'high'
                ];
                $issuesFound++;
            }
            
            // Check for missing viewport meta tag
            if (strpos($content, '<meta name="viewport"') === false && 
                (strpos($content, '<!DOCTYPE html') !== false || strpos($content, '<html') !== false)) {
                $this->issues[] = [
                    'file' => $relativePath,
                    'type' => 'missing_viewport',
                    'issue' => 'Missing viewport meta tag',
                    'recommendation' => 'Add <meta name="viewport" content="width=device-width, initial-scale=1.0">',
                    'severity' => 'critical'
                ];
                $issuesFound++;
            }
            
            // Check for non-mobile friendly forms
            if (preg_match('/<input[^>]*type="number"/', $content)) {
                $this->issues[] = [
                    'file' => $relativePath,
                    'type' => 'mobile_input',
                    'issue' => 'Number input type (may have poor mobile support)',
                    'recommendation' => 'Consider using tel or pattern attributes for better mobile keyboards',
                    'severity' => 'low'
                ];
                $issuesFound++;
            }
        }
        
        echo "  Analyzed " . count($viewFiles) . " view files\n";
        echo "  Issues found: $issuesFound\n\n";
    }
    
    /**
     * Analyze viewport meta tag usage
     */
    private function analyzeViewportMeta() {
        echo "[3/5] Analyzing viewport meta tags...\n";
        
        $viewFiles = glob($this->projectPath . '/app/views/**/*.php', GLOB_BRACE);
        $filesWithViewport = 0;
        $filesWithoutViewport = 0;
        
        foreach ($viewFiles as $viewFile) {
            $content = file_get_contents($viewFile);
            
            // Check if file has HTML structure
            if (strpos($content, '<!DOCTYPE html') !== false || strpos($content, '<html') !== false) {
                if (strpos($content, '<meta name="viewport"') !== false) {
                    $filesWithViewport++;
                } else {
                    $filesWithoutViewport++;
                }
            }
        }
        
        echo "  Files with viewport meta tag: $filesWithViewport\n";
        echo "  Files without viewport meta tag: $filesWithoutViewport\n\n";
        
        if ($filesWithoutViewport > 0) {
            $this->issues[] = [
                'file' => 'GLOBAL',
                'type' => 'viewport_compliance',
                'issue' => "$filesWithoutViewport HTML files missing viewport meta tag",
                'recommendation' => 'Ensure all HTML pages have proper viewport meta tag',
                'severity' => 'critical'
            ];
        }
    }
    
    /**
     * Analyze touch targets for mobile
     */
    private function analyzeTouchTargets() {
        echo "[4/5] Analyzing touch targets...\n";
        
        $cssFiles = glob($this->projectPath . '/assets/css/**/*.css', GLOB_BRACE);
        
        foreach ($cssFiles as $cssFile) {
            $content = file_get_contents($cssFile);
            $relativePath = str_replace($this->projectPath . '/', '', $cssFile);
            
            // Check for small button sizes (less than 44px is too small for touch)
            if (preg_match('/(?:width|height|padding):\s*(\d+)px/', $content, $matches)) {
                $size = (int)$matches[1];
                if ($size < 44) {
                    $this->issues[] = [
                        'file' => $relativePath,
                        'type' => 'small_touch_target',
                        'issue' => "Touch target size $size px (should be at least 44px)",
                        'recommendation' => 'Increase touch target sizes to at least 44x44px for mobile',
                        'severity' => 'medium'
                    ];
                }
            }
        }
        
        echo "  Touch target analysis completed\n\n";
    }
    
    /**
     * Analyze image responsiveness
     */
    private function analyzeImageResponsiveness() {
        echo "[5/5] Analyzing image responsiveness...\n";
        
        $viewFiles = glob($this->projectPath . '/app/views/**/*.php', GLOB_BRACE);
        $issuesFound = 0;
        
        foreach ($viewFiles as $viewFile) {
            $content = file_get_contents($viewFile);
            $relativePath = str_replace($this->projectPath . '/', '', $viewFile);
            
            // Check for images without responsive classes
            if (preg_match('/<img[^>]*>/', $content, $matches)) {
                foreach ($matches as $imgTag) {
                    if (strpos($imgTag, 'img-fluid') === false && 
                        strpos($imgTag, 'responsive') === false &&
                        strpos($imgTag, 'max-width') === false) {
                        
                        $this->issues[] = [
                            'file' => $relativePath,
                            'type' => 'non_responsive_image',
                            'issue' => 'Image without responsive class or styling',
                            'recommendation' => 'Add img-fluid class or max-width: 100% CSS',
                            'severity' => 'medium'
                        ];
                        $issuesFound++;
                    }
                }
            }
        }
        
        echo "  Analyzed image tags in views\n";
        echo "  Non-responsive images found: $issuesFound\n\n";
    }
    
    /**
     * Generate recommendations
     */
    private function generateRecommendations() {
        echo "=== GENERATING RECOMMENDATIONS ===\n\n";
        
        $severityCounts = [
            'critical' => 0,
            'high' => 0,
            'medium' => 0,
            'low' => 0
        ];
        
        foreach ($this->issues as $issue) {
            $severityCounts[$issue['severity']]++;
        }
        
        echo "Issue Summary:\n";
        echo "  Critical: " . $severityCounts['critical'] . "\n";
        echo "  High: " . $severityCounts['high'] . "\n";
        echo "  Medium: " . $severityCounts['medium'] . "\n";
        echo "  Low: " . $severityCounts['low'] . "\n";
        echo "  Total: " . count($this->issues) . "\n\n";
    }
    
    /**
     * Save analysis report
     */
    public function saveReport($filename = 'mobile_responsiveness_report.md') {
        $content = "# Mobile Responsiveness Analysis Report\n\n";
        $content .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";
        
        $content .= "## Executive Summary\n\n";
        $content .= "Total issues found: " . count($this->issues) . "\n\n";
        
        // Group by severity
        $bySeverity = [
            'critical' => [],
            'high' => [],
            'medium' => [],
            'low' => []
        ];
        
        foreach ($this->issues as $issue) {
            $bySeverity[$issue['severity']][] = $issue;
        }
        
        $content .= "### Critical Issues\n\n";
        if (!empty($bySeverity['critical'])) {
            foreach ($bySeverity['critical'] as $issue) {
                $content .= "- **{$issue['file']}**: {$issue['issue']}\n";
                $content .= "  - Recommendation: {$issue['recommendation']}\n\n";
            }
        } else {
            $content .= "No critical issues found.\n\n";
        }
        
        $content .= "### High Priority Issues\n\n";
        if (!empty($bySeverity['high'])) {
            foreach ($bySeverity['high'] as $issue) {
                $content .= "- **{$issue['file']}**: {$issue['issue']}\n";
                $content .= "  - Recommendation: {$issue['recommendation']}\n\n";
            }
        } else {
            $content .= "No high priority issues found.\n\n";
        }
        
        $content .= "### Medium Priority Issues\n\n";
        if (!empty($bySeverity['medium'])) {
            foreach ($bySeverity['medium'] as $issue) {
                $content .= "- **{$issue['file']}**: {$issue['issue']}\n";
                $content .= "  - Recommendation: {$issue['recommendation']}\n\n";
            }
        } else {
            $content .= "No medium priority issues found.\n\n";
        }
        
        $content .= "### Low Priority Issues\n\n";
        if (!empty($bySeverity['low'])) {
            foreach ($bySeverity['low'] as $issue) {
                $content .= "- **{$issue['file']}**: {$issue['issue']}\n";
                $content .= "  - Recommendation: {$issue['recommendation']}\n\n";
            }
        } else {
            $content .= "No low priority issues found.\n\n";
        }
        
        file_put_contents($this->projectPath . '/' . $filename, $content);
        echo "Report saved to: $filename\n";
    }
}

// Run analysis if executed directly
if (php_sapi_name() === 'cli') {
    $analyzer = new MobileResponsivenessAnalyzer(__DIR__ . '/..');
    $analyzer->runAnalysis();
    $analyzer->saveReport();
}