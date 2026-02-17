<?php
// Set the page title
$title = '400 - Bad Request';

// Capture the content for the layout
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-8 text-center">
        <div class="card p-5">
            <div class="error-icon mb-4">
                <i class="fas fa-exclamation-circle fa-5x text-warning"></i>
            </div>
            <h1 class="display-4 mb-3">Bad Request</h1>
            <p class="lead text-muted mb-4">
                The request could not be understood by the server.<br>
                Please check your request and try again.
            </p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="javascript:history.back()" class="btn btn-primary btn-lg">
                    <i class="fas fa-arrow-left me-2"></i>Go Back
                </a>
                <a href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-home me-2"></i>Return to Homepage
                </a>
            </div>
            <small class="d-block mt-4 text-muted">
                Error 400 - Bad Request
            </small>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Include the modern layout
require __DIR__ . '/../layouts/modern.php';