<?php
/**
 * Automatic Mobile Responsiveness Fixes
 * Applies critical mobile improvements to view files
 */

class MobileResponsivenessFixer {
    private $projectPath;
    private $fixesApplied = [];
    
    public function __construct($projectPath) {
        $this->projectPath = $projectPath;
    }
    
    /**
     * Run all mobile fixes
     */
    public function runFixes() {
        echo "=== APPLYING MOBILE RESPONSIVENESS FIXES ===\n\n";
        
        $this->addViewportMetaTags();
        $this->makeImagesResponsive();
        $this->wrapTables();
        $this->addResponsiveCSS();
        $this->generateReport();
        
        return $this->fixesApplied;
    }
    
    /**
     * Add viewport meta tags to HTML files
     */
    private function addViewportMetaTags() {
        echo "[1/4] Adding viewport meta tags...\n";
        
        $viewFiles = glob($this->projectPath . '/app/views/**/*.php', GLOB_BRACE);
        $fixedFiles = 0;
        
        foreach ($viewFiles as $viewFile) {
            $content = file_get_contents($viewFile);
            $originalContent = $content;
            $relativePath = str_replace($this->projectPath . '/', '', $viewFile);
            
            // Check if file has HTML structure but no viewport meta tag
            if ((strpos($content, '<!DOCTYPE html') !== false || strpos($content, '<html') !== false) &&
                strpos($content, '<meta name="viewport"') === false) {
                
                // Add viewport meta tag after charset or in head
                if (strpos($content, '<head>') !== false) {
                    $content = str_replace(
                        '<head>',
                        "<head>\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0, shrink-to-fit=no\">",
                        $content
                    );
                } elseif (strpos($content, '<meta charset=') !== false) {
                    $content = str_replace(
                        '<meta charset=',
                        "<meta charset=" . substr($content, strpos($content, '<meta charset=') + 14, 1) . ">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0, shrink-to-fit\"><meta charset=",
                        $content
                    );
                    $content = str_replace(
                        '<meta charset=',
                        "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0, shrink-to-fit=no\">\n    <meta charset=",
                        $content
                    );
                }
                
                if ($content !== $originalContent) {
                    file_put_contents($viewFile, $content);
                    $this->fixesApplied[] = [
                        'file' => $relativePath,
                        'fix' => 'Added viewport meta tag',
                        'type' => 'viewport'
                    ];
                    $fixedFiles++;
                }
            }
        }
        
        echo "  Fixed $fixedFiles files with viewport meta tags\n\n";
    }
    
    /**
     * Make images responsive
     */
    private function makeImagesResponsive() {
        echo "[2/4] Making images responsive...\n";
        
        $viewFiles = glob($this->projectPath . '/app/views/**/*.php', GLOB_BRACE);
        $fixedImages = 0;
        
        foreach ($viewFiles as $viewFile) {
            $content = file_get_contents($viewFile);
            $originalContent = $content;
            $relativePath = str_replace($this->projectPath . '/', '', $viewFile);
            
            // Find img tags without responsive classes
            $content = preg_replace_callback(
                '/<img([^>]*?)>/',
                function($matches) use (&$fixedImages, $relativePath) {
                    $imgTag = $matches[1];
                    
                    // Skip if already has responsive classes
                    if (strpos($imgTag, 'img-fluid') !== false || 
                        strpos($imgTag, 'responsive') !== false ||
                        strpos($imgTag, 'max-width') !== false) {
                        return $matches[0];
                    }
                    
                    // Add img-fluid class
                    if (strpos($imgTag, 'class=') !== false) {
                        $imgTag = preg_replace(
                            '/class=([\'"])(.*?)\1/',
                            'class=$1$2 img-fluid$1',
                            $imgTag
                        );
                    } else {
                        $imgTag .= ' class="img-fluid"';
                    }
                    
                    $fixedImages++;
                    return '<img' . $imgTag . '>';
                },
                $content
            );
            
            if ($content !== $originalContent) {
                file_put_contents($viewFile, $content);
                $this->fixesApplied[] = [
                    'file' => $relativePath,
                    'fix' => 'Added img-fluid class to images',
                    'type' => 'images'
                ];
            }
        }
        
        echo "  Made $fixedImages images responsive\n\n";
    }
    
