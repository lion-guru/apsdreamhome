<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="fas fa-book"></i> MLM Commission Ledger (Legacy Audit)</h4>
        <a href="<?= BASE_URL ?>/admin/commission" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <div class="card aps-cp-card">
        <div class="card-body p-0">
            <div class="table-responsive"><table class="table table-striped mb-0">
                <thead><tr><th scope="col">#</th><th scope="col">Commission ID</th><th scope="col">Action</th><th scope="col">Details</th><th scope="col">Date</th></tr></thead>
                <tbody>
                    <?php if (empty($ledger ?? [])): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <i class="fas fa-book fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No audit log entries found</h5>
                            <p class="text-muted mb-3">Commission audit entries will appear here as transactions are processed.</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($ledger ?? [] as $l): ?>
                    <tr>
                        <td><?= $l['id'] ?></td>
                        <td>#<?= (int)$l['commission_id'] ?></td>
                        <td><span class="badge bg-<?= $l['action']=='paid'?'success':($l['action']=='cancelled'?'danger':'info') ?>"><?= $l['action'] ?></span></td>
                        <td><?= htmlspecialchars($l['details'] ?? '-') ?></td>
                        <td><?= date('d-m-Y H:i', strtotime($l['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table></div>
        </div>
    </div>
</div>
