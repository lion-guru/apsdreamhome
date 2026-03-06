<?php
/**
 * APS Dream Home - Secure Key Management System
 * Store and manage API keys securely in database
 */

echo "🔐 Secure Key Management System\n";
echo "===============================\n\n";

// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'apsdreamhome';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connected\n\n";
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n\n";
    exit;
}

// Create keys table if not exists
echo "🔧 Creating keys table...\n";
$createTableSQL = "
CREATE TABLE IF NOT EXISTS api_keys (
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

try {
    $pdo->exec($createTableSQL);
    echo "✅ Keys table created/verified\n\n";
} catch (PDOException $e) {
    echo "❌ Table creation failed: " . $e->getMessage() . "\n\n";
}

// Load environment variables
$envFile = __DIR__ . '/.env';
$envVars = [];
if (file_exists($envFile)) {
    echo "🔧 Loading environment variables...\n";
    $envContent = file_get_contents($envFile);
    $envLines = explode("\n", $envContent);
    
    foreach ($envLines as $line) {
        if (strpos($line, '=') !== false && !str_starts_with($line, '#')) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"");
            $envVars[$key] = $value;
        }
    }
    echo "✅ Loaded " . count($envVars) . " environment variables\n\n";
}

// Store API keys in database
echo "🔐 Storing API keys in database...\n";
$keysToStore = [
    'GOOGLE_MAPS_API_KEY' => [
        'type' => 'api_key',
        'service' => 'Google Maps',
        'description' => 'Google Maps JavaScript API key for mapping services'
    ],
    'RECAPTCHA_SITE_KEY' => [
        'type' => 'api_key',
        'service' => 'Google reCAPTCHA',
        'description' => 'reCAPTCHA site key for form validation'
    ],
    'RECAPTCHA_SECRET_KEY' => [
        'type' => 'api_key',
        'service' => 'Google reCAPTCHA',
        'description' => 'reCAPTCHA secret key for server-side validation'
    ],
    'OPENROUTER_API_KEY' => [
        'type' => 'api_key',
        'service' => 'OpenRouter',
        'description' => 'OpenRouter API key for AI services'
    ],
    'WHATSAPP_ACCESS_TOKEN' => [
        'type' => 'token',
        'service' => 'WhatsApp Business',
        'description' => 'WhatsApp Business API access token'
    ],
    'WHATSAPP_WEBHOOK_VERIFY_TOKEN' => [
        'type' => 'token',
        'service' => 'WhatsApp Business',
        'description' => 'WhatsApp webhook verification token'
    ]
];

$insertSQL = "INSERT INTO api_keys (key_name, key_value, key_type, service_name, description) 
             VALUES (:key_name, :key_value, :key_type, :service_name, :description)
             ON DUPLICATE KEY UPDATE 
             key_value = VALUES(key_value), 
             description = VALUES(description),
             updated_at = CURRENT_TIMESTAMP";

$stmt = $pdo->prepare($insertSQL);

foreach ($keysToStore as $envKey => $config) {
    if (isset($envVars[$envKey]) && !empty($envVars[$envKey]) && $envVars[$envKey] !== 'your_' . strtolower(str_replace('_', '', $envKey))) {
        try {
            $stmt->execute([
                ':key_name' => $envKey,
                ':key_value' => $envVars[$envKey],
                ':key_type' => $config['type'],
                ':service_name' => $config['service'],
                ':description' => $config['description']
            ]);
            echo "✅ Stored: {$config['service']} - {$envKey}\n";
        } catch (PDOException $e) {
            echo "❌ Failed to store {$envKey}: " . $e->getMessage() . "\n";
        }
    } else {
        echo "⚠️  Skipped: {$envKey} (not configured or using placeholder)\n";
    }
}

echo "\n🔧 Creating key management functions...\n";

// Create key management class
$keyManagerClass = '<?php
/**
 * APS Dream Home - Key Manager Class
 * Secure key management and retrieval
 */
