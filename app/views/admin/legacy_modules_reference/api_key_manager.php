<?php
/**
 * APS Dream Home - API Key Manager
 * 
 * This page allows administrators to create, view, and manage API keys
 * for third-party integrations with the APS Dream Home system.
 */

require_once __DIR__ . '/core/init.php';
require_once dirname(dirname(dirname(__DIR__))) . '/includes/ApiKeyManager.php';

// Check if user is superadmin
if (!isSuperAdmin()) {
    header('Location: dashboard.php?error=' . urlencode('Access Denied: Superadmin privilege required.'));
    exit();
}

$db = \App\Core\App::database();
$apiKeyMgr = new ApiKeyManager($db);

// Initialize variables
$message = '';
$error = '';
$availableEndpoints = [];

// Scan API directory to get available endpoints
function scanApiDirectory($dir, $prefix = '') {
    $endpoints = [];
    
    if (!is_dir($dir)) {
        return $endpoints;
    }
    
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $path = $dir . '/' . $file;
        $relativePath = $prefix . '/' . $file;
        
        if (is_dir($path)) {
            // Add directory wildcard endpoint
            $endpoints[] = [
                'path' => $relativePath . '/*',
                'description' => 'All endpoints in ' . $relativePath
            ];
            
            // Recursively scan subdirectory
            $endpoints = array_merge($endpoints, scanApiDirectory($path, $relativePath));
        } else if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            // Add specific endpoint
            $endpoint = $relativePath;
            $description = '';
            
            // Try to extract description from file
            $content = file_get_contents($path);
            if (preg_match('/@description\s+(.*?)(\n\s*\*\s*@|\n\s*\*\/)/s', $content, $matches)) {
                $description = trim(preg_replace('/\n\s*\*\s*/', ' ', $matches[1]));
            }
            
            $endpoints[] = [
                'path' => $endpoint,
                'description' => $description
            ];
        }
    }
    return $endpoints;
}

// Scan API directory
$availableEndpoints = scanApiDirectory(dirname(dirname(dirname(__DIR__))) . '/api');

// Add wildcard for all endpoints
array_unshift($availableEndpoints, [
    'path' => '*',
    'description' => 'All API endpoints (full access)'
]);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF Token
    if (!verifyCSRFToken()) {
        $error = "Invalid CSRF token. Please try again.";
    } else {
        // Create new API key
        if (isset($_POST['create_key'])) {
            $name = trim($_POST['key_name']);
            $permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];
            $rateLimit = isset($_POST['rate_limit']) ? (int)$_POST['rate_limit'] : 1000;
            
            if (empty($name)) {
                $error = "API key name is required";
            } else {
                $userId = $_SESSION['admin_session']['id'] ?? null;
                $plainKey = $apiKeyMgr->generateKey($name, $userId, $permissions, null, $rateLimit);
                
                if ($plainKey) {
                    $message = "API key created successfully. <strong>Please copy it now, as it will not be shown again:</strong> <code class='bg-light p-2'>" . h($plainKey) . "</code>";
                    logAdminActivity("Created API Key", "Name: $name");
                } else {
                    $error = "Failed to create API key";
                }
            }
        }
        
        // Revoke API key
        else if (isset($_POST['revoke_key'])) {
            $apiKey = $_POST['api_key'];
            
            if ($apiKeyMgr->deactivateKey($apiKey)) {
                $message = "API key revoked successfully";
                logAdminActivity("Revoked API Key", "Key: " . substr($apiKey, 0, 8) . "...");
            } else {
                $error = "Failed to revoke API key";
            }
        }
    }
}

