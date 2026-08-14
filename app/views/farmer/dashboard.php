<?php
$page_title = $page_title ?? 'Farmer Dashboard';
$current_page = 'farmer-dashboard';
$extraHead = '<style>
.farmer-stat-card { background:#fff; border-radius:16px; padding:1.5rem; box-shadow:0 2px 12px rgba(0,0,0,0.06); border:1px solid #e2e8f0; height:100%; transition:all 0.2s; }
.farmer-stat-card:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(0,0,0,0.1); }
.stat-icon-f { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.3rem; margin-bottom:12px; }
.stat-icon-f.green { background:rgba(22,163,74,0.12); color:#16a34a; }
.stat-icon-f.blue { background:rgba(37,99,235,0.12); color:#2563eb; }
.stat-icon-f.orange { background:rgba(234,88,12,0.12); color:#ea580c; }
.stat-icon-f.purple { background:rgba(13,148,136,0.12); color:#0f766e; }
.stat-value-f { font-size:1.6rem; font-weight:700; color:#1e293b; }
.stat-label-f { font-size:0.85rem; color:#64748b; }
</style>';
$stats = $stats ?? ['total_holdings'=>0,'total_area'=>0,'amount_received'=>0,'pending_amount'=>0,'active_agreements'=>0];
$transactions = $transactions ?? [];
$land_holdings = $land_holdings ?? [];
?>

<div class="container py-4">
    <div class="card border-0 shadow-sm mb-4" class="style-97153">
        <div class="card-body p-4 text-white">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-2"><i class="fas fa-seedling me-2"></i>Welcome, <?php echo htmlspecialchars($_SESSION['farmer_name'] ?? $farmer['name'] ?? 'Farmer'); ?>!</h3>
                    <p class="mb-0 opacity-75">Track your land acquisition status, payments, and agreements in one place.</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <span class="badge bg-white text-success fs-6 px-3 py-2 rounded-pill">
                        <i class="fas fa-user me-1"></i>Farmer Portal
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="farmer-stat-card">
                <div class="stat-icon-f green"><i class="fas fa-map-marked-alt"></i></div>
                <div class="stat-value-f"><?php echo $stats['total_holdings']; ?></div>
                <div class="stat-label-f">Land Holdings</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="farmer-stat-card">
                <div class="stat-icon-f blue"><i class="fas fa-vector-square"></i></div>
                <div class="stat-value-f"><?php echo number_format($stats['total_area'], 2); ?> sq.ft</div>
                <div class="stat-label-f">Total Land Area</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="farmer-stat-card">
                <div class="stat-icon-f orange"><i class="fas fa-hand-holding-usd"></i></div>
                <div class="stat-value-f">â‚¹<?php echo number_format($stats['amount_received']); ?></div>
                <div class="stat-label-f">Amount Received</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="farmer-stat-card">
                <div class="stat-icon-f purple"><i class="fas fa-file-signature"></i></div>
                <div class="stat-value-f"><?php echo $stats['active_agreements']; ?></div>
                <div class="stat-label-f">Active Agreements</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history text-primary me-2"></i>Recent Transactions</h5>
                    <a href="<?php echo BASE_URL; ?>/farmer/payments" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($transactions)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-credit-card fa-3x mb-3"></i>
                        <p>No transactions yet</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr><th>Date</th><th>Type</th><th>Amount</th><th>Method</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $t): ?>
                                <tr>
                                    <td><?php echo date('d M Y', strtotime($t['created_at'] ?? 'now')); ?></td>
                                    <td><span class="badge bg-<?php echo ($t['transaction_type'] ?? '') === 'credit' ? 'success' : (($t['transaction_type'] ?? '') === 'debit' ? 'danger' : 'info'); ?>"><?php echo ucfirst($t['transaction_type'] ?? 'N/A'); ?></span></td>
                                    <td><strong>â‚¹<?php echo number_format($t['amount'] ?? 0); ?></strong></td>
                                    <td><?php echo htmlspecialchars($t['payment_method'] ?? 'N/A'); ?></td>
                                    <td><span class="badge bg-<?php echo ($t['status'] ?? '') === 'completed' ? 'success' : (($t['status'] ?? '') === 'pending' ? 'warning' : 'secondary'); ?>"><?php echo ucfirst($t['status'] ?? 'N/A'); ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fas fa-map-marked-alt text-success me-2"></i>Quick Links</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="d-grid gap-2">
                        <a href="<?php echo BASE_URL; ?>/farmer/land-holdings" class="btn btn-outline-success"><i class="fas fa-map me-2"></i>View Land Holdings</a>
                        <a href="<?php echo BASE_URL; ?>/farmer/payments" class="btn btn-outline-primary"><i class="fas fa-credit-card me-2"></i>Payment History</a>
                        <a href="<?php echo BASE_URL; ?>/farmer/agreements" class="btn btn-outline-info"><i class="fas fa-file-contract me-2"></i>My Agreements</a>
                        <a href="<?php echo BASE_URL; ?>/farmer/profile" class="btn btn-outline-secondary"><i class="fas fa-user-cog me-2"></i>Edit Profile</a>
                    </div>
                </div>
            </div>

            <?php if (!empty($land_holdings)): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fas fa-chart-pie text-warning me-2"></i>Acquisition Summary</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php
                    $acquired = count(array_filter($land_holdings, fn($l) => ($l['acquisition_status'] ?? '') === 'acquired'));
                    $pending = count(array_filter($land_holdings, fn($l) => ($l['acquisition_status'] ?? '') === 'pending'));
                    $negotiation = count(array_filter($land_holdings, fn($l) => ($l['acquisition_status'] ?? '') === 'negotiation'));
                    $total = count($land_holdings);
                    ?>
                    <div class="mb-2 d-flex justify-content-between">
                        <span><i class="fas fa-check-circle text-success me-1"></i>Acquired</span>
                        <span class="fw-bold"><?php echo $acquired; ?></span>
                    </div>
                    <?php if ($total > 0): ?>
                    <div class="progress mb-3" class="style-79794">
                        <div class="progress-bar bg-success" class="style-21974"></div>
                    </div>
                    <?php endif; ?>
                    <div class="mb-2 d-flex justify-content-between">
                        <span><i class="fas fa-clock text-warning me-1"></i>Negotiation</span>
                        <span class="fw-bold"><?php echo $negotiation; ?></span>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span><i class="fas fa-hourglass-half text-secondary me-1"></i>Pending</span>
                        <span class="fw-bold"><?php echo $pending; ?></span>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
