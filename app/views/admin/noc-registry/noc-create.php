<?php
$page_title = $page_title ?? 'Request NOC';
ob_start();
$eligible_bookings = $eligible_bookings ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-file-signature me-2"></i><?= htmlspecialchars($page_title) ?></h4>
        <span class="text-muted">Submit a new No Objection Certificate request</span>
    </div>
    <a href="<?= BASE_URL ?>/admin/noc-registry/nocs" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to NOCs</a>
</div>

<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0">NOC Request Details</h6>
    </div>
    <div class="card-body">
        <?php if (empty($eligible_bookings)): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>No eligible bookings found.</strong> All bookings must be fully paid or registered before an NOC can be requested.
            </div>
        <?php else: ?>
            <form method="POST" action="<?= BASE_URL ?>/admin/noc-registry/nocs/store">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Booking <span class="text-danger">*</span></label>
                        <select name="booking_id" class="form-select" required>
                            <option value="">— Select Booking —</option>
                            <?php foreach ($eligible_bookings as $b): ?>
                                <option value="<?= $b['id'] ?>">
                                    <?= htmlspecialchars($b['booking_number']) ?> — <?= htmlspecialchars($b['customer_name']) ?>
                                    (<?= htmlspecialchars($b['plot_no']) ?>, <?= htmlspecialchars($b['colony_name']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Purpose <span class="text-danger">*</span></label>
                        <select name="purpose" class="form-select" required>
                            <option value="Property transfer / Registry">Property Transfer / Registry</option>
                            <option value="Bank loan processing">Bank Loan Processing</option>
                            <option value="Court order compliance">Court Order Compliance</option>
                            <option value="Mutation / Name transfer">Mutation / Name Transfer</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Notes / Remarks</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Any additional details or special instructions..."></textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i>Submit Request</button>
                    <a href="<?= BASE_URL ?>/admin/noc-registry/nocs" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/unified.php';
