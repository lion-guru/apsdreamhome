<?php

namespace App\Models;

use App\Models\Model;
use PDO;

/**
 * Admin Model
 * Handles all admin panel related database operations
 */
class Admin extends Model
{
    protected static string $table = 'admin';
    protected $primaryKey = 'aid';

    /**
     * Get admin by ID
     */
    public function getAdminById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE aid = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get admin by email
     */
    public function getAdminByEmail($email)
    {
        $sql = "SELECT * FROM {$this->table} WHERE aemail = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Authenticate admin
     */
    public function authenticateAdmin($email, $password)
    {
        $admin = $this->getAdminByEmail($email);

        if ($admin && password_verify($password, $admin['apass'])) {
            return $admin;
        }

        return false;
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats()
    {
        $stats = [];

        // Total users
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM users");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_users'] = $result ? (int)$result['total'] : 0;

        // Total properties
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM properties");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_properties'] = $result ? (int)$result['total'] : 0;

        // Total leads
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM leads");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_leads'] = $result ? (int)$result['total'] : 0;

        // Total farmers
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM farmers WHERE status = 'active'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_farmers'] = $result ? (int)$result['total'] : 0;

        // Recent activities count
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total
            FROM lead_activities
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['recent_activities'] = $result ? (int)$result['total'] : 0;

        // Monthly revenue
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(amount), 0) as total
            FROM payments
            WHERE status = 'completed'
            AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['monthly_revenue'] = $result ? (float)$result['total'] : 0;

        // System health indicators
        $stats['system_health'] = $this->getSystemHealth();

        return $stats;
    }

    /**
     * Get recent activities
     */
    public function getRecentActivities($limit = 10)
    {
        $sql = "
            SELECT la.*, l.name as lead_name, u.name as user_name, a.activity_name
            FROM lead_activities la
            LEFT JOIN leads l ON la.lead_id = l.id
            LEFT JOIN users u ON la.user_id = u.id
            LEFT JOIN activities a ON la.activity_id = a.id
            ORDER BY la.created_at DESC
            LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get users with filters (Admin Panel)
     */
    public function getUsersWithFilters($filters = [])
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['search'])) {
            $conditions[] = "(u.name LIKE :search OR u.email LIKE :search OR u.phone LIKE :search)";
            $params['search'] = "%{$filters['search']}%";
        }

        if (!empty($filters['role'])) {
            $conditions[] = "u.role = :role";
            $params['role'] = $filters['role'];
        }

        if (!empty($filters['status'])) {
            $conditions[] = "u.status = :status";
            $params['status'] = $filters['status'];
        }

        $whereClause = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : "";

        $offset = ($filters['page'] - 1) * $filters['per_page'];
        $limit = $filters['per_page'];

