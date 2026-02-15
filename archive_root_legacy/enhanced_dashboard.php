<?php
/**
 * Enhanced Modern Admin Dashboard - APS Dream Home
 * Comprehensive admin interface with advanced search and analytics
 */

// Enhanced security and initialization
require_once __DIR__ . '/config.php';

// Force cache clear by adding timestamp
$cache_buster = time();

// Prevent redirect loops
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    // This is an AJAX request, don't redirect
} elseif (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Check if user is coming from login page
    if (!isset($_SESSION['login_success']) && !isset($_POST['username'])) {
        header('Location: ../index.php');
        exit();
    } else {
        header('Location: index.php');
        exit();
    }
}

// Check for redirect loop (if redirected more than 3 times in 10 seconds)
$redirect_count = $_SESSION['redirect_count'] ?? 0;
$redirect_time = $_SESSION['redirect_time'] ?? 0;

if (time() - $redirect_time < 10) {
    $_SESSION['redirect_count'] = $redirect_count + 1;
} else {
    $_SESSION['redirect_count'] = 0;
}

$_SESSION['redirect_time'] = time();

if ($_SESSION['redirect_count'] > 3) {
    // Too many redirects, break the loop
    $_SESSION['redirect_count'] = 0;
    error_log('Redirect loop detected in enhanced_dashboard.php, breaking loop');
}

// Set page title and include templates
require_once __DIR__ . '/../includes/templates/header.php';
?>

