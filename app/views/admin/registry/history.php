<?php
$page_title = 'Registry Activity Log';
$active_page = 'registry';
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-history"></i> Registry Activity Log</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= BASE_URL ?>/admin/registry/show/<?= $booking_id ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Registry</a>
        <a href="<?= BASE_URL ?>/admin/registry" class="btn btn-outline-secondary ms-2"><i class="fas fa-list"></i> All Registries</a>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error ?? '') ?></div>
<?php endif; ?>

<?php if ($booking): ?>
    <div class="alert alert-info">
        <strong>Booking:</strong> #<?= htmlspecialchars($booking['booking_number'] ?? '') ?>
        &nbsp;|&nbsp; <strong>Customer:</strong> <?= htmlspecialchars($booking['customer_name'] ?? '') ?>
    </div>
<?php endif; ?>

<div class="card aps-cp-card">
    <div class="card-header aps-cp-card-header">
        <h5 class="card-title mb-0"><i class="fas fa-list"></i> Activity Timeline</h5>
    </div>
    <div class="card-body aps-cp-card-body">
        <?php if (empty($activities)): ?>
            <p class="text-muted text-center py-4">No activity recorded yet.</p>
        <?php else: ?>
            <div class="timeline style-37179">
                <?php foreach ($activities as $a): ?>
                    <div class="style-36833">
                        <div class="style-79878"></div>
                        <small class="text-muted"><?= date('d M Y h:i A', strtotime($a['created_at'])) ?></small>
                        <br>
                        <strong><?= ucfirst(str_replace('_', ' ', $a['action'])) ?></strong>
                        <?php if (!empty($a['details'])): ?>
                            <p class="mb-0 mt-1"><?= htmlspecialchars($a['details'] ?? '') ?></p>
                        <?php endif; ?>
                        <?php if (!empty($a['performed_by'])): ?>
                            <small class="text-muted">- Admin #<?= htmlspecialchars($a['performed_by'] ?? '') ?></small>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
