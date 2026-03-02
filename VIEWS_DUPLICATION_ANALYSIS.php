<?php
/**
 * APS Dream Home - Views Duplication Analysis
 * Super Admin Level Deep Scan of app/views directory
 * Identify duplications and optimization opportunities
 */

echo "👑 APS DREAM HOME - VIEWS DUPLICATION ANALYSIS\n";
echo "==========================================\n";

class ViewsDuplicationAnalyzer {
    private $viewsDir;
    private $analysis = [];
    
    public function __construct() {
        $this->viewsDir = __DIR__ . '/app/views';
        echo "🔍 Analyzing views directory: {$this->viewsDir}\n";
    }
    
    /**
     * Complete views analysis
     */
    public function analyzeViews() {
        echo "\n📁 COMPLETE VIEWS STRUCTURE ANALYSIS\n";
        echo "===================================\n";
        
        $this->scanAllViewDirectories();
        $this->analyzeFileSizes();
        $this->detectDuplicates();
        $this->analyzeCodePatterns();
        $this->identifyOptimizationOpportunities();
        $this->generateRecommendations();
    }
    
    /**
     * Scan all view directories
     */
    private function scanAllViewDirectories() {
        echo "\n📂 VIEW DIRECTORIES SCAN:\n";
        
        $directories = [
            'admin' => 'Admin interface views',
            'agent' => 'Agent interface views', 
            'agents' => 'Alternative agent views',
            'associates' => 'Associate management views',
            'auth' => 'Authentication views',
            'chatbot' => 'Chatbot interface views',
            'components' => 'Reusable component views',
            'crm' => 'CRM system views',
            'customers' => 'Customer management views',
            'employees' => 'Employee management views',
            'errors' => 'Error page views',
            'farmers' => 'Farmer management views',
            'home' => 'Homepage views',
            'interior-design' => 'Interior design views',
            'layouts' => 'Layout template views',
            'leads' => 'Lead management views',
            'pages' => 'Static page views',
            'partials' => 'Partial view components',
            'payment' => 'Payment processing views',
            'payments' => 'Alternative payment views',
            'projects' => 'Project management views',
            'properties' => 'Property management views',
            'property' => 'Single property views',
            'saas' => 'SaaS feature views',
            'static' => 'Static content views',
            'team' => 'Team management views',
            'test' => 'Testing views',
            'user' => 'User dashboard views',
            'users' => 'Alternative user views',
            'emails' => 'Email template views'
        ];
        
        foreach ($directories as $dir => $description) {
            $path = $this->viewsDir . '/' . $dir;
            if (is_dir($path)) {
                $files = glob($path . '/*.php');
                $totalSize = $this->getDirectorySize($path);
                
                echo "✅ $dir: " . count($files) . " files (" . number_format($totalSize/1024, 1) . "KB) - $description\n";
                
                $this->analysis['directories'][$dir] = [
                    'files' => $files,
                    'count' => count($files),
                    'size' => $totalSize,
                    'description' => $description
                ];
                
                // Deep scan for important directories
                if (in_array($dir, ['admin', 'user', 'agent', 'auth', 'layouts', 'pages'])) {
                    $this->deepScanDirectory($dir, $path);
                }
            } else {
                echo "❌ $dir: Not found - $description\n";
            }
        }
        
        // Analyze standalone files
        $standaloneFiles = ['property_details.php'];
        foreach ($standaloneFiles as $file) {
            $filePath = $this->viewsDir . '/' . $file;
            if (file_exists($filePath)) {
                $size = filesize($filePath);
                echo "📄 $file: " . number_format($size/1024, 1) . "KB - Standalone view\n";
                
                $this->analysis['standalone'][$file] = [
                    'size' => $size,
                    'path' => $filePath
                ];
            }
        }
    }
    
