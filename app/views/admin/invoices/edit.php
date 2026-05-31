<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Edit Invoice</h1>
            <p class="text-muted mb-0"><?= $invoice['invoice_number'] ?? '' ?></p>
        </div>
        <div>
            <a href="/admin/invoices/<?= $invoice['id'] ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Invoice
            </a>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header py-3 bg-white">
            <h6 class="m-0 font-weight-bold text-primary">Edit Invoice Details</h6>
        </div>
        <div class="card-body">
            <form action="/admin/invoices/<?= $invoice['id'] ?>/update" method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Invoice Number</label>
                        <input type="text" name="invoice_number" class="form-control" 
                               value="<?= htmlspecialchars($invoice['invoice_number'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">Select Customer</option>
                            <?php if (!empty($customers)): ?>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?= $customer['id'] ?>" <?= ($invoice['customer_id'] == $customer['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($customer['name']) ?> (<?= htmlspecialchars($customer['email'] ?? '') ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Property</label>
                        <select name="property_id" class="form-select">
                            <option value="">Select Property (Optional)</option>
                            <?php if (!empty($properties)): ?>
                                <?php foreach ($properties as $property): ?>
                                    <option value="<?= $property['id'] ?>" <?= ($invoice['property_id'] == $property['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($property['title']) ?> - ₹<?= number_format($property['price'] ?? 0) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" step="0.01" required 
                               value="<?= $invoice['amount'] ?? '' ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control" 
                               value="<?= $invoice['due_date'] ?? '' ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="draft" <?= ($invoice['status'] == 'draft') ? 'selected' : '' ?>>Draft</option>
                            <option value="sent" <?= ($invoice['status'] == 'sent') ? 'selected' : '' ?>>Sent</option>
                            <option value="partial" <?= ($invoice['status'] == 'partial') ? 'selected' : '' ?>>Partial Payment</option>
                            <option value="paid" <?= ($invoice['status'] == 'paid') ? 'selected' : '' ?>>Paid</option>
                            <option value="overdue" <?= ($invoice['status'] == 'overdue') ? 'selected' : '' ?>>Overdue</option>
                            <option value="cancelled" <?= ($invoice['status'] == 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($invoice['description'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <a href="/admin/invoices/<?= $invoice['id'] ?>" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Invoice
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
