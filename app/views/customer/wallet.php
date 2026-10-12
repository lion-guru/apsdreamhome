<?php
$page_title = $page_title ?? 'My Wallet';
$current_page = $current_page ?? 'wallet';
$wallet = $wallet ?? ['balance' => 0, 'total_earned' => 0, 'total_redeemed' => 0];
$transactions = $transactions ?? [];
$stats = $stats ?? ['balance' => 0, 'total_earned' => 0, 'total_redeemed' => 0];
?>
<div class="aps-cp-hero">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2><i class="fas fa-wallet me-2"></i>My Wallet</h2>
            <p class="text-muted">Manage your wallet balance and transaction history</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="<?= BASE_URL ?>/user/wallet/packages" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Activate Wallet
            </a>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="aps-cp-stat aps-cp-stat--blue">
            <div class="aps-cp-stat-icon"><i class="fas fa-rupee-sign"></i></div>
            <div class="aps-cp-stat-body">
                <div class="aps-cp-stat-value">₹<?= number_format($stats['balance'], 2) ?></div>
                <div class="aps-cp-stat-label">Current Balance</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="aps-cp-stat aps-cp-stat--green">
            <div class="aps-cp-stat-icon"><i class="fas fa-arrow-circle-up"></i></div>
            <div class="aps-cp-stat-body">
                <div class="aps-cp-stat-value">₹<?= number_format($stats['total_earned'], 2) ?></div>
                <div class="aps-cp-stat-label">Total Earned</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="aps-cp-stat aps-cp-stat--orange">
            <div class="aps-cp-stat-icon"><i class="fas fa-arrow-circle-down"></i></div>
            <div class="aps-cp-stat-body">
                <div class="aps-cp-stat-value">₹<?= number_format($stats['total_redeemed'], 2) ?></div>
                <div class="aps-cp-stat-label">Total Redeemed</div>
            </div>
        </div>
    </div>
</div>

<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-qrcode me-2"></i>UPI Pay Link</h5>
        <span class="badge bg-success">Active</span>
    </div>
    <div class="aps-cp-card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <p class="mb-2">Add money to your wallet instantly via UPI:</p>
                <div class="input-group mb-3">
                    <input type="text" class="form-control form-control-lg" value="<?= BASE_URL ?>/customer/pay-emi/upi-qr" readonly id="upiPayLink">
                    <button class="btn btn-outline-secondary" type="button" onclick="copyUpiLink()">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
                <small class="text-muted">Opens your UPI app (GPay, PhonePe, Paytm) with pre-filled amount. VPA: <strong>apsdreamhome@icici</strong></small>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="<?= BASE_URL ?>/customer/pay-emi/upi-qr" class="btn btn-success btn-lg" target="_blank">
                    <i class="fas fa-mobile-alt me-2"></i>Pay via UPI App
                </a>
            </div>
        </div>
    </div>
</div>

<div class="aps-cp-card">
    <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Transactions</h5>
        <span class="badge bg-info"><?= count($transactions) ?> transactions</span>
    </div>
    <div class="aps-cp-card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($transactions)): ?>
                        <?php foreach ($transactions as $txn): ?>
                            <tr>
                                <td><?= date('d M Y H:i', strtotime($txn['created_at'] ?? 'now')) ?></td>
                                <td>
                                    <?php
                                        $type = $txn['transaction_type'] ?? $txn['type'] ?? 'credit';
                                        $badgeClass = $type === 'debit' ? 'danger' : 'success';
                                    ?>
                                    <span class="badge bg-<?= $badgeClass ?>">
                                        <?= ucfirst($type) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($txn['description'] ?? $txn['notes'] ?? '-') ?></td>
                                <td class="text-end">
                                    <?php
                                        $amount = (float)($txn['amount'] ?? 0);
                                        $type = $txn['transaction_type'] ?? $txn['type'] ?? 'credit';
                                        $sign = $type === 'debit' ? '-' : '+';
                                    ?>
                                    <span class="<?= $type === 'debit' ? 'text-danger' : 'text-success' ?>">
                                        <?= $sign ?>₹<?= number_format($amount, 2) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    ₹<?= number_format((float)($txn['balance_after'] ?? 0), 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="fas fa-wallet fa-3x mb-3"></i>
                                <h5>No Transactions Yet</h5>
                                <p class="mb-3">Your wallet transaction history will appear here.</p>
                                <a href="<?= BASE_URL ?>/user/wallet/packages" class="btn btn-primary">
                                    <i class="fas fa-bolt me-1"></i>Activate Wallet
                                </a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function copyUpiLink() {
    const input = document.getElementById('upiPayLink');
    input.select();
    document.execCommand('copy');
    const btn = input.nextElementSibling;
    const original = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
    btn.classList.remove('btn-outline-secondary');
    btn.classList.add('btn-success');
    setTimeout(() => {
        btn.innerHTML = original;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-secondary');
    }, 2000);
}
</script>