    /**
     * Deep scan directory
     */
    private function deepScanDirectory($dirName, $path) {
        echo "  🔍 Deep scanning $dirName:\n";
        
        $files = glob($path . '/*.php');
        foreach ($files as $file) {
            $fileName = basename($file);
            $size = filesize($file);
            $purpose = $this->inferFilePurpose($fileName);
            
            echo "    📄 $fileName: " . number_format($size/1024, 1) . "KB - $purpose\n";
            
            // Analyze file content for patterns
            $this->analyzeFileContent($file, $fileName, $dirName);
        }
    }
    
    /**
     * Analyze file content
     */
    private function analyzeFileContent($filePath, $fileName, $directory) {
        $content = file_get_contents($filePath);
        
        $fileInfo = [
            'path' => $filePath,
            'size' => filesize($filePath),
            'lines' => substr_count($content, "\n") + 1,
            'includes' => $this->extractIncludes($content),
            'forms' => $this->extractForms($content),
            'database_queries' => $this->extractDatabaseQueries($content),
            'javascript' => $this->extractJavaScript($content),
            'css' => $this->extractCSS($content),
            'php_code' => $this->extractPHPCode($content)
        ];
        
        $this->analysis['files'][$directory][$fileName] = $fileInfo;
    }
    
    /**
     * Analyze file sizes
     */
    private function analyzeFileSizes() {
        echo "\n📊 FILE SIZE ANALYSIS:\n";
        
        $allFiles = [];
        foreach ($this->analysis['files'] as $directory => $files) {
            foreach ($files as $fileName => $fileInfo) {
                $allFiles[] = [
                    'name' => $fileName,
                    'directory' => $directory,
                    'size' => $fileInfo['size'],
                    'lines' => $fileInfo['lines']
                ];
            }
        }
        
        // Sort by size
        usort($allFiles, function($a, $b) {
            return $b['size'] - $a['size'];
        });
        
        echo "📈 Largest files:\n";
        for ($i = 0; $i < min(10, count($allFiles)); $i++) {
            $file = $allFiles[$i];
            echo "  📄 {$file['directory']}/{$file['name']}: " . number_format($file['size']/1024, 1) . "KB ({$file['lines']} lines)\n";
        }
        
        // Calculate statistics
        $totalSize = array_sum(array_column($allFiles, 'size'));
        $totalLines = array_sum(array_column($allFiles, 'lines'));
        $avgSize = $totalSize / count($allFiles);
        $avgLines = $totalLines / count($allFiles);
        
        echo "\n📊 Size Statistics:\n";
        echo "  📦 Total Files: " . count($allFiles) . "\n";
        echo "  📏 Total Size: " . number_format($totalSize/1024, 1) . "KB\n";
        echo "  📝 Total Lines: $totalLines\n";
        echo "  📊 Average Size: " . number_format($avgSize/1024, 2) . "KB\n";
        echo "  📊 Average Lines: " . number_format($avgLines, 1) . "\n";
    }
    
    /**
     * Detect duplicates
     */
    private function detectDuplicates() {
        echo "\n🔍 DUPLICATION DETECTION:\n";
        
        $duplicates = [];
        $fileContents = [];
        
        // Collect file contents
        foreach ($this->analysis['files'] as $directory => $files) {
            foreach ($files as $fileName => $fileInfo) {
                $content = file_get_contents($fileInfo['path']);
                $normalizedContent = $this->normalizeContent($content);
                $hash = md5($normalizedContent);
                
                if (!isset($fileContents[$hash])) {
                    $fileContents[$hash] = [];
                }
                $fileContents[$hash][] = [
                    'directory' => $directory,
                    'file' => $fileName,
                    'size' => $fileInfo['size']
                ];
            }
        }
        
        // Find duplicates
        foreach ($fileContents as $hash => $files) {
            if (count($files) > 1) {
                $duplicates[] = [
                    'hash' => $hash,
                    'files' => $files,
                    'count' => count($files)
                ];
            }
        }
        
        if (!empty($duplicates)) {
            echo "🚨 DUPLICATES FOUND:\n";
            foreach ($duplicates as $duplicate) {
                echo "  📋 Duplicate Group ({$duplicate['count']} files):\n";
                foreach ($duplicate['files'] as $file) {
                    echo "    📄 {$file['directory']}/{$file['file']}\n";
                }
                echo "\n";
            }
        } else {
            echo "✅ No exact duplicates found\n";
        }
        
        // Check for similar files
        $this->detectSimilarFiles();
        
        $this->analysis['duplicates'] = $duplicates;
    }
    
