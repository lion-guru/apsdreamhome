<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Payment Successful' ?> - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .success-container {
            max-width: 600px;
            margin: 50px auto;
            text-align: center;
            padding: 40px;
        }
        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #28a745, #20c997);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 3rem;
            color: white;
        }
        .success-title {
            color: #28a745;
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .payment-id {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            font-family: monospace;
            font-size: 1.1rem;
            margin: 20px 0;
        }
        .next-steps {
            background: #e9ecef;
            border-radius: 10px;
            padding: 25px;
            margin: 30px 0;
            text-align: left;
        }
        .next-steps h5 {
            color: #495057;
            margin-bottom: 15px;
        }
        .next-steps ul {
            list-style: none;
            padding: 0;
        }
        .next-steps li {
            padding: 8px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .next-steps li:last-child {
            border-bottom: none;
        }
        .next-steps i {
            color: #28a745;
            margin-right: 10px;
        }
        .btn-home {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 15px 40px;
            font-size: 1.1rem;
            border-radius: 25px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container success-container">
        <!-- Success Icon -->
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>

        <!-- Success Message -->
        <h1 class="success-title">
            <i class="fas fa-check-circle"></i> Payment Successful!
        </h1>
        <p class="lead text-muted">Thank you for your booking. Your payment has been processed successfully.</p>

        <!-- Payment Details -->
        <div class="payment-id">
            <strong>Payment ID:</strong> <?= htmlspecialchars($payment_id) ?>
        </div>

        <!-- Next Steps -->
        <div class="next-steps">
            <h5><i class="fas fa-list-check"></i> What's Next?</h5>
            <ul>
                <li>
                    <i class="fas fa-envelope"></i>
                    You'll receive a confirmation email shortly with booking details
                </li>
                <li>
                    <i class="fas fa-phone"></i>
                    Our team will contact you within 24 hours to discuss next steps
                </li>
                <li>
                    <i class="fas fa-home"></i>
                    Property visit can be scheduled at your convenience
                </li>
                <li>
                    <i class="fas fa-file-contract"></i>
                    Legal documentation will be prepared for your review
                </li>
            </ul>
        </div>

        <!-- Contact Information -->
        <div class="alert alert-info">
            <h6><i class="fas fa-info-circle"></i> Need Help?</h6>
            <p class="mb-2">For any questions about your booking:</p>
            <p class="mb-1"><i class="fas fa-phone"></i> +91-XXXXXXXXXX</p>
            <p class="mb-0"><i class="fas fa-envelope"></i> bookings@apsdreamhome.com</p>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex gap-3 justify-content-center">
            <a href="<?= BASE_URL ?>/properties" class="btn btn-outline-primary">
                <i class="fas fa-search"></i> Browse More Properties
            </a>
            <a href="<?= BASE_URL ?>/dashboard" class="btn btn-outline-secondary">
                <i class="fas fa-tachometer-alt"></i> My Dashboard
            </a>
            <a href="<?= BASE_URL ?>/" class="btn btn-home">
                <i class="fas fa-home"></i> Back to Home
            </a>
        </div>
    </div>

    <!-- Auto redirect after 10 seconds -->
    <script>
        setTimeout(function() {
            if (confirm('Would you like to return to homepage?')) {
                window.location.href = '<?= BASE_URL ?>/';
            }
        }, 10000);
    </script>
</body>
</html>
