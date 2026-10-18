<?php
$orders = $orders ?? [];
$stats = $stats ?? ['total'=>0,'pending'=>0,'approved'=>0,'received'=>0,'total_value'=>0];
$filters = $filters ?? ['status'=>''];
$statuses = $statuses ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Purchase Orders</h1>
        <a href="<?= BASE_URL ?>/admin/purchase-orders/create" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Create PO</a>
    </div>

    <div class="row mb-4">
        <div class="col-md-3"><div class="card bg-primary text-white"><div class="card-body"><h3><?= $stats['total'] ?></h3><small>Total POs</small></div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-dark"><div class="card-body"><h3><?= $stats['pending'] ?></h3><small>Pending</small></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white"><div class="card-body"><h3><?= $stats['approved'] ?></h3><small>Approved</small></div></div></div>
        <div class="col-md-3"><div class="card bg-info text-white"><div class="card-body"><h3>₹<?= number_format($stats['total_value'] ?? 0) ?></h3><small>Total Value</small></div></div></div>
    </div>

    <div class="card mb-4"><div class="card-body">
        <form method="GET" class="row">
            <div class="col-md-3"><label>Status</label><select name="status" class="form-select"><option value="">All</option><?php foreach ($statuses as $s) { ?><option value="<?= $s ?>" <?= $filters['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php } ?></select></div>
            <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100">Filter</button></div>
            <div class="col-md-2 d-flex align-items-end"><a href="<?= BASE_URL ?>/admin/purchase-orders" class="btn btn-outline-secondary w-100">Clear</a></div>
        </form>
    </div></div>

    <div class="card"><div class="card-body p-0"><table class="table mb-0">
        <thead><tr><th>PO Number</th><th>Vendor</th><th>Date</th><th>Amount</th><th>Payment</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (empty($orders)): ?><tr><td colspan="7" class="text-center text-muted py-4">No purchase orders found</td></tr>
        <?php else: foreach ($orders as $o): ?>
            <tr>
                <td><code><?= htmlspecialchars($o['order_number']) ?></code></td>
                <td><?= htmlspecialchars($o['vendor_name'] ?? '—') ?></td>
                <td><?= date('M d, Y', strtotime($o['order_date'] ?? $o['created_at'])) ?></td>
                <td>₹<?= number_format($o['total_amount'] ?? 0, 2) ?></td>
                <td><span class="badge bg-<?= $o['payment_status']==='paid'?'success':($o['payment_status']==='partial'?'warning':'secondary') ?>"><?= ucfirst($o['payment_status'] ?? 'unpaid') ?></span></td>
                <td><span class="badge bg-<?= match($o['status']) { 'approved'=>'success', 'received'=>'info', 'cancelled'=>'danger', default=>'warning' } ?>"><?= ucfirst($o['status']) ?></span></td>
                <td><a href="<?= BASE_URL ?>/admin/purchase-orders/<?= $o['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                <?php if ($o['status'] === 'pending'): ?>
                <form method="POST" action="<?= BASE_URL ?>/admin/purchase-orders/approve/<?= $o['id'] ?>" class="d-inline" onsubmit="return confirm('Approve this PO?');"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><button class="btn btn-sm btn-outline-success"><i class="fas fa-check"></i></button></form>
                <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table></div></div>
</div>