
<?php
/**
 * Universal Dashboard Template for All Employee Roles
 * Mobile-First, Modern UI/UX Design
 * Author: Enhanced for Abhay Singh - APS Dream Home
 * Hybrid Real Estate Integration
 */

// Configuration array for role-specific customization
$role_config = [
    'company_owner' => [
        'title' => 'Company Owner Dashboard',
        'icon' => 'fas fa-crown',
        'description' => 'Complete company ownership and control - All powers and access',
        'theme_class' => 'dashboard-company-owner',
        'menu_items' => [
            ['icon' => 'fas fa-tachometer-alt', 'label' => 'Owner Dashboard', 'url' => 'company_owner_dashboard.php'],
            ['icon' => 'fas fa-building', 'label' => 'Company Overview', 'url' => 'company_overview.php'],
            ['icon' => 'fas fa-users-cog', 'label' => 'All Staff Management', 'url' => 'all_staff_management.php'],
            ['icon' => 'fas fa-chart-line', 'label' => 'Complete Analytics', 'url' => 'complete_analytics.php'],
            ['icon' => 'fas fa-money-bill-wave', 'label' => 'Financial Control', 'url' => 'financial_control.php'],
            ['icon' => 'fas fa-cog', 'label' => 'Master Settings', 'url' => 'master_settings.php'],
            ['icon' => 'fas fa-shield-alt', 'label' => 'Security & Audit', 'url' => 'security_audit.php'],
            ['icon' => 'fas fa-database', 'label' => 'Full Database Access', 'url' => 'full_database_access.php'],
            ['icon' => 'fas fa-key', 'label' => 'Role & Permissions', 'url' => 'role_permissions.php'],
            ['icon' => 'fas fa-chart-pie', 'label' => 'Business Intelligence', 'url' => 'business_intelligence.php'],
            ['icon' => 'fas fa-handshake', 'label' => 'Partnership Management', 'url' => 'partnership_management.php'],
            ['icon' => 'fas fa-gavel', 'label' => 'Legal & Compliance', 'url' => 'legal_compliance.php'],

            // New Hybrid Real Estate Features
            ['icon' => 'fas fa-building', 'label' => '🏗️ Hybrid Real Estate Control', 'url' => 'hybrid_real_estate_control_center.php'],
            ['icon' => 'fas fa-calculator', 'label' => '💰 Development Cost Calculator', 'url' => 'development_cost_calculator.php'],
            ['icon' => 'fas fa-building', 'label' => '🏢 Property Management', 'url' => 'property_management.php'],
            ['icon' => 'fas fa-chart-line', 'label' => '📊 Hybrid Commission Dashboard', 'url' => 'hybrid_commission_dashboard.php'],
            ['icon' => 'fas fa-cogs', 'label' => '⚙️ Commission Plan Builder', 'url' => 'commission_plan_builder.php'],
            ['icon' => 'fas fa-calculator', 'label' => '🎯 Commission Calculator', 'url' => 'commission_plan_calculator.php'],
            ['icon' => 'fas fa-users', 'label' => '👥 Associate Management', 'url' => 'associates_management.php']
        ],
    ],
    'admin' => [
        'title' => 'Admin Dashboard',
        'icon' => 'fas fa-user-shield',
        'description' => 'Administrative control and oversight',
        'theme_class' => 'dashboard-admin'
    ],
    'manager' => [
        'title' => 'Manager Dashboard',
        'icon' => 'fas fa-users-cog',
        'description' => 'Team and project management',
        'theme_class' => 'dashboard-manager'
    ],
    'finance' => [
        'title' => 'Finance Dashboard',
        'icon' => 'fas fa-chart-line',
        'description' => 'Financial management and reporting',
        'theme_class' => 'dashboard-finance'
    ],
    'hr' => [
        'title' => 'HR Dashboard',
        'icon' => 'fas fa-users',
        'description' => 'Human resources management',
        'theme_class' => 'dashboard-hr'
    ],
    'it' => [
        'title' => 'IT Dashboard',
        'icon' => 'fas fa-server',
        'description' => 'Technology and system management',
        'theme_class' => 'dashboard-it'
    ],
    'sales' => [
        'title' => 'Sales Dashboard',
        'icon' => 'fas fa-handshake',
        'description' => 'Sales performance and leads',
        'theme_class' => 'dashboard-sales'
    ],
    'marketing' => [
        'title' => 'Marketing Dashboard',
        'icon' => 'fas fa-bullhorn',
        'description' => 'Marketing campaigns and analytics',
        'theme_class' => 'dashboard-marketing'
    ],
    'legal' => [
        'title' => 'Legal Dashboard',
        'icon' => 'fas fa-balance-scale',
        'description' => 'Legal compliance and documentation',
        'theme_class' => 'dashboard-legal'
    ],
    'operations' => [
        'title' => 'Operations Dashboard',
        'icon' => 'fas fa-cogs',
        'description' => 'Operations management and workflows',
        'theme_class' => 'dashboard-operations'
    ],
    'support' => [
        'title' => 'Support Dashboard',
        'icon' => 'fas fa-headset',
        'description' => 'Customer support and tickets',
        'theme_class' => 'dashboard-support'
    ],
    'employee' => [
        'title' => 'Employee Dashboard',
        'icon' => 'fas fa-user-tie',
        'description' => 'Employee tasks and information',
        'theme_class' => 'dashboard-employee'
    ],
    'director' => [
        'title' => 'Director Dashboard',
        'icon' => 'fas fa-chess-king',
        'description' => 'Strategic oversight and direction',
        'theme_class' => 'dashboard-director'
    ],
    'office_admin' => [
        'title' => 'Office Admin Dashboard',
        'icon' => 'fas fa-building',
        'description' => 'Office administration and coordination',
        'theme_class' => 'dashboard-office-admin'
    ]
];

