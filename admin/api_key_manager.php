<?php
/**
 * APS Dream Home - API Key Manager
 * 
 * This page allows administrators to create, view, and manage API keys
 * for third-party integrations with the APS Dream Home system.
 */

// Start session
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "apsdreamhomefinal";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Include API key functions
require_once '../api/auth/api_keys.php';

// Create tables if they don't exist
$createApiKeyTable = "CREATE TABLE IF NOT EXISTS " . API_KEY_TABLE . " (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    api_key VARCHAR(64) NOT NULL,
    name VARCHAR(255) NOT NULL,
    permissions TEXT,
    rate_limit INT DEFAULT " . DEFAULT_RATE_LIMIT . ",
    status ENUM('active', 'revoked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL,
    last_used_at TIMESTAMP NULL,
    UNIQUE KEY (api_key),
    INDEX (user_id)
)";

$createRequestLogTable = "CREATE TABLE IF NOT EXISTS " . API_REQUEST_LOG_TABLE . " (
    id INT AUTO_INCREMENT PRIMARY KEY,
    api_key_id INT NOT NULL,
    endpoint VARCHAR(255) NOT NULL,
    request_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    INDEX (api_key_id),
    INDEX (request_time)
)";

$conn->query($createApiKeyTable);
$conn->query($createRequestLogTable);

// Initialize variables
$message = '';
$error = '';
$apiKeys = [];
$availableEndpoints = [];

// Scan API directory to get available endpoints
function scanApiDirectory($dir, $prefix = '') {
    global $availableEndpoints;
    
    if (!is_dir($dir)) {
        return;
    }
    
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $path = $dir . '/' . $file;
        $relativePath = $prefix . '/' . $file;
        
        if (is_dir($path)) {
            // Add directory wildcard endpoint
            $availableEndpoints[] = [
                'path' => $relativePath . '/*',
                'description' => 'All endpoints in ' . $relativePath
            ];
            
            // Recursively scan subdirectory
            scanApiDirectory($path, $relativePath);
        } else if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            // Add specific endpoint
            $endpoint = $relativePath;
            $description = '';
            
            // Try to extract description from file
            $content = file_get_contents($path);
            if (preg_match('/@description\s+(.*?)(\n\s*\*\s*@|\n\s*\*\/)/s', $content, $matches)) {
                $description = trim(preg_replace('/\n\s*\*\s*/', ' ', $matches[1]));
            }
            
            $availableEndpoints[] = [
                'path' => $endpoint,
                'description' => $description
            ];
        }
    }
}

// Scan API directory
scanApiDirectory(dirname(__DIR__) . '/api');

// Add wildcard for all endpoints
array_unshift($availableEndpoints, [
    'path' => '*',
    'description' => 'All API endpoints (full access)'
]);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create new API key
    if (isset($_POST['create_key'])) {
        $name = trim($_POST['key_name']);
        $permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];
        $rateLimit = isset($_POST['rate_limit']) ? (int)$_POST['rate_limit'] : DEFAULT_RATE_LIMIT;
        
        if (empty($name)) {
            $error = "API key name is required";
        } else {
            $userId = $_SESSION['user_id'];
            $apiKey = generateApiKey($userId, $name, $permissions, $rateLimit);
            
            if ($apiKey) {
                $message = "API key created successfully: " . $apiKey;
            } else {
                $error = "Failed to create API key";
            }
        }
    }
    
    // Revoke API key
    else if (isset($_POST['revoke_key'])) {
        $apiKey = $_POST['api_key'];
        $userId = $_SESSION['user_id'];
        
        if (revokeApiKey($apiKey, $userId)) {
            $message = "API key revoked successfully";
        } else {
            $error = "Failed to revoke API key";
        }
    }
}

// Get user's API keys
$apiKeys = listApiKeys($_SESSION['user_id']);

