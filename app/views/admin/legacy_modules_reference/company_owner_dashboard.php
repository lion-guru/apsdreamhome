<?php
require_once __DIR__ . '/core/init.php';
require_once 'includes/universal_dashboard_template.php';

$employee = getAuthUsername() ?? 'Company Owner';

// Database connection
$db = \App\Core\App::database();

// Company-wide statistics - Owner sees EVERYTHING
try {
    // Total Revenue
    $res = $db->fetch("SELECT SUM(amount) as sum FROM payments WHERE status='completed'");
    $total_revenue = $res['sum'] ?? 0;
    
    // Total Properties
    $res = $db->fetch("SELECT COUNT(*) as cnt FROM properties");
    $total_properties = $res['cnt'] ?? 0;
    
    // Total Employees
    $res = $db->fetch("SELECT COUNT(*) as cnt FROM employees WHERE status='active'");
    $total_employees = $res['cnt'] ?? 0;
    
    // Total Customers
    $res = $db->fetch("SELECT COUNT(*) as cnt FROM customers");
    $total_customers = $res['cnt'] ?? 0;
    
    // Profit Calculation
    $res = $db->fetch("SELECT SUM(amount) as sum FROM expenses");
    $total_expenses = $res['sum'] ?? 0;
    $net_profit = $total_revenue - $total_expenses;
    
    // Active Projects
    $res = $db->fetch("SELECT COUNT(*) as cnt FROM projects WHERE status='active'");
    $active_projects = $res['cnt'] ?? 0;
    
} catch (Exception $e) {
    $total_revenue = 0;
    $total_properties = 0;
    $total_employees = 0;
    $total_customers = 0;
    $net_profit = 0;
    $active_projects = 0;
}

// Company Owner Statistics - Complete Business Overview
$stats = [
    [
        'icon' => 'fas fa-rupee-sign',
        'value' => '₹' . h(number_format($total_revenue, 0)),
        'label' => 'Total Company Revenue',
        'change' => '+25% this quarter',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-chart-line',
        'value' => '₹' . h(number_format($net_profit, 0)),
        'label' => 'Net Profit',
        'change' => '+18% growth',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-building',
        'value' => h($total_properties),
        'label' => 'Total Properties',
        'change' => '+12 new listings',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-users',
        'value' => h($total_employees),
        'label' => 'Company Workforce',
        'change' => '+8 new hires',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-handshake',
        'value' => h($total_customers),
        'label' => 'Total Customers',
        'change' => '+45 this month',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-project-diagram',
        'value' => h($active_projects),
        'label' => 'Active Projects',
        'change' => '95% completion rate',
        'change_type' => 'positive'
    ]
];

// Company Owner Quick Actions - Ultimate Control
$quick_actions = [
    [
        'title' => 'Company Overview',
        'icon' => 'fas fa-building',
        'url' => 'company_overview.php',
        'color' => 'warning'
    ],
    [
        'title' => 'All Staff Control',
        'icon' => 'fas fa-users-cog',
        'url' => 'all_staff_management.php',
        'color' => 'primary'
    ],
    [
        'title' => 'Financial Control',
        'icon' => 'fas fa-money-bill-wave',
        'url' => 'financial_control.php',
        'color' => 'success'
    ],
    [
        'title' => 'Master Settings',
        'icon' => 'fas fa-cog',
        'url' => 'master_settings.php',
        'color' => 'info'
    ],
    [
        'title' => 'Security Audit',
        'icon' => 'fas fa-shield-alt',
        'url' => 'security_audit.php',
        'color' => 'danger'
    ],
    [
        'title' => 'Business Intelligence',
        'icon' => 'fas fa-chart-pie',
        'url' => 'business_intelligence.php',
        'color' => 'secondary'
    ]
];

