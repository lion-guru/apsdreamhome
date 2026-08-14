<?php $pageTitle = 'Select Payment Gateway'; ?>
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="fas fa-home me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/payment">Payment</a></li>
            <li class="breadcrumb-item active" aria-current="page">Gateway</li>
        </ol>
    </nav>
    <div class="text-center mb-4">
        <h4><i class="fas fa-credit-card me-2"></i>Select Payment Gateway</h4>
        <p class="text-muted">Choose your preferred payment method</p>
    </div>
    <div class="row justify-content-center g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 text-center p-4 gateway-card" onclick="selectGateway('razorpay')" class="style-78508">
                <div class="display-4 text-primary mb-3"><i class="fas fa-bolt"></i></div>
                <h5>Razorpay</h5>
                <p class="text-muted small mb-3">Pay via UPI, Cards, NetBanking & Wallets</p>
                <a href="<?= BASE_URL ?>/payment/razorpay_checkout" class="btn btn-primary"><i class="fas fa-arrow-right me-1"></i>Pay with Razorpay</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 text-center p-4 gateway-card" onclick="selectGateway('stripe')" class="style-78508">
                <div class="display-4 text-info mb-3"><i class="fab fa-stripe-s"></i></div>
                <h5>Stripe</h5>
                <p class="text-muted small mb-3">International cards & Apple Pay</p>
                <a href="<?= BASE_URL ?>/payment/stripe_checkout" class="btn btn-info text-white"><i class="fas fa-arrow-right me-1"></i>Pay with Stripe</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 text-center p-4 gateway-card" onclick="selectGateway('upi')" class="style-78508">
                <div class="display-4 text-success mb-3"><i class="fas fa-mobile-alt"></i></div>
                <h5>UPI</h5>
                <p class="text-muted small mb-3">Google Pay, PhonePe, Paytm</p>
                <a href="<?= BASE_URL ?>/payment/payment_form?method=upi" class="btn btn-success"><i class="fas fa-arrow-right me-1"></i>Pay with UPI</a>
            </div>
        </div>
    </div>
    <div class="text-center mt-4">
        <a href="<?= BASE_URL ?>/payment/payment_form" class="btn btn-outline-secondary">View All Payment Options</a>
    </div>
</div>
<script>
function selectGateway(gateway) {
    document.querySelectorAll('.gateway-card').forEach(function(c) { c.classList.remove('border-primary'); });
    event.currentTarget.classList.add('border-primary');
}
</script>
