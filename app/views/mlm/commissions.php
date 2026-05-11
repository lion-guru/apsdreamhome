<?php $pageTitle = 'MLM Commissions'; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="fas fa-home me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/mlm">MLM</a></li>
            <li class="breadcrumb-item active" aria-current="page">Commissions</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-coins me-2"></i>Commissions</h4>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center p-3">
                <h4 class="text-primary mb-0">₹<?= number_format($totalCommission ?? 0) ?></h4>
                <small class="text-muted">Total Commission Earned</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center p-3">
                <h4 class="text-success mb-0">₹<?= number_format($pendingCommission ?? 0) ?></h4>
                <small class="text-muted">Pending Commission</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center p-3">
                <h4 class="text-warning mb-0">₹<?= number_format($paidCommission ?? 0) ?></h4>
                <small class="text-muted">Paid Commission</small>
            </div>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (!empty($commissions)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>#</th><th>From</th><th>Level</th><th>Amount</th><th>Type</th><th>Status</th><th>Date</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($commissions as $i => $c): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td class="small"><?= htmlspecialchars($c['from_name'] ?? '') ?></td>
                            <td>Level <?= $c['level'] ?? 1 ?></td>
                            <td>₹<?= number_format($c['amount'] ?? 0) ?></td>
                            <td><span class="badge bg-info"><?= ucfirst($c['type'] ?? '') ?></span></td>
                            <td><span class="badge bg-<?= ($c['status'] ?? 'pending') === 'paid' ? 'success' : 'warning' ?>"><?= ucfirst($c['status'] ?? 'pending') ?></span></td>
                            <td class="small"><?= htmlspecialchars($c['created_at'] ?? '') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-coins fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Commissions Yet</h5>
                <p class="text-muted mb-0">Commissions will appear as your network grows.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