// Recent company-wide activities
$recent_activities = [
    [
        'title' => 'Major Sale Completed',
        'description' => 'Luxury villa sold for ₹2.5 Crores - Highest sale this quarter',
        'time' => '2 hours ago',
        'icon' => 'fas fa-trophy text-warning'
    ],
    [
        'title' => 'New Department Created',
        'description' => 'Digital Marketing department established with 5 new hires',
        'time' => '1 day ago',
        'icon' => 'fas fa-plus-circle text-success'
    ],
    [
        'title' => 'Partnership Agreement',
        'description' => 'Strategic partnership signed with Premier Builders Ltd.',
        'time' => '2 days ago',
        'icon' => 'fas fa-handshake text-primary'
    ],
    [
        'title' => 'System Upgrade Completed',
        'description' => 'Complete UI/UX modernization deployed across all departments',
        'time' => '3 days ago',
        'icon' => 'fas fa-rocket text-info'
    ]
];

// Custom content for Company Owner
$custom_content = '
<div class="row mt-4">
    <div class="col-12">
        <div class="alert alert-warning border-0" style="background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);">
            <div class="d-flex align-items-center">
                <i class="fas fa-crown fa-2x text-warning me-3"></i>
                <div>
                    <h5 class="alert-heading mb-1">Company Owner Access</h5>
                    <p class="mb-0">You have complete control over all company operations, finances, staff, and strategic decisions. Use this power responsibly to grow APS Dream Home.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-warning text-dark">
                <h5 class="card-title mb-0"><i class="fas fa-chart-bar me-2"></i>Company Performance</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <h3 class="text-success">95%</h3>
                        <small class="text-muted">Customer Satisfaction</small>
                    </div>
                    <div class="col-6 mb-3">
                        <h3 class="text-primary">87%</h3>
                        <small class="text-muted">Employee Satisfaction</small>
                    </div>
                    <div class="col-6">
                        <h3 class="text-warning">₹15.8L</h3>
                        <small class="text-muted">Monthly Avg Revenue</small>
                    </div>
                    <div class="col-6">
                        <h3 class="text-info">23%</h3>
                        <small class="text-muted">Market Growth</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0"><i class="fas fa-users me-2"></i>Department Overview</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-handshake me-2 text-orange"></i>Sales Team</span>
                        <span class="badge bg-success rounded-pill">12 Active</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-bullhorn me-2 text-pink"></i>Marketing</span>
                        <span class="badge bg-info rounded-pill">8 Active</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-chart-line me-2 text-success"></i>Finance</span>
                        <span class="badge bg-warning rounded-pill">5 Active</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-users me-2 text-danger"></i>HR Department</span>
                        <span class="badge bg-primary rounded-pill">6 Active</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-server me-2 text-purple"></i>IT Department</span>
                        <span class="badge bg-secondary rounded-pill">4 Active</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="card-title mb-0"><i class="fas fa-rocket me-2"></i>Strategic Goals & Achievements</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="border-start border-success border-4 ps-3">
                            <h6 class="text-success">Q4 2024 Goals</h6>
                            <ul class="list-unstyled mb-0">
                                <li>✅ Revenue target: ₹50L (Achieved)</li>
                                <li>✅ Team expansion: +15 members (Done)</li>
                                <li>⏳ New office setup (In Progress)</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="border-start border-primary border-4 ps-3">
                            <h6 class="text-primary">Technology Upgrades</h6>
                            <ul class="list-unstyled mb-0">
                                <li>✅ Modern UI/UX Implementation</li>
                                <li>✅ Mobile-responsive design</li>
                                <li>⏳ AI integration planning</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="border-start border-warning border-4 ps-3">
                            <h6 class="text-warning">Market Expansion</h6>
                            <ul class="list-unstyled mb-0">
                                <li>✅ New project locations identified</li>
                                <li>⏳ Partnership agreements pending</li>
                                <li>📅 Launch planned for Q1 2025</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>';

echo generateUniversalDashboard('company_owner', $stats, $quick_actions, $recent_activities, $custom_content);
?>
