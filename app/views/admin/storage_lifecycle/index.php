<?php
$rules = $rules ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Storage Lifecycle Rules</h1>
            <small class="text-muted">Auto-delete or archive files older than N days by prefix.</small>
        </div>
        <button type="button" class="btn btn-success" onclick="runRules()"><i class="fas fa-play me-2"></i>Run Now</button>
    </div>

    <div class="card mb-4"><div class="card-body">
        <form onsubmit="return addRule(event)" class="row g-2 align-items-end">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div class="col-md-3"><label class="form-label small">Prefix</label><input type="text" name="prefix" class="form-control" placeholder="backups/" required></div>
            <div class="col-md-2"><label class="form-label small">Action</label><select name="action" class="form-select"><option value="delete">Delete</option><option value="archive">Archive</option><option value="warn">Warn</option></select></div>
            <div class="col-md-2"><label class="form-label small">Days</label><input type="number" name="days" class="form-control" min="1" value="90"></div>
            <div class="col-md-2"><button class="btn btn-primary w-100">Add Rule</button></div>
        </form>
    </div></div>

    <div class="card"><div class="card-body p-0">
        <?php if (empty($rules)): ?>
            <div class="text-center text-muted py-5">No lifecycle rules. Add one above.</div>
        <?php else: ?>
        <table class="table mb-0">
            <thead><tr><th>Prefix</th><th>Action</th><th>Days</th><th>Status</th><th>Last Run</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($rules as $r): ?>
                <tr>
                    <td><code><?= htmlspecialchars($r['prefix']) ?></code></td>
                    <td><span class="badge bg-<?= $r['action'] === 'delete' ? 'danger' : ($r['action'] === 'archive' ? 'info' : 'warning') ?>"><?= ucfirst($r['action']) ?></span></td>
                    <td><?= (int)$r['days'] ?></td>
                    <td><span class="badge bg-<?= $r['is_active'] ? 'success' : 'secondary' ?>"><?= $r['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                    <td><?= $r['last_run'] ? date('M d, Y h:i A', strtotime($r['last_run'])) : '—' ?></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-<?= $r['is_active'] ? 'warning' : 'success' ?>" onclick="toggleRule(<?= $r['id'] ?>)"><?= $r['is_active'] ? 'Disable' : 'Enable' ?></button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteRule(<?= $r['id'] ?>)"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div></div>
</div>

<script>
function addRule(e) {
    e.preventDefault();
    const f = e.target;
    const body = new URLSearchParams(new FormData(f));
    fetch('<?= BASE_URL . '/admin/storage-lifecycle/store' ?>', {method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body})
        .then(r=>r.json()).then(d=>{ if(d.success) location.reload(); else alert(d.error||'Failed'); });
    return false;
}
function toggleRule(id) {
    fetch('<?= BASE_URL . '/admin/storage-lifecycle/toggle/' ?>'+id, {method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(r=>r.json()).then(()=>location.reload());
}
function deleteRule(id) {
    if(!confirm('Delete this rule?')) return;
    fetch('<?= BASE_URL . '/admin/storage-lifecycle/delete/' ?>'+id, {method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(r=>r.json()).then(()=>location.reload());
}
function runRules() {
    if(!confirm('Run all active lifecycle rules now?')) return;
    fetch('<?= BASE_URL . '/admin/storage-lifecycle/run' ?>', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'}, body:'csrf_token=<?=$_SESSION['csrf_token']??''?>'})
        .then(r=>r.json()).then(d=>{ alert(d.message||d.error); });
}
</script>
