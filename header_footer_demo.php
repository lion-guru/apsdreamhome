<?php
/**
 * APS Dream Home - Comprehensive Header/Footer Demo
 * This page demonstrates all features of the new unified header and footer system
 */

// Define BASE_URL for testing
if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script_name = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    $base_path = str_replace('\\', '/', $script_name);
    $base_path = rtrim($base_path, '/') . '/';
    define('BASE_URL', $protocol . $host . $base_path);
}

// Start session for testing different scenarios
session_start();

// Demo different user scenarios
$demo_scenarios = [
    'public' => [
        'title' => 'Public Site (Guest User)',
        'description' => 'Default header and footer for visitors',
        'session' => []
    ],
    'customer' => [
        'title' => 'Customer Dashboard',
        'description' => 'Header and footer for logged-in customers',
        'session' => [
            'user_id' => 123,
            'user_type' => 'customer',
            'user_name' => 'John Smith',
            'customer_name' => 'John Smith'
        ]
    ],
    'employee' => [
        'title' => 'Employee Panel',
        'description' => 'Header and footer for logged-in employees',
        'session' => [
            'user_id' => 456,
            'user_type' => 'employee',
            'user_name' => 'Sarah Johnson',
            'employee_name' => 'Sarah Johnson'
        ]
    ],
    'associate' => [
        'title' => 'Associate Portal',
        'description' => 'Header and footer for logged-in associates',
        'session' => [
            'user_id' => 789,
            'user_type' => 'associate',
            'user_name' => 'Mike Wilson',
            'associate_name' => 'Mike Wilson'
        ]
    ]
];

// Get current scenario or default to public
$current_scenario = $_GET['scenario'] ?? 'public';
$scenario = $demo_scenarios[$current_scenario] ?? $demo_scenarios['public'];

// Set session variables for the current scenario
foreach ($scenario['session'] as $key => $value) {
    $_SESSION[$key] = $value;
}

// Set test flash messages based on scenario
$_SESSION['flash_messages'] = [
    [
        'type' => 'info',
        'text' => 'Welcome to the ' . $scenario['title'] . ' demo!'
    ]
];

// Include the new header
include __DIR__ . '/app/views/layouts/header_unified.php';
?>

