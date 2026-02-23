<?php
// app/views/pages/sitemap.php
?>

<!-- Hero Section -->
<section class="bg-dark text-white text-center py-5">
    <div class="container">
        <h1 class="display-4 fw-bold">Sitemap</h1>
        <p class="lead">Navigate through our website with ease.</p>
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
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Sitemap</li>
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
                        <h5 class="mb-0"><i class="fas fa-compass me-2"></i> Main Pages</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> Home</a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>properties" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> Properties</a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>projects" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> Projects</a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>about" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> About Us</a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>contact" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> Contact</a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>team" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> Our Team</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Services -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
                    <div class="card-header bg-premium text-white py-3">
                        <h5 class="mb-0"><i class="fas fa-concierge-bell me-2"></i> Our Services</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>services" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> All Services</a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>legal" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> Legal Services</a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>bank" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> Bank Details</a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>calc" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> EMI Calculator</a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>commission-calculator" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> Commission Calculator</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- User Area -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
                    <div class="card-header bg-premium text-white py-3">
                        <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i> User Portal</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>login" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> Login</a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>register" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> Register</a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>dashboard" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> User Dashboard</a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>careers" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> Careers</a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>mlm-opportunity" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> MLM Opportunity</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Resources -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
                    <div class="card-header bg-dark text-white py-3">
                        <h5 class="mb-0"><i class="fas fa-book me-2"></i> Resources</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>news" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> Latest News</a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>gallery" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> Project Gallery</a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>faq" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> Help & FAQ</a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>budhacity" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> Budha City Project</a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>lucknow-project" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> Lucknow Project</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Support & Legal -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
                    <div class="card-header bg-dark text-white py-3">
                        <h5 class="mb-0"><i class="fas fa-gavel me-2"></i> Support & Legal</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>privacy-policy" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> Privacy Policy</a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>legal" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> Legal Documentation</a></li>
                            <li class="list-group-item border-0"><a href="<?= BASE_URL ?>sitemap" class="text-decoration-none text-dark d-block py-1 hover-premium"><i class="fas fa-chevron-right small me-2 text-premium"></i> Site Map</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-5">
            <a href="<?= BASE_URL ?>" class="btn btn-premium btn-lg px-5">Back to Home</a>
        </div>
    </div>
</section>