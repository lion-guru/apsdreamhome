<?php
$page_title = $page_title ?? 'Request NOC';
$page_heading = $page_heading ?? 'Request NOC for Booking';
$bookings = $bookings ?? [];
$users = $users ?? [];
ob_start();
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-file-contract me-2"></i>Request NOC</h2>
            <p class="text-muted mb-0">Submit a new No Objection Certificate request</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/noc-registry/nocs" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <form method="POST" action="<?= BASE_URL ?>/admin/noc-registry/nocs/store">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? $_SESSION['csrf_token'] ?? '' ?>">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-link me-2"></i>Booking Details</h5></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Booking *</label>
                                <select class="form-select" name="booking_id" id="bookingSelect" required>
                                    <option value="">Select booking...</option>
                                    <?php foreach ($bookings as $b): ?>
                                        <option value="<?= $b['id'] ?>" data-plot="<?= $b['plot_id'] ?? 0 ?>" data-user="<?= $b['customer_id'] ?? 0 ?>"><?= htmlspecialchars($b['booking_number'] ?? '#'.$b['id']) ?> — <?= htmlspecialchars($b['customer_name'] ?? '') ?> (₹<?= number_format($b['total_plot_value'] ?? 0) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Plot</label>
                                <input type="text" class="form-control" id="plotInfo" readonly placeholder="Auto-filled from booking">
                                <input type="hidden" name="plot_id" id="plotId">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Customer</label>
                                <input type="text" class="form-control" id="customerInfo" readonly placeholder="Auto-filled from booking">
                                <input type="hidden" name="user_id" id="userId">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-edit me-2"></i>NOC Details</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Purpose *</label>
                            <input type="text" class="form-control" name="purpose" required placeholder="e.g., Registry, Bank Loan, Transfer">
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="Any additional notes..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i><strong>Auto-Eligibility Check:</strong> When submitted, the system will automatically check EMI status, penalties, RERA compliance, documents, and commissions. If any check fails, the NOC will be blocked with detailed reasons.
                        </div>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-paper-plane me-2"></i>Submit NOC Request</button>
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
        document.getElementById('plotInfo').value = opt.dataset.plot ? 'Plot #' + opt.dataset.plot : '';
        document.getElementById('customerInfo').value = opt.text.split(' — ')[1] || '';
    }
});
</script>
<?php
$content = ob_get_clean();
include APP_PATH . '/views/admin/layouts/unified.php';