/**
 * Generate Universal Dashboard HTML
 * @param string $role - User role
 * @param array $stats - Statistics data
 * @param array $quick_actions - Quick action buttons
 * @param array $recent_activities - Recent activity feed
 * @param string $custom_content - Additional custom content
 */
function generateUniversalDashboard($role, $stats = [], $quick_actions = [], $recent_activities = [], $custom_content = '') {
    global $role_config;
    
    $config = $role_config[$role] ?? $role_config['employee'];
    $user_name = $_SESSION['admin_username'] ?? 'User';
    
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($config['title']); ?> | APS Dream Home</title>
        
        <!-- CSS Files -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <link href="css/modern-ui.css" rel="stylesheet">
        <link href="css/admin-enhancements.css" rel="stylesheet">
        <link href="css/universal-dashboard.css" rel="stylesheet">
        
        <!-- PWA Support -->
        <meta name="theme-color" content="#1976d2">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
    </head>
    <body class="universal-dashboard <?php echo $config['theme_class']; ?>">
        
        <!-- Header -->
        <header class="dashboard-header fixed-top">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <button class="sidebar-toggle d-lg-none me-3" onclick="toggleSidebar()">
                            <i class="fas fa-bars text-white"></i>
                        </button>
                        <a href="#" class="header-brand text-white text-decoration-none">
                            <i class="fas fa-home me-2"></i>
                            <span class="fw-bold">APS Dream Home</span>
                        </a>
                    </div>
                    
                    <div class="header-actions">
                        <div class="dropdown">
                            <button class="btn btn-link text-white dropdown-toggle" data-bs-toggle="dropdown">
                                <div class="user-avatar me-2">
                                    <?php echo strtoupper(substr($user_name, 0, 2)); ?>
                                </div>
                                <span class="d-none d-md-inline"><?php echo htmlspecialchars($user_name); ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><span class="dropdown-item-text"><strong><?php echo ucfirst($role); ?></strong></span></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                                <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Sidebar -->
        <nav class="dashboard-sidebar" id="dashboardSidebar">
            <div class="sidebar-content">
                <div class="sidebar-header p-3">
                    <div class="d-flex align-items-center text-white">
                        <i class="<?php echo $config['icon']; ?> me-2"></i>
                        <span class="fw-bold"><?php echo ucfirst($role); ?> Panel</span>
                    </div>
                </div>
                
                <ul class="sidebar-menu list-unstyled">
                    <li><a href="<?php echo strtolower($role); ?>_dashboard.php" class="sidebar-link"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a></li>
                    <?php if (in_array($role, ['superadmin', 'admin'])): ?>
                    <li><a href="properties.php" class="sidebar-link"><i class="fas fa-building me-2"></i> Properties</a></li>
                    <li><a href="leads.php" class="sidebar-link"><i class="fas fa-users me-2"></i> Leads & Customers</a></li>
                    <li><a href="bookings.php" class="sidebar-link"><i class="fas fa-calendar-check me-2"></i> Bookings</a></li>
                    <li><a href="associates.php" class="sidebar-link"><i class="fas fa-handshake me-2"></i> Associates</a></li>
                    <li><a href="payments.php" class="sidebar-link"><i class="fas fa-money-bill-wave me-2"></i> Payments</a></li>
                    <li><a href="reports.php" class="sidebar-link"><i class="fas fa-chart-bar me-2"></i> Reports</a></li>
                    <?php endif; ?>
                    
                    <?php if ($role === 'superadmin'): ?>
                    <li><a href="manage_admins.php" class="sidebar-link"><i class="fas fa-users-cog me-2"></i> Manage Admins</a></li>
                    <li><a href="system_settings.php" class="sidebar-link"><i class="fas fa-cog me-2"></i> System Settings</a></li>
                    <li><a href="security_center.php" class="sidebar-link"><i class="fas fa-shield-alt me-2"></i> Security Center</a></li>
                    <li><a href="backup_restore.php" class="sidebar-link"><i class="fas fa-database me-2"></i> Database Backup</a></li>
                    <?php endif; ?>
                    
                    <?php if ($role === 'finance'): ?>
                    <li><a href="expenses.php" class="sidebar-link"><i class="fas fa-receipt me-2"></i> Expenses</a></li>
                    <li><a href="emi_management.php" class="sidebar-link"><i class="fas fa-credit-card me-2"></i> EMI Management</a></li>
                    <li><a href="financial_reports.php" class="sidebar-link"><i class="fas fa-chart-pie me-2"></i> Financial Reports</a></li>
                    <?php endif; ?>
                    
                    <?php if ($role === 'hr'): ?>
                    <li><a href="employees.php" class="sidebar-link"><i class="fas fa-users me-2"></i> Employees</a></li>
                    <li><a href="leaves.php" class="sidebar-link"><i class="fas fa-calendar-times me-2"></i> Leave Management</a></li>
                    <li><a href="attendance.php" class="sidebar-link"><i class="fas fa-calendar-check me-2"></i> Attendance</a></li>
                    <li><a href="payroll.php" class="sidebar-link"><i class="fas fa-money-bill-wave me-2"></i> Payroll</a></li>
                    <?php endif; ?>
                    
                    <?php if ($role === 'sales'): ?>
                    <li><a href="leads.php" class="sidebar-link"><i class="fas fa-users me-2"></i> Leads</a></li>
                    <li><a href="bookings.php" class="sidebar-link"><i class="fas fa-calendar-check me-2"></i> Bookings</a></li>
                    <li><a href="analytics_dashboard.php" class="sidebar-link"><i class="fas fa-chart-line me-2"></i> Sales Analytics</a></li>
                    <?php endif; ?>
                    
                    <?php if ($role === 'marketing'): ?>
                    <li><a href="campaigns.php" class="sidebar-link"><i class="fas fa-bullseye me-2"></i> Campaigns</a></li>
                    <li><a href="social_media.php" class="sidebar-link"><i class="fas fa-share-alt me-2"></i> Social Media</a></li>
                    <li><a href="analytics_dashboard.php" class="sidebar-link"><i class="fas fa-chart-line me-2"></i> Analytics</a></li>
                    <?php endif; ?>
                    
                    <?php if ($role === 'it'): ?>
                    <li><a href="support_dashboard.php" class="sidebar-link"><i class="fas fa-ticket-alt me-2"></i> Support Tickets</a></li>
                    <li><a href="system_monitor.php" class="sidebar-link"><i class="fas fa-server me-2"></i> System Monitor</a></li>
                    <li><a href="ai_dashboard.php" class="sidebar-link"><i class="fas fa-robot me-2"></i> AI Tools</a></li>
                    <?php endif; ?>
                    
                    <?php if ($role === 'legal'): ?>
                    <li><a href="cases.php" class="sidebar-link"><i class="fas fa-balance-scale me-2"></i> Cases</a></li>
                    <li><a href="contracts.php" class="sidebar-link"><i class="fas fa-file-contract me-2"></i> Contracts</a></li>
                    <li><a href="compliance_dashboard.php" class="sidebar-link"><i class="fas fa-shield-alt me-2"></i> Compliance</a></li>
                    <?php endif; ?>
                    
                    <?php if ($role === 'operations'): ?>
                    <li><a href="tasks_dashboard.php" class="sidebar-link"><i class="fas fa-tasks me-2"></i> Task Management</a></li>
                    <li><a href="attendance_dashboard.php" class="sidebar-link"><i class="fas fa-calendar-check me-2"></i> Attendance</a></li>
                    <li><a href="logistics.php" class="sidebar-link"><i class="fas fa-truck me-2"></i> Logistics</a></li>
                    <?php endif; ?>
                    
                    <?php if ($role === 'employee'): ?>
                    <li><a href="my_tasks.php" class="sidebar-link"><i class="fas fa-tasks me-2"></i> My Tasks</a></li>
                    <li><a href="leave_request.php" class="sidebar-link"><i class="fas fa-calendar-times me-2"></i> Leave Request</a></li>
                    <li><a href="tickets.php" class="sidebar-link"><i class="fas fa-ticket-alt me-2"></i> Support</a></li>
                    <?php endif; ?>
                    
                    <li><a href="documents_dashboard.php" class="sidebar-link"><i class="fas fa-folder-open me-2"></i> Documents</a></li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="dashboard-main">
            <div class="container-fluid">
                <!-- Dashboard Header -->
                <div class="dashboard-page-header mb-4">
                    <h1 class="dashboard-title">
                        <i class="<?php echo $config['icon']; ?> me-3"></i>
                        Welcome, <?php echo htmlspecialchars($user_name); ?>!
                    </h1>
                    <p class="dashboard-subtitle text-muted">
                        <?php echo $config['description']; ?>
                    </p>
                </div>
        
        <!-- Mobile Navigation Overlay -->
        <div class="mobile-nav-overlay" id="mobileNavOverlay" onclick="toggleMobileNav()"></div>

        <!-- Role Header -->
        <div class="role-header fade-in">
            <h1>
                <i class="<?php echo $config['icon']; ?> me-3"></i>
                Welcome, <?php echo htmlspecialchars($user_name); ?>!
            </h1>
            <div class="role-badge">
                <?php echo $config['description']; ?>
            </div>
        </div>

        <!-- Statistics Grid -->
        <?php if (!empty($stats)): ?>
        <div class="stats-grid">
            <?php foreach ($stats as $index => $stat): ?>
            <div class="stat-card-modern fade-in" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                <div class="stat-icon">
                    <i class="<?php echo $stat['icon'] ?? 'fas fa-chart-bar'; ?>"></i>
                </div>
                <div class="stat-value"><?php echo htmlspecialchars($stat['value']); ?></div>
                <div class="stat-label"><?php echo htmlspecialchars($stat['label']); ?></div>
                <?php if (isset($stat['change'])): ?>
                <div class="stat-change <?php echo $stat['change_type'] ?? 'positive'; ?>">
                    <i class="fas fa-arrow-<?php echo ($stat['change_type'] ?? 'positive') === 'positive' ? 'up' : 'down'; ?>"></i>
                    <?php echo htmlspecialchars($stat['change']); ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <?php if (!empty($quick_actions)): ?>
        <div class="quick-actions">
            <h3><i class="fas fa-bolt me-2"></i>Quick Actions</h3>
            <div class="actions-grid">
                <?php foreach ($quick_actions as $action): ?>
                <a href="<?php echo htmlspecialchars($action['url'] ?? '#'); ?>" class="action-card fade-in">
                    <div class="action-icon">
                        <i class="<?php echo htmlspecialchars($action['icon'] ?? ''); ?>"></i>
                    </div>
                    <div class="action-title"><?php echo htmlspecialchars($action['title'] ?? ''); ?></div>
                    <div class="action-desc"><?php echo htmlspecialchars($action['description'] ?? ''); ?></div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Custom Content Area -->
        <?php if (!empty($custom_content)): ?>
        <div class="custom-content-area">
            <?php echo $custom_content; ?>
        </div>
        <?php endif; ?>

        <!-- Recent Activities -->
        <?php if (!empty($recent_activities)): ?>
        <div class="activity-feed">
            <h3><i class="fas fa-clock me-2"></i>Recent Activities</h3>
            <?php foreach ($recent_activities as $activity): ?>
            <div class="activity-item fade-in">
                <div class="activity-icon">
                    <i class="<?php echo $activity['icon'] ?? 'fas fa-info'; ?>"></i>
                </div>
                <div class="activity-content flex-grow-1">
                    <h5><?php echo htmlspecialchars($activity['title']); ?></h5>
                    <p><?php echo htmlspecialchars($activity['description']); ?></p>
                </div>
                <div class="activity-time">
                    <?php echo htmlspecialchars($activity['time']); ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <footer class="text-center py-4 mt-5">
            <div class="container">
                <p class="text-muted mb-0">
                    &copy; <?php echo date('Y'); ?> APS Dream Home. All rights reserved. | 
                    <strong>Version 2.0</strong> - Modern Dashboard System
                </p>
            </div>
        </footer>

        <!-- JavaScript -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            // Mobile Navigation Toggle
            function toggleMobileNav() {
                const drawer = document.getElementById('mobileNavDrawer');
                const overlay = document.getElementById('mobileNavOverlay');
                
                drawer.classList.toggle('open');
                overlay.classList.toggle('show');
            }

            // Auto-hide alerts after 5 seconds
            document.addEventListener('DOMContentLoaded', function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    setTimeout(() => {
                        if (alert.classList.contains('alert-dismissible')) {
                            const closeBtn = alert.querySelector('.btn-close');
                            if (closeBtn) closeBtn.click();
                        }
                    }, 5000);
                });
            });

            // Add loading animation to buttons
            document.addEventListener('DOMContentLoaded', function() {
                const actionCards = document.querySelectorAll('.action-card');
                actionCards.forEach(card => {
                    card.addEventListener('click', function(e) {
                        // Add loading state
                        const icon = this.querySelector('.action-icon i');
                        const originalClass = icon.className;
                        icon.className = 'fas fa-spinner fa-spin';
                        
                        // Restore original icon after navigation
                        setTimeout(() => {
                            icon.className = originalClass;
                        }, 1000);
                    });
                });
            });

            // PWA Installation prompt
            let deferredPrompt;
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                deferredPrompt = e;
                
                // Show install button if needed
                const installBtn = document.getElementById('installBtn');
                if (installBtn) {
                    installBtn.style.display = 'block';
                    installBtn.addEventListener('click', () => {
                        deferredPrompt.prompt();
                        deferredPrompt.userChoice.then((choiceResult) => {
                            deferredPrompt = null;
                            installBtn.style.display = 'none';
                        });
                    });
                }
            });

            // Service Worker Registration for PWA
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js')
                        .then((registration) => {
                            console.log('SW registered: ', registration);
                        })
                        .catch((registrationError) => {
                            console.log('SW registration failed: ', registrationError);
                        });
                });
            }

            // Real-time updates simulation
            function updateDashboardStats() {
                const statValues = document.querySelectorAll('.stat-value');
                statValues.forEach(stat => {
                    // Add subtle animation to show data is live
                    stat.style.transform = 'scale(1.05)';
                    setTimeout(() => {
                        stat.style.transform = 'scale(1)';
                    }, 200);
                });
            }

            // Update stats every 30 seconds
            setInterval(updateDashboardStats, 30000);
        </script>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

