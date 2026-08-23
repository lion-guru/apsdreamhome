<?php $pageTitle = $pageTitle ?? $page_title ?? 'Payment Error'; $errorMessage = $error ?? $error_message ?? 'An unexpected error occurred during payment processing.'; ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body py-5">
                    <div class="mb-4"><i class="fas fa-exclamation-triangle text-warning" class="style-69098"></i></div>
                    <h3 class="text-warning mb-3">Payment Error</h3>
                    <p class="text-muted mb-4"><?= h($errorMessage) ?></p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="javascript:history.back()" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Go Back</a>
                        <a href="<?= BASE_URL ?>payment/gateway-selection" class="btn btn-primary"><i class="fas fa-redo me-1"></i>Try Again</a>
                    </div>
                    <p class="mt-4 small text-muted">Need help? <a href="<?= BASE_URL ?>contact">Contact Support</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
