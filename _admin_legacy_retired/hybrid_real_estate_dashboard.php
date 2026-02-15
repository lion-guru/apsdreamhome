<?php
session_start();
include 'config.php';
require_once 'includes/universal_dashboard_template.php';

// Company Owner has ultimate access - no restrictions
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// For demonstration, we'll assume company owner role
$_SESSION['admin_role'] = 'company_owner';
$employee = $_SESSION['admin_username'] ?? 'Company Owner';

// Use the correct connection variable from config.php
$conn = $con ?? $conn;

// Company-wide statistics including hybrid real estate features
try {
    // Total Revenue
    $total_revenue = $conn->query("SELECT SUM(amount) as sum FROM payments WHERE status='completed'")->fetch(PDO::FETCH_ASSOC)['sum'] ?? 0;

    // Total Properties (Company + Resell)
    $total_properties = $conn->query("SELECT COUNT(*) as cnt FROM real_estate_properties")->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;

    // Company Properties
    $company_properties = $conn->query("SELECT COUNT(*) as cnt FROM real_estate_properties WHERE property_type='company'")->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;

    // Resell Properties
    $resell_properties = $conn->query("SELECT COUNT(*) as cnt FROM real_estate_properties WHERE property_type='resell'")->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;

    // Total Employees
    $total_employees = $conn->query("SELECT COUNT(*) as cnt FROM employees WHERE status='active'")->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;

    // Total Customers
    $total_customers = $conn->query("SELECT COUNT(*) as cnt FROM customers")->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;

    // Total Commission Paid
    $total_commission = $conn->query("SELECT SUM(total_commission) as sum FROM hybrid_commission_records WHERE status='paid'")->fetch(PDO::FETCH_ASSOC)['sum'] ?? 0;

    // Profit Calculation
    $total_expenses = $conn->query("SELECT SUM(amount) as sum FROM expenses")->fetch(PDO::FETCH_ASSOC)['sum'] ?? 0;
    $net_profit = $total_revenue - $total_expenses;

    // Active Projects
    $active_projects = $conn->query("SELECT COUNT(*) as cnt FROM projects WHERE status='active'")->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;

    // Available Properties
    $available_properties = $conn->query("SELECT COUNT(*) as cnt FROM real_estate_properties WHERE status='available'")->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;

    // Sold Properties
    $sold_properties = $conn->query("SELECT COUNT(*) as cnt FROM real_estate_properties WHERE status='sold'")->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;

} catch (Exception $e) {
    $total_revenue = 0;
    $total_properties = 0;
    $company_properties = 0;
    $resell_properties = 0;
    $total_employees = 0;
    $total_customers = 0;
    $total_commission = 0;
    $net_profit = 0;
    $active_projects = 0;
    $available_properties = 0;
    $sold_properties = 0;
}

// Enhanced Statistics for Company Owner - Complete Hybrid Real Estate Overview
$stats = [
    [
        'icon' => 'fas fa-rupee-sign',
        'value' => '₹' . number_format($total_revenue, 0),
        'label' => 'Total Company Revenue',
        'change' => '+25% this quarter',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-chart-line',
        'value' => '₹' . number_format($net_profit, 0),
        'label' => 'Net Profit',
        'change' => '+18% growth',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-building',
        'value' => $total_properties,
        'label' => 'Total Properties',
        'change' => $company_properties . ' Company + ' . $resell_properties . ' Resell',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-home',
        'value' => $company_properties,
        'label' => 'Company Properties',
        'change' => 'MLM Commission Structure',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-handshake',
        'value' => $resell_properties,
        'label' => 'Resell Properties',
        'change' => 'Fixed Commission Structure',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-coins',
        'value' => '₹' . number_format($total_commission, 0),
        'label' => 'Total Commission Paid',
        'change' => 'Hybrid Commission System',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-users',
        'value' => $total_employees,
        'label' => 'Company Workforce',
        'change' => '+8 new hires',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-handshake',
        'value' => $total_customers,
        'label' => 'Total Customers',
        'change' => '+45 this month',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-check-circle',
        'value' => $sold_properties,
        'label' => 'Properties Sold',
        'change' => 'Available: ' . $available_properties,
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-project-diagram',
        'value' => $active_projects,
        'label' => 'Active Projects',
        'change' => '95% completion rate',
        'change_type' => 'positive'
    ]
];

