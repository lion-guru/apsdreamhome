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
                    <a href="/associate/tree" class="btn btn-outline-primary rounded-pill px-4">
                        <i class="fas fa-network-wired me-2"></i>View Tree
                    </a>
                    <a href="/associate/commissions" class="btn btn-outline-success rounded-pill px-4">
                        <i class="fas fa-file-invoice-dollar me-2"></i>Commissions
                    </a>
                    <a href="/associate/customers/add" class="btn btn-primary rounded-pill px-4">
                        <i class="fas fa-user-plus me-2"></i>Add Customer
                    </a>
                    <a href="/associate/marketing" class="btn btn-outline-info rounded-pill px-4">
                        <i class="fas fa-share-alt me-2"></i>Share Link
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
