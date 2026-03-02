<?php
/**
 * Thank You Page - APS Dream Homes
 * Modern Layout Integrated
 */

require_once __DIR__ . '/init.php';

$page_title = 'Thank You | APS Dream Homes';
$layout = 'modern';

// Handle random thank you image/message if needed
$image_dir = __DIR__ . '/../../../../assets/images/thank/';
$random_image = 'default-thank.jpg';

if (is_dir($image_dir)) {
    $files = array_diff(scandir($image_dir), ['.', '..']);
    if (!empty($files)) {
        $random_image = $files[array_rand($files)];
    }
}

ob_start();
?>

<div class="container py-5 mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 text-center">
            <div class="thank-you-wrapper" data-aos="zoom-in">
                <!-- Success Icon -->
                <div class="success-animation mb-4">
                    <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                        <i class="fas fa-check fa-3x"></i>
                    </div>
                </div>

                <h1 class="display-3 fw-bold text-dark mb-3">Thank You!</h1>
                <p class="lead text-secondary mb-5">Your submission has been received successfully. We appreciate your interest in APS Dream Homes.</p>

                <!-- Random Thank You Image -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5 bg-white mx-auto" style="max-width: 500px;">
                    <img src="<?= get_asset_url('thank/' . $random_image, 'images') ?>" class="img-fluid" alt="Thank You" id="thank-you-image">
                </div>

                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="index.php" class="btn btn-primary rounded-pill px-5 py-3 fw-bold shadow-sm">
                        <i class="fas fa-home me-2"></i> Back to Home
                    </a>
                    <a href="properties.php" class="btn btn-outline-dark rounded-pill px-5 py-3 fw-bold">
                        <i class="fas fa-search me-2"></i> Browse Properties
                    </a>
                </div>

                <div class="mt-5 pt-5 border-top">
                    <p class="text-muted mb-3">Follow us for latest updates</p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="#" class="btn btn-light rounded-circle shadow-sm" style="width: 45px; height: 45px;"><i class="fab fa-facebook-f mt-2"></i></a>
                        <a href="#" class="btn btn-light rounded-circle shadow-sm" style="width: 45px; height: 45px;"><i class="fab fa-twitter mt-2"></i></a>
                        <a href="#" class="btn btn-light rounded-circle shadow-sm" style="width: 45px; height: 45px;"><i class="fab fa-instagram mt-2"></i></a>
                        <a href="#" class="btn btn-light rounded-circle shadow-sm" style="width: 45px; height: 45px;"><i class="fab fa-linkedin-in mt-2"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .success-animation {
        animation: bounceIn 1s ease;
    }
    @keyframes bounceIn {
        0% { transform: scale(0.3); opacity: 0; }
        50% { transform: scale(1.05); opacity: 1; }
        70% { transform: scale(0.9); }
        100% { transform: scale(1); }
    }
    #thank-you-image {
        transition: transform 0.5s ease;
    }
    #thank-you-image:hover {
        transform: scale(1.05);
    }
</style>

<?php
$content = ob_get_clean();

// Include the layout
require_once __DIR__ . '/../layouts/' . $layout . '.php';
?>
