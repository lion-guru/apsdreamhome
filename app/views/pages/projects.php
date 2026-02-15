<?php
/**
 * Projects View - APS Dream Homes
 * Modern layout with filtering and hover effects
 */
?>

<!-- Hero Section -->
<section class="projects-hero bg-dark text-white py-5 mb-0 position-relative overflow-hidden">
    <div class="container py-5 mt-4 text-center" data-aos="fade-up">
        <h1 class="display-3 fw-bold mb-3">Our Landmark Projects</h1>
        <p class="lead opacity-75 mx-auto header-desc">Explore our premium residential and commercial developments designed for modern living.</p>
    </div>
</section>

<div class="bg-light py-5">
    <div class="container">
        <!-- Project Categories / Filter -->
        <div class="row mb-5" data-aos="fade-up">
            <div class="col-12 text-center">
                <div class="d-inline-flex shadow-sm rounded-pill p-1 bg-white border">
                    <button class="btn btn-primary rounded-pill px-4 filter-btn active" data-filter="all">All Projects</button>
                    <button class="btn btn-white border-0 rounded-pill px-4 filter-btn" data-filter="ongoing">Ongoing</button>
                    <button class="btn btn-white border-0 rounded-pill px-4 filter-btn" data-filter="completed">Completed</button>
                </div>
            </div>
        </div>

        <!-- Projects Grid -->
        <div class="row g-4">
            <?php if (!empty($projects)): ?>
                <?php foreach ($projects as $index => $project): ?>
                <div class="col-lg-4 col-md-6 project-item" data-aos="fade-up" data-aos-delay="<?= ($index % 3) * 100 ?>" data-category="<?= strtolower($project['status']) ?>">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden project-card">
                        <div class="position-relative overflow-hidden">
                            <img src="<?= h($project['image'] ?? 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=800&q=80') ?>" class="card-img-top project-img" alt="<?= h($project['title']) ?>" style="height: 280px; object-fit: cover;">
                            <div class="position-absolute top-0 end-0 m-3">
                                <span class="badge bg-<?= ($project['status'] == 'Ongoing') ? 'warning' : 'success' ?> text-dark rounded-pill px-3 py-2 shadow-sm fw-bold">
                                    <i class="fas <?= ($project['status'] == 'Ongoing') ? 'fa-clock' : 'fa-check-circle' ?> me-1"></i>
                                    <?= h($project['status']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <h4 class="fw-bold mb-2 text-dark"><?= h($project['title']) ?></h4>
                            <p class="text-muted small mb-3"><i class="fas fa-map-marker-alt text-primary me-2"></i> <?= h($project['location']) ?></p>
                            <p class="card-text text-secondary mb-4"><?= h(substr($project['description'] ?? 'Experience luxury living with modern amenities and prime location.', 0, 120)) ?>...</p>

                            <div class="row g-2 mb-4">
                                <div class="col-6">
                                    <div class="p-2 bg-light rounded-3 text-center">
                                        <p class="text-muted small mb-0">Area</p>
                                        <p class="fw-bold mb-0 small"><?= $project['area'] ?? '5 Acres' ?></p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 bg-light rounded-3 text-center">
                                        <p class="text-muted small mb-0">Units</p>
                                        <p class="fw-bold mb-0 small"><?= $project['units'] ?? '200+ Units' ?></p>
                                    </div>
                                </div>
                            </div>

                            <a href="project-details.php?id=<?= $project['id'] ?>" class="btn btn-primary w-100 rounded-pill fw-bold">Project Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-city fa-4x text-muted opacity-25"></i>
                    </div>
                    <h3 class="text-muted fw-bold">No projects found</h3>
                    <p class="text-muted">Stay tuned for our upcoming landmark developments!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .projects-hero {
        background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
        padding-bottom: 100px !important;
    }
    .header-desc {
        max-width: 700px;
    }
    .project-card {
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        border: 1px solid rgba(0,0,0,0.05) !important;
    }
    .project-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 1.5rem 4rem rgba(0,0,0,0.15) !important;
    }
    .project-img {
        transition: transform 0.6s ease;
    }
    .project-card:hover .project-img {
        transform: scale(1.1);
    }
    .btn-white {
        background: white;
        color: #333;
    }
    .btn-white:hover {
        background: #f8f9fa;
    }
</style>
