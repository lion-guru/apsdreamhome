<?php
$rje = $rje ?? [];
$lines = $lines ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Recurring JE: <?= htmlspecialchars($rje['template_name'] ?? '') ?></h1>
        <a href="<?= BASE_URL ?>/admin/recurring-journal-entries" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>

    <div class="row mb-4">
        <div class="col-md-3"><div class="card"><div class="card-body"><h5>Frequency</h5><p class="mb-0"><?= ucfirst($rje['frequency'] ?? '') ?></p></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h5>Next Execution</h5><p class="mb-0"><?= $rje['next_execution_date'] ? date('M d, Y', strtotime($rje['next_execution_date'])) : '—' ?></p></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h5>Last Executed</h5><p class="mb-0"><?= $rje['last_execution_date'] ? date('M d, Y', strtotime($rje['last_execution_date'])) : '—' ?></p></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h5>Executions</h5><p class="mb-0"><?= (int)($rje['execution_count'] ?? 0) . ($rje['max_executions'] ? ' / ' . $rje['max_executions'] : '') ?></p></div></div></div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4"><div class="card"><div class="card-body"><h5>Status</h5><span class="badge bg-<?= $rje['is_active'] ? 'success' : 'secondary' ?>"><?= $rje['is_active'] ? 'Active' : 'Inactive' ?></span></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><h5>Entry Type</h5><p class="mb-0"><?= ucfirst($rje['entry_type'] ?? '') ?></p></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><h5>Start / End Date</h5><p class="mb-0"><?= date('M d, Y', strtotime($rje['start_date'])) ?> <?= $rje['end_date'] ? 'to ' . date('M d, Y', strtotime($rje['end_date'])) : '—' ?></p></div></div></div>
    </div>

    <?php if ($rje['description']): ?>
    <div class="card mb-4"><div class="card-body"><h5>Description</h5><p class="mb-0"><?= htmlspecialchars($rje['description']) ?></p></div></div></div>
    <?php endif; ?>

    <div class="card mb-4"><div class="card-body">
        <h5>Journal Lines (<?= count($lines) ?>)</h5>
        <?php if (empty($lines)): ?>
        <p class="text-muted">No lines defined.</p>
        <?php else: ?>
        <table class="table">
            <thead><tr><th>#</th><th>Account</th><th>Debit</th><th>Credit</th><th>Description</th></tr></thead>
            <tbody>
            <?php $tdr = 0; $tcr = 0; foreach ($lines as $i => $l): $tdr += (float)($l['debit_amount'] ?? 0); $tcr += (float)($l['credit_amount'] ?? 0); ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($l['account_id']) ?></td>
                <td>₹<?= number_format($l['debit_amount'] ?? 0, 2) ?></td>
                <td>₹<?= number_format($l['credit_amount'] ?? 0, 2) ?></td>
                <td><?= htmlspecialchars($l['description'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="fw-bold"><td colspan="2">Total</td><td>₹<?= number_format($tdr, 2) ?></td><td>₹<?= number_format($tcr, 2) ?></td><td></td></tr>
            </tbody>
        </table>
        <?php endif; ?>
    </div></div>

    <div class="row">
        <div class="col-md-6">
            <?php if ($rje['is_active']): ?>
            <button type="button" class="btn btn-success" onclick="executeJe(<?= $rje['id'] ?>)"><i class="fas fa-play me-2"></i>Execute Now (Post JE)</button>
            <?php endif; ?>
            <a href="?status=<?= $_GET['status'] ?? 'active' ?>" class="btn btn-outline-secondary">Back to List</a>
        </div>
        <div class="col-md-6 d-flex gap-2">
            <?php if ($rje['is_active']): ?>
            <form method="POST" action="<?= BASE_URL ?>/admin/recurring-journal-entries/delete/<?= $rje['id'] ?>" class="d-inline" onsubmit="return confirm('Deactivate/delete this template?');"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><button class="btn btn-outline-danger"><i class="fas fa-trash"></i> Delete</button></form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function executeJe(id) {
    if (!confirm('Post this recurring journal entry as a real journal entry now?')) return;
    fetch('<?= BASE_URL ?>/admin/recurring-journal-entries/execute/' + id, {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded', 'X-Requested-With':'XMLHttpRequest'},
        body: 'csrf_token=' + encodeURIComponent('<?= $_SESSION['csrf_token'] ?? '' ?>')
    }).then(r => r.json()).then(d => {
        if (d.success) { alert('Journal entry #' + d.journal_entry_id + ' posted successfully'); location.reload(); }
        else { alert(d.error || 'Failed'); }
    }).catch(() => alert('Error'));
}
</script>
