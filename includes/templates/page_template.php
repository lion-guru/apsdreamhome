<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';

// Initialize variables
$page_title = "PAGE_TITLE - APS Dream Homes";
$meta_description = "META_DESCRIPTION";
$meta_keywords = "META_KEYWORDS";
$page_url = get_current_url();
$page_image = get_asset_url("images/default-page-image.jpg");

// Additional CSS/JS
$additional_css = '
<link rel="stylesheet" href="' . get_asset_url("css/common.css") . '">
<link rel="stylesheet" href="' . get_asset_url("css/PAGE_SPECIFIC.css") . '">
';

$additional_js = '
<script src="' . get_asset_url("js/common.js") . '"></script>
<script src="' . get_asset_url("js/PAGE_SPECIFIC.js") . '"></script>
';

// Include header
require_once __DIR__ . '/header.php';
?>

<!-- Hero Section -->
<section class="hero-section bg-light py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold">PAGE_HEADING</h1>
                <p class="lead text-muted">PAGE_SUBHEADING</p>
                <div class="d-flex gap-3">
                    <a href="#main-content" class="btn btn-primary px-4">Learn More</a>
                    <a href="/contact" class="btn btn-outline-primary px-4">Contact Us</a>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="<?= get_asset_url('images/PAGE_HERO_IMAGE.jpg') ?>" alt="PAGE_HERO_ALT" class="img-fluid rounded-3 shadow-lg">
            </div>
        </div>
    </div>
</section>

<!-- Breadcrumb -->
<nav class="bg-light border-bottom py-2">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item active">PAGE_NAME</li>
        </ol>
    </div>
</nav>

<!-- Main Content -->
<main id="main-content" class="py-5">
    <div class="container">
        <!-- PAGE_CONTENT -->
    </div>
</main>

<?php
// Include footer
require_once __DIR__ . '/footer.php';
?>