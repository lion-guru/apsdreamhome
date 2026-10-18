<?php
$vendors = $vendors ?? [];
$departments = $departments ?? [];
$today = $today ?? date('Y-m-d');
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Create Purchase Order</h1>
        <a href="<?= BASE_URL ?>/admin/purchase-orders" class="btn btn-secondary">Back</a>
    </div>
    <div class="card"><div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/admin/purchase-orders/store" id="poForm">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div class="row">
                <div class="col-md-3"><label>Vendor <span class="text-danger">*</span></label><select name="vendor_id" class="form-select" required id="vendorSelect"><option value="">Select Vendor</option><?php foreach ($vendors as $v) { ?><option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['name']) ?></option><?php } ?></select></div>
                <div class="col-md-3"><label>Order Number</label><input type="text" name="order_number" class="form-control" value="PO-<?= date('YmdHis') ?>" readonly></div>
                <div class="col-md-3"><label>Order Date <span class="text-danger">*</span></label><input type="date" name="order_date" class="form-control" value="<?= $today ?>" required></div>
                <div class="col-md-3"><label>Expected Delivery</label><input type="date" name="expected_delivery" class="form-control"></div>
                <div class="col-md-4"><label>Department</label><select name="department_id" class="form-select"><option value="">None</option><?php foreach ($departments as $d) { ?><option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option><?php } ?></select></div>
                <div class="col-md-4"><label>Delivery Location</label><input type="text" name="delivery_location" class="form-control"></div>
                <div class="col-md-4"><label>Payment Terms</label><input type="text" name="payment_terms" class="form-control"></div>
            </div>
            <h5 class="mt-4">Line Items</h5>
            <table class="table table-bordered" id="itemsTable">
                <thead><tr><th>Item</th><th>Description</th><th>Unit</th><th>Qty</th><th>Price</th><th>Disc %</th><th>Tax %</th><th>Total</th><th></th></tr></thead>
                <tbody>
                    <tr data-line="1">
                        <td><input type="text" name="items[1][item_name]" class="form-control form-control-sm" placeholder="Item name" required></td>
                        <td><input type="text" name="items[1][item_description]" class="form-control form-control-sm"></td>
                        <td><input type="text" name="items[1][unit]" class="form-control form-control-sm" value="Nos"></td>
                        <td><input type="number" name="items[1][quantity]" class="form-control form-control-sm po-qty" step="0.01" min="1" value="1" required></td>
                        <td><input type="number" name="items[1][unit_price]" class="form-control form-control-sm po-price" step="0.01" min="0" value="0" required></td>
                        <td><input type="number" name="items[1][discount_percent]" class="form-control form-control-sm po-disc" step="0.01" min="0" value="0"></td>
                        <td><input type="number" name="items[1][tax_percent]" class="form-control form-control-sm po-tax" step="0.01" min="0" value="0"></td>
                        <td class="text-end fw-bold po-total">0.00</td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger po-remove" disabled><i class="fas fa-trash"></i></button></td>
                    </tr>
                </tbody>
            </table>
            <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="addLineBtn"><i class="fas fa-plus me-1"></i>Add Item</button>
            <div class="row mt-4">
                <div class="col-md-3"><div class="alert alert-info mb-0">Subtotal: <strong id="poSubtotal">0.00</strong></div></div>
                <div class="col-md-3"><label>Tax (₹)</label><input type="number" name="tax_amount" id="poTax" class="form-control" step="0.01" value="0" readonly></div>
                <div class="col-md-3"><label>Discount (₹)</label><input type="number" name="discount_amount" id="poDiscount" class="form-control" step="0.01" value="0"></div>
                <div class="col-md-3"><div class="alert alert-success mb-0">Total: <strong id="poGrandTotal">0.00</strong><input type="hidden" name="total_amount" id="poTotalInput" value="0"></div></div>
            </div>
            <div class="row mt-4">
                <div class="col-md-6"><label>Terms & Conditions</label><textarea name="terms_conditions" class="form-control" rows="2"></textarea></div>
                <div class="col-md-6"><label>Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
            </div>
            <div class="mt-4"><button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Create Purchase Order</button></div>
        </form>
    </div></div>
</div>
<script>
let poLineCount = 1;
function poUpdateTotals() {
    let subtotal = 0, tax = 0;
    document.querySelectorAll('#itemsTable tbody tr').forEach(function(row) {
        let qty = parseFloat(row.querySelector('.po-qty').value) || 0;
        let price = parseFloat(row.querySelector('.po-price').value) || 0;
        let disc = parseFloat(row.querySelector('.po-disc').value) || 0;
        let taxPct = parseFloat(row.querySelector('.po-tax').value) || 0;
        let lineTotal = qty * price;
        lineTotal -= lineTotal * disc / 100;
        let lineTax = lineTotal * taxPct / 100;
        lineTotal += lineTax;
        row.querySelector('.po-total').textContent = lineTotal.toFixed(2);
        subtotal += lineTotal;
        tax += lineTax;
    });
    let discount = parseFloat(document.getElementById('poDiscount').value) || 0;
    let grandTotal = subtotal - discount;
    document.getElementById('poSubtotal').textContent = subtotal.toFixed(2);
    document.getElementById('poTax').value = tax.toFixed(2);
    document.getElementById('poGrandTotal').textContent = grandTotal.toFixed(2);
    document.getElementById('poTotalInput').value = grandTotal.toFixed(2);
}
document.getElementById('itemsTable').addEventListener('input', function(e) {
    if (e.target.classList.contains('po-qty') || e.target.classList.contains('po-price') || e.target.classList.contains('po-disc') || e.target.classList.contains('po-tax')) { poUpdateTotals(); }
});
document.getElementById('itemsTable').addEventListener('click', function(e) {
    if (e.target.closest('.po-remove')) {
        let rows = document.querySelectorAll('#itemsTable tbody tr');
        if (rows.length > 1) { e.target.closest('tr').remove(); poUpdateTotals(); }
    }
});
document.getElementById('addLineBtn').addEventListener('click', function() {
    poLineCount++;
    let tbody = document.querySelector('#itemsTable tbody');
    let newRow = document.createElement('tr');
    newRow.dataset.line = poLineCount;
    let idx = poLineCount;
    newRow.innerHTML = '<td><input type="text" name="items[' + idx + '][item_name]" class="form-control form-control-sm" required></td><td><input type="text" name="items[' + idx + '][item_description]" class="form-control form-control-sm"></td><td><input type="text" name="items[' + idx + '][unit]" class="form-control form-control-sm" value="Nos"></td><td><input type="number" name="items[' + idx + '][quantity]" class="form-control form-control-sm po-qty" step="0.01" min="1" value="1" required></td><td><input type="number" name="items[' + idx + '][unit_price]" class="form-control form-control-sm po-price" step="0.01" min="0" value="0" required></td><td><input type="number" name="items[' + idx + '][discount_percent]" class="form-control form-control-sm po-disc" step="0.01" min="0" value="0"></td><td><input type="number" name="items[' + idx + '][tax_percent]" class="form-control form-control-sm po-tax" step="0.01" min="0" value="0"></td><td class="text-end fw-bold po-total">0.00</td><td><button type="button" class="btn btn-sm btn-outline-danger po-remove"><i class="fas fa-trash"></i></button></td>';
    tbody.appendChild(newRow);
    poUpdateTotals();
});
document.getElementById('poDiscount').addEventListener('input', poUpdateTotals);
poUpdateTotals();
</script>