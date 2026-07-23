<?php $page_title = $page_title ?? 'KYC Verification'; $requests = $requests ?? []; $stats = $stats ?? []; ?>
<div class="container-fluid px-4 py-4">
    <h4 class="fw-bold mb-4"><i class="fas fa-id-card me-2 text-primary"></i>KYC Verification</h4>
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm text-center p-3" style="border-radius:14px"><div style="font-size:28px;font-weight:800;color:#667eea"><?= $stats['total'] ?? 0 ?></div><small class="text-muted">Total</small></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm text-center p-3" style="border-radius:14px"><div style="font-size:28px;font-weight:800;color:#f59e0b"><?= $stats['pending'] ?? 0 ?></div><small class="text-muted">Pending</small></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm text-center p-3" style="border-radius:14px"><div style="font-size:28px;font-weight:800;color:#22c55e"><?= $stats['verified'] ?? 0 ?></div><small class="text-muted">Verified</small></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm text-center p-3" style="border-radius:14px"><div style="font-size:28px;font-weight:800;color:#ef4444"><?= $stats['rejected'] ?? 0 ?></div><small class="text-muted">Rejected</small></div></div>
    </div>
    <div class="card border-0 shadow-sm" style="border-radius:14px"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th>ID</th><th>User</th><th>PAN</th><th>Aadhaar</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead><tbody>
    <?php if (empty($requests)): ?><tr><td colspan="7" class="text-center py-4 text-muted">No KYC requests</td></tr>
    <?php else: foreach ($requests as $r): ?><tr>
        <td>#<?= $r['id'] ?></td>
        <td><?= htmlspecialchars($r['user_name'] ?? '-') ?></td>
        <td><code><?= htmlspecialchars(substr($r['pan_number'] ?? '',0,5).'****') ?></code></td>
        <td><code>****<?= htmlspecialchars(substr($r['aadhaar_number'] ?? '',-4)) ?></code></td>
        <td><span class="badge bg-<?= ($r['status']??'')==='verified'?'success':(($r['status']??'')==='rejected'?'danger':'warning') ?>"><?= ucfirst($r['status'] ?? 'pending') ?></span></td>
        <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
        <td>
            <?php if (($r['status']??'')==='pending'): ?>
            <form method="POST" action="<?= BASE_URL ?>/admin/kyc/<?= $r['id'] ?>/approve" class="d-inline"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><button class="btn btn-sm btn-success"><i class="fas fa-check"></i></button></form>
            <form method="POST" action="<?= BASE_URL ?>/admin/kyc/<?= $r['id'] ?>/reject" class="d-inline"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><input type="hidden" name="rejection_reason" value="Rejected by admin"><button class="btn btn-sm btn-danger"><i class="fas fa-times"></i></button></form>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/admin/kyc/<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
        </td>
    </tr><?php endforeach; endif; ?>
    </tbody></table></div></div></div>
</div>
