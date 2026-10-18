<?php
$reports = $reports ?? [];
$stats = $stats ?? ['total'=>0,'active'=>0,'due'=>0];
$filters = $filters ?? ['status'=>''];
$statuses = $statuses ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Scheduled Reports</h1>
        <a href="<?= BASE_URL ?>/admin/scheduled-reports/create" class="btn btn-primary"><i class="fas fa-plus me-2"></i>New Schedule</a>
    </div>

    <div class="row mb-4">
        <div class="col-md-3"><div class="card bg-primary text-white"><div class="card-body"><h3><?= $stats['total'] ?? 0 ?></h3><small>Total</small></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white"><div class="card-body"><h3><?= $stats['active'] ?? 0 ?></h3><small>Active</small></div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-dark"><div class="card-body"><h3><?= $stats['due'] ?? 0 ?></h3><small>Due Now</small></div></div></div>
    </div>

    <div class="card mb-4"><div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-3"><label>Status</label><select name="status" class="form-select"><option value="">All</option><?php foreach ($statuses as $s): ?><option value="<?= $s ?>" <?= $filters['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100">Filter</button></div>
            <div class="col-md-2 d-flex align-items-end"><a href="<?= BASE_URL ?>/admin/scheduled-reports" class="btn btn-outline-secondary w-100">Clear</a></div>
        </form>
    </div></div>

    <div class="card"><div class="card-body p-0"><table class="table mb-0">
        <thead><tr><th>Title</th><th>Report Type</th><th>Frequency</th><th>Format</th><th>Next Run</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (empty($reports)): ?><tr><td colspan="7" class="text-center text-muted py-4">No scheduled reports</td></tr>
        <?php else: foreach ($reports as $r): ?>
            <tr>
                <td><strong><?= htmlspecialchars($r['title']) ?></strong><?php if ($r['notes']): ?><br><small class="text-muted"><?= htmlspecialchars($r['notes']) ?></small><?php endif; ?></td>
                <td><?= ucfirst($r['report_type']) ?></td>
                <td><?= ucfirst($r['frequency']) ?></td>
                <td><?= strtoupper($r['format']) ?></td>
                <td><?= $r['next_run'] ? date('M d, Y h:i A', strtotime($r['next_run'])) : '—' ?></td>
                <td><span class="badge bg-<?= $r['status']==='active'?'success':'secondary' ?>"><?= ucfirst($r['status']) ?></span></td>
                <td>
                    <a href="<?= BASE_URL ?>/admin/scheduled-reports/<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                    <button type="button" class="btn btn-sm btn-success" onclick="runNow(<?= $r['id'] ?>)"><i class="fas fa-play"></i></button>
                    <form method="POST" action="<?= BASE_URL ?>/admin/scheduled-reports/delete/<?= $r['id'] ?>" class="d-inline" onsubmit="return confirm('Delete this schedule?');"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table></div></div>
</div>

<script>
function runNow(id) {
    if (!confirm('Queue this report for immediate run?')) return;
    fetch('<?= BASE_URL ?>/admin/scheduled-reports/' + id + '/run', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded', 'X-Requested-With':'XMLHttpRequest'},
        body: 'csrf_token=' + encodeURIComponent('<?= $_SESSION['csrf_token'] ?? '' ?>')
    }).then(r => r.json()).then(d => {
        if (d.success) { alert(d.message); location.reload(); } else { alert(d.error || 'Failed'); }
    }).catch(() => alert('Error'));
}
</script>
