<?php
$page_title = $page_title ?? 'Request Registry';
$page_heading = $page_heading ?? 'Request Registry (Requires Approved NOC)';
$bookings = $bookings ?? [];
$users = $users ?? [];
ob_start();
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-landmark me-2"></i>Request Registry</h2>
            <p class="text-muted mb-0">Register plot ownership at sub-registrar office</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/noc-registry/registries" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>

    <div class="alert alert-warning mb-4">
        <i class="fas fa-exclamation-triangle me-2"></i><strong>Prerequisite:</strong> The booking must have an <strong>approved NOC</strong> before you can request registry. If NOC is not yet approved, <a href="<?= BASE_URL ?>/admin/noc-registry/nocs/create">request one first</a>.
    </div>

    <div class="row">
        <div class="col-md-8">
            <form method="POST" action="<?= BASE_URL ?>/admin/noc-registry/registries/store">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? $_SESSION['csrf_token'] ?? '' ?>">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-link me-2"></i>Booking (with approved NOC)</h5></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Booking *</label>
                                <select class="form-select" name="booking_id" id="bookingSelect" required>
                                    <option value="">Select booking with approved NOC...</option>
                                    <?php foreach ($bookings as $b): ?>
                                        <option value="<?= $b['id'] ?>" data-plot="<?= $b['plot_id'] ?? 0 ?>" data-user="<?= $b['customer_id'] ?? 0 ?>"><?= htmlspecialchars($b['booking_number'] ?? '#'.$b['id']) ?> — <?= htmlspecialchars($b['customer_name'] ?? '') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Sub-Registrar Office *</label>
                                <input type="text" class="form-control" name="sub_registrar_office" required placeholder="e.g., SRO Gorakhpur">
                            </div>
                            <input type="hidden" name="plot_id" id="plotId">
                            <input type="hidden" name="user_id" id="userId">
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Cost Breakdown</h5></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Stamp Duty (₹) *</label>
                                <input type="number" class="form-control" name="stamp_duty_amount" required min="0" step="0.01" placeholder="0.00">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Registration Fee (₹) *</label>
                                <input type="number" class="form-control" name="registration_fee" required min="0" step="0.01" placeholder="0.00">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Other Charges (₹)</label>
                                <input type="number" class="form-control" name="other_charges" min="0" step="0.01" placeholder="0.00">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Total Registry Cost (₹) *</label>
                                <input type="number" class="form-control" name="total_registry_cost" required min="0" step="0.01" placeholder="Auto-calculate or enter">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Notes</h5></div>
                    <div class="card-body">
                        <textarea class="form-control" name="notes" rows="3" placeholder="Any additional notes..."></textarea>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-landmark me-2"></i>Submit Registry Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('bookingSelect').addEventListener('change', function() {
    var opt = this.options[this.selectedIndex];
    if (opt.value) {
        document.getElementById('plotId').value = opt.dataset.plot || '';
        document.getElementById('userId').value = opt.dataset.user || '';
    }
});
</script>
<?php
$content = ob_get_clean();
include APP_PATH . '/views/admin/layouts/unified.php';
