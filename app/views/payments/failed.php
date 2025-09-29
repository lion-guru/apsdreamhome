<?php include '../app/views/includes/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <i class="fas fa-times-circle text-danger" style="font-size: 4rem;"></i>
                    </div>

                    <h1 class="card-title text-danger mb-4">Payment Failed</h1>

                    <p class="lead mb-4">
                        Unfortunately, your payment could not be processed.
                    </p>

                    <div class="alert alert-warning">
                        <h5>Possible reasons:</h5>
                        <ul class="text-start mb-0">
                            <li>Insufficient funds</li>
                            <li>Card expired or blocked</li>
                            <li>Incorrect card details</li>
                            <li>Network connectivity issues</li>
                            <li>Bank security restrictions</li>
                        </ul>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="javascript:history.back()" class="btn btn-primary me-md-2">
                            <i class="fas fa-arrow-left"></i> Try Again
                        </a>
                        <a href="/contact" class="btn btn-outline-primary">
                            <i class="fas fa-support"></i> Contact Support
                        </a>
                    </div>

                    <div class="mt-4">
                        <p class="text-muted">
                            <small>
                                If you continue to experience issues, please contact our support team
                                or try using a different payment method.
                            </small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../app/views/includes/footer.php'; ?>
