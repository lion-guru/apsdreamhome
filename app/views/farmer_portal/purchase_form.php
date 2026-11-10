<?php
/**
 * Farmer Portal - Record Land Purchase form
 */
$farmer = $farmer ?? [];
$farmer_id = $farmer_id ?? ($farmer['id'] ?? 0);
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-cart-plus me-2 text-primary"></i>Record Land Purchase</h4>
        <a href="<?= BASE_URL ?>/farmers_admin/<?= (int)$farmer_id ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Farmer
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars((string)$error) ?></div>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars((string)$message) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><?= htmlspecialchars((string)($farmer['name'] ?? 'Farmer')) ?></h5>
        </div>
        <div class="card-body">
            <form method="post" action="<?= BASE_URL ?>/farmers/<?= (int)$farmer_id ?>/store-purchase">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)($csrf_token ?? '')) ?>">

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Purchase Amount (&#8377;) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Transaction Date</label>
                        <input type="date" name="transaction_date" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cash">Cash</option>
                            <option value="cheque">Cheque</option>
                            <option value="upi">UPI</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Reference Number</label>
                        <input type="text" name="reference_number" class="form-control" maxlength="100"
                               placeholder="UTR / Cheque / Transaction No.">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="completed">Completed</option>
                            <option value="pending">Pending</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" maxlength="1000"
                                  placeholder="Land details, seller, registry reference..."></textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Purchase
                    </button>
                    <a href="<?= BASE_URL ?>/farmers_admin/<?= (int)$farmer_id ?>" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
