<?php $pageTitle = 'Payment Form'; ?>
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="fas fa-home me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/payment">Payment</a></li>
            <li class="breadcrumb-item active" aria-current="page">Make Payment</li>
        </ol>
    </nav>
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0">
                    <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Payment Details</h5>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
                    <?php endif; ?>
                    <form method="POST" action="<?= BASE_URL ?>/payment/process">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">Amount (â‚¹)</label>
                            <input type="number" name="amount" class="form-control form-control-lg" required step="0.01" value="<?= htmlspecialchars($_POST['amount'] ?? $amount ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select name="method" class="form-select" required>
                                <option value="">Select method</option>
                                <option value="card" <?= ($_POST['method'] ?? '') === 'card' ? 'selected' : '' ?>>Credit/Debit Card</option>
                                <option value="upi" <?= ($_POST['method'] ?? $method ?? '') === 'upi' ? 'selected' : '' ?>>UPI</option>
                                <option value="netbanking" <?= ($_POST['method'] ?? '') === 'netbanking' ? 'selected' : '' ?>>NetBanking</option>
                                <option value="wallet" <?= ($_POST['method'] ?? '') === 'wallet' ? 'selected' : '' ?>>Wallet</option>
                            </select>
                        </div>
                        <div id="cardDetails" class="style-24280">
                            <div class="mb-3">
                                <label class="form-label">Card Number</label>
                                <input type="text" name="card_number" class="form-control" placeholder="1234 5678 9012 3456" maxlength="19">
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label">Expiry</label>
                                    <input type="text" name="expiry" class="form-control" placeholder="MM/YY" maxlength="5">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">CVV</label>
                                    <input type="password" name="cvv" class="form-control" placeholder="***" maxlength="4">
                                </div>
                            </div>
                        </div>
                        <div id="upiDetails" class="style-61568">
                            <div class="mb-3">
                                <label class="form-label">UPI ID</label>
                                <input type="text" name="upi_id" class="form-control" placeholder="example@paytm" value="<?= htmlspecialchars($_POST['upi_id'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? $name ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? $email ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($_POST['phone'] ?? $phone ?? '') ?>">
                        </div>
                        <button type="submit" class="btn btn-primary w-100 btn-lg"><i class="fas fa-lock me-1"></i>Pay â‚¹<?= number_format($_POST['amount'] ?? $amount ?? 0, 2) ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.querySelector('[name="method"]')?.addEventListener('change', function() {
    document.getElementById('cardDetails').style.display = this.value === 'card' ? 'block' : 'none';
    document.getElementById('upiDetails').style.display = this.value === 'upi' ? 'block' : 'none';
});
</script>
