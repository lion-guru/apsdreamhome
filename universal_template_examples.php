<?php
// Universal Template Examples

require_once __DIR__ . '/includes/universal_template.php';

// Example 1: Homepage using default theme
function example_homepage() {
    $content = "
    <!-- Hero Section -->
    <section class='hero-section'>
        <div class='container'>
            <div class='row'>
                <div class='col-lg-8 mx-auto text-center'>
                    <h1 class='display-4 fw-bold mb-4'>Find Your Dream Home</h1>
                    <p class='lead mb-4'>Discover the perfect property with APS Dream Home</p>
                    <div class='d-flex justify-content-center gap-3'>
                        <a href='properties' class='btn btn-light btn-lg'>
                            <i class='fas fa-search me-2'></i>Browse Properties
                        </a>
                        <a href='contact' class='btn btn-outline-light btn-lg'>
                            <i class='fas fa-phone me-2'></i>Contact Us
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class='py-5'>
        <div class='container'>
            <h2 class='section-title'>Why Choose APS Dream Home?</h2>
            <div class='row g-4'>
                <div class='col-md-4'>
                    <div class='card text-center h-100'>
                        <div class='card-body'>
                            <i class='fas fa-home fa-3x text-primary mb-3'></i>
                            <h5>Wide Selection</h5>
                            <p>Browse thousands of properties across different categories.</p>
                        </div>
                    </div>
                </div>
                <div class='col-md-4'>
                    <div class='card text-center h-100'>
                        <div class='card-body'>
                            <i class='fas fa-shield-alt fa-3x text-success mb-3'></i>
                            <h5>Verified Listings</h5>
                            <p>All properties are verified for authenticity.</p>
                        </div>
                    </div>
                </div>
                <div class='col-md-4'>
                    <div class='card text-center h-100'>
                        <div class='card-body'>
                            <i class='fas fa-headset fa-3x text-info mb-3'></i>
                            <h5>24/7 Support</h5>
                            <p>Get help whenever you need it.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>";

    page($content, 'APS Dream Home - Find Your Perfect Property');
}

// Example 2: Dashboard using dashboard theme
function example_dashboard() {
    $content = "
    <div class='dashboard-container'>
        <!-- Welcome Section -->
        <div class='welcome-section fade-in'>
            <div class='row'>
                <div class='col-md-8'>
                    <h1>Welcome back, John Doe! 👋</h1>
                    <p>Here's what's happening with your property portfolio today</p>
                </div>
                <div class='col-md-4 text-md-end'>
                    <div class='profile-completion'>
                        <div class='completion-circle' style='background: conic-gradient(from 0deg, #28a745 85%, #e9ecef 85%);'>
                            <div class='completion-text'>
                                <div class='completion-number'>85%</div>
                                <div class='completion-label'>Complete</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class='row g-4 mb-4'>
            <div class='col-md-3'>
                <div class='card text-center fade-in'>
                    <div class='card-body'>
                        <i class='fas fa-home fa-2x text-primary mb-3'></i>
                        <h3>12</h3>
                        <p class='mb-0'>Active Properties</p>
                    </div>
                </div>
            </div>
            <div class='col-md-3'>
                <div class='card text-center fade-in'>
                    <div class='card-body'>
                        <i class='fas fa-search fa-2x text-success mb-3'></i>
                        <h3>8</h3>
                        <p class='mb-0'>Total Inquiries</p>
                    </div>
                </div>
            </div>
            <div class='col-md-3'>
                <div class='card text-center fade-in'>
                    <div class='card-body'>
                        <i class='fas fa-file-alt fa-2x text-info mb-3'></i>
                        <h3>15</h3>
                        <p class='mb-0'>Documents</p>
                    </div>
                </div>
            </div>
            <div class='col-md-3'>
                <div class='card text-center fade-in'>
                    <div class='card-body'>
                        <i class='fas fa-money-bill-wave fa-2x text-warning mb-3'></i>
                        <h3>₹2.5L</h3>
                        <p class='mb-0'>Total Investment</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class='card fade-in'>
            <div class='card-body'>
                <h3 class='section-title'>Quick Actions</h3>
                <div class='row g-3'>
                    <div class='col-md-3'>
                        <a href='properties?action=add' class='btn btn-primary w-100'>
                            <i class='fas fa-plus-circle me-2'></i>Add Property
                        </a>
                    </div>
                    <div class='col-md-3'>
                        <a href='documents' class='btn btn-success w-100'>
                            <i class='fas fa-upload me-2'></i>Upload Docs
                        </a>
                    </div>
                    <div class='col-md-3'>
                        <a href='payments' class='btn btn-info w-100'>
                            <i class='fas fa-credit-card me-2'></i>Make Payment
                        </a>
                    </div>
                    <div class='col-md-3'>
                        <a href='support' class='btn btn-warning w-100'>
                            <i class='fas fa-headset me-2'></i>Get Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Elements -->
    <div class='floating-elements'>
        <div class='floating-element'></div>
        <div class='floating-element'></div>
        <div class='floating-element'></div>
    </div>";

    dashboard_page($content, 'Customer Dashboard');
}

