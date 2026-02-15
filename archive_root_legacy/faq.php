<?php
require_once __DIR__ . '/includes/config/config.php';
require_once __DIR__ . '/includes/functions.php';

// Initialize variables
$page_title = "Frequently Asked Questions - APS Dream Homes";
$meta_description = "Find answers to common questions about buying, selling, and investing in real estate with APS Dream Homes.";
$meta_keywords = "FAQ, frequently asked questions, real estate FAQ, property buying guide, home selling tips, APS Dream Homes help";

// Additional CSS
$additional_css = '
<link rel="stylesheet" href="' . get_asset_url("css/common.css") . '">
<link rel="stylesheet" href="' . get_asset_url("css/faq.css") . '">
';

// Additional JS
$additional_js = '
<script src="' . get_asset_url("js/common.js") . '"></script>
<script src="' . get_asset_url("js/faq.js") . '"></script>
';

// Fetch FAQ categories
$categories_query = "SELECT DISTINCT category FROM faqs WHERE status = 'active' ORDER BY category";
$categories_result = mysqli_query($conn, $categories_query);
$categories = [];
while ($row = mysqli_fetch_assoc($categories_result)) {
    $categories[] = $row['category'];
}

// Fetch FAQs
$current_category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : 'all';
$where_clause = $current_category !== 'all' ? "AND category = '$current_category'" : "";

$faqs_query = "SELECT * FROM faqs WHERE status = 'active' $where_clause ORDER BY priority DESC, category, id";
$faqs_result = mysqli_query($conn, $faqs_query);

// Group FAQs by category
$grouped_faqs = [];
while ($faq = mysqli_fetch_assoc($faqs_result)) {
    $grouped_faqs[$faq['category']][] = $faq;
}

// Include header
require_once __DIR__ . '/includes/templates/header.php';
?>

<!-- Hero Section -->
<section class="hero-section bg-light py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold">Frequently Asked Questions</h1>
                <p class="lead text-muted">Find answers to common questions about real estate and our services.</p>
                <div class="mt-4">
                    <form class="search-form" id="faqSearch">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search FAQs..." id="searchInput">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="<?= get_asset_url('images/faq-hero.jpg') ?>" alt="FAQ Hero" class="img-fluid rounded-3 shadow-lg">
            </div>
        </div>
    </div>
</section>

<!-- Breadcrumb -->
<nav class="bg-light border-bottom py-2">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item active">FAQ</li>
        </ol>
    </div>
</nav>

<!-- FAQ Content -->
<main id="main-content" class="py-5">
    <div class="container">
        <!-- Category Filter -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="faq-filter text-center">
                    <button class="btn <?= $current_category === 'all' ? 'btn-primary' : 'btn-outline-primary' ?> me-2 mb-2" 
                            onclick="window.location.href='faq'">
                        All Categories
                    </button>
                    <?php foreach ($categories as $category): ?>
                    <button class="btn <?= $current_category === $category ? 'btn-primary' : 'btn-outline-primary' ?> me-2 mb-2"
                            onclick="window.location.href='faq?category=<?= urlencode($category) ?>'">
                        <?= htmlspecialchars(ucwords($category)) ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- FAQ Accordion -->
        <div class="row">
            <div class="col-12">
                <div class="accordion faq-accordion" id="faqAccordion">
                    <?php foreach ($grouped_faqs as $category => $faqs): ?>
                    <div class="faq-category mb-4">
                        <h3 class="category-title mb-3"><?= htmlspecialchars(ucwords($category)) ?></h3>
                        <?php foreach ($faqs as $index => $faq): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#faq-<?= $faq['id'] ?>">
                                    <?= htmlspecialchars($faq['question']) ?>
                                </button>
                            </h2>
                            <div id="faq-<?= $faq['id'] ?>" class="accordion-collapse collapse" 
                                 data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <?= nl2br(htmlspecialchars($faq['answer'])) ?>
                                    <?php if (!empty($faq['related_links'])): ?>
                                    <div class="related-links mt-3">
                                        <h6>Related Resources:</h6>
                                        <ul class="list-unstyled">
                                            <?php foreach (json_decode($faq['related_links'], true) as $link): ?>
                                            <li>
                                                <a href="<?= htmlspecialchars($link['url']) ?>" class="text-primary">
                                                    <i class="fas fa-external-link-alt me-2"></i>
                                                    <?= htmlspecialchars($link['title']) ?>
                                                </a>
                                            </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if (empty($grouped_faqs)): ?>
                <div class="text-center py-5">
                    <h3>No FAQs found</h3>
                    <p class="text-muted">Please try selecting a different category or check back later.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Contact CTA -->
        <div class="contact-cta text-center mt-5 py-5 bg-light rounded-3">
            <h3>Didn't Find Your Answer?</h3>
            <p class="text-muted mb-4">Our team is here to help you with any questions you may have</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="/contact" class="btn btn-primary">Contact Us</a>
                <a href="tel:<?= CONTACT_PHONE ?>" class="btn btn-outline-primary">
                    <i class="fas fa-phone me-2"></i>Call Now
                </a>
            </div>
        </div>
    </div>
</main>

<?php
// Include footer
require_once __DIR__ . '/includes/templates/footer.php';
?>