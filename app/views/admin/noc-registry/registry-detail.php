<?php
$page_title = $page_title ?? 'Registry Details';
$page_heading = $page_heading ?? 'Registry Request Details';
$registry = $registry ?? [];
ob_start();
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-landmark me-2"></i>Registry #<?= $registry['id'] ?? 0 ?></h2>
            <p class="text-muted mb-0"><?= htmlspecialchars($registry['booking_number'] ?? '') ?> — <?= htmlspecialchars($registry['customer_name'] ?? '') ?></p>
        </div>
        <a href="<?= BASE_URL ?>/admin/noc-registry/registries" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Registry Information</h5></div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Status</div>
                        <div class="col-sm-8">
                            <?php $colors = ['pending'=>'warning','appointment_scheduled'=>'info','documents_submitted'=>'primary','in_progress'=>'primary','completed'=>'success','rejected'=>'danger','cancelled'=>'secondary']; ?>
                            <span class="badge bg-<?= $colors[$registry['status']] ?? 'secondary' ?> px-3 py-2 fs-6"><?= ucfirst(str_replace('_',' ',$registry['status'] ?? '')) ?></span>
                        </div>
                    </div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Registration No</div><div class="col-sm-8"><strong><?= htmlspecialchars($registry['registration_no'] ?? 'Not yet assigned') ?></strong></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Sub-Registrar Office</div><div class="col-sm-8"><?= htmlspecialchars($registry['sub_registrar_office'] ?? '') ?></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Plot</div><div class="col-sm-8"><?= htmlspecialchars(($registry['block'] ?? '') . '-' . ($registry['plot_number'] ?? '')) ?></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Customer</div><div class="col-sm-8"><strong><?= htmlspecialchars($registry['customer_name'] ?? '') ?></strong></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Registration Date</div><div class="col-sm-8"><?= $registry['registration_date'] ? date('d M Y', strtotime($registry['registration_date'])) : 'Not yet' ?></div></div>
                    <hr>
                    <div class="row mb-2"><div class="col-sm-4 text-muted">Stamp Duty</div><div class="col-sm-8">₹<?= number_format($registry['stamp_duty_amount'] ?? 0) ?></div></div>
                    <div class="row mb-2"><div class="col-sm-4 text-muted">Registration Fee</div><div class="col-sm-8">₹<?= number_format($registry['registration_fee'] ?? 0) ?></div></div>
                    <div class="row mb-2"><div class="col-sm-4 text-muted">Other Charges</div><div class="col-sm-8">₹<?= number_format($registry['other_charges'] ?? 0) ?></div></div>
                    <div class="row mb-2"><div class="col-sm-4 text-muted"><strong>Total Cost</strong></div><div class="col-sm-8"><strong class="text-primary">₹<?= number_format($registry['total_registry_cost'] ?? 0) ?></strong></div></div>
                    <?php if (!empty($registry['rejection_reason'])): ?>
                    <hr>
                    <div class="row"><div class="col-sm-4 text-muted">Rejection Reason</div><div class="col-sm-8 text-danger"><?= htmlspecialchars($registry['rejection_reason']) ?></div></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Update Status</h5></div>
                <div class="card-body">
                    <?php if (!in_array($registry['status'] ?? '', ['completed','cancelled'])): ?>
                    <form method="POST" action="<?= BASE_URL ?>/admin/noc-registry/registries/update-status">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? $_SESSION['csrf_token'] ?? '' ?>">
                        <input type="hidden" name="id" value="<?= $registry['id'] ?? 0 ?>">
                        <div class="mb-3">
                            <label class="form-label">New Status</label>
                            <select class="form-select" name="status" required>
                                <?php foreach (['pending'=>'Pending','appointment_scheduled'=>'Appointment Scheduled','documents_submitted'=>'Documents Submitted','in_progress'=>'In Progress','completed'=>'Completed','rejected'=>'Rejected','cancelled'=>'Cancelled'] as $v=>$l): ?>
                                    <option value="<?= $v ?>" <?= ($registry['status'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Registration No (if completing)</label>
                            <input type="text" class="form-control" name="registration_no" placeholder="Optional">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason (if rejecting/cancelling)</label>
                            <textarea class="form-control" name="reason" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-2"></i>Update Status</button>
                    </form>
                    <?php else: ?>
                        <p class="text-muted mb-0">Registry is <?= $registry['status'] ?>.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include APP_PATH . '/views/admin/layouts/unified.php';
