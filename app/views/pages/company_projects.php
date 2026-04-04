<?php
/**
 * Company Projects Page
 * Display company projects portfolio from sites table
 */
$projects = $projects ?? [];
$grouped_projects = $grouped_projects ?? [];
?>

<!-- Company Projects Hero -->
<section class="hero-section bg-gradient-success text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold mb-4">Our Projects</h1>
                <p class="lead mb-0">Explore our completed and ongoing projects across Uttar Pradesh</p>
            </div>
        </div>
    </div>
</section>

<!-- Company Projects Content -->
<section class="py-5">
    <div class="container">
        <?php if (!empty($projects)): ?>
            <?php foreach ($grouped_projects as $state => $stateProjects): ?>
                <div class="mb-5">
                    <h3 class="mb-4"><i class="fas fa-map-marker-alt text-success me-2"></i><?php echo htmlspecialchars($state); ?></h3>
                    <div class="row">
                        <?php foreach ($stateProjects as $project): ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card project-card h-100 shadow-sm">
                                    <div class="project-image position-relative">
                                        <?php 
                                        $imgPath = '/assets/images/projects/project-default.jpg';
                                        if (!empty($project->amenities) && strpos($project->amenities, 'pool') !== false) {
                                            $imgPath = '/assets/images/projects/project1.jpg';
                                        } elseif (!empty($project->amenities) && strpos($project->amenities, 'park') !== false) {
                                            $imgPath = '/assets/images/projects/project2.jpg';
                                        }
                                        ?>
                                        <img src="<?php echo BASE_URL . $imgPath; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($project->site_name); ?>" style="height: 200px; object-fit: cover;">
                                        <span class="badge bg-success position-absolute top-0 end-0 m-2">
                                            <?php echo ucfirst($project->site_type); ?>
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($project->site_name); ?></h5>
                                        <p class="text-muted mb-2">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <?php echo htmlspecialchars($project->city); ?><?php if (!empty($project->location)) echo ' - ' . htmlspecialchars($project->location); ?>
                                        </p>
                                        <?php if (!empty($project->description)): ?>
                                            <p class="card-text small"><?php echo htmlspecialchars(substr($project->description, 0, 100)); ?>...</p>
                                        <?php endif; ?>
                                        <?php if (!empty($project->total_area)): ?>
                                            <p class="small text-primary mb-2">
                                                <i class="fas fa-expand me-1"></i> <?php echo htmlspecialchars($project->total_area); ?> Acres
                                            </p>
                                        <?php endif; ?>
                                        <a href="<?php echo BASE_URL; ?>/projects/<?php echo strtolower(str_replace(' ', '-', $project->site_name)); ?>" class="btn btn-primary btn-sm">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Fallback to sample projects if no data -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="projects-grid">
                        <h2>Featured Projects</h2>
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <div class="card">
                                    <img src="assets/images/projects/project1.jpg" class="card-img-top" alt="Project 1">
                                    <div class="card-body">
                                        <h5>APS Dream City</h5>
                                        <p class="text-muted">Gorakhpur • Residential</p>
                                        <p>Premium residential project with modern amenities</p>
                                        <a href="#" class="btn btn-primary">View Details</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="card">
                                    <img src="assets/images/projects/project2.jpg" class="card-img-top" alt="Project 2">
                                    <div class="card-body">
                                        <h5>APS Heights</h5>
                                        <p class="text-muted">Lucknow • Commercial</p>
                                        <p>Modern commercial spaces for businesses</p>
                                        <a href="#" class="btn btn-primary">View Details</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="card">
                                    <img src="assets/images/projects/project3.jpg" class="card-img-top" alt="Project 3">
                                    <div class="card-body">
                                        <h5>APS Greens</h5>
                                        <p class="text-muted">Varanasi • Residential</p>
                                        <p>Eco-friendly residential project</p>
                                        <a href="#" class="btn btn-primary">View Details</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
