<?php
// Clean Customer Dashboard using Universal Template

require_once __DIR__ . '/includes/universal_template.php';

// Check if customer is logged in
if (!isset($_SESSION['customer_logged_in']) || $_SESSION['customer_logged_in'] !== true) {
    header('Location: clean_login.php');
    exit();
}

$customer_name = $_SESSION['customer_name'] ?? 'Customer';

// Demo stats (replace with real data)
$dashboard_stats = [
    'active_properties' => 12,
    'total_inquiries' => 8,
    'total_documents' => 15,
    'total_spent' => 250000,
    'profile_completion' => 85
];

$content = '
<div class="dashboard-container">
    <!-- Welcome Section -->
    <div class="welcome-section fade-in">
        <div class="row">
            <div class="col-md-8">
                <h1>Welcome back, ' . htmlspecialchars($customer_name) . '! 👋</h1>
                <p>Here\'s what\'s happening with your property portfolio today</p>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="profile-completion">
                    <div class="completion-circle" style="background: conic-gradient(from 0deg, #28a745 ' . $dashboard_stats['profile_completion'] . '%, #e9ecef ' . $dashboard_stats['profile_completion'] . '%);">
                        <div class="completion-text">
                            <div class="completion-number">' . $dashboard_stats['profile_completion'] . '%</div>
                            <div class="completion-label">Complete</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card text-center fade-in">
                <div class="card-body">
                    <i class="fas fa-home fa-2x text-primary mb-3"></i>
                    <h3>' . $dashboard_stats['active_properties'] . '</h3>
                    <p class="mb-0">Active Properties</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center fade-in">
                <div class="card-body">
                    <i class="fas fa-search fa-2x text-success mb-3"></i>
                    <h3>' . $dashboard_stats['total_inquiries'] . '</h3>
                    <p class="mb-0">Total Inquiries</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center fade-in">
                <div class="card-body">
                    <i class="fas fa-file-alt fa-2x text-info mb-3"></i>
                    <h3>' . $dashboard_stats['total_documents'] . '</h3>
                    <p class="mb-0">Documents</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center fade-in">
                <div class="card-body">
                    <i class="fas fa-money-bill-wave fa-2x text-warning mb-3"></i>
                    <h3>₹' . number_format($dashboard_stats['total_spent']/100000, 1) . 'L</h3>
                    <p class="mb-0">Total Investment</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card fade-in">
        <div class="card-body">
            <h3 class="section-title">Quick Actions</h3>
            <div class="row g-3">
                <div class="col-md-3">
                    <a href="properties?action=add" class="btn btn-primary w-100">
                        <i class="fas fa-plus-circle me-2"></i>Add Property
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="documents" class="btn btn-success w-100">
                        <i class="fas fa-upload me-2"></i>Upload Docs
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="payments" class="btn btn-info w-100">
                        <i class="fas fa-credit-card me-2"></i>Make Payment
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="support" class="btn btn-warning w-100">
                        <i class="fas fa-headset me-2"></i>Get Support
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card fade-in mt-4">
        <div class="card-body">
            <h3 class="section-title">Recent Activity</h3>
            <div class="list-group list-group-flush">
                <div class="list-group-item d-flex align-items-center">
                    <i class="fas fa-tachometer-alt text-primary me-3"></i>
                    <div class="flex-grow-1">
                        <div class="fw-bold">Dashboard Access</div>
                        <small class="text-muted">Just now</small>
                    </div>
                </div>
                <div class="list-group-item d-flex align-items-center">
                    <i class="fas fa-chart-line text-success me-3"></i>
                    <div class="flex-grow-1">
                        <div class="fw-bold">Portfolio Updated</div>
                        <small class="text-muted">2 hours ago</small>
                    </div>
                </div>
                <div class="list-group-item d-flex align-items-center">
                    <i class="fas fa-bell text-info me-3"></i>
                    <div class="flex-grow-1">
                        <div class="fw-bold">New Notification</div>
                        <small class="text-muted">1 day ago</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Logout -->
    <div class="text-center mt-4">
        <a href="clean_login.php?logout=1" class="btn btn-outline-danger btn-lg">
            <i class="fas fa-sign-out-alt me-2"></i>Logout Securely
        </a>
    </div>
</div>

<!-- Floating Elements -->
<div class="floating-elements">
    <div class="floating-element"></div>
    <div class="floating-element"></div>
    <div class="floating-element"></div>
</div>';

dashboard_page($content, 'Customer Dashboard - APS Dream Home');
?>
