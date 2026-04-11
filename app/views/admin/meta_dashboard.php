<?php

/**
 * SUPER ADMIN META DASHBOARD
 * The Ultimate Control Center for APS Dream Home
 */

// Get current user info
$admin_name = $_SESSION['admin_name'] ?? 'Super Admin';
$admin_role = $_SESSION['admin_role'] ?? 'super_admin';
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';

// Get database instance
$db = \App\Core\Database::getInstance();

// Get stats
$totalUsers = $db->fetchOne("SELECT COUNT(*) as count FROM users")['count'] ?? 0;
$todayUsers = $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()")['count'] ?? 0;
$todayLeads = $db->fetchOne("SELECT COUNT(*) as count FROM leads WHERE DATE(created_at) = CURDATE()")['count'] ?? 0;
$pendingProperties = $db->fetchOne("SELECT COUNT(*) as count FROM user_properties WHERE status = 'pending'")['count'] ?? 0;
$pendingPayouts = $db->fetchOne("SELECT COUNT(*) as count FROM payouts WHERE status = 'pending'")['count'] ?? 0;
$totalRevenue = $db->fetchOne("SELECT SUM(amount) as total FROM payments WHERE status = 'completed'")['total'] ?? 0;

// AI Chatbot Stats
$todayChats = $db->fetchOne("SELECT COUNT(*) as count FROM ai_conversations WHERE DATE(created_at) = CURDATE()")['count'] ?? 0;
$totalChats = $db->fetchOne("SELECT COUNT(*) as count FROM ai_conversations")['count'] ?? 0;
$totalQA = $db->fetchOne("SELECT COUNT(*) as count FROM ai_knowledge_base")['count'] ?? 0;
$mostAsked = $db->fetchAll("SELECT question_pattern, usage_count FROM ai_knowledge_base WHERE usage_count > 0 ORDER BY usage_count DESC LIMIT 5");

// Get users by role
$roleStats = $db->fetchAll("SELECT role, user_type, COUNT(*) as count FROM users GROUP BY role, user_type");

// All 14 role dashboards
$roleDashboards = [
    ['name' => 'Super Admin', 'role' => 'super_admin', 'icon' => 'fa-crown', 'color' => 'warning', 'url' => '/admin/dashboard'],
    ['name' => 'CEO', 'role' => 'ceo', 'icon' => 'fa-user-tie', 'color' => 'primary', 'url' => '/admin/ceo-dashboard'],
    ['name' => 'CFO', 'role' => 'cfo', 'icon' => 'fa-chart-line', 'color' => 'success', 'url' => '/admin/cfo-dashboard'],
    ['name' => 'CMO', 'role' => 'cmo', 'icon' => 'fa-bullhorn', 'color' => 'info', 'url' => '/admin/cm-dashboard'],
    ['name' => 'COO', 'role' => 'coo', 'icon' => 'fa-cogs', 'color' => 'danger', 'url' => '/admin/coo-dashboard'],
    ['name' => 'CTO', 'role' => 'cto', 'icon' => 'fa-laptop-code', 'color' => 'secondary', 'url' => '/admin/cto-dashboard'],
    ['name' => 'CHRO', 'role' => 'chro', 'icon' => 'fa-users', 'color' => 'dark', 'url' => '/admin/hr-dashboard'],
    ['name' => 'Director', 'role' => 'director', 'icon' => 'fa-user-shield', 'color' => 'primary', 'url' => '/admin/director-dashboard'],
    ['name' => 'Finance Head', 'role' => 'finance_head', 'icon' => 'fa-rupee-sign', 'color' => 'success', 'url' => '/admin/finance-dashboard'],
    ['name' => 'IT Head', 'role' => 'it_head', 'icon' => 'fa-server', 'color' => 'info', 'url' => '/admin/it-dashboard'],
    ['name' => 'Marketing Head', 'role' => 'marketing_head', 'icon' => 'fa-ad', 'color' => 'warning', 'url' => '/admin/marketing-dashboard'],
    ['name' => 'Operations Head', 'role' => 'operations_head', 'icon' => 'fa-tasks', 'color' => 'danger', 'url' => '/admin/operations-dashboard'],
    ['name' => 'Sales Head', 'role' => 'sales_head', 'icon' => 'fa-handshake', 'color' => 'primary', 'url' => '/admin/sales-dashboard'],
    ['name' => 'Agent', 'role' => 'agent', 'icon' => 'fa-headset', 'color' => 'success', 'url' => '/agent/dashboard'],
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Meta Dashboard | APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
        }

        .god-mode-badge {
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: #000;
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: bold;
            z-index: 9999;
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.4);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .super-card {
            border: none;
            border-radius: 16px;
            transition: all 0.3s;
        }

        .super-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .role-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #1e1b4b 0%, #312e81 100%);
        }
    </style>
