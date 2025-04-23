<?php
/**
 * Log Maintenance Script
 * Cleans old log files and manages log rotation
 * 
 * Recommended to run this script daily via cron:
 * 0 0 * * * php /path/to/clean_logs.php
 */

require_once __DIR__ . '/../../includes/config/config.php';
require_once __DIR__ . '/../../includes/logger.php';

class LogMaintenance {
    private $logger;
    private $config = [
        'retention_days' => [
            'security' => 90,  // Keep security logs for 90 days
            'error' => 30,     // Keep error logs for 30 days
            'access' => 7,     // Keep access logs for 7 days
            'debug' => 3       // Keep debug logs for 3 days
        ],
        'max_size' => [
            'security' => 52428800, // 50MB
            'error' => 26214400,    // 25MB
            'access' => 10485760,   // 10MB
            'debug' => 5242880      // 5MB
        ]
    ];

    public function __construct() {
        $this->logger = new Logger();
    }

    /**
     * Run all maintenance tasks
     */
    public function run() {
        try {
            $this->cleanOldLogs();
            $this->rotateOversizedLogs();
            $this->compressOldLogs();
            $this->generateReport();
        } catch (Exception $e) {
            $this->logger->error('Log maintenance failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Clean old log files based on retention policy
     */
    private function cleanOldLogs() {
        foreach ($this->config['retention_days'] as $type => $days) {
            $this->logger->debug("Cleaning {$type} logs older than {$days} days");
            $this->logger->cleanOldLogs($days);
        }
    }

    /**
     * Rotate logs that exceed maximum size
     */
    private function rotateOversizedLogs() {
        $logPath = __DIR__ . '/../../logs';
        foreach ($this->config['max_size'] as $type => $maxSize) {
            $typeDir = $logPath . '/' . $type;
            if (!is_dir($typeDir)) continue;

            $files = glob($typeDir . '/*.log');
            foreach ($files as $file) {
                if (filesize($file) > $maxSize) {
                    $this->rotateSingleLog($file);
                }
            }
        }
    }

    /**
     * Rotate a single log file
     */
    private function rotateSingleLog($file) {
        $info = pathinfo($file);
        $timestamp = date('Y-m-d_His');
        $rotated = $info['dirname'] . '/' . $info['filename'] . '_' . $timestamp . '.log';
        
        if (rename($file, $rotated)) {
            $this->logger->info("Rotated log file", [
                'original' => $file,
                'rotated' => $rotated
            ]);
        }
    }

    /**
     * Compress log files older than 1 day
     */
    private function compressOldLogs() {
        $logPath = __DIR__ . '/../../logs';
        $types = array_keys($this->config['retention_days']);
        
        foreach ($types as $type) {
            $typeDir = $logPath . '/' . $type;
            if (!is_dir($typeDir)) continue;

            $files = glob($typeDir . '/*.log');
            foreach ($files as $file) {
                // Skip current day's log
                if (basename($file) == date('Y-m-d') . '.log') continue;
                
                // Skip already compressed files
                if (strpos($file, '.gz') !== false) continue;
                
                // Compress if older than 1 day
                if (filemtime($file) < strtotime('-1 day')) {
                    $this->compressFile($file);
                }
            }
        }
    }

    /**
     * Compress a single file using gzip
     */
    private function compressFile($file) {
        $gzFile = $file . '.gz';
        $fp = gzopen($gzFile, 'w9');
        
        if ($fp) {
            gzwrite($fp, file_get_contents($file));
            gzclose($fp);
            
            if (file_exists($gzFile)) {
                unlink($file);
                $this->logger->info("Compressed log file", [
                    'original' => $file,
                    'compressed' => $gzFile
                ]);
            }
        }
    }

    /**
     * Generate maintenance report
     */
    private function generateReport() {
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'stats' => []
        ];

        $logPath = __DIR__ . '/../../logs';
        foreach ($this->config['retention_days'] as $type => $days) {
            $typeDir = $logPath . '/' . $type;
            if (!is_dir($typeDir)) continue;

            $stats = [
                'total_files' => 0,
                'total_size' => 0,
                'compressed_files' => 0,
                'compressed_size' => 0,
                'oldest_file' => null,
                'newest_file' => null
            ];

            $files = glob($typeDir . '/*.*');
            foreach ($files as $file) {
                $stats['total_files']++;
                $stats['total_size'] += filesize($file);
                
                if (strpos($file, '.gz') !== false) {
                    $stats['compressed_files']++;
                    $stats['compressed_size'] += filesize($file);
                }

                $mtime = filemtime($file);
                if (!$stats['oldest_file'] || $mtime < filemtime($stats['oldest_file'])) {
                    $stats['oldest_file'] = $file;
                }
                if (!$stats['newest_file'] || $mtime > filemtime($stats['newest_file'])) {
                    $stats['newest_file'] = $file;
                }
            }

            $report['stats'][$type] = $stats;
        }

        // Save report
        $reportFile = $logPath . '/maintenance_' . date('Y-m-d') . '.json';
        file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT));
        
        $this->logger->info("Generated log maintenance report", [
            'report_file' => $reportFile
        ]);
    }
}

// Run maintenance if script is executed directly
if (php_sapi_name() === 'cli') {
    $maintenance = new LogMaintenance();
    $maintenance->run();
}
