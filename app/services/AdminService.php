<?php

namespace App\Services;

use App\Core\Database;
use App\Services\PropertyService;
use App\Services\UserService;
use App\Services\LeadService;

class AdminService {
    private $db;

    public function __construct() {
        try {
            // Hold Database singleton wrapper; use its query() API
            $this->db = Database::getInstance();
        } catch (\Exception $e) {
            // Log the error and provide a user-friendly message
            error_log('Database connection error: ' . $e->getMessage());
            throw new \RuntimeException('Unable to connect to the database. Please try again later.');
        }
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats() {
        try {
            $stats = [];

            // Total users
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM users", null, \PDO::FETCH_ASSOC);
            $result = $stmt->fetch();
            $stats['total_users'] = $result ? (int)$result['total'] : 0;

            // Total properties
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM properties", null, \PDO::FETCH_ASSOC);
            $result = $stmt->fetch();
            $stats['total_properties'] = $result ? (int)$result['total'] : 0;

            // Total leads
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM leads", null, \PDO::FETCH_ASSOC);
            $result = $stmt->fetch();
            $stats['total_leads'] = $result ? (int)$result['total'] : 0;

            // Recent activities count
            $stmt = $this->db->query("
                SELECT COUNT(*) as total
                FROM lead_activities
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ", \PDO::FETCH_ASSOC);
            $result = $stmt->fetch();
            $stats['recent_activities'] = $result ? (int)$result['total'] : 0;

            // Monthly revenue
            $stmt = $this->db->query("
                SELECT COALESCE(SUM(amount), 0) as total
                FROM payments
                WHERE status = 'completed'
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ", \PDO::FETCH_ASSOC);
            $result = $stmt->fetch();
            $stats['monthly_revenue'] = $result ? (float)$result['total'] : 0;

            return $stats;

        } catch (\Exception $e) {
            return [
                'total_users' => 0,
                'total_properties' => 0,
                'total_leads' => 0,
                'recent_activities' => 0,
                'monthly_revenue' => 0
            ];
        }
    }

    /**
     * Get recent activities
     */
    public function getRecentActivities($limit = 10) {
        try {
            $sql = "
                SELECT la.*, l.name as lead_name, u.name as user_name
                FROM lead_activities la
                LEFT JOIN leads l ON la.lead_id = l.id
                LEFT JOIN users u ON la.created_by = u.id
                ORDER BY la.created_at DESC
                LIMIT ?
            ";
            $stmt = $this->db->query($sql, [$limit], \PDO::FETCH_ASSOC);
            $stmt->execute([$limit]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Check database connection status
     */
    private function checkDatabaseConnection() {
        try {
            $stmt = $this->db->query('SELECT 1', null, \PDO::FETCH_ASSOC);
            return ['status' => 'OK', 'message' => 'Database connection is active'];
        } catch (\PDOException $e) {
            return ['status' => 'ERROR', 'message' => 'Database connection failed: ' . $e->getMessage()];
        }
    }

    /**
     * Check disk space status
     */
    private function checkDiskSpace() {
        $free = disk_free_space(__DIR__);
        $total = disk_total_space(__DIR__);
        $used = $total - $free;
        $percentage = $total > 0 ? round(($used / $total) * 100, 2) : 0;
        
        return [
            'status' => $percentage > 90 ? 'WARNING' : 'OK',
            'message' => "Disk usage: {$percentage}%",
            'free' => $this->formatBytes($free),
            'used' => $this->formatBytes($used),
            'total' => $this->formatBytes($total),
            'percentage' => $percentage
        ];
    }

    /**
     * Check memory usage
     */
    private function checkMemoryUsage() {
        $usage = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);
        $limit = $this->getMemoryLimit();
        $percentage = $limit > 0 ? round(($usage / $limit) * 100, 2) : 0;
        
        return [
            'status' => $percentage > 90 ? 'WARNING' : 'OK',
            'message' => "Memory usage: {$percentage}%",
            'usage' => $this->formatBytes($usage),
            'peak' => $this->formatBytes($peak),
            'limit' => $this->formatBytes($limit),
            'percentage' => $percentage
        ];
    }

    /**
     * Get last backup date
     */
    private function getLastBackupDate() {
        $backupDir = ROOT . 'backups';
        $lastBackup = 0;
        
        if (is_dir($backupDir)) {
            $files = glob($backupDir . '/*.sql');
            if (!empty($files)) {
                $lastBackup = max(array_map('filemtime', $files));
            }
        }
        
        return [
            'status' => $lastBackup > 0 ? 'OK' : 'WARNING',
            'message' => $lastBackup > 0 ? 
                'Last backup: ' . date('Y-m-d H:i:s', $lastBackup) : 
                'No backups found',
            'timestamp' => $lastBackup,
            'date' => $lastBackup > 0 ? date('Y-m-d H:i:s', $lastBackup) : 'Never'
        ];
    }

    /**
     * Format bytes to human-readable format
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Get memory limit in bytes
     */
    private function getMemoryLimit() {
        $memoryLimit = ini_get('memory_limit');
        if (preg_match('/^(\d+)(.)$/', $memoryLimit, $matches)) {
            if ($matches[2] == 'M') {
                return $matches[1] * 1024 * 1024;
            } elseif ($matches[2] == 'K') {
                return $matches[1] * 1024;
            } elseif ($matches[2] == 'G') {
                return $matches[1] * 1024 * 1024 * 1024;
            }
        }
        return $memoryLimit;
    }

    /**
     * Get system health status
     */
    public function getSystemHealth() {
        return [
            'database' => $this->checkDatabaseConnection(),
            'disk_space' => $this->checkDiskSpace(),
            'memory_usage' => $this->checkMemoryUsage(),
            'last_backup' => $this->getLastBackupDate()
        ];
    }

    /**
     * Get users with filters
     */
    public function getUsers($filters = []) {
        try {
            $where = [];
            $params = [];

            if (!empty($filters['search'])) {
                $where[] = "(u.name LIKE ? OR u.email LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            if (!empty($filters['role'])) {
                $where[] = "u.role = ?";
                $params[] = $filters['role'];
            }

            if (!empty($filters['status'])) {
                $where[] = "u.status = ?";
                $params[] = $filters['status'];
            }

            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            $offset = ($filters['page'] - 1) * $filters['per_page'];
            $limit = $filters['per_page'];

            $sql = "
                SELECT u.*, COUNT(p.id) as property_count
                FROM users u
                LEFT JOIN properties p ON u.id = p.user_id
                $whereClause
                GROUP BY u.id
                ORDER BY u.created_at DESC
                LIMIT $offset, $limit
            ";
            $stmt = $this->db->query($sql, null, \PDO::FETCH_ASSOC);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get user statistics
     */
    public function getUserStats() {
        try {
            $stats = [];

            // Total users by role
            $stmt = $this->db->query("
                SELECT role, COUNT(*) as count
                FROM users
                GROUP BY role
            ");
            $stats['by_role'] = $stmt->fetchAll();

            // Total users by status
            $stmt = $this->db->query("
                SELECT status, COUNT(*) as count
                FROM users
                GROUP BY status
            ");
            $stats['by_status'] = $stmt->fetchAll();

            return $stats;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get properties with filters
     */
    public function getProperties($filters = []) {
        try {
            $where = [];
            $params = [];

            if (!empty($filters['search'])) {
                $where[] = "(p.title LIKE ? OR p.description LIKE ? OR p.location LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            if (!empty($filters['type'])) {
                $where[] = "p.type = ?";
                $params[] = $filters['type'];
            }

            if (!empty($filters['status'])) {
                $where[] = "p.status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['user_id'])) {
                $where[] = "p.user_id = ?";
                $params[] = $filters['user_id'];
            }

            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            $offset = ($filters['page'] - 1) * $filters['per_page'];
            $limit = $filters['per_page'];

            $sql = "
                SELECT p.*, u.name as owner_name, COUNT(pi.id) as image_count
                FROM properties p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN property_images pi ON p.id = pi.property_id
                $whereClause
                GROUP BY p.id
                ORDER BY p.created_at DESC
                LIMIT $offset, $limit
            ";
            $stmt = $this->db->query($sql, null, \PDO::FETCH_ASSOC);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get property statistics
     */
    public function getPropertyStats() {
        try {
            $stats = [];

            // Total properties by type
            $stmt = $this->db->query("
                SELECT type, COUNT(*) as count
                FROM properties
                GROUP BY type
            ")->fetchAll(\PDO::FETCH_ASSOC);
            $stats['by_type'] = $stmt;

            // Total properties by status
            $stmt = $this->db->query("
                SELECT status, COUNT(*) as count
                FROM properties
                GROUP BY status
            ")->fetchAll(\PDO::FETCH_ASSOC);
            $stats['by_status'] = $stmt;
            
            return $stats;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get leads with filters
     */
    public function getLeads($filters = []) {
        try {
            $where = [];
            $params = [];

            if (!empty($filters['search'])) {
                $where[] = "(l.name LIKE ? OR l.email LIKE ? OR l.phone LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            if (!empty($filters['status'])) {
                $where[] = "l.status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['source'])) {
                $where[] = "l.source = ?";
                $params[] = $filters['source'];
            }

            if (!empty($filters['assigned_to'])) {
                $where[] = "l.assigned_to = ?";
                $params[] = $filters['assigned_to'];
            }

            if (!empty($filters['date_from'])) {
                $where[] = "l.created_at >= ?";
                $params[] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $where[] = "l.created_at <= ?";
                $params[] = $filters['date_to'];
            }

            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            $offset = ($filters['page'] - 1) * $filters['per_page'];
            $limit = $filters['per_page'];

            $sql = "
                SELECT l.*, u.name as assigned_user_name
                FROM leads l
                LEFT JOIN users u ON l.assigned_to = u.id
                $whereClause
                ORDER BY l.created_at DESC
                LIMIT $offset, $limit
            ";
            $stmt = $this->db->query($sql, null, \PDO::FETCH_ASSOC);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get lead statistics
     */
    public function getLeadStats() {
        try {
            $stats = [];

            // Total leads by status
            $stmt = $this->db->query("
                SELECT status, COUNT(*) as count
                FROM leads
                GROUP BY status
            ")->fetchAll(\PDO::FETCH_ASSOC);
            $stats['by_status'] = $stmt;

            // Total leads by source
            $stmt = $this->db->query("
                SELECT source, COUNT(*) as count
                FROM leads
                GROUP BY source
            ")->fetchAll(\PDO::FETCH_ASSOC);
            $stats['by_source'] = $stmt;
            
            return $stats;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get all settings
     */
    public function getAllSettings() {
        try {
            $stmt = $this->db->query("SELECT * FROM site_settings ORDER BY setting_group, setting_name", null, \PDO::FETCH_ASSOC);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get setting groups
     */
    public function getSettingGroups() {
        try {
            $stmt = $this->db->query("SELECT DISTINCT setting_group FROM site_settings ORDER BY setting_group", null, \PDO::FETCH_ASSOC);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Update setting
     */
    public function updateSetting($key, $value) {
        try {
            $sql = "
                INSERT INTO site_settings (setting_name, setting_value, updated_at)
                VALUES (?, ?, NOW())
                ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()
            ";
            $stmt = $this->db->query($sql);
            $stmt->execute([$key, $value, $value]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get database statistics
     */
    public function getDatabaseStats() {
        try {
            $stats = [];

            // Get table sizes
            $stmt = $this->db->query("SHOW TABLE STATUS");
            $tables = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $stats['total_tables'] = count($tables);
            $stats['total_size'] = array_sum(array_column($tables, 'Data_length')) +
                                  array_sum(array_column($tables, 'Index_length'));

            return $stats;
        } catch (\Exception $e) {
            return ['total_tables' => 0, 'total_size' => 0];
        }
    }

    /**
     * Get backup files
     */
    public function getBackupFiles() {
        try {
            $backupDir = ROOT . 'backups/';
            if (!is_dir($backupDir)) {
                return [];
            }

            $files = scandir($backupDir);
            $backupFiles = [];

            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                    $backupFiles[] = [
                        'name' => $file,
                        'size' => filesize($backupDir . $file),
                        'date' => date('Y-m-d H:i:s', filemtime($backupDir . $file))
                    ];
                }
            }

            return $backupFiles;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Create database backup
     */
    public function createBackup() {
        try {
            $backupDir = ROOT . 'backups/';
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filepath = $backupDir . $filename;

            // Use mysqldump if available, otherwise create simple backup
            if (function_exists('exec')) {
                $command = "mysqldump -h" . DB_HOST . " -u" . DB_USER . " -p" . DB_PASS . " " . DB_NAME . " > " . $filepath;
                exec($command);
            } else {
                // Simple backup using PHP
                $this->createSimpleBackup($filepath);
            }

            return $filepath;
        } catch (\Exception $e) {
            throw new \Exception('Failed to create backup: ' . $e->getMessage());
        }
    }

    /**
     * Clear application cache
     */
    public function clearCache() {
        try {
            $cacheDir = ROOT . 'cache/';
            if (is_dir($cacheDir)) {
                $files = glob($cacheDir . '*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
        } catch (\Exception $e) {
            throw new \Exception('Failed to clear cache: ' . $e->getMessage());
        }
    }

    /**
     * Export data
     */
    public function exportData($type) {
        try {
            switch ($type) {
                case 'users':
                    $stmt = $this->db->query("SELECT name, email, phone, role, status, created_at FROM users");
                    break;
                case 'properties':
                    $stmt = $this->db->query("SELECT title, description, price, location, type, status, created_at FROM properties");
                    break;
                case 'leads':
                    $stmt = $this->db->query("SELECT name, email, phone, source, status, budget, created_at FROM leads");
                    break;
                default:
                    throw new \Exception('Invalid export type');
            }

            return $stmt->fetchAll();
        } catch (\Exception $e) {
            throw new \Exception('Export failed: ' . $e->getMessage());
        }
    }


    /**
     * Create simple backup
     */
    private function createSimpleBackup($filepath) {
        try {
            // Get all tables
            $stmt = $this->db->query("SHOW TABLES");
            $tables = $stmt->fetchAll();

            $sql = "-- APS Dream Home Database Backup\n";
            $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";

            foreach ($tables as $table) {
                $tableName = $table[0];

                // Get create table statement
                $stmt = $this->db->query("SHOW CREATE TABLE `$tableName`");
                $createTable = $stmt->fetch();
                $sql .= $createTable['Create Table'] . ";\n\n";

                // Get table data
                $stmt = $this->db->query("SELECT * FROM `$tableName`");
                $rows = $stmt->fetchAll();

                if (!empty($rows)) {
                    $sql .= "INSERT INTO `$tableName` VALUES\n";
                    $values = [];
                    foreach ($rows as $row) {
                        $escapedValues = array_map(function($value) {
                            return is_null($value) ? 'NULL' : "'" . addslashes($value) . "'";
                        }, $row);
                        $values[] = "(" . implode(", ", $escapedValues) . ")";
                    }
                    $sql .= implode(",\n", $values) . ";\n\n";
                }
            }

            file_put_contents($filepath, $sql);
        } catch (\Exception $e) {
            throw new \Exception('Failed to create backup: ' . $e->getMessage());
        }
    }

    /**
     * Get bookings with filters
     */
    public function getBookings($filters = []) {
        try {
            $where = [];
            $params = [];

            if (!empty($filters['search'])) {
                $where[] = "(b.notes LIKE ? OR p.title LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            if (!empty($filters['status'])) {
                $where[] = "b.status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['property_id'])) {
                $where[] = "b.property_id = ?";
                $params[] = $filters['property_id'];
            }

            if (!empty($filters['user_id'])) {
                $where[] = "b.user_id = ?";
                $params[] = $filters['user_id'];
            }

            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            $offset = ($filters['page'] - 1) * $filters['per_page'];
            $limit = $filters['per_page'];

            $sql = "
                SELECT b.*, p.title as property_title, u.name as user_name
                FROM bookings b
                LEFT JOIN properties p ON b.property_id = p.id
                LEFT JOIN users u ON b.user_id = u.id
                $whereClause
                ORDER BY b.created_at DESC
                LIMIT $offset, $limit
            ";
            $stmt = $this->db->query($sql, null, \PDO::FETCH_ASSOC);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get booking statistics
     */
    public function getBookingStats() {
        try {
            $stats = [];

            // Total bookings by status
            $sql1 = "
                SELECT status, COUNT(*) as count
                FROM bookings
                GROUP BY status
            ";
            $stmt = $this->db->query($sql1, null, \PDO::FETCH_ASSOC);
            $stats['by_status'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Total bookings by month (last 12 months)
            $sql2 = "
                SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count
                FROM bookings
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month
            ";
            $stmt = $this->db->query($sql2, null, \PDO::FETCH_ASSOC);
            $stats['by_month'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $stats;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get system logs
     */
    public function getLogs($logType = 'error', $lines = 100) {
        try {
            $logFile = ROOT . 'logs/' . $logType . '.log';

            if (!file_exists($logFile)) {
                return [];
            }

            $logs = [];
            $file = new \SplFileObject($logFile, 'r');
            $file->seek(PHP_INT_MAX);
            $totalLines = $file->key();

            $startLine = max(0, $totalLines - $lines);
            $file->seek($startLine);

            while (!$file->eof() && count($logs) < $lines) {
                $line = trim($file->fgets());
                if (!empty($line)) {
                    $logs[] = [
                        'timestamp' => substr($line, 0, 19),
                        'level' => $this->extractLogLevel($line),
                        'message' => substr($line, 21)
                    ];
                }
            }

            return array_reverse($logs);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get available log files
     */
    public function getAvailableLogFiles() {
        try {
            $logDir = ROOT . 'logs/';
            if (!is_dir($logDir)) {
                return [];
            }

            $files = scandir($logDir);
            $logFiles = [];

            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'log') {
                    $logFiles[] = [
                        'name' => $file,
                        'size' => filesize($logDir . $file),
                        'modified' => date('Y-m-d H:i:s', filemtime($logDir . $file))
                    ];
                }
            }

            return $logFiles;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Extract log level from log line
     */
    private function extractLogLevel($line) {
        if (strpos($line, '[ERROR]') !== false) return 'ERROR';
        if (strpos($line, '[WARNING]') !== false) return 'WARNING';
        if (strpos($line, '[INFO]') !== false) return 'INFO';
        if (strpos($line, '[DEBUG]') !== false) return 'DEBUG';
        return 'UNKNOWN';
    }
}
