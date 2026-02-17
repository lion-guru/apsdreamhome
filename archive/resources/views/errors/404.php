<?php
// Set the page title
$title = '404 - Page Not Found';

// Capture the content for the layout
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-8 text-center">
        <div class="card p-5">
            <div class="error-icon mb-4">
                <i class="fas fa-search fa-5x text-warning"></i>
            </div>
            <h1 class="display-4 mb-3">Page Not Found</h1>
            <p class="lead text-muted mb-4">
                The page you are looking for might have been removed, 
                had its name changed, or is temporarily unavailable.
            </p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>" class="btn btn-primary btn-lg">
                    <i class="fas fa-home me-2"></i>Return to Homepage
                </a>
                <a href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>properties" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-building me-2"></i>Browse Properties
                </a>
            </div>
            <small class="d-block mt-4 text-muted">
                Error 404 - Page Not Found
            </small>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Include the modern layout
require __DIR__ . '/../layouts/modern.php';