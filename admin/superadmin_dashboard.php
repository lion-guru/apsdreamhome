<?php
// Modern, AI-powered, feature-rich Super Admin Dashboard (2025 best practices)
require_once(__DIR__ . '/../admin/includes/session_manager.php');
require_once(__DIR__ . '/../admin/includes/superadmin_helpers.php');
initAdminSession();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
if (!isSuperAdmin()) {
    header('Location: index.php');
    exit();
}
$page_title = 'Super Admin Dashboard';
include __DIR__ . '/../includes/templates/dynamic_header.php';
// Example stats (replace with real queries)
$superadmin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Super Admin');
$stats = [
    'total_admins' => 5,
    'total_users' => 1200,
    'active_modules' => 8,
    'ai_tools' => 2,
    'backups' => 6,
    'audit_logs' => 120,
];
?>
<div class="container py-5">
    <div class="d-flex justify-content-end mb-3">
        <a href="logout.php" class="btn btn-outline-danger btn-sm"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    <h1 class="mb-4 text-center text-primary"><i class="fas fa-user-shield me-2"></i>Welcome, <?php echo $superadmin_name; ?>!</h1>
    <!-- Stats Cards -->
    <div class="dashboard-stats d-flex flex-wrap gap-3 justify-content-center mb-4">
        <div class="stat-box bg-primary text-white">
            <div class="fs-3 fw-bold"><?php echo $stats['total_admins']; ?></div><div>Admins</div>
        </div>
        <div class="stat-box bg-success text-white">
            <div class="fs-3 fw-bold"><?php echo $stats['total_users']; ?></div><div>Users</div>
        </div>
        <div class="stat-box bg-info text-white">
            <div class="fs-3 fw-bold"><?php echo $stats['active_modules']; ?></div><div>Active Modules</div>
        </div>
        <div class="stat-box bg-warning text-dark">
            <div class="fs-3 fw-bold"><?php echo $stats['ai_tools']; ?></div><div>AI Tools</div>
        </div>
        <div class="stat-box bg-secondary text-white">
            <div class="fs-3 fw-bold"><?php echo $stats['backups']; ?></div><div>Backups</div>
        </div>
        <div class="stat-box bg-danger text-white">
            <div class="fs-3 fw-bold"><?php echo $stats['audit_logs']; ?></div><div>Audit Logs</div>
        </div>
    </div>
    <!-- AI Chatbot Panel -->
    <div class="ai-chatbot mb-4">
        <strong><i class="fa-solid fa-robot"></i> Ask AI (Chatbot):</strong>
        <form id="aiChatForm" class="d-flex mt-2" onsubmit="return false;">
            <input type="text" class="form-control me-2" id="aiChatInput" placeholder="Ask about users, modules, AI, or system health...">
            <button class="btn btn-primary" onclick="sendAIQuery()"><i class="fa-solid fa-paper-plane"></i></button>
        </form>
        <div id="aiChatResponse" class="mt-2 text-secondary small">Try: "Show recent admin logins"</div>
    </div>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0">
                <div class="card-body">
                    <h2 class="card-title mb-4 text-center text-primary"><i class="fas fa-user-shield me-2"></i>Super Admin Controls</h2>
                    <p class="lead text-center mb-4">
                        You have full control over this real estate management system.<br>
                        Use the controls below to manage admins, users, roles, modules, AI/configuration, and more.
                    </p>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <a href="adminlist.php" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-users-cog fa-lg me-2"></i> Manage Admins/Users
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="register.php" class="btn btn-outline-success w-100 py-3">
                                <i class="fas fa-user-plus fa-lg me-2"></i> Register Admin
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="ai_admin_insights.php" class="btn btn-outline-warning w-100 py-3">
                                <i class="fas fa-robot fa-lg me-2"></i> Configure AI & Critical Settings
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="activity_log.php" class="btn btn-outline-danger w-100 py-3">
                                <i class="fas fa-clipboard-list fa-lg me-2"></i> View Audit Logs
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="backup_manager.php" class="btn btn-outline-info w-100 py-3">
                                <i class="fas fa-database fa-lg me-2"></i> Backup Manager
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="header_footer_settings.php" class="btn btn-outline-secondary w-100 py-3">
                                <i class="fas fa-cogs fa-lg me-2"></i> Site Settings
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="2fa_setup.php" class="btn btn-outline-dark w-100 py-3">
                                <i class="fas fa-shield-alt fa-lg me-2"></i> Two-Factor Authentication
                            </a>
                        </div>
                    </div>
                    <hr class="my-4">
                    <div class="dashboard-card p-4 bg-white rounded shadow-sm">
                        <h4 class="mb-3"><i class="fa fa-magic me-2"></i>AI Suggestions & Reminders</h4>
                        <div id="aiSuggestionsPanel">
                            <div class="text-center text-muted">Loading personalized suggestions...</div>
                        </div>
                    </div>
                    <div class="dashboard-card">
                        <h4><i class="fa-solid fa-share-nodes"></i> Export & Share</h4>
                        <button class="btn btn-outline-secondary me-2"><i class="fa-solid fa-file-csv"></i> Export CSV</button>
                        <button class="btn btn-outline-secondary me-2"><i class="fa-solid fa-file-pdf"></i> Export PDF</button>
                        <button class="btn btn-outline-secondary"><i class="fa-solid fa-qrcode"></i> Share via QR</button>
                    </div>
                    <div class="alert alert-info text-center mb-0 mt-4">
                        <i class="fas fa-info-circle me-2"></i>
                        Only Super Admins can access this dashboard and perform critical actions.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Super Admin Control Panel Section -->
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><i class="fa fa-user-shield"></i> Super Admin Control Panel</h3>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-3">
                            <a href="manage_users.php" class="btn btn-outline-primary w-100 mb-2"><i class="fa fa-users"></i> Manage Users</a>
                            <a href="fetch_permissions.php" class="btn btn-outline-info w-100 mb-2"><i class="fa fa-key"></i> Manage Permissions</a>
                            <a href="fetch_settings.php" class="btn btn-outline-secondary w-100 mb-2"><i class="fa fa-cogs"></i> Site Settings</a>
                            <a href="fetch_ai_settings.php" class="btn btn-outline-warning w-100 mb-2"><i class="fa fa-robot"></i> AI/Automation Settings</a>
                        </div>
                        <div class="col-md-3">
                            <a href="audit_access_log_view.php" class="btn btn-outline-danger w-100 mb-2"><i class="fa fa-clipboard-list"></i> Audit Logs</a>
                            <a href="log_archive_view.php" class="btn btn-outline-dark w-100 mb-2"><i class="fa fa-archive"></i> Log Archives</a>
                            <a href="backup_manager.php" class="btn btn-outline-success w-100 mb-2"><i class="fa fa-database"></i> Backups</a>
                        </div>
                        <div class="col-md-3">
                            <a href="admin_dashboard.php" class="btn btn-outline-primary w-100 mb-2"><i class="fa fa-gauge"></i> Admin Dashboard</a>
                            <a href="employee_dashboard.php" class="btn btn-outline-secondary w-100 mb-2"><i class="fa fa-briefcase"></i> Employee Dashboard</a>
                            <a href="index.php" class="btn btn-outline-info w-100 mb-2"><i class="fa fa-home"></i> Main Dashboard</a>
                        </div>
                        <div class="col-md-3">
                            <a href="userlist.php" class="btn btn-outline-primary w-100 mb-2"><i class="fa fa-list"></i> All Users List</a>
                            <a href="adminedit.php" class="btn btn-outline-warning w-100 mb-2"><i class="fa fa-user-edit"></i> Edit Admins/Employees</a>
                            <a href="dashboard.php" class="btn btn-outline-success w-100 mb-2"><i class="fa fa-tachometer-alt"></i> All Dashboards</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-12">
            <div class="alert alert-info">
                <strong>Tip:</strong> As Super Admin, you have access to all controls, analytics, user/role management, settings, backups, and audit logs. Use the above shortcuts for one-click access to all features.
            </div>
        </div>
    </div>
