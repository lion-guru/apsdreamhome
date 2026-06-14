<?php
$page_title = $page_title ?? 'Visit Details';
$page_heading = $page_heading ?? 'Visit Details';
$visit = $visit ?? [];
ob_start();
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-calendar-check me-2"></i>Visit Details</h2>
            <p class="text-muted mb-0">Visit #<?= $visit['id'] ?? 0 ?></p>
        </div>
        <div class="btn-group">
            <a href="<?= BASE_URL ?>/admin/visits/edit/<?= $visit['id'] ?? 0 ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit me-1"></i>Edit</a>
            <a href="<?= BASE_URL ?>/admin/visits" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
        </div>
    </div>
    <?php if (empty($visit)): ?>
        <div class="text-center py-5 text-muted"><i class="fas fa-calendar-check fa-4x d-block mb-3"></i><h5>Visit not found</h5></div>
    <?php else: ?>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Visit Information</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row mb-3"><div class="col-sm-5 text-muted">Visit ID</div><div class="col-sm-7"><strong>#<?= $visit['id'] ?></strong></div></div>
                    <div class="row mb-3"><div class="col-sm-5 text-muted">Customer</div><div class="col-sm-7"><?= $visit['customer_name'] ?? '-' ?></div></div>
                    <div class="row mb-3"><div class="col-sm-5 text-muted">Property/Plot</div><div class="col-sm-7"><?= $visit['property_title'] ?? $visit['plot_number'] ?? '-' ?></div></div>
                    <div class="row mb-3"><div class="col-sm-5 text-muted">Scheduled Date</div><div class="col-sm-7"><?= date('d M Y H:i', strtotime($visit['scheduled_at'] ?? 'now')) ?></div></div>
                    <div class="row mb-3"><div class="col-sm-5 text-muted">Assigned To</div><div class="col-sm-7"><?= $visit['assigned_to_name'] ?? 'Unassigned' ?></div></div>
                    <div class="row mb-3"><div class="col-sm-5 text-muted">Status</div><div class="col-sm-7"><span class="badge bg-<?= ($visit['status'] ?? 'scheduled') === 'completed' ? 'success' : (($visit['status'] ?? 'scheduled') === 'cancelled' ? 'danger' : 'primary') ?>-subtle text-<?= ($visit['status'] ?? 'scheduled') === 'completed' ? 'success' : (($visit['status'] ?? 'scheduled') === 'cancelled' ? 'danger' : 'primary') ?> rounded-pill px-3"><?= ucfirst($visit['status'] ?? 'Scheduled') ?></span></div></div>
                    <div class="row"><div class="col-sm-5 text-muted">Notes</div><div class="col-sm-7"><?= nl2br($visit['notes'] ?? '-') ?></div></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-phone me-2"></i>Contact Info</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="mb-3"><small class="text-muted d-block">Customer Phone</small><strong><?= $visit['customer_phone'] ?? '-' ?></strong></div>
                    <div class="mb-3"><small class="text-muted d-block">Customer Email</small><?= $visit['customer_email'] ?? '-' ?></div>
                    <div class="mb-3"><small class="text-muted d-block">Visit Purpose</small><?= $visit['purpose'] ?? 'Site Visit' ?></div>
                    <div><small class="text-muted d-block">Created At</small><?= date('d M Y H:i', strtotime($visit['created_at'] ?? 'now')) ?></div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
include APP_PATH . '/views/admin/layouts/admin.php';

