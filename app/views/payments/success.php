<?php
if (!isset($payment)) {
    $payment = [];
}
if (!isset($property)) {
    $property = [];
}
include __DIR__ . '/../includes/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>

                    <h1 class="card-title text-success mb-4">Payment Successful!</h1>

                    <p class="lead mb-4">
                        Congratulations! Your payment has been processed successfully.
                    </p>

                    <?php if (is_array($payment)): ?>
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5 class="card-title">Payment Details</h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>Payment ID:</strong> <?php echo htmlspecialchars($payment['id']); ?></p>
                                                <p class="mb-1"><strong>Amount:</strong> ₹<?php echo number_format($payment['amount']); ?></p>
                                                <p class="mb-1"><strong>Status:</strong>
                                                    <span class="badge bg-success"><?php echo ucfirst(htmlspecialchars($payment['status'])); ?></span>
                                                </p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>Date:</strong> <?php echo date('M d, Y H:i', strtotime($payment['created_at'])); ?></p>
                                                <p class="mb-1"><strong>Method:</strong> <?php echo ucfirst(htmlspecialchars($payment['payment_method'] ?? 'Card')); ?></p>
                                                <?php if (!empty($payment['transaction_id'])): ?>
                                                    <p class="mb-1"><strong>Transaction ID:</strong> <?php echo htmlspecialchars($payment['transaction_id']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (is_array($property)): ?>
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5 class="card-title">Property Details</h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>Title:</strong> <?php echo htmlspecialchars($property['title']); ?></p>
                                                <p class="mb-1"><strong>Location:</strong> <?php echo htmlspecialchars($property['location']); ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>Type:</strong> <?php echo ucfirst(htmlspecialchars($property['type'])); ?></p>
                                                <p class="mb-1"><strong>Price:</strong> ₹<?php echo number_format($property['price']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="/properties" class="btn btn-primary me-md-2">
                            <i class="fas fa-search"></i> Browse More Properties
                        </a>
                        <a href="/profile" class="btn btn-outline-primary">
                            <i class="fas fa-user"></i> View Profile
                        </a>
                    </div>

                    <div class="mt-4">
                        <p class="text-muted">
                            A confirmation email has been sent to your registered email address.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>