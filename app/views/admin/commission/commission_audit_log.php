<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="fas fa-book"></i> MLM Commission Ledger (Legacy Audit)</h4>
        <a href="/admin/commission" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <div class="card aps-cp-card">
        <div class="card-body p-0">
            <div class="table-responsive"><table class="table table-striped mb-0">
                <thead><tr><th>#</th><th>Commission ID</th><th>Action</th><th>Details</th><th>Date</th></tr></thead>
                <tbody>
                    <?php foreach ($ledger ?? [] as $l): ?>
                    <tr>
                        <td><?= $l['id'] ?></td>
                        <td>#<?= (int)$l['commission_id'] ?></td>
                        <td><span class="badge bg-<?= $l['action']=='paid'?'success':($l['action']=='cancelled'?'danger':'info') ?>"><?= $l['action'] ?></span></td>
                        <td><?= htmlspecialchars($l['details'] ?? '-') ?></td>
                        <td><?= date('d-m-Y H:i', strtotime($l['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
    </div>
</div>