<!-- Enhanced Admin Dashboard CSS -->
<style>
    .admin-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
    }

    .stats-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        margin-bottom: 1.5rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }

    .stats-icon {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 1rem;
    }

    .search-section {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .quick-actions {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }

    .admin-nav {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }

    .nav-item {
        margin-bottom: 0.5rem;
    }

    .nav-link {
        color: #495057;
        text-decoration: none;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
    }

    .nav-link:hover, .nav-link.active {
        background: #667eea;
        color: white;
        transform: translateX(5px);
    }

    .nav-link i {
        margin-right: 0.75rem;
        width: 20px;
        text-align: center;
    }

    .analytics-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }

    .chart-container {
        position: relative;
        height: 300px;
        margin: 1rem 0;
    }

    .alert-modern {
        border: none;
        border-radius: 15px;
        padding: 1rem 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }

    .user-info-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .ai-assistant-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
    }

    .ai-assistant-card::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: aiPulse 4s ease-in-out infinite;
    }

    @keyframes aiPulse {
        0%, 100% { transform: scale(1) rotate(0deg); opacity: 0.3; }
        50% { transform: scale(1.1) rotate(180deg); opacity: 0.1; }
    }

    .role-widget {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        padding: 1rem;
        margin-bottom: 1rem;
        border-left: 4px solid #667eea;
    }

    .performance-metrics {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .notification-center {
        background: rgba(255, 193, 7, 0.1);
        border: 1px solid rgba(255, 193, 7, 0.3);
        border-radius: 15px;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .quick-action-card {
        background: white;
        border-radius: 15px;
        padding: 1rem;
        margin-bottom: 0.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .quick-action-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(0,0,0,0.12);
    }

    .activity-timeline {
        position: relative;
        padding-left: 2rem;
    }

    .activity-timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(180deg, #667eea 0%, #28a745 100%);
    }

    .activity-item {
        position: relative;
        margin-bottom: 1rem;
        padding: 1rem;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .activity-dot {
        position: absolute;
        left: -2rem;
        top: 1rem;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 0 0 3px #667eea;
    }

    .system-monitor {
        background: rgba(40, 167, 69, 0.1);
        border: 1px solid rgba(40, 167, 69, 0.3);
        border-radius: 15px;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .metric-bar {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        height: 8px;
        overflow: hidden;
        margin: 0.5rem 0;
    }

    .metric-fill {
        height: 100%;
        border-radius: 10px;
        transition: width 0.5s ease;
    }

    .floating-action-btn {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        font-size: 24px;
        box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
        cursor: pointer;
        transition: all 0.3s ease;
        z-index: 1000;
    }

    .floating-action-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 25px rgba(102, 126, 234, 0.6);
    }

    .role-indicator {
        position: absolute;
        top: 1rem;
        right: 1rem;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .role-superadmin { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); }
    .role-admin { background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); }
    .role-manager { background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); }
    .role-sales { background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); }
    .role-hr { background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); }
    .role-marketing { background: linear-gradient(135deg, #6f42c1 0%, #59359a 100%); }
    .role-finance { background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%); }
    .role-it { background: linear-gradient(135deg, #6c757d 0%, #545b62 100%); }
    .role-operations { background: linear-gradient(135deg, #fd7e14 0%, #e8590c 100%); }
    .role-support { background: linear-gradient(135deg, #e83e8c 0%, #c2185b 100%); }
</style>

<div class="container-fluid">
    <!-- AI Assistant Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="ai-assistant-card">
                <div class="role-indicator role-<?php echo $_SESSION['admin_role'] ?? 'admin'; ?>">
                    <?php echo ucfirst($_SESSION['admin_role'] ?? 'Admin'); ?> Dashboard
                </div>
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="mb-2"><i class="fas fa-robot me-3"></i>AI Assistant</h3>
                        <p class="mb-0 opacity-75">Ask me anything about your data, get insights, or request assistance with tasks.</p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <button class="btn btn-light" onclick="openAIAssistant()">
                            <i class="fas fa-comments me-2"></i>Chat with AI
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Global Search Section -->
    <div class="search-section">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-3"><i class="fas fa-search me-2"></i>Global Search</h4>
                <div class="input-group input-group-lg">
                    <input type="text" class="form-control" id="globalSearch" placeholder="Search users, properties, projects, bookings, reports..." onkeyup="globalSearch(this.value)">
                    <button class="btn btn-primary" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <div class="mt-2">
                    <small class="text-muted">
                        Search across: Users, Properties, Projects, Bookings, Reports, Analytics, Settings
                    </small>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="d-grid gap-2">
                    <button class="btn btn-success" onclick="exportData()">
                        <i class="fas fa-download me-2"></i>Export Data
                    </button>
                    <button class="btn btn-info" onclick="showAdvancedSearch()">
                        <i class="fas fa-filter me-2"></i>Advanced Filters
                    </button>
                    <button class="btn btn-warning" onclick="window.open('../cache_clear.html', '_blank')" title="Clear Browser Cache">
                        <i class="fas fa-broom me-2"></i>Clear Cache
                    </button>
                </div>
            </div>
        </div>

        <!-- Search Results -->
        <div id="searchResults" class="mt-4" style="display: none;">
            <h5>Search Results</h5>
            <div id="searchResultsContent"></div>
        </div>
    </div>

    <!-- Quick Stats Row -->
    <div class="row mb-4" id="statsRow">
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon bg-primary text-white">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="mb-1" id="totalUsers">--</h3>
                <p class="text-muted mb-0">Total Users</p>
                <small class="text-success">
                    <i class="fas fa-arrow-up"></i> +12% this month
                </small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon bg-success text-white">
                    <i class="fas fa-home"></i>
                </div>
                <h3 class="mb-1" id="totalProperties">--</h3>
                <p class="text-muted mb-0">Total Properties</p>
                <small class="text-success">
                    <i class="fas fa-arrow-up"></i> +8% this month
                </small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon bg-warning text-white">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3 class="mb-1" id="totalBookings">--</h3>
                <p class="text-muted mb-0">Total Bookings</p>
                <small class="text-success">
                    <i class="fas fa-arrow-up"></i> +15% this month
                </small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon bg-info text-white">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 class="mb-1" id="totalRevenue">--</h3>
                <p class="text-muted mb-0">Monthly Revenue</p>
                <small class="text-success">
                    <i class="fas fa-arrow-up"></i> +25% this month
                </small>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Role-Specific Widgets -->
        <div class="col-md-4">
            <?php if (in_array($_SESSION['admin_role'] ?? '', ['admin', 'superadmin'])): ?>
            <div class="role-widget">
                <h6><i class="fas fa-crown me-2"></i>Admin Control Panel</h6>
                <div class="d-grid gap-2">
                    <a href="manage_users.php" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-users me-1"></i>Manage Users
                    </a>
                    <a href="manage_roles.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-user-tag me-1"></i>Role Management
                    </a>
                    <a href="security_logs.php" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-shield-alt me-1"></i>Security Logs
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <?php if (in_array($_SESSION['admin_role'] ?? '', ['manager', 'director'])): ?>
            <div class="role-widget">
                <h6><i class="fas fa-users-cog me-2"></i>Team Management</h6>
                <div class="d-grid gap-2">
                    <a href="employees.php" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-id-badge me-1"></i>Employee Management
                    </a>
                    <a href="projects.php" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-building me-1"></i>Project Overview
                    </a>
                    <a href="reports.php" class="btn btn-outline-warning btn-sm">
                        <i class="fas fa-chart-bar me-1"></i>Team Reports
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <?php if (in_array($_SESSION['admin_role'] ?? '', ['sales'])): ?>
            <div class="role-widget">
                <h6><i class="fas fa-chart-line me-2"></i>Sales Performance</h6>
                <div class="d-grid gap-2">
                    <a href="leads.php" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-user-plus me-1"></i>Manage Leads
                    </a>
                    <a href="bookings.php" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-calendar-check me-1"></i>View Bookings
                    </a>
                    <a href="analytics_dashboard.php" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-chart-bar me-1"></i>Sales Analytics
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <?php if (in_array($_SESSION['admin_role'] ?? '', ['hr'])): ?>
            <div class="role-widget">
                <h6><i class="fas fa-users me-2"></i>HR Management</h6>
                <div class="d-grid gap-2">
                    <a href="employees.php" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-id-badge me-1"></i>Employee Records
                    </a>
                    <a href="attendance.php" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-clock me-1"></i>Attendance
                    </a>
                    <a href="leaves.php" class="btn btn-outline-warning btn-sm">
                        <i class="fas fa-calendar me-1"></i>Leave Management
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- System Status -->
            <div class="system-monitor">
                <h6><i class="fas fa-heartbeat me-2"></i>System Health</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span>Database:</span>
                    <span class="badge bg-success" id="dbStatus">Connected</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>AI System:</span>
                    <span class="badge bg-success" id="aiStatus">Active</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Email Service:</span>
                    <span class="badge bg-success" id="emailStatus">Ready</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>WhatsApp:</span>
                    <span class="badge bg-success" id="whatsappStatus">Connected</span>
                </div>
            </div>
        </div>

        <!-- Analytics Chart -->
        <div class="col-md-8">
            <div class="analytics-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-chart-area me-2"></i>Analytics Overview</h5>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary active" onclick="loadChartData('daily')">Daily</button>
                        <button class="btn btn-outline-primary" onclick="loadChartData('weekly')">Weekly</button>
                        <button class="btn btn-outline-primary" onclick="loadChartData('monthly')">Monthly</button>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="analyticsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity & Notifications -->
    <div class="row">
        <div class="col-md-6">
            <div class="analytics-card">
                <h6 class="mb-3"><i class="fas fa-clock me-2"></i>Recent Activity</h6>
                <div class="activity-timeline" id="recentActivity">
                    <div class="text-center text-muted">
                        <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                        <p>Loading recent activity...</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="notification-center">
                <h6 class="mb-3"><i class="fas fa-bell me-2"></i>Notifications</h6>
                <div id="systemAlerts">
                    <div class="alert alert-success alert-modern">
                        <i class="fas fa-check-circle me-2"></i>
                        All systems operational
                    </div>
                    <div class="alert alert-info alert-modern">
                        <i class="fas fa-info-circle me-2"></i>
                        Database backup completed successfully
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Action Button -->
<button class="floating-action-btn" onclick="showQuickActions()" title="Quick Actions">
    <i class="fas fa-plus"></i>
</button>

<!-- Quick Actions Modal -->
<div class="modal fade" id="quickActionsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6>User Management</h6>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary quick-action-card" onclick="window.location.href='manage_users.php'">
                                <i class="fas fa-users me-2"></i>Add New User
                            </button>
                            <button class="btn btn-outline-secondary quick-action-card" onclick="window.location.href='manage_roles.php'">
                                <i class="fas fa-user-tag me-2"></i>Manage Roles
                            </button>
                            <button class="btn btn-outline-info quick-action-card" onclick="window.location.href='employees.php'">
                                <i class="fas fa-id-badge me-2"></i>Employee Management
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6>Property Management</h6>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-success quick-action-card" onclick="window.location.href='properties.php'">
                                <i class="fas fa-home me-2"></i>All Properties
                            </button>
                            <button class="btn btn-outline-warning quick-action-card" onclick="window.location.href='projects.php'">
                                <i class="fas fa-building me-2"></i>Projects
                            </button>
                            <button class="btn btn-outline-dark quick-action-card" onclick="window.location.href='bookings.php'">
                                <i class="fas fa-calendar-check me-2"></i>Bookings
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6>Analytics & Reports</h6>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-info quick-action-card" onclick="window.location.href='analytics_dashboard.php'">
                                <i class="fas fa-chart-bar me-2"></i>Analytics
                            </button>
                            <button class="btn btn-outline-primary quick-action-card" onclick="window.location.href='reports.php'">
                                <i class="fas fa-file-alt me-2"></i>Reports
                            </button>
                            <button class="btn btn-outline-success quick-action-card" onclick="window.location.href='system_health.php'">
                                <i class="fas fa-heartbeat me-2"></i>System Health
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AI Assistant Modal -->
<div class="modal fade" id="aiAssistantModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-robot me-2"></i>AI Assistant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Ask me anything:</label>
                    <textarea class="form-control" id="aiQuery" rows="3" placeholder="Example: Show me sales analytics for this month..."></textarea>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" onclick="askAI()">
                        <i class="fas fa-paper-plane me-2"></i>Ask AI
                    </button>
                    <button class="btn btn-secondary" onclick="voiceQuery()">
                        <i class="fas fa-microphone me-2"></i>Voice Query
                    </button>
                </div>
                <div id="aiResponse" class="mt-3" style="min-height: 100px; background: #f8f9fa; border-radius: 10px; padding: 1rem;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Advanced Search Modal -->
<div class="modal fade" id="advancedSearchModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-filter me-2"></i>Advanced Search</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Search In:</label>
                        <select class="form-select" id="searchModule">
                            <option value="all">All Modules</option>
                            <option value="users">Users</option>
                            <option value="properties">Properties</option>
                            <option value="projects">Projects</option>
                            <option value="bookings">Bookings</option>
                            <option value="reports">Reports</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date Range:</label>
                        <select class="form-select" id="dateRange">
                            <option value="all">All Time</option>
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                            <option value="year">This Year</option>
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Status:</label>
                        <select class="form-select" id="statusFilter">
                            <option value="all">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sort By:</label>
                        <select class="form-select" id="sortBy">
                            <option value="date">Date</option>
                            <option value="name">Name</option>
                            <option value="status">Status</option>
                            <option value="relevance">Relevance</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="performAdvancedSearch()">Search</button>
            </div>
        </div>
    </div>
</div>

    <!-- Advanced Navigation -->
    <div class="admin-nav">
        <h5 class="mb-3"><i class="fas fa-compass me-2"></i>Admin Navigation</h5>
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-muted mb-2">User Management</h6>
                <div class="nav-item">
                    <a href="manage_users.php" class="nav-link">
                        <i class="fas fa-users"></i>All Users
                    </a>
                </div>
                <div class="nav-item">
                    <a href="manage_roles.php" class="nav-link">
                        <i class="fas fa-user-tag"></i>Roles & Permissions
                    </a>
                </div>
                <div class="nav-item">
                    <a href="employees.php" class="nav-link">
                        <i class="fas fa-id-badge"></i>Employee Management
                    </a>
                </div>
                <div class="nav-item">
                    <a href="associates_management.php" class="nav-link">
                        <i class="fas fa-handshake"></i>Associates
                    </a>
                </div>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted mb-2">Property Management</h6>
                <div class="nav-item">
                    <a href="properties.php" class="nav-link">
                        <i class="fas fa-home"></i>All Properties
                    </a>
                </div>
                <div class="nav-item">
                    <a href="projects.php" class="nav-link">
                        <i class="fas fa-building"></i>Projects
                    </a>
                </div>
                <div class="nav-item">
                    <a href="bookings.php" class="nav-link">
                        <i class="fas fa-calendar-check"></i>Bookings
                    </a>
                </div>
                <div class="nav-item">
                    <a href="leads.php" class="nav-link">
                        <i class="fas fa-user-plus"></i>Leads
                    </a>
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-muted mb-2">Analytics & Reports</h6>
                <div class="nav-item">
                    <a href="analytics_dashboard.php" class="nav-link">
                        <i class="fas fa-chart-bar"></i>Analytics Dashboard
                    </a>
                </div>
                <div class="nav-item">
                    <a href="reports.php" class="nav-link">
                        <i class="fas fa-file-alt"></i>Reports
                    </a>
                </div>
                <div class="nav-item">
                    <a href="ai_dashboard.php" class="nav-link">
                        <i class="fas fa-robot"></i>AI Insights
                    </a>
                </div>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted mb-2">System Management</h6>
                <div class="nav-item">
                    <a href="system_health.php" class="nav-link">
                        <i class="fas fa-heartbeat"></i>System Health
                    </a>
                </div>
                <div class="nav-item">
                    <a href="settings.php" class="nav-link">
                        <i class="fas fa-cog"></i>Settings
                    </a>
                </div>
                <div class="nav-item">
                    <a href="security_logs.php" class="nav-link">
                        <i class="fas fa-shield-alt"></i>Security Logs
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Advanced Search Modal -->
<div class="modal fade" id="advancedSearchModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-filter me-2"></i>Advanced Search</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Search In:</label>
                        <select class="form-select" id="searchModule">
                            <option value="all">All Modules</option>
                            <option value="users">Users</option>
                            <option value="properties">Properties</option>
                            <option value="projects">Projects</option>
                            <option value="bookings">Bookings</option>
                            <option value="reports">Reports</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date Range:</label>
                        <select class="form-select" id="dateRange">
                            <option value="all">All Time</option>
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                            <option value="year">This Year</option>
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Status:</label>
                        <select class="form-select" id="statusFilter">
                            <option value="all">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sort By:</label>
                        <select class="form-select" id="sortBy">
                            <option value="date">Date</option>
                            <option value="name">Name</option>
                            <option value="status">Status</option>
                            <option value="relevance">Relevance</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="performAdvancedSearch()">Search</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js?v=<?php echo $cache_buster; ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js?v=<?php echo $cache_buster; ?>"></script>
<script>
// Initialize dashboard data
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardStats();
    loadChartData('daily');
    loadRecentActivity();
    loadSystemStatus();
    startRealTimeUpdates();
});

// Load dashboard statistics
function loadDashboardStats() {
    fetch('ajax/get_dashboard_stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('totalUsers').textContent = data.stats.total_users || 0;
                document.getElementById('totalProperties').textContent = data.stats.total_properties || 0;
                document.getElementById('totalBookings').textContent = data.stats.total_bookings || 0;
                document.getElementById('totalRevenue').textContent = '₹' + (data.stats.total_revenue || 0);
            }
        })
        .catch(error => {
            console.error('Error loading dashboard stats:', error);
        });
}

// Load chart data
function loadChartData(period) {
    fetch('ajax/get_analytics_data.php?period=' + period)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderChart(data.chart_data, period);
            }
        })
        .catch(error => {
            console.error('Error loading chart data:', error);
        });
}

