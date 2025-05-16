<?php
/**
 * API Key Management Interface
 * Manage API keys, permissions, and rate limits
 */

require_once __DIR__ . '/../includes/config/config.php';
require_once __DIR__ . '/../includes/// SECURITY: Sensitive information removed_manager.php';
require_once __DIR__ . '/../includes/middleware/rate_limit_middleware.php';

// Check admin authentication
session_start();
if (!isset($_SESSION['auser'])) {
    header('location:index.php');
    exit();
}

// Apply rate limiting
$rateLimitMiddleware->handle('admin');

// Initialize API key manager
$apiKeyManager = new ApiKeyManager();

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'generate_key':
            $name = $_POST['name'] ?? '';
            $userId = $_SESSION['auser']['id'] ?? 0;
            $permissions = json_decode($_POST['permissions'] ?? '[]', true);
            $expiresAt = $_POST['expires_at'] ?? null;
            $rateLimit = (int)($_POST['rate_limit'] ?? 1000);
            
            $result = $apiKeyManager->generateKey($name, $userId, $permissions, $expiresAt, $rateLimit);
            echo json_encode($result);
            break;
            
        case 'list_keys':
            $userId = $_SESSION['auser']['id'] ?? 0;
            $keys = $apiKeyManager->listKeys($userId);
            echo json_encode($keys);
            break;
            
        case 'revoke_key':
            $keyId = $_POST['key_id'] ?? 0;
            $userId = $_SESSION['auser']['id'] ?? 0;
            $result = $apiKeyManager->revokeKey($keyId, $userId);
            echo json_encode(['success' => $result]);
            break;
            
        case 'update_key':
            $keyId = $_POST['key_id'] ?? 0;
            $userId = $_SESSION['auser']['id'] ?? 0;
            $updates = json_decode($_POST['updates'] ?? '{}', true);
            $result = $apiKeyManager->updateKey($keyId, $userId, $updates);
            echo json_encode(['success' => $result]);
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
    exit;
}