        $sql = "
            SELECT u.*, COUNT(p.id) as property_count, COUNT(l.id) as lead_count
            FROM users u
            LEFT JOIN properties p ON u.id = p.user_id
            LEFT JOIN leads l ON u.id = l.assigned_to
            {$whereClause}
            GROUP BY u.id
            ORDER BY u.created_at DESC
            LIMIT {$offset}, {$limit}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get user statistics for admin
     */
    public function getUserStats()
    {
        $stats = [];

        // Users by role
        $stmt = $this->db->prepare("
            SELECT role, COUNT(*) as count
            FROM users
            GROUP BY role
        ");
        $stmt->execute();
        $stats['by_role'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Users by status
        $stmt = $this->db->prepare("
            SELECT status, COUNT(*) as count
            FROM users
            GROUP BY status
        ");
        $stmt->execute();
        $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Recent registrations
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM users
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['recent_registrations'] = $result['count'];

        return $stats;
    }

    /**
     * Get properties with filters (Admin Panel)
     */
    public function getPropertiesWithFilters($filters = [])
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['search'])) {
            $conditions[] = "(p.title LIKE :search OR p.description LIKE :search OR p.location LIKE :search)";
            $params['search'] = "%{$filters['search']}%";
        }

        if (!empty($filters['type'])) {
            $conditions[] = "p.type = :type";
            $params['type'] = $filters['type'];
        }

        if (!empty($filters['status'])) {
            $conditions[] = "p.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['user_id'])) {
            $conditions[] = "p.user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }

        $whereClause = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : "";

        $offset = ($filters['page'] - 1) * $filters['per_page'];
        $limit = $filters['per_page'];

        $sql = "
            SELECT p.*, u.name as owner_name, u.email as owner_email, u.phone as owner_phone,
                   COUNT(pi.id) as image_count, COUNT(b.id) as booking_count
            FROM properties p
            LEFT JOIN users u ON p.user_id = u.id
            LEFT JOIN property_images pi ON p.id = pi.property_id
            LEFT JOIN bookings b ON p.id = b.property_id
            {$whereClause}
            GROUP BY p.id
            ORDER BY p.created_at DESC
            LIMIT {$offset}, {$limit}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get property statistics for admin
     */
    public function getPropertyStats()
    {
        $stats = [];

        // Properties by type
        $stmt = $this->db->prepare("
            SELECT type, COUNT(*) as count, AVG(price) as avg_price, SUM(price) as total_value
            FROM properties
            GROUP BY type
        ");
        $stmt->execute();
        $stats['by_type'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Properties by status
        $stmt = $this->db->prepare("
            SELECT status, COUNT(*) as count
            FROM properties
            GROUP BY status
        ");
        $stmt->execute();
        $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Price ranges
        $stmt = $this->db->prepare("
            SELECT
                COUNT(CASE WHEN price < 1000000 THEN 1 END) as under_10_lakh,
                COUNT(CASE WHEN price BETWEEN 1000000 AND 5000000 THEN 1 END) as between_10_50_lakh,
                COUNT(CASE WHEN price BETWEEN 5000000 AND 10000000 THEN 1 END) as between_50_lakh_1_cr,
                COUNT(CASE WHEN price > 10000000 THEN 1 END) as above_1_cr
            FROM properties
        ");
        $stmt->execute();
        $stats['by_price_range'] = $stmt->fetch(PDO::FETCH_ASSOC);

        return $stats;
    }

    /**
     * Get leads with filters (Admin Panel)
     */
    public function getLeadsWithFilters($filters = [])
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['search'])) {
            $conditions[] = "(l.name LIKE :search OR l.email LIKE :search OR l.phone LIKE :search OR l.company LIKE :search)";
            $params['search'] = "%{$filters['search']}%";
        }

        if (!empty($filters['status'])) {
            $conditions[] = "l.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['source'])) {
            $conditions[] = "l.source = :source";
            $params['source'] = $filters['source'];
        }

        if (!empty($filters['assigned_to'])) {
            $conditions[] = "l.assigned_to = :assigned_to";
            $params['assigned_to'] = $filters['assigned_to'];
        }

        if (!empty($filters['date_from'])) {
            $conditions[] = "l.created_at >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $conditions[] = "l.created_at <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        $whereClause = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : "";

        $offset = ($filters['page'] - 1) * $filters['per_page'];
        $limit = $filters['per_page'];

        $sql = "
            SELECT l.*, u.name as assigned_user_name, u.email as assigned_user_email,
                   s.source_name, st.status_name, p.priority_name,
                   COUNT(la.id) as activity_count, COUNT(ln.id) as note_count
            FROM leads l
            LEFT JOIN users u ON l.assigned_to = u.id
            LEFT JOIN lead_sources s ON l.source = s.id
            LEFT JOIN lead_statuses st ON l.status = st.id
            LEFT JOIN lead_priorities p ON l.priority = p.id
            LEFT JOIN lead_activities la ON l.id = la.lead_id
            LEFT JOIN lead_notes ln ON l.id = ln.lead_id
            {$whereClause}
            GROUP BY l.id
            ORDER BY l.created_at DESC
            LIMIT {$offset}, {$limit}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get lead statistics for admin
     */
    public function getLeadStats()
    {
        $stats = [];

        // Leads by status
        $stmt = $this->db->prepare("
            SELECT st.status_name as status, COUNT(l.id) as count,
                   COUNT(CASE WHEN l.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as recent_count
            FROM lead_statuses st
            LEFT JOIN leads l ON st.id = l.status
            GROUP BY st.id, st.status_name
        ");
        $stmt->execute();
        $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Leads by source
        $stmt = $this->db->prepare("
            SELECT s.source_name as source, COUNT(l.id) as count
            FROM lead_sources s
            LEFT JOIN leads l ON s.id = l.source
            GROUP BY s.id, s.source_name
        ");
        $stmt->execute();
        $stats['by_source'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Leads by priority
        $stmt = $this->db->prepare("
            SELECT p.priority_name as priority, COUNT(l.id) as count
            FROM lead_priorities p
            LEFT JOIN leads l ON p.id = l.priority
            GROUP BY p.id, p.priority_name
        ");
        $stmt->execute();
        $stats['by_priority'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Conversion rates
        $stmt = $this->db->prepare("
            SELECT
                COUNT(CASE WHEN l.status = 'converted' THEN 1 END) as converted,
                COUNT(*) as total,
                ROUND((COUNT(CASE WHEN l.status = 'converted' THEN 1 END) / COUNT(*)) * 100, 2) as conversion_rate
            FROM leads l
            WHERE l.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $stmt->execute();
        $stats['conversion_rate'] = $stmt->fetch(PDO::FETCH_ASSOC);

        return $stats;
    }

    /**
     * Get bookings with filters (Admin Panel)
     */
    public function getBookingsWithFilters($filters = [])
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['search'])) {
            $conditions[] = "(b.notes LIKE :search OR p.title LIKE :search OR u.name LIKE :search)";
            $params['search'] = "%{$filters['search']}%";
        }

        if (!empty($filters['status'])) {
            $conditions[] = "b.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['property_id'])) {
            $conditions[] = "b.property_id = :property_id";
            $params['property_id'] = $filters['property_id'];
        }

        if (!empty($filters['user_id'])) {
            $conditions[] = "b.user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }

        $whereClause = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : "";

        $offset = ($filters['page'] - 1) * $filters['per_page'];
        $limit = $filters['per_page'];

        $sql = "
            SELECT b.*, p.title as property_title, p.price as property_price,
                   u.name as user_name, u.email as user_email, u.phone as user_phone,
                   GROUP_CONCAT(pi.image_path) as property_images
            FROM bookings b
            LEFT JOIN properties p ON b.property_id = p.id
            LEFT JOIN users u ON b.user_id = u.id
            LEFT JOIN property_images pi ON p.id = pi.property_id
            {$whereClause}
            GROUP BY b.id
            ORDER BY b.created_at DESC
            LIMIT {$offset}, {$limit}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get booking statistics for admin
     */
    public function getBookingStats()
    {
        $stats = [];

        // Bookings by status
        $stmt = $this->db->prepare("
            SELECT b.status, COUNT(b.id) as count,
                   SUM(p.price) as total_value
            FROM bookings b
            LEFT JOIN properties p ON b.property_id = p.id
            GROUP BY b.status
        ");
        $stmt->execute();
        $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Bookings by month (last 12 months)
        $stmt = $this->db->prepare("
            SELECT DATE_FORMAT(b.created_at, '%Y-%m') as month,
                   COUNT(b.id) as count,
                   SUM(p.price) as total_value
            FROM bookings b
            LEFT JOIN properties p ON b.property_id = p.id
            WHERE b.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(b.created_at, '%Y-%m')
            ORDER BY month
        ");
        $stmt->execute();
        $stats['by_month'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $stats;
    }

    /**
     * Get all settings
     */
    public function getAllSettings()
    {
        $sql = "SELECT * FROM site_settings ORDER BY setting_group, setting_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get setting groups
     */
    public function getSettingGroups()
    {
        $sql = "SELECT DISTINCT setting_group FROM site_settings ORDER BY setting_group";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update setting
     */
    public function updateSetting($key, $value)
    {
        $sql = "
            INSERT INTO site_settings (setting_name, setting_value, updated_at)
            VALUES (:key, :value, NOW())
            ON DUPLICATE KEY UPDATE setting_value = :value, updated_at = NOW()
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'key' => $key,
            'value' => $value
        ]);
    }

    /**
     * Get database statistics
     */
    public function getDatabaseStats()
    {
        $stats = [];

        // Get table information
        $stmt = $this->db->prepare("SHOW TABLE STATUS");
        $stmt->execute();
        $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stats['total_tables'] = count($tables);
        $stats['total_size'] = array_sum(array_column($tables, 'Data_length')) +
                              array_sum(array_column($tables, 'Index_length'));

        // Get record counts for main tables
        $mainTables = ['users', 'properties', 'leads', 'farmers', 'bookings', 'payments'];
        $stats['table_counts'] = [];

        foreach ($mainTables as $table) {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM {$table}");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['table_counts'][$table] = $result['count'];
        }

        return $stats;
    }

    /**
     * Get backup files
     */
    public function getBackupFiles()
    {
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
                    'date' => date('Y-m-d H:i:s', filemtime($backupDir . $file)),
                    'formatted_size' => $this->formatBytes(filesize($backupDir . $file))
                ];
            }
        }

        return $backupFiles;
    }

    /**
     * Create database backup
     */
    public function createBackup()
    {
        $backupDir = ROOT . 'backups/';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = $backupDir . $filename;

        return $this->createSimpleBackup($filepath);
    }

    /**
     * Get system logs
     */
    public function getLogs($logType = 'error', $lines = 100)
    {
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
    }

    /**
     * Get available log files
     */
    public function getAvailableLogFiles()
    {
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
                    'formatted_size' => $this->formatBytes(filesize($logDir . $file)),
                    'modified' => date('Y-m-d H:i:s', filemtime($logDir . $file))
                ];
            }
        }

        return $logFiles;
    }

    /**
     * Export data for admin
     */
    public function exportData($type)
    {
        switch ($type) {
            case 'users':
                $sql = "SELECT name, email, phone, role, status, created_at FROM users";
                break;
            case 'properties':
                $sql = "SELECT title, description, price, location, type, status, created_at FROM properties";
                break;
            case 'leads':
                $sql = "SELECT name, email, phone, source, status, budget, created_at FROM leads";
                break;
            case 'farmers':
                $sql = "SELECT name, email, phone, state_id, district_id, status, created_at FROM farmers";
                break;
            case 'bookings':
                $sql = "SELECT b.created_at, p.title as property, u.name as customer, b.status FROM bookings b LEFT JOIN properties p ON b.property_id = p.id LEFT JOIN users u ON b.user_id = u.id";
                break;
            case 'payments':
                $sql = "SELECT p.title as property, u.name as customer, pay.amount, pay.status, pay.created_at FROM payments pay LEFT JOIN properties p ON pay.property_id = p.id LEFT JOIN users u ON pay.user_id = u.id";
                break;
            default:
                throw new \Exception('Invalid export type');
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get system health status
     */
    public function getSystemHealth()
    {
        return [
            'database' => $this->checkDatabaseConnection(),
            'disk_space' => $this->checkDiskSpace(),
            'memory_usage' => $this->checkMemoryUsage(),
            'last_backup' => $this->getLastBackupDate()
        ];
    }

    /**
     * Check database connection
     */
    private function checkDatabaseConnection()
    {
        try {
            $stmt = $this->db->prepare('SELECT 1');
            $stmt->execute();
            return ['status' => 'OK', 'message' => 'Database connection is active'];
        } catch (\PDOException $e) {
            return ['status' => 'ERROR', 'message' => 'Database connection failed: ' . $e->getMessage()];
        }
    }

    /**
     * Check disk space
     */
    private function checkDiskSpace()
    {
        $free = disk_free_space(ROOT);
        $total = disk_total_space(ROOT);
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
    private function checkMemoryUsage()
    {
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
    private function getLastBackupDate()
    {
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
    private function formatBytes($bytes, $precision = 2)
    {
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
    private function getMemoryLimit()
    {
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
     * Extract log level from log line
     */
    private function extractLogLevel($line)
    {
        if (strpos($line, '[ERROR]') !== false) return 'ERROR';
        if (strpos($line, '[WARNING]') !== false) return 'WARNING';
        if (strpos($line, '[INFO]') !== false) return 'INFO';
        if (strpos($line, '[DEBUG]') !== false) return 'DEBUG';
        return 'UNKNOWN';
    }

    /**
     * Create simple backup
     */
    private function createSimpleBackup($filepath)
    {
        // Get all tables
        $stmt = $this->db->prepare("SHOW TABLES");
        $stmt->execute();
        $tables = $stmt->fetchAll(PDO::FETCH_NUM);

        $sql = "-- APS Dream Home Database Backup\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";

        foreach ($tables as $table) {
            $tableName = $table[0];

            // Get create table statement
            $stmt = $this->db->prepare("SHOW CREATE TABLE `{$tableName}`");
            $stmt->execute();
            $createTable = $stmt->fetch(PDO::FETCH_ASSOC);
            $sql .= $createTable['Create Table'] . ";\n\n";

            // Get table data
            $stmt = $this->db->prepare("SELECT * FROM `{$tableName}`");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                $sql .= "INSERT INTO `{$tableName}` VALUES\n";
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

        return file_put_contents($filepath, $sql) ? $filepath : false;
    }

    /**
     * Get comprehensive admin analytics
     */
    public function getAdminAnalytics()
    {
        $analytics = [];

        // System Overview
        $stmt = $this->db->prepare("
            SELECT
                (SELECT COUNT(*) FROM users WHERE role = 'customer' AND status = 'active') as total_customers,
                (SELECT COUNT(*) FROM users WHERE role = 'agent' AND status = 'active') as total_agents,
                (SELECT COUNT(*) FROM associates WHERE status = 'active') as total_associates,
                (SELECT COUNT(*) FROM properties WHERE status = 'available') as available_properties,
                (SELECT COUNT(*) FROM properties WHERE status = 'sold') as sold_properties,
                (SELECT COUNT(*) FROM bookings WHERE status = 'confirmed') as confirmed_bookings,
                (SELECT COUNT(*) FROM payments WHERE status = 'completed') as completed_payments,
                (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'completed') as total_revenue,
                (SELECT COUNT(*) FROM leads WHERE status = 'new') as new_leads,
                (SELECT COUNT(*) FROM leads WHERE status = 'converted') as converted_leads
        ");
        $stmt->execute();
        $analytics['system_overview'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // Monthly Trends
        $stmt = $this->db->prepare("
            SELECT
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(CASE WHEN role = 'customer' THEN 1 END) as new_customers,
                COUNT(CASE WHEN role = 'agent' THEN 1 END) as new_agents,
                COUNT(CASE WHEN role = 'associate' THEN 1 END) as new_associates,
                COUNT(properties.id) as new_properties,
                COALESCE(SUM(CASE WHEN payments.status = 'completed' THEN payments.amount ELSE 0 END), 0) as monthly_revenue
            FROM users
            LEFT JOIN associates ON users.id = associates.user_id
            LEFT JOIN properties ON DATE_FORMAT(properties.created_at, '%Y-%m') = DATE_FORMAT(users.created_at, '%Y-%m')
            LEFT JOIN payments ON DATE_FORMAT(payments.created_at, '%Y-%m') = DATE_FORMAT(users.created_at, '%Y-%m')
            WHERE users.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month DESC
            LIMIT 12
        ");
        $stmt->execute();
        $analytics['monthly_trends'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Top Performing Agents
        $stmt = $this->db->prepare("
            SELECT u.name, u.email, u.phone,
                   COUNT(p.id) as properties_listed,
                   COUNT(CASE WHEN p.status = 'sold' THEN 1 END) as properties_sold,
                   COALESCE(SUM(CASE WHEN pay.status = 'completed' THEN pay.amount ELSE 0 END), 0) as total_sales,
                   COUNT(b.id) as total_bookings,
                   AVG(pr.rating) as avg_rating,
                   COUNT(pr.id) as total_reviews
            FROM users u
            LEFT JOIN properties p ON u.id = p.created_by
            LEFT JOIN payments pay ON p.id = pay.property_id AND pay.status = 'completed'
            LEFT JOIN bookings b ON u.id = b.agent_id
            LEFT JOIN property_reviews pr ON p.id = pr.property_id
            WHERE u.role = 'agent' AND u.status = 'active'
            GROUP BY u.id
            ORDER BY total_sales DESC
            LIMIT 10
        ");
        $stmt->execute();
        $analytics['top_agents'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Top Performing Associates
        $stmt = $this->db->prepare("
            SELECT a.*, u.name, u.email, u.phone,
                   COUNT(CASE WHEN down.associate_id IS NOT NULL THEN 1 END) as total_downline,
                   COALESCE(SUM(CASE WHEN p.status = 'completed' THEN p.amount ELSE 0 END), 0) as personal_sales,
                   COALESCE(SUM(CASE WHEN ac.commission_amount IS NOT NULL THEN ac.commission_amount ELSE 0 END), 0) as total_commissions,
                   COUNT(CASE WHEN po.status = 'completed' THEN 1 END) as payouts_received
            FROM associates a
            JOIN users u ON a.user_id = u.id
            LEFT JOIN associates down ON a.associate_id = down.sponsor_id
            LEFT JOIN payments p ON a.associate_id = p.associate_id
            LEFT JOIN associate_commissions ac ON a.associate_id = ac.associate_id
            LEFT JOIN payouts po ON a.associate_id = po.associate_id
            WHERE a.status = 'active'
            GROUP BY a.associate_id
            ORDER BY total_commissions DESC
            LIMIT 10
        ");
        $stmt->execute();
        $analytics['top_associates'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Property Performance by Type
        $stmt = $this->db->prepare("
            SELECT pt.name as property_type, pt.icon,
                   COUNT(p.id) as total_properties,
                   COUNT(CASE WHEN p.status = 'sold' THEN 1 END) as sold_properties,
                   COALESCE(SUM(CASE WHEN p.status = 'sold' THEN p.price ELSE 0 END), 0) as total_sales_value,
                   AVG(p.price) as avg_price
            FROM property_types pt
            LEFT JOIN properties p ON pt.id = p.property_type_id
            GROUP BY pt.id
            ORDER BY total_sales_value DESC
        ");
        $stmt->execute();
        $analytics['property_performance'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Location Performance
        $stmt = $this->db->prepare("
            SELECT p.city, p.state,
                   COUNT(p.id) as total_properties,
                   COUNT(CASE WHEN p.status = 'sold' THEN 1 END) as sold_properties,
                   COALESCE(SUM(CASE WHEN p.status = 'sold' THEN p.price ELSE 0 END), 0) as total_sales_value,
                   AVG(p.price) as avg_price
            FROM properties p
            WHERE p.city IS NOT NULL AND p.city != ''
            GROUP BY p.city, p.state
            HAVING total_properties > 0
            ORDER BY total_sales_value DESC
            LIMIT 15
        ");
        $stmt->execute();
        $analytics['location_performance'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $analytics;
    }

    /**
     * Get admin notifications and alerts
     */
    public function getAdminNotifications()
    {
        $notifications = [];

        // Pending KYC verifications
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as pending_kyc
            FROM associates
            WHERE kyc_status = 'pending'
        ");
        $stmt->execute();
        $pendingKYC = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($pendingKYC['pending_kyc'] > 0) {
            $notifications[] = [
                'type' => 'warning',
                'title' => 'KYC Verification Pending',
                'message' => $pendingKYC['pending_kyc'] . ' associates are waiting for KYC verification',
                'action_url' => '/admin/associates?kyc_status=pending'
            ];
        }

        // Pending payout requests
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as pending_payouts,
                   COALESCE(SUM(amount), 0) as total_amount
            FROM payouts
            WHERE status = 'pending'
        ");
        $stmt->execute();
        $pendingPayouts = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($pendingPayouts['pending_payouts'] > 0) {
            $notifications[] = [
                'type' => 'info',
                'title' => 'Payout Requests Pending',
                'message' => $pendingPayouts['pending_payouts'] . ' payout requests awaiting approval (₹' . number_format($pendingPayouts['total_amount']) . ')',
                'action_url' => '/admin/payouts?status=pending'
            ];
        }

        // Low stock properties (less than 5 available)
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as low_stock_properties
            FROM properties
            WHERE status = 'available'
        ");
        $stmt->execute();
        $lowStock = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($lowStock['low_stock_properties'] < 10) {
            $notifications[] = [
                'type' => 'warning',
                'title' => 'Low Property Inventory',
                'message' => 'Only ' . $lowStock['low_stock_properties'] . ' properties are currently available',
                'action_url' => '/admin/properties?status=available'
            ];
        }

        // New registrations today
        $stmt = $this->db->prepare("
            SELECT
                COUNT(CASE WHEN role = 'customer' THEN 1 END) as new_customers,
                COUNT(CASE WHEN role = 'agent' THEN 1 END) as new_agents,
                COUNT(CASE WHEN role = 'associate' THEN 1 END) as new_associates
            FROM users
            WHERE DATE(created_at) = CURDATE()
        ");
        $stmt->execute();
        $newRegistrations = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($newRegistrations['new_customers'] > 0 || $newRegistrations['new_agents'] > 0 || $newRegistrations['new_associates'] > 0) {
            $notifications[] = [
                'type' => 'success',
                'title' => 'New Registrations Today',
                'message' => $newRegistrations['new_customers'] . ' customers, ' . $newRegistrations['new_agents'] . ' agents, ' . $newRegistrations['new_associates'] . ' associates registered',
                'action_url' => '/admin/users'
            ];
        }

        return $notifications;
    }

    /**
     * Get reports data for admin
     */
    public function getReportsData($reportType, $filters = [])
    {
        switch ($reportType) {
            case 'sales':
                return $this->getSalesReport($filters);
            case 'associates':
                return $this->getAssociatesReport($filters);
            case 'properties':
                return $this->getPropertiesReport($filters);
            case 'customers':
                return $this->getCustomersReport($filters);
            case 'financial':
                return $this->getFinancialReport($filters);
            default:
                return [];
        }
    }

    /**
     * Get sales report data
     */
    private function getSalesReport($filters)
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['date_from'])) {
            $conditions[] = "p.created_at >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $conditions[] = "p.created_at <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        if (!empty($filters['property_type'])) {
            $conditions[] = "p.property_type_id = :property_type";
            $params['property_type'] = $filters['property_type'];
        }

        if (!empty($filters['city'])) {
            $conditions[] = "p.city = :city";
            $params['city'] = $filters['city'];
        }

        $whereClause = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : "";

        $sql = "
            SELECT DATE_FORMAT(p.created_at, '%Y-%m-%d') as sale_date,
                   COUNT(*) as sales_count,
                   COALESCE(SUM(p.price), 0) as total_value,
                   AVG(p.price) as avg_sale_price,
                   p.city, p.state, pt.name as property_type
            FROM properties p
            LEFT JOIN property_types pt ON p.property_type_id = pt.id
            {$whereClause}
            GROUP BY DATE_FORMAT(p.created_at, '%Y-%m-%d'), p.city, p.state, pt.name
            ORDER BY sale_date DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get associates report data
     */
    private function getAssociatesReport($filters)
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['status'])) {
            $conditions[] = "a.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['kyc_status'])) {
            $conditions[] = "a.kyc_status = :kyc_status";
            $params['kyc_status'] = $filters['kyc_status'];
        }

        if (!empty($filters['level'])) {
            $conditions[] = "a.level = :level";
            $params['level'] = $filters['level'];
        }

        $whereClause = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : "";

        $sql = "
            SELECT a.*, u.name, u.email, u.phone, u.city, u.state,
                   COUNT(down.associate_id) as downline_count,
                   COALESCE(SUM(CASE WHEN p.status = 'completed' THEN p.amount ELSE 0 END), 0) as total_sales,
                   COALESCE(SUM(ac.commission_amount), 0) as total_commissions,
                   a.joining_date, a.kyc_status, a.status as associate_status
            FROM associates a
            JOIN users u ON a.user_id = u.id
            LEFT JOIN associates down ON a.associate_id = down.sponsor_id
            LEFT JOIN payments p ON a.associate_id = p.associate_id
            LEFT JOIN associate_commissions ac ON a.associate_id = ac.associate_id
            {$whereClause}
            GROUP BY a.associate_id
            ORDER BY a.joining_date DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get properties report data
     */
    private function getPropertiesReport($filters)
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['status'])) {
            $conditions[] = "p.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['property_type'])) {
            $conditions[] = "p.property_type_id = :property_type";
            $params['property_type'] = $filters['property_type'];
        }

        if (!empty($filters['city'])) {
            $conditions[] = "p.city = :city";
            $params['city'] = $filters['city'];
        }

        $whereClause = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : "";

        $sql = "
            SELECT p.*, pt.name as property_type_name, pt.icon,
                   u.name as agent_name, u.phone as agent_phone,
                   (SELECT COUNT(*) FROM property_images pi WHERE pi.property_id = p.id) as image_count,
                   (SELECT AVG(rating) FROM property_reviews pr WHERE pr.property_id = p.id) as avg_rating,
                   (SELECT COUNT(*) FROM property_reviews pr2 WHERE pr2.property_id = p.id) as review_count,
                   p.created_at, p.updated_at
            FROM properties p
            LEFT JOIN property_types pt ON p.property_type_id = pt.id
            LEFT JOIN users u ON p.created_by = u.id
            {$whereClause}
            ORDER BY p.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get customers report data
     */
    private function getCustomersReport($filters)
    {
        $conditions = ["u.role = 'customer' AND u.status = 'active'"];
        $params = [];

        if (!empty($filters['city'])) {
            $conditions[] = "c.city = :city";
            $params['city'] = $filters['city'];
        }

        if (!empty($filters['registration_date_from'])) {
            $conditions[] = "u.created_at >= :registration_date_from";
            $params['registration_date_from'] = $filters['registration_date_from'];
        }

        if (!empty($filters['registration_date_to'])) {
            $conditions[] = "u.created_at <= :registration_date_to";
            $params['registration_date_to'] = $filters['registration_date_to'];
        }

        $whereClause = "WHERE " . implode(' AND ', $conditions);

        $sql = "
            SELECT u.*, c.phone, c.address, c.city, c.state, c.pincode, c.occupation,
                   COUNT(DISTINCT pv.id) as total_views,
                   COUNT(DISTINCT cf.id) as total_favorites,
                   COUNT(DISTINCT b.id) as total_bookings,
                   COALESCE(SUM(CASE WHEN pay.status = 'completed' THEN pay.amount ELSE 0 END), 0) as total_spent,
                   COUNT(DISTINCT pr.id) as total_reviews,
                   u.created_at as registration_date
            FROM {$this->table} u
            LEFT JOIN customer_profiles c ON u.id = c.user_id
            LEFT JOIN property_views pv ON u.id = pv.customer_id
            LEFT JOIN customer_favorites cf ON u.id = cf.customer_id
            LEFT JOIN bookings b ON u.id = b.customer_id
            LEFT JOIN payments pay ON u.id = pay.user_id
            LEFT JOIN property_reviews pr ON u.id = pr.customer_id
            {$whereClause}
            GROUP BY u.id
            ORDER BY u.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get financial report data
     */
    private function getFinancialReport($filters)
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['date_from'])) {
            $conditions[] = "p.created_at >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $conditions[] = "p.created_at <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        $whereClause = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : "";

        $sql = "
            SELECT 'property_sales' as type,
                   DATE_FORMAT(p.created_at, '%Y-%m-%d') as transaction_date,
                   p.title as description,
                   p.price as amount,
                   'income' as flow_type,
                   p.id as reference_id
            FROM properties p
            WHERE p.status = 'sold' {$whereClause}

            UNION ALL

            SELECT 'associate_commissions' as type,
                   DATE_FORMAT(ac.created_at, '%Y-%m-%d') as transaction_date,
                   CONCAT('Commission for ', u.name) as description,
                   ac.commission_amount as amount,
                   'expense' as flow_type,
                   ac.id as reference_id
            FROM associate_commissions ac
            JOIN associates a ON ac.associate_id = a.associate_id
            JOIN users u ON a.user_id = u.id
            {$whereClause}

            UNION ALL

            SELECT 'payouts' as type,
                   DATE_FORMAT(po.payout_date, '%Y-%m-%d') as transaction_date,
                   CONCAT('Payout to ', u.name) as description,
                   po.amount as amount,
                   'expense' as flow_type,
                   po.id as reference_id
            FROM payouts po
            JOIN associates a ON po.associate_id = a.associate_id
            JOIN users u ON a.user_id = u.id
            WHERE po.status = 'completed' {$whereClause}

            ORDER BY transaction_date DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
