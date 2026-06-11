<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-cubes me-2"></i>MLM & Real Estate Enterprise</h1>
        <a href="<?= BASE_URL ?>/admin/mlm-realestate/cron" class="btn btn-outline-warning btn-sm" onclick="return confirm('Run compliance cron?')"><i class="fas fa-play me-1"></i>Run Compliance Cron</a>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white shadow-sm border-0">
                <div class="card-body text-center py-3">
                    <h6>Total Networkers</h6>
                    <h2 class="mb-0 fw-bold"><?= (int)($stats['total_networkers'] ?? 0) ?></h2>
                    <small>Paid onboarding</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white shadow-sm border-0">
                <div class="card-body text-center py-3">
                    <h6>Free Consultants</h6>
                    <h2 class="mb-0 fw-bold"><?= (int)($stats['total_consultants'] ?? 0) ?></h2>
                    <small>Slab-based payout</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white shadow-sm border-0">
                <div class="card-body text-center py-3">
                    <h6>Available Plots</h6>
                    <h2 class="mb-0 fw-bold"><?= (int)($stats['available_plots'] ?? 0) ?></h2>
                    <small>Out of <?= (int)($stats['total_plots'] ?? 0) ?> total</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark shadow-sm border-0">
                <div class="card-body text-center py-3">
                    <h6>Pending Approvals</h6>
                    <h2 class="mb-0 fw-bold"><?= (int)($stats['pending_bookings'] ?? 0) ?></h2>
                    <small><?= (int)($stats['pending_rera'] ?? 0) ?> RERA · <?= (int)($stats['active_salaries'] ?? 0) ?> salaries</small>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-danger text-white shadow-sm border-0">
                <div class="card-body text-center py-3">
                    <h6>Total Revenue</h6>
                    <h2 class="mb-0 fw-bold">₹<?= number_format((float)($stats['total_revenue'] ?? 0)) ?></h2>
                    <small>From confirmed/completed bookings</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white shadow-sm border-0">
                <div class="card-body text-center py-3">
                    <h6>Commission Paid</h6>
                    <h2 class="mb-0 fw-bold">₹<?= number_format((float)($stats['total_commission_paid'] ?? 0)) ?></h2>
                    <small>Total commission released</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white shadow-sm border-0">
                <div class="card-body text-center py-3">
                    <h6>Booked Plots</h6>
                    <h2 class="mb-0 fw-bold"><?= (int)($stats['booked_plots'] ?? 0) ?></h2>
                    <small>Currently booked/sold</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white shadow-sm border-0">
                <div class="card-body text-center py-3">
                    <h6>Total Bookings</h6>
                    <h2 class="mb-0 fw-bold"><?= (int)($stats['total_bookings'] ?? 0) ?></h2>
                    <small>Active bookings</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Bookings</h5>
                    <div>
                        <a href="<?= BASE_URL ?>/admin/mlm-realestate/bookings/create" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>New Booking</a>
                        <a href="<?= BASE_URL ?>/admin/mlm-realestate/bookings" class="btn btn-sm btn-outline-secondary">View All</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light"><tr><th>ID</th><th>Agent</th><th>Amount</th><th>Paid</th><th>Status</th><th>Date</th></tr></thead>
                            <tbody>
                                <?php if (!empty($recent_bookings)): foreach ($recent_bookings as $b): ?>
                                <tr>
                                    <td>#<?= $b['id'] ?></td>
                                    <td><?= htmlspecialchars($b['agent_name'] ?? 'N/A') ?></td>
                                    <td>₹<?= number_format((float)($b['total_amount'] ?? 0)) ?></td>
                                    <td>₹<?= number_format((float)($b['amount'] ?? 0)) ?></td>
                                    <td><span class="badge bg-<?= $b['status'] === 'completed' ? 'success' : ($b['status'] === 'cancelled' ? 'danger' : 'warning') ?>"><?= htmlspecialchars($b['status'] ?? '') ?></span></td>
                                    <td><?= htmlspecialchars($b['created_at'] ?? '') ?></td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-calendar fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No bookings found</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white"><h5 class="mb-0">Quick Actions</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= BASE_URL ?>/admin/mlm-realestate/packages" class="btn btn-outline-primary btn-sm"><i class="fas fa-box me-1"></i>Manage Packages</a>
                        <a href="<?= BASE_URL ?>/admin/mlm-realestate/networkers" class="btn btn-outline-info btn-sm"><i class="fas fa-user-tie me-1"></i>Networkers</a>
                        <a href="<?= BASE_URL ?>/admin/mlm-realestate/free-consultants" class="btn btn-outline-secondary btn-sm"><i class="fas fa-user-friends me-1"></i>Free Consultants</a>
                        <a href="<?= BASE_URL ?>/admin/mlm-realestate/rera" class="btn btn-outline-warning btn-sm"><i class="fas fa-gavel me-1"></i>RERA Requests</a>
                        <a href="<?= BASE_URL ?>/admin/mlm-realestate/plots" class="btn btn-outline-success btn-sm"><i class="fas fa-th me-1"></i>Plot Inventory</a>
                        <a href="<?= BASE_URL ?>/admin/mlm-realestate/salary" class="btn btn-outline-danger btn-sm"><i class="fas fa-money-bill-wave me-1"></i>Salary Tracker</a>
                    </div>
                </div>
            </div>
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white"><h5 class="mb-0">Today's Activity</h5></div>
                <div class="card-body aps-cp-card-body">
                    <p class="mb-1"><strong>Flushed (Cap):</strong> ₹<?= number_format((float)($daily_cap_flushed ?? 0), 2) ?></p>
                    <p class="mb-0"><strong>Total Bookings:</strong> <?= (int)($stats['total_bookings'] ?? 0) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>