<?php
$grn = $grn ?? [];
$items = $items ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>GRN: <?= htmlspecialchars($grn['grn_number'] ?? '') ?></h1>
        <a href="<?= BASE_URL ?>/admin/grn" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back to GRNs</a>
    </div>

    <div class="row mb-4">
        <div class="col-md-3"><div class="card"><div class="card-body"><h5>GRN Number</h5><p class="mb-0"><code><?= htmlspecialchars($grn['grn_number'] ?? '') ?></code></p></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h5>PO Number</h5><p class="mb-0"><?= htmlspecialchars($grn['order_number'] ?? '—') ?></p></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h5>Vendor</h5><p class="mb-0"><?= htmlspecialchars($grn['vendor_name'] ?? '—') ?></p></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h5>Total Amount</h5><p class="mb-0">₹<?= number_format($grn['total_amount'] ?? 0, 2) ?></p></div></div></div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4"><div class="card"><div class="card-body"><h5>Status</h5><span class="badge bg-<?= match($grn['status']) { 'accepted'=>'success', 'verified'=>'info', 'rejected'=>'danger', 'partial'=>'warning', default=>'secondary' } ?>"><?= ucfirst($grn['status']) ?></span></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><h5>Received At</h5><p class="mb-0"><?= $grn['received_at'] ? date('M d, Y h:i A', strtotime($grn['received_at'])) : '—' ?></p></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><h5>Received By</h5><p class="mb-0">#<?= $grn['received_by'] ?? '—' ?></p></div></div></div>
    </div>

    <?php if (!empty($grn['invoice_number']) || !empty($grn['challan_number'])): ?>
    <div class="card mb-4"><div class="card-body"><h5>Delivery Details</h5>
        <?php if ($grn['invoice_number']): ?><p><strong>Invoice:</strong> <?= htmlspecialchars($grn['invoice_number']) ?> (<?= $grn['invoice_date'] ?? '—' ?>)</p><?php endif; ?>
        <?php if ($grn['challan_number']): ?><p><strong>Challan:</strong> <?= htmlspecialchars($grn['challan_number']) ?> (<?= $grn['challan_date'] ?? '—' ?>)</p><?php endif; ?>
        <?php if ($grn['transport_name']): ?><p><strong>Transporter:</strong> <?= htmlspecialchars($grn['transport_name']) ?></p><?php endif; ?>
        <?php if ($grn['vehicle_number']): ?><p><strong>Vehicle:</strong> <?= htmlspecialchars($grn['vehicle_number']) ?></p><?php endif; ?>
        <?php if ($grn['driver_name']): ?><p><strong>Driver:</strong> <?= htmlspecialchars($grn['driver_name']) ?> (<?= htmlspecialchars($grn['driver_phone'] ?? '') ?>)</p><?php endif; ?>
        <?php if ($grn['remarks']): ?><p><strong>Remarks:</strong> <?= htmlspecialchars($grn['remarks']) ?></p><?php endif; ?>
    </div></div>
    <?php endif; ?>

    <div class="card mb-4"><div class="card-body">
        <h5>Items (<?= count($items) ?>)</h5>
        <?php if (empty($items)): ?>
        <p class="text-muted">No items recorded.</p>
        <?php else: ?>
        <table class="table">
            <thead><tr><th>Item</th><th>Unit</th><th>Ordered</th><th>Received</th><th>Accepted</th><th>Rejected</th><th>Unit Price</th><th>Line Total</th><th>Quality Remarks</th></tr></thead>
            <tbody>
            <?php foreach ($items as $it): ?>
            <tr>
                <td><?= htmlspecialchars($it['item_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($it['unit'] ?? 'Nos') ?></td>
                <td><?= number_format($it['ordered_qty'] ?? 0, 2) ?></td>
                <td><?= number_format($it['received_qty'] ?? 0, 2) ?></td>
                <td><?= number_format($it['accepted_qty'] ?? 0, 2) ?></td>
                <td><?= number_format($it['rejected_qty'] ?? 0, 2) ?></td>
                <td>₹<?= number_format($it['unit_price'] ?? 0, 2) ?></td>
                <td>₹<?= number_format($it['line_total'] ?? 0, 2) ?></td>
                <td><?= htmlspecialchars($it['quality_remarks'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div></div>

    <?php if ($grn['status'] === 'draft'): ?>
    <div class="row">
        <div class="col-md-6">
            <form method="POST" action="<?= BASE_URL ?>/admin/grn/delete/<?= $grn['id'] ?>" class="d-inline" onsubmit="return confirm('Delete this GRN?');">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <button class="btn btn-danger"><i class="fas fa-trash"></i> Delete GRN</button>
            </form>
        </div>
        <div class="col-md-6 d-flex gap-2">
            <button type="button" class="btn btn-success" onclick="changeStatus('<?= $grn['id'] ?>', 'verified')"><i class="fas fa-check-circle"></i> Mark Verified</button>
            <button type="button" class="btn btn-outline-danger" onclick="changeStatus('<?= $grn['id'] ?>', 'rejected')"><i class="fas fa-times-circle"></i> Reject</button>
        </div>
    </div>
    <?php elseif ($grn['status'] === 'verified'): ?>
    <div class="row">
        <div class="col-md-6 d-flex gap-2">
            <button type="button" class="btn btn-success" onclick="changeStatus('<?= $grn['id'] ?>', 'accepted')"><i class="fas fa-check-double"></i> Accept GRN</button>
            <button type="button" class="btn btn-outline-danger" onclick="changeStatus('<?= $grn['id'] ?>', 'rejected')"><i class="fas fa-times-circle"></i> Reject</button>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function changeStatus(grnId, status) {
    if (!confirm('Mark GRN #' + grnId + ' as ' + status + '?')) return;
    fetch('<?= BASE_URL ?>/admin/grn/' + grnId + '/status', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded', 'X-Requested-With':'XMLHttpRequest'},
        body: 'csrf_token=' + encodeURIComponent('<?= $_SESSION['csrf_token'] ?? '' ?>') + '&status=' + encodeURIComponent(status)
    }).then(r => r.json()).then(d => {
        if (d.success) { location.reload(); } else { alert(d.error || 'Failed'); }
    }).catch(() => alert('Error'));
}
</script>
