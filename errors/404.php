<?php 
$page_title = '404 - Page Not Found';
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="error-template">
                <h1>Oops!</h1>
                <h2>404 Not Found</h2>
                <div class="error-details">
                    Sorry, the page you are looking for could not be found.
                </div>
                <div class="error-actions mt-4">
                    <a href="<?php echo BASE_URL; ?>" class="btn btn-primary btn-lg me-2">
                        <i class="fas fa-home"></i> Take Me Home
                    </a>
                    <a href="<?php echo BASE_URL; ?>/contact" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-envelope"></i> Contact Support
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