// Enhanced Quick Actions - Complete Hybrid Real Estate Control
$quick_actions = [
    [
        'title' => '🏗️ Development Cost Calculator',
        'icon' => 'fas fa-calculator',
        'url' => '../development_cost_calculator.php',
        'color' => 'success',
        'description' => 'Calculate plot rates with commission integration'
    ],
    [
        'title' => '🏢 Property Management',
        'icon' => 'fas fa-building',
        'url' => '../property_management.php',
        'color' => 'primary',
        'description' => 'Manage company and resell properties'
    ],
    [
        'title' => '📊 Hybrid Commission Dashboard',
        'icon' => 'fas fa-chart-line',
        'url' => '../hybrid_commission_dashboard.php',
        'color' => 'warning',
        'description' => 'Monitor both business types performance'
    ],
    [
        'title' => '⚙️ Commission Plan Builder',
        'icon' => 'fas fa-cogs',
        'url' => '../commission_plan_builder.php',
        'color' => 'info',
        'description' => 'Create and manage MLM commission plans'
    ],
    [
        'title' => '🎯 Commission Calculator',
        'icon' => 'fas fa-calculator',
        'url' => '../commission_plan_calculator.php',
        'color' => 'secondary',
        'description' => 'Test different commission scenarios'
    ],
    [
        'title' => '📈 Business Intelligence',
        'icon' => 'fas fa-chart-pie',
        'url' => 'business_intelligence.php',
        'color' => 'danger',
        'description' => 'Advanced analytics and insights'
    ],
    [
        'title' => '👥 Associate Management',
        'icon' => 'fas fa-users',
        'url' => 'associates_management.php',
        'color' => 'primary',
        'description' => 'Manage MLM associates and levels'
    ],
    [
        'title' => '💰 Financial Control',
        'icon' => 'fas fa-money-bill-wave',
        'url' => 'financial_control.php',
        'color' => 'success',
        'description' => 'Complete financial management'
    ],
    [
        'title' => '🔧 Master Settings',
        'icon' => 'fas fa-cog',
        'url' => 'master_settings.php',
        'color' => 'info',
        'description' => 'System configuration and settings'
    ],
    [
        'title' => '🔒 Security Audit',
        'icon' => 'fas fa-shield-alt',
        'url' => 'security_audit.php',
        'color' => 'danger',
        'description' => 'Security monitoring and audit'
    ]
];

// Recent company-wide activities with hybrid real estate focus
$recent_activities = [
    [
        'title' => '🏗️ New Company Property Added',
        'description' => 'Green Valley Phase 2 - 50 plots ready for sale with MLM commission structure',
        'time' => '1 hour ago',
        'icon' => 'fas fa-plus-circle text-success'
    ],
    [
        'title' => '💰 Major Commission Payout',
        'description' => '₹15,25,000 distributed across 7-level MLM structure for company property sales',
        'time' => '3 hours ago',
        'icon' => 'fas fa-coins text-warning'
    ],
    [
        'title' => '🏠 Resell Property Sale',
        'description' => 'Luxury apartment sold for ₹2.8 Crores - Fixed 3% commission applied',
        'time' => '5 hours ago',
        'icon' => 'fas fa-home text-primary'
    ],
    [
        'title' => '📊 Development Cost Calculated',
        'description' => 'New project cost analysis completed - Plot rate optimized at ₹5,500/sqft',
        'time' => '1 day ago',
        'icon' => 'fas fa-calculator text-info'
    ],
    [
        'title' => '👥 New Associate Onboarded',
        'description' => '5 new associates joined - Level 1 training completed for hybrid system',
        'time' => '2 days ago',
        'icon' => 'fas fa-user-plus text-success'
    ],
    [
        'title' => '🎯 Commission Plan Updated',
        'description' => 'Hybrid commission plan optimized - Company: 15%, Resell: 5%, Total: 20%',
        'time' => '3 days ago',
        'icon' => 'fas fa-chart-line text-warning'
    ]
];