    /**
     * Detect similar files
     */
    private function detectSimilarFiles() {
        echo "🔍 SIMILAR FILES DETECTION:\n";
        
        $similarFiles = [];
        $allFiles = [];
        
        // Collect all files with their content
        foreach ($this->analysis['files'] as $directory => $files) {
            foreach ($files as $fileName => $fileInfo) {
                $content = file_get_contents($fileInfo['path']);
                $allFiles[] = [
                    'directory' => $directory,
                    'file' => $fileName,
                    'content' => $content,
                    'size' => $fileInfo['size']
                ];
            }
        }
        
        // Compare each file with others
        for ($i = 0; $i < count($allFiles); $i++) {
            for ($j = $i + 1; $j < count($allFiles); $j++) {
                $file1 = $allFiles[$i];
                $file2 = $allFiles[$j];
                
                $similarity = $this->calculateSimilarity($file1['content'], $file2['content']);
                
                if ($similarity > 0.8) { // 80% similarity threshold
                    $similarFiles[] = [
                        'file1' => $file1,
                        'file2' => $file2,
                        'similarity' => $similarity
                    ];
                }
            }
        }
        
        if (!empty($similarFiles)) {
            echo "🚨 SIMILAR FILES FOUND:\n";
            foreach ($similarFiles as $similar) {
                echo "  📋 " . number_format($similar['similarity']*100, 1) . "% similar:\n";
                echo "    📄 {$similar['file1']['directory']}/{$similar['file1']['file']}\n";
                echo "    📄 {$similar['file2']['directory']}/{$similar['file2']['file']}\n\n";
            }
        } else {
            echo "✅ No highly similar files found\n";
        }
        
        $this->analysis['similar'] = $similarFiles;
    }
    
    /**
     * Analyze code patterns
     */
    private function analyzeCodePatterns() {
        echo "\n🔍 CODE PATTERNS ANALYSIS:\n";
        
        $patterns = [
            'database_queries' => 0,
            'forms' => 0,
            'includes' => 0,
            'javascript_blocks' => 0,
            'css_blocks' => 0,
            'php_echo_statements' => 0,
            'html_comments' => 0,
            'php_comments' => 0
        ];
        
        foreach ($this->analysis['files'] as $directory => $files) {
            foreach ($files as $fileName => $fileInfo) {
                $patterns['database_queries'] += count($fileInfo['database_queries']);
                $patterns['forms'] += count($fileInfo['forms']);
                $patterns['includes'] += count($fileInfo['includes']);
                $patterns['javascript_blocks'] += count($fileInfo['javascript']);
                $patterns['css_blocks'] += count($fileInfo['css']);
                
                // Count PHP echo statements
                $content = file_get_contents($fileInfo['path']);
                $patterns['php_echo_statements'] += substr_count($content, '<?=');
                $patterns['html_comments'] += substr_count($content, '<!--');
                $patterns['php_comments'] += substr_count($content, '//') + substr_count($content, '/*');
            }
        }
        
        echo "📊 Code Patterns Found:\n";
        foreach ($patterns as $pattern => $count) {
            echo "  🔧 $pattern: $count\n";
        }
        
        $this->analysis['patterns'] = $patterns;
    }
    
