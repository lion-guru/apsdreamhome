<?php $page_title = 'Referrals Management'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-share-alt me-2"></i>Referrals Management</h2>
        <a href="<?= BASE_URL ?>/admin/referrals/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i>New Referral</a>
    </div>
    <div class="row mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3><?= $stats['total'] ?></h3><small class="text-muted">Total</small></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3 class="text-warning"><?= $stats['pending'] ?></h3><small class="text-muted">Pending</small></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3 class="text-success"><?= $stats['converted'] ?></h3><small class="text-muted">Converted</small></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3 class="text-danger"><?= $stats['rejected'] ?></h3><small class="text-muted">Rejected</small></div></div></div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($referrals)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-share-alt fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No referrals yet</h5>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>#</th><th>Referrer</th><th>Referred Email</th><th>Code</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($referrals as $r): ?>
                            <tr>
                                <td><?= $r['id'] ?></td>
                                <td><?= htmlspecialchars($r['referrer_name'] ?? 'Unknown') ?></td>
                                <td><?= htmlspecialchars($r['referred_email']) ?></td>
                                <td><code><?= htmlspecialchars($r['referral_code'] ?? '') ?></code></td>
                                <td><span class="badge bg-<?= ($r['status'] ?? 'pending')==='converted'?'success':(($r['status'] ?? 'pending')==='rejected'?'danger':'warning') ?>"><?= ucfirst($r['status'] ?? 'pending') ?></span></td>
                                <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                                <td>
                                    <?php if (($r['status'] ?? 'pending') === 'pending'): ?>
                                        <a href="<?= BASE_URL ?>/admin/referrals/<?= $r['id'] ?>/approve" class="btn btn-sm btn-success" onclick="return confirm('Approve this referral?')"><i class="fas fa-check"></i></a>
                                        <a href="<?= BASE_URL ?>/admin/referrals/<?= $r['id'] ?>/reject" class="btn btn-sm btn-danger" onclick="return confirm('Reject this referral?')"><i class="fas fa-times"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