// Close connection
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>APS Dream Home - API Key Manager</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .api-key-container {
            margin-bottom: 30px;
        }
        .api-key-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .api-key-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .api-key-name {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }
        .api-key-value {
            font-family: monospace;
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
            word-break: break-all;
        }
        .api-key-details {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .api-key-detail {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
        }
        .api-key-detail h4 {
            margin-top: 0;
            margin-bottom: 5px;
            color: #6c757d;
            font-size: 14px;
        }
        .api-key-detail p {
            margin: 0;
            font-weight: bold;
        }
        .permissions-container {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 15px;
        }
        .permission-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }
        .permission-item label {
            margin-left: 8px;
            flex: 1;
        }
        .permission-description {
            color: #6c757d;
            font-size: 12px;
            margin-left: 25px;
        }
        .status-active {
            color: #28a745;
        }
        .status-revoked {
            color: #dc3545;
        }
        .copy-btn {
            background: none;
            border: none;
            color: #007bff;
            cursor: pointer;
        }
        .copy-btn:hover {
            color: #0056b3;
        }
        .form-row {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="content">
        <div class="container">
            <h1>API Key Manager</h1>
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h2>Your API Keys</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($apiKeys)): ?>
                        <p>You haven't created any API keys yet.</p>
                    <?php else: ?>
                        <div class="api-key-container">
                            <?php foreach ($apiKeys as $key): ?>
                                <div class="api-key-card">
                                    <div class="api-key-header">
                                        <h3 class="api-key-name"><?php echo htmlspecialchars($key['name']); ?></h3>
                                        <span class="status-<?php echo $key['status']; ?>"><?php echo ucfirst($key['status']); ?></span>
                                    </div>
                                    
                                    <?php if ($key['status'] === 'active'): ?>
                                        <div class="api-key-value">
                                            <?php echo htmlspecialchars($key['api_key']); ?>
                                            <button class="copy-btn" onclick="copyToClipboard('<?php echo $key['api_key']; ?>')" title="Copy to clipboard">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="api-key-details">
                                        <div class="api-key-detail">
                                            <h4>Created</h4>
                                            <p><?php echo date('M j, Y', strtotime($key['created_at'])); ?></p>
                                        </div>
                                        
                                        <div class="api-key-detail">
                                            <h4>Last Used</h4>
                                            <p><?php echo $key['last_used_at'] ? date('M j, Y', strtotime($key['last_used_at'])) : 'Never'; ?></p>
                                        </div>
                                        
                                        <div class="api-key-detail">
                                            <h4>Rate Limit</h4>
                                            <p><?php echo $key['rate_limit']; ?> requests/hour</p>
                                        </div>
                                        
                                        <div class="api-key-detail">
                                            <h4>Usage</h4>
                                            <p><?php echo $key['usage_count']; ?> requests</p>
                                        </div>
                                    </div>
                                    
                                    <div style="margin-top: 15px;">
                                        <h4>Permissions</h4>
                                        <?php if (empty($key['permissions'])): ?>
                                            <p>All endpoints (full access)</p>
                                        <?php else: ?>
                                            <ul>
                                                <?php foreach ($key['permissions'] as $permission): ?>
                                                    <li><?php echo htmlspecialchars($permission); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($key['status'] === 'active'): ?>
                                        <div style="margin-top: 15px;">
                                            <form method="post" onsubmit="return confirm('Are you sure you want to revoke this API key? This action cannot be undone.');">
                                                <input type="hidden" name="api_key" value="<?php echo $key['api_key']; ?>">
                                                <button type="submit" name="revoke_key" class="btn btn-danger">Revoke Key</button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card" style="margin-top: 30px;">
                <div class="card-header">
                    <h2>Create New API Key</h2>
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="form-row">
                            <label for="key_name">API Key Name:</label>
                            <input type="text" id="key_name" name="key_name" required placeholder="e.g., Mobile App Integration">
                        </div>
                        
                        <div class="form-row">
                            <label for="rate_limit">Rate Limit (requests per hour):</label>
                            <input type="number" id="rate_limit" name="rate_limit" value="<?php echo DEFAULT_RATE_LIMIT; ?>" min="1" max="1000">
                        </div>
                        
                        <div class="form-row">
                            <label>Permissions:</label>
                            <p class="help-text">Select which API endpoints this key can access. Leave all unchecked for full access.</p>
                            
                            <div class="permissions-container">
                                <?php foreach ($availableEndpoints as $endpoint): ?>
                                    <div class="permission-item">
                                        <input type="checkbox" id="perm_<?php echo md5($endpoint['path']); ?>" name="permissions[]" value="<?php echo htmlspecialchars($endpoint['path']); ?>">
                                        <label for="perm_<?php echo md5($endpoint['path']); ?>"><?php echo htmlspecialchars($endpoint['path']); ?></label>
                                    </div>
                                    <?php if (!empty($endpoint['description'])): ?>
                                        <div class="permission-description"><?php echo htmlspecialchars($endpoint['description']); ?></div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <button type="submit" name="create_key" class="btn btn-primary">Create API Key</button>
                    </form>
                </div>
            </div>
            
            <div class="card" style="margin-top: 30px;">
                <div class="card-header">
                    <h2>API Integration Guide</h2>
                </div>
                <div class="card-body">
                    <h3>How to Use API Keys</h3>
                    <p>To authenticate API requests, include your API key in the request headers:</p>
                    
                    <pre><code>X-API-Key: your_api_key_here</code></pre>
                    
                    <h3>Rate Limiting</h3>
                    <p>API requests are rate-limited based on the settings for each key. If you exceed your rate limit, you'll receive a 429 Too Many Requests response.</p>
                    
                    <h3>Response Headers</h3>
                    <p>Each API response includes the following headers:</p>
                    <ul>
                        <li><code>X-Rate-Limit-Limit</code>: Your hourly request limit</li>
                        <li><code>X-Rate-Limit-Remaining</code>: Number of requests remaining in the current window</li>
                        <li><code>X-Rate-Limit-Reset</code>: Time when the rate limit will reset (Unix timestamp)</li>
                    </ul>
                    
                    <h3>Available Endpoints</h3>
                    <p>For a complete list of available API endpoints and documentation, visit the <a href="../database/api_documentation.php">API Documentation</a>.</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function copyToClipboard(text) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            alert('API key copied to clipboard');
        }
    </script>
</body>
</html>
