<?php
/**
 * Unified Key Management System
 * 
 * Comprehensive CRUD operations for managing API keys, encryption keys,
 * and other sensitive configuration keys with security features.
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Core/Security.php';

class UnifiedKeyManagement {
    private $db;
    private $security;
    
    public function __construct() {
        $this->db = new Database();
        $this->security = new Security();
    }
    
    /**
     * Create a new key entry
     */
    public function createKey($keyData) {
        try {
            // Validate input
            if (empty($keyData['key_name']) || empty($keyData['key_value'])) {
                throw new Exception("Key name and value are required");
            }
            
            // Encrypt the key value
            $encryptedValue = $this->security->encrypt($keyData['key_value']);
            
            // Insert into database
            $sql = "INSERT INTO api_keys (key_name, key_value, key_type, description, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $keyData['key_name'],
                $encryptedValue,
                $keyData['key_type'] ?? 'api_key',
                $keyData['description'] ?? ''
            ]);
            
            return $result ? ['success' => true, 'message' => 'Key created successfully'] 
                          : ['success' => false, 'message' => 'Failed to create key'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Read all keys
     */
    public function getAllKeys() {
        try {
            $sql = "SELECT id, key_name, key_type, description, created_at, updated_at 
                    FROM api_keys 
                    ORDER BY created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Read a specific key
     */
    public function getKey($id) {
        try {
            $sql = "SELECT * FROM api_keys WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            
            $key = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($key) {
                // Decrypt the key value for display
                $key['key_value'] = $this->security->decrypt($key['key_value']);
            }
            
            return $key;
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Update a key
     */
    public function updateKey($id, $keyData) {
        try {
            // Validate input
            if (empty($keyData['key_name']) || empty($keyData['key_value'])) {
                throw new Exception("Key name and value are required");
            }
            
            // Encrypt the key value
            $encryptedValue = $this->security->encrypt($keyData['key_value']);
            
            $sql = "UPDATE api_keys 
                    SET key_name = ?, key_value = ?, key_type = ?, description = ?, updated_at = NOW() 
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $keyData['key_name'],
                $encryptedValue,
                $keyData['key_type'] ?? 'api_key',
                $keyData['description'] ?? '',
                $id
            ]);
            
            return $result ? ['success' => true, 'message' => 'Key updated successfully'] 
                          : ['success' => false, 'message' => 'Failed to update key'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Delete a key
     */
    public function deleteKey($id) {
        try {
            $sql = "DELETE FROM api_keys WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$id]);
            
            return $result ? ['success' => true, 'message' => 'Key deleted successfully'] 
                          : ['success' => false, 'message' => 'Failed to delete key'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Validate key format
     */
    public function validateKeyFormat($keyValue, $keyType) {
        switch ($keyType) {
            case 'api_key':
                return preg_match('/^[a-zA-Z0-9]{32,}$/', $keyValue);
            case 'jwt_secret':
                return strlen($keyValue) >= 32;
            case 'encryption_key':
                return strlen($keyValue) === 32;
            default:
                return !empty($keyValue);
        }
    }
    
    /**
     * Generate secure random key
     */
    public function generateSecureKey($keyType = 'api_key') {
        switch ($keyType) {
            case 'api_key':
                return bin2hex(random_bytes(32));
            case 'jwt_secret':
                return base64url_encode(random_bytes(64));
            case 'encryption_key':
                return bin2hex(random_bytes(16));
            default:
                return bin2hex(random_bytes(32));
        }
    }
}

// Handle HTTP requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keyManager = new UnifiedKeyManagement();
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            $response = $keyManager->createKey($_POST);
            break;
            
        case 'update':
            $response = $keyManager->updateKey($_POST['id'], $_POST);
            break;
            
        case 'delete':
            $response = $keyManager->deleteKey($_POST['id']);
            break;
            
        case 'generate':
            $keyType = $_POST['key_type'] ?? 'api_key';
            $response = ['success' => true, 'key_value' => $keyManager->generateSecureKey($keyType)];
            break;
            
        default:
            $response = ['success' => false, 'message' => 'Invalid action'];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// HTML Interface
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unified Key Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .key-value { font-family: monospace; background: #f8f9fa; padding: 5px; border-radius: 3px; }
        .key-actions { white-space: nowrap; }
        .modal-body { max-height: 500px; overflow-y: auto; }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-key"></i> Unified Key Management</h4>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#keyModal">
                            <i class="fas fa-plus"></i> Add New Key
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="keysTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Key Name</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Keys will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Modal -->
    <div class="modal fade" id="keyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New Key</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="keyForm">
                        <input type="hidden" id="keyId" name="id">
                        <input type="hidden" id="action" name="action" value="create">
                        
                        <div class="mb-3">
                            <label for="keyName" class="form-label">Key Name *</label>
                            <input type="text" class="form-control" id="keyName" name="key_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="keyType" class="form-label">Key Type *</label>
                            <select class="form-select" id="keyType" name="key_type" required>
                                <option value="api_key">API Key</option>
                                <option value="jwt_secret">JWT Secret</option>
                                <option value="encryption_key">Encryption Key</option>
                                <option value="database_key">Database Key</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="keyValue" class="form-label">Key Value *</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="keyValue" name="key_value" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline-secondary" type="button" id="generateKey">
                                    <i class="fas fa-dice"></i> Generate
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveKey">Save Key</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let keyModal;
        
        document.addEventListener('DOMContentLoaded', function() {
            keyModal = new bootstrap.Modal(document.getElementById('keyModal'));
            loadKeys();
        });
        
        function loadKeys() {
            fetch('unified_key_management.php')
                .then(response => response.json())
                .then(data => {
                    const tbody = document.querySelector('#keysTable tbody');
                    tbody.innerHTML = '';
                    
                    data.forEach(key => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${key.id}</td>
                            <td>${key.key_name}</td>
                            <td><span class="badge bg-info">${key.key_type}</span></td>
                            <td>${key.description || '-'}</td>
                            <td>${new Date(key.created_at).toLocaleString()}</td>
                            <td class="key-actions">
                                <button class="btn btn-sm btn-primary" onclick="editKey(${key.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteKey(${key.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                });
        }
        
        function editKey(id) {
            fetch(`unified_key_management.php?id=${id}`)
                .then(response => response.json())
                .then(key => {
                    document.getElementById('keyId').value = key.id;
                    document.getElementById('action').value = 'update';
                    document.getElementById('modalTitle').textContent = 'Edit Key';
                    document.getElementById('keyName').value = key.key_name;
                    document.getElementById('keyType').value = key.key_type;
                    document.getElementById('keyValue').value = key.key_value;
                    document.getElementById('description').value = key.description || '';
                    
                    keyModal.show();
                });
        }
        
        function deleteKey(id) {
            if (confirm('Are you sure you want to delete this key?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);
                
                fetch('unified_key_management.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Key deleted successfully');
                        loadKeys();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }
        
        document.getElementById('togglePassword').addEventListener('click', function() {
            const input = document.getElementById('keyValue');
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        document.getElementById('generateKey').addEventListener('click', function() {
            const keyType = document.getElementById('keyType').value;
            
            const formData = new FormData();
            formData.append('action', 'generate');
            formData.append('key_type', keyType);
            
            fetch('unified_key_management.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('keyValue').value = data.key_value;
                }
            });
        });
        
        document.getElementById('saveKey').addEventListener('click', function() {
            const form = document.getElementById('keyForm');
            const formData = new FormData(form);
            
            fetch('unified_key_management.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    keyModal.hide();
                    loadKeys();
                    form.reset();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        });
        
        // Reset modal when hidden
        document.getElementById('keyModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('keyForm').reset();
            document.getElementById('keyId').value = '';
            document.getElementById('action').value = 'create';
            document.getElementById('modalTitle').textContent = 'Add New Key';
        });
    </script>
</body>
</html>