</div>
<!-- Visual Editor and Component Library -->
<div class="wrapper">
    <nav id="sidebar">
        <div class="sidebar-header">
            <h3>Super Admin Panel</h3>
        </div>
        <ul class="list-unstyled components">
            <li class="active">
                <a href="#contentSubmenu" data-toggle="collapse" aria-expanded="false">
                    <i class="fas fa-edit"></i> Content Management
                </a>
                <ul class="collapse list-unstyled" id="contentSubmenu">
                    <li><a href="#" data-section="pages">Pages</a></li>
                    <li><a href="#" data-section="posts">Posts</a></li>
                    <li><a href="#" data-section="media">Media</a></li>
                </ul>
            </li>
            <li>
                <a href="#layoutSubmenu" data-toggle="collapse" aria-expanded="false">
                    <i class="fas fa-layer-group"></i> Layout Management
                </a>
                <ul class="collapse list-unstyled" id="layoutSubmenu">
                    <li><a href="#" data-section="templates">Templates</a></li>
                    <li><a href="#" data-section="components">Components</a></li>
                </ul>
            </li>
            <li>
                <a href="#settingsSubmenu" data-toggle="collapse" aria-expanded="false">
                    <i class="fas fa-cogs"></i> Settings
                </a>
                <ul class="collapse list-unstyled" id="settingsSubmenu">
                    <li><a href="#" data-section="general">General</a></li>
                    <li><a href="#" data-section="security">Security</a></li>
                </ul>
            </li>
        </ul>
    </nav>
    <div id="content">
        <div class="visual-editor mb-4">
            <h4>Visual Editor</h4>
            <div class="preview-area" id="previewArea">
                <!-- Drag and drop components here -->
            </div>
        </div>
        <div class="component-library">
            <h5>Component Library</h5>
            <div class="draggable-component">Header</div>
            <div class="draggable-component">Footer</div>
            <div class="draggable-component">Contact Form</div>
            <div class="draggable-component">Gallery</div>
            <div class="draggable-component">Testimonial</div>
        </div>
    </div>