// Get user's API keys
$userId = $_SESSION['admin_session']['id'] ?? null;
$apiKeys = $apiKeyMgr->getUserKeys($userId);
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
        .bg-light-info {
            background-color: #f0f7ff;
        }
        .table-avatar {
            align-items: center;
            display: inline-flex;
            font-size: inherit;
            font-weight: 400;
            margin: 0;
            padding: 0;
            vertical-align: middle;
            white-space: nowrap;
        }
        .badge-pill {
            padding-left: .65em;
            padding-right: .65em;
        }
        .bg-success-light {
            background-color: rgba(15, 183, 107, 0.12) !important;
            color: #26af48 !important;
        }
        .bg-danger-light {
            background-color: rgba(242, 17, 54, 0.12) !important;
            color: #e63c3c !important;
        }
        .action-icon {
            color: #777;
            font-size: 18px;
            padding: 0 10px;
            display: inline-block;
        }
        .permissions-container::-webkit-scrollbar {
            width: 5px;
        }
        .permissions-container::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 5px;
        }
    </style>
    <?php 
    $page_title = "API Key Manager";
    include 'admin_header.php'; 
    ?>
    <?php include 'admin_sidebar.php'; ?>
    
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title"><?php echo h($page_title); ?></h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">API Key Manager</li>
                        </ul>
                    </div>
                    <div class="col-auto float-right ml-auto">
                        <a href="#create_key_form" class="btn btn-primary add-btn"><i class="fas fa-plus"></i> Create New Key</a>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo h($error); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h4 class="card-title mb-0">Your API Keys</h4>
                        </div>
                        <div class="card-body">
                            <?php if (empty($apiKeys)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-key fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">You haven't created any API keys yet.</p>
                                    <a href="#create_key_form" class="btn btn-primary mt-2">Generate Your First Key</a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover table-center mb-0">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Status</th>
                                                <th>Usage</th>
                                                <th>Rate Limit</th>
                                                <th>Created</th>
                                                <th>Last Used</th>
                                                <th class="text-right">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($apiKeys as $key): ?>
                                                <tr>
                                                    <td>
                                                        <h2 class="table-avatar">
                                                            <span class="text-primary font-weight-bold"><?php echo h($key['name']); ?></span>
                                                        </h2>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-pill bg-<?php echo $key['status'] === 'active' ? 'success' : 'danger'; ?>-light">
                                                            <?php echo h(ucfirst($key['status'])); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="text-muted"><?php echo h($key['usage_count']); ?> requests</span>
                                                    </td>
                                                    <td>
                                                        <span class="text-muted"><?php echo h($key['rate_limit']); ?>/hr</span>
                                                    </td>
                                                    <td>
                                                        <span class="text-muted"><?php echo date('M j, Y', strtotime($key['created_at'])); ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="text-muted"><?php echo $key['last_used_at'] ? date('M j, Y', strtotime($key['last_used_at'])) : 'Never'; ?></span>
                                                    </td>
                                                    <td class="text-right">
                                                        <div class="dropdown dropdown-action">
                                                            <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></a>
                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                <?php if ($key['status'] === 'active'): ?>
                                                                    <form method="post" onsubmit="return confirm('Are you sure you want to revoke this API key? This action cannot be undone.');" style="display: inline;">
                                                                        <?php echo getCsrfField(); ?>
                                                                        <input type="hidden" name="api_key" value="<?php echo h($key['api_key']); ?>">
                                                                        <button type="submit" name="revoke_key" class="dropdown-item text-danger">
                                                                            <i class="fas fa-trash-alt m-r-5"></i> Revoke
                                                                        </button>
                                                                    </form>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php if ($key['status'] === 'active'): ?>
                                                    <tr>
                                                        <td colspan="7" class="bg-light-info py-2">
                                                            <div class="d-flex align-items-center px-3">
                                                                <small class="text-muted mr-2">Key ID:</small>
                                                                <code id="key_<?php echo h(md5($key['api_key'])); ?>" class="mr-2"><?php echo h($key['api_key']); ?></code>
                                                                <button class="btn btn-sm btn-outline-primary copy-btn py-0" onclick="copyToClipboard('<?php echo h($key['api_key']); ?>')" title="Copy to clipboard">
                                                                    <i class="fas fa-copy"></i> Copy
                                                                </button>
                                                                <div class="ml-auto">
                                                                    <small class="text-muted">Permissions: </small>
                                                                    <?php if (empty($key['permissions'])): ?>
                                                                        <span class="badge badge-secondary">Full Access</span>
                                                                    <?php else: ?>
                                                                        <?php foreach (array_slice($key['permissions'], 0, 3) as $perm): ?>
                                                                            <span class="badge badge-info"><?php echo h($perm); ?></span>
                                                                        <?php endforeach; ?>
                                                                        <?php if (count($key['permissions']) > 3): ?>
                                                                            <span class="badge badge-info">+<?php echo count($key['permissions']) - 3; ?> more</span>
                                                                        <?php endif; ?>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4" id="create_key_form">
                <div class="col-md-7">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h4 class="card-title mb-0">Create New API Key</h4>
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <?php echo getCsrfField(); ?>
                                <div class="form-group">
                                    <label>API Key Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="key_name" required placeholder="e.g., Mobile App Integration">
                                    <small class="form-text text-muted">A descriptive name to identify this key.</small>
                                </div>
                                
                                <div class="form-group">
                                    <label>Rate Limit (requests per hour)</label>
                                    <input type="number" class="form-control" name="rate_limit" value="1000" min="1" max="10000">
                                    <small class="form-text text-muted">Default is 1000 requests per hour.</small>
                                </div>
                                
                                <div class="form-group">
                                    <label>Permissions</label>
                                    <p class="text-muted small">Select which API endpoints this key can access. Leave all unchecked for full access.</p>
                                    
                                    <div class="permissions-container border rounded p-3" style="max-height: 300px; overflow-y: auto; background-color: #fcfcfc;">
                                        <?php foreach ($availableEndpoints as $endpoint): ?>
                                            <div class="custom-control custom-checkbox mb-2">
                                                <input type="checkbox" class="custom-control-input" id="perm_<?php echo md5($endpoint['path']); ?>" name="permissions[]" value="<?php echo h($endpoint['path']); ?>">
                                                <label class="custom-control-label" for="perm_<?php echo md5($endpoint['path']); ?>">
                                                    <strong><?php echo h($endpoint['path']); ?></strong>
                                                    <?php if (!empty($endpoint['description'])): ?>
                                                        <br><small class="text-muted"><?php echo h($endpoint['description']); ?></small>
                                                    <?php endif; ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <div class="submit-section">
                                    <button type="submit" name="create_key" class="btn btn-primary submit-btn">Generate API Key</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-5">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h4 class="card-title mb-0">API Quick Guide</h4>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info py-2">
                                <small><i class="fas fa-info-circle mr-1"></i> Use these keys to authenticate your third-party applications.</small>
                            </div>
                            
                            <h6>Authentication</h6>
                            <p class="small text-muted">Include your API key in the request headers:</p>
                            <div class="bg-dark text-light p-2 rounded mb-3">
                                <code class="text-info">X-API-Key: your_api_key_here</code>
                            </div>
                            
                            <h6>Rate Limiting</h6>
                            <p class="small text-muted mb-0">API requests are rate-limited based on your key settings. Responses include these headers:</p>
                            <ul class="small text-muted mt-1">
                                <li><code>X-Rate-Limit-Limit</code></li>
                                <li><code>X-Rate-Limit-Remaining</code></li>
                                <li><code>X-Rate-Limit-Reset</code></li>
                            </ul>
                            
                            <hr>
                            
                            <div class="text-center">
                                <p class="small mb-2">Need more help?</p>
                                <a href="../database/api_documentation.php" class="btn btn-sm btn-outline-info">View Full Documentation</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        async function copyToClipboard(text) {
            try {
                await navigator.clipboard.writeText(text);
                
                // Show a brief success message
                const btn = event.currentTarget;
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                btn.classList.replace('btn-outline-primary', 'btn-success');
                
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.classList.replace('btn-success', 'btn-outline-primary');
                }, 2000);
            } catch (err) {
                console.error('Failed to copy: ', err);
                
                // Fallback for older browsers
                const textarea = document.createElement('textarea');
                textarea.value = text;   
                document.body.appendChild(textarea);
                textarea.select();       
                document.execCommand('copy');
                document.body.removeChild(textarea);
                alert('API key copied to clipboard');
            }
        }
    </script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>


