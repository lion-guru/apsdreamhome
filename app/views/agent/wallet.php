<?php
$balance = $balance ?? 0;
$transactions = $transactions ?? [];
$base = BASE_URL ?? ('/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/'));
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1" style="color:#15803d;font-weight:700;"><i class="fas fa-wallet me-2"></i>My Wallet</h4>
        <p class="text-muted mb-0">View balance and transaction history</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card border-0 shadow-sm" style="background:linear-gradient(135deg,#15803d,#059669);color:#fff;">
            <div class="card-body py-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 opacity-75">Available Balance</p>
                        <h2 class="mb-0 fw-bold">₹<?= number_format($balance) ?></h2>
                    </div>
                    <div style="width:60px;height:60px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-wallet fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <i class="fas fa-info-circle text-muted mb-2"></i>
                <p class="text-muted mb-0">Withdrawals are processed within 3-5 business days.<br>Minimum withdrawal: ₹1,000</p>
            </div>
        </div>
    </div>
</div>

<?php if (empty($transactions)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <div style="width:80px;height:80px;border-radius:50%;background:#dcfce7;display:inline-flex;align-items:center;justify-content:center;margin-bottom:16px;">
            <i class="fas fa-receipt fa-2x" style="color:#15803d;"></i>
        </div>
        <h5 class="text-muted">No transactions yet</h5>
        <p class="text-muted mb-0">Your wallet transactions will appear here</p>
    </div>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0">
        <h6 class="mb-0" style="font-weight:600;color:#15803d;"><i class="fas fa-history me-2"></i>Transaction History</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:#f0fdf4;">
                    <tr>
                        <th class="px-3 py-3" style="color:#15803d;font-weight:600;">Type</th>
                        <th class="px-3 py-3" style="color:#15803d;font-weight:600;">Amount</th>
                        <th class="px-3 py-3" style="color:#15803d;font-weight:600;">Description</th>
                        <th class="px-3 py-3" style="color:#15803d;font-weight:600;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $tx): ?>
                    <tr>
                        <td class="px-3">
                            <?php
                            $type = $tx['type'] ?? $tx['transaction_type'] ?? 'credit';
                            $isCredit = in_array($type, ['credit', 'commission', 'bonus', 'refund']);
                            ?>
                            <span class="badge <?= $isCredit ? 'bg-success' : 'bg-danger' ?>">
                                <i class="fas fa-arrow-<?= $isCredit ? 'down' : 'up' ?>"></i>
                            </span>
                            <?= ucfirst($type) ?>
                        </td>
                        <td class="px-3 fw-bold" style="color:<?= $isCredit ? '#15803d' : '#dc2626' ?>;">
                            <?= $isCredit ? '+' : '-' ?>₹<?= number_format($tx['amount'] ?? 0) ?>
                        </td>
                        <td class="px-3"><small class="text-muted"><?= htmlspecialchars($tx['description'] ?? '-') ?></small></td>
                        <td class="px-3"><small class="text-muted"><?= date('d M Y H:i', strtotime($tx['created_at'] ?? 'now')) ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>