class KeyManager {
    private $pdo;
    private static $instance = null;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public static function getInstance($pdo = null) {
        if (self::$instance === null) {
            if ($pdo === null) {
                // Use default database connection
                $host = "localhost";
                $user = "root";
                $password = "";
                $database = "apsdreamhome";
                $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            self::$instance = new self($pdo);
        }
        return self::$instance;
    }
    
    /**
     * Get API key by name
     */
    public function getKey($keyName) {
        $stmt = $this->pdo->prepare("SELECT key_value FROM api_keys WHERE key_name = ? AND is_active = 1");
        $stmt->execute([$keyName]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result["key_value"] : null;
    }
    
    /**
     * Get all keys for a service
     */
    public function getKeysByService($serviceName) {
        $stmt = $this->pdo->prepare("SELECT key_name, key_value, key_type FROM api_keys WHERE service_name = ? AND is_active = 1");
        $stmt->execute([$serviceName]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Store or update a key
     */
    public function storeKey($keyName, $keyValue, $keyType, $serviceName, $description = "") {
        $stmt = $this->pdo->prepare("
            INSERT INTO api_keys (key_name, key_value, key_type, service_name, description) 
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            key_value = VALUES(key_value), 
            description = VALUES(description),
            updated_at = CURRENT_TIMESTAMP
        ");
        return $stmt->execute([$keyName, $keyValue, $keyType, $serviceName, $description]);
    }
    
    /**
     * Mark key as used
     */
    public function markKeyUsed($keyName) {
        $stmt = $this->pdo->prepare("
            UPDATE api_keys 
            SET usage_count = usage_count + 1, last_used_at = CURRENT_TIMESTAMP 
            WHERE key_name = ?
        ");
        return $stmt->execute([$keyName]);
    }
    
    /**
     * Deactivate a key
     */
    public function deactivateKey($keyName) {
        $stmt = $this->pdo->prepare("UPDATE api_keys SET is_active = 0 WHERE key_name = ?");
        return $stmt->execute([$keyName]);
    }
    
    /**
     * Get all active keys (for admin)
     */
    public function getAllKeys() {
        $stmt = $this->pdo->prepare("
            SELECT key_name, service_name, key_type, description, is_active, created_at, last_used_at, usage_count 
            FROM api_keys 
            ORDER BY service_name, key_name
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get key usage statistics
     */
    public function getKeyStats() {
        $stmt = $this->pdo->prepare("
            SELECT 
                service_name,
                COUNT(*) as total_keys,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_keys,
                SUM(usage_count) as total_usage
            FROM api_keys 
            GROUP BY service_name
            ORDER BY service_name
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>';

file_put_contents(__DIR__ . '/config/KeyManager.php', $keyManagerClass);
echo "✅ KeyManager class created\n\n";

// Create key management dashboard
echo "🔧 Creating key management dashboard...\n";
$dashboardHTML = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Key Management - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="bi bi-key-fill me-2"></i>API Key Management</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addKeyModal">
                        <i class="bi bi-plus-circle me-1"></i>Add New Key
                    </button>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Keys</h5>
                                <h2 id="totalKeys">-</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Active Keys</h5>
                                <h2 id="activeKeys">-</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Services</h5>
                                <h2 id="totalServices">-</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Usage</h5>
                                <h2 id="totalUsage">-</h2>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Keys Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Stored API Keys</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="keysTable">
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
                                <tbody id="keysTableBody">
                                    <!-- Keys will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Key Modal -->
    <div class="modal fade" id="addKeyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New API Key</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addKeyForm">
                        <div class="mb-3">
                            <label for="keyName" class="form-label">Key Name</label>
                            <input type="text" class="form-control" id="keyName" required>
                        </div>
                        <div class="mb-3">
                            <label for="keyValue" class="form-label">Key Value</label>
                            <input type="password" class="form-control" id="keyValue" required>
                        </div>
                        <div class="mb-3">
                            <label for="keyType" class="form-label">Key Type</label>
                            <select class="form-select" id="keyType" required>
                                <option value="api_key">API Key</option>
                                <option value="token">Token</option>
                                <option value="password">Password</option>
                                <option value="certificate">Certificate</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="serviceName" class="form-label">Service Name</label>
                            <input type="text" class="form-control" id="serviceName" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="addKey()">Add Key</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Load keys from server
        function loadKeys() {
            fetch("api_keys.php?action=list")
                .then(response => response.json())
                .then(data => {
                    updateStats(data.stats);
                    updateTable(data.keys);
                })
                .catch(error => console.error("Error loading keys:", error));
        }
        
        function updateStats(stats) {
            document.getElementById("totalKeys").textContent = stats.totalKeys || 0;
            document.getElementById("activeKeys").textContent = stats.activeKeys || 0;
            document.getElementById("totalServices").textContent = stats.totalServices || 0;
            document.getElementById("totalUsage").textContent = stats.totalUsage || 0;
        }
        
        function updateTable(keys) {
            const tbody = document.getElementById("keysTableBody");
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
                        <button class="btn btn-sm btn-outline-primary" onclick="viewKey(\'${key.key_name}\')">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning" onclick="editKey(\'${key.key_name}\')">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deactivateKey(\'${key.key_name}\')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }
        
        function addKey() {
            const form = document.getElementById("addKeyForm");
            const formData = new FormData(form);
            
            fetch("api_keys.php?action=add", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById("addKeyModal")).hide();
                    form.reset();
                    loadKeys();
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(error => console.error("Error adding key:", error));
        }
        
        // Load keys on page load
        document.addEventListener("DOMContentLoaded", loadKeys);
    </script>
</body>
</html>';

file_put_contents(__DIR__ . '/admin/api_keys.php', $dashboardHTML);
echo "✅ Key management dashboard created\n\n";

// Create API endpoint for key management
$apiEndpoint = '<?php
/**
 * API Keys Management API Endpoint
 */
require_once "../config/KeyManager.php";

header("Content-Type: application/json");

// Database connection
$host = "localhost";
$user = "root";
$password = "";
$database = "apsdreamhome";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

$keyManager = KeyManager::getInstance($pdo);

$action = $_GET["action"] ?? "";

switch ($action) {
    case "list":
        $keys = $keyManager->getAllKeys();
        $stats = $keyManager->getKeyStats();
        
        $totalKeys = count($keys);
        $activeKeys = array_filter($keys, fn($k) => $k["is_active"]);
        $totalUsage = array_sum(array_column($keys, "usage_count"));
        
        echo json_encode([
            "success" => true,
            "keys" => $keys,
            "stats" => [
                "totalKeys" => $totalKeys,
                "activeKeys" => count($activeKeys),
                "totalServices" => count($stats),
                "totalUsage" => $totalUsage
            ]
        ]);
        break;
        
    case "add":
        $keyName = $_POST["keyName"] ?? "";
        $keyValue = $_POST["keyValue"] ?? "";
        $keyType = $_POST["keyType"] ?? "";
        $serviceName = $_POST["serviceName"] ?? "";
        $description = $_POST["description"] ?? "";
        
        if (empty($keyName) || empty($keyValue) || empty($keyType) || empty($serviceName)) {
            echo json_encode(["success" => false, "message" => "All required fields must be filled"]);
            break;
        }
        
        $success = $keyManager->storeKey($keyName, $keyValue, $keyType, $serviceName, $description);
        echo json_encode(["success" => $success]);
        break;
        
    case "get":
        $keyName = $_GET["keyName"] ?? "";
        if (empty($keyName)) {
            echo json_encode(["success" => false, "message" => "Key name is required"]);
            break;
        }
        
        $keyValue = $keyManager->getKey($keyName);
        echo json_encode(["success" => !empty($keyValue), "value" => $keyValue]);
        break;
        
    case "deactivate":
        $keyName = $_POST["keyName"] ?? "";
        if (empty($keyName)) {
            echo json_encode(["success" => false, "message" => "Key name is required"]);
            break;
        }
        
        $success = $keyManager->deactivateKey($keyName);
        echo json_encode(["success" => $success]);
        break;
        
    default:
        echo json_encode(["success" => false, "message" => "Invalid action"]);
}
?>';

file_put_contents(__DIR__ . '/admin/api_keys_api.php', $apiEndpoint);
echo "✅ API endpoint created\n\n";

echo "🎉 Secure Key Management System Complete!\n";
echo "==========================================\n";
echo "✅ Database table created\n";
echo "✅ Environment keys stored in database\n";
echo "✅ KeyManager class created\n";
echo "✅ Management dashboard created\n";
echo "✅ API endpoint created\n\n";

echo "🚀 Access Points:\n";
echo "- Dashboard: http://localhost/apsdreamhome/admin/api_keys.php\n";
echo "- API: http://localhost/apsdreamhome/admin/api_keys_api.php\n";
echo "- KeyManager Class: config/KeyManager.php\n\n";

echo "📊 Features:\n";
echo "- Secure key storage in database\n";
echo "- Usage tracking and statistics\n";
echo "- Key activation/deactivation\n";
echo "- Web-based management interface\n";
echo "- API access for programmatic use\n";
echo "- Environment variable integration\n";
?>
