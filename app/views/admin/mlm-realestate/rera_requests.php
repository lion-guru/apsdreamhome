<div class="container-fluid py-4">
    <h1 class="h3 mb-4"><i class="fas fa-gavel me-2"></i>RERA Compliance Requests</h1>
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>ID</th><th>User</th><th>Email</th><th>Amount</th><th>Status</th><th>RERA #</th><th>RERA Approved</th><th>Date</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php if (empty($requests ?? [])): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="fas fa-gavel fa-3x text-muted mb-3 style-82835"></i>
                                <h5 class="text-muted">No RERA requests found</h5>
                                <p class="text-muted mb-3">RERA compliance requests from associates will appear here for review.</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($requests as $r): ?>
                        <tr>
                            <td>#<?= $r['id'] ?></td>
                            <td><strong><?= htmlspecialchars($r['user_name'] ?? $r['name'] ?? '') ?></strong></td>
                            <td><?= htmlspecialchars($r['user_email'] ?? $r['email'] ?? '') ?></td>
                            <td>₹<?= number_format((float)$r['deducted_amount'], 2) ?></td>
                            <td><span class="badge bg-<?= $r['status'] === 'approved' ? 'success' : ($r['status'] === 'rejected' ? 'danger' : 'warning') ?>"><?= htmlspecialchars($r['status'] ?? '') ?></span></td>
                            <td><?= htmlspecialchars($r['rera_number'] ?? '-') ?></td>
                            <td><span class="badge bg-<?= ($r['is_rera_approved'] ?? 0) ? 'success' : 'secondary' ?>"><?= ($r['is_rera_approved'] ?? 0) ? 'Yes' : 'No' ?></span></td>
                            <td><?= htmlspecialchars($r['created_at'] ?? '') ?></td>
                            <td>
                                <?php if ($r['status'] === 'pending'): ?>
                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveModal" data-id="<?= $r['id'] ?>"><i class="fas fa-check"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="approveModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="<?= BASE_URL ?>/admin/mlm-realestate/rera/approve">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <div class="modal-header"><h5 class="modal-title">Approve RERA</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" name="id" id="reraId" value="">
            <div class="mb-3"><label class="form-label">RERA Number</label><input type="text" name="rera_number" class="form-control" placeholder="e.g., UP/RERA/2026/12345" required></div>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-success">Approve</button></div>
    </form>
</div></div></div>
<script>
document.getElementById('approveModal')?.addEventListener('show.bs.modal', function(e) {
    document.getElementById('reraId').value = e.relatedTarget.getAttribute('data-id');
});
</script>