// Example 3: Login page using login theme
function example_login() {
    $content = "
    <div class='container py-5'>
        <div class='row justify-content-center'>
            <div class='col-md-6'>
                <div class='login-container mx-auto'>
                    <!-- Header -->
                    <div class='login-header'>
                        <div class='logo-section d-inline-block mb-3'>
                            <h3 class='mb-0'>
                                <i class='fas fa-home me-2'></i>
                                APS DREAM HOMES
                            </h3>
                        </div>
                        <h2 class='mb-2'>Customer Login</h2>
                        <p class='mb-0'>Access your property details and bookings</p>
                    </div>

                    <div class='card-body p-4'>
                        <?php if (isset(\$error)): ?>
                            <div class='alert alert-danger'>
                                <?php echo \$error; ?>
                            </div>
                        <?php endif; ?>

                        <form method='POST' class='needs-validation' novalidate>
                            <div class='mb-3'>
                                <label class='form-label'>
                                    <i class='fas fa-envelope me-1'></i>Email or Phone *
                                </label>
                                <input type='text' class='form-control' name='login' required>
                                <div class='form-text'>Enter your registered email or phone</div>
                            </div>

                            <div class='mb-3'>
                                <label class='form-label'>
                                    <i class='fas fa-lock me-1'></i>Password *
                                </label>
                                <div class='input-group'>
                                    <input type='password' class='form-control' name='password' required>
                                    <button class='btn btn-outline-secondary toggle-password' type='button'>
                                        <i class='fas fa-eye'></i>
                                    </button>
                                </div>
                            </div>

                            <div class='d-grid gap-2'>
                                <button type='submit' class='btn btn-primary btn-lg'>
                                    <i class='fas fa-sign-in-alt me-2'></i>Login
                                </button>
                            </div>

                            <div class='row mt-3'>
                                <div class='col-6'>
                                    <a href='forgot-password' class='text-decoration-none'>
                                        <i class='fas fa-key me-1'></i>Forgot Password?
                                    </a>
                                </div>
                                <div class='col-6 text-end'>
                                    <a href='customer_registration' class='text-decoration-none'>
                                        <i class='fas fa-user-plus me-1'></i>Register
                                    </a>
                                </div>
                            </div>
                        </form>

                        <div class='demo-credentials mt-4 p-3 bg-light rounded'>
                            <h6 class='text-muted'>Demo Credentials:</h6>
                            <small>
                                <strong>Email:</strong> admin@example.com<br>
                                <strong>Password:</strong> password
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>";

    login_page($content, 'Customer Login');
}

// Example 4: Admin page using admin theme
function example_admin() {
    $content = "
    <div class='admin-container'>
        <div class='row'>
            <div class='col-md-3'>
                <div class='card admin-sidebar'>
                    <h5 class='card-title'>Admin Panel</h5>
                    <div class='list-group list-group-flush'>
                        <a href='admin_dashboard' class='list-group-item admin-nav-link active'>
                            <i class='fas fa-tachometer-alt me-2'></i>Dashboard
                        </a>
                        <a href='admin_properties' class='list-group-item admin-nav-link'>
                            <i class='fas fa-home me-2'></i>Properties
                        </a>
                        <a href='admin_users' class='list-group-item admin-nav-link'>
                            <i class='fas fa-users me-2'></i>Users
                        </a>
                        <a href='admin_reports' class='list-group-item admin-nav-link'>
                            <i class='fas fa-chart-bar me-2'></i>Reports
                        </a>
                        <a href='admin_settings' class='list-group-item admin-nav-link'>
                            <i class='fas fa-cog me-2'></i>Settings
                        </a>
                    </div>
                </div>
            </div>

            <div class='col-md-9'>
                <div class='card'>
                    <div class='card-body'>
                        <h2>Admin Dashboard</h2>
                        <p>Welcome to the admin panel. Manage your platform from here.</p>

                        <div class='row g-4'>
                            <div class='col-md-4'>
                                <div class='card border-primary'>
                                    <div class='card-body text-center'>
                                        <i class='fas fa-home fa-2x text-primary mb-3'></i>
                                        <h4>1,250</h4>
                                        <p class='mb-0'>Total Properties</p>
                                    </div>
                                </div>
                            </div>
                            <div class='col-md-4'>
                                <div class='card border-success'>
                                    <div class='card-body text-center'>
                                        <i class='fas fa-users fa-2x text-success mb-3'></i>
                                        <h4>890</h4>
                                        <p class='mb-0'>Active Users</p>
                                    </div>
                                </div>
                            </div>
                            <div class='col-md-4'>
                                <div class='card border-info'>
                                    <div class='card-body text-center'>
                                        <i class='fas fa-chart-line fa-2x text-info mb-3'></i>
                                        <h4>₹12.5L</h4>
                                        <p class='mb-0'>Total Revenue</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>";

    admin_page($content, 'Admin Panel');
}

// Example 5: Custom styling
function example_custom() {
    global $template;

    $content = "
    <div class='container py-5'>
        <h1>Custom Styled Page</h1>
        <div class='card'>
            <div class='card-body'>
                <p>This page has custom styling added via the template system.</p>
            </div>
        </div>
    </div>";

    $template->setTheme('default')
             ->setTitle('Custom Page')
             ->addCSS('.card { border: 3px solid var(--primary-color); }')
             ->addJS('console.log("Custom JavaScript loaded!");')
             ->render($content);
}

// Example 6: Alert components
function example_alerts() {
    $content = "
    <div class='container py-5'>
        <h1>Alert Components</h1>

        <div class='alert alert-success'>
            <i class='fas fa-check-circle me-2'></i>
            <strong>Success!</strong> Your operation completed successfully.
        </div>

        <div class='alert alert-danger'>
            <i class='fas fa-exclamation-triangle me-2'></i>
            <strong>Error!</strong> Something went wrong. Please try again.
        </div>

        <div class='alert alert-warning'>
            <i class='fas fa-exclamation-circle me-2'></i>
            <strong>Warning!</strong> Please review your input before proceeding.
        </div>

        <div class='alert alert-info'>
            <i class='fas fa-info-circle me-2'></i>
            <strong>Info:</strong> This is an informational message.
        </div>
    </div>";

    page($content, 'Alert Examples');
}
?>
