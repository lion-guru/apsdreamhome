<?php
$page_title = $page_title ?? 'New Registry';
ob_start();
$eligible_bookings = $eligible_bookings ?? [];
$stamp_duty = $stamp_duty ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-landmark me-2"></i><?= htmlspecialchars($page_title) ?></h4>
        <span class="text-muted">Schedule property registration at sub-registrar office</span>
    </div>
    <a href="<?= BASE_URL ?>/admin/noc-registry/registries" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to Registries</a>
</div>

<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">Registry Details</h6>
            </div>
            <div class="card-body">
                <?php if (empty($eligible_bookings)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>No eligible bookings found.</strong> NOC must be approved before creating a registry.
                    </div>
                <?php else: ?>
                    <form method="POST" action="<?= BASE_URL ?>/admin/noc-registry/registries/store">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Booking <span class="text-danger">*</span></label>
                                <select name="booking_id" class="form-select" required id="bookingSelect">
                                    <option value="">— Select Booking —</option>
                                    <?php foreach ($eligible_bookings as $b): ?>
                                        <option value="<?= $b['id'] ?>" data-price="<?= $b['total_price'] ?? 0 ?>">
                                            <?= htmlspecialchars($b['booking_number']) ?> — <?= htmlspecialchars($b['customer_name']) ?>
                                            (<?= htmlspecialchars($b['plot_no']) ?>, <?= htmlspecialchars($b['colony_name']) ?>)
                                            — ₹<?= number_format($b['total_price'] ?? 0, 0) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Sub-Registrar Office <span class="text-danger">*</span></label>
                                <select name="sub_registrar_office" class="form-select" required>
                                    <option value="SRO Gorakhpur">SRO Gorakhpur</option>
                                    <option value="SRO Lucknow">SRO Lucknow</option>
                                    <option value="SRO Varanasi">SRO Varanasi</option>
                                    <option value="SRO Kushinagar">SRO Kushinagar</option>
                                    <option value="SRO Deoria">SRO Deoria</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Appointment Date</label>
                                <input type="date" name="appointment_date" class="form-control" min="<?= date('Y-m-d') ?>">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Notes</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Additional instructions..."></textarea>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Create Registry</button>
                            <a href="<?= BASE_URL ?>/admin/noc-registry/registries" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Stamp Duty Calculator -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>Stamp Duty Calculator</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Property Value (₹)</label>
                    <input type="number" class="form-control" id="calcValue" placeholder="e.g. 2500000" value="2500000">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">State</label>
                    <select class="form-select" id="calcState">
                        <option value="Uttar Pradesh" selected>Uttar Pradesh (4%)</option>
                        <option value="Bihar">Bihar (6%)</option>
                        <option value="Rajasthan">Rajasthan (5%)</option>
                        <option value="Maharashtra">Maharashtra (6%)</option>
                    </select>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-2">
                    <span class="small">Stamp Duty:</span>
                    <span class="fw-bold" id="calcStamp">₹<?= number_format($stamp_duty['stamp_duty'] ?? 0, 0) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="small">Registration Fee:</span>
                    <span class="fw-bold" id="calcReg">₹<?= number_format($stamp_duty['registration_fee'] ?? 0, 0) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="small">Other Charges:</span>
                    <span class="fw-bold" id="calcOther">₹1,000</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="fw-bold">Total Cost:</span>
                    <span class="fw-bold text-primary fs-5" id="calcTotal">₹<?= number_format($stamp_duty['total'] ?? 0, 0) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var rates = { 'Uttar Pradesh': 0.04, 'Bihar': 0.06, 'Rajasthan': 0.05, 'Maharashtra': 0.06 };
    var caps = { 'Uttar Pradesh': 30000, 'Bihar': 50000, 'Rajasthan': 50000, 'Maharashtra': 30000 };

    function recalc() {
        var val = parseFloat(document.getElementById('calcValue').value) || 0;
        var state = document.getElementById('calcState').value;
        var rate = rates[state] || 0.04;
        var cap = caps[state] || 30000;
        var stamp = Math.round(val * rate);
        var regFee = Math.min(Math.round(val * 0.01), cap);
        var total = stamp + regFee + 1000;
        document.getElementById('calcStamp').textContent = '₹' + stamp.toLocaleString('en-IN');
        document.getElementById('calcReg').textContent = '₹' + regFee.toLocaleString('en-IN');
        document.getElementById('calcTotal').textContent = '₹' + total.toLocaleString('en-IN');
    }

    document.getElementById('calcValue').addEventListener('input', recalc);
    document.getElementById('calcState').addEventListener('change', recalc);
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/unified.php';
