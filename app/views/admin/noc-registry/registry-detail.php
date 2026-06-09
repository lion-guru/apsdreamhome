<?php
$page_title = $page_title ?? 'Registry Detail';
ob_start();
$registry = $registry ?? [];
$stamp_duty_calc = $stamp_duty_calc ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-landmark me-2"></i><?= htmlspecialchars($page_title) ?></h4>
        <span class="text-muted">Registry #<?= $registry['id'] ?? 0 ?> — <?= htmlspecialchars($registry['booking_number'] ?? '') ?></span>
    </div>
    <a href="<?= BASE_URL ?>/admin/noc-registry/registries" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to Registries</a>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
<?php endif; ?>

<?php if (empty($registry)): ?>
    <div class="alert alert-danger">Registry not found.</div>
<?php else: ?>
    <div class="row g-4">
        <!-- Registry Info -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Registry Information</h6>
                        <?php
                        $colors = ['pending'=>'secondary','appointment_scheduled'=>'info','documents_submitted'=>'warning','in_progress'=>'primary','completed'=>'success','rejected'=>'danger','cancelled'=>'dark'];
                        $color = $colors[$registry['status']] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?= $color ?> fs-6"><?= ucfirst(str_replace('_', ' ', $registry['status'])) ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-muted small">Booking Number</div>
                            <div class="fw-semibold"><?= htmlspecialchars($registry['booking_number']) ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Customer</div>
                            <div class="fw-semibold"><?= htmlspecialchars($registry['customer_name']) ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Plot</div>
                            <div class="fw-semibold"><?= htmlspecialchars($registry['plot_no']) ?>, <?= htmlspecialchars($registry['colony_name']) ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Sub-Registrar Office</div>
                            <div class="fw-semibold"><?= htmlspecialchars($registry['sub_registrar_office']) ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Registration No.</div>
                            <div class="fw-semibold"><?= htmlspecialchars($registry['registration_no'] ?? '—') ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Appointment Date</div>
                            <div class="fw-semibold"><?= $registry['appointment_date'] ? date('d M Y', strtotime($registry['appointment_date'])) : '—' ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Created At</div>
                            <div class="fw-semibold"><?= date('d M Y h:i A', strtotime($registry['created_at'])) ?></div>
                        </div>
                        <?php if ($registry['notes']): ?>
                        <div class="col-12">
                            <div class="text-muted small">Notes</div>
                            <div><?= nl2br(htmlspecialchars($registry['notes'])) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Cost Breakdown -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0"><i class="fas fa-receipt me-2"></i>Cost Breakdown</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td>Plot Value</td>
                                <td class="text-end fw-bold">₹<?= number_format($registry['total_plot_value'] ?? 0, 0) ?></td>
                            </tr>
                            <tr>
                                <td>Stamp Duty (4% — UP)</td>
                                <td class="text-end">₹<?= number_format($registry['stamp_duty_amount'], 0) ?></td>
                            </tr>
                            <tr>
                                <td>Registration Fee (1%, capped)</td>
                                <td class="text-end">₹<?= number_format($registry['registration_fee'], 0) ?></td>
                            </tr>
                            <tr>
                                <td>Other Charges</td>
                                <td class="text-end">₹<?= number_format($registry['other_charges'], 0) ?></td>
                            </tr>
                            <tr class="table-primary">
                                <td class="fw-bold">Total Registry Cost</td>
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
                    <h6 class="mb-0"><i class="fas fa-sync-alt me-2"></i>Update Status</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/noc-registry/registries/update-status">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <input type="hidden" name="registry_id" value="<?= $registry['id'] ?>">

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">New Status</label>
                            <select name="status" class="form-select" required>
                                <option value="">— Select —</option>
                                <option value="appointment_scheduled" <?= $registry['status'] === 'appointment_scheduled' ? 'selected' : '' ?>>Appointment Scheduled</option>
                                <option value="documents_submitted" <?= $registry['status'] === 'documents_submitted' ? 'selected' : '' ?>>Documents Submitted</option>
                                <option value="in_progress" <?= $registry['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="completed" <?= $registry['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="rejected" <?= $registry['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                <option value="cancelled" <?= $registry['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>

                        <div class="mb-3" id="regNoGroup" style="display:none;">
                            <label class="form-label small fw-semibold">Registration Number</label>
                            <input type="text" name="registration_no" class="form-control" placeholder="e.g. REG-2026-001234">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Status update notes..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Update Status</button>
                    </form>
                </div>
            </div>

            <!-- Stamp Duty Calculator -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>Stamp Duty (UP)</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small">Stamp Duty (4%):</span>
                        <span class="fw-bold">₹<?= number_format($stamp_duty_calc['stamp_duty'] ?? 0, 0) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small">Reg. Fee (1%, max ₹30K):</span>
                        <span class="fw-bold">₹<?= number_format($stamp_duty_calc['registration_fee'] ?? 0, 0) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small">Other Charges:</span>
                        <span class="fw-bold">₹1,000</span>
                    </div>
                    <hr class="my-1">
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">Total:</span>
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
