<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Create Property Allocation</h1>
        <a href="<?= BASE_URL ?>/admin/property-allocations" class="btn btn-secondary">Back</a>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error ?? '') ?></div>
    <?php endif; ?>

    <div class="card aps-cp-card">
        <div class="card-body aps-cp-card-body">
            <form method="POST" action="<?= BASE_URL ?>/admin/property-allocations/store">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? $_SESSION['csrf_token'] ?? ''; ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">Select Customer</option>
                            <?php foreach ($users as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name'] ?? '') ?> (<?= htmlspecialchars($c['phone'] ?? '') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Property <span class="text-danger">*</span></label>
                        <select name="property_id" class="form-select" required>
                            <option value="">Select Property</option>
                            <?php foreach ($properties as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['title'] ?? '') ?> - Plot <?= htmlspecialchars($p['plot_number'] ?? '') ?> (<?= number_format($p['area_sqft'] ?? 0) ?> sqft)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Booking Amount <span class="text-danger">*</span></label>
                        <input type="number" name="booking_amount" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Total Price <span class="text-danger">*</span></label>
                        <input type="number" name="total_price" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Installment Plan</label>
                        <select name="installment_plan" class="form-select">
                            <option value="full_payment">Full Payment</option>
                            <option value="half_yearly">Half Yearly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Installment Months (if applicable)</label>
                        <input type="number" name="installment_months" class="form-control" value="0">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Create Allocation</button>
            </form>
        </div>
    </div>
</div>
