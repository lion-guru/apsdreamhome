<?php

$associate = $associate ?? [];
$network = $network ?? [];
$commissions = $commissions ?? [];
$stats = $stats ?? [];
$page_title = $page_title ?? __('assoc_dashboard');
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<style>
.dashboard-card { transition: transform 0.2s; }
.dashboard-card:hover { transform: translateY(-5px); }
.stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
</style>
<div class="container-fluid px-4 py-3">
    <div class="card bg-gradient-primary text-white mb-4 border-0" style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1"><?= sprintf(__('assoc_welcome_back'), htmlspecialchars($_SESSION['user_name'] ?? __('assoc_dashboard'))); ?></h4>
                    <p class="mb-0 opacity-75"><?= __('assoc_track_performance') ?></p>
                </div>
                <div class="text-end">
                    <h5 class="mb-1"><?= __('assoc_current_rank') ?></h5>
                    <span class="badge bg-warning text-dark fs-6"><?php echo htmlspecialchars($associate['rank'] ?? 'Bronze'); ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card dashboard-card border-0 shadow-sm h-100">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small"><?= __('assoc_total_earnings') ?></p>
                            <h4 class="mb-0">₹<?php echo number_format($stats['total_earnings'] ?? 0); ?></h4>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10">
                            <i class="fas fa-rupee-sign text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card border-0 shadow-sm h-100">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small"><?= __('assoc_this_month') ?></p>
                            <h4 class="mb-0">₹<?php echo number_format($stats['month_earnings'] ?? 0); ?></h4>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10">
                            <i class="fas fa-calendar text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card border-0 shadow-sm h-100">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small"><?= __('assoc_network_size') ?></p>
                            <h4 class="mb-0"><?php echo number_format($stats['network_size'] ?? 0); ?></h4>
                        </div>
                        <div class="stat-icon bg-info bg-opacity-10">
                            <i class="fas fa-users text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card border-0 shadow-sm h-100">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small"><?= __('assoc_direct_referrals') ?></p>
                            <h4 class="mb-0"><?php echo number_format($stats['direct_referrals'] ?? 0); ?></h4>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10">
                            <i class="fas fa-user-plus text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-sitemap me-2"></i><?= __('assoc_network_overview') ?></h5>
                    <a href="<?php echo $base; ?>/associate/genealogy" class="btn btn-sm btn-primary"><?= __('assoc_view_full_tree') ?></a>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($network)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><?= __('assoc_level') ?></th>
                                        <th><?= __('assoc_members') ?></th>
                                        <th><?= __('assoc_active') ?></th>
                                        <th><?= __('assoc_commission') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($network as $level): ?>
                                        <tr>
                                            <td><?= __('assoc_level') . ' ' . $level['level'] ?></td>
                                            <td><?php echo $level['members']; ?></td>
                                            <td><?php echo $level['active']; ?></td>
                                            <td>₹<?php echo number_format($level['commission'] ?? 0); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-sitemap fa-3x text-muted mb-3"></i>
                            <p class="text-muted"><?= __('assoc_no_network_data') ?></p>
                            <a href="<?php echo $base; ?>/associate/register?ref=<?php echo $_SESSION['referral_code'] ?? ''; ?>" class="btn btn-primary">
                                <i class="fas fa-share-alt me-2"></i><?= __('assoc_invite_members') ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i><?= __('assoc_recent_earnings') ?></h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($commissions)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach (array_slice($commissions, 0, 5) as $commission): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <small class="text-muted"><?php echo $commission['type']; ?></small>
                                        <p class="mb-0 fw-medium">₹<?php echo number_format($commission['amount']); ?></p>
                                    </div>
                                    <small class="text-muted"><?php echo date('M d', strtotime($commission['date'])); ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-3"><?= __('assoc_no_recent_earnings') ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i><?= __('assoc_quick_actions') ?></h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="d-grid gap-2">
                        <a href="<?php echo $base; ?>/associate/register?ref=<?php echo $_SESSION['referral_code'] ?? ''; ?>" class="btn btn-outline-primary">
                            <i class="fas fa-user-plus me-2"></i><?= __('assoc_invite_member') ?>
                        </a>
                        <a href="<?php echo $base; ?>/associate/wallet/withdraw" class="btn btn-outline-success">
                            <i class="fas fa-wallet me-2"></i><?= __('assoc_request_payout') ?>
                        </a>
                        <a href="<?php echo $base; ?>/associate/profile" class="btn btn-outline-info">
                            <i class="fas fa-file me-2"></i><?= __('assoc_my_profile') ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-download me-2"></i><?= __('assoc_export_data') ?></h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="d-grid gap-2">
                        <a href="<?php echo $base; ?>/associate/export/my-earnings" class="btn btn-outline-dark btn-sm"><i class="fas fa-file-csv me-2"></i><?= __('assoc_my_earnings') ?></a>
                        <a href="<?php echo $base; ?>/associate/export/my-payouts" class="btn btn-outline-dark btn-sm"><i class="fas fa-file-csv me-2"></i><?= __('assoc_my_payouts') ?></a>
                        <a href="<?php echo $base; ?>/associate/export/downline" class="btn btn-outline-dark btn-sm"><i class="fas fa-file-csv me-2"></i><?= __('assoc_downline_report') ?></a>
                        <a href="<?php echo $base; ?>/associate/export/plot-sales" class="btn btn-outline-dark btn-sm"><i class="fas fa-file-csv me-2"></i><?= __('assoc_plot_sales') ?></a>
                        <a href="<?php echo $base; ?>/associate/export/active-team" class="btn btn-outline-dark btn-sm"><i class="fas fa-file-csv me-2"></i><?= __('assoc_active_team_stats') ?></a>
                        <a href="<?php echo $base; ?>/associate/export/new-directs" class="btn btn-outline-dark btn-sm"><i class="fas fa-file-csv me-2"></i><?= __('assoc_new_directs') ?></a>
                        <a href="<?php echo $base; ?>/associate/export/registry" class="btn btn-outline-dark btn-sm"><i class="fas fa-file-csv me-2"></i><?= __('assoc_registry_data') ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
