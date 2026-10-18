<?php
$pos = $pos ?? [];
$today = $today ?? date('Y-m-d');
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Create GRN</h1>
        <a href="<?= BASE_URL ?>/admin/grn" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back to GRNs</a>
    </div>

    <div class="card mb-4"><div class="card-body">
        <form id="grnForm" onsubmit="return submitGrn(event);">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Purchase Order <span class="text-danger">*</span></label>
                    <select name="purchase_order_id" id="purchase_order_id" class="form-select" required onchange="loadPoItems(this.value)">
                        <option value="">-- Select an approved/received PO --</option>
                        <?php foreach ($pos as $po): ?>
                        <option value="<?= $po['id'] ?>"><?= htmlspecialchars($po['order_number']) ?> — <?= htmlspecialchars($po['vendor_name']) ?> (₹<?= number_format($po['total_amount'] ?? 0, 2) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">GRN Number</label>
                    <input type="text" name="grn_number" id="grn_number" class="form-control" placeholder="GRN-20261016...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Invoice Number</label>
                    <input type="text" name="invoice_number" class="form-control" placeholder="Invoice #">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Invoice Date</label>
                    <input type="date" name="invoice_date" class="form-control">
                </div>
            </div>

            <div class="row g-3 mt-3">
                <div class="col-md-3"><label class="form-label">Transporter</label><input type="text" name="transport_name" class="form-control"></div>
                <div class="col-md-3"><label class="form-label">Vehicle No.</label><input type="text" name="vehicle_number" class="form-control"></div>
                <div class="col-md-3"><label class="form-label">Driver Name</label><input type="text" name="driver_name" class="form-control"></div>
                <div class="col-md-3"><label class="form-label">Driver Phone</label><input type="text" name="driver_phone" class="form-control"></div>
            </div>

            <div class="row g-3 mt-3">
                <div class="col-md-3"><label class="form-label">Challan Number</label><input type="text" name="challan_number" class="form-control"></div>
                <div class="col-md-3"><label class="form-label">Challan Date</label><input type="date" name="challan_date" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Remarks</label><input type="text" name="remarks" class="form-control"></div>
            </div>

            <div class="mt-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="draft">Draft</option>
                    <option value="verified">Verified</option>
                    <option value="accepted">Accepted</option>
                </select>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Create GRN</button>
                <a href="<?= BASE_URL ?>/admin/grn" class="btn btn-outline-secondary ms-2">Cancel</a>
            </div>
        </form>
    </div></div>
</div>

<script>
let poItems = [];
function loadPoItems(poId) {
    if (!poId) { poItems = []; return; }
    // Fetch PO items to pre-fill GRN items (server-side createFromPo handles fallback)
    fetch('<?= BASE_URL ?>/admin/purchase-orders/' + poId)
        .then(r => r.json()).then(d => {
            if (d.success && d.order) {
                poItems = d.items || [];
            }
        }).catch(() => {});
}
function submitGrn(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    const orig = btn.innerText;
    btn.innerText = 'Creating...'; btn.disabled = true;
    const formData = new FormData(e.target);
    fetch('<?= BASE_URL ?>/admin/grn/store', {
        method: 'POST',
        headers: {'X-Requested-With':'XMLHttpRequest'},
        body: formData
    }).then(r => r.json()).then(d => {
        btn.innerText = orig; btn.disabled = false;
        if (d.success) { location.href = d.redirect; }
        else { alert(d.error || 'Failed to create GRN'); }
    }).catch(() => { btn.innerText = orig; btn.disabled = false; alert('Error'); });
}
</script>
