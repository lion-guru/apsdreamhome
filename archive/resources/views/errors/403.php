<?php
// Set the page title
$title = '403 - Forbidden';

// Capture the content for the layout
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-8 text-center">
        <div class="card p-5">
            <div class="error-icon mb-4">
                <i class="fas fa-shield-alt fa-5x text-warning"></i>
            </div>
            <h1 class="display-4 mb-3">Access Forbidden</h1>
            <p class="lead text-muted mb-4">
                Sorry, you don't have permission to access this page.<br>
                If you believe this is an error, please contact support.
            </p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>" class="btn btn-primary btn-lg">
                    <i class="fas fa-home me-2"></i>Return to Homepage
                </a>
                <a href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>contact" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-envelope me-2"></i>Contact Support
                </a>
            </div>
            <small class="d-block mt-4 text-muted">
                Error 403 - Forbidden
            </small>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Include the modern layout
require __DIR__ . '/../layouts/modern.php';