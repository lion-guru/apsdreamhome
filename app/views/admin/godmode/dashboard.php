<?php
/**
 * God Mode Dashboard - Admin Super Powers
 * User Impersonation, Role Switching, System Control
 */

$base = BASE_URL;
$page_title = "God Mode - Super Admin";

// Get data from controller
$stats = $stats ?? [];
$impersonations = $impersonations ?? [];
$users = $users ?? [];
$roles = $roles ?? [];
$current_admin = $current_admin ?? null;

// Check if currently impersonating
$isImpersonating = isset($_SESSION['god_mode_impersonating']);
$impersonatingUserId = $_SESSION['god_mode_user_id'] ?? null;

// Check if role switched
$roleSwitched = isset($_SESSION['god_mode_role_switched']);
$tempRole = $_SESSION['god_mode_temp_role'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> | APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --god-primary: #6b21a8;
            --god-secondary: #9333ea;
            --god-accent: #fbbf24;
            --god-dark: #1e1b4b;
        }
        
        body {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #1e1b4b 100%);
            min-height: 100vh;
            color: #fff;
        }
        
        .god-header {
            background: rgba(107, 33, 168, 0.9);
            backdrop-filter: blur(10px);
            border-bottom: 2px solid var(--god-accent);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .god-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .god-card:hover {
            border-color: var(--god-accent);
            transform: translateY(-2px);
        }
        
        .god-card-header {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .god-card-header i {
            color: var(--god-accent);
        }
        
        .stat-card {
            background: linear-gradient(135deg, var(--god-primary) 0%, var(--god-secondary) 100%);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--god-accent);
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .btn-god {
            background: linear-gradient(135deg, var(--god-primary) 0%, var(--god-secondary) 100%);
            border: none;
            color: white;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-god:hover {
            background: linear-gradient(135deg, var(--god-secondary) 0%, var(--god-accent) 100%);
            color: white;
            transform: scale(1.05);
        }
        
        .btn-warning-god {
            background: #f59e0b;
            border: none;
            color: white;
        }
        
        .btn-danger-god {
            background: #ef4444;
            border: none;
            color: white;
        }
        
        .user-card {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .user-card:hover {
            background: rgba(255, 255, 255, 0.08);
        }
        
        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 15px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.1);
            margin: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .role-badge:hover, .role-badge.active {
            background: var(--god-accent);
            color: var(--god-dark);
        }
        
        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }
        
        .status-active { background: #22c55e; }
        .status-inactive { background: #ef4444; }
        .status-warning { background: #f59e0b; }
        
        .impersonation-banner {
            background: linear-gradient(90deg, #f59e0b, #ef4444);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .search-box {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 25px;
            padding: 12px 20px;
            color: white;
            width: 100%;
        }
        
        .search-box::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .command-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            padding: 15px;
            color: white;
            text-align: left;
            transition: all 0.3s ease;
            width: 100%;
            margin-bottom: 10px;
        }
        
        .command-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--god-accent);
        }
        
        .command-btn i {
            margin-right: 10px;
            color: var(--god-accent);
        }
        
        .log-entry {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 8px;
            font-family: monospace;
            font-size: 0.85rem;
        }
        
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        
        .god-toast {
            background: rgba(107, 33, 168, 0.95);
            border: 1px solid var(--god-accent);
            border-radius: 10px;
            padding: 15px 20px;
            color: white;
            margin-bottom: 10px;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        .modal-god .modal-content {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
            border: 1px solid var(--god-accent);
            color: white;
        }
        
        .modal-god .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .modal-god .modal-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .form-control-god {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .form-control-god:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--god-accent);
            color: white;
            box-shadow: 0 0 0 0.2rem rgba(251, 191, 36, 0.25);
        }
    </style>
</head>
<body>
    <!-- Impersonation Warning Banner -->
    <?php if ($isImpersonating): ?>
    <div class="container mt-3">
        <div class="impersonation-banner">
            <div>
                <i class="fas fa-user-secret me-2"></i>
                <strong>IMpersonating User ID: <?php echo $impersonatingUserId; ?></strong>
                <span class="ms-3">Started: <?php echo date('H:i:s', $_SESSION['god_mode_start_time'] ?? time()); ?></span>
            </div>
            <button class="btn btn-light btn-sm" onclick="stopImpersonation()">
                <i class="fas fa-sign-out-alt me-1"></i>Exit Impersonation
            </button>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Role Switch Warning -->
    <?php if ($roleSwitched): ?>
    <div class="container mt-3">
        <div class="alert alert-warning-god" style="background: linear-gradient(90deg, #f59e0b, #fbbf24); color: #1e1b4b; padding: 15px; border-radius: 10px;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-exchange-alt me-2"></i>
                    <strong>Role Switched:</strong> Currently viewing as <span class="badge bg-dark"><?php echo ucfirst($tempRole); ?></span>
                </div>
                <button class="btn btn-dark btn-sm" onclick="restoreRole()">
                    <i class="fas fa-undo me-1"></i>Restore Admin Role
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Header -->
    <header class="god-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <i class="fas fa-crown fa-2x" style="color: var(--god-accent);"></i>
                    <div>
                        <h4 class="mb-0">God Mode</h4>
                        <small class="opacity-75">Super Admin Control Panel</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-warning text-dark">
                        <i class="fas fa-shield-alt me-1"></i>
                        <?php echo ucfirst($current_admin['role'] ?? 'Admin'); ?>
                    </span>
                    <span class="text-light"><?php echo $current_admin['name'] ?? 'Unknown'; ?></span>
                    <a href="/admin/dashboard" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Back to Admin
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="container py-4">
        <!-- System Stats -->
        <div class="row mb-4">
            <div class="col-md-2 col-6">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['total_users'] ?? 0; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['total_leads'] ?? 0; ?></div>
                    <div class="stat-label">Total Leads</div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['total_properties'] ?? 0; ?></div>
                    <div class="stat-label">Properties</div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['total_commissions'] ?? 0; ?></div>
                    <div class="stat-label">Commissions</div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['active_sessions'] ?? 0; ?></div>
                    <div class="stat-label">Active Sessions</div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['failed_logins_24h'] ?? 0; ?></div>
                    <div class="stat-label">Failed Logins</div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- User Impersonation -->
            <div class="col-lg-6">
                <div class="god-card">
                    <div class="god-card-header">
                        <i class="fas fa-user-secret"></i>
                        User Impersonation
                    </div>
                    <p class="text-muted mb-3">Temporarily login as any user to see what they see.</p>
                    
                    <div class="input-group mb-3">
                        <input type="text" class="search-box" id="userSearch" placeholder="Search users by name, email, or phone...">
                        <button class="btn btn-god" onclick="searchUsers()">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted">Quick Filters:</small>
                        <div class="mt-2">
                            <span class="badge bg-secondary me-1" style="cursor: pointer;" onclick="filterUsers('customer')">Customers</span>
                            <span class="badge bg-secondary me-1" style="cursor: pointer;" onclick="filterUsers('associate')">Associates</span>
                            <span class="badge bg-secondary me-1" style="cursor: pointer;" onclick="filterUsers('agent')">Agents</span>
                            <span class="badge bg-secondary me-1" style="cursor: pointer;" onclick="filterUsers('employee')">Employees</span>
                        </div>
                    </div>
                    
                    <div id="usersList" style="max-height: 400px; overflow-y: auto;">
                        <?php foreach ($users as $user): ?>
                        <div class="user-card" data-user-id="<?php echo $user['id']; ?>">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <div class="fw-bold"><?php echo htmlspecialchars($user['name'] ?? 'Unknown'); ?></div>
                                    <div class="small text-muted"><?php echo htmlspecialchars($user['email']); ?></div>
                                    <span class="badge bg-<?php echo $user['role'] === 'customer' ? 'primary' : ($user['role'] === 'associate' ? 'success' : 'info'); ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </div>
                            </div>
                            <button class="btn btn-god btn-sm" onclick="impersonateUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['name'] ?? 'User'); ?>')">
                                <i class="fas fa-sign-in-alt me-1"></i>Impersonate
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Role Switching -->
            <div class="col-lg-6">
                <div class="god-card">
                    <div class="god-card-header">
                        <i class="fas fa-exchange-alt"></i>
                        Role Switching
                    </div>
                    <p class="text-muted mb-3">Experience the system from different role perspectives.</p>
                    
                    <div class="mb-4">
                        <small class="text-muted">Select a role to switch:</small>
                        <div class="mt-3">
                            <?php foreach ($roles as $role): ?>
                            <span class="role-badge <?php echo ($tempRole === $role['id']) ? 'active' : ''; ?>" onclick="switchRole('<?php echo $role['id']; ?>')">
                                <i class="fas <?php echo $role['icon']; ?>"></i>
                                <?php echo $role['name']; ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="alert alert-info" style="background: rgba(59, 130, 246, 0.2); border: 1px solid rgba(59, 130, 246, 0.5);">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Current Role:</strong> <?php echo ucfirst($_SESSION['admin_role'] ?? 'admin'); ?><br>
                        <small>Your permissions will change based on the selected role.</small>
                    </div>
                </div>

                <!-- System Commands -->
                <div class="god-card">
                    <div class="god-card-header">
                        <i class="fas fa-terminal"></i>
                        System Commands
                    </div>
                    <p class="text-muted mb-3">Execute powerful system maintenance commands.</p>
                    
                    <button class="command-btn" onclick="executeCommand('clear_cache')">
                        <i class="fas fa-broom"></i>
                        <strong>Clear Cache</strong>
                        <small class="d-block text-muted">Clear all file and memory caches</small>
                    </button>
                    
                    <button class="command-btn" onclick="executeCommand('clear_logs')">
                        <i class="fas fa-archive"></i>
                        <strong>Archive Old Logs</strong>
                        <small class="d-block text-muted">Archive logs older than 30 days</small>
                    </button>
                    
                    <button class="command-btn" onclick="executeCommand('optimize_database')">
                        <i class="fas fa-database"></i>
                        <strong>Optimize Database</strong>
                        <small class="d-block text-muted">Run OPTIMIZE TABLE on all tables</small>
                    </button>
                    
                    <button class="command-btn" onclick="executeCommand('reset_failed_logins')">
                        <i class="fas fa-shield-alt"></i>
                        <strong>Reset Failed Logins</strong>
                        <small class="d-block text-muted">Clear failed login attempts counter</small>
                    </button>
                    
                    <button class="command-btn" onclick="executeCommand('sync_permissions')">
                        <i class="fas fa-sync"></i>
                        <strong>Sync Permissions</strong>
                        <small class="d-block text-muted">Synchronize RBAC permissions cache</small>
                    </button>
                </div>
            </div>
        </div>

        <!-- Active Impersonations -->
        <?php if (!empty($impersonations)): ?>
        <div class="god-card mt-4">
            <div class="god-card-header">
                <i class="fas fa-users-cog"></i>
                Active Impersonation Sessions
            </div>
            <div class="table-responsive">
                <table class="table table-dark table-hover">
                    <thead>
                        <tr>
                            <th>Admin ID</th>
                            <th>Impersonating User</th>
                            <th>Started</th>
                            <th>Duration</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($impersonations as $imp): ?>
                        <tr>
                            <td><?php echo $imp['admin_id']; ?></td>
                            <td><?php echo $imp['user_id']; ?></td>
                            <td><?php echo date('Y-m-d H:i:s', $imp['start_time']); ?></td>
                            <td><?php echo human_time_diff($imp['start_time'], time()); ?></td>
                            <td>
                                <button class="btn btn-warning-god btn-sm" onclick="stopImpersonation()">
                                    <i class="fas fa-stop"></i> Stop
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- System Health -->
        <div class="god-card mt-4">
            <div class="god-card-header">
                <i class="fas fa-heartbeat"></i>
                System Health
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="status-indicator status-active"></span>
                        <div>
                            <div class="fw-bold">Database</div>
                            <small class="text-muted">Connection OK</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="status-indicator status-active"></span>
                        <div>
                            <div class="fw-bold">Storage</div>
                            <small class="text-muted">85% Used</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="status-indicator status-warning"></span>
                        <div>
                            <div class="fw-bold">Memory</div>
                            <small class="text-muted">High Usage</small>
                        </div>
                    </div>
                </div>
            </div>
            <button class="btn btn-outline-light btn-sm" onclick="checkSystemHealth()">
                <i class="fas fa-sync me-1"></i>Refresh Health Check
            </button>
        </div>

        <!-- Audit Logs -->
        <div class="god-card mt-4">
            <div class="god-card-header">
                <i class="fas fa-history"></i>
                Recent God Mode Activity
            </div>
            <div id="auditLogs">
                <div class="log-entry">
                    <span class="text-muted">[<?php echo date('H:i:s'); ?>]</span>
                    <span class="text-warning">God Mode dashboard accessed</span>
                    <span class="text-muted">by <?php echo $current_admin['name'] ?? 'Unknown'; ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Impersonate Confirmation Modal -->
    <div class="modal fade modal-god" id="impersonateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-secret me-2"></i>Confirm Impersonation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to impersonate <strong id="impersonateUserName">User</strong>?</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> Your actions will be logged as this user. Use responsibly.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-god" onclick="confirmImpersonate()">
                        <i class="fas fa-sign-in-alt me-1"></i>Start Impersonation
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const baseUrl = '<?php echo $base; ?>';
        let selectedUserId = null;
        
        // Show toast notification
        function showToast(message, type = 'info') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = 'god-toast';
            toast.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                ${message}
            `;
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        }
        
        // Impersonate user
        function impersonateUser(userId, userName) {
            selectedUserId = userId;
            document.getElementById('impersonateUserName').textContent = userName;
            new bootstrap.Modal(document.getElementById('impersonateModal')).show();
        }
        
        // Confirm impersonation
        function confirmImpersonate() {
            if (!selectedUserId) return;
            
            fetch(`${baseUrl}/admin/godmode/impersonate/${selectedUserId}`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => window.location.href = data.redirect, 1000);
                } else {
                    showToast(data.error || 'Failed to impersonate', 'error');
                }
            })
            .catch(err => {
                showToast('Error: ' + err.message, 'error');
            });
            
            bootstrap.Modal.getInstance(document.getElementById('impersonateModal')).hide();
        }
        
        // Stop impersonation
        function stopImpersonation() {
            fetch(`${baseUrl}/admin/godmode/stop-impersonation`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1000);
                }
            })
            .catch(err => showToast('Error stopping impersonation', 'error'));
        }
        
        // Switch role
        function switchRole(role) {
            if (!confirm(`Switch to ${role} role? This will change your permissions.`)) return;
            
            fetch(`${baseUrl}/admin/godmode/switch-role`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `role=${role}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast(data.error || 'Failed to switch role', 'error');
                }
            })
            .catch(err => showToast('Error switching role', 'error'));
        }
        
        // Restore role
        function restoreRole() {
            fetch(`${baseUrl}/admin/godmode/restore-role`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1000);
                }
            })
            .catch(err => showToast('Error restoring role', 'error'));
        }
        
        // Execute system command
        function executeCommand(command) {
            if (!confirm(`Execute command: ${command}?`)) return;
            
            fetch(`${baseUrl}/admin/godmode/execute-command`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `command=${command}`
            })
            .then(r => r.json())
            .then(data => {
                showToast(data.result || data.message, data.success ? 'success' : 'error');
            })
            .catch(err => showToast('Error executing command', 'error'));
        }
        
        // Search users
        function searchUsers() {
            const query = document.getElementById('userSearch').value;
            showToast('Searching for: ' + query);
            // Implementation would fetch from API
        }
        
        // Filter users by role
        function filterUsers(role) {
            showToast('Filtering by role: ' + role);
            // Implementation would filter the list
        }
        
        // Check system health
        function checkSystemHealth() {
            fetch(`${baseUrl}/admin/godmode/system-health`)
                .then(r => r.json())
                .then(data => {
                    showToast('System health check completed', 'success');
                    console.log(data);
                })
                .catch(err => showToast('Error checking health', 'error'));
        }
        
        // Human time diff helper
        function human_time_diff(from, to) {
            const diff = to - from;
            const minutes = Math.floor(diff / 60);
            const hours = Math.floor(minutes / 60);
            
            if (hours > 0) return `${hours}h ${minutes % 60}m`;
            return `${minutes}m`;
        }
    </script>
</body>
</html>