// Enhanced custom content for Company Owner with Hybrid Real Estate features
$custom_content = '
<div class="row mt-4">
    <div class="col-12">
        <div class="alert alert-warning border-0" style="background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);">
            <div class="d-flex align-items-center">
                <i class="fas fa-crown fa-2x text-warning me-3"></i>
                <div>
                    <h5 class="alert-heading mb-1">🏗️ Hybrid Real Estate Empire Owner</h5>
                    <p class="mb-0">You have complete control over your dual business model - Company colony plotting with MLM commissions and Resell properties with fixed commissions. Your hybrid system is revolutionizing real estate!</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hybrid Real Estate Performance Overview -->
<div class="row mt-4">
    <div class="col-lg-6 mb-4">
        <div class="card h-100 border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0"><i class="fas fa-building me-2"></i>🏗️ Company Properties (MLM)</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <h3 class="text-success">' . $company_properties . '</h3>
                        <small class="text-muted">Total Properties</small>
                    </div>
                    <div class="col-6 mb-3">
                        <h3 class="text-primary">7 Levels</h3>
                        <small class="text-muted">MLM Structure</small>
                    </div>
                    <div class="col-6">
                        <h3 class="text-warning">15%</h3>
                        <small class="text-muted">Avg Commission</small>
                    </div>
                    <div class="col-6">
                        <h3 class="text-info">₹10L+</h3>
                        <small class="text-muted">Monthly Earnings</small>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="progress mb-2" style="height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 85%"></div>
                    </div>
                    <small class="text-muted">Team Growth: 85% of target</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card h-100 border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="card-title mb-0"><i class="fas fa-home me-2"></i>🏠 Resell Properties (Fixed)</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <h3 class="text-success">' . $resell_properties . '</h3>
                        <small class="text-muted">Total Properties</small>
                    </div>
                    <div class="col-6 mb-3">
                        <h3 class="text-primary">5%</h3>
                        <small class="text-muted">Avg Commission</small>
                    </div>
                    <div class="col-6">
                        <h3 class="text-warning">₹5L+</h3>
                        <small class="text-muted">Monthly Volume</small>
                    </div>
                    <div class="col-6">
                        <h3 class="text-info">Quick</h3>
                        <small class="text-muted">Turnaround</small>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="progress mb-2" style="height: 8px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 70%"></div>
                    </div>
                    <small class="text-muted">Market Expansion: 70% of target</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Commission Structure Overview -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="card-title mb-0"><i class="fas fa-coins me-2"></i>💰 Hybrid Commission Structure</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <h6 class="text-success">Company Properties (MLM)</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Level</th>
                                        <th>Direct</th>
                                        <th>Team</th>
                                        <th>Level Bonus</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Associate (1)</td>
                                        <td>6%</td>
                                        <td>2%</td>
                                        <td>0%</td>
                                        <td>8%</td>
                                    </tr>
                                    <tr>
                                        <td>BDM (3)</td>
                                        <td>10%</td>
                                        <td>4%</td>
                                        <td>2%</td>
                                        <td>20%</td>
                                    </tr>
                                    <tr>
                                        <td>Site Manager (7)</td>
                                        <td>20%</td>
                                        <td>8%</td>
                                        <td>6%</td>
                                        <td>46%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <h6 class="text-warning">Resell Properties (Fixed)</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Property Type</th>
                                        <th>Commission</th>
                                        <th>Volume Range</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Plots</td>
                                        <td>3-5%</td>
                                        <td>₹0-5Cr+</td>
                                    </tr>
                                    <tr>
                                        <td>Flats</td>
                                        <td>2-3%</td>
                                        <td>₹0-5Cr+</td>
                                    </tr>
                                    <tr>
                                        <td>House</td>
                                        <td>3%</td>
                                        <td>All</td>
                                    </tr>
                                    <tr>
                                        <td>Commercial</td>
                                        <td>4%</td>
                                        <td>All</td>
                                    </tr>
                                    <tr>
                                        <td>Land</td>
                                        <td>2%</td>
                                        <td>All</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Development Cost Integration -->