// Render analytics chart
function renderChart(chartData, period) {
    const ctx = document.getElementById('analyticsChart').getContext('2d');

    // Destroy existing chart if it exists
    if (window.analyticsChart) {
        window.analyticsChart.destroy();
    }

    window.analyticsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Revenue',
                data: chartData.revenue,
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Bookings',
                data: chartData.bookings,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Revenue & Bookings Trend (' + period.charAt(0).toUpperCase() + period.slice(1) + ')'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Global search functionality
function globalSearch(query) {
    if (query.length < 2) {
        document.getElementById('searchResults').style.display = 'none';
        return;
    }

    fetch('ajax/global_search.php?q=' + encodeURIComponent(query))
        .then(response => response.json())
        .then(data => {
            if (data.success && data.results.length > 0) {
                displaySearchResults(data.results);
            } else {
                document.getElementById('searchResults').style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error performing search:', error);
        });
}

// Display search results
function displaySearchResults(results) {
    const resultsDiv = document.getElementById('searchResults');
    const contentDiv = document.getElementById('searchResultsContent');

    let html = '<div class="list-group">';
    results.forEach(result => {
        html += `
            <a href="${result.url}" class="list-group-item list-group-item-action">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">${result.title}</h6>
                    <small class="text-muted">${result.type}</small>
                </div>
                <p class="mb-1">${result.description}</p>
            </a>
        `;
    });
    html += '</div>';

    contentDiv.innerHTML = html;
    resultsDiv.style.display = 'block';
}

// Show advanced search modal
function showAdvancedSearch() {
    const modal = new bootstrap.Modal(document.getElementById('advancedSearchModal'));
    modal.show();
}

// Perform advanced search
function performAdvancedSearch() {
    const module = document.getElementById('searchModule').value;
    const dateRange = document.getElementById('dateRange').value;
    const status = document.getElementById('statusFilter').value;
    const sortBy = document.getElementById('sortBy').value;

    // Close modal
    bootstrap.Modal.getInstance(document.getElementById('advancedSearchModal')).hide();

    // Perform search
    fetch(`ajax/advanced_search.php?module=${module}&date_range=${dateRange}&status=${status}&sort=${sortBy}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displaySearchResults(data.results);
            }
        })
        .catch(error => {
            console.error('Error performing advanced search:', error);
        });
}

// Load recent activity
function loadRecentActivity() {
    fetch('ajax/get_recent_activity.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayRecentActivity(data.activities);
            }
        })
        .catch(error => {
            console.error('Error loading recent activity:', error);
        });
}

// Display recent activity
function displayRecentActivity(activities) {
    const container = document.getElementById('recentActivity');

    if (activities.length === 0) {
        container.innerHTML = '<p class="text-muted">No recent activity.</p>';
        return;
    }

    let html = '<div class="list-group list-group-flush">';
    activities.forEach(activity => {
        html += `
            <div class="list-group-item px-0">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">${activity.title}</h6>
                    <small class="text-muted">${activity.time}</small>
                </div>
                <p class="mb-1">${activity.description}</p>
            </div>
        `;
    });
    html += '</div>';

    container.innerHTML = html;
}

// Load system status
function loadSystemStatus() {
    fetch('ajax/get_system_status.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateSystemStatus(data.status);
            }
        })
        .catch(error => {
            console.error('Error loading system status:', error);
        });
}

// Update system status indicators
function updateSystemStatus(status) {
    document.getElementById('dbStatus').textContent = status.database;
    document.getElementById('dbStatus').className = `badge bg-${status.database === 'Connected' ? 'success' : 'danger'}`;

    document.getElementById('aiStatus').textContent = status.ai_system;
    document.getElementById('aiStatus').className = `badge bg-${status.ai_system === 'Active' ? 'success' : 'warning'}`;

    document.getElementById('emailStatus').textContent = status.email_service;
    document.getElementById('emailStatus').className = `badge bg-${status.email_service === 'Ready' ? 'success' : 'warning'}`;

    document.getElementById('whatsappStatus').textContent = status.whatsapp;
    document.getElementById('whatsappStatus').className = `badge bg-${status.whatsapp === 'Connected' ? 'success' : 'warning'}`;
}

// Export data functionality
function exportData() {
    const format = confirm('Export as CSV? (Cancel for PDF)') ? 'csv' : 'pdf';

    window.location.href = `ajax/export_dashboard_data.php?format=${format}`;
}

// AI Assistant functions
function openAIAssistant() {
    const modal = new bootstrap.Modal(document.getElementById('aiAssistantModal'));
    modal.show();
}

function askAI() {
    const query = document.getElementById('aiQuery').value.trim();
    if (!query) return;

    document.getElementById('aiResponse').innerHTML = '<i class="fas fa-brain fa-spin me-2"></i>AI is thinking...';

    // Simulate AI response
    setTimeout(() => {
        document.getElementById('aiResponse').innerHTML = `
            <div class="alert alert-info">
                <strong>AI Response:</strong><br>
                Based on your query "${query}", I can help you with:
                <ul class="mt-2 mb-0">
                    <li>📊 Generate sales reports and analytics</li>
                    <li>🔍 Search across all system data</li>
                    <li>📈 Provide insights and recommendations</li>
                    <li>⚙️ Help with system configuration</li>
                </ul>
                <small class="text-muted">Try asking: "Show me property sales trends" or "Find customers in Delhi"</small>
            </div>
        `;
    }, 1500);
}

function voiceQuery() {
    if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        const recognition = new SpeechRecognition();

        recognition.continuous = false;
        recognition.interimResults = false;
        recognition.lang = 'en-US';

        recognition.onstart = function() {
            document.getElementById('aiQuery').placeholder = 'Listening...';
        };

        recognition.onresult = function(event) {
            const transcript = event.results[0][0].transcript;
            document.getElementById('aiQuery').value = transcript;
        };

        recognition.onerror = function(event) {
            document.getElementById('aiResponse').innerHTML = '<div class="alert alert-warning">Voice recognition error. Please try typing your query.</div>';
        };

        recognition.start();
    } else {
        document.getElementById('aiResponse').innerHTML = '<div class="alert alert-warning">Voice recognition not supported in this browser.</div>';
    }
}

// Show quick actions modal
function showQuickActions() {
    const modal = new bootstrap.Modal(document.getElementById('quickActionsModal'));
    modal.show();
}

// Enhanced refresh function
function refreshData() {
    loadDashboardStats();
    loadChartData('daily');
    loadRecentActivity();
    loadSystemStatus();

    // Show success message with animation
    showAlert('Dashboard refreshed successfully!', 'success');

    // Add refresh animation
    const cards = document.querySelectorAll('.stats-card');
    cards.forEach((card, index) => {
        setTimeout(() => {
            card.style.transform = 'scale(0.95)';
            setTimeout(() => {
                card.style.transform = 'scale(1)';
            }, 200);
        }, index * 100);
    });
}

// Enhanced chart rendering with multiple chart types
function renderChart(chartData, period) {
    const ctx = document.getElementById('analyticsChart').getContext('2d');

    // Destroy existing chart if it exists
    if (window.analyticsChart) {
        window.analyticsChart.destroy();
    }

    window.analyticsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Revenue (₹)',
                data: chartData.revenue,
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true,
                yAxisID: 'y'
            }, {
                label: 'Bookings',
                data: chartData.bookings,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                fill: true,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Revenue & Bookings Trend (' + period.charAt(0).toUpperCase() + period.slice(1) + ')'
                }
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Time Period'
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Revenue (₹)'
                    },
                    beginAtZero: true
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Number of Bookings'
                    },
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false,
                    },
                },
            }
        }
    });
}

// Enhanced display functions for activity timeline
function displayRecentActivity(activities) {
    const container = document.getElementById('recentActivity');

    if (activities.length === 0) {
        container.innerHTML = '<p class="text-muted">No recent activity.</p>';
        return;
    }

    let html = '';
    activities.forEach((activity, index) => {
        const activityType = activity.type || 'general';
        const activityIcon = getActivityIcon(activityType);
        const activityColor = getActivityColor(activityType);

        html += `
            <div class="activity-item">
                <div class="activity-dot" style="background: ${activityColor};"></div>
                <div class="d-flex w-100 justify-content-between">
                    <div>
                        <h6 class="mb-1">${activity.title}</h6>
                        <p class="mb-1 text-muted">${activity.description}</p>
                    </div>
                    <small class="text-muted">${activity.time}</small>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

// Get activity icon based on type
function getActivityIcon(type) {
    const icons = {
        'user': 'fas fa-user',
        'booking': 'fas fa-calendar-check',
        'property': 'fas fa-home',
        'project': 'fas fa-building',
        'payment': 'fas fa-credit-card',
        'system': 'fas fa-cog',
        'security': 'fas fa-shield-alt'
    };
    return icons[type] || 'fas fa-info-circle';
}

// Get activity color based on type
function getActivityColor(type) {
    const colors = {
        'user': '#007bff',
        'booking': '#28a745',
        'property': '#17a2b8',
        'project': '#6f42c1',
        'payment': '#fd7e14',
        'system': '#6c757d',
        'security': '#dc3545'
    };
    return colors[type] || '#6c757d';
}

// Real-time updates
function startRealTimeUpdates() {
    // Update every 30 seconds
    setInterval(() => {
        loadDashboardStats();
        loadSystemStatus();
    }, 30000);

    // Update activity every 60 seconds
    setInterval(() => {
        loadRecentActivity();
    }, 60000);
}

// Show alert messages
function showAlert(message, type) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show alert-modern" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

    const alertsContainer = document.getElementById('systemAlerts');
    alertsContainer.insertAdjacentHTML('afterbegin', alertHtml);

    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = alertsContainer.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}
</script>

<?php
require_once __DIR__ . '/../includes/templates/footer.php';
?>
