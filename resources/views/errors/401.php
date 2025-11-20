<?php
// Set the page title
$title = '401 - Unauthorized';

// Capture the content for the layout
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-8 text-center">
        <div class="card p-5">
            <div class="error-icon mb-4">
                <i class="fas fa-lock fa-5x text-info"></i>
            </div>
            <h1 class="display-4 mb-3">Unauthorized Access</h1>
            <p class="lead text-muted mb-4">
                You need to be logged in to access this page.<br>
                Please sign in to continue.
            </p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>auth/login" class="btn btn-primary btn-lg">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </a>
                <a href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-home me-2"></i>Return to Homepage
                </a>
            </div>
            <small class="d-block mt-4 text-muted">
                Error 401 - Unauthorized
            </small>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Include the modern layout
require __DIR__ . '/../layouts/modern.php';