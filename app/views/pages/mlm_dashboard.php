<?php
$page_title = $page_title ?? 'My MLM Dashboard';
$page_description = $page_description ?? 'MLM Dashboard - Track your network, commissions, and rank progress';
$current_page = 'mlm_dashboard';
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0"><i class="fas fa-network-wired me-2"></i>My MLM Dashboard</h2>
        <a href="<?= BASE_URL ?>/associate/register?ref=<?= urlencode($referral_code ?? 'APSREF') ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-user-plus me-1"></i>Become Associate
        </a>
    </div>

    <?php if (empty($associate_id) || $associate_id === 'N/A'): ?>
        <!-- Not an associate yet -->
        <div class="text-center py-5">
            <div class="aps-cp-card" style="max-width: 500px; margin: 0 auto;">
                <div class="aps-cp-card-body text-center py-5">
                    <div class="display-1 text-muted mb-3"><i class="fas fa-users"></i></div>
                    <h3 class="fw-bold mb-3">Become an APS Associate</h3>
                    <p class="text-muted mb-4">Join our network of successful associates and start earning commissions today.</p>
                    <a href="<?= BASE_URL ?>/associate/register?ref=<?= urlencode($referral_code ?? 'APSREF') ?>" class="btn btn-primary btn-lg px-4">
                        <i class="fas fa-user-plus me-2"></i>Become Associate
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Associate Dashboard -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="aps-cp-stat aps-cp-stat--blue">
                    <div class="aps-cp-stat-icon"><i class="fas fa-users"></i></div>
                    <div class="aps-cp-stat-body">
                        <div class="aps-cp-stat-value"><?= number_format($team_size ?? 0) ?></div>
                        <div class="aps-cp-stat-label">Team Size</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="aps-cp-stat aps-cp-stat--green">
                    <div class="aps-cp-stat-icon"><i class="fas fa-users"></i></div>
                    <div class="aps-cp-stat-body">
                        <div class="aps-cp-stat-value"><?= number_format($total_sales ?? 0) ?></div>
                        <div class="aps-cp-stat-label">Total Sales</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="aps-cp-stat aps-cp-stat--orange">
                    <div class="aps-cp-stat-icon"><i class="fas fa-rupee-sign"></i></div>
                    <div class="aps-cp-stat-body">
                        <div class="aps-cp-stat-value">₹<?= number_format($commission_earned ?? 0, 0) ?></div>
                        <div class="aps-cp-stat-label">Commission Earned</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="aps-cp-stat aps-cp-stat--purple">
                    <div class="aps-cp-stat-icon"><i class="fas fa-clock"></i></div>
                    <div class="aps-cp-stat-body">
                        <div class="aps-cp-stat-value">₹<?= number_format($pending_payout ?? 0, 0) ?></div>
                        <div class="aps-cp-stat-label">Pending Payout</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-8">
                <div class="aps-cp-card">
                    <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Rank Progress</div>
                    </div>
                    <div class="aps-cp-card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-2">Current Rank</h6>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="badge fs-6 px-3 py-2" style="background: <?= $current_rank_color ?? '#94a3b8' ?>;">
                                        <i class="<?= $current_rank['badge_icon'] ?? 'fa-user' ?> me-1"></i>
                                        <?= $current_rank_label ?? 'Associate' ?>
                                    </span>
                                    <span class="text-muted small">Rate: <?= $current_rank_rate ?? 5 ?>%</span>
                                </div>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <h6 class="fw-bold mb-2">Next Rank</h6>
                                <div class="d-flex align-items-center justify-content-md-end gap-3">
                                    <span class="badge bg-secondary px-3 py-2" style="background: <?= $next_rank_color ?? '#64748b' ?>;">
                                        <i class="<?= $next_rank['badge_icon'] ?? 'fa-user' ?> me-1"></i>
                                        <?= $next_rank_label ?? 'Sr. Associate' ?>
                                    </span>
                                    <span class="text-muted small">Rate: <?= $next_rank_rate ?? 7 ?>%</span>
                                </div>
                            </div>
                        </div>
                        <div class="progress" style="height: 12px;">
                            <div class="progress-bar" role="progressbar" style="width: <?= $rank_progress_pct ?? 0 ?>%; background: <?= $current_rank_color ?? '#667eea' ?>;" aria-valuenow="<?= $rank_progress_pct ?? 0 ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <small class="text-muted"><?= $rank_progress_pct ?? 0 ?>% to next rank (<?= number_format($amount_to_next_rank ?? 1000000) ?> more volume needed)</small>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="aps-cp-card">
                        <div class="aps-cp-card-header">
                            <h6 class="mb-0"><i class="fas fa-trophy me-2"></i>Rank Benefits</div>
                        </div>
                        <div class="aps-cp-card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Rank</th>
                                            <th class="text-center">Direct</th>
                                            <th class="text-center">Volume</th>
                                            <th class="text-center">Team</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rank_benefits ?? [] as $rank): ?>
                                        <tr <?= ($rank['rank_name'] === ($current_rank['rank_name'] ?? '')) ? 'class="table-primary"' : '' ?>>
                                            <td>
                                                <span class="badge px-2 py-1" style="background: <?= $rank['color_code'] ?? '#94a3b8' ?>;">
                                                    <i class="<?= $rank['badge_icon'] ?? 'fa-user' ?> me-1"></i>
                                                    <?= htmlspecialchars($rank['rank_name']) ?>
                                                </span>
                                            </td>
                                            <td class="text-center"><?= $rank['direct_sale_pct'] ?? 0 ?>%</td>
                                            <td class="text-center"><?= number_format($rank['min_volume'] ?? 0) ?></td>
                                            <td class="text-center"><?= $rank['min_leg_count'] ?? 0 ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="aps-cp-card">
                        <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Commission History</h5>
                            <a href="<?= BASE_URL ?>/associate/commissions" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="aps-cp-card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Booking</th>
                                            <th class="text-end">Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($commission_history)): ?>
                                            <?php foreach ($commission_history as $ch): ?>
                                            <tr>
                                                <td><?= date('d M Y', strtotime($ch['created_at'] ?? '')) ?></td>
                                                <td><span class="badge bg-info"><?= htmlspecialchars($ch['type'] ?? '') ?></span></td>
                                                <td><?= htmlspecialchars($ch['booking_number'] ?? '-') ?></td>
                                                <td class="text-end text-success fw-bold">₹<?= number_format((float)($ch['amount'] ?? 0), 2) ?></td>
                                                <td><span class="badge bg-<?= ($ch['status'] ?? '') === 'paid' ? 'success' : (($ch['status'] ?? '') === 'pending' ? 'warning' : 'secondary') ?>"><?= ucfirst($ch['status'] ?? '') ?></span></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-5">No commission history yet</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="aps-cp-card">
                        <div class="aps-cp-card-header">
                            <h6 class="mb-0"><i class="fas fa-users me-2"></i>Downline Members</div>
                        </div>
                        <div class="aps-cp-card-body p-0">
                            <?php if (!empty($downline_members)): ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($downline_members as $dm): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-semibold"><?= htmlspecialchars($dm['name'] ?? '') ?></div>
                                            <small class="text-muted"><?= $dm['referral_code'] ?? '' ?></small>
                                        </div>
                                        <span class="badge bg-info"><?= $dm['level'] ?? 1 ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-users fa-2x text-muted mb-2 d-block"></i>
                                    <p class="text-muted mb-3">No downline members yet</p>
                                    <a href="<?= BASE_URL ?>/associate/network/tree" class="btn btn-sm btn-outline-primary">View Network Tree</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-3">
                <div class="col-lg-8">
                    <div class="aps-cp-card">
                        <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Payout History</h5>
                            <a href="<?= BASE_URL ?>/associate/commissions" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="aps-cp-card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Period</th>
                                            <th class="text-end">Amount</th>
                                            <th>Status</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($payout_history)): ?>
                                            <?php foreach ($payout_history as $ph): ?>
                                            <tr>
                                                <td><?= date('d M Y', strtotime($ph['created_at'] ?? '')) ?></td>
                                                <td><?= htmlspecialchars($ph['period'] ?? '') ?></td>
                                                <td class="text-end fw-bold text-success">₹<?= number_format((float)($ph['amount'] ?? 0), 2) ?></td>
                                                <td><span class="badge bg-<?= ($ph['status'] ?? '') === 'paid' ? 'success' : (($ph['status'] ?? '') === 'pending' ? 'warning' : 'secondary') ?>"><?= ucfirst($ph['status'] ?? '') ?></span></td>
                                                <td class="text-end">
                                                    <?php if (($ph['status'] ?? '') === 'paid'): ?>
                                                        <a href="<?= BASE_URL ?>/associate/commissions/view/<?= $ph['id'] ?? '' ?>" class="btn btn-sm btn-outline-primary">View</a>
                                                    <?php else: ?>
                                                        <span class="text-muted small">Processing</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-5">No payout history yet</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="aps-cp-card">
                        <div class="aps-cp-card-header">
                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Quick Actions</div>
                        </div>
                        <div class="aps-cp-card-body">
                            <div class="d-grid gap-2">
                                <a href="<?= BASE_URL ?>/associate/network/tree" class="btn btn-outline-primary">
                                    <i class="fas fa-project-diagram me-2"></i>View Network Tree
                                </a>
                                <a href="<?= BASE_URL ?>/associate/team" class="btn btn-outline-primary">
                                    <i class="fas fa-users me-2"></i>My Team
                                </a>
                                <a href="<?= BASE_URL ?>/associate/commissions" class="btn btn-outline-primary">
                                    <i class="fas fa-rupee-sign me-2"></i>Commissions
                                </a>
                                <a href="<?= BASE_URL ?>/associate/rank-eligibility" class="btn btn-outline-primary">
                                    <i class="fas fa-trophy me-2"></i>Rank Eligibility
                                </a>
                                <a href="<?= BASE_URL ?>/associate/referral" class="btn btn-outline-primary">
                                    <i class="fas fa-share-alt me-2"></i>Refer & Earn
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>