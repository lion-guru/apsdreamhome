<?php $page_title = 'CRM Dashboard'; ?>
<div class="container-fluid py-4">
    <h2 class="mb-4"><i class="fas fa-tachometer-alt me-2"></i>CRM Dashboard</h2>
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-2x text-primary mb-2"></i>
                    <h3 class="mb-0"><?= number_format($stats['total_customers']) ?></h3>
                    <small class="text-muted"><?= __('admin_total_customers') ?></small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-bullseye fa-2x text-warning mb-2"></i>
                    <h3 class="mb-0"><?= number_format($stats['active_leads']) ?></h3>
                    <small class="text-muted"><?= __('admin_active_leads') ?></small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-ticket-alt fa-2x text-info mb-2"></i>
                    <h3 class="mb-0"><?= number_format($stats['open_tickets']) ?></h3>
                    <small class="text-muted"><?= __('admin_open_tickets') ?></small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-question-circle fa-2x text-secondary mb-2"></i>
                    <h3 class="mb-0"><?= number_format($stats['total_inquiries']) ?></h3>
                    <small class="text-muted"><?= __('admin_total_inquiries') ?></small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <h3 class="mb-0"><?= number_format($stats['converted_this_month']) ?></h3>
                    <small class="text-muted"><?= __('admin_converted_month') ?></small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x text-danger mb-2"></i>
                    <h3 class="mb-0"><?= number_format($stats['pending_followups']) ?></h3>
                    <small class="text-muted"><?= __('admin_pending_followups') ?></small>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-ticket-alt me-2"></i><?= __('admin_recent_support_tickets') ?></h6>
                    <a href="<?= BASE_URL ?>/admin/crm/support" class="btn btn-sm btn-outline-primary"><?= __('admin_view_all') ?></a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recent_tickets)): ?>
                        <p class="text-muted text-center py-4"><?= __('admin_no_tickets') ?></p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead><tr><th>#</th><th><?= __('admin_subject') ?></th><th><?= __('admin_customer') ?></th><th><?= __('admin_status') ?></th><th><?= __('admin_date') ?></th></tr></thead>
                                <tbody>
                                <?php foreach ($recent_tickets as $t): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($t['ticket_number'] ?? $t['id']) ?></td>
                                        <td><?= htmlspecialchars(substr($t['subject'], 0, 40)) ?></td>
                                        <td><?= htmlspecialchars($t['user_name'] ?? 'Unknown') ?></td>
                                        <td><span class="badge bg-<?= $t['status']==='open'?'warning':($t['status']==='resolved'?'success':'info') ?>"><?= ucfirst($t['status']) ?></span></td>
                                        <td><?= date('d M', strtotime($t['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-bullseye me-2"></i><?= __('admin_recent_leads') ?></h6>
                    <a href="<?= BASE_URL ?>/admin/leads" class="btn btn-sm btn-outline-primary"><?= __('admin_view_all') ?></a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recent_leads)): ?>
                        <p class="text-muted text-center py-4"><?= __('admin_no_leads') ?></p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead><tr><th><?= __('admin_name') ?></th><th><?= __('admin_phone') ?></th><th><?= __('admin_source') ?></th><th><?= __('admin_status') ?></th><th><?= __('admin_assigned') ?></th></tr></thead>
                                <tbody>
                                <?php foreach ($recent_leads as $l): ?>
                                    <tr>
                                        <td><a href="<?= BASE_URL ?>/admin/leads/show/<?= $l['id'] ?>"><?= htmlspecialchars($l['name']) ?></a></td>
                                        <td><?= htmlspecialchars($l['phone'] ?? '') ?></td>
                                        <td><span class="badge bg-light text-dark"><?= htmlspecialchars($l['source'] ?? 'N/A') ?></span></td>
                                        <td><span class="badge bg-<?= $l['status']==='new'?'primary':($l['status']==='converted'?'success':($l['status']==='closed'?'secondary':'warning')) ?>"><?= ucfirst($l['status']) ?></span></td>
                                        <td><?= htmlspecialchars($l['assignee_name'] ?? 'Unassigned') ?></td>
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
