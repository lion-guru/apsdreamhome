<?php
$base = rtrim(BASE_URL, '/');
$csrf = $this->getCsrfToken();
$mode = $mode ?? 'create';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - APS Dream Home</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>/assets/fonts/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/uiux-fixes.css?v=1">
    <style>
        .item-row { border: 1px solid #dee2e6; border-radius: .375rem; padding: 1rem; margin-bottom: 1rem; background: #f8f9fa; }
        .item-row .row > div { padding: 0 .5rem; }
        .calc-summary { background: #e7f3ff; border: 1px solid #b6d4fe; border-radius: .375rem; padding: 1rem; margin-top: 1rem; }
        .calc-row { display: flex; justify-content: space-between; padding: .25rem 0; }
        .calc-row.total { font-weight: 700; font-size: 1.1rem; color: #6B4EE6; border-top: 1px solid #b6d4fe; padding-top: .75rem; }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../../layouts/admin.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-<?= $mode === 'create' ? 'plus' : 'edit' ?> me-2 text-primary"></i><?= htmlspecialchars($page_title) ?></h1>
        <a href="<?= $base ?>/admin/gst/invoices" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>

    <form method="POST" id="invoiceForm" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <input type="hidden" name="item_count" id="itemCount" value="0">
        
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Invoice Details</h5></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Invoice Number <span class="text-danger">*</span></label>
                                <input type="text" name="invoice_number" class="form-control" required value="<?= htmlspecialchars($data['invoice_number'] ?? '') ?>" placeholder="e.g., INV-2024-001">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
                                <input type="date" name="invoice_date" class="form-control" required value="<?= htmlspecialchars($data['invoice_date'] ?? date('Y-m-d')) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Invoice Type <span class="text-danger">*</span></label>
                                <select name="invoice_type" class="form-select" id="invoiceType" required>
                                    <?php foreach ($invoiceTypes as $t): ?>
                                        <option value="<?= $t ?>" <?= ($data['invoice_type'] ?? '') == $t ? 'selected' : '' ?>><?= $t ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Supply Type</label>
                                <select name="supply_type" class="form-select" id="supplyType">
                                    <option value="intra_state" <?= ($data['supply_type'] ?? '') == 'intra_state' ? 'selected' : '' ?>>Intra-State</option>
                                    <option value="inter_state" <?= ($data['supply_type'] ?? '') == 'inter_state' ? 'selected' : '' ?>>Inter-State</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Place of Supply</label>
                                <input type="text" name="place_of_supply" class="form-control" id="placeOfSupply" value="<?= htmlspecialchars($data['place_of_supply'] ?? '') ?>" placeholder="State code (e.g., 09)" maxlength="2">
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="reverse_charge" id="reverseCharge" <?= ($data['reverse_charge'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="reverseCharge">Reverse Charge</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light"><h5 class="mb-0"><i class="fas fa-user me-2"></i>Customer Details</h5></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Customer GSTIN <span class="text-danger">*</span></label>
                                <input type="text" name="customer_gstin" class="form-control" id="customerGstin" value="<?= htmlspecialchars($data['customer_gstin'] ?? '') ?>" placeholder="15-char GSTIN" maxlength="15">
                                <div class="form-text">Mandatory for B2B invoices</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Customer Name</label>
                                <input type="text" name="customer_name" class="form-control" value="<?= htmlspecialchars($data['customer_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">State Code</label>
                                <input type="text" name="customer_state_code" class="form-control" value="<?= htmlspecialchars($data['customer_state_code'] ?? '') ?>" placeholder="e.g., 09" maxlength="2">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pincode</label>
                                <input type="text" name="customer_pincode" class="form-control" value="<?= htmlspecialchars($data['customer_pincode'] ?? '') ?>" placeholder="6-digit pincode" maxlength="6">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea name="customer_address" class="form-control" rows="2"><?= htmlspecialchars($data['customer_address'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Invoice Items</h5>
                        <button type="button" class="btn btn-sm btn-success" id="addItemBtn"><i class="fas fa-plus me-1"></i> Add Item</button>
                    </div>
                    <div class="card-body">
                        <div id="itemsContainer">
                            <!-- Items added dynamically -->
                        </div>
                        
                        <div class="calc-summary" id="calcSummary" style="display:none">
                            <h6 class="mb-3"><i class="fas fa-calculator me-1"></i>Invoice Summary</h6>
                            <div class="calc-row"><span>Total Taxable:</span><span id="sumTaxable">₹0.00</span></div>
                            <div class="calc-row"><span>Total CGST:</span><span id="sumCgst">₹0.00</span></div>
                            <div class="calc-row"><span>Total SGST:</span><span id="sumSgst">₹0.00</span></div>
                            <div class="calc-row"><span>Total IGST:</span><span id="sumIgst">₹0.00</span></div>
                            <div class="calc-row"><span>Total Cess:</span><span id="sumCess">₹0.00</span></div>
                            <div class="calc-row total"><span>Grand Total:</span><span id="sumTotal">₹0.00</span></div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light"><h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Reference & Notes</h5></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Booking</label>
                                <select name="booking_id" class="form-select">
                                    <option value="">None</option>
                                    <?php
                                    $bookings = $this->db->fetchAll("SELECT id, booking_number FROM bookings WHERE tenant_id = ? ORDER BY id DESC LIMIT 50", [$this->tenantId()]);
                                    foreach ($bookings as $b): ?>
                                        <option value="<?= $b['id'] ?>" <?= ($data['booking_id'] ?? '') == $b['id'] ? 'selected' : '' ?>><?= htmlspecialchars($b['booking_number']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Plot</label>
                                <select name="plot_id" class="form-select">
                                    <option value="">None</option>
                                    <?php foreach ($plots as $p): ?>
                                        <option value="<?= $p['id'] ?>" <?= ($data['plot_id'] ?? '') == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['plot_number']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"><?= htmlspecialchars($data['notes'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm sticky-top" style="top: 100px;">
                    <div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="fas fa-save me-2"></i>Actions</h5></div>
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-3 btn-lg"><i class="fas fa-save me-1"></i> <?= $mode === 'create' ? 'Create Invoice' : 'Update Invoice' ?></button>
                        <a href="<?= $base ?>/admin/gst/invoices" class="btn btn-outline-secondary w-100 mb-3"><i class="fas fa-times me-1"></i> Cancel</a>
                        <hr>
                        <h6 class="text-muted small">Quick Help</h6>
                        <ul class="small text-muted mb-0">
                            <li>Intra-State: CGST + SGST</li>
                            <li>Inter-State: IGST only</li>
                            <li>B2B requires Customer GSTIN</li>
                            <li>HSN auto-fills GST rates</li>
                            <li>IRN auto-generates for B2B >₹200</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<template id="itemTemplate">
    <div class="item-row" data-index="{INDEX}">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">HSN Code <span class="text-danger">*</span></label>
                <select name="item_hsn_{INDEX}" class="form-control form-control-sm item-hsn" required>
                    <option value="">Select HSN</option>
                    <?php foreach ($hsnCodes as $h): ?>
                        <option value="<?= $h['hsn_code'] ?>" data-gst="<?= $h['gst_rate'] ?>" data-cess="<?= $h['cess_rate'] ?>" data-unit="<?= $h['unit'] ?>" data-service="<?= $h['is_service'] ?>"><?= $h['hsn_code'] ?> - <?= htmlspecialchars(mb_strimwidth($h['description'], 0, 40, '...')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small">Description <span class="text-danger">*</span></label>
                <input type="text" name="item_desc_{INDEX}" class="form-control form-control-sm item-desc" required placeholder="Description">
            </div>
            <div class="col-md-2">
                <label class="form-label small">UOM</label>
                <input type="text" name="item_uom_{INDEX}" class="form-control form-control-sm item-uom" value="PCS">
            </div>
            <div class="col-md-1">
                <label class="form-label small">Qty</label>
                <input type="number" step="0.001" name="item_qty_{INDEX}" class="form-control form-control-sm item-qty" value="1" min="0.001" required>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Rate</label>
                <input type="number" step="0.01" name="item_rate_{INDEX}" class="form-control form-control-sm item-rate" value="0" min="0" required>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Disc</label>
                <input type="number" step="0.01" name="item_discount_{INDEX}" class="form-control form-control-sm item-discount" value="0" min="0">
            </div>
            <div class="col-md-2">
                <label class="form-label small">GST%</label>
                <input type="number" step="0.01" name="item_gst_{INDEX}" class="form-control form-control-sm item-gst" value="0" min="0" max="100" required>
            </div>
            <div class="col-md-1">
                <label class="form-label small">Cess%</label>
                <input type="number" step="0.01" name="item_cess_{INDEX}" class="form-control form-control-sm item-cess" value="0" min="0">
            </div>
            <div class="col-md-1">
                <label class="form-label small">Amount</label>
                <input type="text" name="item_amt_{INDEX}" class="form-control form-control-sm item-amt text-end" readonly style="background:#e9ecef">
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-outline-danger btn-sm remove-item" title="Remove"><i class="fas fa-trash"></i></button>
            </div>
        </div>
    </div>
</template>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
<script>
let itemIndex = 0;
const template = document.getElementById('itemTemplate').innerHTML;
const container = document.getElementById('itemsContainer');
const summary = document.getElementById('calcSummary');

function addItem() {
    const html = template.replace(/{INDEX}/g, itemIndex);
    container.insertAdjacentHTML('beforeend', html);
    bindItemEvents(itemIndex);
    itemIndex++;
    document.getElementById('itemCount').value = itemIndex;
    updateSummary();
}

function bindItemEvents(index) {
    const row = container.querySelector('[data-index="' + index + '"]');
    if (!row) return;
    
    const hsnSelect = row.querySelector('.item-hsn');
    const qty = row.querySelector('.item-qty');
    const rate = row.querySelector('.item-rate');
    const discount = row.querySelector('.item-discount');
    const gst = row.querySelector('.item-gst');
    const cess = row.querySelector('.item-cess');
    const amt = row.querySelector('.item-amt');
    const uom = row.querySelector('.item-uom');
    const desc = row.querySelector('.item-desc');
    const removeBtn = row.querySelector('.remove-item');

    function calc() {
        const q = parseFloat(qty.value) || 0;
        const r = parseFloat(rate.value) || 0;
        const d = parseFloat(discount.value) || 0;
        const g = parseFloat(gst.value) || 0;
        const c = parseFloat(cess.value) || 0;
        const taxable = (q * r) - d;
        let cgst = 0, sgst = 0, igst = 0;
        const supplyType = document.getElementById('supplyType').value;
        if (supplyType === 'inter_state') {
            igst = taxable * (g / 100);
        } else {
            cgst = taxable * (g / 200);
            sgst = taxable * (g / 200);
        }
        const cessAmt = taxable * (c / 100);
        const total = taxable + cgst + sgst + igst + cessAmt;
        amt.value = '₹' + total.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        updateSummary();
    }

    hsnSelect.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (opt.dataset.gst) {
            gst.value = opt.dataset.gst;
            gst.dispatchEvent(new Event('input'));
        }
        if (opt.dataset.cess) {
            cess.value = opt.dataset.cess;
            cess.dispatchEvent(new Event('input'));
        }
        if (opt.dataset.unit) {
            uom.value = opt.dataset.unit;
        }
        if (opt.dataset.service === '1') {
            document.getElementById('invoiceType').value = 'B2B';
        }
    });

    [qty, rate, discount, gst, cess].forEach(el => el.addEventListener('input', calc));
    removeBtn.addEventListener('click', () => {
        row.remove();
        document.getElementById('itemCount').value = --itemIndex;
        updateSummary();
    });

    calc();
}

function updateSummary() {
    let taxable = 0, cgst = 0, sgst = 0, igst = 0, cess = 0, total = 0;
    container.querySelectorAll('.item-row').forEach(row => {
        const q = parseFloat(row.querySelector('.item-qty').value) || 0;
        const r = parseFloat(row.querySelector('.item-rate').value) || 0;
        const d = parseFloat(row.querySelector('.item-discount').value) || 0;
        const g = parseFloat(row.querySelector('.item-gst').value) || 0;
        const c = parseFloat(row.querySelector('.item-cess').value) || 0;
        const t = (q * r) - d;
        taxable += t;
        const supplyType = document.getElementById('supplyType').value;
        if (supplyType === 'inter_state') {
            igst += t * (g / 100);
        } else {
            cgst += t * (g / 200);
            sgst += t * (g / 200);
        }
        cess += t * (c / 100);
        total += t + (supplyType === 'inter_state' ? t * (g / 100) : t * (g / 100)) + t * (c / 100);
    });
    document.getElementById('sumTaxable').textContent = '₹' + taxable.toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('sumCgst').textContent = '₹' + cgst.toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('sumSgst').textContent = '₹' + sgst.toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('sumIgst').textContent = '₹' + igst.toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('sumCess').textContent = '₹' + cess.toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('sumTotal').textContent = '₹' + (taxable + cgst + sgst + igst + cess).toLocaleString('en-IN', {minimumFractionDigits: 2});
    summary.style.display = itemIndex > 0 ? 'block' : 'none';
}

document.getElementById('addItemBtn').addEventListener('click', addItem);
document.getElementById('invoiceType').addEventListener('change', updateSummary);
document.getElementById('supplyType').addEventListener('change', updateSummary);

// Bootstrap validation
(function() {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();

// Add first item on load
addItem();
</script>
</body>
</html>