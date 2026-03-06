<?php
/**
 * Saved Searches - Modern & Interactive
 * Manage your saved property searches at APS Dream Homes
 */

require_once __DIR__ . '/init.php';

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['uid'];
$user_name = h($_SESSION['name'] ?? 'User');

// Page variables
$page_title = 'My Saved Searches | APS Dream Homes';
$layout = 'modern';

ob_start();
?>

<div class="container py-5 mt-5">
    <div class="row mb-5 animate-fade-up">
        <div class="col-md-8">
            <h1 class="display-6 fw-bold text-primary">My Saved Searches</h1>
            <p class="text-muted">Stay updated with properties that match your criteria.</p>
        </div>
        <div class="col-md-4 text-md-end d-flex align-items-center justify-content-md-end">
            <a href="properties.php" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="fas fa-search me-2"></i>New Search
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3 animate-fade-up" style="animation-delay: 0.1s;">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="p-4 bg-primary text-white text-center">
                    <div class="avatar-initial rounded-circle bg-white text-primary d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px; font-size: 2rem; font-weight: bold;">
                        <?= strtoupper(substr($user_name, 0, 1)) ?>
                    </div>
                    <h5 class="fw-bold mb-0"><?= $user_name ?></h5>
                    <p class="small opacity-75 mb-0">Member</p>
                </div>
                <div class="list-group list-group-flush">
                    <a href="customer_dashboard.php" class="list-group-item list-group-item-action py-3 px-4 border-0">
                        <i class="fas fa-th-large me-3 text-primary"></i>Dashboard
                    </a>
                    <a href="profile.php" class="list-group-item list-group-item-action py-3 px-4 border-0">
                        <i class="fas fa-user me-3 text-primary"></i>My Profile
                    </a>
                    <a href="saved-searches.php" class="list-group-item list-group-item-action py-3 px-4 border-0 active bg-primary-soft text-primary fw-bold">
                        <i class="fas fa-search me-3 text-primary"></i>Saved Searches
                    </a>
                    <a href="logout.php" class="list-group-item list-group-item-action py-3 px-4 border-0 text-danger">
                        <i class="fas fa-sign-out-alt me-3"></i>Logout
                    </a>
                </div>
            </div>

            <!-- AI Tips -->
            <div class="card border-0 shadow-sm rounded-4 bg-light">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="fas fa-lightbulb text-warning me-2"></i>AI Tip</h6>
                    <p class="small text-muted mb-0">Try setting price alerts to get notified immediately when a property in your budget becomes available.</p>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9 animate-fade-up" style="animation-delay: 0.2s;">
            <div class="card border-0 shadow-sm rounded-4 p-4 min-vh-50">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0">Manage Searches</h4>
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm rounded-pill px-3 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            Sort By
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">Newest First</a></li>
                            <li><a class="dropdown-item" href="#">Oldest First</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Search Results Placeholder -->
                <div class="text-center py-5">
                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 100px; height: 100px;">
                        <i class="fas fa-search-location fa-3x text-muted opacity-25"></i>
                    </div>
                    <h5 class="fw-bold">No saved searches yet</h5>
                    <p class="text-muted mx-auto" style="max-width: 400px;">When you perform a property search, you can save it to quickly access it later and receive email updates.</p>
                    <a href="properties.php" class="btn btn-primary rounded-pill px-4 mt-2">Start Searching</a>
                </div>

                <!-- Example of how a saved search would look -->
                <!--
                <div class="card border rounded-4 p-3 mb-3 transition-hover shadow-sm">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <h6 class="fw-bold mb-1">Luxury Villas in Gorakhpur</h6>
                            <p class="small text-muted mb-0">Type: Villa • Price: ₹50L - ₹1.5Cr • Amenities: Pool, Gym</p>
                        </div>
                        <div class="col-md-5 text-md-end mt-3 mt-md-0">
                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3 me-2">Run Search</button>
                            <button class="btn btn-sm btn-outline-danger rounded-circle" title="Delete"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </div>
                </div>
                -->
            </div>
        </div>
    </div>
</div>

<style>
.bg-primary-soft { background-color: rgba(13, 110, 253, 0.08); }
.min-vh-50 { min-height: 50vh; }
.transition-hover { transition: all 0.3s ease; }
.transition-hover:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.05) !important; }
.avatar-initial { background-color: #eee; }
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/' . $layout . '.php';
?>
