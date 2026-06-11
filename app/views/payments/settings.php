<?php $pageTitle = 'Payment Settings'; ?>
<?php $config = $config ?? ['currency' => 'INR', 'currency_symbol' => '₹', 'gateway' => 'razorpay', 'razorpay_key' => '', 'razorpay_secret' => '', 'paypal_client_id' => '', 'stripe_publishable_key' => '', 'stripe_secret_key' => '', 'paytm_merchant_id' => '', 'paytm_merchant_key' => '']; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>payments">Payments</a></li>
            <li class="breadcrumb-item active">Settings</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center mb-4"><h4 class="mb-0"><i class="fas fa-cog me-2"></i>Payment Settings</h4></div>
    <form method="post" action="<?= BASE_URL ?>payments/settings">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-globe me-2"></i>General Settings</h6></div>
                    <div class="card-body aps-cp-card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Currency</label>
                                <select class="form-select" name="currency">
                                    <option value="INR" <?= ($config['currency'] ?? '') === 'INR' ? 'selected' : '' ?>>INR (₹)</option>
                                    <option value="USD" <?= ($config['currency'] ?? '') === 'USD' ? 'selected' : '' ?>>USD ($)</option>
                                    <option value="EUR" <?= ($config['currency'] ?? '') === 'EUR' ? 'selected' : '' ?>>EUR (€)</option>
                                    <option value="GBP" <?= ($config['currency'] ?? '') === 'GBP' ? 'selected' : '' ?>>GBP (£)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Currency Symbol</label>
                                <input type="text" class="form-control" name="currency_symbol" value="<?= htmlspecialchars($config['currency_symbol'] ?? '₹') ?>" maxlength="5">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Default Gateway</label>
                                <select class="form-select" name="gateway">
                                    <option value="razorpay" <?= ($config['gateway'] ?? '') === 'razorpay' ? 'selected' : '' ?>>Razorpay</option>
                                    <option value="paypal" <?= ($config['gateway'] ?? '') === 'paypal' ? 'selected' : '' ?>>PayPal</option>
                                    <option value="stripe" <?= ($config['gateway'] ?? '') === 'stripe' ? 'selected' : '' ?>>Stripe</option>
                                    <option value="paytm" <?= ($config['gateway'] ?? '') === 'paytm' ? 'selected' : '' ?>>Paytm</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Razorpay</h6></div>
                    <div class="card-body aps-cp-card-body">
                        <div class="mb-3"><label class="form-label">Key ID</label><input type="text" class="form-control" name="razorpay_key" value="<?= htmlspecialchars($config['razorpay_key'] ?? '') ?>" placeholder="rzp_live_xxxxxxxx"></div>
                        <div class="mb-0"><label class="form-label">Key Secret</label><input type="password" class="form-control" name="razorpay_secret" value="<?= htmlspecialchars($config['razorpay_secret'] ?? '') ?>" placeholder="********"></div>
                    </div>
                </div>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white"><h6 class="mb-0"><i class="fab fa-paypal me-2"></i>PayPal</h6></div>
                    <div class="card-body aps-cp-card-body">
                        <div class="mb-0"><label class="form-label">Client ID</label><input type="text" class="form-control" name="paypal_client_id" value="<?= htmlspecialchars($config['paypal_client_id'] ?? '') ?>" placeholder="xxxxxxxxxxxx"></div>
                    </div>
                </div>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-credit-card me-2"></i>Stripe</h6></div>
                    <div class="card-body aps-cp-card-body">
                        <div class="mb-3"><label class="form-label">Publishable Key</label><input type="text" class="form-control" name="stripe_publishable_key" value="<?= htmlspecialchars($config['stripe_publishable_key'] ?? '') ?>"></div>
                        <div class="mb-0"><label class="form-label">Secret Key</label><input type="password" class="form-control" name="stripe_secret_key" value="<?= htmlspecialchars($config['stripe_secret_key'] ?? '') ?>"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Test Mode</h6></div>
                    <div class="card-body aps-cp-card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="test_mode" id="test_mode" value="1" <?= !empty($config['test_mode']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="test_mode">Enable Test Mode</label>
                        </div>
                        <p class="small text-muted">Test mode uses sandbox credentials. No real transactions will be processed.</p>
                        <hr>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-2"></i>Save Settings</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
