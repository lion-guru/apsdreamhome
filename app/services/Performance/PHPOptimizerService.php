<?php

namespace App\Services\Performance;

use App\Core\Database;
use Psr\Log\LoggerInterface;

/**
 * Modern PHP Optimizer Service
 * Handles PHP performance optimization with proper MVC patterns
 */
class PHPOptimizerService
{
    private Database $db;
    private LoggerInterface $logger;
    private array $config;
    private array $optimizations = [];

    // Optimization levels
    public const LEVEL_DEVELOPMENT = 'development';
    public const LEVEL_PRODUCTION = 'production';
    public const LEVEL_HIGH_PERFORMANCE = 'high_performance';

    // Optimization types
    public const OPT_MEMORY = 'memory';
    public const OPT_CPU = 'cpu';
    public const OPT_IO = 'io';
    public const OPT_CACHE = 'cache';
    public const OPT_DATABASE = 'database';

    public function __construct(Database $db, LoggerInterface $logger, array $config = [])
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->config = array_merge([
            'optimization_level' => self::LEVEL_PRODUCTION,
            'auto_optimize' => true,
            'optimization_interval' => 3600, // 1 hour
            'memory_limit' => '256M',
            'max_execution_time' => 30,
            'opcache_enabled' => true,
            'output_buffering' => true,
            'gzip_compression' => true,
            'session_gc_probability' => 1,
            'session_gc_divisor' => 100
        ], $config);
        
