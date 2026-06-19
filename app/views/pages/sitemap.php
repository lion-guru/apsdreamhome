<?php
// app/views/pages/sitemap.php
?>

<!-- Hero Section -->
<section class="bg-dark text-white text-center py-5">
    <div class="container">
        <h1 class="display-4 fw-bold"><?= __('sitemap_title') ?></h1>
        <p class="lead"><?= __('sitemap_subtitle') ?></p>
    </div>
</section>

<!-- Breadcrumb -->
<div class="bg-light py-2">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <?php if (isset($breadcrumbs)): ?>
                    <?php foreach ($breadcrumbs as $crumb): ?>
                        <?php if (empty($crumb['url']) || $crumb === end($breadcrumbs)): ?>
                            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($crumb['title']) ?></li>
                        <?php else: ?>
                            <li class="breadcrumb-item"><a href="<?= $crumb['url'] ?>"><?= htmlspecialchars($crumb['title']) ?></a></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><?= __('breadcrumb_home') ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= __('nav_sitemap') ?></li>
                <?php endif; ?>
            </ol>
        </nav>
    </div>
</div>

<section class="section-padding bg-light">
    <div class="container">
        <div class="row g-4">
            <!-- Main Navigation -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
                    <div class="card-header bg-premium text-white py-3">
                        <h5 class="mb-0"><i class="fas fa-compass me-2"></i> <?= __('sitemap_main_pages') ?></h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> <?= __('breadcrumb_home') ?></a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>properties" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> <?= __('nav_properties') ?></a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>projects" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> <?= __('nav_projects') ?></a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>about" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> <?= __('nav_about') ?></a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>contact" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> <?= __('nav_contact') ?></a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>team" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> <?= __('sitemap_our_team') ?></a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Services -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
                    <div class="card-header bg-premium text-white py-3">
                        <h5 class="mb-0"><i class="fas fa-concierge-bell me-2"></i> <?= __('sitemap_our_services') ?></h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>services" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> <?= __('sitemap_all_services') ?></a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>legal" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> <?= __('sitemap_legal_services') ?></a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>bank" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> <?= __('sitemap_bank_details') ?></a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>calc" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> <?= __('sitemap_emi_calculator') ?></a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>commission-calculator" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> <?= __('sitemap_commission_calculator') ?></a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- User Area -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
                    <div class="card-header bg-premium text-white py-3">
                        <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i> <?= __('sitemap_user_portal') ?></h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>login" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> <?= __('nav_login') ?></a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>register" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> <?= __('nav_register') ?></a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>dashboard" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> <?= __('sitemap_user_dashboard') ?></a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>careers" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> <?= __('nav_careers') ?></a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>mlm-opportunity" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> <?= __('sitemap_mlm_opportunity') ?></a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Resources -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
                    <div class="card-header bg-dark text-white py-3">
                        <h5 class="mb-0"><i class="fas fa-book me-2"></i> <?= __('sitemap_resources') ?></h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>news" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> <?= __('sitemap_latest_news') ?></a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>gallery" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> <?= __('sitemap_project_gallery') ?></a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>faq" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> <?= __('sitemap_help_faq') ?></a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>budhacity" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> <?= __('sitemap_budha_city') ?></a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>lucknow-project" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> <?= __('sitemap_lucknow_project') ?></a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Support & Legal -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
                    <div class="card-header bg-dark text-white py-3">
                        <h5 class="mb-0"><i class="fas fa-gavel me-2"></i> <?= __('sitemap_support_legal') ?></h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>privacy-policy" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> <?= __('sitemap_privacy_policy') ?></a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>legal" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> <?= __('sitemap_legal_docs') ?></a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>sitemap" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> <?= __('sitemap_site_map') ?></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-5">
            <a href="<?= BASE_URL ?>" class="btn btn-premium btn-lg px-5"><?= __('breadcrumb_home') ?></a>
        </div>
    </div>
</section>