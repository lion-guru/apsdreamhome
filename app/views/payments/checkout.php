<?php include '../app/views/includes/header.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">Complete Your Purchase</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-12">
                            <h4>Property Details</h4>
                            <p class="mb-1"><strong>Title:</strong> <?php echo htmlspecialchars($property['title']); ?></p>
                            <p class="mb-1"><strong>Location:</strong> <?php echo htmlspecialchars($property['location']); ?></p>
                            <p class="mb-1"><strong>Price:</strong> ₹<?php echo number_format($property['price']); ?></p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <h4>Payment Summary</h4>
                            <div class="d-flex justify-content-between">
                                <span>Property Price:</span>
                                <span>₹<?php echo number_format($property['price']); ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Processing Fee:</span>
                                <span>₹0.00</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between font-weight-bold">
                                <span>Total Amount:</span>
                                <span>₹<?php echo number_format($property['price']); ?></span>
                            </div>
                        </div>
                    </div>

                    <form action="/payment/process" method="POST" id="payment-form">
                        <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">

                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                <option value="card">Credit/Debit Card</option>
                                <option value="netbanking">Net Banking</option>
                                <option value="upi">UPI</option>
                                <option value="wallet">Wallet</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="card_number" class="form-label">Card Number</label>
                            <input type="text" class="form-control" id="card_number" name="card_number"
                                   placeholder="1234 5678 9012 3456" maxlength="19">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="expiry_date" class="form-label">Expiry Date</label>
                                <input type="text" class="form-control" id="expiry_date" name="expiry_date"
                                       placeholder="MM/YY" maxlength="5">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="cvv" class="form-label">CVV</label>
                                <input type="text" class="form-control" id="cvv" name="cvv"
                                       placeholder="123" maxlength="4">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="cardholder_name" class="form-label">Cardholder Name</label>
                            <input type="text" class="form-control" id="cardholder_name" name="cardholder_name"
                                   placeholder="John Doe">
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg" id="pay-button">
                                Pay ₹<?php echo number_format($property['price']); ?>
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="fas fa-shield-alt"></i>
                            Your payment information is secure and encrypted
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Simple card number formatting
    document.getElementById('card_number').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        let formatted = value.match(/.{1,4}/g)?.join(' ') || value;
        e.target.value = formatted.substring(0, 19);
    });

    // Expiry date formatting
    document.getElementById('expiry_date').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        e.target.value = value.substring(0, 5);
    });

    // CVV formatting
    document.getElementById('cvv').addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/[^0-9]/g, '').substring(0, 4);
    });

    // Form validation
    document.getElementById('payment-form').addEventListener('submit', function(e) {
        const cardNumber = document.getElementById('card_number').value;
        const expiryDate = document.getElementById('expiry_date').value;
        const cvv = document.getElementById('cvv').value;
        const paymentMethod = document.getElementById('payment_method').value;

        if (paymentMethod === 'card') {
            if (cardNumber.length < 16) {
                alert('Please enter a valid card number');
                e.preventDefault();
                return;
            }

            if (expiryDate.length !== 5) {
                alert('Please enter a valid expiry date (MM/YY)');
                e.preventDefault();
                return;
            }

            if (cvv.length < 3) {
                alert('Please enter a valid CVV');
                e.preventDefault();
                return;
            }
        }

        // Show loading state
        document.getElementById('pay-button').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        document.getElementById('pay-button').disabled = true;
    });
</script>

<?php include '../app/views/includes/footer.php'; ?>
