<?php
$extraHead = '<style>
.investment-card-hover { transition: transform 0.3s ease, shadow 0.3s ease; }
.investment-card-hover:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
</style>';

$investments = [];
try {
    $stmt = $this->db->prepare("SELECT p.*, s.site_name, s.location as site_location 
        FROM plots p LEFT JOIN site_master s ON p.site_id = s.id 
        WHERE p.customer_id = ? AND p.status = 'active' ORDER BY p.updated_at DESC LIMIT 20");
    $userId = $_SESSION['user_id'] ?? $_SESSION['customer_id'] ?? 0;
    $stmt->execute([$userId]);
    $investments = $stmt->fetchAll(\PDO::FETCH_ASSOC);
} catch (\Exception $e) {
    error_log('Investments fetch error: ' . $e->getMessage());
}
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="h3 mb-0"><i class="fas fa-chart-line me-2 text-primary"></i>My Investments</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>user/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active">Investments</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-primary text-white">
                <div class="card-body">
                    <h6 class="text-white-50 small fw-bold text-uppercase mb-2">Total Active Plots</h6>
                    <h3 class="mb-0 fw-bold"><?= count($investments) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <?php if (empty($investments)): ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm text-center py-5">
                    <div class="card-body">
                        <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                        <h5>No active investments found</h5>
                        <p class="text-muted">You haven't purchased any plots yet.</p>
                        <a href="<?= BASE_URL ?>properties" class="btn btn-primary px-4">Browse Properties</a>
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
                            <h5 class="card-title fw-bold mb-1"><?= htmlspecialchars($inv['site_name'] ?? 'N/A') ?></h5>
                            <p class="text-muted small mb-3">
                                <i class="fas fa-map-marker-alt me-1 text-danger"></i>
                                <?= htmlspecialchars($inv['site_location'] ?? 'N/A') ?>
                            </p>
                            <hr class="my-3 opacity-10">
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="text-muted small d-block mb-1">Plot Number</label>
                                    <span class="fw-bold"><?= htmlspecialchars($inv['plot_number'] ?? 'N/A') ?></span>
                                </div>
                                <div class="col-6">
                                    <label class="text-muted small d-block mb-1">Sector</label>
                                    <span class="fw-bold"><?= htmlspecialchars($inv['sector'] ?? 'N/A') ?></span>
                                </div>
                                <div class="col-6">
                                    <label class="text-muted small d-block mb-1">Size</label>
                                    <span class="fw-bold"><?= htmlspecialchars($inv['plot_size'] ?? 'N/A') ?> sq.ft</span>
                                </div>
                                <div class="col-6">
                                    <label class="text-muted small d-block mb-1">Rate</label>
                                    <span class="fw-bold">&#8377;<?= number_format($inv['rate'] ?? 0) ?>/sq.ft</span>
                                </div>
                            </div>
                            <div class="d-grid mt-4">
                                <a href="<?= BASE_URL ?>properties/<?= $inv['id'] ?? '' ?>" class="btn btn-outline-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