/**
 * Helper function to get default stats for any role
 */
function getDefaultStats($role) {
    // This would typically fetch from database
    return [
        [
            'icon' => 'fas fa-users',
            'value' => '124',
            'label' => 'Active Users',
            'change' => '+12%',
            'change_type' => 'positive'
        ],
        [
            'icon' => 'fas fa-chart-line',
            'value' => '₹45,670',
            'label' => 'Revenue',
            'change' => '+8%',
            'change_type' => 'positive'
        ],
        [
            'icon' => 'fas fa-tasks',
            'value' => '23',
            'label' => 'Pending Tasks',
            'change' => '-5%',
            'change_type' => 'positive'
        ]
    ];
}

/**
 * Helper function to get default quick actions for any role
 */
function getDefaultQuickActions($role) {
    $common_actions = [
        [
            'title' => 'Add Property',
            'description' => 'Create new property listing',
            'icon' => 'fas fa-plus',
            'url' => 'properties.php'
        ],
        [
            'title' => 'Manage Leads',
            'description' => 'View and manage leads',
            'icon' => 'fas fa-users',
            'url' => 'leads.php'
        ],
        [
            'title' => 'View Reports',
            'description' => 'Generate and view reports',
            'icon' => 'fas fa-chart-bar',
            'url' => 'reports.php'
        ],
        [
            'title' => 'Settings',
            'description' => 'Configure preferences',
            'icon' => 'fas fa-cog',
            'url' => 'settings.php'
        ]
    ];
    
    // Role-specific actions can be added here
    return $common_actions;
}

/**
 * Helper function to get default recent activities
 */
function getDefaultActivities($role) {
    return [
        [
            'icon' => 'fas fa-user-plus',
            'title' => 'New Lead Added',
            'description' => 'John Doe inquired about Property #123',
            'time' => '2 mins ago'
        ],
        [
            'icon' => 'fas fa-home',
            'title' => 'Property Updated',
            'description' => 'Villa in Sector 15 details modified',
            'time' => '15 mins ago'
        ],
        [
            'icon' => 'fas fa-handshake',
            'title' => 'Booking Confirmed',
            'description' => 'Booking #456 confirmed for tomorrow',
            'time' => '1 hour ago'
        ]
    ];
}
?>
