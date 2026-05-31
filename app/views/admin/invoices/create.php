<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Create Invoice</h1>
            <p class="text-muted mb-0">Generate new invoice for plot booking</p>
        </div>
        <div>
            <a href="/admin/invoices" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Invoices
            </a>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header py-3 bg-white">
            <h6 class="m-0 font-weight-bold text-primary">New Invoice Details</h6>
        </div>
        <div class="card-body">
            <form action="/admin/invoices/store" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? $_SESSION['csrf_token'] ?? ''; ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Invoice Number</label>
                        <input type="text" name="invoice_number" class="form-control" 
                               value="<?= $invoice_number ?? '' ?>" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">Select Customer</option>
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $customer): ?>
                                    <option value="<?= $customer['id'] ?>">
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
                                    <option value="<?= $property['id'] ?>">
                                        <?= htmlspecialchars($property['title']) ?> - ₹<?= number_format($property['price'] ?? 0) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" step="0.01" required 
                               placeholder="Enter amount">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control" 
                               value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Installment Number</label>
                        <input type="number" name="installment_number" class="form-control" value="1" min="1">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Total Installments</label>
                        <input type="number" name="total_installments" class="form-control" value="1" min="1">
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" 
                                  placeholder="Enter invoice description..."></textarea>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <a href="/admin/invoices" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Create Invoice
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
