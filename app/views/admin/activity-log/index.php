<?php $page_title = 'Activity Log'; ?>
<div class="container-fluid py-4">
    <h2 class="mb-4"><i class="fas fa-history me-2"></i><?= __('admin_activity_log') ?></h2>
    <div class="row mb-4">
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3><?= number_format($stats['total']) ?></h3><small class="text-muted"><?= __('admin_total_events') ?></small></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3 class="text-primary"><?= $stats['today'] ?></h3><small class="text-muted"><?= __('admin_today') ?></small></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3 class="text-info"><?= $stats['unique_users'] ?></h3><small class="text-muted"><?= __('admin_active_users') ?></small></div></div></div>
    </div>
    <div class="row mb-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-fire me-2"></i><?= __('admin_top_actions') ?></h6></div>
                <div class="card-body p-0">
                    <?php if (empty($top_actions)): ?>
                        <p class="text-muted text-center py-3"><?= __('admin_no_data') ?></p>
                    <?php else: ?>
                        <table class="table table-sm mb-0">
                            <thead><tr><th><?= __('admin_action') ?></th><th><?= __('admin_count') ?></th></tr></thead>
                            <tbody>
                            <?php foreach ($top_actions as $a): ?>
                                <tr>
                                    <td><span class="badge bg-light text-dark"><?= htmlspecialchars($a['action'] ?? '') ?></span></td>
                                    <td><strong><?= $a['cnt'] ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-list me-2"></i><?= __('admin_recent_activity') ?></h6></div>
                <div class="card-body p-0">
                    <?php if (empty($logs)): ?>
                        <p class="text-muted text-center py-4"><?= __('admin_no_activity') ?></p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead><tr><th><?= __('admin_time') ?></th><th><?= __('admin_user') ?></th><th><?= __('admin_action') ?></th><th><?= __('admin_details') ?></th><th><?= __('admin_ip') ?></th></tr></thead>
                                <tbody>
                                <?php foreach ($logs as $l): ?>
                                    <tr>
                                        <td><small><?= date('d M H:i', strtotime($l['created_at'])) ?></small></td>
                                        <td><?= htmlspecialchars($l['user_name'] ?? 'System') ?></td>
                                        <td><span class="badge bg-<?= ($l['action'] ?? '')==='login'?'success':(($l['action'] ?? '')==='logout'?'secondary':'primary') ?>"><?= htmlspecialchars($l['action'] ?? '') ?></span></td>
                                        <td><small class="text-muted"><?= htmlspecialchars(substr($l['details'] ?? '', 0, 50)) ?></small></td>
                                        <td><small class="text-muted"><?= htmlspecialchars($l['ip_address'] ?? '') ?></small></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
