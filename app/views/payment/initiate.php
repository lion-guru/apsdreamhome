<?php include __DIR__ . "/../layouts/header.php"; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1 class="h2 mb-4">
                    <i class="fas fa-credit-card"></i> Initiate
                </h1>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Initiate - APS Dream Home Payment System
                    </div>
                    <form method="POST" action="/payment/process" id="paymentForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="customer_id" class="form-label">Customer *</label>
                                    <select class="form-select" id="customer_id" name="customer_id" required>
                                        <option value="">Select Customer</option>
                                        <option value="1">Rahul Sharma</option>
                                        <option value="2">Priya Singh</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="property_id" class="form-label">Property *</label>
                                    <select class="form-select" id="property_id" name="property_id" required>
                                        <option value="">Select Property</option>
                                        <option value="1">Plot A-101</option>
                                        <option value="2">Project Suryoday Heights</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="payment_type" class="form-label">Payment Type *</label>
                                    <select class="form-select" id="payment_type" name="payment_type" required>
                                        <option value="">Select Type</option>
                                        <option value="booking">Booking Amount</option>
                                        <option value="down_payment">Down Payment</option>
                                        <option value="emi">EMI</option>
                                        <option value="full_payment">Full Payment</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Amount (₹) *</label>
                                    <input type="number" class="form-control" id="amount" name="amount" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="gateway" class="form-label">Payment Gateway *</label>
                                    <select class="form-select" id="gateway" name="gateway" required>
                                        <option value="">Select Gateway</option>
                                        <option value="razorpay">Razorpay</option>
                                        <option value="paytm">Paytm</option>
                                        <option value="phonepe">PhonePe</option>
                                        <option value="upi">UPI</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="/payment" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-credit-card"></i> Process Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . "/../layouts/footer.php"; ?>