<?php
$page_title = 'My Transactions - APS Dream Home';
$extraHead = '<style>
    .txn-card { border: none; border-radius: 12px; box-shadow: 0 3px 15px rgba(0,0,0,0.05); margin-bottom: 1rem; }
    .txn-card.completed { border-left: 4px solid #198754; }
    .txn-card.pending { border-left: 4px solid #ffc107; }
    .txn-card.cancelled { border-left: 4px solid #dc3545; }
</style>';
?>

<div class="content-area p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-file-invoice-dollar me-2 text-success"></i>My Transactions</h3>
        <a href="<?= BASE_URL ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>

    <?php if (empty($transactions)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-file-invoice fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">No transactions yet</h5>
                <p class="text-muted">Your completed deals will appear here.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8">
                <?php foreach ($transactions as $txn): ?>
                    <div class="card txn-card <?= htmlspecialchars($txn['status']) ?>">
                        <div class="card-body aps-cp-card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1"><?= htmlspecialchars($txn['property_title'] ?? 'Property') ?></h6>
                                    <p class="text-muted small mb-0">
                                        <i class="fas fa-calendar me-1"></i><?= date('d M Y', strtotime($txn['deal_closed_at'] ?? $txn['created_at'])) ?>
                                        | <i class="fas fa-hashtag me-1"></i>TXN-<?= (int)$txn['id'] ?>
                                    </p>
                                </div>
                                <span class="badge bg-<?= $txn['status'] === 'completed' ? 'success' : ($txn['status'] === 'pending' ? 'warning' : 'danger') ?>">
                                    <?= ucfirst($txn['status']) ?>
                                </span>
                            </div>
                            <div class="row text-center">
                                <div class="col-4">
                                    <small class="text-muted">Final Price</small>
                                    <p class="fw-bold mb-0">₹<?= number_format($txn['final_price'] ?? 0) ?></p>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted">Platform Fee</small>
                                    <p class="fw-bold mb-0 text-danger">-₹<?= number_format($txn['platform_fee'] ?? 0) ?></p>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted">Referral Commission</small>
                                    <p class="fw-bold mb-0 text-success">+₹<?= number_format($txn['referral_commission'] ?? 0) ?></p>
                                </div>
                            </div>
                            <?php if ($txn['referrer_name']): ?>
                                <p class="text-muted small mb-0 mt-2">
                                    <i class="fas fa-user me-1"></i>Referrer: <?= htmlspecialchars($txn['referrer_name']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body aps-cp-card-body">
                        <h6><i class="fas fa-chart-pie me-2"></i>Summary</h6>
                        <?php
                        $totalDeals = count($transactions);
                        $totalVolume = array_sum(array_column($transactions, 'final_price'));
                        $totalFees = array_sum(array_column($transactions, 'platform_fee'));
                        $totalCommission = array_sum(array_column($transactions, 'referral_commission'));
                        ?>
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between">
                                <span>Total Deals</span>
                                <strong><?= $totalDeals ?></strong>
                            </div>
                            <div class="list-group-item d-flex justify-content-between">
                                <span>Total Volume</span>
                                <strong>₹<?= number_format($totalVolume) ?></strong>
                            </div>
                            <div class="list-group-item d-flex justify-content-between">
                                <span>Platform Fees Paid</span>
                                <strong class="text-danger">₹<?= number_format($totalFees) ?></strong>
                            </div>
                            <div class="list-group-item d-flex justify-content-between">
                                <span>Commission Earned</span>
                                <strong class="text-success">₹<?= number_format($totalCommission) ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>