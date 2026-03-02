<?php
$page_title = $data['title'] ?? 'Projects - APS Dream Home';
$page_description = $data['description'] ?? 'Explore our ongoing and completed real estate projects across Uttar Pradesh';
?>

<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h6 class="text-uppercase text-primary fw-bold">Our Projects</h6>
            <h2 class="display-5 fw-bold">Building Dreams, Creating Reality</h2>
            <div class="mx-auto bg-primary mt-3" style="height:4px;width:80px;border-radius:2px;"></div>
        </div>

        <!-- Projects Grid -->
        <div class="row g-4">
            <?php if (!empty($data['projects'])): ?>
                <?php foreach ($data['projects'] as $project): ?>
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="row g-0">
                                <div class="col-md-5">
                                    <img src="<?php echo !empty($project->image_path) ? htmlspecialchars($project->image_path) : BASE_URL . '/assets/images/project-placeholder.jpg'; ?>"
                                        class="img-fluid rounded-start h-100 object-fit-cover"
                                        alt="<?php echo htmlspecialchars($project->name); ?>">
                                </div>
                                <div class="col-md-7">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="card-title fw-bold"><?php echo htmlspecialchars($project->name); ?></h5>
                                            <span class="badge bg-<?php echo $project->status === 'Completed' ? 'success' : 'primary'; ?>">
                                                <?php echo htmlspecialchars($project->status); ?>
                                            </span>
                                        </div>
                                        <p class="text-muted small mb-2">
                                            <i class="fas fa-map-marker-alt text-primary me-2"></i><?php echo htmlspecialchars($project->location); ?>
                                        </p>
                                        <p class="text-muted small mb-3">
                                            <i class="fas fa-building text-primary me-2"></i><?php echo htmlspecialchars($project->type); ?>
                                        </p>
                                        <p class="card-text"><?php echo htmlspecialchars($project->description); ?></p>
                                        
                                        <?php if ($project->status === 'Ongoing'): ?>
                                            <div class="progress mb-3" style="height: 8px;">
                                                <div class="progress-bar bg-primary" role="progressbar" 
                                                     style="width: <?php echo htmlspecialchars($project->completion); ?>"
                                                     aria-valuenow="<?php echo str_replace('%', '', $project->completion); ?>" 
                                                     aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                            <p class="text-muted small mb-3">Completion: <?php echo htmlspecialchars($project->completion); ?></p>
                                        <?php endif; ?>
                                        
                                        <a href="<?php echo BASE_URL; ?>projects/<?php echo $project->id; ?>" class="btn btn-outline-primary btn-sm">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted lead">No projects available at the moment.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Project Statistics -->
        <div class="row mt-5">
            <div class="col-md-3 col-6 text-center mb-4">
                <div class="display-6 fw-bold text-primary">15+</div>
                <div class="text-muted">Projects Completed</div>
            </div>
            <div class="col-md-3 col-6 text-center mb-4">
                <div class="display-6 fw-bold text-primary">8</div>
                <div class="text-muted">Ongoing Projects</div>
            </div>
            <div class="col-md-3 col-6 text-center mb-4">
                <div class="display-6 fw-bold text-primary">5000+</div>
                <div class="text-muted">Happy Families</div>
            </div>
            <div class="col-md-3 col-6 text-center mb-4">
                <div class="display-6 fw-bold text-primary">10+</div>
                <div class="text-muted">Years Experience</div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Our Projects -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <h6 class="text-uppercase text-primary fw-bold">Why Choose Our Projects</h6>
            <h2 class="display-5 fw-bold">Excellence in Every Detail</h2>
            <div class="mx-auto bg-primary mt-3" style="height:4px;width:80px;border-radius:2px;"></div>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="text-center">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-certificate fa-2x"></i>
                    </div>
                    <h5 class="fw-bold">Quality Assurance</h5>
                    <p class="text-muted">Premium construction materials and quality checks at every stage.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-map-marked-alt fa-2x"></i>
                    </div>
                    <h5 class="fw-bold">Prime Locations</h5>
                    <p class="text-muted">Strategic locations with excellent connectivity and infrastructure.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-handshake fa-2x"></i>
                    </div>
                    <h5 class="fw-bold">Transparent Deals</h5>
                    <p class="text-muted">Honest pricing and clear documentation with no hidden charges.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 bg-primary text-white text-center">
    <div class="container">
        <h2 class="display-5 fw-bold mb-4">Interested in Our Projects?</h2>
        <p class="lead mb-5 opacity-75">Get in touch to know more about our ongoing and upcoming projects.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="<?php echo BASE_URL; ?>contact" class="btn btn-light btn-lg px-5 rounded-pill text-primary fw-bold">Contact Us</a>
            <a href="<?php echo BASE_URL; ?>properties" class="btn btn-outline-light btn-lg px-5 rounded-pill">View Properties</a>
        </div>
    </div>
</section>