<?php
// app/views/pages/privacy.php
$breadcrumbs = $breadcrumbs ?? [['title' => 'Home', 'url' => BASE_URL], ['title' => 'Privacy Policy', 'url' => '']];
?>
<!-- Hero Section -->
<section class="privacy-hero-section section-padding bg-primary text-white text-center rounded-bottom-4 py-5" data-aos="fade-down">
    <div class="container py-4">
        <h1 class="display-5 fw-bold mb-2"><?php echo htmlspecialchars($page_title ?? 'Privacy Policy'); ?></h1>
        <p class="lead mb-0">How we collect, use, and protect your data</p>
    </div>
</section>

<!-- Breadcrumb -->
<div class="bg-light py-2">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <?php foreach ($breadcrumbs as $crumb): ?>
                    <?php if (isset($crumb['url'])): ?>
                        <li class="breadcrumb-item"><a href="<?= $crumb['url'] ?>"><?= $crumb['title'] ?></a></li>
                    <?php else: ?>
                        <li class="breadcrumb-item active" aria-current="page"><?= $crumb['title'] ?></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </nav>
    </div>
</div>

<section class="section-padding py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden" data-aos="fade-up">
                    <div class="card-body p-4 p-md-5">
                        <?php if (!empty($pageContent)): ?>
                            <?= $pageContent ?>
                        <?php else: ?>
                            <p>Privacy policy content is being updated. Please check back later.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="text-center mt-5" data-aos="fade-up" data-aos-delay="100">
                    <a href="<?= BASE_URL ?>" class="btn btn-primary rounded-pill px-5 py-3 shadow-sm">
                        <i class="fas fa-home me-2"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>