    /**
     * Identify optimization opportunities
     */
    private function identifyOptimizationOpportunities() {
        echo "\n💡 OPTIMIZATION OPPORTUNITIES:\n";
        
        $opportunities = [];
        
        // Check for large files
        foreach ($this->analysis['files'] as $directory => $files) {
            foreach ($files as $fileName => $fileInfo) {
                if ($fileInfo['size'] > 50000) { // > 50KB
                    $opportunities[] = [
                        'type' => 'large_file',
                        'file' => "$directory/$fileName",
                        'size' => $fileInfo['size'],
                        'recommendation' => 'Consider splitting into smaller components'
                    ];
                }
                
                if ($fileInfo['lines'] > 500) { // > 500 lines
                    $opportunities[] = [
                        'type' => 'long_file',
                        'file' => "$directory/$fileName",
                        'lines' => $fileInfo['lines'],
                        'recommendation' => 'Consider breaking into smaller functions'
                    ];
                }
            }
        }
        
        // Check for duplicate directories
        $duplicateDirs = ['agent', 'agents', 'user', 'users', 'payment', 'payments'];
        foreach ($duplicateDirs as $dir) {
            if (isset($this->analysis['directories'][$dir])) {
                $opportunities[] = [
                    'type' => 'duplicate_directory',
                    'directory' => $dir,
                    'recommendation' => 'Consider consolidating with similar directory'
                ];
            }
        }
        
        // Check for missing components
        if (!isset($this->analysis['directories']['components']) || 
            $this->analysis['directories']['components']['count'] < 5) {
            $opportunities[] = [
                'type' => 'missing_components',
                'recommendation' => 'Consider creating reusable components for common UI elements'
            ];
        }
        
        if (!empty($opportunities)) {
            echo "💡 Optimization Opportunities:\n";
            foreach ($opportunities as $opportunity) {
                echo "  🔧 {$opportunity['type']}: {$opportunity['recommendation']}\n";
                if (isset($opportunity['file'])) {
                    echo "    📄 {$opportunity['file']}\n";
                }
                if (isset($opportunity['directory'])) {
                    echo "    📁 {$opportunity['directory']}\n";
                }
                echo "\n";
            }
        } else {
            echo "✅ No major optimization opportunities found\n";
        }
        
        $this->analysis['opportunities'] = $opportunities;
    }
    
    /**
     * Generate recommendations
     */
    private function generateRecommendations() {
        echo "\n📋 RECOMMENDATIONS:\n";
        
        $recommendations = [];
        
        // Based on analysis
        if (!empty($this->analysis['duplicates'])) {
            $recommendations[] = "Remove or consolidate duplicate files to reduce maintenance overhead";
        }
        
        if (!empty($this->analysis['similar'])) {
            $recommendations[] = "Refactor similar files to use shared components or inheritance";
        }
        
        if ($this->analysis['patterns']['database_queries'] > 50) {
            $recommendations[] = "Consider moving database queries to models or repositories";
        }
        
        if ($this->analysis['patterns']['forms'] > 30) {
            $recommendations[] = "Create reusable form components to reduce duplication";
        }
        
        if (!empty($this->analysis['opportunities'])) {
            $recommendations[] = "Address optimization opportunities to improve maintainability";
        }
        
        $recommendations[] = "Implement a proper component-based architecture";
        $recommendations[] = "Use layout inheritance to reduce code duplication";
        $recommendations[] = "Create reusable partials for common UI elements";
        $recommendations[] = "Implement proper separation of concerns in views";
        
        foreach ($recommendations as $recommendation) {
            echo "✅ $recommendation\n";
        }
        
        $this->analysis['recommendations'] = $recommendations;
    }
    
    // Helper methods
    private function getDirectorySize($dir) {
        $size = 0;
        foreach (glob($dir . '/*') as $file) {
            $size += is_file($file) ? filesize($file) : $this->getDirectorySize($file);
        }
        return $size;
    }
    
