<?php
/**
 * Company Projects Page
 * Display company projects portfolio from sites table
 */
$projects = $projects ?? [];
$grouped_projects = $grouped_projects ?? $grouped ?? [];

// Build state > district > project structure if not already done
if (empty($grouped_projects) && !empty($projects)) {
    $grouped_projects = [];
    foreach ($projects as $project) {
        $state = $project->state ?? 'Other';
        $district = $project->district ?? 'Other';
        if (!isset($grouped_projects[$state])) {
            $grouped_projects[$state] = [];
        }
        if (!isset($grouped_projects[$state][$district])) {
            $grouped_projects[$state][$district] = [];
        }
        $grouped_projects[$state][$district][] = $project;
    }
}
?>

<!-- Company Projects Hero -->
<section class="py-5 text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold mb-4"><i class="fas fa-building me-3"></i>Our Projects</h1>
                <p class="lead mb-0">Explore our completed and ongoing projects across Uttar Pradesh</p>
            </div>
        </div>
    </div>
</section>

<!-- Company Projects Content -->
<section class="py-5">
    <div class="container">
        <?php if (!empty($grouped_projects)): ?>
            <?php foreach ($grouped_projects as $state => $districts): ?>
                <div class="mb-5">
                    <!-- State Header -->
                    <div class="d-flex align-items-center mb-4">
                        <div class="me-3">
                            <span class="badge bg-primary rounded-pill px-3 py-2">
                                <i class="fas fa-map me-1"></i><?php echo htmlspecialchars($state); ?>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <hr class="m-0">
                        </div>
                    </div>
                    
                    <?php foreach ($districts as $district => $districtProjects): ?>
                        <!-- District Header -->
                        <h4 class="text-secondary mb-3 ms-4">
                            <i class="fas fa-building me-2"></i><?php echo htmlspecialchars($district); ?>
                            <span class="badge bg-secondary ms-2"><?php echo count($districtProjects); ?> Projects</span>
                        </h4>
                        
                        <div class="row ms-4">
                            <?php foreach ($districtProjects as $project): 
                                $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $project->site_name));
                                $district = strtolower($project->district ?? 'gorakhpur');
                                $imgPath = '/assets/images/placeholder/property.svg';
                                if (stripos($project->site_name, 'Suryoday') !== false) {
                                    $imgPath = '/assets/images/projects/gorakhpur/suryoday.jpg';
                                } elseif (stripos($project->site_name, 'Raghunath') !== false) {
                                    $imgPath = '/assets/images/projects/gorakhpur/raghunath nagri motiram.JPG';
                                } elseif (stripos($project->site_name, 'Braj') !== false || stripos($project->site_name, 'Radha') !== false) {
                                    $imgPath = '/assets/images/projects/gorakhpur/suryoday1.jpeg';
                                } elseif (stripos($project->site_name, 'Budh') !== false || stripos($project->site_name, 'Bihar') !== false) {
                                    $imgPath = '/assets/images/projects/' . $district . '/budh-bihar.jpg';
                                } elseif (stripos($project->site_name, 'Awadhpuri') !== false) {
                                    $imgPath = '/assets/images/projects/' . $district . '/awadhpuri.jpg';
                                } elseif (stripos($project->site_name, 'Ganga') !== false) {
                                    $imgPath = '/assets/images/projects/' . $district . '/ganga-nagri.jpg';
                                }
                            ?>
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="card project-card h-100 shadow-sm border-0 overflow-hidden">
                                        <div class="project-image position-relative">
                                            <img src="<?php echo BASE_URL . $imgPath; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($project->site_name); ?>" style="height: 180px; object-fit: cover;" onerror="this.src='<?php echo BASE_URL; ?>/assets/images/placeholder/property.svg'">
                                            <div class="position-absolute top-0 start-0 m-2">
                                                <span class="badge bg-<?php echo $project->status === 'active' ? 'success' : ($project->status === 'completed' ? 'primary' : 'warning'); ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $project->status ?? 'Active')); ?>
                                                </span>
                                            </div>
                                            <div class="position-absolute top-0 end-0 m-2">
                                                <span class="badge bg-dark">
                                                    <?php echo ucfirst($project->site_type ?? 'Residential'); ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <h5 class="card-title fw-bold"><?php echo htmlspecialchars($project->site_name); ?></h5>
                                            <p class="text-muted small mb-2">
                                                <i class="fas fa-map-marker-alt me-1 text-danger"></i>
                                                <?php echo htmlspecialchars($project->location ?? $project->city); ?>
                                            </p>
                                            <?php if (!empty($project->total_area)): ?>
                                                <p class="small text-primary mb-2">
                                                    <i class="fas fa-expand me-1"></i> <?php echo htmlspecialchars($project->total_area); ?> Acres
                                                </p>
                                            <?php endif; ?>
                                            <?php if (!empty($project->description)): ?>
                                                <p class="card-text small text-muted"><?php echo htmlspecialchars(substr($project->description, 0, 80)); ?>...</p>
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
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Fallback message -->
            <div class="text-center py-5">
                <i class="fas fa-building fa-4x text-muted mb-4"></i>
                <h3 class="text-muted">No Projects Available</h3>
                <p class="text-muted">Check back soon for our upcoming projects!</p>
                <a href="<?php echo BASE_URL; ?>/" class="btn btn-primary">
                    <i class="fas fa-home me-2"></i>Go to Homepage
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>
