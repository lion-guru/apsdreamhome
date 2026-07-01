<?php
$page_title = $page_title ?? 'My Commissions - APS Dream Home';
$commissions = $commissions ?? [];
$total_earned = $total_earned ?? 0;
$total_pending = $total_pending ?? 0;
$breakdown = $breakdown ?? [];
$status_filter = $status_filter ?? '';
$type_filter = $type_filter ?? '';
$date_from = $date_from ?? '';
$date_to = $date_to ?? '';
$current_page_no = $current_page_no ?? 1;
$total_pages = $total_pages ?? 1;
$pagination_url = $pagination_url ?? BASE_URL . '/associate/commissions?';

$typeLabels = [
    'direct_sale' => ['label' => 'Direct Sale', 'icon' => 'fa-handshake', 'color' => 'primary'],
    'override' => ['label' => 'Override', 'icon' => 'fa-layer-group', 'color' => 'info'],
    'level_bonus' => ['label' => 'Level Bonus', 'icon' => 'fa-stairs', 'color' => 'success'],
    'generation_bonus' => ['label' => 'Generation Bonus', 'icon' => 'fa-users', 'color' => 'warning'],
    'matching_bonus' => ['label' => 'Matching Bonus', 'icon' => 'fa-code-compare', 'color' => 'danger'],
    'rank_bonus' => ['label' => 'Rank Bonus', 'icon' => 'fa-medal', 'color' => 'purple'],
    'royalty_pool' => ['label' => 'Royalty Pool', 'icon' => 'fa-crown', 'color' => 'gold'],
    'infinity_override' => ['label' => 'Infinity Override', 'icon' => 'fa-infinity', 'color' => 'dark'],
    'performance_bonus' => ['label' => 'Performance Bonus', 'icon' => 'fa-chart-line', 'color' => 'teal'],
    'team_bonus' => ['label' => 'Team Bonus', 'icon' => 'fa-people-group', 'color' => 'indigo'],
];
?>
<div class="container-fluid px-4">
    <!-- Withdrawal Banner -->
    <div class="alert alert-info d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <i class="fas fa-wallet me-2"></i>
            <strong>₹<?php echo number_format($total_earned); ?></strong> earned &bull;
            <strong>₹<?php echo number_format($total_pending); ?></strong> pending
        </div>
        <a href="<?php echo BASE_URL; ?>/associate/wallet/withdraw" class="btn btn-success btn-sm">
            <i class="fas fa-arrow-right me-1"></i>Request Withdrawal
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body aps-cp-card-body">
                    <h6><i class="fas fa-check-circle me-1"></i>Total Earned</h6>
                    <h3 class="mb-0">₹<?php echo number_format($total_earned); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-warning text-white">
                <div class="card-body aps-cp-card-body">
                    <h6><i class="fas fa-clock me-1"></i>Pending</h6>
                    <h3 class="mb-0">₹<?php echo number_format($total_pending); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body aps-cp-card-body">
                    <h6><i class="fas fa-list me-1"></i>Total Transactions</h6>
                    <h3 class="mb-0"><?php echo count($commissions); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Commission Breakdown by Type -->
    <?php if (!empty($breakdown)): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-chart-pie text-primary me-2"></i>Commission Breakdown by Type</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <?php foreach ($breakdown as $b):
                    $type = $b['commission_type'];
                    $meta = $typeLabels[$type] ?? ['label' => ucfirst(str_replace('_', ' ', $type)), 'icon' => 'fa-coins', 'color' => 'secondary'];
                    $paidPct = $b['total_amount'] > 0 ? round(($b['paid_amount'] / $b['total_amount']) * 100) : 0;
                ?>
                <div class="col-md-6 col-lg-4">
                    <a href="<?php echo BASE_URL; ?>/associate/commissions?type=<?php echo urlencode($type); ?>" class="text-decoration-none">
                        <div class="card h-100 border-0" style="background: #f8fafc; transition: all 0.2s;">
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background: rgba(var(--bs-<?php echo $meta['color']; ?>-rgb), 0.1);">
                                        <i class="fas <?php echo $meta['icon']; ?> text-<?php echo $meta['color']; ?>"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark"><?php echo $meta['label']; ?></div>
                                        <small class="text-muted"><?php echo $b['count']; ?> transactions</small>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-end">
                                    <div>
                                        <div class="text-muted small">Total</div>
                                        <div class="fw-bold text-dark" style="font-size: 1.1rem;">₹<?php echo number_format($b['total_amount']); ?></div>
                                    </div>
                                    <div class="text-end">
                                        <div class="text-muted small">Paid / Pending</div>
                                        <span class="badge bg-success">₹<?php echo number_format($b['paid_amount']); ?></span>
                                        <?php if ($b['pending_amount'] > 0): ?>
                                        <span class="badge bg-warning">₹<?php echo number_format($b['pending_amount']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <!-- Mini progress bar -->
                                <div class="progress mt-2" style="height: 4px;">
                                    <div class="progress-bar bg-success" style="width: <?php echo $paidPct; ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body aps-cp-card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="paid" <?php echo $status_filter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="direct" <?php echo $type_filter === 'direct' ? 'selected' : ''; ?>>Direct</option>
                        <option value="team" <?php echo $type_filter === 'team' ? 'selected' : ''; ?>>Team</option>
                        <option value="referral" <?php echo $type_filter === 'referral' ? 'selected' : ''; ?>>Referral</option>
                        <option value="bonus" <?php echo $type_filter === 'bonus' ? 'selected' : ''; ?>>Bonus</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="<?php echo htmlspecialchars($date_from); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="<?php echo htmlspecialchars($date_to); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search me-1"></i>Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Commission Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-money-bill-wave text-warning me-2"></i>Commission History</h5>
            <a href="<?php echo BASE_URL; ?>/associate/commissions" class="btn btn-sm btn-outline-secondary" title="Reset filters">
                <i class="fas fa-redo me-1"></i>Reset
            </a>
        </div>
        <div class="card-body p-0">
            <?php if (empty($commissions)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-coins fa-4x text-muted mb-3"></i>
                    <p class="text-muted">No commission transactions found.</p>
                    <?php if ($status_filter || $type_filter || $date_from || $date_to): ?>
                        <a href="<?php echo BASE_URL; ?>/associate/commissions" class="btn btn-sm btn-outline-primary">Clear Filters</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Property</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($commissions as $c): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($c['property'] ?? 'N/A'); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo ucfirst($c['commission_type'] ?? 'N/A'); ?></span></td>
                                    <td><strong>₹<?php echo number_format($c['amount'] ?? 0); ?></strong></td>
                                    <td>
                                        <span class="badge bg-<?php echo ($c['status'] ?? '') === 'paid' ? 'success' : (($c['status'] ?? '') === 'cancelled' ? 'danger' : 'warning'); ?>">
                                            <?php echo ucfirst($c['status'] ?? 'Pending'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($c['date'] ?? ''); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="d-flex justify-content-center py-3">
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?php echo $current_page_no <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo $pagination_url; ?>page=<?php echo $current_page_no - 1; ?>">Previous</a>
                            </li>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i === $current_page_no ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?php echo $pagination_url; ?>page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo $current_page_no >= $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo $pagination_url; ?>page=<?php echo $current_page_no + 1; ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
