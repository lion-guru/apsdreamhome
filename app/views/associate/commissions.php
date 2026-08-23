<?php
$page_title = $page_title ?? __('assoc_comm_page_title', [], 'My Commissions - APS Dream Home');
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
    'direct_sale' => ['label' => __('assoc_comm_type_direct_sale', [], 'Direct Sale'), 'icon' => 'fa-handshake', 'color' => 'primary'],
    'override' => ['label' => __('assoc_comm_type_override', [], 'Override'), 'icon' => 'fa-layer-group', 'color' => 'info'],
    'level_bonus' => ['label' => __('assoc_comm_type_level_bonus', [], 'Level Bonus'), 'icon' => 'fa-stairs', 'color' => 'success'],
    'generation_bonus' => ['label' => __('assoc_comm_type_generation_bonus', [], 'Generation Bonus'), 'icon' => 'fa-users', 'color' => 'warning'],
    'matching_bonus' => ['label' => __('assoc_comm_type_matching_bonus', [], 'Matching Bonus'), 'icon' => 'fa-code-compare', 'color' => 'danger'],
    'rank_bonus' => ['label' => __('assoc_comm_type_rank_bonus', [], 'Rank Bonus'), 'icon' => 'fa-medal', 'color' => 'primary'],
    'royalty_pool' => ['label' => __('assoc_comm_type_royalty_pool', [], 'Royalty Pool'), 'icon' => 'fa-crown', 'color' => 'warning'],
    'infinity_override' => ['label' => __('assoc_comm_type_infinity_override', [], 'Infinity Override'), 'icon' => 'fa-infinity', 'color' => 'dark'],
    'performance_bonus' => ['label' => __('assoc_comm_type_performance_bonus', [], 'Performance Bonus'), 'icon' => 'fa-chart-line', 'color' => 'info'],
    'team_bonus' => ['label' => __('assoc_comm_type_team_bonus', [], 'Team Bonus'), 'icon' => 'fa-people-group', 'color' => 'secondary'],
];
?>
<div class="container-fluid px-4">
    <!-- Withdrawal Banner -->
    <div class="alert alert-info d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <i class="fas fa-wallet me-2"></i>
            <strong>₹<?php echo number_format($total_earned); ?></strong> <?php echo __('assoc_comm_total_earned', [], 'earned'); ?> &bull;
            <strong>₹<?php echo number_format($total_pending); ?></strong> <?php echo __('assoc_comm_pending', [], 'pending'); ?>
        </div>
        <a href="<?php echo BASE_URL; ?>/associate/wallet/withdraw" class="btn btn-success btn-sm">
            <i class="fas fa-arrow-right me-1"></i><?php echo __('assoc_comm_request_withdrawal', [], 'Request Withdrawal'); ?>
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body aps-cp-card-body">
                    <h6><i class="fas fa-check-circle me-1"></i><?php echo __('assoc_comm_total_earned', [], 'Total Earned'); ?></h6>
                    <h3 class="mb-0">₹<?php echo number_format($total_earned); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-warning text-white">
                <div class="card-body aps-cp-card-body">
                    <h6><i class="fas fa-clock me-1"></i><?php echo __('assoc_comm_pending', [], 'Pending'); ?></h6>
                    <h3 class="mb-0">₹<?php echo number_format($total_pending); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body aps-cp-card-body">
                    <h6><i class="fas fa-list me-1"></i><?php echo __('assoc_comm_total_transactions', [], 'Total Transactions'); ?></h6>
                    <h3 class="mb-0"><?php echo count($commissions); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Commission Breakdown by Type -->
    <?php if (!empty($breakdown)): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-chart-pie text-primary me-2"></i><?php echo __('assoc_comm_breakdown', [], 'Commission Breakdown by Type'); ?></h5>
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
                        <div class="card h-100 border-0 style-25547">
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center style-27974">
                                        <i class="fas <?php echo e($meta['icon']); ?> text-<?php echo e($meta['color']); ?>"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark"><?php echo e($meta['label']); ?></div>
                                        <small class="text-muted"><?php echo e($b['count']); ?> <?php echo __('assoc_comm_transactions', [], 'transactions'); ?></small>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-end">
                                    <div>
                                        <div class="text-muted small"><?php echo __('assoc_comm_total', [], 'Total'); ?></div>
                                        <div class="fw-bold text-dark style-64545">₹<?php echo number_format($b['total_amount']); ?></div>
                                    </div>
                                    <div class="text-end">
                                        <div class="text-muted small"><?php echo __('assoc_comm_paid_pending', [], 'Paid / Pending'); ?></div>
                                        <span class="badge bg-success">₹<?php echo number_format($b['paid_amount']); ?></span>
                                        <?php if ($b['pending_amount'] > 0): ?>
                                        <span class="badge bg-warning">₹<?php echo number_format($b['pending_amount']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <!-- Mini progress bar -->
                                <div class="progress mt-2 style-83142">
                                    <div class="progress-bar bg-success style-96169"></div>
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
    <?php echo CSRFProtection::csrfField(); ?>
                <div class="col-md-3">
                    <label class="form-label small"><?php echo __('assoc_comm_status', [], 'Status'); ?></label>
                    <select name="status" class="form-select form-select-sm">
                        <option value=""><?php echo __('assoc_comm_all_status', [], 'All Status'); ?></option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>><?php echo __('assoc_comm_pending', [], 'Pending'); ?></option>
                        <option value="paid" <?php echo $status_filter === 'paid' ? 'selected' : ''; ?>><?php echo __('assoc_comm_paid', [], 'Paid'); ?></option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>><?php echo __('assoc_comm_cancelled', [], 'Cancelled'); ?></option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small"><?php echo __('assoc_comm_type', [], 'Type'); ?></label>
                    <select name="type" class="form-select form-select-sm">
                        <option value=""><?php echo __('assoc_comm_all_types', [], 'All Types'); ?></option>
                        <option value="direct" <?php echo $type_filter === 'direct' ? 'selected' : ''; ?>><?php echo __('assoc_comm_direct', [], 'Direct'); ?></option>
                        <option value="team" <?php echo $type_filter === 'team' ? 'selected' : ''; ?>><?php echo __('assoc_comm_team', [], 'Team'); ?></option>
                        <option value="referral" <?php echo $type_filter === 'referral' ? 'selected' : ''; ?>><?php echo __('assoc_comm_referral', [], 'Referral'); ?></option>
                        <option value="bonus" <?php echo $type_filter === 'bonus' ? 'selected' : ''; ?>><?php echo __('assoc_comm_bonus', [], 'Bonus'); ?></option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small"><?php echo __('assoc_comm_from', [], 'From'); ?></label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="<?php echo htmlspecialchars($date_from ?? ''); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small"><?php echo __('assoc_comm_to', [], 'To'); ?></label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="<?php echo htmlspecialchars($date_to ?? ''); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search me-1"></i><?php echo __('assoc_comm_filter', [], 'Filter'); ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Commission Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-money-bill-wave text-warning me-2"></i><?php echo __('assoc_comm_history', [], 'Commission History'); ?></h5>
            <a href="<?php echo BASE_URL; ?>/associate/commissions" class="btn btn-sm btn-outline-secondary" title="<?php echo __('assoc_comm_reset', [], 'Reset filters'); ?>">
                <i class="fas fa-redo me-1"></i><?php echo __('assoc_comm_reset', [], 'Reset'); ?>
            </a>
        </div>
        <div class="card-body p-0">
            <?php if (empty($commissions)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-coins fa-4x text-muted mb-3"></i>
                    <p class="text-muted"><?php echo __('assoc_comm_empty', [], 'No commission transactions found.'); ?></p>
                    <?php if ($status_filter || $type_filter || $date_from || $date_to): ?>
                        <a href="<?php echo BASE_URL; ?>/associate/commissions" class="btn btn-sm btn-outline-primary"><?php echo __('assoc_comm_clear_filters', [], 'Clear Filters'); ?></a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th><?php echo __('assoc_comm_th_property', [], 'Property'); ?></th>
                                <th><?php echo __('assoc_comm_th_type', [], 'Type'); ?></th>
                                <th><?php echo __('assoc_comm_th_amount', [], 'Amount'); ?></th>
                                <th><?php echo __('assoc_comm_th_status', [], 'Status'); ?></th>
                                <th><?php echo __('assoc_comm_th_date', [], 'Date'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($commissions as $c): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($c['property'] ?? __('assoc_comm_na', [], 'N/A')); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo e(ucfirst($c['commission_type'] ?? __('assoc_comm_na', [], 'N/A'))); ?></span></td>
                                    <td><strong>₹<?php echo number_format($c['amount'] ?? 0); ?></strong></td>
                                    <td>
                                        <span class="badge bg-<?php echo ($c['status'] ?? '') === 'paid' ? 'success' : (($c['status'] ?? '') === 'cancelled' ? 'danger' : 'warning'); ?>">
                                            <?php echo e(ucfirst($c['status'] ?? __('assoc_comm_pending', [], 'Pending'))); ?>
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
                                <a class="page-link" href="<?php echo e($pagination_url); ?>page=<?php echo (int)($current_page_no - 1); ?>"><?php echo __('assoc_comm_pagination_prev', [], 'Previous'); ?></a>
                            </li>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i === $current_page_no ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?php echo e($pagination_url); ?>page=<?php echo (int)$i; ?>"><?php echo (int)$i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo $current_page_no >= $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo e($pagination_url); ?>page=<?php echo (int)($current_page_no + 1); ?>"><?php echo __('assoc_comm_pagination_next', [], 'Next'); ?></a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
