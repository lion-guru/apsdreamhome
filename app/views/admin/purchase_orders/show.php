<?php
$order = $order ?? [];
$items = $items ?? [];
$grnRecords = $grnRecords ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>PO: <?= htmlspecialchars($order['order_number'] ?? '') ?></h1>
        <div>
            <?php if (($order['status'] ?? '') === 'pending'): ?>
            <form method="POST" action="<?= BASE_URL ?>/admin/purchase-orders/approve/<?= $order['id'] ?? '' ?>" class="d-inline" onsubmit="return confirm('Approve this PO?');"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><button class="btn btn-success me-2"><i class="fas fa-check me-2"></i>Approve</button></form>
            <?php endif; ?>
            <?php if (($order['status'] ?? '') === 'approved'): ?>
            <button class="btn btn-info me-2" data-bs-toggle="modal" data-bs-target="#receiveModal"><i class="fas fa-truck me-2"></i>Receive (GRN)</button>
            <?php endif; ?>
            <?php if (in_array($order['status'] ?? '', ['pending','approved'])): ?>
            <form method="POST" action="<?= BASE_URL ?>/admin/purchase-orders/cancel/<?= $order['id'] ?? '' ?>" class="d-inline" onsubmit="return confirm('Cancel this PO?');"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><button class="btn btn-danger"><i class="fas fa-times me-2"></i>Cancel</button></form>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/admin/purchase-orders" class="btn btn-secondary ms-2">Back</a>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Vendor</h6><p class="mb-0 fw-bold"><?= htmlspecialchars($order['vendor_name'] ?? '—') ?></p></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Order Date</h6><p class="mb-0"><?= date('M d, Y', strtotime($order['order_date'] ?? $order['created_at'])) ?></p></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Expected Delivery</h6><p class="mb-0"><?= $order['expected_delivery'] ? date('M d, Y', strtotime($order['expected_delivery'])) : '—' ?></p></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Status</h6><p class="mb-0"><span class="badge bg-<?= match($order['status']) { 'approved'=>'success', 'received'=>'info', 'cancelled'=>'danger', default=>'warning' } ?>"><?= ucfirst($order['status'] ?? '') ?></span></p></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Payment</h6><p class="mb-0"><span class="badge bg-<?= $order['payment_status']==='paid'?'success':'warning' ?>"><?= ucfirst($order['payment_status'] ?? 'unpaid') ?></span></p></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Department</h6><p class="mb-0"><?= htmlspecialchars($order['department_name'] ?? '—') ?></p></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Delivery Location</h6><p class="mb-0"><?= htmlspecialchars($order['delivery_location'] ?? '—') ?></p></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Payment Terms</h6><p class="mb-0"><?= htmlspecialchars($order['payment_terms'] ?? '—') ?></p></div></div></div>
        <div class="col-md-3"><div class="card bg-primary text-white"><div class="card-body"><h6>Subtotal</h6><p class="mb-0 fw-bold">₹<?= number_format($order['subtotal'] ?? 0, 2) ?></p></div></div></div>
        <div class="col-md-3"><div class="card bg-info text-white"><div class="card-body"><h6>Tax</h6><p class="mb-0 fw-bold">₹<?= number_format($order['tax_amount'] ?? 0, 2) ?></p></div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-dark"><div class="card-body"><h6>Discount</h6><p class="mb-0 fw-bold">₹<?= number_format($order['discount_amount'] ?? 0, 2) ?></p></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white"><div class="card-body"><h6>Total</h6><p class="mb-0 fw-bold fs-5">₹<?= number_format($order['total_amount'] ?? 0, 2) ?></p></div></div></div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4"><div class="card-header"><strong>Line Items</strong></div><div class="card-body p-0"><table class="table mb-0">
                <thead><tr><th>Item</th><th>Description</th><th>Qty</th><th>Price</th><th>Disc %</th><th>Tax %</th><th>Total</th></tr></thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['item_name']) ?></td>
                        <td><?= htmlspecialchars($item['item_description'] ?? '') ?></td>
                        <td><?= $item['quantity'] ?> <?= htmlspecialchars($item['unit']) ?></td>
                        <td>₹<?= number_format($item['unit_price'], 2) ?></td>
                        <td><?= $item['discount_percent'] ?>%</td>
                        <td><?= $item['tax_percent'] ?>%</td>
                        <td class="fw-bold">₹<?= number_format($item['line_total'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div></div>
            <div class="card mb-4"><div class="card-header"><strong>GRN History</strong></div><div class="card-body p-0">
                <?php if (empty($grnRecords)): ?><div class="p-3 text-muted">No GRN records yet</div>
                <?php else: ?><table class="table mb-0"><thead><tr><th>GRN #</th><th>Date</th><th>Items</th><th>Qty</th><th>Status</th></tr></thead><tbody>
                    <?php foreach ($grnRecords as $g): ?>
                    <tr><td><code><?= htmlspecialchars($g['grn_number']) ?></code></td><td><?= date('M d, Y', strtotime($g['received_at'] ?? $g['created_at'])) ?></td><td><?= $g['total_items'] ?></td><td><?= number_format($g['total_quantity'], 2) ?></td><td><span class="badge bg-<?= $g['status']==='accepted'?'success':($g['status']==='rejected'?'danger':'warning') ?>"><?= ucfirst($g['status']) ?></span></td></tr>
                    <?php endforeach; ?></tbody></table>
                <?php endif; ?>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card"><div class="card-header"><strong>Notes</strong></div><div class="card-body"><p><?= nl2br(htmlspecialchars($order['notes'] ?? '')) ?></p><hr><strong>Terms:</strong><p><?= nl2br(htmlspecialchars($order['terms_conditions'] ?? '')) ?></p></div></div>
        </div>
    </div>
</div>
<?php if (($order['status'] ?? '') === 'approved'): ?>
<div class="modal fade" id="receiveModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Receive Goods (GRN)</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <form id="grnForm" method="POST" action="<?= BASE_URL ?>/admin/purchase-orders/receive/<?= $order['id'] ?? '' ?>">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div class="row">
                <div class="col-md-4"><label>GRN Number</label><input type="text" name="grn_number" class="form-control" value="GRN-<?= date('YmdHis') ?>"></div>
                <div class="col-md-4"><label>Invoice Number</label><input type="text" name="invoice_number" class="form-control"></div>
                <div class="col-md-4"><label>Invoice Date</label><input type="date" name="invoice_date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
                <div class="col-md-4"><label>Transport Name</label><input type="text" name="transport_name" class="form-control"></div>
                <div class="col-md-4"><label>Vehicle Number</label><input type="text" name="vehicle_number" class="form-control"></div>
                <div class="col-md-4"><label>Driver Name</label><input type="text" name="driver_name" class="form-control"></div>
                <div class="col-md-4"><label>Driver Phone</label><input type="text" name="driver_phone" class="form-control"></div>
                <div class="col-md-4"><label>Challan Number</label><input type="text" name="challan_number" class="form-control"></div>
                <div class="col-md-4"><label>Challan Date</label><input type="date" name="challan_date" class="form-control"></div>
            </div>
            <h6 class="mt-3">Item Receipt</h6>
            <table class="table table-sm"><thead><tr><th>Item</th><th>Ordered</th><th>Accepted Qty</th></tr></thead><tbody>
            <?php foreach ($items as $item): ?>
                <tr><td><?= htmlspecialchars($item['item_name']) ?></td><td><?= $item['quantity'] ?></td><td><input type="hidden" name="items_po_id[]" value="<?= $item['id'] ?>"><input type="number" name="items_accepted[]" class="form-control form-control-sm" step="0.01" min="0" max="<?= $item['quantity'] ?>" value="0"></td></tr>
            <?php endforeach; ?>
            </tbody></table>
            <div class="mt-3"><label>Remarks</label><textarea name="remarks" class="form-control" rows="2"></textarea></div>
        </form>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" form="grnForm" class="btn btn-primary"><i class="fas fa-save me-2"></i>Create GRN</button></div>
</div></div></div>
<?php endif; ?>