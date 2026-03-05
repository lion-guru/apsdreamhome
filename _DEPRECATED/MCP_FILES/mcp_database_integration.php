<?php
/**
 * APS Dream Home - MCP Database Integration
 * Store and manage MCP server data in database
 */

class MCPDatabaseIntegration {
    private $db;
    
    public function __construct() {
        // Connect to MySQL database
        $this->db = new PDO(
            'mysql:host=localhost;dbname=apsdreamhome',
            'root',
            '',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
        
        $this->createTables();
    }
    
    /**
     * Create MCP database tables
     */
    private function createTables() {
        // MCP Servers table
        $sql = "CREATE TABLE IF NOT EXISTS mcp_servers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            server_name VARCHAR(100) UNIQUE NOT NULL,
            server_type VARCHAR(50) NOT NULL,
            status ENUM('active', 'inactive', 'error') DEFAULT 'inactive',
            configuration JSON,
            last_started DATETIME,
            last_stopped DATETIME,
            uptime_seconds INT DEFAULT 0,
            error_count INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $this->db->exec($sql);
        
        // MCP Logs table
        $sql = "CREATE TABLE IF NOT EXISTS mcp_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            server_name VARCHAR(100) NOT NULL,
            log_level ENUM('info', 'warning', 'error', 'debug') DEFAULT 'info',
            message TEXT NOT NULL,
            details JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_server_created (server_name, created_at)
        )";
        
        $this->db->exec($sql);
        
        // MCP Data table for storing processed data
        $sql = "CREATE TABLE IF NOT EXISTS mcp_data (
            id INT AUTO_INCREMENT PRIMARY KEY,
            server_name VARCHAR(100) NOT NULL,
            data_type VARCHAR(100) NOT NULL,
            source_url VARCHAR(500),
            processed_data JSON,
            processing_time_ms INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_server_type_created (server_name, data_type, created_at)
        )";
        
        $this->db->exec($sql);
        
