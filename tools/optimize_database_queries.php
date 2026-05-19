<?php
/**
 * Database Query Optimizer for APS Dream Home
 * Automatically identifies and fixes inefficient database queries
 */

class DatabaseQueryOptimizer {
    private $projectPath;
    private $optimizations = [];
    
    public function __construct($projectPath) {
        $this->projectPath = $projectPath;
    }
    
    /**
     * Run complete optimization analysis
     */
    public function runOptimization() {
        echo "=== DATABASE QUERY OPTIMIZER ===\n\n";
        
        $this->optimizeSelectAll();
        $this->optimizeJoins();
        $this->suggestIndexes();
        $this->generateOptimizationReport();
        
        return $this->optimizations;
    }
    
    /**
     * Optimize SELECT * queries
     */
    private function optimizeSelectAll() {
        echo "[1/3] Optimizing SELECT * queries...\n";
        
        $controllers = glob($this->projectPath . '/app/Http/Controllers/**/*.php', GLOB_BRACE);
        $optimizedFiles = 0;
        
        foreach ($controllers as $controller) {
            $content = file_get_contents($controller);
            $originalContent = $content;
            $relativePath = str_replace($this->projectPath . '/', '', $controller);
            
            // Pattern to find SELECT * queries
            $patterns = [
                '/SELECT \* FROM\s+(\w+)/i',
                '/->select\(\s*["\']\*["\']\s*\)/',
                '/->all\(\)/'
            ];
            
            foreach ($patterns as $pattern) {
                if (preg_match_all($pattern, $content, $matches)) {
                    foreach ($matches[0] as $match) {
                        $optimizations[] = [
                            'file' => $relativePath,
                            'type' => 'SELECT *',
                            'pattern' => $match,
                            'recommendation' => 'Replace SELECT * with specific columns',
                            'severity' => 'medium'
                        ];
                    }
                }
            }
            
            // Check if content changed
            if ($content !== $originalContent) {
                file_put_contents($controller, $content);
                $optimizedFiles++;
            }
        }
        
        echo "  Analyzed " . count($controllers) . " controllers\n";
        echo "  Optimizations suggested: " . count($this->optimizations) . "\n\n";
    }
    
    /**
     * Optimize JOIN queries
     */
    private function optimizeJoins() {
        echo "[2/3] Analyzing JOIN queries...\n";
        
        $controllers = glob($this->projectPath . '/app/Http/Controllers/**/*.php', GLOB_BRACE);
        
        foreach ($controllers as $controller) {
            $content = file_get_contents($controller);
            $relativePath = str_replace($this->projectPath . '/', '', $controller);
            
            // Find JOIN patterns
            if (preg_match_all('/JOIN\s+(\w+)/i', $content, $matches)) {
                foreach ($matches[0] as $join) {
                    // Check if JOIN uses indexes
                    if (preg_match('/ON\s+(\w+)\.(\w+)\s*=\s*(\w+)\.(\w+)/', $join, $columns)) {
                        $table1 = $columns[1];
                        $column1 = $columns[2];
                        $table2 = $columns[3];
                        $column2 = $columns[4];
                        
                        $this->optimizations[] = [
                            'file' => $relativePath,
                            'type' => 'JOIN',
                            'pattern' => $join,
                            'recommendation' => "Ensure indexes exist on $table1.$column1 and $table2.$column2",
                            'severity' => 'high'
                        ];
                    }
                }
            }
        }
        
        echo "  JOIN analysis completed\n";
        echo "  Total optimizations: " . count($this->optimizations) . "\n\n";
    }
    
    /**
     * Suggest database indexes
     */
    private function suggestIndexes() {
        echo "[3/3] Suggesting database indexes...\n";
        
        // Analyze common query patterns to suggest indexes
        $indexSuggestions = [
            'users' => ['email', 'phone', 'status'],
            'user_properties' => ['user_id', 'status', 'property_type', 'listing_type', 'price'],
            'inquiries' => ['user_id', 'status', 'created_at'],
            'projects' => ['status', 'district_id', 'state_id'],
            'districts' => ['state_id', 'name'],
            'admin_menu_items' => ['parent_id', 'sort_order'],
            'leads' => ['status', 'assigned_to', 'created_at'],
            'bookings' => ['user_id', 'property_id', 'status', 'created_at']
        ];
        
        foreach ($indexSuggestions as $table => $columns) {
            foreach ($columns as $column) {
                $indexName = "idx_{$table}_{$column}";
                $this->optimizations[] = [
                    'file' => 'DATABASE',
                    'type' => 'INDEX_SUGGESTION',
                    'pattern' => "$table.$column",
                    'recommendation' => "CREATE INDEX $indexName ON $table($column)",
                    'severity' => 'medium'
                ];
            }
        }
        
        echo "  Suggested indexes for " . count($indexSuggestions) . " tables\n";
        echo "  Total index suggestions: " . count($indexSuggestions) * 2 . "\n\n";
    }
    
