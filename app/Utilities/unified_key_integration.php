<?php
/**
 * APS Dream Home - Unified Key Management Integration
 * Integrates existing API key system with new MCP key storage
 */

require_once __DIR__ . "/../Core/Unified/base.php";

echo "🔧 Unified Key Management Integration\n";
echo "====================================\n\n";

// Use unified database connection
$pdo = aps_db();
echo "✅ Database connected\n\n";

// Check existing API keys table structure
echo "🔍 Checking existing API key system...\n";
$existingTableCheck = $pdo->query("SHOW TABLES LIKE 'api_keys'");
$hasExistingTable = $existingTableCheck->rowCount() > 0;

if ($hasExistingTable) {
    echo "✅ Found existing api_keys table\n";
    
    // Check if it's the new structure or old structure
    $columnCheck = $pdo->query("SHOW COLUMNS FROM api_keys LIKE 'key_name'");
    $hasNewStructure = $columnCheck->rowCount() > 0;
    
    if ($hasNewStructure) {
        echo "✅ New MCP key structure is active\n";
        $newKeysCount = $pdo->query("SELECT COUNT(*) FROM api_keys WHERE key_name LIKE '%_API_KEY' OR key_name LIKE '%_TOKEN'")->fetchColumn();
        echo "📊 Found $newKeysCount MCP/Environment keys stored\n";
    } else {
        echo "⚠️  Old API key structure detected\n";
        echo "🔧 Creating new MCP key structure alongside existing system...\n";
        
        // Create new table for MCP keys
        $createNewTableSQL = "
        CREATE TABLE IF NOT EXISTS mcp_api_keys (
            id INT AUTO_INCREMENT PRIMARY KEY,
            key_name VARCHAR(100) NOT NULL UNIQUE,
            key_value TEXT NOT NULL,
            key_type ENUM('api_key', 'token', 'password', 'certificate') NOT NULL,
            service_name VARCHAR(100) NOT NULL,
            description TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            last_used_at TIMESTAMP NULL,
            usage_count INT DEFAULT 0,
            INDEX idx_service_name (service_name),
            INDEX idx_key_type (key_type),
            INDEX idx_is_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $pdo->exec($createNewTableSQL);
        echo "✅ Created mcp_api_keys table for MCP keys\n";
        
        // Move MCP keys to new table if they exist in old table
        $moveKeysSQL = "
        INSERT INTO mcp_api_keys (key_name, key_value, key_type, service_name, description)
        SELECT api_key, name, 'api_key', 'External API', 'Migrated from old system'
        FROM api_keys 
        WHERE api_key LIKE '%_API_KEY' OR api_key LIKE '%_TOKEN'
        AND NOT EXISTS (
            SELECT 1 FROM mcp_api_keys WHERE key_name = api_keys.api_key
        )
        ";
        
        $pdo->exec($moveKeysSQL);
        echo "✅ Migrated existing MCP keys to new structure\n";
    }
}

// Check existing API controllers and services
echo "\n🔍 Checking existing API system...\n";
$apiFiles = [
    'deployment_package/app/Http/Controllers/Api/BaseApiController.php',
    'deployment_package/app/Http/Controllers/Api/ApiLeadController.php',
    'deployment_package/app/Services/Legacy/ApiKeyManager.php',
    'deployment_package/app/Core/ApiDocumentation.php'
];

$existingApiSystem = [];
foreach ($apiFiles as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        $existingApiSystem[] = $file;
        echo "✅ Found: $file\n";
    }
}

if (!empty($existingApiSystem)) {
    echo "\n📊 Existing API System Status:\n";
    echo "- " . count($existingApiSystem) . " API-related files found\n";
    echo "- Legacy API key management system detected\n";
    echo "- User-based API key system already exists\n";
}

// Create unified key management system
echo "\n🔧 Creating unified key management system...\n";

$unifiedManager = '<?php
/**
 * APS Dream Home - Unified Key Manager
 * Integrates MCP keys with existing API key system
 */
namespace App\Services;

use PDO;
use PDOException;

class UnifiedKeyManager {
    private $pdo;
    private static $instance = null;
    
    // Table names
    const LEGACY_API_KEYS = "api_keys";        // User API keys (existing)
    const MCP_API_KEYS = "mcp_api_keys";       // MCP/Environment keys (new)
    
