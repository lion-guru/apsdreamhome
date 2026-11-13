<?php
$pageContent = $pageContent ?? '';
$breadcrumbs = $breadcrumbs ?? [['title' => 'Home', 'url' => BASE_URL], ['title' => 'Cancellation Policy', 'url' => '']];
$pageContent = str_replace(['<h4', '</h4>', '<h5', '</h5>', '<h6', '</h6>'], ['<h2 class="fs-4"', '</h2>', '<h3 class="fs-5"', '</h3>', '<h3 class="fs-6"', '</h3>'], $pageContent);
?>
<style>
    .card-body a.text-primary { color: #0a58ca !important; }
    .card-body a.text-primary:hover { color: #084298 !important; }
</style>
<section class="section-padding py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h1 class="display-6 fw-bold text-primary mb-2">Cancellation Policy</h1>
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <?php foreach ($breadcrumbs as $crumb): ?>
                            <?php if (isset($crumb['url']) && $crumb['url']): ?>
                                <li class="breadcrumb-item"><a href="<?= e($crumb['url']) ?>"><?= e($crumb['title']) ?></a></li>
                            <?php else: ?>
                                <li class="breadcrumb-item active" aria-current="page"><?= e($crumb['title']) ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ol>
                </nav>

                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-body p-4 p-md-5">
                        <?php if (!empty($pageContent)): ?>
                            <?= $pageContent ?>
                        <?php else: ?>
                            <p class="text-muted">Content coming soon. Please check back later.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="text-center mt-5">
                    <a href="<?= BASE_URL ?>" class="btn btn-primary rounded-pill px-5 py-3 shadow-sm">
                        <i class="fas fa-home me-2"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