<!-- Demo Content -->
<div class="container py-5">
    <!-- Demo Header -->
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="display-4 mb-3">
                <i class="fas fa-magic me-3 text-primary"></i>
                Header & Footer Demo
            </h1>
            <p class="lead text-muted">Showcasing the new unified header and footer system</p>
        </div>
    </div>

    <!-- Scenario Selector -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-circle me-2"></i>
                        Current Demo: <?php echo $scenario['title']; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-3"><?php echo $scenario['description']; ?></p>

                    <!-- Scenario Buttons -->
                    <div class="row g-2">
                        <?php foreach ($demo_scenarios as $key => $demo): ?>
                        <div class="col-md-3">
                            <a href="?scenario=<?php echo $key; ?>"
                               class="btn btn-outline-primary w-100 <?php echo $key === $current_scenario ? 'active' : ''; ?>">
                                <i class="fas fa-<?php echo $key === 'public' ? 'globe' : ($key === 'customer' ? 'user' : ($key === 'employee' ? 'user-tie' : 'users')); ?> me-1"></i>
                                <?php echo $demo['title']; ?>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Showcase -->
    <div class="row g-4 mb-5">
        <!-- Header Features -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-code me-2"></i>
                        Header Features
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="text-center">
                                <i class="fas fa-mobile-alt fa-2x text-primary mb-2"></i>
                                <h6>Responsive Design</h6>
                                <small class="text-muted">Mobile-first approach</small>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-center">
                                <i class="fas fa-user-check fa-2x text-primary mb-2"></i>
                                <h6>Role-Based Navigation</h6>
                                <small class="text-muted">Different layouts per user type</small>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-center">
                                <i class="fas fa-bars fa-2x text-primary mb-2"></i>
                                <h6>Smart Mobile Menu</h6>
                                <small class="text-muted">Collapsible with overlay</small>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-center">
                                <i class="fas fa-link fa-2x text-primary mb-2"></i>
                                <h6>Active Page Detection</h6>
                                <small class="text-muted">Automatic highlighting</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Features -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-code me-2"></i>
                        Footer Features
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="text-center">
                                <i class="fas fa-building fa-2x text-info mb-2"></i>
                                <h6>Company Information</h6>
                                <small class="text-muted">About, contact, hours</small>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-center">
                                <i class="fas fa-list-ul fa-2x text-info mb-2"></i>
                                <h6>Quick Navigation</h6>
                                <small class="text-muted">Organized link sections</small>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-center">
                                <i class="fas fa-envelope fa-2x text-info mb-2"></i>
                                <h6>Newsletter Signup</h6>
                                <small class="text-muted">Interactive subscription form</small>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-center">
                                <i class="fas fa-share-alt fa-2x text-info mb-2"></i>
                                <h6>Social Media</h6>
                                <small class="text-muted">All platform links</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Technical Implementation -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>
                        Technical Implementation
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <h6>📁 Files Created/Modified:</h6>
                            <ul class="list-unstyled">
                                <li>✅ <code>header_unified.php</code> (23.5KB)</li>
                                <li>✅ <code>footer_unified.php</code> (12KB)</li>
                                <li>✅ <code>includes/header.php</code> (162B)</li>
                                <li>✅ <code>includes/footer.php</code> (160B)</li>
                                <li>✅ <code>assets/css/style.css</code> (15KB+)</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h6>🧹 Cleanup Completed:</h6>
                            <ul class="list-unstyled">
                                <li>✅ Removed 9 duplicate header files</li>
                                <li>✅ Removed 9 duplicate footer files</li>
                                <li>✅ Streamlined layouts directory</li>
                                <li>✅ Updated include system</li>
                                <li>✅ Maintained backward compatibility</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h6>🎯 Key Improvements:</h6>
                            <ul class="list-unstyled">
                                <li>✅ Role-based navigation system</li>
                                <li>✅ Mobile-responsive design</li>
                                <li>✅ Modern Bootstrap 5 styling</li>
                                <li>✅ Interactive animations</li>
                                <li>✅ Performance optimized</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Interactive Demo -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning">
                    <h5 class="mb-0 text-dark">
                        <i class="fas fa-mouse-pointer me-2"></i>
                        Interactive Demo
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <!-- Flash Messages Test -->
                        <div class="col-md-6">
                            <h6>💬 Flash Messages</h6>
                            <p class="text-muted small">Test the alert system by refreshing the page</p>
                            <div class="bg-light p-3 rounded">
                                <small class="text-muted">Current: Info message displayed above</small>
                            </div>
                        </div>

                        <!-- Navigation Test -->
                        <div class="col-md-6">
                            <h6>🧭 Navigation States</h6>
                            <p class="text-muted small">Active page highlighting and hover effects</p>
                            <div class="bg-light p-3 rounded">
                                <nav class="navbar navbar-expand-lg navbar-light">
                                    <ul class="navbar-nav">
                                        <li class="nav-item">
                                            <a class="nav-link active" href="#">Home</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#">Properties</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#">About</a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Role Comparison -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        User Role Comparison
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>User Type</th>
                                    <th>Header Layout</th>
                                    <th>Navigation Items</th>
                                    <th>Footer Layout</th>
                                    <th>Special Features</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Public/Guest</strong></td>
                                    <td>Top bar + Main header</td>
                                    <td>Home, Properties, Projects, About, Contact</td>
                                    <td>Full footer with newsletter</td>
                                    <td>Login/Register buttons</td>
                                </tr>
                                <tr>
                                    <td><strong>Customer</strong></td>
                                    <td>Sidebar + Top navbar</td>
                                    <td>Dashboard, Properties, Favorites, Bookings</td>
                                    <td>Simplified footer</td>
                                    <td>User dropdown, notifications</td>
                                </tr>
                                <tr>
                                    <td><strong>Employee</strong></td>
                                    <td>Sidebar + Top navbar</td>
                                    <td>Dashboard, Profile, Tasks, Attendance</td>
                                    <td>Simplified footer</td>
                                    <td>Employee-specific menu</td>
                                </tr>
                                <tr>
                                    <td><strong>Associate</strong></td>
                                    <td>Sidebar + Top navbar</td>
                                    <td>Dashboard, Team, Business, Earnings</td>
                                    <td>Simplified footer</td>
                                    <td>Associate portal features</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row">
        <div class="col-12 text-center">
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="header_footer_test.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-vial me-2"></i>Run Basic Test
                </a>
                <a href="index.php" class="btn btn-success btn-lg">
                    <i class="fas fa-home me-2"></i>Go to Homepage
                </a>
                <a href="properties.php" class="btn btn-info btn-lg">
                    <i class="fas fa-building me-2"></i>View Properties
                </a>
                <a href="about.php" class="btn btn-warning btn-lg">
                    <i class="fas fa-info-circle me-2"></i>About Us
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Demo Styles -->
<style>
.demo-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.demo-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.scenario-selector .btn {
    border-radius: 25px;
    padding: 0.5rem 1.5rem;
}

.scenario-selector .btn.active {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

.feature-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 1.5rem;
}

.navbar-demo {
    background: rgba(13, 110, 253, 0.1);
    border-radius: 10px;
    padding: 1rem;
}
</style>

<?php
// Include the footer
include __DIR__ . '/app/views/layouts/footer_unified.php';
?>