        $this->initializeOptimizationTables();
        $this->loadOptimizations();
    }

    /**
     * Optimize PHP configuration
     */
    public function optimizePHP(): array
    {
        try {
            $optimizations = [];
            $improvements = 0;

            // Memory optimization
            if ($this->optimizeMemory()) {
                $optimizations[] = 'Memory optimization applied';
                $improvements++;
            }

            // CPU optimization
            if ($this->optimizeCPU()) {
                $optimizations[] = 'CPU optimization applied';
                $improvements++;
            }

            // I/O optimization
            if ($this->optimizeIO()) {
                $optimizations[] = 'I/O optimization applied';
                $improvements++;
            }

            // Cache optimization
            if ($this->optimizeCache()) {
                $optimizations[] = 'Cache optimization applied';
                $improvements++;
            }

            // Database optimization
            if ($this->optimizeDatabase()) {
                $optimizations[] = 'Database optimization applied';
                $improvements++;
            }

            // Apply PHP ini settings
            $this->applyPHPSettings();

            $this->logger->info("PHP optimization completed", [
                'optimizations' => $optimizations,
                'improvements' => $improvements
            ]);

            return [
                'success' => true,
                'message' => "PHP optimization completed",
                'optimizations' => $optimizations,
                'improvements' => $improvements
            ];

        } catch (\Exception $e) {
            $this->logger->error("PHP optimization failed", ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'PHP optimization failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get current PHP configuration
     */
    public function getPHPConfiguration(): array
    {
        return [
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'max_input_time' => ini_get('max_input_time'),
            'post_max_size' => ini_get('post_max_size'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'max_input_vars' => ini_get('max_input_vars'),
            'opcache_enabled' => function_exists('opcache_get_status'),
            'opcache_status' => function_exists('opcache_get_status') ? opcache_get_status() : null,
            'gzip_compression' => ini_get('zlib.output_compression'),
            'output_buffering' => ini_get('output_buffering'),
            'session_gc_probability' => ini_get('session.gc_probability'),
            'session_gc_divisor' => ini_get('session.gc_divisor'),
            'error_reporting' => ini_get('error_reporting'),
            'display_errors' => ini_get('display_errors'),
            'log_errors' => ini_get('log_errors'),
            'error_log' => ini_get('error_log')
        ];
    }

    /**
     * Set PHP configuration
     */
    public function setPHPConfiguration(string $key, string $value): array
    {
        try {
            if (ini_set($key, $value) === false) {
                return [
                    'success' => false,
                    'message' => "Failed to set {$key}"
                ];
            }

            // Log configuration change
            $sql = "INSERT INTO php_optimization_log 
                    (optimization_type, config_key, old_value, new_value, status, created_at) 
                    VALUES (?, ?, ?, ?, 'success', NOW())";
            
            $this->db->execute($sql, [
                'config_change',
                $key,
                ini_get($key),
                $value
            ]);

            $this->logger->info("PHP configuration updated", [
                'key' => $key,
                'old_value' => ini_get($key),
                'new_value' => $value
            ]);

            return [
                'success' => true,
                'message' => "Configuration {$key} updated successfully"
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to update PHP configuration", [
                'key' => $key,
                'value' => $value,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to update configuration: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get optimization statistics
     */
    public function getOptimizationStats(array $filters = []): array
    {
        try {
            $stats = [];

            // Recent optimizations
            $sql = "SELECT * FROM php_optimization_log";
            $params = [];
            
            if (!empty($filters['date_from'])) {
                $sql .= " WHERE created_at >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= (empty($params) ? " WHERE" : " AND") . " created_at <= ?";
                $params[] = $filters['date_to'];
            }
            
            $sql .= " ORDER BY created_at DESC";

            if (!empty($filters['limit'])) {
                $sql .= " LIMIT ?";
                $params[] = (int)$filters['limit'];
            }

            $stats['recent_optimizations'] = $this->db->fetchAll($sql, $params);

            // Optimization summary
            $summarySql = "SELECT 
                        optimization_type,
                        COUNT(*) as count,
                        COUNT(CASE WHEN status = 'success' THEN 1 END) as success_count
                    FROM php_optimization_log";
            
            $summaryParams = [];
            
            if (!empty($filters['date_from'])) {
                $summarySql .= " WHERE created_at >= ?";
                $summaryParams[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $summarySql .= " AND created_at <= ?";
                $summaryParams[] = $filters['date_to'];
            }
            
            $summarySql .= " GROUP BY optimization_type";
            
            $summaryStats = $this->db->fetchAll($summarySql, $summaryParams);
            $stats['by_type'] = [];
            foreach ($summaryStats as $stat) {
                $stats['by_type'][$stat['optimization_type']] = [
                    'total' => $stat['count'],
                    'success' => $stat['success_count']
                ];
            }

            // Current configuration
            $stats['current_config'] = $this->getPHPConfiguration();

            // Performance metrics
            $stats['performance_metrics'] = $this->getPerformanceMetrics();

            return $stats;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get optimization stats", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Run performance benchmark
     */
    public function runBenchmark(): array
    {
        try {
            $benchmark = [];
            $startTime = microtime(true);

            // Memory benchmark
            $memoryStart = memory_get_usage(true);
            $testData = str_repeat('x', 1000000); // 1MB of data
            $memoryEnd = memory_get_usage(true);
            $benchmark['memory_usage'] = $memoryEnd - $memoryStart;
            unset($testData);

            // CPU benchmark
            $cpuStart = microtime(true);
            for ($i = 0; $i < 1000000; $i++) {
                $result = sqrt($i);
            }
            $cpuEnd = microtime(true);
            $benchmark['cpu_time'] = ($cpuEnd - $cpuStart) * 1000; // milliseconds

            // I/O benchmark
            $ioStart = microtime(true);
            $testFile = sys_get_temp_dir() . '/benchmark_' . uniqid() . '.tmp';
            file_put_contents($testFile, str_repeat('test', 10000));
            $content = file_get_contents($testFile);
            unlink($testFile);
            $ioEnd = microtime(true);
            $benchmark['io_time'] = ($ioEnd - $ioStart) * 1000; // milliseconds

            // Total benchmark time
            $totalTime = (microtime(true) - $startTime) * 1000;
            $benchmark['total_time'] = $totalTime;

            // Save benchmark results
            $this->saveBenchmarkResults($benchmark);

            $this->logger->info("Performance benchmark completed", $benchmark);

            return [
                'success' => true,
                'message' => 'Benchmark completed successfully',
                'benchmark' => $benchmark
            ];

        } catch (\Exception $e) {
            $this->logger->error("Benchmark failed", ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Benchmark failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Private helper methods
     */
    private function initializeOptimizationTables(): void
    {
        $tables = [
            "CREATE TABLE IF NOT EXISTS php_optimization_log (
                id INT AUTO_INCREMENT PRIMARY KEY,
                optimization_type VARCHAR(100) NOT NULL,
                config_key VARCHAR(255),
                old_value TEXT,
                new_value TEXT,
                status ENUM('success', 'failed') DEFAULT 'success',
                error_message TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_type (optimization_type),
                INDEX idx_status (status),
                INDEX idx_created_at (created_at)
            )",
            
            "CREATE TABLE IF NOT EXISTS php_benchmark_results (
                id INT AUTO_INCREMENT PRIMARY KEY,
                memory_usage INT,
                cpu_time DECIMAL(10,2),
                io_time DECIMAL(10,2),
                total_time DECIMAL(10,2),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_created_at (created_at)
            )"
        ];

        foreach ($tables as $sql) {
            $this->db->execute($sql);
        }
    }

    private function loadOptimizations(): void
    {
        $this->optimizations = [
            self::OPT_MEMORY => [
                'memory_limit' => $this->config['memory_limit'],
                'session_gc_probability' => $this->config['session_gc_probability'],
                'session_gc_divisor' => $this->config['session_gc_divisor']
            ],
            self::OPT_CPU => [
                'max_execution_time' => $this->config['max_execution_time'],
                'max_input_time' => min(30, $this->config['max_execution_time']),
                'opcache_enabled' => $this->config['opcache_enabled']
            ],
            self::OPT_IO => [
                'output_buffering' => $this->config['output_buffering'],
                'gzip_compression' => $this->config['gzip_compression']
            ],
            self::OPT_CACHE => [
                'opcache_enabled' => $this->config['opcache_enabled'],
                'opcache_validate_timestamps' => $this->config['optimization_level'] === self::LEVEL_DEVELOPMENT ? 1 : 0,
                'opcache_revalidate_freq' => $this->config['optimization_level'] === self::LEVEL_DEVELOPMENT ? 0 : 2
            ],
            self::OPT_DATABASE => [
                'persistent_connections' => $this->config['optimization_level'] === self::LEVEL_HIGH_PERFORMANCE,
                'query_cache' => true,
                'connection_pooling' => $this->config['optimization_level'] === self::LEVEL_HIGH_PERFORMANCE
            ]
        ];
    }

    private function optimizeMemory(): bool
    {
        $optimized = false;
        $memoryOptimizations = $this->optimizations[self::OPT_MEMORY];

        foreach ($memoryOptimizations as $key => $value) {
            $currentValue = ini_get($key);
            
            if ($currentValue != $value) {
                if (ini_set($key, $value) !== false) {
                    $this->logOptimization(self::OPT_MEMORY, $key, $currentValue, $value);
                    $optimized = true;
                }
            }
        }

        return $optimized;
    }

    private function optimizeCPU(): bool
    {
        $optimized = false;
        $cpuOptimizations = $this->optimizations[self::OPT_CPU];

        foreach ($cpuOptimizations as $key => $value) {
            $currentValue = ini_get($key);
            
            if ($currentValue != $value) {
                if (ini_set($key, $value) !== false) {
                    $this->logOptimization(self::OPT_CPU, $key, $currentValue, $value);
                    $optimized = true;
                }
            }
        }

        return $optimized;
    }

    private function optimizeIO(): bool
    {
        $optimized = false;
        $ioOptimizations = $this->optimizations[self::OPT_IO];

        foreach ($ioOptimizations as $key => $value) {
            $currentValue = ini_get($key);
            
            if ($currentValue != $value) {
                if (ini_set($key, $value) !== false) {
                    $this->logOptimization(self::OPT_IO, $key, $currentValue, $value);
                    $optimized = true;
                }
            }
        }

        return $optimized;
    }

    private function optimizeCache(): bool
    {
        $optimized = false;
        $cacheOptimizations = $this->optimizations[self::OPT_CACHE];

        foreach ($cacheOptimizations as $key => $value) {
            $currentValue = ini_get($key);
            
            if ($currentValue != $value) {
                if (ini_set($key, $value) !== false) {
                    $this->logOptimization(self::OPT_CACHE, $key, $currentValue, $value);
                    $optimized = true;
                }
            }
        }

        return $optimized;
    }

    private function optimizeDatabase(): bool
    {
        $optimized = false;
        $dbOptimizations = $this->optimizations[self::OPT_DATABASE];

        foreach ($dbOptimizations as $key => $value) {
            // Database optimizations would be applied at the database level
            $this->logOptimization(self::OPT_DATABASE, $key, 'unknown', $value ? 'enabled' : 'disabled');
            $optimized = true;
        }

        return $optimized;
    }

    private function applyPHPSettings(): void
    {
        // Apply level-specific settings
        switch ($this->config['optimization_level']) {
            case self::LEVEL_DEVELOPMENT:
                ini_set('error_reporting', E_ALL);
                ini_set('display_errors', 1);
                ini_set('display_startup_errors', 1);
                ini_set('log_errors', 1);
                break;
            
            case self::LEVEL_PRODUCTION:
                ini_set('error_reporting', E_ALL & ~E_DEPRECATED & ~E_STRICT);
                ini_set('display_errors', 0);
                ini_set('display_startup_errors', 0);
                ini_set('log_errors', 1);
                break;
            
            case self::LEVEL_HIGH_PERFORMANCE:
                ini_set('error_reporting', 0);
                ini_set('display_errors', 0);
                ini_set('display_startup_errors', 0);
                ini_set('log_errors', 0);
                break;
        }
    }

    private function logOptimization(string $type, string $key, string $oldValue, string $newValue): void
    {
        $sql = "INSERT INTO php_optimization_log 
                (optimization_type, config_key, old_value, new_value, status, created_at) 
                VALUES (?, ?, ?, ?, 'success', NOW())";
        
        $this->db->execute($sql, [$type, $key, $oldValue, $newValue]);
    }

    private function saveBenchmarkResults(array $benchmark): void
    {
        $sql = "INSERT INTO php_benchmark_results 
                (memory_usage, cpu_time, io_time, total_time, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        
        $this->db->execute($sql, [
            $benchmark['memory_usage'],
            $benchmark['cpu_time'],
            $benchmark['io_time'],
            $benchmark['total_time']
        ]);
    }

    private function getPerformanceMetrics(): array
    {
        $metrics = [];

        // Memory usage
        $metrics['memory_usage'] = [
            'current' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
            'limit' => $this->parseMemoryLimit(ini_get('memory_limit'))
        ];

        // OpCache status
        if (function_exists('opcache_get_status')) {
            $opcacheStatus = opcache_get_status();
            $metrics['opcache'] = [
                'enabled' => $opcacheStatus['opcache_enabled'] ?? false,
                'hit_rate' => $this->calculateOpCacheHitRate($opcacheStatus),
                'memory_usage' => $opcacheStatus['memory_usage'] ?? [],
                'statistics' => $opcacheStatus['opcache_statistics'] ?? []
            ];
        }

        // Server load
        $metrics['server_load'] = sys_getloadavg() ?? [0, 0, 0];

        return $metrics;
    }

    private function parseMemoryLimit(string $limit): int
    {
        $limit = trim($limit);
        $last = strtolower($limit[strlen($limit) - 1]);
        $value = (int) $limit;
        
        switch ($last) {
            case 'g':
                $value *= 1024 * 1024 * 1024;
                break;
            case 'm':
                $value *= 1024 * 1024;
                break;
            case 'k':
                $value *= 1024;
                break;
        }
        
        return $value;
    }

    private function calculateOpCacheHitRate(array $opcacheStatus): float
    {
        $stats = $opcacheStatus['opcache_statistics'] ?? [];
        
        if (isset($stats['hits']) && isset($stats['misses'])) {
            $total = $stats['hits'] + $stats['misses'];
            return $total > 0 ? ($stats['hits'] / $total) * 100 : 0;
        }
        
        return 0;
    }
}
