<?php
/**
 * Associate Wallet Withdrawal View
 * @var array $wallet
 * @var float $walletBalance
 * @var array $bankAccounts
 * @var string $base
 * @var string $csrf_token
 */
$base = BASE_URL;
$page_title = $page_title ?? 'Withdraw Funds';
?>
<?php include __DIR__ . '/../layouts/associate.php'; ?>
<?php ob_start(); ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="mb-0"><i class="fas fa-money-bill-wave me-2 text-success"></i>Withdraw Funds</h4>
        <a href="<?= $base ?>/associate/wallet/my" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back to Wallet</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center bg-gradient-info text-white h-100">
                <div class="card-body">
                    <div class="fs-4 fw-bold">₹<?= number_format($walletBalance ?? 0, 2) ?></div>
                    <small>Available Balance</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center bg-gradient-warning text-white h-100">
                <div class="card-body">
                    <div class="fs-4 fw-bold">₹1,000</div>
                    <small>Min. Withdrawal</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center bg-gradient-success text-white h-100">
                <div class="card-body">
                    <div class="fs-4 fw-bold">0%</div>
                    <small>Processing Fee</small>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($bankAccounts)): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        No bank account linked. Please add a bank account first.
        <a href="<?= $base ?>/associate/bank-details" class="btn btn-sm btn-outline-warning ms-2">Add Bank Account</a>
    </div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="<?= $base ?>/associate/wallet/process-withdrawal" id="withdrawalForm">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Bank Account</label>
                        <select name="bank_account_id" class="form-select" required>
                            <option value="">Select Bank Account</option>
                            <?php foreach ($bankAccounts as $ba): ?>
                                <option value="<?= $ba['id'] ?>">
                                    <?= htmlspecialchars($ba['bank_name']) ?> - ****<?= substr($ba['account_number'], -4) ?>
                                    (<?= htmlspecialchars($ba['account_holder_name']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Amount (₹)</label>
                        <input type="number" name="amount" class="form-control" required min="1000" step="100" max="<?= $walletBalance ?>">
                        <div class="form-text">Min: ₹1,000 | Max: ₹<?= number_format($walletBalance, 2) ?></div>
                    </div>
                </div>

                <div class="alert alert-info mb-4">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Processing Time:</strong> 3-5 business days<br>
                    <strong>Min. Amount:</strong> ₹1,000<br>
                    <strong>Fee:</strong> 0% (Free)
                </div>

                <button type="submit" class="btn btn-success btn-lg w-100">
                    <i class="fas fa-paper-plane me-2"></i>Submit Withdrawal Request
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
document.getElementById('withdrawalForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';

    try {
        const formData = new FormData(this);
        const response = await fetch('<?= $base ?>/associate/wallet/process-withdrawal', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': '<?= $csrf_token ?>'
            },
            body: formData
        });
        const data = await response.json();
        
        if (data.success) {
            alert('✅ ' + data.message);
            setTimeout(() => location.href = '<?= $base ?>/associate/wallet/transactions', 1000);
        } else {
            alert('❌ ' + (data.message || 'Failed to submit withdrawal'));
        }
    } catch (error) {
        alert('Error: ' + error.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
});
</script>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/associate.php'; ?>