</div>
<style>
    .dashboard-stats .stat-box { border-radius: .5rem; padding: 1.5rem 2rem; min-width: 160px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.07); font-size: 1.1rem; }
    .ai-chatbot { background: #eaf4ff; border-left: 5px solid #007bff; padding: 1.5rem; border-radius: .5rem; margin-bottom: 2rem; }
    .dashboard-card { box-shadow: 0 2px 8px rgba(0,0,0,0.07); border-radius: 1rem; background: #fff; padding: 2rem; margin-bottom: 2rem; }
</style>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// AI Chatbot JS (simulate response)
function sendAIQuery() {
    const input = document.getElementById('aiChatInput').value.trim();
    if (!input) return;
    document.getElementById('aiChatResponse').innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Thinking...';
    setTimeout(() => {
        document.getElementById('aiChatResponse').innerHTML = '<b>AI:</b> This is a sample AI-powered answer to: <code>' + input + '</code>';
    }, 1200);
}
// AI Suggestions Panel (AJAX)
function logAIInteraction(action, suggestion, feedback, notes) {
    $.post('admin/log_ai_interaction.php', {
        action: action,
        suggestion: suggestion,
        feedback: feedback||'',
        notes: notes||''
    });
}
$(function(){
    $.get('user_ai_suggestions.php', function(resp) {
        if(resp.success) {
            let html = '';
            if(resp.status && resp.status.length) {
                html += '<div class="mb-2"><b>Reminders:</b><ul>';
                resp.status.forEach(function(rem) { html += '<li>'+rem+' <span class="badge bg-light text-dark pointer ms-1" onclick="logAIInteraction(\'feedback\', `'+rem.replace(/'/g,"&#39;")+'`,\'like\')">👍</span> <span class="badge bg-light text-dark pointer" onclick="logAIInteraction(\'feedback\', `'+rem.replace(/'/g,"&#39;")+'`,\'dislike\')">👎</span></li>'; });
                html += '</ul></div>';
            }
            if(resp.suggestions && resp.suggestions.length) {
                html += '<div><b>AI Suggestions:</b><ul>';
                resp.suggestions.forEach(function(sugg) { html += '<li>'+sugg+' <span class="badge bg-light text-dark pointer ms-1" onclick="logAIInteraction(\'feedback\', `'+sugg.replace(/'/g,"&#39;")+'`,\'like\')">👍</span> <span class="badge bg-light text-dark pointer" onclick="logAIInteraction(\'feedback\', `'+sugg.replace(/'/g,"&#39;")+'`,\'dislike\')">👎</span></li>'; });
                html += '</ul></div>';
            }
            if(!html) html = '<div class="text-success">You are all caught up!</div>';
            $('#aiSuggestionsPanel').html(html);
            if(resp.suggestions) resp.suggestions.forEach(function(sugg){ logAIInteraction('view', sugg); });
            if(resp.status) resp.status.forEach(function(rem){ logAIInteraction('view', rem); });
        } else {
            $('#aiSuggestionsPanel').html('<div class="text-danger">Could not load suggestions.</div>');
        }
    },'json');
});
</script>
<script src="assets/plugins/sortable/Sortable.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize drag and drop
    const previewArea = document.getElementById('previewArea');
    new Sortable(previewArea, {
        group: {
            name: 'shared',
            pull: true,
            put: true
        },
        animation: 150,
        onEnd: function (evt) {
            // Save layout order
        }
    });
});
</script>
<?php include __DIR__ . '/../includes/templates/new_footer.php'; ?>
