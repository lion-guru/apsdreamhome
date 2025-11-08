<?php
/**
 * Modern SuperAdmin Dashboard - APS Dream Home
 * Mobile-First, Responsive Design with Modern UI/UX
 */

session_start();

// Security check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || $_SESSION['admin_role'] !== 'superadmin') {
    header('Location: index.php?error=unauthorized');
    exit();
}

// Include universal dashboard template
require_once 'includes/universal_dashboard_template.php';

// SuperAdmin specific statistics
$stats = [
    [
        'icon' => 'fas fa-users-cog',
        'value' => '5',
        'label' => 'Total Admins',
        'change' => '+1 this month',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-users',
        'value' => '1,247',
        'label' => 'Total Users',
        'change' => '+156 this month',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-server',
        'value' => '8',
        'label' => 'Active Modules',
        'change' => 'All systems operational',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-shield-alt',
        'value' => '99.9%',
        'label' => 'System Uptime',
        'change' => 'Excellent',
        'change_type' => 'positive'
    ]
];

// SuperAdmin quick actions
$quick_actions = [
    [
        'title' => 'Manage Users',
        'description' => 'Add, edit, and manage all users',
        'icon' => 'fas fa-users-cog',
        'url' => 'manage_users.php'
    ],
    [
        'title' => 'System Settings',
        'description' => 'Configure system-wide settings',
        'icon' => 'fas fa-cogs',
        'url' => 'header_footer_settings.php'
    ],
    [
        'title' => 'Audit Logs',
        'description' => 'View security and activity logs',
        'icon' => 'fas fa-clipboard-list',
        'url' => 'audit_access_log_view.php'
    ],
    [
        'title' => 'Backup Manager',
        'description' => 'Manage system backups',
        'icon' => 'fas fa-database',
        'url' => 'backup_manager.php'
    ],
    [
        'title' => 'AI Configuration',
        'description' => 'Configure AI and automation',
        'icon' => 'fas fa-robot',
        'url' => 'ai_admin_insights.php'
    ],
    [
        'title' => 'Security Center',
        'description' => 'Two-factor auth and security',
        'icon' => 'fas fa-shield-alt',
        'url' => '2fa_setup.php'
    ]
];

// Recent activities
$recent_activities = [
    [
        'icon' => 'fas fa-user-plus',
        'title' => 'New Admin Added',
        'description' => 'HR Manager role created for Sarah Johnson',
        'time' => '5 mins ago'
    ],
    [
        'icon' => 'fas fa-database',
        'title' => 'Backup Completed',
        'description' => 'Automated system backup completed successfully',
        'time' => '1 hour ago'
    ],
    [
        'icon' => 'fas fa-shield-alt',
        'title' => 'Security Scan',
        'description' => 'Weekly security scan completed - No issues found',
        'time' => '2 hours ago'
    ]
];

// Generate and display the dashboard
echo generateUniversalDashboard('superadmin', $stats, $quick_actions, $recent_activities);
?>
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
