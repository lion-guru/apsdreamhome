<?php
$user = $user ?? [];
$wallet = $wallet ?? ['balance' => 0, 'total_credited' => 0];
$transactions = $transactions ?? [];
$commissions = $commissions ?? [];
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
$csrf = $_SESSION['csrf_token'] ?? '';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= $base ?>/admin/users/<?= $user['id'] ?>" class="text-decoration-none text-muted"><i class="fas fa-arrow-left me-2"></i><?= htmlspecialchars($user['name'] ?? '') ?></a>
        <h4 class="mt-2 mb-0"><i class="fas fa-wallet me-2 text-success"></i>Wallet & Transactions</h4>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#creditModal"><i class="fas fa-plus me-1"></i>Credit</button>
        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#debitModal"><i class="fas fa-minus me-1"></i>Debit</button>
    </div>
</div>

<!-- Balance Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-success text-white"><div class="card-body text-center">
            <h6 class="opacity-75">Current Balance</h6>
            <h2 class="mb-0">₹<?= number_format((float)$wallet['balance']) ?></h2>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-primary text-white"><div class="card-body text-center">
            <h6 class="opacity-75">Total Credited</h6>
            <h2 class="mb-0">₹<?= number_format((float)$wallet['total_credited']) ?></h2>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-info text-white"><div class="card-body text-center">
            <h6 class="opacity-75">Commission Earnings</h6>
            <h2 class="mb-0">₹<?= number_format(array_sum(array_column($commissions, 'amount'))) ?></h2>
        </div></div>
    </div>
</div>

<!-- Wallet Transactions -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Wallet Transactions (Last 50)</h6></div>
    <div class="card-body p-0">
        <?php if (empty($transactions)): ?>
        <div class="text-center py-4 text-muted">No transactions yet</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr><th>Date</th><th>Type</th><th>Amount</th><th>Description</th></tr></thead>
                <tbody>
                <?php foreach ($transactions as $t): ?>
                <tr>
                    <td><small><?= date('M d, Y H:i', strtotime($t['created_at'] ?? '')) ?></small></td>
                    <td><span class="badge bg-<?= ($t['type'] ?? '') === 'credit' ? 'success' : 'warning' ?>"><?= ucfirst($t['type'] ?? '') ?></span></td>
                    <td class="fw-bold <?= ($t['type'] ?? '') === 'credit' ? 'text-success' : 'text-danger' ?>">
                        <?= ($t['type'] ?? '') === 'credit' ? '+' : '-' ?>₹<?= number_format((float)($t['amount'] ?? 0)) ?>
                    </td>
                    <td><small class="text-muted"><?= htmlspecialchars($t['description'] ?? '') ?></small></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Commission Ledger -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-coins me-2"></i>Commission Ledger (Last 50)</h6></div>
    <div class="card-body p-0">
        <?php if (empty($commissions)): ?>
        <div class="text-center py-4 text-muted">No commissions yet</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr><th>Date</th><th>Type</th><th>Amount</th><th>From</th><th>Status</th><th>Notes</th></tr></thead>
                <tbody>
                <?php foreach ($commissions as $c): ?>
                <tr>
                    <td><small><?= date('M d, Y H:i', strtotime($c['created_at'] ?? '')) ?></small></td>
                    <td><span class="badge bg-primary"><?= str_replace('_', ' ', ucfirst($c['commission_type'] ?? '')) ?></span></td>
                    <td class="fw-bold text-success">₹<?= number_format((float)($c['amount'] ?? 0)) ?></td>
                    <td><?= htmlspecialchars($c['source_name'] ?? $c['source_user_id'] ?? '') ?></td>
                    <td><span class="badge bg-<?= ($c['status'] ?? '') === 'approved' ? 'success' : 'warning' ?>"><?= ucfirst($c['status'] ?? '') ?></span></td>
                    <td><small class="text-muted"><?= htmlspecialchars($c['notes'] ?? '') ?></small></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Credit Modal -->
<div class="modal fade" id="creditModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="fas fa-plus-circle text-success me-2"></i>Credit Wallet</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <form id="creditForm">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <div class="mb-3"><label class="form-label">Amount (₹)</label><input type="number" class="form-control" name="amount" required min="1" step="1"></div>
            <div class="mb-3"><label class="form-label">Reason</label><input type="text" class="form-control" name="reason" placeholder="e.g. Bonus, Referral reward, Manual correction"></div>
        </form>
    </div>
    <div class="modal-footer"><button class="btn btn-success" onclick="creditWallet()">Credit</button></div>
</div></div></div>

<!-- Debit Modal -->
<div class="modal fade" id="debitModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="fas fa-minus-circle text-warning me-2"></i>Debit Wallet</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <form id="debitForm">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <div class="mb-3"><label class="form-label">Amount (₹)</label><input type="number" class="form-control" name="amount" required min="1" step="1"></div>
            <div class="mb-3"><label class="form-label">Reason</label><input type="text" class="form-control" name="reason" placeholder="e.g. Penalty, Adjustment, Correction"></div>
        </form>
    </div>
    <div class="modal-footer"><button class="btn btn-warning" onclick="debitWallet()">Debit</button></div>
</div></div></div>

<script>
function creditWallet() {
    showLoader();
    fetch('<?= $base ?>/admin/users/<?= $user['id'] ?>/wallet/credit', {
        method: 'POST', headers: {'X-Requested-With': 'XMLHttpRequest'}, body: new URLSearchParams(new FormData(document.getElementById('creditForm')))
    }).then(r => r.json()).then(d => { showToast(d.message || d.error, 'danger'); if (d.success) location.reload(); }).catch(() => showToast('Error', 'danger')).finally(() => hideLoader());
}
function debitWallet() {
    showLoader();
    fetch('<?= $base ?>/admin/users/<?= $user['id'] ?>/wallet/debit', {
        method: 'POST', headers: {'X-Requested-With': 'XMLHttpRequest'}, body: new URLSearchParams(new FormData(document.getElementById('debitForm')))
    }).then(r => r.json()).then(d => { showToast(d.message || d.error, 'danger'); if (d.success) location.reload(); }).catch(() => showToast('Error', 'danger')).finally(() => hideLoader());
}
</script>