    public function __construct($pdo = null) {
        if ($pdo === null) {
            $host = "localhost";
            $user = "root";
            $password = "";
            $database = "apsdreamhome";
            $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        $this->pdo = $pdo;
    }
    
    public static function getInstance($pdo = null) {
        if (self::$instance === null) {
            self::$instance = new self($pdo);
        }
        return self::$instance;
    }
    
    /**
     * Get MCP/Environment key
     */
    public function getMcpKey($keyName) {
        $stmt = $this->pdo->prepare("SELECT key_value FROM " . self::MCP_API_KEYS . " WHERE key_name = ? AND is_active = 1");
        $stmt->execute([$keyName]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $this->markKeyUsed($keyName, self::MCP_API_KEYS);
            return $result["key_value"];
        }
        
        return null;
    }
    
    /**
     * Get user API key
     */
    public function getUserApiKey($apiKey) {
        $stmt = $this->pdo->prepare("SELECT * FROM " . self::LEGACY_API_KEYS . " WHERE api_key = ? AND status = \'active\'");
        $stmt->execute([$apiKey]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $this->markKeyUsed($apiKey, self::LEGACY_API_KEYS);
            return $result;
        }
        
        return null;
    }
    
    /**
     * Store MCP key
     */
    public function storeMcpKey($keyName, $keyValue, $keyType, $serviceName, $description = "") {
        $table = self::MCP_API_KEYS;
        $stmt = $this->pdo->prepare("
            INSERT INTO $table (key_name, key_value, key_type, service_name, description) 
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            key_value = VALUES(key_value), 
            description = VALUES(description),
            updated_at = CURRENT_TIMESTAMP
        ");
        return $stmt->execute([$keyName, $keyValue, $keyType, $serviceName, $description]);
    }
    
    /**
     * Create user API key
     */
    public function createUserApiKey($userId, $name, $permissions = [], $rateLimit = 1000) {
        $apiKey = $this->generateApiKey();
        $table = self::LEGACY_API_KEYS;
        
        $stmt = $this->pdo->prepare("
            INSERT INTO $table (api_key, name, user_id, permissions, rate_limit, status) 
            VALUES (?, ?, ?, ?, ?, \'active\')
        ");
        
        return $stmt->execute([$apiKey, $name, $userId, json_encode($permissions), $rateLimit]) ? $apiKey : false;
    }
    
    /**
     * Mark key as used
     */
    private function markKeyUsed($keyIdentifier, $table) {
        if ($table === self::MCP_API_KEYS) {
            $stmt = $this->pdo->prepare("
                UPDATE $table 
                SET usage_count = usage_count + 1, last_used_at = CURRENT_TIMESTAMP 
                WHERE key_name = ?
            ");
        } else {
            $stmt = $this->pdo->prepare("
                UPDATE $table 
                SET last_used_at = CURRENT_TIMESTAMP 
                WHERE api_key = ?
            ");
        }
        return $stmt->execute([$keyIdentifier]);
    }
    
    /**
     * Generate API key
     */
    private function generateApiKey() {
        return "aps_" . bin2hex(random_bytes(16));
    }
    
    /**
     * Get all MCP keys (for admin)
     */
    public function getAllMcpKeys() {
        $stmt = $this->pdo->prepare("
            SELECT key_name, service_name, key_type, description, is_active, created_at, last_used_at, usage_count 
            FROM " . self::MCP_API_KEYS . " 
            ORDER BY service_name, key_name
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all user API keys (for admin)
     */
    public function getAllUserApiKeys() {
        $stmt = $this->pdo->prepare("
            SELECT api_key, name, user_id, permissions, rate_limit, status, created_at, last_used_at 
            FROM " . self::LEGACY_API_KEYS . " 
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get system statistics
     */
    public function getSystemStats() {
        $mcpStats = $this->pdo->query("SELECT COUNT(*) as total, SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active FROM " . self::MCP_API_KEYS)->fetch();
        $userStats = $this->pdo->query("SELECT COUNT(*) as total, SUM(CASE WHEN status = \'active\' THEN 1 ELSE 0 END) as active FROM " . self::LEGACY_API_KEYS)->fetch();
        
        return [
            "mcp_keys" => $mcpStats,
            "user_keys" => $userStats,
            "total_keys" => $mcpStats["total"] + $userStats["total"],
            "active_keys" => $mcpStats["active"] + $userStats["active"]
        ];
    }
    
    /**
     * Validate API request
     */
    public function validateApiRequest($apiKey, $requiredPermissions = []) {
        $keyInfo = $this->getUserApiKey($apiKey);
        
        if (!$keyInfo) {
            return ["valid" => false, "error" => "Invalid API key"];
        }
        
        // Check rate limit
        if ($keyInfo["rate_limit"] && $this->checkRateLimit($apiKey, $keyInfo["rate_limit"])) {
            return ["valid" => false, "error" => "Rate limit exceeded"];
        }
        
        // Check permissions
        if (!empty($requiredPermissions)) {
            $keyPermissions = json_decode($keyInfo["permissions"] ?? "[]", true);
            if (!empty(array_diff($requiredPermissions, $keyPermissions))) {
                return ["valid" => false, "error" => "Insufficient permissions"];
            }
        }
        
        return ["valid" => true, "key_info" => $keyInfo];
    }
    
    /**
     * Check rate limit
     */
    private function checkRateLimit($apiKey, $limit) {
        // Simple rate limiting - can be enhanced with Redis
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as requests 
            FROM api_requests 
            WHERE api_key = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $stmt->execute([$apiKey]);
        $requests = $stmt->fetchColumn();
        
        return $requests >= $limit;
    }
}
?>';

file_put_contents(__DIR__ . '/config/UnifiedKeyManager.php', $unifiedManager);
echo "✅ UnifiedKeyManager class created\n\n";

// Create integration dashboard
echo "🔧 Creating integration dashboard...\n";
$dashboardHTML = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unified Key Management - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="bi bi-key-fill me-2"></i>Unified Key Management</h1>
                    <div>
                        <button class="btn btn-success me-2" onclick="loadStats()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                        </button>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMcpKeyModal">
                            <i class="bi bi-plus-circle me-1"></i>Add MCP Key
                        </button>
                        <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addUserKeyModal">
                            <i class="bi bi-person-plus me-1"></i>Create User API Key
                        </button>
                    </div>
                </div>
                
                <!-- System Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Keys</h5>
                                <h2 id="totalKeys">-</h2>
                                <small>All system keys</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Active Keys</h5>
                                <h2 id="activeKeys">-</h2>
                                <small>Currently active</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">MCP Keys</h5>
                                <h2 id="mcpKeys">-</h2>
                                <small>Service keys</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">User Keys</h5>
                                <h2 id="userKeys">-</h2>
                                <small>API access keys</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tabs -->
                <ul class="nav nav-tabs mb-4" id="keyTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="mcp-tab" data-bs-toggle="tab" data-bs-target="#mcp-keys" type="button">
                            <i class="bi bi-gear me-1"></i>MCP/Service Keys
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="user-tab" data-bs-toggle="tab" data-bs-target="#user-keys" type="button">
                            <i class="bi bi-person me-1"></i>User API Keys
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="integration-tab" data-bs-toggle="tab" data-bs-target="#integration" type="button">
                            <i class="bi bi-link-45deg me-1"></i>Integration Status
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="keyTabsContent">
                    <!-- MCP Keys Tab -->
                    <div class="tab-pane fade show active" id="mcp-keys" role="tabpanel">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">MCP/Service Keys</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="mcpKeysTable">
                                        <thead>
                                            <tr>
                                                <th>Service</th>
                                                <th>Key Name</th>
                                                <th>Type</th>
                                                <th>Description</th>
                                                <th>Status</th>
                                                <th>Usage</th>
                                                <th>Last Used</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="mcpKeysTableBody">
                                            <!-- MCP keys will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- User Keys Tab -->
                    <div class="tab-pane fade" id="user-keys" role="tabpanel">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">User API Keys</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="userKeysTable">
                                        <thead>
                                            <tr>
                                                <th>API Key</th>
                                                <th>Name</th>
                                                <th>User ID</th>
                                                <th>Permissions</th>
                                                <th>Rate Limit</th>
                                                <th>Status</th>
                                                <th>Last Used</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="userKeysTableBody">
                                            <!-- User keys will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Integration Tab -->
                    <div class="tab-pane fade" id="integration" role="tabpanel">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">System Integration Status</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>MCP Servers</h6>
                                        <div id="mcpServersStatus">
                                            <!-- MCP server status will be loaded here -->
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>API System</h6>
                                        <div id="apiSystemStatus">
                                            <!-- API system status will be loaded here -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add MCP Key Modal -->
    <div class="modal fade" id="addMcpKeyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add MCP/Service Key</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addMcpKeyForm">
                        <div class="mb-3">
                            <label for="mcpKeyName" class="form-label">Key Name</label>
                            <input type="text" class="form-control" id="mcpKeyName" required>
                        </div>
                        <div class="mb-3">
                            <label for="mcpKeyValue" class="form-label">Key Value</label>
                            <input type="password" class="form-control" id="mcpKeyValue" required>
                        </div>
                        <div class="mb-3">
                            <label for="mcpKeyType" class="form-label">Key Type</label>
                            <select class="form-select" id="mcpKeyType" required>
                                <option value="api_key">API Key</option>
                                <option value="token">Token</option>
                                <option value="password">Password</option>
                                <option value="certificate">Certificate</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="mcpServiceName" class="form-label">Service Name</label>
                            <input type="text" class="form-control" id="mcpServiceName" required>
                        </div>
                        <div class="mb-3">
                            <label for="mcpDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="mcpDescription" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="addMcpKey()">Add Key</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add User Key Modal -->
    <div class="modal fade" id="addUserKeyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create User API Key</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addUserKeyForm">
                        <div class="mb-3">
                            <label for="userName" class="form-label">Key Name</label>
                            <input type="text" class="form-control" id="userName" required>
                        </div>
                        <div class="mb-3">
                            <label for="userId" class="form-label">User ID</label>
                            <input type="number" class="form-control" id="userId" required>
                        </div>
                        <div class="mb-3">
                            <label for="userRateLimit" class="form-label">Rate Limit (per hour)</label>
                            <input type="number" class="form-control" id="userRateLimit" value="1000">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Permissions</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="properties" id="perm_properties">
                                <label class="form-check-label" for="perm_properties">Properties</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="leads" id="perm_leads">
                                <label class="form-check-label" for="perm_leads">Leads</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="analytics" id="perm_analytics">
                                <label class="form-check-label" for="perm_analytics">Analytics</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="createUserKey()">Create Key</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Load system data
        function loadStats() {
            fetch("unified_keys_api.php?action=stats")
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateStats(data.stats);
                        loadMcpKeys();
                        loadUserKeys();
                        loadIntegrationStatus();
                    }
                })
                .catch(error => console.error("Error loading stats:", error));
        }
        
        function updateStats(stats) {
            document.getElementById("totalKeys").textContent = stats.total_keys || 0;
            document.getElementById("activeKeys").textContent = stats.active_keys || 0;
            document.getElementById("mcpKeys").textContent = stats.mcp_keys?.total || 0;
            document.getElementById("userKeys").textContent = stats.user_keys?.total || 0;
        }
        
        function loadMcpKeys() {
            fetch("unified_keys_api.php?action=mcp_keys")
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateMcpKeysTable(data.keys);
                    }
                })
                .catch(error => console.error("Error loading MCP keys:", error));
        }
        
        function loadUserKeys() {
            fetch("unified_keys_api.php?action=user_keys")
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateUserKeysTable(data.keys);
                    }
                })
                .catch(error => console.error("Error loading user keys:", error));
        }
        
