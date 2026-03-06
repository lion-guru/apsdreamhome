<?php
require_once __DIR__ . '/init.php';

// Check authentication and customer role
requireAuth('login.php');
requireRole('customer', 'index.php');

$uid = getAuthUserId();
$page_title = 'My Investments - APS Dream Homes';

// Fetch Active Investments (Plots)
$investments = [];
$db = \App\Core\App::database();
$sql = "SELECT p.*, s.site_name, s.location as site_location 
        FROM plots p 
        LEFT JOIN site_master s ON p.site_id = s.id 
        WHERE p.customer_id = ? AND p.status = 'active'
        ORDER BY p.updated_at DESC";
$investments = $db->query($sql, [$uid])->fetchAll(\PDO::FETCH_ASSOC);

include 'includes/user_header.php';
include 'includes/user_sidebar.php';
?>

<main class="user-main-content">
    <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="h3 mb-0 text-gray-800">My Investments</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="customer_dashboard.php" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item active">Investments</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Investment Summary -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 bg-primary text-white">
                    <div class="card-body">
                        <h6 class="text-white-50 small fw-bold text-uppercase mb-2">Total Active Plots</h6>
                        <h3 class="mb-0 fw-bold"><?php echo count($investments); ?></h3>
                    </div>
                </div>
            </div>
            <!-- More stats can be added here if needed -->
        </div>

        <!-- Investments List -->
        <div class="row g-4">
            <?php if (empty($investments)): ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm text-center py-5">
                        <div class="card-body">
                            <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                            <h5>No active investments found</h5>
                            <p class="text-muted">You haven't purchased any plots yet.</p>
                            <a href="property.php" class="btn btn-primary px-4 shadow-none">Start Investing</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($investments as $inv): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm investment-card-hover overflow-hidden">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="bg-primary-subtle text-primary p-2 rounded">
                                        <i class="fas fa-map-marked-alt fa-lg"></i>
                                    </div>
                                    <span class="badge bg-success rounded-pill px-3">ACTIVE</span>
                                </div>
                                <h5 class="card-title fw-bold mb-1"><?php echo h($inv['site_name']); ?></h5>
                                <p class="text-muted small mb-3">
                                    <i class="fas fa-map-marker-alt me-1 text-danger"></i>
                                    <?php echo h($inv['site_location']); ?>
                                </p>
                                <hr class="my-3 opacity-10">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <label class="text-muted small d-block mb-1">Plot Number</label>
                                        <span class="fw-bold text-dark"><?php echo h($inv['plot_number']); ?></span>
                                    </div>
                                    <div class="col-6">
                                        <label class="text-muted small d-block mb-1">Sector</label>
                                        <span class="fw-bold text-dark"><?php echo h($inv['sector']); ?></span>
                                    </div>
                                    <div class="col-6">
                                        <label class="text-muted small d-block mb-1">Size</label>
                                        <span class="fw-bold text-dark"><?php echo h($inv['plot_size']); ?> sq.ft</span>
                                    </div>
                                    <div class="col-6">
                                        <label class="text-muted small d-block mb-1">Rate</label>
                                        <span class="fw-bold text-dark">₹<?php echo number_format($inv['rate']); ?>/sq.ft</span>
                                    </div>
                                </div>
                                <div class="d-grid mt-4">
                                    <button class="btn btn-outline-primary shadow-none">View Details</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<style>
.investment-card-hover {
    transition: transform 0.3s ease, shadow 0.3s ease;
}
.investment-card-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}
</style>

<?php include 'includes/user_footer.php'; ?>