        // MCP Configuration History table
        $sql = "CREATE TABLE IF NOT EXISTS mcp_config_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            server_name VARCHAR(100) NOT NULL,
            old_configuration JSON,
            new_configuration JSON,
            changed_by VARCHAR(100),
            change_reason VARCHAR(500),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_server_created (server_name, created_at)
        )";
        
        $this->db->exec($sql);
    }
    
    /**
     * Register MCP server in database
     */
    public function registerServer($serverName, $serverType, $configuration) {
        $sql = "INSERT INTO mcp_servers (server_name, server_type, configuration, status) 
                 VALUES (?, ?, ?, 'inactive')
                 ON DUPLICATE KEY UPDATE 
                 server_type = VALUES(server_type),
                 configuration = VALUES(configuration),
                 updated_at = CURRENT_TIMESTAMP";
        
        $stmt = $this->db->prepare($sql);
        $configJson = json_encode($configuration);
        $stmt->execute([$serverName, $serverType, $configJson]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Update server status
     */
    public function updateServerStatus($serverName, $status, $error = null) {
        $sql = "UPDATE mcp_servers 
                 SET status = ?, 
                     last_started = CASE WHEN ? THEN CURRENT_TIMESTAMP ELSE last_started END,
                     last_stopped = CASE WHEN ? THEN CURRENT_TIMESTAMP ELSE last_stopped END,
                     error_count = CASE WHEN ? THEN error_count + 1 ELSE error_count END,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE server_name = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $status,
            $status === 'active',
            $status === 'error',
            $status === 'error',
            $serverName
        ]);
        
        // Log status change
        if ($error) {
            $this->logServerEvent($serverName, 'error', $error);
        } else {
            $this->logServerEvent($serverName, 'info', "Status changed to: {$status}");
        }
        
        return $stmt->rowCount();
    }
    
    /**
     * Log server event
     */
    public function logServerEvent($serverName, $logLevel, $message, $details = null) {
        $sql = "INSERT INTO mcp_logs (server_name, log_level, message, details) 
                 VALUES (?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $serverName,
            $logLevel,
            $message,
            $details ? json_encode($details) : null
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Store processed data from MCP server
     */
    public function storeProcessedData($serverName, $dataType, $sourceUrl, $processedData, $processingTime) {
        $sql = "INSERT INTO mcp_data (server_name, data_type, source_url, processed_data, processing_time_ms) 
                 VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $serverName,
            $dataType,
            $sourceUrl,
            json_encode($processedData),
            $processingTime
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Get server status
     */
    public function getServerStatus($serverName = null) {
        if ($serverName) {
            $sql = "SELECT * FROM mcp_servers WHERE server_name = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$serverName]);
            return $stmt->fetch();
        } else {
            $sql = "SELECT * FROM mcp_servers ORDER BY server_name";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        }
    }
    
    /**
     * Get server logs
     */
    public function getServerLogs($serverName = null, $limit = 100, $logLevel = null) {
        $sql = "SELECT * FROM mcp_logs WHERE 1=1";
        $params = [];
        
        if ($serverName) {
            $sql .= " AND server_name = ?";
            $params[] = $serverName;
        }
        
        if ($logLevel) {
            $sql .= " AND log_level = ?";
            $params[] = $logLevel;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get processed data
     */
    public function getProcessedData($serverName = null, $dataType = null, $limit = 50) {
        $sql = "SELECT * FROM mcp_data WHERE 1=1";
        $params = [];
        
        if ($serverName) {
            $sql .= " AND server_name = ?";
            $params[] = $serverName;
        }
        
        if ($dataType) {
            $sql .= " AND data_type = ?";
            $params[] = $dataType;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get server statistics
     */
    public function getServerStatistics($serverName = null) {
        $sql = "SELECT 
                     server_name,
                     status,
                     COUNT(*) as total_logs,
                     SUM(CASE WHEN log_level = 'error' THEN 1 ELSE 0 END) as error_count,
                     SUM(CASE WHEN log_level = 'warning' THEN 1 ELSE 0 END) as warning_count,
                     MAX(created_at) as last_activity
                 FROM mcp_logs ml
                 LEFT JOIN mcp_servers ms ON ml.server_name = ms.server_name
                 WHERE 1=1";
        
        $params = [];
        
        if ($serverName) {
            $sql .= " AND ml.server_name = ?";
            $params[] = $serverName;
        }
        
        $sql .= " GROUP BY ml.server_name, ms.status ORDER BY ml.server_name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Save configuration history
     */
    public function saveConfigurationHistory($serverName, $oldConfig, $newConfig, $changedBy, $reason) {
        $sql = "INSERT INTO mcp_config_history (server_name, old_configuration, new_configuration, changed_by, change_reason) 
                 VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $serverName,
            json_encode($oldConfig),
            json_encode($newConfig),
            $changedBy,
            $reason
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Get configuration history
     */
    public function getConfigurationHistory($serverName = null, $limit = 50) {
        $sql = "SELECT * FROM mcp_config_history WHERE 1=1";
        $params = [];
        
        if ($serverName) {
            $sql .= " AND server_name = ?";
            $params[] = $serverName;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Clean old logs (keep last 30 days)
     */
    public function cleanOldLogs() {
        $sql = "DELETE FROM mcp_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        $deletedLogs = $stmt->rowCount();
        
        $sql = "DELETE FROM mcp_data WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        $deletedData = $stmt->rowCount();
        
        return [
            'deleted_logs' => $deletedLogs,
            'deleted_data' => $deletedData
        ];
    }
}

// Example usage for MCP data integration
if (isset($_GET['action'])) {
    $mcpDb = new MCPDatabaseIntegration();
    
    switch ($_GET['action']) {
        case 'register_server':
            $serverName = $_POST['server_name'] ?? '';
            $serverType = $_POST['server_type'] ?? '';
            $config = $_POST['configuration'] ?? [];
            
            $result = $mcpDb->registerServer($serverName, $serverType, $config);
            echo json_encode(['success' => true, 'server_id' => $result]);
            break;
            
        case 'update_status':
            $serverName = $_POST['server_name'] ?? '';
            $status = $_POST['status'] ?? '';
            $error = $_POST['error'] ?? null;
            
            $result = $mcpDb->updateServerStatus($serverName, $status, $error);
            echo json_encode(['success' => true, 'updated' => $result]);
            break;
            
        case 'get_statistics':
            $serverName = $_GET['server_name'] ?? null;
            $stats = $mcpDb->getServerStatistics($serverName);
            echo json_encode(['success' => true, 'statistics' => $stats]);
            break;
            
        case 'get_logs':
            $serverName = $_GET['server_name'] ?? null;
            $limit = $_GET['limit'] ?? 100;
            $logs = $mcpDb->getServerLogs($serverName, $limit);
            echo json_encode(['success' => true, 'logs' => $logs]);
            break;
            
        case 'store_data':
            $serverName = $_POST['server_name'] ?? '';
            $dataType = $_POST['data_type'] ?? '';
            $sourceUrl = $_POST['source_url'] ?? '';
            $data = $_POST['processed_data'] ?? [];
            $processingTime = $_POST['processing_time'] ?? 0;
            
            $result = $mcpDb->storeProcessedData($serverName, $dataType, $sourceUrl, $data, $processingTime);
            echo json_encode(['success' => true, 'data_id' => $result]);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
}
?>