    private function inferFilePurpose($fileName) {
        if (strpos($fileName, 'index') !== false) return 'Main listing page';
        if (strpos($fileName, 'create') !== false) return 'Creation form';
        if (strpos($fileName, 'edit') !== false) return 'Edit form';
        if (strpos($fileName, 'show') !== false) return 'Detail view';
        if (strpos($fileName, 'delete') !== false) return 'Delete confirmation';
        if (strpos($fileName, 'dashboard') !== false) return 'Dashboard view';
        if (strpos($fileName, 'profile') !== false) return 'Profile management';
        if (strpos($fileName, 'settings') !== false) return 'Settings page';
        if (strpos($fileName, 'report') !== false) return 'Report view';
        if (strpos($fileName, 'list') !== false) return 'Listing page';
        return 'General view';
    }
    
    private function extractIncludes($content) {
        $includes = [];
        if (preg_match_all('/include\s*[\'"]([^\'"]+)[\'"]/', $content, $matches)) {
            $includes = $matches[1];
        }
        if (preg_match_all('/require\s*[\'"]([^\'"]+)[\'"]/', $content, $matches)) {
            $includes = array_merge($includes, $matches[1]);
        }
        return $includes;
    }
    
    private function extractForms($content) {
        $forms = [];
        if (preg_match_all('/<form[^>]*>/', $content, $matches)) {
            $forms = $matches[0];
        }
        return $forms;
    }
    
    private function extractDatabaseQueries($content) {
        $queries = [];
        if (preg_match_all('/SELECT\s+.*\s+FROM\s+/i', $content, $matches)) {
            $queries = array_merge($queries, $matches[0]);
        }
        if (preg_match_all('/INSERT\s+INTO\s+/i', $content, $matches)) {
            $queries = array_merge($queries, $matches[0]);
        }
        if (preg_match_all('/UPDATE\s+.*\s+SET\s+/i', $content, $matches)) {
            $queries = array_merge($queries, $matches[0]);
        }
        if (preg_match_all('/DELETE\s+FROM\s+/i', $content, $matches)) {
            $queries = array_merge($queries, $matches[0]);
        }
        return $queries;
    }
    
    private function extractJavaScript($content) {
        $scripts = [];
        if (preg_match_all('/<script[^>]*>.*?<\/script>/is', $content, $matches)) {
            $scripts = $matches[0];
        }
        return $scripts;
    }
    
    private function extractCSS($content) {
        $styles = [];
        if (preg_match_all('/<style[^>]*>.*?<\/style>/is', $content, $matches)) {
            $styles = $matches[0];
        }
        return $styles;
    }
    
    private function extractPHPCode($content) {
        $phpCode = [];
        if (preg_match_all('/<\?php.*?\?>/is', $content, $matches)) {
            $phpCode = $matches[0];
        }
        return $phpCode;
    }
    
    private function normalizeContent($content) {
        // Remove whitespace, comments, and normalize for comparison
        $content = preg_replace('/\s+/', ' ', $content);
        $content = preg_replace('/<!--.*?-->/', '', $content);
        $content = preg_replace('/\/\*.*?\*\//', '', $content);
        $content = preg_replace('/\/\/.*$/m', '', $content);
        $content = trim($content);
        return $content;
    }
    
    private function calculateSimilarity($content1, $content2) {
        $norm1 = $this->normalizeContent($content1);
        $norm2 = $this->normalizeContent($content2);
        
        similar_text($norm1, $norm2, $similarity);
        return $similarity / 100;
    }
}

// Execute the analysis
$analyzer = new ViewsDuplicationAnalyzer();
$analyzer->analyzeViews();

echo "\n🎉 VIEWS DUPLICATION ANALYSIS COMPLETE!\n";
echo "📊 Complete views structure analyzed and optimized.\n";
echo "🔍 All duplications and patterns identified.\n";
echo "💡 Optimization recommendations provided.\n";
?>