    /**
     * Wrap tables for mobile responsiveness
     */
    private function wrapTables() {
        echo "[3/4] Wrapping tables for mobile...\n";
        
        $viewFiles = glob($this->projectPath . '/app/views/**/*.php', GLOB_BRACE);
        $fixedTables = 0;
        
        foreach ($viewFiles as $viewFile) {
            $content = file_get_contents($viewFile);
            $originalContent = $content;
            $relativePath = str_replace($this->projectPath . '/', '', $viewFile);
            
            // Wrap tables without responsive wrapper
            $content = preg_replace_callback(
                '/(<table[^>]*class=[\'"])([^\'"]*)([\'"][^>]*>)/',
                function($matches) use (&$fixedTables) {
                    $classes = $matches[2];
                    
                    // Skip if already has table-responsive class
                    if (strpos($classes, 'table-responsive') !== false) {
                        return $matches[0];
                    }
                    
                    // Add table-responsive class
                    $newClasses = trim($classes . ' table-responsive');
                    return $matches[1] . $newClasses . $matches[3];
                },
                $content
            );
            
            // Also wrap tables without any class
            $content = preg_replace(
                '/(<table)([^>]*>)/',
                '<div class="table-responsive">$1$2',
                $content
            );
            $content = str_replace(
                '</table>',
                '</table></div>',
                $content
            );
            
            if ($content !== $originalContent) {
                file_put_contents($viewFile, $content);
                $this->fixesApplied[] = [
                    'file' => $relativePath,
                    'fix' => 'Wrapped tables for mobile responsiveness',
                    'type' => 'tables'
                ];
                $fixedTables++;
            }
        }
        
        echo "  Fixed $fixedTables tables\n\n";
    }
    
    /**
     * Add responsive CSS link to layout files
     */
    private function addResponsiveCSS() {
        echo "[4/4] Adding responsive CSS to layouts...\n";
        
        $layoutFiles = glob($this->projectPath . '/app/views/layouts/**/*.php', GLOB_BRACE);
        $updatedLayouts = 0;
        
        foreach ($layoutFiles as $layoutFile) {
            $content = file_get_contents($layoutFile);
            $originalContent = $content;
            $relativePath = str_replace($this->projectPath . '/', '', $layoutFile);
            
            // Check if mobile-responsive.css is already included
            if (strpos($content, 'mobile-responsive.css') === false) {
                // Add CSS link in head section
                if (strpos($content, '</head>') !== false) {
                    $cssLink = '<link rel="stylesheet" href="/assets/css/mobile-responsive.css">';
                    $content = str_replace('</head>', $cssLink . "\n    </head>", $content);
                    
                    if ($content !== $originalContent) {
                        file_put_contents($layoutFile, $content);
                        $this->fixesApplied[] = [
                            'file' => $relativePath,
                            'fix' => 'Added mobile-responsive.css',
                            'type' => 'css'
                        ];
                        $updatedLayouts++;
                    }
                }
            }
        }
        
        echo "  Updated $updatedLayouts layout files\n\n";
    }
    
    /**
     * Generate fix report
     */
    private function generateReport() {
        echo "=== FIX REPORT ===\n\n";
        
        $byType = [];
        foreach ($this->fixesApplied as $fix) {
            $type = $fix['type'];
            if (!isset($byType[$type])) {
                $byType[$type] = 0;
            }
            $byType[$type]++;
        }
        
        echo "Total fixes applied: " . count($this->fixesApplied) . "\n";
        foreach ($byType as $type => $count) {
            echo "  $type: $count\n";
        }
        echo "\n";
    }
    
    /**
     * Save fix report
     */
    public function saveReport($filename = 'mobile_fixes_applied.md') {
        $content = "# Mobile Responsiveness Fixes Applied\n\n";
        $content .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";
        
        $content .= "## Summary\n\n";
        $content .= "Total fixes applied: " . count($this->fixesApplied) . "\n\n";
        
        // Group by type
        $byType = [];
        foreach ($this->fixesApplied as $fix) {
            $type = $fix['type'];
            if (!isset($byType[$type])) {
                $byType[$type] = [];
            }
            $byType[$type][] = $fix;
        }
        
        foreach ($byType as $type => $fixes) {
            $content .= "### $type\n\n";
            foreach ($fixes as $fix) {
                $content .= "- **{$fix['file']}**: {$fix['fix']}\n";
            }
            $content .= "\n";
        }
        
        file_put_contents($this->projectPath . '/' . $filename, $content);
        echo "Report saved to: $filename\n";
    }
}

// Run fixes if executed directly
if (php_sapi_name() === 'cli') {
    $fixer = new MobileResponsivenessFixer(__DIR__ . '/..');
    $fixer->runFixes();
    $fixer->saveReport();
}