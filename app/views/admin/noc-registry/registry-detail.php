<?php
$page_title = $page_title ?? __('admin_registry_detail');
ob_start();
$registry = $registry ?? [];
$stamp_duty_calc = $stamp_duty_calc ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-landmark me-2"></i><?= htmlspecialchars($page_title) ?></h4>
        <span class="text-muted">Registry #<?= $registry['id'] ?? 0 ?> — <?= htmlspecialchars($registry['booking_number'] ?? '') ?></span>
    </div>
    <a href="<?= BASE_URL ?>/admin/noc-registry/registries" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i><?= __('admin_back_to_registries') ?></a>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_success'] ?? ''); unset($_SESSION['flash_success']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_error'] ?? ''); unset($_SESSION['flash_error']); ?></div>
<?php endif; ?>

<?php if (empty($registry)): ?>
    <div class="alert alert-danger"><?= __('admin_registry_not_found') ?></div>
<?php else: ?>
    <div class="row g-4">
        <!-- Registry Info -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><?= __('admin_registry_information') ?></h6>
                        <?php
                        $colors = ['pending'=>'secondary','appointment_scheduled'=>'info','documents_submitted'=>'warning','in_progress'=>'primary','completed'=>'success','rejected'=>'danger','cancelled'=>'dark'];
                        $color = $colors[$registry['status']] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?= $color ?> fs-6"><?= ucfirst(str_replace('_', ' ', $registry['status'])) ?></span>
                    </div>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-muted small"><?= __('admin_booking_label') ?></div>
                            <div class="fw-semibold"><?= htmlspecialchars($registry['booking_number']) ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small"><?= __('admin_customer_label') ?></div>
                            <div class="fw-semibold"><?= htmlspecialchars($registry['customer_name']) ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small"><?= __('admin_plot_label') ?></div>
                            <div class="fw-semibold"><?= htmlspecialchars($registry['plot_no']) ?>, <?= htmlspecialchars($registry['colony_name']) ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small"><?= __('admin_sub_registrar_office') ?></div>
                            <div class="fw-semibold"><?= htmlspecialchars($registry['sub_registrar_office']) ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small"><?= __('admin_registration_no') ?></div>
                            <div class="fw-semibold"><?= htmlspecialchars($registry['registration_no'] ?? '—') ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small"><?= __('admin_appointment_date') ?></div>
                            <div class="fw-semibold"><?= $registry['appointment_date'] ? date('d M Y', strtotime($registry['appointment_date'])) : '—' ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small"><?= __('admin_created_at') ?></div>
                            <div class="fw-semibold"><?= date('d M Y h:i A', strtotime($registry['created_at'])) ?></div>
                        </div>
                        <?php if ($registry['notes']): ?>
                        <div class="col-12">
                            <div class="text-muted small"><?= __('admin_notes_label') ?></div>
                            <div><?= nl2br(htmlspecialchars($registry['notes'])) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Cost Breakdown -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0"><i class="fas fa-receipt me-2"></i><?= __('admin_cost_breakdown') ?></h6>
                </div>
                <div class="card-body aps-cp-card-body">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td><?= __('admin_plot_value') ?></td>
                                <td class="text-end fw-bold">₹<?= number_format($registry['total_plot_value'] ?? 0, 0) ?></td>
                            </tr>
                            <tr>
                                <td><?= __('admin_stamp_duty_up') ?></td>
                                <td class="text-end">₹<?= number_format($registry['stamp_duty_amount'], 0) ?></td>
                            </tr>
                            <tr>
                                <td><?= __('admin_registration_fee_capped') ?></td>
                                <td class="text-end">₹<?= number_format($registry['registration_fee'], 0) ?></td>
                            </tr>
                            <tr>
                                <td>Other Charges</td>
                                <td class="text-end">₹<?= number_format($registry['other_charges'], 0) ?></td>
                            </tr>
                            <tr class="table-primary">
                                <td class="fw-bold"><?= __('admin_total_registry_cost') ?></td>
                                <td class="text-end fw-bold fs-5">₹<?= number_format($registry['total_registry_cost'], 0) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Status Update -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0"><i class="fas fa-sync-alt me-2"></i><?= __('admin_update_status') ?></h6>
                </div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/noc-registry/registries/update-status">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <input type="hidden" name="registry_id" value="<?= $registry['id'] ?>">

                        <div class="mb-3">
                            <label class="form-label small fw-semibold"><?= __('admin_new_status') ?></label>
                            <select name="status" class="form-select" required>
                                <option value="">— Select —</option>
                                <option value="appointment_scheduled" <?= $registry['status'] === 'appointment_scheduled' ? 'selected' : '' ?>><?= __('admin_appointment_scheduled') ?></option>
                                <option value="documents_submitted" <?= $registry['status'] === 'documents_submitted' ? 'selected' : '' ?>><?= __('admin_documents_submitted') ?></option>
                                <option value="in_progress" <?= $registry['status'] === 'in_progress' ? 'selected' : '' ?>><?= __('admin_in_progress') ?></option>
                                <option value="completed" <?= $registry['status'] === 'completed' ? 'selected' : '' ?>><?= __('admin_completed') ?></option>
                                <option value="rejected" <?= $registry['status'] === 'rejected' ? 'selected' : '' ?>><?= __('admin_rejected') ?></option>
                                <option value="cancelled" <?= $registry['status'] === 'cancelled' ? 'selected' : '' ?>><?= __('admin_cancelled') ?></option>
                            </select>
                        </div>

                        <div class="mb-3" id="regNoGroup" style="display:none;">
                            <label class="form-label small fw-semibold"><?= __('admin_registration_number') ?></label>
                            <input type="text" name="registration_no" class="form-control" placeholder="e.g. REG-2026-001234">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold"><?= __('admin_notes_label') ?></label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="<?= __('admin_status_update_notes_placeholder') ?>"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i><?= __('admin_update_status') ?></button>
                    </form>
                </div>
            </div>

            <!-- Stamp Duty Calculator -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0"><i class="fas fa-calculator me-2"></i><?= __('admin_stamp_duty') ?> (UP)</h6>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small"><?= __('admin_stamp_duty_colon') ?></span>
                        <span class="fw-bold">₹<?= number_format($stamp_duty_calc['stamp_duty'] ?? 0, 0) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small"><?= __('admin_reg_fee_max') ?></span>
                        <span class="fw-bold">₹<?= number_format($stamp_duty_calc['registration_fee'] ?? 0, 0) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small">Other Charges:</span>
                        <span class="fw-bold">₹1,000</span>
                    </div>
                    <hr class="my-1">
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold"><?= __('admin_total_label') ?></span>
                        <span class="fw-bold text-primary">₹<?= number_format($stamp_duty_calc['total'] ?? 0, 0) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var sel = document.querySelector('[name="status"]');
    var grp = document.getElementById('regNoGroup');
    if (sel && grp) {
        sel.addEventListener('change', function() {
            grp.style.display = this.value === 'completed' ? 'block' : 'none';
        });
        if (sel.value === 'completed') grp.style.display = 'block';
    }
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/unified.php';
