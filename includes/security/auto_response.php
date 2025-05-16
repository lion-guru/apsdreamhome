<?php
/**
 * Automated Response System
 * Handles automated responses to security incidents and system issues
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../security_logger.php';
require_once __DIR__ . '/../notification/email_manager.php';
require_once __DIR__ . '/../notification/sms_manager.php';
require_once __DIR__ . '/../// SECURITY: Sensitive information removed_manager.php';

class AutoResponseSystem {
    private $logger;
    private $emailManager;
    private $smsManager;
    private $apiKeyManager;
    private $con;
    private $config;

    // Define response actions for different incident types
    private const RESPONSE_ACTIONS = [
        'brute_force' => [
            'threshold' => 5,
            'cooldown' => 3600,
            'actions' => ['block_ip', 'notify_admin', 'log_incident']
        ],
        'api_abuse' => [
            'threshold' => 100,
            'cooldown' => 1800,
            'actions' => ['revoke_key', 'notify_admin', 'log_incident']
        ],
        'malware_detected' => [
            'threshold' => 1,
            'cooldown' => 0,
            'actions' => ['quarantine_file', 'notify_admin', 'block_ip', 'log_incident']
        ],
        'sql_injection' => [
            'threshold' => 2,
            'cooldown' => 1800,
            'actions' => ['block_ip', 'notify_admin', 'log_incident']
        ],
        'disk_space_critical' => [
            'threshold' => 90, // 90% usage
            'cooldown' => 3600,
            'actions' => ['cleanup_temp', 'cleanup_logs', 'notify_admin', 'log_incident']
        ],
        'high_error_rate' => [
            'threshold' => 50, // errors per minute
            'cooldown' => 900,
            'actions' => ['restart_service', 'notify_admin', 'log_incident']
        ],
        'backup_failure' => [
            'threshold' => 2,
            'cooldown' => 7200,
            'actions' => ['retry_backup', 'notify_admin', 'log_incident']
        ],
        'rate_limit_exceeded' => [
            'threshold' => 10,
            'cooldown' => 1800,
            'actions' => ['temporary_block', 'notify_admin', 'log_incident']
        ]
    ];

    public function __construct($database_connection = null) {
        $this->con = $database_connection ?? getDbConnection();
        $this->logger = new SecurityLogger();
        $this->emailManager = new EmailManager();
        $this->smsManager = new SmsManager();
        $this->apiKeyManager = new ApiKeyManager();
        $this->loadConfig();
    }

    /**
     * Load configuration
     */
    private function loadConfig() {
        $this->config = [
            'blocked_ips_file' => __DIR__ . '/../../data/security/blocked_ips.json',
            'quarantine_dir' => __DIR__ . '/../../data/security/quarantine',
            'temp_dir' => __DIR__ . '/../../temp',
            'log_dir' => __DIR__ . '/../../logs',
            'service_name' => getenv('SERVICE_NAME') ?: 'apache2',
            'max_response_attempts' => 3
        ];

        // Create necessary directories
        foreach (['quarantine_dir', 'temp_dir'] as $dir) {
            if (!is_dir($this->config[$dir])) {
                mkdir($this->config[$dir], 0750, true);
            }
        }
    }

    /**
     * Handle security incident
     */
    public function handleIncident($type, $data) {
        if (!isset(self::RESPONSE_ACTIONS[$type])) {
            $this->logger->error('Unknown incident type', ['type' => $type]);
            return false;
        }

        $config = self::RESPONSE_ACTIONS[$type];
        
        // Check if incident meets threshold
        if (!$this->meetsThreshold($type, $data, $config['threshold'])) {
            return false;
        }

        // Check cooldown period
        if (!$this->checkCooldown($type, $config['cooldown'])) {
            return false;
        }

        // Execute response actions
        $success = true;
        foreach ($config['actions'] as $action) {
            try {
                $this->executeAction($action, $type, $data);
            } catch (Exception $e) {
                $success = false;
                $this->logger->error('Action failed', [
                    'action' => $action,
                    'type' => $type,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $success;
    }

    /**
     * Check if incident meets threshold
     */
    private function meetsThreshold($type, $data, $threshold) {
        switch ($type) {
            case 'brute_force':
                return ($data['failed_attempts'] ?? 0) >= $threshold;
                
            case 'api_abuse':
                return ($data['requests_per_minute'] ?? 0) >= $threshold;
                
            case 'disk_space_critical':
                return ($data['usage_percentage'] ?? 0) >= $threshold;
                
            case 'high_error_rate':
                return ($data['errors_per_minute'] ?? 0) >= $threshold;
                
            default:
                return true;
        }
    }

    /**
     * Check cooldown period
     */
    private function checkCooldown($type, $cooldown) {
        if ($cooldown === 0) {
            return true;
        }

        $key = "auto_response_cooldown_{$type}";
        $lastResponse = $this->getState($key);
        
        if ($lastResponse && (time() - $lastResponse) < $cooldown) {
            return false;
        }

        $this->setState($key, time());
        return true;
    }

    /**
     * Execute response action
     */
    private function executeAction($action, $type, $data) {
        switch ($action) {
            case 'block_ip':
                $this->blockIp($data['ip'] ?? '');
                break;
                
            case 'revoke_key':
                if (isset($data['// SECURITY: Sensitive information removed_id'])) {
                    $this->apiKeyManager->revokeKey($data['// SECURITY: Sensitive information removed_id']);
                }
                break;
                
            case 'quarantine_file':
                if (isset($data['file_path'])) {
                    $this->quarantineFile($data['file_path']);
                }
                break;
                
            case 'cleanup_temp':
                $this->cleanupDirectory($this->config['temp_dir']);
                break;
                
            case 'cleanup_logs':
                $this->cleanupDirectory($this->config['log_dir'], '*.log', 7);
                break;
                
            case 'restart_service':
                $this->restartService();
                break;
                
            case 'retry_backup':
                if (isset($data['backup_manager'])) {
                    $data['backup_manager']->createBackup($data['backup_type'] ?? 'daily');
                }
                break;
                
            case 'temporary_block':
                $this->temporaryBlock($data['ip'] ?? '', $data['duration'] ?? 3600);
                break;
                
            case 'notify_admin':
                $this->notifyAdmin($type, $data);
                break;
                
            case 'log_incident':
                $this->logIncident($type, $data);
                break;
        }
    }

    /**
     * Block IP address
     */
    private function blockIp($ip) {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new Exception('Invalid IP address');
        }

        $blockedIps = $this->loadBlockedIps();
        if (!in_array($ip, $blockedIps)) {
            $blockedIps[] = $ip;
            $this->saveBlockedIps($blockedIps);
            
            // Add to .htaccess if using Apache
            $this->updateHtaccess($ip, 'deny');
        }
    }

    /**
     * Quarantine suspicious file
     */
    private function quarantineFile($filePath) {
        if (!file_exists($filePath)) {
            throw new Exception('File not found');
        }

        $quarantinePath = $this->config['quarantine_dir'] . '/' . basename($filePath) . '.' . time();
        if (!rename($filePath, $quarantinePath)) {
            throw new Exception('Failed to quarantine file');
        }

        // Create metadata file
        $metadata = [
            'original_path' => $filePath,
            'quarantine_time' => date('Y-m-d H:i:s'),
            'reason' => 'Malware detection'
        ];
        file_put_contents($quarantinePath . '.meta', json_encode($metadata));
    }

    /**
     * Cleanup directory
     */
    private function cleanupDirectory($dir, $pattern = '*', $daysOld = 30) {
        if (!is_dir($dir)) {
            throw new Exception('Directory not found');
        }

        $files = glob($dir . '/' . $pattern);
        $now = time();

        foreach ($files as $file) {
            if (is_file($file)) {
                if (filemtime($file) < ($now - ($daysOld * 86400))) {
                    unlink($file);
                }
            }
        }
    }

    /**
     * Restart service
     */
    private function restartService() {
        $service = $this->config['service_name'];
        $command = sprintf('net stop %s && net start %s', $service, $service);
        
        exec($command, $output, $returnCode);
        if ($returnCode !== 0) {
            throw new Exception('Failed to restart service');
        }
    }

    /**
     * Temporary IP block
     */
    private function temporaryBlock($ip, $duration) {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new Exception('Invalid IP address');
        }

        $this->setState("temp_block_{$ip}", time() + $duration);
        $this->updateHtaccess($ip, 'deny');

        // Schedule unblock
        $this->scheduleUnblock($ip, $duration);
    }

    /**
     * Schedule IP unblock
     */
    private function scheduleUnblock($ip, $duration) {
        // Store unblock time in database
        $stmt = $this->con->prepare(
            "INSERT INTO scheduled_tasks (task_type, task_data, execute_at) 
             VALUES ('unblock_ip', ?, DATE_ADD(NOW(), INTERVAL ? SECOND))"
        );
        $stmt->bind_param('si', $ip, $duration);
        $stmt->execute();
    }

    /**
     * Update .htaccess file
     */
    private function updateHtaccess($ip, $action) {
        $htaccess = __DIR__ . '/../../.htaccess';
        $content = file_exists($htaccess) ? file_get_contents($htaccess) : '';
        
        if ($action === 'deny') {
            if (strpos($content, "Deny from {$ip}") === false) {
                $content .= "\nDeny from {$ip}";
            }
        } else {
            $content = preg_replace("/\nDeny from {$ip}/", '', $content);
        }
        
        file_put_contents($htaccess, $content);
    }

    /**
     * Notify administrators
     */
    private function notifyAdmin($type, $data) {
        // Send email
        $this->emailManager->sendSecurityAlert($type, $data);
        
        // Send SMS for high-priority incidents
        if (in_array($type, ['malware_detected', 'sql_injection', 'brute_force'])) {
            $this->smsManager->sendAlert($type, "Security incident: {$type}", $data);
        }
    }

    /**
     * Log security incident
     */
    private function logIncident($type, $data) {
        $this->logger->alert("Security incident: {$type}", $data);
    }

    /**
     * Load blocked IPs
     */
    private function loadBlockedIps() {
        $file = $this->config['blocked_ips_file'];
        return file_exists($file) ? 
            json_decode(file_get_contents($file), true) : [];
    }

    /**
     * Save blocked IPs
     */
    private function saveBlockedIps($ips) {
        file_put_contents(
            $this->config['blocked_ips_file'],
            json_encode(array_values(array_unique($ips)))
        );
    }

    /**
     * Get state value
     */
    private function getState($key) {
        $stmt = $this->con->prepare(
            "SELECT value FROM system_state WHERE `key` = ?"
        );
        $stmt->bind_param('s', $key);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $row['value'];
        }
        return null;
    }

    /**
     * Set state value
     */
    private function setState($key, $value) {
        $stmt = $this->con->prepare(
            "INSERT INTO system_state (`key`, value) 
             VALUES (?, ?) 
             ON DUPLICATE KEY UPDATE value = ?"
        );
        $stmt->bind_param('sss', $key, $value, $value);
        $stmt->execute();
    }

    /**
     * Get response actions configuration
     */
    public function getResponseActions() {
        return self::RESPONSE_ACTIONS;
    }
}

// Create global auto-response system instance
$autoResponseSystem = new AutoResponse(); // SECURITY: Removed potentially dangerous code