<div class="row mt-4">
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0"><i class="fas fa-calculator me-2"></i>💰 Development Cost Integration</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Cost Components</h6>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Land Cost
                                <span class="badge bg-primary rounded-pill">₹20L</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Development Cost
                                <span class="badge bg-success rounded-pill">₹15L</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Commission Cost
                                <span class="badge bg-warning rounded-pill">₹5.25L</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Total Cost
                                <span class="badge bg-info rounded-pill">₹40.25L</span>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Plot Rate Calculation</h6>
                        <div class="text-center">
                            <h2 class="text-success">₹5,031/sqft</h2>
                            <p class="text-muted">Final Rate (1000 sqft plot)</p>
                            <div class="mt-3">
                                <div class="progress mb-2" style="height: 10px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: 25%"></div>
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: 15%"></div>
                                    <div class="progress-bar bg-info" role="progressbar" style="width: 60%"></div>
                                </div>
                                <small class="text-muted">Profit 25% | Commission 15% | Costs 60%</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0"><i class="fas fa-chart-line me-2"></i>📈 Business Growth</h5>
            </div>
            <div class="card-body text-center">
                <h3 class="text-success">+300%</h3>
                <p class="text-muted">Total Sales Volume</p>
                <hr>
                <h4 class="text-primary">+200%</h4>
                <p class="text-muted">Associate Earnings</p>
                <hr>
                <h4 class="text-warning">+100%</h4>
                <p class="text-muted">Profit Margins</p>
                <hr>
                <h4 class="text-info">+150%</h4>
                <p class="text-muted">Operational Efficiency</p>
            </div>
        </div>
    </div>
</div>

<!-- Strategic Goals -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="card-title mb-0"><i class="fas fa-rocket me-2"></i>🎯 Strategic Goals & Achievements</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="border-start border-success border-4 ps-3">
                            <h6 class="text-success">🏗️ Company Properties</h6>
                            <ul class="list-unstyled mb-0">
                                <li>✅ 50+ plots ready for sale</li>
                                <li>✅ 7-level MLM structure active</li>
                                <li>⏳ ₹1Cr monthly commission target</li>
                                <li>📅 Team expansion to 100 associates</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="border-start border-warning border-4 ps-3">
                            <h6 class="text-warning">🏠 Resell Properties</h6>
                            <ul class="list-unstyled mb-0">
                                <li>✅ External property network built</li>
                                <li>✅ Fixed commission system active</li>
                                <li>⏳ ₹50L monthly volume target</li>
                                <li>📅 25+ properties in inventory</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="border-start border-primary border-4 ps-3">
                            <h6 class="text-primary">💰 Cost Optimization</h6>
                            <ul class="list-unstyled mb-0">
                                <li>✅ Development cost calculator</li>
                                <li>✅ Commission integration</li>
                                <li>⏳ Profit margin optimization</li>
                                <li>📅 Automated cost analysis</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Feature Access -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-gradient-primary text-white">
                <h5 class="card-title mb-0"><i class="fas fa-bolt me-2"></i>⚡ Quick Access - Your Hybrid Real Estate Tools</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="../development_cost_calculator.php" class="btn btn-outline-success btn-lg w-100">
                            <i class="fas fa-calculator fa-2x mb-2"></i><br>
                            <strong>Cost Calculator</strong><br>
                            <small>Plot rate calculation</small>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="../property_management.php" class="btn btn-outline-primary btn-lg w-100">
                            <i class="fas fa-building fa-2x mb-2"></i><br>
                            <strong>Property Manager</strong><br>
                            <small>Company + Resell</small>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="../hybrid_commission_dashboard.php" class="btn btn-outline-warning btn-lg w-100">
                            <i class="fas fa-chart-line fa-2x mb-2"></i><br>
                            <strong>Hybrid Dashboard</strong><br>
                            <small>Performance analytics</small>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="../commission_plan_builder.php" class="btn btn-outline-info btn-lg w-100">
                            <i class="fas fa-cogs fa-2x mb-2"></i><br>
                            <strong>Plan Builder</strong><br>
                            <small>Commission plans</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>';

echo generateUniversalDashboard('company_owner', $stats, $quick_actions, $recent_activities, $custom_content);
?>
