<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/apsdreamhome/');
}

// Ensure variables are defined to prevent errors
if (!isset($projects)) { $projects = []; }
if (!isset($cities)) { $cities = []; }
if (!isset($current_city)) { $current_city = ''; }
if (!isset($project_types)) { $project_types = []; }

/**
 * Projects Index View
 * Shows all projects with filtering and search
 */

// Set page title and description for layout
$page_title = $title ?? 'Our Projects - APS Dream Home';
$page_description = 'Discover exceptional residential and commercial spaces crafted with precision and care';
?>

<div class="container-fluid py-5">
    <!-- Page Header -->
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="display-4 fw-bold text-primary mb-3">Our Premium Projects</h1>
            <p class="lead text-muted">Discover exceptional residential and commercial spaces crafted with precision and care</p>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search Projects</label>
                            <input type="text" class="form-control" id="search" name="search"
                                   placeholder="Search by project name, location..."
                                   value="<?= htmlspecialchars($current_search ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="city" class="form-label">City</label>
                            <select class="form-select" id="city" name="city">
                                <option value="">All Cities</option>
                                <?php foreach ($cities as $city): ?>
                                    <option value="<?= htmlspecialchars($city) ?>"
                                            <?= ($current_city === $city) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($city) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="project_type" class="form-label">Project Type</label>
                            <select class="form-select" id="project_type" name="project_type">
                                <option value="">All Types</option>
                                <?php foreach ($project_types as $type): ?>
                                    <option value="<?= htmlspecialchars($type) ?>">
                                        <?= htmlspecialchars(ucfirst($type)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-2"></i>Search
                            </button>
                            <a href="/projects" class="btn btn-outline-secondary">
                                <i class="fas fa-undo me-2"></i>Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects Grid -->
    <div class="row">
        <?php if (empty($projects)): ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h4>No Projects Found</h4>
                    <p class="text-muted">Try adjusting your search criteria or browse all our projects.</p>
                    <a href="/projects" class="btn btn-primary">View All Projects</a>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($projects as $project): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card project-card h-100 shadow-sm">
                        <!-- Project Image -->
                        <div class="position-relative">
                            <?php if (!empty($project['gallery_images'])): ?>
                                <img src="/uploads/projects/<?= htmlspecialchars($project['gallery_images'][0]) ?>"
                                     class="card-img-top" alt="<?= htmlspecialchars($project['project_name']) ?>"
                                     style="height: 250px; object-fit: cover;">
                            <?php else: ?>
                                <img src="/assets/images/no-project-image.jpg"
                                     class="card-img-top" alt="No Image Available"
                                     style="height: 250px; object-fit: cover;">
                            <?php endif; ?>

                            <!-- Project Status Badge -->
                            <div class="position-absolute top-0 end-0 m-3">
                                <?php if ($project['is_featured']): ?>
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-star me-1"></i>Featured
                                    </span>
                                <?php endif; ?>
                                <span class="badge bg-<?= $project['project_status'] === 'completed' ? 'success' : 'info' ?>">
                                    <?= ucfirst($project['project_status']) ?>
                                </span>
                            </div>

                            <!-- Price Badge -->
                            <div class="position-absolute bottom-0 start-0 m-3">
                                <span class="badge bg-primary fs-6">
                                    ₹<?= number_format($project['base_price'], 0) ?> onwards
                                </span>
                            </div>
                        </div>

                        <div class="card-body d-flex flex-column">
                            <!-- Project Info -->
                            <div class="mb-3">
                                <h5 class="card-title mb-2">
                                    <a href="/projects/<?= htmlspecialchars($project['project_code']) ?>"
                                       class="text-decoration-none text-dark">
                                        <?= htmlspecialchars($project['project_name']) ?>
                                    </a>
                                </h5>
                                <p class="card-text text-muted mb-2">
                                    <i class="fas fa-map-marker-alt me-2"></i>
                                    <?= htmlspecialchars($project['location'] . ', ' . $project['city']) ?>
                                </p>
                                <p class="card-text small">
                                    <?= htmlspecialchars(substr($project['short_description'] ?? $project['description'], 0, 100)) ?>...
                                </p>
                            </div>

                            <!-- Project Stats -->
                            <div class="row text-center mb-3">
                                <div class="col-4">
                                    <small class="text-muted d-block">Total Area</small>
                                    <strong><?= number_format($project['total_area'] ?? 0) ?> sq ft</strong>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block">Available Plots</small>
                                    <strong><?= $project['available_plots'] ?? 0 ?></strong>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block">Price/sqft</small>
                                    <strong>₹<?= number_format($project['price_per_sqft'] ?? 0) ?></strong>
                                </div>
                            </div>

                            <!-- Amenities Preview -->
                            <?php if (!empty($project['amenities'])): ?>
                                <div class="mb-3">
                                    <small class="text-muted">Key Amenities:</small>
                                    <div class="amenities-preview">
                                        <?php foreach (array_slice($project['amenities'], 0, 3) as $amenity): ?>
                                            <span class="badge bg-light text-dark me-1 mb-1">
                                                <i class="fas fa-check me-1"></i><?= htmlspecialchars($amenity) ?>
                                            </span>
                                        <?php endforeach; ?>
                                        <?php if (count($project['amenities']) > 3): ?>
                                            <span class="badge bg-light text-dark">
                                                +<?= count($project['amenities']) - 3 ?> more
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Action Buttons -->
                            <div class="mt-auto">
                                <div class="d-grid gap-2">
                                    <a href="/projects/<?= htmlspecialchars($project['project_code']) ?>"
                                       class="btn btn-primary">
                                        <i class="fas fa-eye me-2"></i>View Details
                                    </a>
                                    <button class="btn btn-outline-primary"
                                            onclick="showInterest('<?= htmlspecialchars($project['project_code']) ?>')">
                                        <i class="fas fa-heart me-2"></i>I'm Interested
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Load More Button (if needed) -->
    <?php if (!empty($projects) && count($projects) >= 12): ?>
        <div class="row">
            <div class="col-12 text-center">
                <button class="btn btn-outline-primary btn-lg" onclick="loadMoreProjects()">
                    <i class="fas fa-plus me-2"></i>Load More Projects
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Interest Modal -->
<div class="modal fade" id="interestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Express Interest</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="interestForm">
                <div class="modal-body">
                    <input type="hidden" id="projectCode" name="project_code">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number *</label>
                        <input type="tel" class="form-control" id="phone" name="phone" required>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Message</label>
                        <textarea class="form-control" id="message" name="message" rows="3"
                                  placeholder="Tell us about your requirements..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Interest</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showInterest(projectCode) {
    document.getElementById('projectCode').value = projectCode;
    $('#interestModal').modal('show');
}

function loadMoreProjects() {
    // In a real implementation, you would load more projects via AJAX
    alert('Load more functionality would be implemented here.');
}

// Form submission
document.getElementById('interestForm')?.addEventListener('submit', function(e) {
    e.preventDefault();

    // In a real implementation, you would submit this data to the server
    alert('Thank you for your interest! We will contact you soon.');

    // Close modal and reset form
    $('#interestModal').modal('hide');
    this.reset();
});
</script>

<style>
.project-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: none;
}

.project-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.amenities-preview {
    max-height: 80px;
    overflow: hidden;
}

.card-img-top {
    transition: transform 0.3s ease;
}

.project-card:hover .card-img-top {
    transform: scale(1.05);
}

.stats-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.stats-card {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.filter-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}

@media (max-width: 768px) {
    .display-4 {
        font-size: 2rem;
    }

    .project-card .card-img-top {
        height: 200px;
    }
}
</style>