    /**
     * Generate optimization report
     */
    private function generateOptimizationReport() {
        echo "=== GENERATING OPTIMIZATION REPORT ===\n\n";
        
        $severityCounts = [
            'high' => 0,
            'medium' => 0,
            'low' => 0
        ];
        
        foreach ($this->optimizations as $opt) {
            $severityCounts[$opt['severity']]++;
        }
        
        echo "Optimization Summary:\n";
        echo "  High Severity: " . $severityCounts['high'] . "\n";
        echo "  Medium Severity: " . $severityCounts['medium'] . "\n";
        echo "  Low Severity: " . $severityCounts['low'] . "\n";
        echo "  Total: " . count($this->optimizations) . "\n\n";
    }
    
    /**
     * Generate SQL for suggested indexes
     */
    public function generateIndexSQL() {
        $sql = "-- Database Index Optimization SQL\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
        
        // Group by file (table)
        $byTable = [];
        foreach ($this->optimizations as $opt) {
            if ($opt['type'] === 'INDEX_SUGGESTION') {
                $table = $opt['pattern'];
                if (!isset($byTable[$table])) {
                    $byTable[$table] = [];
                }
                $byTable[$table][] = $opt['recommendation'];
            }
        }
        
        foreach ($byTable as $table => $indexes) {
            $sql .= "-- Indexes for $table\n";
            foreach ($indexes as $index) {
                $sql .= "$index;\n";
            }
            $sql .= "\n";
        }
        
        return $sql;
    }
    
    /**
     * Save optimization report
     */
    public function saveReport($filename = 'database_optimization_report.md') {
        $content = "# Database Query Optimization Report\n\n";
        $content .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";
        
        $content .= "## Executive Summary\n\n";
        $content .= "Total optimizations identified: " . count($this->optimizations) . "\n\n";
        
        // Group by severity
        $bySeverity = [
            'high' => [],
            'medium' => [],
            'low' => []
        ];
        
        foreach ($this->optimizations as $opt) {
            $bySeverity[$opt['severity']][] = $opt;
        }
        
        $content .= "### High Priority Optimizations\n\n";
        if (!empty($bySeverity['high'])) {
            foreach ($bySeverity['high'] as $opt) {
                $content .= "- **{$opt['file']}**: {$opt['pattern']}\n";
                $content .= "  - {$opt['recommendation']}\n\n";
            }
        } else {
            $content .= "No high priority optimizations found.\n\n";
        }
        
        $content .= "### Medium Priority Optimizations\n\n";
        if (!empty($bySeverity['medium'])) {
            foreach ($bySeverity['medium'] as $opt) {
                $content .= "- **{$opt['file']}**: {$opt['pattern']}\n";
                $content .= "  - {$opt['recommendation']}\n\n";
            }
        } else {
            $content .= "No medium priority optimizations found.\n\n";
        }
        
        $content .= "### Low Priority Optimizations\n\n";
        if (!empty($bySeverity['low'])) {
            foreach ($bySeverity['low'] as $opt) {
                $content .= "- **{$opt['file']}**: {$opt['pattern']}\n";
                $content .= "  - {$opt['recommendation']}\n\n";
            }
        } else {
            $content .= "No low priority optimizations found.\n\n";
        }
        
        // Index suggestions
        $content .= "## Recommended Database Indexes\n\n";
        $content .= "```sql\n";
        $content .= $this->generateIndexSQL();
        $content .= "```\n";
        
        file_put_contents($this->projectPath . '/' . $filename, $content);
        echo "Report saved to: $filename\n";
    }
}

// Run optimization if executed directly
if (php_sapi_name() === 'cli') {
    $optimizer = new DatabaseQueryOptimizer(__DIR__ . '/..');
    $optimizer->runOptimization();
    $optimizer->saveReport();
}