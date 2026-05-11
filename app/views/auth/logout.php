<?php $pageTitle = 'Logged Out'; ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body py-5">
                    <div class="mb-4"><i class="fas fa-check-circle text-success" style="font-size:5rem"></i></div>
                    <h4>You've Been Logged Out</h4>
                    <p class="text-muted mb-1">Your session has been ended successfully.</p>
                    <p class="text-muted small mb-4">Thank you for visiting APS Dream Home. See you again soon!</p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="<?= BASE_URL ?>login" class="btn btn-primary"><i class="fas fa-sign-in-alt me-1"></i>Login Again</a>
                        <a href="<?= BASE_URL ?>" class="btn btn-outline-secondary"><i class="fas fa-home me-1"></i>Go Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
