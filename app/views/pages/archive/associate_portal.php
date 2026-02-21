<?php
/**
 * Complete Associate Portal - APS Dream Homes
 * Ultimate MLM Associate Management System
 * All Features: Dashboard, Profile, Business Reports, Team Management, Customers, Commissions, Support
 */

require_once __DIR__ . '/init.php';

// Initialize database connection
$db = \App\Core\App::database();

// Check if associate is logged in
if (!isset($_SESSION['uid']) || (isset($_SESSION['utype']) && $_SESSION['utype'] !== 'associate')) {
    header("Location: login.php");
    exit();
}

$associate_id = $_SESSION['uid']; // This is the user_id
$associate_name = $_SESSION['uname'] ?? 'Associate';
$associate_level = $_SESSION['level'] ?? 'Associate'; // Should be fetched from DB
$associate_email = $_SESSION['uemail'] ?? '';

// Get comprehensive associate data from mlm_profiles + users
try {
    // Join users and mlm_profiles
    $query = "
        SELECT u.name, u.email, u.phone, mp.* 
        FROM users u 
        JOIN mlm_profiles mp ON u.id = mp.user_id 
        WHERE u.id = :uid
    ";
    $associate_data = $db->fetch($query, ['uid' => $associate_id]);

    if (!$associate_data) {
        // Handle case where user exists but no MLM profile
        // Create a default profile or redirect
        // For now, redirect with error
        header("Location: login.php?error=associate_profile_missing");
        exit;
    }

    // Update session data with latest from DB
    $associate_level = $associate_data['current_level'];
    $_SESSION['level'] = $associate_level;

} catch (Exception $e) {
    error_log("Error fetching associate data: " . $e->getMessage());
    $associate_data = [];
}

// Get dashboard statistics from mlm_profiles directly (optimized)
$stats = [
    'total_business' => $associate_data['lifetime_sales'] ?? 0,
    'total_commission' => $associate_data['total_commission'] ?? 0,
    'direct_team' => $associate_data['direct_referrals'] ?? 0,
    'total_team' => $associate_data['total_team_size'] ?? 0
];

// Level targets and progress
$level_targets = [
    'Associate' => ['min' => 0, 'max' => 1000000, 'commission' => 5, 'reward' => 'Mobile'],
    'Sr. Associate' => ['min' => 1000000, 'max' => 3500000, 'commission' => 7, 'reward' => 'Tablet'],
    'BDM' => ['min' => 3500000, 'max' => 7000000, 'commission' => 10, 'reward' => 'Laptop'],
    'Sr. BDM' => ['min' => 7000000, 'max' => 15000000, 'commission' => 12, 'reward' => 'Tour'],
    'Vice President' => ['min' => 15000000, 'max' => 30000000, 'commission' => 15, 'reward' => 'Bike'],
    'President' => ['min' => 30000000, 'max' => 50000000, 'commission' => 18, 'reward' => 'Bullet'],
    'Site Manager' => ['min' => 50000000, 'max' => 999999999, 'commission' => 20, 'reward' => 'Car']
];

$current_level_info = $level_targets[$associate_level] ?? $level_targets['Associate'];
$progress_percentage = 0;
if ($current_level_info['max'] > $current_level_info['min']) {
    $progress_percentage = min(100, (($stats['total_business'] - $current_level_info['min']) / ($current_level_info['max'] - $current_level_info['min'])) * 100);
}

// Page variables
$page_title = 'Associate Portal | APS Dream Homes';
$layout = 'modern';

ob_start();
?>

<div class="container py-5 mt-5">
    <!-- Header Section -->
    <div class="row mb-4 animate-fade-up">
        <div class="col-md-8 d-flex align-items-center">
            <div class="position-relative me-4">
                <img src="<?= !empty($associate_data['profile_image']) ? htmlspecialchars($associate_data['profile_image']) : 'https://ui-avatars.com/api/?name=' . urlencode($associate_name) . '&size=100&background=1e3a8a&color=fff' ?>"
                    alt="Profile" class="rounded-circle shadow-sm border border-3 border-white" style="width:100px; height:100px; object-fit:cover;">
                <span class="position-absolute bottom-0 end-0 bg-success border border-2 border-white rounded-circle p-2" title="Active"></span>
            </div>
            <div>
                <h1 class="display-6 fw-bold text-primary mb-1">Welcome, <?= htmlspecialchars($associate_name) ?>!</h1>
                <p class="text-muted mb-0">
                    <span class="badge bg-primary me-2"><?= htmlspecialchars($associate_level) ?></span>
                    <i class="fas fa-id-badge me-2"></i>ID: <?= htmlspecialchars($associate_data['referral_code'] ?? 'N/A') ?>
                </p>
            </div>
        </div>
        <div class="col-md-4 text-md-end d-flex align-items-center justify-content-md-end mt-3 mt-md-0">
            <div class="card bg-light border-0 shadow-sm p-3 w-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">Next Level Progress</span>
                    <span class="fw-bold text-primary small"><?= number_format($progress_percentage, 1) ?>%</span>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-gradient-primary" role="progressbar" style="width: <?= $progress_percentage ?>%"></div>
                </div>
                <div class="d-flex justify-content-between mt-2 small text-muted">
                    <span>Target: ₹<?= number_format($current_level_info['max']) ?></span>
                    <span>Gap: ₹<?= number_format(max(0, $current_level_info['max'] - $stats['total_business'])) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="row g-4 mb-5 animate-fade-up" style="animation-delay: 0.1s;">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100 transition-hover">
                <div class="icon-box bg-primary-soft rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-chart-line text-primary fs-4"></i>
                </div>
                <h3 class="fw-bold mb-1">₹<?= number_format($stats['total_business']) ?></h3>
                <p class="text-muted mb-0">Total Business</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100 transition-hover">
                <div class="icon-box bg-success-soft rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-wallet text-success fs-4"></i>
                </div>
                <h3 class="fw-bold mb-1">₹<?= number_format($stats['total_commission']) ?></h3>
                <p class="text-muted mb-0">Total Commission</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100 transition-hover">
                <div class="icon-box bg-info-soft rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-users text-info fs-4"></i>
                </div>
                <h3 class="fw-bold mb-1"><?= number_format($stats['total_team']) ?></h3>
                <p class="text-muted mb-0">Team Size</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100 transition-hover">
                <div class="icon-box bg-warning-soft rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-user-plus text-warning fs-4"></i>
                </div>
                <h3 class="fw-bold mb-1"><?= number_format($stats['direct_team']) ?></h3>
                <p class="text-muted mb-0">Direct Referrals</p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-5 animate-fade-up" style="animation-delay: 0.2s;">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h5 class="fw-bold mb-4"><i class="fas fa-bolt me-2 text-warning"></i>Quick Actions</h5>
                <div class="d-flex flex-wrap gap-3">
                    <a href="mlm-opportunity.php" class="btn btn-outline-primary rounded-pill px-4">
                        <i class="fas fa-network-wired me-2"></i>View Tree
                    </a>
                    <a href="commission_dashboard.php" class="btn btn-outline-success rounded-pill px-4">
                        <i class="fas fa-file-invoice-dollar me-2"></i>Commissions
                    </a>
                    <a href="add_customer.php" class="btn btn-primary rounded-pill px-4">
                        <i class="fas fa-user-plus me-2"></i>Add Customer
                    </a>
                    <a href="marketing_materials.php" class="btn btn-outline-info rounded-pill px-4">
                        <i class="fas fa-share-alt me-2"></i>Share Link
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/modern.php';
?>