// Get available permissions
$availablePermissions = [
    'properties.read' => 'Read Properties',
    'properties.write' => 'Write Properties',
    'properties.delete' => 'Delete Properties',
    'properties.detailed_read' => 'Detailed Property Info',
    'users.read' => 'Read Users',
    'users.write' => 'Write Users',
    'users.delete' => 'Delete Users',
    'analytics.read' => 'Read Analytics',
    'analytics.detailed' => 'Detailed Analytics',
    'logs.read' => 'Read Logs',
    'logs.write' => 'Write Logs'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Key Management - APS Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <style>
        .api-key {
            font-family: monospace;
            word-break: break-all;
        }
        .key-card {
            margin-bottom: 20px;
        }
        .key-actions {
            display: flex;
            gap: 10px;
        }
        .permissions-list {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        .permission-badge {
            font-size: 0.8em;
            padding: 2px 8px;
        }
        .key-info {
            font-size: 0.9em;
            color: #6c757d;
        }
        .select2-container {
            width: 100% !important;
        }
    </style>
</head>
<body>
    <?php include("../includes/templates/dynamic_header.php"); ?>
    <div class="container py-4">
        <h1 class="mb-4">API Key Management</h1>
        
        <!-- Generate Key -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">Generate New API Key</div>
                    <div class="card-body">
                        <form id="generateKeyForm">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Key Name</label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Rate Limit (requests/hour)</label>
                                    <input type="number" class="form-control" name="rate_limit" 
                                           value="1000" min="1" max="10000" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Permissions</label>
                                    <select class="form-select" name="permissions" multiple required>
                                        <?php foreach ($availablePermissions as $value => $label): ?>
                                        <option value="<?= htmlspecialchars($value) ?>">
                                            <?= htmlspecialchars($label) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Expires At (optional)</label>
                                    <input type="text" class="form-control" name="expires_at">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Generate Key</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- API Keys List -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">Active API Keys</div>
                    <div class="card-body">
                        <div id="keysList"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Key Modal -->
    <div class="modal fade" id="editKeyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit API Key</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editKeyForm">
                        <input type="hidden" name="key_id">
                        <div class="mb-3">
                            <label class="form-label">Key Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rate Limit (requests/hour)</label>
                            <input type="number" class="form-control" name="rate_limit" 
                                   min="1" max="10000" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Permissions</label>
                            <select class="form-select" name="permissions" multiple required>
                                <?php foreach ($availablePermissions as $value => $label): ?>
                                <option value="<?= htmlspecialchars($value) ?>">
                                    <?= htmlspecialchars($label) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Expires At (optional)</label>
                            <input type="text" class="form-control" name="expires_at">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveKeyChanges">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- New Key Modal -->
    <div class="modal fade" id="newKeyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New API Key Generated</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Your new API key has been generated. Please save it now as you won't be able to see it again:</p>
                    <div class="alert alert-info api-key" id="newKeyDisplay"></div>
                    <p><strong>Important:</strong> Store this key securely and never share it publicly.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="copyNewKey()">Copy Key</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php include("../includes/templates/new_footer.php"); ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // Initialize modals
        const editKeyModal = new bootstrap.Modal(document.getElementById('editKeyModal'));
        const newKeyModal = new bootstrap.Modal(document.getElementById('newKeyModal'));

        // Initialize Select2 for permissions
        $('select[name="permissions"]').select2({
            placeholder: 'Select permissions',
            closeOnSelect: false
        });

        // Initialize Flatpickr for date inputs
        flatpickr('input[name="expires_at"]', {
            enableTime: true,
            dateFormat: "Y-m-d H:i:S",
            minDate: "today"
        });

        // Load API keys
        function loadKeys() {
            $.get('// SECURITY: Sensitive information removeds.php', { action: 'list_keys' }, function(keys) {
                const $keysList = $('#keysList').empty();
                
                if (keys.length === 0) {
                    $keysList.append('<p>No API keys found.</p>');
                    return;
                }

                keys.forEach(key => {
                    const permissions = JSON.parse(key.permissions);
                    const permissionBadges = permissions.map(p => 
                        `<span class="badge bg-info permission-badge">${p}</span>`
                    ).join('');

                    $keysList.append(`
                        <div class="card key-card">
                            <div class="card-body">
                                <h5 class="card-title">${key.name}</h5>
                                <div class="key-info">
                                    <p>
                                        Created: ${key.created_at}<br>
                                        ${key.expires_at ? 'Expires: ' + key.expires_at + '<br>' : ''}
                                        Rate Limit: ${key.rate_limit} requests/hour<br>
                                        Last Used: ${key.last_used_at || 'Never'}
                                    </p>
                                </div>
                                <div class="permissions-list mb-3">
                                    ${permissionBadges}
                                </div>
                                <div class="key-actions">
                                    <button class="btn btn-primary btn-sm edit-key" 
                                            data-key='${JSON.stringify(key)}'>
                                        Edit
                                    </button>
                                    <button class="btn btn-danger btn-sm revoke-key" 
                                            data-key-id="${key.id}">
                                        Revoke
                                    </button>
                                </div>
                            </div>
                        </div>
                    `);
                });
            });
        }

        // Generate new key
        $('#generateKeyForm').submit(function(e) {
            e.preventDefault();
            
            const formData = {
                name: this.name.value,
                permissions: JSON.stringify($(this.permissions).val()),
                rate_limit: this.rate_limit.value,
                expires_at: this.expires_at.value || null
            };
            
            $.post('// SECURITY: Sensitive information removeds.php?action=generate_key', formData, function(result) {
                if (result.key) {
                    $('#newKeyDisplay').text(result.key);
                    newKeyModal.show();
                    $('#generateKeyForm')[0].reset();
                    loadKeys();
                } else {
                    alert('Failed to generate API key');
                }
            });
        });

        // Copy new key to clipboard
        function copyNewKey() {
            const key = $('#newKeyDisplay').text();
            navigator.clipboard.writeText(key).then(() => {
                alert('API key copied to clipboard');
            });
        }

        // Edit key
        $(document).on('click', '.edit-key', function() {
            const key = $(this).data('key');
            const $form = $('#editKeyForm');
            
            $form.find('[name="key_id"]').val(key.id);
            $form.find('[name="name"]').val(key.name);
            $form.find('[name="rate_limit"]').val(key.rate_limit);
            $form.find('[name="expires_at"]').val(key.expires_at);
            $form.find('[name="permissions"]').val(JSON.parse(key.permissions)).trigger('change');
            
            editKeyModal.show();
        });

        // Save key changes
        $('#saveKeyChanges').click(function() {
            const $form = $('#editKeyForm');
            const updates = {
                name: $form.find('[name="name"]').val(),
                rate_limit: parseInt($form.find('[name="rate_limit"]').val()),
                permissions: $form.find('[name="permissions"]').val(),
                expires_at: $form.find('[name="expires_at"]').val() || null
            };
            
            $.post('// SECURITY: Sensitive information removeds.php?action=update_key', {
                key_id: $form.find('[name="key_id"]').val(),
                updates: JSON.stringify(updates)
            }, function(result) {
                if (result.success) {
                    editKeyModal.hide();
                    loadKeys();
                } else {
                    alert('Failed to update API key');
                }
            });
        });

        // Revoke key
        $(document).on('click', '.revoke-key', function() {
            if (!confirm('Are you sure you want to revoke this API key? This action cannot be undone.')) {
                return;
            }
            
            const keyId = $(this).data('key-id');
            $.post('// SECURITY: Sensitive information removeds.php?action=revoke_key', { key_id: keyId }, function(result) {
                if (result.success) {
                    loadKeys();
                } else {
                    alert('Failed to revoke API key');
                }
            });
        });

        // Initial load
        loadKeys();
    </script>
</body>
</html>

