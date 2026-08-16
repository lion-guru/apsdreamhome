<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Edit Invoice</h1>
            <p class="text-muted mb-0"><?= htmlspecialchars($invoice['invoice_number'] ?? '') ?></p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/admin/invoices/manage/<?= $invoice['id'] ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Invoice
            </a>
        </div>
    </div>

    <?php if (!empty($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_GET['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>/admin/invoices/manage/<?= $invoice['id'] ?>/update" method="POST" id="invoiceForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? $_SESSION['csrf_token'] ?? '') ?>">

        <div class="row">
            <div class="col-lg-8">
                <div class="card aps-cp-card mb-4">
                    <div class="card-header aps-cp-card-header">
                        <span><i class="fas fa-user me-2"></i>Client Details</span>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Client Type</label>
                                <select name="client_type" class="form-select" id="clientType">
                                    <option value="customer" <?= ($invoice['client_type'] ?? '') === 'customer' ? 'selected' : '' ?>>Customer</option>
                                    <option value="associate" <?= ($invoice['client_type'] ?? '') === 'associate' ? 'selected' : '' ?>>Associate</option>
                                    <option value="vendor" <?= ($invoice['client_type'] ?? '') === 'vendor' ? 'selected' : '' ?>>Vendor</option>
                                    <option value="employee" <?= ($invoice['client_type'] ?? '') === 'employee' ? 'selected' : '' ?>>Employee</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Select Client</label>
                                <select name="client_id" class="form-select" id="clientSelect">
                                    <option value="">-- Select from users (optional) --</option>
                                    <?php if (!empty($users)): ?>
                                        <?php foreach ($users as $u): ?>
                                            <option value="<?= $u['id'] ?>" <?= (int)($invoice['client_id'] ?? 0) === (int)$u['id'] ? 'selected' : '' ?>
                                                data-name="<?= htmlspecialchars($u['name']) ?>" data-email="<?= htmlspecialchars($u['email'] ?? '') ?>" data-phone="<?= htmlspecialchars($u['phone'] ?? '') ?>">
                                                <?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['email'] ?? '') ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Client Name <span class="text-danger">*</span></label>
                                <input type="text" name="client_name" class="form-control" id="clientName" value="<?= htmlspecialchars($invoice['client_name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Email</label>
                                <input type="email" name="client_email" class="form-control" id="clientEmail" value="<?= htmlspecialchars($invoice['client_email'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Phone</label>
                                <input type="text" name="client_phone" class="form-control" id="clientPhone" value="<?= htmlspecialchars($invoice['client_phone'] ?? '') ?>">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Client Address</label>
                                <textarea name="client_address" class="form-control" rows="2" id="clientAddress"><?= htmlspecialchars($invoice['client_address'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card aps-cp-card mb-4">
                    <div class="card-header aps-cp-card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-list me-2"></i>Line Items</span>
                        <button type="button" class="btn btn-sm btn-primary" id="addItemBtn"><i class="fas fa-plus me-1"></i>Add Item</button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0" id="itemsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="style-42149">#</th>
                                        <th class="style-14247">Item Name <span class="text-danger">*</span></th>
                                        <th class="style-14637">Description</th>
                                        <th class="style-69407">Type</th>
                                        <th class="style-60520">Qty</th>
                                        <th class="style-2707">Unit Price</th>
                                        <th class="style-60520">Disc %</th>
                                        <th class="style-60520">Tax %</th>
                                        <th class="style-93361">Line Total</th>
                                        <th class="style-42149"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    <?php $rowNum = 0; foreach (($invoice['items'] ?? []) as $item): $rowNum++; ?>
                                        <tr class="item-row">
                                            <td><?= $rowNum ?></td>
                                            <td><input type="text" name="item_name[]" class="form-control form-control-sm" value="<?= htmlspecialchars($item['item_name'] ?? '') ?>" required></td>
                                            <td><input type="text" name="item_description[]" class="form-control form-control-sm" value="<?= htmlspecialchars($item['item_description'] ?? '') ?>"></td>
                                            <td>
                                                <select name="item_type[]" class="form-select form-select-sm">
                                                    <option value="property" <?= ($item['item_type'] ?? '') === 'property' ? 'selected' : '' ?>>Property</option>
                                                    <option value="service" <?= ($item['item_type'] ?? '') === 'service' ? 'selected' : '' ?>>Service</option>
                                                    <option value="product" <?= ($item['item_type'] ?? '') === 'product' ? 'selected' : '' ?>>Product</option>
                                                </select>
                                            </td>
                                            <td><input type="number" name="item_quantity[]" class="form-control form-control-sm calc-input" value="<?= $item['quantity'] ?? 1 ?>" min="0.01" step="any"></td>
                                            <td><input type="number" name="item_unit_price[]" class="form-control form-control-sm calc-input" value="<?= $item['unit_price'] ?? 0 ?>" min="0" step="0.01"></td>
                                            <td><input type="number" name="item_discount[]" class="form-control form-control-sm calc-input" value="<?= $item['discount_percent'] ?? 0 ?>" min="0" max="100" step="0.01"></td>
                                            <td><input type="number" name="item_tax[]" class="form-control form-control-sm calc-input" value="<?= $item['tax_percent'] ?? 18 ?>" min="0" max="100" step="0.01"></td>
                                            <td class="fw-bold item-total">₹<?= number_format($item['line_total'] ?? 0, 2) ?></td>
                                            <td><button type="button" class="btn btn-sm btn-outline-danger remove-item" title="Remove"><i class="fas fa-times"></i></button></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($invoice['items'])): ?>
                                        <tr class="item-row">
                                            <td>1</td>
                                            <td><input type="text" name="item_name[]" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="item_description[]" class="form-control form-control-sm"></td>
                                            <td>
                                                <select name="item_type[]" class="form-select form-select-sm">
                                                    <option value="property">Property</option>
                                                    <option value="service">Service</option>
                                                    <option value="product">Product</option>
                                                </select>
                                            </td>
                                            <td><input type="number" name="item_quantity[]" class="form-control form-control-sm calc-input" value="1" min="0.01" step="any"></td>
                                            <td><input type="number" name="item_unit_price[]" class="form-control form-control-sm calc-input" value="0" min="0" step="0.01"></td>
                                            <td><input type="number" name="item_discount[]" class="form-control form-control-sm calc-input" value="0" min="0" max="100" step="0.01"></td>
                                            <td><input type="number" name="item_tax[]" class="form-control form-control-sm calc-input" value="18" min="0" max="100" step="0.01"></td>
                                            <td class="fw-bold item-total">₹0.00</td>
                                            <td><button type="button" class="btn btn-sm btn-outline-danger remove-item" title="Remove"><i class="fas fa-times"></i></button></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr><td colspan="8" class="text-end border-0"><strong>Subtotal:</strong></td><td class="fw-bold" id="subtotalDisplay">₹0.00</td><td class="border-0"></td></tr>
                                    <tr><td colspan="8" class="text-end border-0"><strong>Tax (GST):</strong></td><td class="fw-bold text-primary" id="taxDisplay">₹0.00</td><td class="border-0"></td></tr>
                                    <tr class="table-active"><td colspan="8" class="text-end border-0"><strong>Total:</strong></td><td class="fw-bold" id="totalDisplay">₹0.00</td><td class="border-0"></td></tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card aps-cp-card mb-4">
                    <div class="card-header aps-cp-card-header">
                        <span><i class="fas fa-cog me-2"></i>Invoice Settings</span>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <div class="mb-3">
                            <label class="form-label">Invoice Number</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($invoice['invoice_number'] ?? '') ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Due Date</label>
                            <input type="date" name="due_date" class="form-control" value="<?= htmlspecialchars($invoice['due_date'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Place of Supply</label>
                            <input type="text" name="place_of_supply" class="form-control" value="<?= htmlspecialchars($invoice['place_of_supply'] ?? 'Uttar Pradesh') ?>" id="placeOfSupply">
                            <small class="text-muted">UP = CGST+SGST | Other = IGST</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">GSTIN</label>
                            <input type="text" name="gstin" class="form-control" value="<?= htmlspecialchars($invoice['gstin'] ?? '') ?>" maxlength="15">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">HSN Code</label>
                            <input type="text" name="hsn_code" class="form-control" value="<?= htmlspecialchars($invoice['hsn_code'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Discount Amount</label>
                            <input type="number" name="discount_amount" class="form-control" value="<?= $invoice['discount_amount'] ?? 0 ?>" min="0" step="0.01">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="draft" <?= ($invoice['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                                <option value="sent" <?= ($invoice['status'] ?? '') === 'sent' ? 'selected' : '' ?>>Sent</option>
                                <option value="viewed" <?= ($invoice['status'] ?? '') === 'viewed' ? 'selected' : '' ?>>Viewed</option>
                                <option value="paid" <?= ($invoice['status'] ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
                                <option value="overdue" <?= ($invoice['status'] ?? '') === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                                <option value="cancelled" <?= ($invoice['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card aps-cp-card mb-4">
                    <div class="card-header aps-cp-card-header">
                        <span><i class="fas fa-sticky-note me-2"></i>Notes & Terms</span>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <div class="mb-3">
                            <label class="form-label">Payment Terms</label>
                            <textarea name="payment_terms" class="form-control" rows="2"><?= htmlspecialchars($invoice['payment_terms'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($invoice['notes'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>Update Invoice
                    </button>
                    <a href="<?= BASE_URL ?>/admin/invoices/manage/<?= $invoice['id'] ?>" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tbody = document.getElementById('itemsBody');
    const addItemBtn = document.getElementById('addItemBtn');
    let rowIndex = tbody.querySelectorAll('.item-row').length;

    // Client select auto-fill
    const clientSelect = document.getElementById('clientSelect');
    if (clientSelect) {
        clientSelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (opt && opt.value) {
                document.getElementById('clientName').value = opt.dataset.name || '';
                document.getElementById('clientEmail').value = opt.dataset.email || '';
                document.getElementById('clientPhone').value = opt.dataset.phone || '';
            }
        });
    }

    addItemBtn.addEventListener('click', function() {
        rowIndex++;
        const row = document.createElement('tr');
        row.className = 'item-row';
        row.innerHTML = `
            <td>${rowIndex}</td>
            <td><input type="text" name="item_name[]" class="form-control form-control-sm" required></td>
            <td><input type="text" name="item_description[]" class="form-control form-control-sm"></td>
            <td><select name="item_type[]" class="form-select form-select-sm"><option value="property">Property</option><option value="service">Service</option><option value="product">Product</option></select></td>
            <td><input type="number" name="item_quantity[]" class="form-control form-control-sm calc-input" value="1" min="0.01" step="any"></td>
            <td><input type="number" name="item_unit_price[]" class="form-control form-control-sm calc-input" value="0" min="0" step="0.01"></td>
            <td><input type="number" name="item_discount[]" class="form-control form-control-sm calc-input" value="0" min="0" max="100" step="0.01"></td>
            <td><input type="number" name="item_tax[]" class="form-control form-control-sm calc-input" value="18" min="0" max="100" step="0.01"></td>
            <td class="fw-bold item-total">₹0.00</td>
            <td><button type="button" class="btn btn-sm btn-outline-danger remove-item"><i class="fas fa-times"></i></button></td>
        `;
        tbody.appendChild(row);
        recalcAll();
    });

    tbody.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
            const rows = tbody.querySelectorAll('.item-row');
            if (rows.length > 1) {
                e.target.closest('.item-row').remove();
                renumberRows();
                recalcAll();
            }
        }
    });

    tbody.addEventListener('input', function(e) {
        if (e.target.classList.contains('calc-input')) {
            recalcRow(e.target.closest('.item-row'));
            recalcAll();
        }
    });

    function recalcRow(row) {
        const qty = parseFloat(row.querySelector('[name="item_quantity[]"]').value) || 0;
        const price = parseFloat(row.querySelector('[name="item_unit_price[]"]').value) || 0;
        const disc = parseFloat(row.querySelector('[name="item_discount[]"]').value) || 0;
        const tax = parseFloat(row.querySelector('[name="item_tax[]"]').value) || 0;
        const gross = qty * price;
        const discAmt = gross * disc / 100;
        const taxable = gross - discAmt;
        const taxAmt = taxable * tax / 100;
        row.querySelector('.item-total').textContent = '₹' + (taxable + taxAmt).toFixed(2);
    }

    function renumberRows() {
        const rows = tbody.querySelectorAll('.item-row');
        rows.forEach((r, i) => { r.querySelector('td').textContent = i + 1; });
        rowIndex = rows.length;
    }

    function recalcAll() {
        let subtotal = 0;
        let totalTax = 0;
        tbody.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('[name="item_quantity[]"]')?.value) || 0;
            const price = parseFloat(row.querySelector('[name="item_unit_price[]"]')?.value) || 0;
            const disc = parseFloat(row.querySelector('[name="item_discount[]"]')?.value) || 0;
            const tax = parseFloat(row.querySelector('[name="item_tax[]"]')?.value) || 0;
            const gross = qty * price;
            const discAmt = gross * disc / 100;
            const taxable = gross - discAmt;
            const taxAmt = taxable * tax / 100;
            subtotal += taxable + taxAmt;
            totalTax += taxAmt;
        });
        document.getElementById('subtotalDisplay').textContent = '₹' + subtotal.toFixed(2);
        document.getElementById('taxDisplay').textContent = '₹' + totalTax.toFixed(2);
        document.getElementById('totalDisplay').textContent = '₹' + subtotal.toFixed(2);
    }

    recalcAll();
});
</script>
