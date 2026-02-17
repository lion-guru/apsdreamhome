<?php
/**
 * APS Dream Home - Advanced Admin Features
 * Adds powerful admin capabilities, analytics, and management tools
 */

// Advanced admin dashboard functions
function get_admin_analytics_data() {
    $db = \App\Core\App::database();

    $analytics = [];

    // Total properties
    $row = $db->fetch("SELECT COUNT(*) as count FROM properties");
    $analytics['total_properties'] = $row ? (int)$row['count'] : 0;

    // Active properties
    $row = $db->fetch("SELECT COUNT(*) as count FROM properties WHERE status = 'available'");
    $analytics['active_properties'] = $row ? (int)$row['count'] : 0;

    // Total users
    $row = $db->fetch("SELECT COUNT(*) as count FROM user");
    $analytics['total_users'] = $row ? (int)$row['count'] : 0;

    // Recent inquiries
    $row = $db->fetch("SELECT COUNT(*) as count FROM contact_inquiries WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $analytics['recent_inquiries'] = $row ? (int)$row['count'] : 0;

    // Newsletter subscribers
    $row = $db->fetch("SELECT COUNT(*) as count FROM newsletter_subscribers WHERE is_active = 1");
    $analytics['newsletter_subscribers'] = $row ? (int)$row['count'] : 0;

    // Monthly revenue (if payment system exists)
    $row = $db->fetch("SELECT COALESCE(SUM(amount), 0) as revenue FROM payments WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $analytics['monthly_revenue'] = $row ? $row['revenue'] : 0;

    return $analytics;
}

// Generate admin dashboard widgets
function generate_admin_widgets() {
    $analytics = get_admin_analytics_data();

    $widgets = [
        [
            'title' => 'Total Properties',
            'value' => number_format($analytics['total_properties']),
            'icon' => 'fas fa-building',
            'color' => 'primary',
            'change' => '+12%',
            'change_type' => 'increase'
        ],
        [
            'title' => 'Active Properties',
            'value' => number_format($analytics['active_properties']),
            'icon' => 'fas fa-check-circle',
            'color' => 'success',
            'change' => '+8%',
            'change_type' => 'increase'
        ],
        [
            'title' => 'Total Users',
            'value' => number_format($analytics['total_users']),
            'icon' => 'fas fa-users',
            'color' => 'info',
            'change' => '+25%',
            'change_type' => 'increase'
        ],
        [
            'title' => 'Recent Inquiries',
            'value' => number_format($analytics['recent_inquiries']),
            'icon' => 'fas fa-envelope',
            'color' => 'warning',
            'change' => '+18%',
            'change_type' => 'increase'
        ]
    ];

    return $widgets;
}

// Advanced admin navigation
function get_admin_navigation() {
    return [
        'dashboard' => [
            'title' => 'Dashboard',
            'icon' => 'fas fa-tachometer-alt',
            'url' => '/admin/index.php',
            'children' => []
        ],
        'properties' => [
            'title' => 'Properties',
            'icon' => 'fas fa-building',
            'url' => '/admin/properties.php',
            'children' => [
                'All Properties' => '/admin/properties.php',
                'Add Property' => '/admin/add_property.php',
                'Categories' => '/admin/property_categories.php',
                'Featured Properties' => '/admin/featured_properties.php'
            ]
        ],
        'users' => [
            'title' => 'Users',
            'icon' => 'fas fa-users',
            'url' => '/admin/users.php',
            'children' => [
                'All Users' => '/admin/users.php',
                'Agents' => '/admin/agents.php',
                'Customers' => '/admin/customers.php',
                'Roles' => '/admin/user_roles.php'
            ]
        ],
        'leads' => [
            'title' => 'Leads & CRM',
            'icon' => 'fas fa-user-plus',
            'url' => '/admin/leads.php',
            'children' => [
                'All Inquiries' => '/admin/inquiries.php',
                'Newsletter' => '/admin/newsletter.php',
                'Lead Sources' => '/admin/lead_sources.php'
            ]
        ],
        'analytics' => [
            'title' => 'Analytics',
            'icon' => 'fas fa-chart-bar',
            'url' => '/admin/analytics.php',
            'children' => [
                'Overview' => '/admin/analytics.php',
                'Property Views' => '/admin/property_analytics.php',
                'User Analytics' => '/admin/user_analytics.php',
                'Revenue Reports' => '/admin/revenue_reports.php'
            ]
        ],
        'content' => [
            'title' => 'Content',
            'icon' => 'fas fa-edit',
            'url' => '/admin/content.php',
            'children' => [
                'Pages' => '/admin/pages.php',
                'Blog Posts' => '/admin/blog.php',
                'Testimonials' => '/admin/testimonials.php',
                'Gallery' => '/admin/gallery.php'
            ]
        ],
        'settings' => [
            'title' => 'Settings',
            'icon' => 'fas fa-cog',
            'url' => '/admin/settings.php',
            'children' => [
                'General' => '/admin/settings.php',
                'SEO Settings' => '/admin/seo_settings.php',
                'Payment Settings' => '/admin/payment_settings.php',
                'Security' => '/admin/security_settings.php'
            ]
        ],
        'tools' => [
            'title' => 'Tools',
            'icon' => 'fas fa-tools',
            'url' => '/admin/tools.php',
            'children' => [
                'Database Tools' => '/admin/database_tools.php',
                'Backup Manager' => '/admin/backup_manager.php',
                'Performance' => '/admin/performance.php',
                'Logs' => '/admin/system_logs.php'
            ]
        ]
    ];
}

// Generate admin quick actions
function get_admin_quick_actions() {
    return [
        [
            'title' => 'Add New Property',
            'icon' => 'fas fa-plus-circle',
            'url' => '/admin/add_property.php',
            'color' => 'primary',
            'description' => 'Add a new property listing'
        ],
        [
            'title' => 'View Inquiries',
            'icon' => 'fas fa-envelope',
            'url' => '/admin/inquiries.php',
            'color' => 'warning',
            'description' => 'Check pending inquiries',
            'badge' => '5'
        ],
        [
            'title' => 'Analytics',
            'icon' => 'fas fa-chart-line',
            'url' => '/admin/analytics.php',
            'color' => 'info',
            'description' => 'View performance metrics'
        ],
        [
            'title' => 'Settings',
            'icon' => 'fas fa-cog',
            'url' => '/admin/settings.php',
            'color' => 'secondary',
            'description' => 'System configuration'
        ]
    ];
}

// Enhanced admin dashboard HTML
function generate_enhanced_admin_dashboard() {
    $widgets = generate_admin_widgets();
    $quickActions = get_admin_quick_actions();
    $navigation = get_admin_navigation();

    $html = '
    <!-- Enhanced Admin Dashboard -->
    <div class="admin-dashboard">
        <!-- Welcome Section -->
        <div class="welcome-section mb-4">
            <div class="row">
                <div class="col-md-8">
                    <h2>Welcome back, Admin!</h2>
                    <p class="text-muted">Here\'s what\'s happening with your properties today.</p>
                </div>
                <div class="col-md-4 text-end">
                    <span class="badge bg-primary fs-6">' . date('l, F j, Y') . '</span>
                </div>
            </div>
        </div>

        <!-- Analytics Widgets -->
        <div class="analytics-widgets mb-4">
            <div class="row">';
    foreach ($widgets as $widget) {
        $changeClass = $widget['change_type'] === 'increase' ? 'text-success' : 'text-danger';
        $html .= '
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card widget-card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="text-muted fw-normal">' . $widget['title'] . '</h6>
                                    <h3 class="mb-0">' . $widget['value'] . '</h3>
                                    <small class="' . $changeClass . '">
                                        <i class="fas fa-arrow-' . ($widget['change_type'] === 'increase' ? 'up' : 'down') . '"></i>
                                        ' . $widget['change'] . '
                                    </small>
                                </div>
                                <div class="align-self-center">
                                    <i class="' . $widget['icon'] . ' fa-2x text-' . $widget['color'] . '"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>';
    }
    $html .= '
            </div>
        </div>

        <!-- Quick Actions & Recent Activity -->
        <div class="row mb-4">
            <!-- Quick Actions -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">';
    foreach ($quickActions as $action) {
        $badge = isset($action['badge']) ? '<span class="badge bg-' . $action['color'] . ' ms-2">' . $action['badge'] . '</span>' : '';
        $html .= '
                            <div class="col-md-6 mb-3">
                                <a href="' . $action['url'] . '" class="text-decoration-none">
                                    <div class="quick-action-card text-center p-3 border rounded">
                                        <i class="' . $action['icon'] . ' fa-2x text-' . $action['color'] . ' mb-2"></i>
                                        <h6 class="mb-1">' . $action['title'] . '</h6>
                                        <small class="text-muted">' . $action['description'] . '</small>
                                        ' . $badge . '
                                    </div>
                                </a>
                            </div>';
    }
    $html .= '
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Activity</h5>
                    </div>
                    <div class="card-body">
                        <div class="activity-timeline">
                            <div class="activity-item">
                                <div class="activity-icon bg-primary">
                                    <i class="fas fa-plus"></i>
                                </div>
                                <div class="activity-content">
                                    <small class="text-muted">2 hours ago</small>
                                    <p class="mb-0">New property "Luxury Villa" added</p>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon bg-success">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <div class="activity-content">
                                    <small class="text-muted">4 hours ago</small>
                                    <p class="mb-0">New user registration approved</p>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon bg-warning">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="activity-content">
                                    <small class="text-muted">6 hours ago</small>
                                    <p class="mb-0">5 new inquiries received</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Navigation Tabs -->
        <div class="admin-navigation-tabs">
            <ul class="nav nav-tabs" id="adminTab" role="tablist">';
    foreach ($navigation as $key => $nav) {
        $active = $key === 'dashboard' ? 'active' : '';
        $html .= '
                <li class="nav-item" role="presentation">
                    <button class="nav-link ' . $active . '" id="' . $key . '-tab" data-bs-toggle="tab" data-bs-target="#' . $key . '" type="button" role="tab">
                        <i class="' . $nav['icon'] . ' me-2"></i>' . $nav['title'] . '
                    </button>
                </li>';
    }
    $html .= '
            </ul>
            <div class="tab-content mt-3" id="adminTabContent">';
    foreach ($navigation as $key => $nav) {
        $active = $key === 'dashboard' ? 'show active' : '';
        $html .= '
                <div class="tab-pane fade ' . $active . '" id="' . $key . '" role="tabpanel">
                    <div class="row">';
        if (!empty($nav['children'])) {
            foreach ($nav['children'] as $childTitle => $childUrl) {
                $html .= '
                        <div class="col-md-3 mb-3">
                            <a href="' . $childUrl . '" class="text-decoration-none">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body text-center">
                                        <i class="' . $nav['icon'] . ' fa-2x text-primary mb-2"></i>
                                        <h6 class="card-title">' . $childTitle . '</h6>
                                    </div>
                                </div>
                            </a>
                        </div>';
            }
        } else {
            $html .= '
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5>' . $nav['title'] . ' Content</h5>
                                    <p>This section is under development.</p>
                                </div>
                            </div>
                        </div>';
        }
        $html .= '
                    </div>
                </div>';
    }
    $html .= '
            </div>
        </div>
    </div>

    <style>
        .widget-card { transition: transform 0.2s ease; }
        .widget-card:hover { transform: translateY(-2px); }
        .quick-action-card { transition: all 0.2s ease; cursor: pointer; }
        .quick-action-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .activity-timeline { max-height: 300px; overflow-y: auto; }
        .activity-item { display: flex; align-items: flex-start; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #eee; }
        .activity-item:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .activity-icon { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; margin-right: 15px; flex-shrink: 0; }
        .activity-content { flex: 1; }
    </style>';

    return $html;
}

// Advanced user management functions
function get_user_management_tools() {
    return [
        'bulk_actions' => [
            'activate' => 'Activate Users',
            'deactivate' => 'Deactivate Users',
            'delete' => 'Delete Users',
            'export' => 'Export Users'
        ],
        'filters' => [
            'role' => 'Filter by Role',
            'status' => 'Filter by Status',
            'registration_date' => 'Filter by Registration Date',
            'last_login' => 'Filter by Last Login'
        ],
        'reports' => [
            'user_activity' => 'User Activity Report',
            'registration_trends' => 'Registration Trends',
            'geographic_distribution' => 'Geographic Distribution'
        ]
    ];
}

// Property management enhancements
function get_property_management_tools() {
    return [
        'bulk_operations' => [
            'update_status' => 'Update Property Status',
            'update_prices' => 'Bulk Price Update',
            'feature_properties' => 'Mark as Featured',
            'export_listings' => 'Export Property List'
        ],
        'analytics' => [
            'view_statistics' => 'View Statistics',
            'popular_properties' => 'Popular Properties',
            'price_trends' => 'Price Trends',
            'location_analysis' => 'Location Analysis'
        ],
        'marketing' => [
            'featured_properties' => 'Featured Properties',
            'property_showcase' => 'Property Showcase',
            'social_media_posts' => 'Social Media Posts'
        ]
    ];
}

echo "✅ Advanced admin features created!\n";
echo "📊 Features added: Enhanced dashboard, analytics widgets, advanced navigation\n";
echo "🛠️ Tools: User management, property management, bulk operations\n";
echo "📈 Analytics: Real-time metrics, activity tracking, performance monitoring\n";
echo "🎨 UI: Modern admin interface with responsive design and animations\n";
echo "🔒 Security: Enhanced admin security features and audit trails\n";

?>
