<?php
$projects = $projects ?? [];
$project_stats = $project_stats ?? [];
$sc = $sc ?? function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; };
$phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112'));
?>
<!-- Hero Section -->
<section class="hero-section text-white text-center py-5" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-4" data-aos="fade-up">
                    Our <span class="text-warning">Projects</span>
                </h1>
                <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
                    Discover premium residential and commercial real estate projects by APS Dream Home.
                </p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="#projects-container" class="btn btn-light btn-lg px-4 py-3">
                        <i class="fas fa-building me-2"></i>Explore Projects
                    </a>
                    <a href="tel:<?= e($phoneRaw) ?>" class="btn btn-outline-light btn-lg px-4 py-3">
                        <i class="fas fa-phone me-2"></i>Contact Us
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Breadcrumb -->
<div class="bg-light py-2">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><?= __('nav.menu.home') ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Projects</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Statistics -->
<section class="py-4 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 col-6 mb-3">
                <div class="p-3">
                    <h3 class="fw-bold text-primary mb-1"><?= e($project_stats['total_projects'] ?? 0) ?></h3>
                    <small class="text-muted">Total Projects</small>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="p-3">
                    <h3 class="fw-bold text-success mb-1"><?= e($project_stats['completed'] ?? 0) ?></h3>
                    <small class="text-muted">Completed</small>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="p-3">
                    <h3 class="fw-bold text-warning mb-1"><?= e($project_stats['under_construction'] ?? 0) ?></h3>
                    <small class="text-muted">Under Construction</small>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="p-3">
                    <h3 class="fw-bold text-info mb-1"><?= number_format($project_stats['total_plots'] ?? 0) ?></h3>
                    <small class="text-muted">Total Plots</small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Projects Grid -->
<section class="py-5" id="projects-container">
    <div class="container">
        <?php if (!empty($projects)): ?>
            <div class="row">
                <?php foreach ($projects as $project):
                    $imgPath = BASE_URL . '/assets/images/placeholder/property.svg';
                    if (!empty($project['images'][0])) {
                        $imgPath = BASE_URL . '/uploads/projects/' . $project['images'][0];
                    }
                    $statusClass = match($project['status'] ?? '') {
                        'completed' => 'success',
                        'under_construction' => 'warning',
                        'planning' => 'info',
                        'delayed' => 'danger',
                        default => 'secondary',
                    };
                    $statusLabel = ucwords(str_replace('_', ' ', $project['status'] ?? 'planning'));
                ?>
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up">
                    <div class="card h-100 shadow-sm border-0 overflow-hidden">
                        <div class="position-relative">
                            <img src="<?= e($imgPath) ?>" class="card-img-top" alt="<?= e($project['name']) ?>"
                                 style="height: 220px; object-fit: cover;"
                                 onerror="this.src='<?= BASE_URL ?>/assets/images/placeholder/property.svg'">
                            <div class="position-absolute top-0 start-0 m-2 d-flex gap-1">
                                <span class="badge bg-<?= $statusClass ?>"><?= e($statusLabel) ?></span>
                                <?php if ($project['is_featured']): ?>
                                    <span class="badge bg-warning text-dark"><i class="fas fa-star"></i> Featured</span>
                                <?php endif; ?>
                                <?php if ($project['is_hot_deal']): ?>
                                    <span class="badge bg-danger"><i class="fas fa-fire"></i> Hot Deal</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($project['project_type']): ?>
                                <div class="position-absolute top-0 end-0 m-2">
                                    <span class="badge bg-dark"><?= e(ucfirst($project['project_type'])) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-2"><?= e($project['name']) ?></h5>
                            <?php if ($project['developer_name']): ?>
                                <p class="text-muted small mb-2"><i class="fas fa-building me-1"></i><?= e($project['developer_name']) ?></p>
                            <?php endif; ?>
                            <p class="text-muted small mb-2"><i class="fas fa-map-marker-alt me-1"></i><?= e($project['location'] ?: $project['address']) ?></p>
                            <?php if ($project['description']): ?>
                                <p class="card-text text-muted small"><?= e(mb_substr($project['description'], 0, 120)) ?>...</p>
                            <?php endif; ?>
                            <?php if ($project['price_range_min'] > 0 || $project['price_range_max'] > 0): ?>
                                <div class="mb-2">
                                    <span class="fw-bold text-primary fs-5">
                                        ₹<?= number_format($project['price_range_min'] / 100000, 1) ?>L
                                    </span>
                                    <?php if ($project['price_range_max'] > $project['price_range_min']): ?>
                                        <span class="text-muted"> - ₹<?= number_format($project['price_range_max'] / 100000, 1) ?>L</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <?php if ($project['total_plots'] > 0): ?>
                                    <small class="text-muted"><i class="fas fa-map me-1"></i><?= $project['total_plots'] ?> plots</small>
                                <?php endif; ?>
                                <?php if ($project['available_plots'] > 0): ?>
                                    <small class="text-success"><i class="fas fa-check-circle me-1"></i><?= $project['available_plots'] ?> available</small>
                                <?php endif; ?>
                                <?php if ($project['rera_number']): ?>
                                    <small class="text-info"><i class="fas fa-certificate me-1"></i>RERA</small>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($project['amenities'])): ?>
                                <div class="mb-3">
                                    <?php foreach (array_slice($project['amenities'], 0, 4) as $amenity): ?>
                                        <span class="badge bg-light text-dark me-1 mb-1"><?= e($amenity) ?></span>
                                    <?php endforeach; ?>
                                    <?php if (count($project['amenities']) > 4): ?>
                                        <span class="badge bg-light text-muted">+<?= count($project['amenities']) - 4 ?> more</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-white border-top-0 d-flex justify-content-between align-items-center">
                            <?php if ($project['completion_date']): ?>
                                <small class="text-muted"><i class="fas fa-calendar me-1"></i><?= e($project['completion_date']) ?></small>
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>/projects/<?= e($project['slug']) ?>" class="btn btn-sm btn-primary">
                                View Details <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-building fa-4x text-muted mb-4"></i>
                <h3 class="text-muted">No Projects Available</h3>
                <p class="text-muted">We are currently developing new projects. Check back soon!</p>
                <a href="<?= BASE_URL ?>/colonies" class="btn btn-primary mt-3">
                    <i class="fas fa-map me-2"></i>Explore Our Colonies
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);">
    <div class="container text-center">
        <h2 class="text-white fw-bold mb-3">Interested in Our Projects?</h2>
        <p class="text-white-50 mb-4">Get in touch with our team for site visits, pricing, and booking details.</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="tel:<?= e($phoneRaw) ?>" class="btn btn-warning btn-lg px-4">
                <i class="fas fa-phone me-2"></i>Call Now
            </a>
            <a href="<?= BASE_URL ?>/contact" class="btn btn-outline-light btn-lg px-4">
                <i class="fas fa-envelope me-2"></i>Contact Us
            </a>
        </div>
    </div>
</section>
