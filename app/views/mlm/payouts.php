<?php $pageTitle = 'MLM Payouts'; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="fas fa-home me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/mlm">MLM</a></li>
            <li class="breadcrumb-item active" aria-current="page">Payouts</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-hand-holding-usd me-2"></i>Payout History</h4>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (!empty($payouts)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>#</th><th>Member</th><th>Amount</th><th>Method</th><th>Status</th><th>Processed Date</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payouts as $i => $p): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($p['member_name'] ?? '') ?></td>
                            <td>₹<?= number_format($p['amount'] ?? 0) ?></td>
                            <td><?= htmlspecialchars($p['method'] ?? 'Bank Transfer') ?></td>
                            <td>
                                <span class="badge bg-<?= ($p['status'] ?? 'pending') === 'completed' ? 'success' : (($p['status'] ?? '') === 'processing' ? 'info' : 'warning') ?>">
                                    <?= ucfirst($p['status'] ?? 'pending') ?>
                                </span>
                            </td>
                            <td class="small"><?= htmlspecialchars($p['processed_at'] ?? $p['created_at'] ?? '') ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-hand-holding-usd fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Payouts Yet</h5>
                <p class="text-muted mb-0">Payouts will appear once commissions are processed.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
