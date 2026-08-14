<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Create Invoice</h1>
            <p class="text-muted mb-0">Generate a new professional invoice with GST/TDS breakdown</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/admin/invoices/manage" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Invoices
            </a>
        </div>
    </div>

    <?php if (!empty($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_GET['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>/admin/invoices/manage/store" method="POST" id="invoiceForm">
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
                                <label class="form-label">Client Type <span class="text-danger">*</span></label>
                                <select name="client_type" class="form-select" id="clientType" required>
                                    <option value="customer">Customer</option>
                                    <option value="associate">Associate</option>
                                    <option value="vendor">Vendor</option>
                                    <option value="employee">Employee</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Select Client</label>
                                <select name="client_id" class="form-select" id="clientSelect">
                                    <option value="">-- Select from users (optional) --</option>
                                    <?php if (!empty($users)): ?>
                                        <?php foreach ($users as $u): ?>
                                            <option value="<?= $u['id'] ?>" data-name="<?= htmlspecialchars($u['name']) ?>" data-email="<?= htmlspecialchars($u['email'] ?? '') ?>" data-phone="<?= htmlspecialchars($u['phone'] ?? '') ?>">
                                                <?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['email'] ?? '') ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Client Name <span class="text-danger">*</span></label>
                                <input type="text" name="client_name" class="form-control" id="clientName" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Email</label>
                                <input type="email" name="client_email" class="form-control" id="clientEmail">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Phone</label>
                                <input type="text" name="client_phone" class="form-control" id="clientPhone">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Client Address</label>
                                <textarea name="client_address" class="form-control" rows="2" id="clientAddress"></textarea>
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
                                        <td class="fw-bold item-total">â‚¹0.00</td>
                                        <td><button type="button" class="btn btn-sm btn-outline-danger remove-item" title="Remove"><i class="fas fa-times"></i></button></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr><td colspan="8" class="text-end border-0"><strong>Subtotal:</strong></td><td class="fw-bold" id="subtotalDisplay">â‚¹0.00</td><td class="border-0"></td></tr>
                                    <tr><td colspan="8" class="text-end border-0"><strong>Tax (GST):</strong></td><td class="fw-bold text-primary" id="taxDisplay">â‚¹0.00</td><td class="border-0"></td></tr>
                                    <tr class="table-active"><td colspan="8" class="text-end border-0"><strong>Total:</strong></td><td class="fw-bold" id="totalDisplay">â‚¹0.00</td><td class="border-0"></td></tr>
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
                            <input type="text" class="form-control" value="<?= htmlspecialchars($invoice_number ?? '') ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Due Date <span class="text-danger">*</span></label>
                            <input type="date" name="due_date" class="form-control" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Place of Supply <span class="text-danger">*</span></label>
                            <input type="text" name="place_of_supply" class="form-control" value="Uttar Pradesh" id="placeOfSupply" required>
                            <small class="text-muted">UP = CGST+SGST | Other = IGST</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">GSTIN</label>
                            <input type="text" name="gstin" class="form-control" value="<?= htmlspecialchars($company['gstin'] ?? '') ?>" maxlength="15">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">HSN Code</label>
                            <input type="text" name="hsn_code" class="form-control" value="9954" placeholder="9954">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="draft">Draft</option>
                                <option value="sent">Sent</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Link to Booking (Optional)</label>
                            <select name="booking_id" class="form-select">
                                <option value="">-- No booking --</option>
                                <?php if (!empty($bookings)): ?>
                                    <?php foreach ($bookings as $b): ?>
                                        <option value="<?= $b['id'] ?>">
                                            <?= htmlspecialchars($b['booking_number'] ?? 'BK-'.$b['id']) ?> - <?= htmlspecialchars($b['client_name'] ?? '') ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
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
                            <textarea name="payment_terms" class="form-control" rows="2" placeholder="e.g., Payment due within 30 days..."><?= htmlspecialchars($company['payment_terms'] ?? 'Payment due within 30 days from invoice date.') ?></textarea>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Additional notes (optional)"></textarea>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>Create Invoice
                    </button>
                    <a href="<?= BASE_URL ?>/admin/invoices/manage" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tbody = document.getElementById('itemsBody');
    const addItemBtn = document.getElementById('addItemBtn');
    let rowIndex = 1;

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

    // Add item row
    addItemBtn.addEventListener('click', function() {
        rowIndex++;
        const row = document.createElement('tr');
        row.className = 'item-row';
        row.innerHTML = `
            <td>${rowIndex}</td>
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
            <td class="fw-bold item-total">â‚¹0.00</td>
            <td><button type="button" class="btn btn-sm btn-outline-danger remove-item" title="Remove"><i class="fas fa-times"></i></button></td>
        `;
        tbody.appendChild(row);
        recalcAll();
    });

    // Remove item row
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

    // Live recalculation
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
        const lineTotal = taxable + taxAmt;
        row.querySelector('.item-total').textContent = 'â‚¹' + lineTotal.toFixed(2);
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
            const qty = parseFloat(row.querySelector('[name="item_quantity[]"]').value) || 0;
            const price = parseFloat(row.querySelector('[name="item_unit_price[]"]').value) || 0;
            const disc = parseFloat(row.querySelector('[name="item_discount[]"]').value) || 0;
            const tax = parseFloat(row.querySelector('[name="item_tax[]"]').value) || 0;
            const gross = qty * price;
            const discAmt = gross * disc / 100;
            const taxable = gross - discAmt;
            const taxAmt = taxable * tax / 100;
            subtotal += gross - discAmt + taxAmt;
            totalTax += taxAmt;
        });
        document.getElementById('subtotalDisplay').textContent = 'â‚¹' + subtotal.toFixed(2);
        document.getElementById('taxDisplay').textContent = 'â‚¹' + totalTax.toFixed(2);
        document.getElementById('totalDisplay').textContent = 'â‚¹' + subtotal.toFixed(2);
    }

    recalcAll();
});
</script>
