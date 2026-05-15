<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-share-alt me-2"></i><?= ($page_title ?? 'Referral Program') ?></h4>
        <a href="<?= ($base ?? BASE_URL) ?>saas/tools/referrals/generate" class="btn btn-success btn-sm"><i class="fas fa-link me-1"></i>Generate Referral Link</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= count($referrals ?? []) ?></h3>
                    <small>Total Referrals</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= count(array_filter($referrals ?? [], fn($r) => ($r['status'] ?? '') === 'converted')) ?></h3>
                    <small>Converted</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">₹<?= number_format(array_sum(array_column($referrals ?? [], 'commission'))) ?></h3>
                    <small>Commission Earned</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= ($user['referral_code'] ?? 'N/A') ?></h3>
                    <small>Your Referral Code</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3"><h6 class="mb-0">Referral History</h6></div>
        <div class="card-body p-0">
            <?php if (!empty($referrals ?? [])): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>Name</th><th>Email</th><th>Status</th><th>Commission</th><th>Date</th></tr></thead>
                    <tbody>
                        <?php foreach (($referrals ?? []) as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($r['email'] ?? '') ?></td>
                            <td><span class="badge bg-<?= ($r['status'] ?? 'pending') === 'converted' ? 'success' : 'warning' ?>"><?= ucfirst($r['status'] ?? 'pending') ?></span></td>
                            <td>₹<?= number_format($r['commission'] ?? 0) ?></td>
                            <td><?= htmlspecialchars($r['created_at'] ?? '') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-share-alt fa-3x mb-3"></i>
                <p>No referrals yet. Share your referral link to earn commissions!</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
