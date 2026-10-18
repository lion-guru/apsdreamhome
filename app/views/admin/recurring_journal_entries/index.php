<?php
$rjes = $rjes ?? [];
$stats = $stats ?? ['total'=>0,'active'=>0,'due_today'=>0];
$filters = $filters ?? ['status'=>'active'];
$statuses = $statuses ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Recurring Journal Entries</h1>
        <a href="<?= BASE_URL ?>/admin/recurring-journal-entries/create" class="btn btn-primary"><i class="fas fa-plus me-2"></i>New Template</a>
        <form method="POST" action="<?= BASE_URL ?>/admin/recurring-journal-entries/process-due" class="d-inline" onsubmit="return confirm('Post all due recurring journal entries?');">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <button type="button" class="btn btn-outline-info" onclick="processDue()"><i class="fas fa-clock me-2"></i>Process Due</button>
            <span id="due-badge" class="badge bg-warning text-dark ms-2"><?= $stats['due_today'] ?? 0 ?> due today</span>
        </form>
    </div>

    <div class="row mb-4">
        <div class="col-md-3"><div class="card bg-primary text-white"><div class="card-body"><h3><?= $stats['total'] ?? 0 ?></h3><small>Templates</small></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white"><div class="card-body"><h3><?= $stats['active'] ?? 0 ?></h3><small>Active</small></div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-dark"><div class="card-body"><h3><?= $stats['due_today'] ?? 0 ?></h3><small>Due Today</small></div></div></div>
    </div>

    <div class="card mb-4"><div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-3"><label>Status</label><select name="status" class="form-select"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
            <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100">Filter</button></div>
            <div class="col-md-2 d-flex align-items-end"><a href="<?= BASE_URL ?>/admin/recurring-journal-entries" class="btn btn-outline-secondary w-100">Clear</a></div>
        </form>
    </div></div>

    <div class="card"><div class="card-body p-0"><table class="table mb-0">
        <thead><tr><th>Template</th><th>Frequency</th><th>Next Execution</th><th>Last Executed</th><th>Count</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (empty($rjes)): ?><tr><td colspan="7" class="text-center text-muted py-4">No recurring journal entries</td></tr>
        <?php else: foreach ($rjes as $r): ?>
            <tr>
                <td><strong><?= htmlspecialchars($r['template_name']) ?></strong><br><small class="text-muted"><?= htmlspecialchars($r['description'] ?? '') ?></small></td>
                <td><?= ucfirst($r['frequency']) ?></td>
                <td><?= $r['next_execution_date'] ? date('M d, Y', strtotime($r['next_execution_date'])) : '—' ?></td>
                <td><?= $r['last_execution_date'] ? date('M d, Y', strtotime($r['last_execution_date'])) : '—' ?></td>
                <td><?= (int)($r['execution_count'] ?? 0) ?></td>
                <td><span class="badge bg-<?= $r['is_active'] ? 'success' : 'secondary' ?>"><?= $r['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                <td>
                    <a href="<?= BASE_URL ?>/admin/recurring-journal-entries/<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                    <?php if ($r['is_active']): ?>
                    <button type="button" class="btn btn-sm btn-success" onclick="executeJe(<?= $r['id'] ?>)"><i class="fas fa-play"></i> Execute Now</button>
                    <?php endif; ?>
                    <form method="POST" action="<?= BASE_URL ?>/admin/recurring-journal-entries/delete/<?= $r['id'] ?>" class="d-inline" onsubmit="return confirm('Delete this template?');"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table></div></div>
</div>

<script>
function processDue() {
    if (!confirm('Post all due recurring journal entries now?')) return;
    fetch('<?= BASE_URL ?>/admin/recurring-journal-entries/process-due', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded', 'X-Requested-With':'XMLHttpRequest'},
        body: 'csrf_token=' + encodeURIComponent('<?= $_SESSION['csrf_token'] ?? '' ?>')
    }).then(r => r.json()).then(d => {
        if (d.success) { alert('Posted ' + d.result.processed + ' entry(ies)'); location.reload(); }
        else { alert(d.error || 'Failed'); }
    }).catch(() => alert('Error'));
}
function executeJe(id) {
    if (!confirm('Post this recurring journal entry now?')) return;
    fetch('<?= BASE_URL ?>/admin/recurring-journal-entries/execute/' + id, {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded', 'X-Requested-With':'XMLHttpRequest'},
        body: 'csrf_token=' + encodeURIComponent('<?= $_SESSION['csrf_token'] ?? '' ?>')
    }).then(r => r.json()).then(d => {
        if (d.success) { alert('Journal entry #' + d.journal_entry_id + ' posted'); location.reload(); }
        else { alert(d.error || 'Failed'); }
    }).catch(() => alert('Error'));
}
</script>
