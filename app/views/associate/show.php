<?php $pageTitle = __('assoc_show_title'); ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="fas fa-home me-1"></i><?= __('assoc_home') ?></a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/associate"><?= __('assoc_users') ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($associate['name'] ?? __('assoc_show_title')) ?></li>
        </ol>
    </nav>
    <?php if (!empty($associate)): ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-user me-2"></i><?= htmlspecialchars($associate['name']) ?></h4>
        <div>
            <a href="<?= BASE_URL ?>/associate/edit/<?= $associate['id'] ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-edit me-1"></i><?= __('assoc_show_edit') ?></a>
            <a href="<?= BASE_URL ?>/associate/metrics/<?= $associate['id'] ?>" class="btn btn-outline-info btn-sm"><i class="fas fa-chart-bar me-1"></i><?= __('assoc_show_metrics') ?></a>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body text-center py-4">
                    <div class="display-4 text-primary mb-2"><i class="fas fa-user-circle"></i></div>
                    <h5><?= htmlspecialchars($associate['name']) ?></h5>
                    <p class="text-muted mb-1"><i class="fas fa-envelope me-1"></i><?= htmlspecialchars($associate['email'] ?? '') ?></p>
                    <p class="text-muted mb-1"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($associate['phone'] ?? '') ?></p>
                    <p class="mb-0"><span class="badge bg-<?= ($associate['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($associate['status'] ?? 'active') ?></span></p>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0"><h6 class="mb-0"><i class="fas fa-info-circle me-2"></i><?= __('assoc_show_details') ?></h6></div>
                <div class="card-body aps-cp-card-body">
                    <div class="mb-2"><small class="text-muted"><?= __('assoc_show_city') ?></small><br><?= htmlspecialchars($associate['city'] ?? __('assoc_na')) ?></div>
                    <div class="mb-2"><small class="text-muted"><?= __('assoc_show_commission_rate') ?></small><br><?= $associate['commission_rate'] ?? '5.00' ?>%</div>
                    <div class="mb-0"><small class="text-muted"><?= __('assoc_show_joined') ?></small><br><?= htmlspecialchars($associate['created_at'] ?? __('assoc_na')) ?></div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm text-center p-3">
                        <h4 class="text-primary mb-0"><?= $associate['property_count'] ?? 0 ?></h4>
                        <small class="text-muted"><?= __('assoc_show_properties') ?></small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm text-center p-3">
                        <h4 class="text-success mb-0">₹<?= number_format($associate['total_sales'] ?? 0) ?></h4>
                        <small class="text-muted"><?= __('assoc_show_total_sales') ?></small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm text-center p-3">
                        <h4 class="text-warning mb-0">₹<?= number_format($associate['commission_earned'] ?? 0) ?></h4>
                        <small class="text-muted"><?= __('assoc_show_commission_earned') ?></small>
                    </div>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0"><h6 class="mb-0"><i class="fas fa-history me-2"></i><?= __('assoc_show_recent_activity') ?></h6></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($recentActivity)): ?>
                        <?php foreach ($recentActivity as $act): ?>
                        <div class="d-flex align-items-center mb-2 pb-2 border-bottom">
                            <div class="me-3"><i class="far fa-circle text-primary"></i></div>
                            <div><small><?= htmlspecialchars($act['description'] ?? '') ?></small><br><small class="text-muted"><?= htmlspecialchars($act['created_at'] ?? '') ?></small></div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-3"><i class="fas fa-inbox fa-2x text-muted mb-2"></i><p class="text-muted mb-0"><?= __('assoc_show_no_activity') ?></p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
            <h5 class="text-muted"><?= __('assoc_show_not_found') ?></h5>
            <a href="<?= BASE_URL ?>/associate" class="btn btn-primary mt-2"><?= __('assoc_show_back') ?></a>
        </div>
    </div>
    <?php endif; ?>
</div>
