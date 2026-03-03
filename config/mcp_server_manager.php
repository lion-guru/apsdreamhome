<?php
/**
 * APS Dream Home - MCP Server Manager
 * Auto-start and manage all configured MCP servers with database integration
 */

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Include database integration
require_once __DIR__ . '/mcp_database_integration.php';

$action = $_POST['action'] ?? '';

try {
    // Initialize database integration
    $mcpDb = new MCPDatabaseIntegration();
    
    switch ($action) {
        case 'start_all':
            $result = startAllMCPServers($mcpDb);
            break;
            
        case 'stop_all':
            $result = stopAllMCPServers($mcpDb);
            break;
            
        case 'restart_all':
            $result = restartAllMCPServers($mcpDb);
            break;
            
        case 'get_status':
            $result = getMCPServerStatus($mcpDb);
            break;
            
        case 'start_server':
            $serverName = $_POST['server_name'] ?? '';
            $result = startMCPServer($serverName, $mcpDb);
            break;
            
        case 'stop_server':
            $serverName = $_POST['server_name'] ?? '';
            $result = stopMCPServer($serverName, $mcpDb);
            break;
            
        default:
            throw new Exception('Invalid action: ' . $action);
    }
    
    echo json_encode([
        'success' => true,
        'action' => $action,
        'result' => $result,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Start all MCP servers
 */
function startAllMCPServers($mcpDb) {
    $configFile = __DIR__ . '/mcp_servers.json';
    
    if (!file_exists($configFile)) {
        throw new Exception('MCP configuration file not found');
    }
    
    $config = json_decode(file_get_contents($configFile), true);
    $results = [];
    
    if (!isset($config['mcpServers'])) {
        throw new Exception('No MCP servers configured');
    }
    
    foreach ($config['mcpServers'] as $serverKey => $serverConfig) {
        $result = startMCPServer($serverKey, $mcpDb);
        $results[$serverKey] = $result;
        
        // Register server in database
        $serverType = getServerType($serverKey);
        $mcpDb->registerServer($serverKey, $serverType, $serverConfig);
        
        // Small delay between server starts
        usleep(100000); // 0.1 second
    }
    
    return $results;
}

/**
 * Get server type based on server key
 */
function getServerType($serverKey) {
    $types = [
        'postgresql' => 'database',
        'sqlite' => 'database',
        'supabase' => 'database',
        'firecrawl' => 'search',
        'brave-search' => 'search',
        'brightdata' => 'data',
        'google-maps' => 'mapping',
        'stripe' => 'payment',
        'slack' => 'communication',
        'whatsapp' => 'communication',
        'ai-image-tagging' => 'ai',
        'browser-stealth' => 'automation'
    ];
    
    return $types[$serverKey] ?? 'other';
}

/**
 * Stop all MCP servers
 */
function stopAllMCPServers($mcpDb) {
    $results = [];
    
    // Kill all node processes related to MCP servers
    $command = 'tasklist /FI "IMAGENAME eq node.exe" /FO "WINDOWTITLE eq *" | findstr /i "mcp-"';
    exec($command, $output, $returnCode);
    
    if ($returnCode === 0) {
        $results['stopped'] = 'All MCP server processes stopped';
        
        // Update database status for all servers
        $servers = $mcpDb->getServerStatus();
        foreach ($servers as $server) {
            $mcpDb->updateServerStatus($server['server_name'], 'inactive');
        }
    } else {
        $results['stopped'] = 'No MCP server processes found running';
    }
    
    return $results;
}

/**
 * Restart all MCP servers
 */
function restartAllMCPServers($mcpDb) {
    // Stop first
    $stopResult = stopAllMCPServers($mcpDb);
    
    // Wait a moment
    sleep(2);
    
    // Start again
    $startResult = startAllMCPServers($mcpDb);
    
    return [
        'stop_result' => $stopResult,
        'start_result' => $startResult
    ];
}

/**
 * Get MCP server status
 */
function getMCPServerStatus() {
    $configFile = __DIR__ . '/mcp_servers.json';
    
    if (!file_exists($configFile)) {
        return ['error' => 'Configuration file not found'];
    }
    
    $config = json_decode(file_get_contents($configFile), true);
    $status = [];
    
    if (!isset($config['mcpServers'])) {
        return ['error' => 'No servers configured'];
    }
    
    foreach ($config['mcpServers'] as $serverKey => $serverConfig) {
        $status[$serverKey] = [
            'configured' => true,
            'running' => isMCPServerRunning($serverKey),
            'last_check' => date('Y-m-d H:i:s')
        ];
    }
    
    return $status;
}

/**
 * Start specific MCP server
 */
function startMCPServer($serverKey) {
    $configFile = __DIR__ . '/mcp_servers.json';
    $config = json_decode(file_get_contents($configFile), true);
    
    if (!isset($config['mcpServers'][$serverKey])) {
        throw new Exception('Server not configured: ' . $serverKey);
    }
    
    $serverConfig = $config['mcpServers'][$serverKey];
    
    // Check if already running
    if (isMCPServerRunning($serverKey)) {
        return [
            'status' => 'already_running',
            'message' => 'Server is already running'
        ];
    }
    
    // Prepare environment variables
    $envVars = [];
    if (isset($serverConfig['env'])) {
        foreach ($serverConfig['env'] as $key => $value) {
            $envVars[] = $key . '=' . escapeshellarg($value);
        }
    }
    
    // Build command
    $command = 'start ""MCP ' . $serverKey . ' Server"" /B cmd /C';
    
    if (!empty($envVars)) {
        $command .= ' /C "' . implode('" "', $envVars) . '"';
    }
    
    $command .= ' node ' . escapeshellarg($serverConfig['args'][0]) . ' 2>&1';
    
    // Start the server in background
    $fullCommand = 'powershell -Command "& {' . $command . '}"';
    
    $logFile = __DIR__ . '/../logs/mcp_' . $serverKey . '.log';
    $commandWithLogging = $fullCommand . ' > "' . $logFile . '" 2>&1';
    
    exec($commandWithLogging, $output, $returnCode);
    
    if ($returnCode === 0) {
        return [
            'status' => 'started',
            'message' => 'Server started successfully',
            'pid' => getServerPID($serverKey),
            'log_file' => $logFile
        ];
    } else {
        return [
            'status' => 'error',
            'message' => 'Failed to start server',
            'error' => implode("\n", $output)
        ];
    }
}

/**
 * Stop specific MCP server
 */
function stopMCPServer($serverKey) {
    $pid = getServerPID($serverKey);
    
    if ($pid) {
        // Kill the process
        exec('taskkill /F /PID ' . $pid . ' 2>&1', $output, $returnCode);
        
        if ($returnCode === 0) {
            return [
                'status' => 'stopped',
                'message' => 'Server stopped successfully'
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Failed to stop server',
                'error' => implode("\n", $output)
            ];
        }
    } else {
        return [
            'status' => 'not_running',
            'message' => 'Server was not running'
        ];
    }
}

/**
 * Check if MCP server is running
 */
function isMCPServerRunning($serverKey) {
    $pid = getServerPID($serverKey);
    
    if ($pid) {
        // Check if process is still running
        exec('tasklist /FI "PID eq ' . $pid . '" 2>NUL', $output, $returnCode);
        return $returnCode === 0;
    }
    
    return false;
}

/**
 * Get server PID
 */
function getServerPID($serverKey) {
    // Look for node processes with server-specific arguments
    $command = 'wmic process where "name=\'node.exe\' and commandline like \'%' . $serverKey . '%\'" get ProcessId /format:value 2>NUL';
    
    exec($command, $output, $returnCode);
    
    if ($returnCode === 0 && !empty($output[1])) {
        return trim($output[1]);
    }
    
    return null;
}
?>
