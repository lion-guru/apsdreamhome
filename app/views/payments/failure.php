<?php $pageTitle = 'Payment Failed'; ?>
<?php $errorMessage = $errorMessage ?? 'Your payment could not be processed. Please try again.'; $transactionId = $transactionId ?? null; ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body py-5">
                    <div class="mb-4"><i class="fas fa-times-circle text-danger" class="style-69098"></i></div>
                    <h3 class="text-danger mb-3">Payment Failed!</h3>
                    <p class="text-muted mb-1"><?= htmlspecialchars($errorMessage) ?></p>
                    <?php if ($transactionId): ?>
                    <p class="small text-muted">Transaction ID: <code><?= htmlspecialchars($transactionId) ?></code></p>
                    <?php endif; ?>
                    <hr class="my-4">
                    <div class="d-flex justify-content-center gap-3">
                        <a href="javascript:history.back()" class="btn btn-outline-primary"><i class="fas fa-arrow-left me-1"></i>Go Back</a>
                        <a href="<?= BASE_URL ?>payments/initiate" class="btn btn-primary"><i class="fas fa-redo me-1"></i>Retry Payment</a>
                    </div>
                    <p class="mt-3 small text-muted">Need help? <a href="<?= BASE_URL ?>contact">Contact Support</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