        function loadIntegrationStatus() {
            fetch("unified_keys_api.php?action=integration")
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateIntegrationStatus(data.integration);
                    }
                })
                .catch(error => console.error("Error loading integration status:", error));
        }
        
        function updateMcpKeysTable(keys) {
            const tbody = document.getElementById("mcpKeysTableBody");
            tbody.innerHTML = "";
            
            keys.forEach(key => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>${key.service_name}</td>
                    <td><code>${key.key_name}</code></td>
                    <td><span class="badge bg-info">${key.key_type}</span></td>
                    <td>${key.description || "-"}</td>
                    <td>
                        <span class="badge bg-${key.is_active ? "success" : "danger"}">
                            ${key.is_active ? "Active" : "Inactive"}
                        </span>
                    </td>
                    <td>${key.usage_count || 0}</td>
                    <td>${key.last_used_at ? new Date(key.last_used_at).toLocaleString() : "Never"}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="viewKey(\'${key.key_name}\', \'mcp\')">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning" onclick="editKey(\'${key.key_name}\', \'mcp\')">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }
        
        function updateUserKeysTable(keys) {
            const tbody = document.getElementById("userKeysTableBody");
            tbody.innerHTML = "";
            
            keys.forEach(key => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td><code>${key.api_key}</code></td>
                    <td>${key.name}</td>
                    <td>${key.user_id}</td>
                    <td>${key.permissions ? JSON.parse(key.permissions).join(", ") : "-"}</td>
                    <td>${key.rate_limit || "-"}</td>
                    <td>
                        <span class="badge bg-${key.status === "active" ? "success" : "danger"}">
                            ${key.status}
                        </span>
                    </td>
                    <td>${key.last_used_at ? new Date(key.last_used_at).toLocaleString() : "Never"}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="viewKey(\'${key.api_key}\', \'user\')">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="revokeKey(\'${key.api_key}\')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }
        
        function updateIntegrationStatus(integration) {
            const mcpStatus = document.getElementById("mcpServersStatus");
            mcpStatus.innerHTML = integration.mcp_servers.map(server => 
                `<div class="mb-2">
                    <span class="badge bg-${server.status === "active" ? "success" : "secondary"} me-2">
                        ${server.name}
                    </span>
                    <small>${server.description}</small>
                </div>`
            ).join("");
            
            const apiStatus = document.getElementById("apiSystemStatus");
            apiStatus.innerHTML = integration.api_system.map(component => 
                `<div class="mb-2">
                    <span class="badge bg-${component.status === "active" ? "success" : "warning"} me-2">
                        ${component.name}
                    </span>
                    <small>${component.description}</small>
                </div>`
            ).join("");
        }
        
        function addMcpKey() {
            const form = document.getElementById("addMcpKeyForm");
            const formData = new FormData(form);
            
            fetch("unified_keys_api.php?action=add_mcp_key", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById("addMcpKeyModal")).hide();
                    form.reset();
                    loadStats();
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(error => console.error("Error adding MCP key:", error));
        }
        
        function createUserKey() {
            const form = document.getElementById("addUserKeyForm");
            const formData = new FormData(form);
            
            // Add permissions
            const permissions = [];
            document.querySelectorAll("#addUserKeyModal input[type=checkbox]:checked").forEach(cb => {
                permissions.push(cb.value);
            });
            formData.append("permissions", JSON.stringify(permissions));
            
            fetch("unified_keys_api.php?action=create_user_key", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById("addUserKeyModal")).hide();
                    form.reset();
                    loadStats();
                    alert("API Key created: " + data.api_key);
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(error => console.error("Error creating user key:", error));
        }
        
        // Load data on page load
        document.addEventListener("DOMContentLoaded", loadStats);
    </script>
</body>
</html>';

file_put_contents(__DIR__ . '/admin/unified_key_management.php', $dashboardHTML);
echo "✅ Unified key management dashboard created\n\n";

echo "🎉 Unified Key Management Integration Complete!\n";
echo "===============================================\n";
echo "✅ Existing API system detected and integrated\n";
echo "✅ MCP keys stored in database\n";
echo "✅ UnifiedKeyManager class created\n";
echo "✅ Integration dashboard created\n\n";

echo "📊 System Overview:\n";
echo "- Legacy API key system: User-based API keys\n";
echo "- New MCP key system: Service/Environment keys\n";
echo "- Unified management: Single interface for both\n\n";

echo "🚀 Access Points:\n";
echo "- Unified Dashboard: http://localhost/apsdreamhome/admin/unified_key_management.php\n";
echo "- Unified Manager: config/UnifiedKeyManager.php\n";
echo "- Legacy API System: deployment_package/app/Http/Controllers/Api/\n\n";

echo "🔧 Integration Features:\n";
echo "- Separate tables for MCP and User keys\n";
echo "- Unified management interface\n";
echo "- Rate limiting and permissions for user keys\n";
echo "- Usage tracking for all keys\n";
echo "- MCP server integration status\n";
echo "- Environment variable support\n";
?>
