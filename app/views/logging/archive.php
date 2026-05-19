<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= htmlspecialchars($pageTitle ?? 'Log Archives') ?></h1>
        <button class="btn btn-outline-primary" onclick="createArchive()"><i class="fas fa-archive me-1"></i> Create Archive</button>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0">Archived Logs</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="table-responsive"><table class="table table-hover align-middle mb-0 table-responsive">
                    <thead class="table-light">
                        <tr><th>Archive Name</th><th>Size</th><th>Entries</th><th>Date Range</th><th>Created</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($archives)): ?>
                            <?php foreach ($archives as $a): ?>
                                <tr>
                                    <td><?= htmlspecialchars($a['name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($a['size'] ?? '0 B') ?></td>
                                    <td><?= (int)($a['entries'] ?? 0) ?></td>
                                    <td><?= htmlspecialchars(($a['date_from'] ?? '') . ' - ' . ($a['date_to'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars($a['created_at'] ?? '') ?></td>
                                    <td>
                                        <a href="<?= $base ?? BASE_URL ?>/logging/archive/download?id=<?= (int)($a['id'] ?? 0) ?>" class="btn btn-sm btn-outline-success"><i class="fas fa-download"></i></a>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteArchive(<?= (int)($a['id'] ?? 0) ?>)"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted py-3">No archives found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
</div>

<script>
function createArchive() {
    fetch('<?= $base ?? BASE_URL ?>/logging/archive/create', { method: 'POST' })
        .then(r => r.json()).then(d => { if (d.success) location.reload(); else alert(d.message ?? 'Failed'); });
}
function deleteArchive(id) {
    if (!confirm('Delete this archive?')) return;
    fetch('<?= $base ?? BASE_URL ?>/logging/archive/delete', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id})
    }).then(r => r.json()).then(d => { if (d.success) location.reload(); });
}
</script>