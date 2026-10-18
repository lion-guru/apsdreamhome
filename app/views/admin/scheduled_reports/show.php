<?php
$report = $report ?? [];
$parameters = $parameters ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= htmlspecialchars($report['title'] ?? '') ?></h1>
        <a href="<?= BASE_URL ?>/admin/scheduled-reports" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>

    <div class="row mb-4">
        <div class="col-md-3"><div class="card"><div class="card-body"><h5>Report Type</h5><p class="mb-0"><?= ucfirst($report['report_type'] ?? '') ?></p></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h5>Frequency</h5><p class="mb-0"><?= ucfirst($report['frequency'] ?? '') ?></p></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h5>Format</h5><p class="mb-0"><?= strtoupper($report['format'] ?? '') ?></p></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h5>Status</h5><span class="badge bg-<?= ($report['status'] ?? '')==='active'?'success':'secondary' ?>"><?= ucfirst($report['status'] ?? '') ?></span></div></div></div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4"><div class="card"><div class="card-body"><h5>Next Run</h5><p class="mb-0"><?= $report['next_run'] ? date('M d, Y h:i A', strtotime($report['next_run'])) : '—' ?></p></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><h5>Last Run</h5><p class="mb-0"><?= $report['last_run'] ? date('M d, Y h:i A', strtotime($report['last_run'])) : '—' ?></p></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><h5>Recipients</h5><p class="mb-0"><?= htmlspecialchars($report['recipients'] ?? '—') ?></p></div></div></div>
    </div>

    <?php if ($report['notes']): ?>
    <div class="card mb-4"><div class="card-body"><h5>Notes</h5><p class="mb-0"><?= htmlspecialchars($report['notes']) ?></p></div></div></div>
    <?php endif; ?>

    <?php if (!empty($parameters)): ?>
    <div class="card mb-4"><div class="card-body"><h5>Parameters</h5>
        <?php foreach ($parameters as $k=>$v): ?><p><strong><?= htmlspecialchars($k) ?>:</strong> <?= htmlspecialchars((string)$v) ?></p><?php endforeach; ?>
    </div></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6 d-flex gap-2">
            <button type="button" class="btn btn-success" onclick="runNow(<?= $report['id'] ?>)"><i class="fas fa-play me-2"></i>Run Now</button>
            <form method="POST" action="<?= BASE_URL ?>/admin/scheduled-reports/delete/<?= $report['id'] ?>" class="d-inline" onsubmit="return confirm('Delete this schedule?');"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><button class="btn btn-outline-danger"><i class="fas fa-trash"></i> Delete</button></form>
        </div>
    </div>
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
