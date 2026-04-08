<?php
/**
 * Projects by Location Page
 */
$projects = $projects ?? [];
$location = $location ?? '';
$locationName = ucfirst($location);
?>
<!-- Hero Section -->
<section class="py-5 text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container text-center py-5">
        <h1 class="display-4 fw-bold mb-3"><i class="fas fa-map-marker-alt me-3"></i><?php echo $locationName; ?> Projects</h1>
        <p class="lead">Explore our premium projects in <?php echo $locationName; ?></p>
    </div>
</section>

<!-- Projects Grid -->
<section class="py-5">
    <div class="container">
        <?php if (!empty($projects)): ?>
            <div class="row">
                <?php foreach ($projects as $project): 
                    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $project->site_name));
                    $imgPath = '/assets/images/placeholder/property.svg';
                    if (stripos($project->site_name, 'Suryoday') !== false) {
                        $imgPath = '/assets/images/projects/gorakhpur/suryoday.jpg';
                    } elseif (stripos($project->site_name, 'Raghunath') !== false) {
                        $imgPath = '/assets/images/projects/gorakhpur/raghunath nagri motiram.JPG';
                    } elseif (stripos($project->site_name, 'Braj') !== false || stripos($project->site_name, 'Radha') !== false) {
                        $imgPath = '/assets/images/projects/gorakhpur/suryoday1.jpeg';
                    } elseif (stripos($project->site_name, 'Budh') !== false) {
                        $imgPath = '/assets/images/projects/kushinagar/budh-bihar.jpg';
                    } elseif (stripos($project->site_name, 'Awadhpuri') !== false) {
                        $imgPath = '/assets/images/projects/lucknow/awadhpuri.jpg';
                    } elseif (stripos($project->site_name, 'Ganga') !== false) {
                        $imgPath = '/assets/images/projects/varanasi/ganga-nagri.jpg';
                    }
                ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 shadow-sm border-0 overflow-hidden">
                        <div class="position-relative" style="height: 200px;">
                            <img src="<?php echo BASE_URL . $imgPath; ?>" class="w-100 h-100" alt="<?php echo htmlspecialchars($project->site_name); ?>" style="object-fit: cover;" onerror="this.src='<?php echo BASE_URL; ?>/assets/images/placeholder/property.svg'">
                            <div class="position-absolute top-0 start-0 m-2">
                                <span class="badge bg-<?php echo $project->status === 'active' ? 'success' : 'primary'; ?>">
                                    <?php echo $project->status === 'active' ? 'Available' : 'Completed'; ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold"><?php echo htmlspecialchars($project->site_name); ?></h5>
                            <p class="text-muted small mb-2">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                <?php echo htmlspecialchars(($project->location ?? '') . ', ' . ($project->district ?? '')); ?>
                            </p>
                            <?php if (!empty($project->total_area)): ?>
                                <p class="small text-primary mb-2">
                                    <i class="fas fa-expand me-1"></i> <?php echo htmlspecialchars($project->total_area); ?> Acres
                                </p>
                            <?php endif; ?>
                            <?php if (!empty($project->description)): ?>
                                <p class="card-text small text-muted"><?php echo htmlspecialchars(substr($project->description, 0, 100)); ?>...</p>
                            <?php endif; ?>
                            <div class="d-flex gap-2 mt-3">
                                <a href="<?php echo BASE_URL; ?>/projects/<?php echo $slug; ?>" class="btn btn-primary btn-sm flex-grow-1">
                                    <i class="fas fa-eye me-1"></i>View Details
                                </a>
                                <a href="https://wa.me/919277121112?text=Hi, I'm interested in <?php echo urlencode($project->site_name); ?>" target="_blank" class="btn btn-success btn-sm">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-building fa-4x text-muted mb-4"></i>
                <h3 class="text-muted">No Projects in <?php echo $locationName; ?></h3>
                <p class="text-muted">We're expanding to more locations. Contact us for updates!</p>
                <a href="<?php echo BASE_URL; ?>/company/projects" class="btn btn-primary">View All Projects</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Other Locations -->
<section class="py-5 bg-light">
    <div class="container">
        <h4 class="mb-4 text-center">Explore Other Locations</h4>
        <div class="row justify-content-center">
            <div class="col-auto">
                <div class="d-flex gap-2 flex-wrap justify-content-center">
                    <a href="<?php echo BASE_URL; ?>/projects/gorakhpur" class="btn btn-outline-primary <?php echo $location === 'gorakhpur' ? 'active' : ''; ?>">Gorakhpur</a>
                    <a href="<?php echo BASE_URL; ?>/projects/lucknow" class="btn btn-outline-primary <?php echo $location === 'lucknow' ? 'active' : ''; ?>">Lucknow</a>
                    <a href="<?php echo BASE_URL; ?>/projects/kushinagar" class="btn btn-outline-primary <?php echo $location === 'kushinagar' ? 'active' : ''; ?>">Kushinagar</a>
                    <a href="<?php echo BASE_URL; ?>/projects/varanasi" class="btn btn-outline-primary <?php echo $location === 'varanasi' ? 'active' : ''; ?>">Varanasi</a>
                    <a href="<?php echo BASE_URL; ?>/company/projects" class="btn btn-primary">All Projects</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-5 text-center text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
        <h3>Interested in <?php echo $locationName; ?> Properties?</h3>
        <p class="mb-4">Contact us for site visits and expert guidance</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="tel:+919277121112" class="btn btn-warning btn-lg">
                <i class="fas fa-phone me-2"></i>Call Now
            </a>
            <a href="https://wa.me/919277121112" target="_blank" class="btn btn-success btn-lg">
                <i class="fab fa-whatsapp me-2"></i>WhatsApp
            </a>
        </div>
    </div>
</section>
