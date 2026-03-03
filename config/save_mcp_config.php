<?php
/**
 * APS Dream Home - MCP Configuration Save Handler
 * Backend script for saving and managing MCP server configurations
 */

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON data
$jsonData = file_get_contents('php://input');
$configData = json_decode($jsonData, true);

if (!$configData) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON data']);
    exit;
}

// Configuration file path
$configFile = __DIR__ . '/config/mcp_servers.json';
$backupFile = __DIR__ . '/config/mcp_servers_backup_' . date('Y-m-d_H-i-s') . '.json';

try {
    // Create backup of existing configuration
    if (file_exists($configFile)) {
        copy($configFile, $backupFile);
    }
    
    // Save new configuration
    $result = file_put_contents($configFile, json_encode($configData, JSON_PRETTY_PRINT));
    
    if ($result === false) {
        throw new Exception('Failed to save configuration file');
    }
    
    // Set proper permissions
    chmod($configFile, 0644);
    
    // Validate configuration
    $validation = validateMcpConfiguration($configData);
    
    echo json_encode([
        'success' => true,
        'message' => 'Configuration saved successfully',
        'validation' => $validation,
        'backup_created' => file_exists($backupFile),
        'config_file' => $configFile,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Validate MCP configuration
 */
function validateMcpConfiguration($config) {
    $errors = [];
    $warnings = [];
    
    if (!isset($config['mcpServers'])) {
        $errors[] = 'mcpServers section is required';
        return ['valid' => false, 'errors' => $errors, 'warnings' => $warnings];
    }
    
    foreach ($config['mcpServers'] as $serverKey => $serverConfig) {
        // Check required fields
        if (!isset($serverConfig['command'])) {
            $errors[] = "Server {$serverKey}: command is required";
        }
        
        if (!isset($serverConfig['args'])) {
            $errors[] = "Server {$serverKey}: args is required";
        }
        
        if (!isset($serverConfig['env'])) {
            $errors[] = "Server {$serverKey}: env is required";
        }
        
        // Validate environment variables
        if (isset($serverConfig['env'])) {
            foreach ($serverConfig['env'] as $envKey => $envValue) {
                if (empty($envValue) && $envKey !== 'POSTGRES_PASSWORD') {
                    $warnings[] = "Server {$serverKey}: {$envKey} is empty";
                }
                
                // Validate specific environment variables
                switch ($envKey) {
                    case 'POSTGRES_CONNECTION_STRING':
                        if (!str_contains($envValue, 'postgresql://')) {
                            $errors[] = "Server {$serverKey}: Invalid PostgreSQL connection string";
                        }
                        break;
                        
                    case 'SQLITE_DATABASE_PATH':
                        if (!file_exists(dirname($envValue))) {
                            $warnings[] = "Server {$serverKey}: SQLite database directory does not exist";
                        }
                        break;
                        
                    case 'SUPABASE_URL':
                        if (!filter_var($envValue, FILTER_VALIDATE_URL)) {
                            $errors[] = "Server {$serverKey}: Invalid Supabase URL";
                        }
                        break;
                        
                    case 'FIRECRAWL_BASE_URL':
                    case 'AI_IMAGE_TAGGING_BASE_URL':
                        if (!filter_var($envValue, FILTER_VALIDATE_URL)) {
                            $errors[] = "Server {$serverKey}: Invalid base URL";
                        }
                        break;
                        
                    case 'STRIPE_SECRET_KEY':
                        if (!str_starts_with($envValue, 'sk_')) {
                            $errors[] = "Server {$serverKey}: Invalid Stripe secret key format";
                        }
                        break;
                        
                    case 'STRIPE_PUBLISHABLE_KEY':
                        if (!str_starts_with($envValue, 'pk_')) {
                            $errors[] = "Server {$serverKey}: Invalid Stripe publishable key format";
                        }
                        break;
                }
            }
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'warnings' => $warnings,
        'servers_configured' => count($config['mcpServers'])
    ];
}
?>
