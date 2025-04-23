<?php
// Custom 404 Not Found Page for APS Dream Homes
http_response_code(404);
require_once __DIR__ . '/includes/templates/dynamic_header.php';
?>

<section class="notfound-section py-5 text-center bg-light" style="min-height: 60vh; display: flex; align-items: center; justify-content: center;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <img src="/assets/images/banner/ban3.jpg" alt="Page Not Found" class="img-fluid mb-4" style="max-width:220px;" loading="lazy">
                <h1 class="display-3 fw-bold text-danger mb-3">404</h1>
                <h2 class="mb-3">Page Not Found</h2>
                <p class="lead mb-4">Sorry, the page you are looking for doesn’t exist or has been moved.<br>Try using the menu or return to the homepage.</p>
                <a href="/" class="btn btn-primary btn-lg rounded-pill px-5">Go to Homepage</a>
            </div>
        </div>
    </div>
</section>

<?php require_once(__DIR__ . '/includes/templates/new_footer.php'); ?>
</body>
</html>
