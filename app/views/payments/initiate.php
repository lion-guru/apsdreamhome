<?php $pageTitle = 'Initiate Payment'; ?>
<?php $gateways = $gateways ?? ['razorpay' => 'Razorpay', 'paypal' => 'PayPal', 'stripe' => 'Stripe', 'paytm' => 'Paytm']; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>payments">Payments</a></li>
            <li class="breadcrumb-item active">Initiate Payment</li>
        </ol>
    </nav>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Initiate New Payment</h5></div>
                <div class="card-body aps-cp-card-body">
                    <form method="post" action="<?= BASE_URL ?>payments/initiate">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">Payment Purpose <span class="text-danger">*</span></label>
                            <select class="form-select" name="purpose" required>
                                <option value="">Select purpose...</option>
                                <option value="booking">Booking Amount</option>
                                <option value="installment">Installment</option>
                                <option value="registration">Registration Fee</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount (₹) <span class="text-danger">*</span></label>
                            <div class="input-group"><span class="input-group-text">₹</span><input type="number" class="form-control" name="amount" step="0.01" min="1" required placeholder="Enter amount"></div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="customer_name" required placeholder="Full name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Customer Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="customer_email" required placeholder="email@example.com">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Customer Phone</label>
                            <input type="tel" class="form-control" name="customer_phone" placeholder="+91 9XXXXXXXX">
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Payment Gateway <span class="text-danger">*</span></label>
                            <div class="row g-2">
                                <?php foreach ($gateways as $key => $label): ?>
                                <div class="col-md-3 col-6">
                                    <div class="form-check gateway-option">
                                        <input class="form-check-input" type="radio" name="gateway" id="gw_<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" <?= $key === 'razorpay' ? 'checked' : '' ?>>
                                        <label class="form-check-label btn btn-outline-secondary w-100 text-center py-3 rounded" for="gw_<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>">
                                            <i class="fas fa-<?= $key === 'razorpay' ? 'bolt' : ($key === 'paypal' ? 'paypal' : ($key === 'stripe' ? 'credit-card' : 'mobile-alt')) ?> fa-lg d-block mb-1"></i><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description / Notes</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Optional notes about this payment"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-credit-card me-2"></i>Proceed to Payment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<style>.gateway-option input[type="radio"]{display:none}.gateway-option input[type="radio"]:checked+label{border-color:#0d6efd;background:#e7f1ff;color:#0d6efd;font-weight:600}</style>