</head>

<body>
    <!-- GOD MODE BADGE -->
    <div class="god-mode-badge">
        <i class="fas fa-crown me-2"></i>GOD MODE
    </div>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="p-4 text-white">
                    <h5 class="mb-4"><i class="fas fa-home me-2"></i>APS Dream Home</h5>
                    <div class="text-white-50 small mb-4">Super Admin Panel</div>

                    <ul class="nav flex-column">
                        <li class="nav-item mb-2">
                            <a class="nav-link text-white active bg-primary rounded" href="#">
                                <i class="fas fa-infinity me-2"></i>Meta Dashboard
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a class="nav-link text-white" href="<?php echo $base; ?>/admin/dashboard">
                                <i class="fas fa-chart-pie me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a class="nav-link text-white" href="<?php echo $base; ?>/admin/users">
                                <i class="fas fa-users me-2"></i>All Users
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a class="nav-link text-white" href="<?php echo $base; ?>/admin/user-properties">
                                <i class="fas fa-home me-2"></i>Properties
                                <?php if ($pendingProperties > 0): ?>
                                    <span class="badge bg-danger ms-2"><?php echo $pendingProperties; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a class="nav-link text-white" href="<?php echo $base; ?>/admin/payouts">
                                <i class="fas fa-money-bill-wave me-2"></i>Payouts
                                <?php if ($pendingPayouts > 0): ?>
                                    <span class="badge bg-warning ms-2"><?php echo $pendingPayouts; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a class="nav-link text-white" href="<?php echo $base; ?>/admin/settings">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a class="nav-link text-white" href="<?php echo $base; ?>/admin/ai-training">
                                <i class="fas fa-robot me-2"></i>AI Training
                                <span class="badge bg-info ms-2">New</span>
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a class="nav-link text-white" href="<?php echo $base; ?>/admin/whatsapp-integration">
                                <i class="fab fa-whatsapp me-2"></i>WhatsApp
                                <span class="badge bg-success ms-2">New</span>
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <a class="nav-link text-danger" href="<?php echo $base; ?>/admin/logout">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-1"><i class="fas fa-infinity text-warning me-2"></i>Super Admin Meta Dashboard</h2>
                        <p class="text-muted">Control everything. Be anyone. Manage all.</p>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <!-- Notifications -->
                        <div class="dropdown">
                            <button class="btn btn-outline-primary position-relative" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-bell"></i>
                                <?php
                                $notificationCount = count($_SESSION['notifications'] ?? []);
                                if ($notificationCount > 0):
                                ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        <?php echo $notificationCount > 9 ? '9+' : $notificationCount; ?>
                                    </span>
                                <?php endif; ?>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 300px; max-height: 400px; overflow-y: auto;">
                                <h6 class="dropdown-header">Notifications</h6>
                                <?php
                                $notifications = array_reverse($_SESSION['notifications'] ?? []);
                                $notifications = array_slice($notifications, 0, 10);
                                if (count($notifications) > 0):
                                    foreach ($notifications as $notif):
                                ?>
                                        <div class="dropdown-item-text border-bottom pb-2 mb-2">
                                            <div class="d-flex align-items-start">
                                                <i class="fas fa-<?php echo $notif['type'] === 'success' ? 'check-circle text-success' : 'info-circle text-info'; ?> me-2 mt-1"></i>
                                                <div>
                                                    <small class="d-block"><?php echo htmlspecialchars($notif['message']); ?></small>
                                                    <small class="text-muted"><?php echo date('M j, g:i a', strtotime($notif['time'])); ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php
                                    endforeach;
                                else:
                                    ?>
                                    <div class="text-center text-muted py-3">
                                        <i class="fas fa-bell-slash mb-2"></i>
                                        <p class="mb-0 small">No new notifications</p>
                                    </div>
                                <?php endif; ?>
                                <div class="dropdown-divider"></div>
                                <a href="<?php echo $base; ?>/admin/ai-training" class="dropdown-item text-center small text-primary">
                                    <i class="fas fa-robot me-1"></i>AI Training Center
                                </a>
                            </div>
                        </div>
                        <span class="badge bg-primary"><?php echo $admin_name; ?></span>
                        <span class="badge bg-warning text-dark"><?php echo strtoupper($admin_role); ?></span>
                    </div>
                </div>

                <!-- Alerts -->
                <?php if ($pendingProperties > 0 || $pendingPayouts > 0): ?>
                    <div class="alert alert-warning alert-dismissible fade show mb-4">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Action Required:</strong>
                        <?php if ($pendingProperties > 0): ?>
                            <span class="badge bg-danger ms-2"><?php echo $pendingProperties; ?> Properties Pending</span>
                            <a href="<?php echo $base; ?>/admin/user-properties" class="btn btn-sm btn-danger ms-2">Review Now</a>
                        <?php endif; ?>
                        <?php if ($pendingPayouts > 0): ?>
                            <span class="badge bg-warning ms-2"><?php echo $pendingPayouts; ?> Payouts Pending</span>
                            <a href="<?php echo $base; ?>/admin/payouts" class="btn btn-sm btn-warning ms-2">Process Now</a>
                        <?php endif; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Stats Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="card super-card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-white-50">Total Users</h6>
                                        <h3><?php echo number_format($totalUsers); ?></h3>
                                        <small>+<?php echo $todayUsers; ?> today</small>
                                    </div>
                                    <i class="fas fa-users fa-3x text-white-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card super-card" style="background: linear-gradient(135deg, #11998e, #38ef7d); color: white;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-white-50">Today's Leads</h6>
                                        <h3><?php echo number_format($todayLeads); ?></h3>
                                    </div>
                                    <i class="fas fa-bullseye fa-3x text-white-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card super-card" style="background: linear-gradient(135deg, #fc4a1a, #f7b733); color: white;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-white-50">Total Revenue</h6>
                                        <h3>₹<?php echo number_format($totalRevenue, 2); ?></h3>
                                    </div>
                                    <i class="fas fa-rupee-sign fa-3x text-white-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card super-card" style="background: linear-gradient(135deg, #4facfe, #00f2fe); color: white;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-white-50">System Status</h6>
                                        <h3 class="text-success"><i class="fas fa-check-circle"></i></h3>
                                        <small>Online</small>
                                    </div>
                                    <i class="fas fa-server fa-3x text-white-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- AI Chatbot Analytics -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="card super-card" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-white-50">Today's Chats</h6>
                                        <h3><?php echo number_format($todayChats); ?></h3>
                                        <small><a href="<?php echo $base; ?>/admin/ai-training" class="text-white">View Analytics →</a></small>
                                    </div>
                                    <i class="fas fa-comments fa-3x text-white-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card super-card" style="background: linear-gradient(135deg, #f093fb, #f5576c); color: white;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-white-50">Total Chats</h6>
                                        <h3><?php echo number_format($totalChats); ?></h3>
                                        <small class="text-white">All time</small>
                                    </div>
                                    <i class="fas fa-robot fa-3x text-white-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card super-card" style="background: linear-gradient(135deg, #4facfe, #00f2fe); color: white;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-white-50">Q&A Patterns</h6>
                                        <h3><?php echo number_format($totalQA); ?></h3>
                                        <small><a href="<?php echo $base; ?>/admin/ai-training" class="text-white">Manage →</a></small>
                                    </div>
                                    <i class="fas fa-brain fa-3x text-white-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card super-card" style="background: linear-gradient(135deg, #43e97b, #38f9d7); color: white;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-white-50">Bot Status</h6>
                                        <h3 class="text-white"><i class="fas fa-circle text-success"></i></h3>
                                        <small class="text-white">Active</small>
                                    </div>
                                    <i class="fas fa-check-circle fa-3x text-white-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Role Dashboards -->
                <div class="card super-card mb-4">
                    <div class="card-header bg-transparent py-3 d-flex justify-content-between">
                        <h5 class="mb-0"><i class="fas fa-layer-group me-2 text-primary"></i>All 14 Role Dashboards</h5>
                        <span class="text-muted small">Click to view as that role</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <?php foreach ($roleDashboards as $dashboard): ?>
                                <?php
                                $userCount = 0;
                                foreach ($roleStats as $stat) {
                                    if ($stat['role'] === $dashboard['role'] || $stat['user_type'] === $dashboard['role']) {
                                        $userCount += $stat['count'];
                                    }
                                }
                                ?>
                                <div class="col-md-4 col-lg-3">
                                    <div class="card h-100 border-0 shadow-sm hover-shadow">
                                        <div class="card-body text-center">
                                            <div class="role-icon bg-<?php echo $dashboard['color']; ?> bg-opacity-10 text-<?php echo $dashboard['color']; ?> mx-auto mb-3">
                                                <i class="fas <?php echo $dashboard['icon']; ?>"></i>
                                            </div>
                                            <h5 class="card-title"><?php echo $dashboard['name']; ?></h5>
                                            <p class="text-muted small mb-2"><?php echo $userCount; ?> Users</p>
                                            <div class="d-grid gap-2">
                                                <a href="<?php echo $base . $dashboard['url']; ?>" class="btn btn-sm btn-outline-<?php echo $dashboard['color']; ?>">
                                                    <i class="fas fa-eye me-1"></i>View
                                                </a>
                                                <a href="<?php echo $base; ?>/admin/users?role=<?php echo $dashboard['role']; ?>" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-users me-1"></i>Manage
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card super-card">
                            <div class="card-header bg-transparent">
                                <h5><i class="fas fa-bolt me-2 text-warning"></i>Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="<?php echo $base; ?>/admin/users/create" class="btn btn-outline-primary">
                                        <i class="fas fa-user-plus me-2"></i>Create New User
                                    </a>
                                    <a href="<?php echo $base; ?>/admin/leads/create" class="btn btn-outline-success">
                                        <i class="fas fa-bullseye me-2"></i>Add Lead
                                    </a>
                                    <a href="<?php echo $base; ?>/admin/properties/create" class="btn btn-outline-info">
                                        <i class="fas fa-building me-2"></i>Add Property
                                    </a>
                                    <a href="<?php echo $base; ?>/admin/visits/create" class="btn btn-outline-warning">
                                        <i class="fas fa-car me-2"></i>Schedule Visit
                                    </a>
                                    <a href="<?php echo $base; ?>/admin/reports" class="btn btn-outline-dark">
                                        <i class="fas fa-chart-bar me-2"></i>View Reports
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card super-card">
                            <div class="card-header bg-transparent">
                                <h5><i class="fas fa-crown me-2 text-warning"></i>Super Powers</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="<?php echo $base; ?>/admin/menu-permissions" class="btn btn-outline-primary">
                                        <i class="fas fa-lock me-2"></i>Manage RBAC Menu
                                    </a>
                                    <a href="<?php echo $base; ?>/admin/api-keys" class="btn btn-outline-success">
                                        <i class="fas fa-key me-2"></i>API Keys
                                    </a>
                                    <a href="<?php echo $base; ?>/admin/backup" class="btn btn-outline-info">
                                        <i class="fas fa-database me-2"></i>Backup Database
                                    </a>
                                    <a href="<?php echo $base; ?>/admin/system-logs" class="btn btn-outline-secondary">
                                        <i class="fas fa-file-alt me-2"></i>System Logs
                                    </a>
                                    <a href="<?php echo $base; ?>/admin/settings" class="btn btn-outline-danger">
                                        <i class="fas fa-cog me-2"></i>System Settings
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Most Asked Questions (AI Analytics) -->
                <div class="row g-4 mt-2">
                    <div class="col-md-12">
                        <div class="card super-card">
                            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                                <h5><i class="fas fa-chart-bar me-2 text-info"></i>Most Asked Questions (AI Chatbot Analytics)</h5>
                                <a href="<?php echo $base; ?>/admin/ai-training" class="btn btn-sm btn-primary">
                                    <i class="fas fa-robot me-1"></i>AI Training Center
                                </a>
                            </div>
                            <div class="card-body">
                                <?php if (count($mostAsked) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Question Pattern</th>
                                                    <th class="text-center">Times Asked</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($mostAsked as $qa): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars(substr($qa['question_pattern'], 0, 50)) . (strlen($qa['question_pattern']) > 50 ? '...' : ''); ?></td>
                                                        <td class="text-center">
                                                            <span class="badge bg-primary"><?php echo number_format($qa['usage_count']); ?></span>
                                                        </td>
                                                        <td>
                                                            <?php if ($qa['usage_count'] > 10): ?>
                                                                <span class="badge bg-success"><i class="fas fa-fire me-1"></i>Hot</span>
                                                            <?php elseif ($qa['usage_count'] > 5): ?>
                                                                <span class="badge bg-warning">Trending</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary">Normal</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4 text-muted">
                                        <i class="fas fa-info-circle fa-3x mb-3"></i>
                                        <p>No usage data yet. Start chatting with the bot to see analytics!</p>
                                        <a href="<?php echo $base; ?>/admin/ai-training" class="btn btn-primary">
                                            <i class="fas fa-robot me-2"></i>Train AI Bot
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="text-center mt-5 text-muted">
                    <p>APS Dream Home ERP System &copy; 2026 | Super Admin Panel</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>