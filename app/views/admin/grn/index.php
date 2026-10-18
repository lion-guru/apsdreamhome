<?php
$grns = $grns ?? [];
$stats = $stats ?? ['total_amount'=>0,'by_status'=>[]];
$filters = $filters ?? ['status'=>''];
$statuses = $statuses ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Goods Receipt Notes (GRN)</h1>
        <a href="<?= BASE_URL ?>/admin/grn/create" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Create GRN</a>
    </div>

    <div class="row mb-4">
        <div class="col-md-3"><div class="card bg-primary text-white"><div class="card-body"><h3><?= count($grns) ?></h3><small>Total GRNs</small></div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-dark"><div class="card-body"><h3><?= number_format($stats['total_amount'] ?? 0, 2) ?></h3><small>Total Value (₹)</small></div></div></div>
        <div class="col-md-3"><div class="card bg-info text-white"><div class="card-body"><h3><?= $stats['by_status'] ? count(array_filter($stats['by_status'], fn($s)=>$s['status']==='accepted')) : 0 ?></h3><small>Accepted</small></div></div></div>
        <div class="col-md-3"><div class="card bg-danger text-white"><div class="card-body"><h3><?= $stats['by_status'] ? count(array_filter($stats['by_status'], fn($s)=>$s['status']==='rejected')) : 0 ?></h3><small>Rejected</small></div></div></div>
    </div>

    <div class="card mb-4"><div class="card-body">
        <form method="GET" class="row">
            <div class="col-md-3"><label>Status</label><select name="status" class="form-select"><option value="">All</option><?php foreach ($statuses as $s) { ?><option value="<?= $s ?>" <?= $filters['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php } ?></select></div>
            <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100">Filter</button></div>
            <div class="col-md-2 d-flex align-items-end"><a href="<?= BASE_URL ?>/admin/grn" class="btn btn-outline-secondary w-100">Clear</a></div>
        </form>
    </div></div>

    <div class="card"><div class="card-body p-0"><table class="table mb-0">
        <thead><tr><th>GRN Number</th><th>PO Number</th><th>Vendor</th><th>Amount</th><th>Status</th><th>Received</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (empty($grns)): ?><tr><td colspan="7" class="text-center text-muted py-4">No GRNs found</td></tr>
        <?php else: foreach ($grns as $g): ?>
            <tr>
                <td><code><?= htmlspecialchars($g['grn_number']) ?></code></td>
                <td><?= htmlspecialchars($g['order_number'] ?? '—') ?></td>
                <td><?= htmlspecialchars($g['vendor_name'] ?? '—') ?></td>
                <td>₹<?= number_format($g['total_amount'] ?? 0, 2) ?></td>
                <td><span class="badge bg-<?= match($g['status']) { 'accepted'=>'success', 'verified'=>'info', 'rejected'=>'danger', 'partial'=>'warning', default=>'secondary' } ?>"><?= ucfirst($g['status']) ?></span></td>
                <td><?= $g['received_at'] ? date('M d, Y', strtotime($g['received_at'])) : '—' ?></td>
                <td>
                    <a href="<?= BASE_URL ?>/admin/grn/<?= $g['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                    <?php if ($g['status'] === 'draft'): ?>
                    <form method="POST" action="<?= BASE_URL ?>/admin/grn/delete/<?= $g['id'] ?>" class="d-inline" onsubmit="return confirm('Delete this GRN?');"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form>
                    <?php endif; ?>
                    <?php if (in_array($g['status'], ['draft','verified'])): ?>
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="changeStatus(<?= $g['id'] ?>, 'accepted')"><i class="fas fa-check"></i> Accept</button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="changeStatus(<?= $g['id'] ?>, 'rejected')"><i class="fas fa-times"></i> Reject</button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table></div></div>
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
