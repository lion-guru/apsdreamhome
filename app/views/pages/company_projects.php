<?php if (!isset($sc)) { $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; }; }$phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')); $phoneDisplay = $sc('contact_phone', '<?= $phoneDisplay ?>'); ?>
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
<section class="hero-section text-white py-5 position-relative" class="style-30433">
    <div class="container position-relative">
        <div class="row align-items-center justify-content-center">
            <div class="col-lg-8 text-center">
                <span class="badge px-3 py-2 mb-3" class="style-72717">
                    <i class="fas fa-building me-1"></i> Portfolio
                </span>
                <h1 class="display-4 fw-bold mb-3"><i class="fas fa-building me-3"></i><?= __('cproj_heading', [], 'Our Projects') ?></h1>
                <p class="lead mb-0 opacity-75"><?= __('cproj_subtitle', [], 'Explore our completed and ongoing projects across Uttar Pradesh') ?></p>
            </div>
        </div>
    </div>
</section>

    <!-- Cross-link Section: Also Explore -->
    <section class="py-3" class="style-53819">
        <div class="container">
            <div class="d-flex flex-wrap gap-2 align-items-center" class="style-1563">
                <span class="fw-semibold text-success me-2"><i class="fas fa-compass me-1"></i><?= __('also_explore') ?></span>
                <a href="<?= BASE_URL ?>/properties" class="btn btn-sm btn-outline-primary px-3">
                    <i class="fas fa-building me-1"></i><?= __('properties') ?>
                </a>
                <a href="<?= BASE_URL ?>/plots/browse" class="btn btn-sm btn-outline-primary px-3">
                    <i class="fas fa-vector-square me-1"></i><?= __('plots') ?>
                </a>
                <a href="<?= BASE_URL ?>/colonies" class="btn btn-sm px-3" class="style-66828">
                    <i class="fas fa-city me-1"></i><?= __('colonies') ?>
                </a>
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
                                <i class="fas fa-map me-1"></i><?php echo htmlspecialchars($state ?? ''); ?>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <hr class="m-0">
                        </div>
                    </div>
                    
                    <?php foreach ($districts as $district => $districtProjects): ?>
                        <!-- District Header -->
                        <h4 class="text-secondary mb-3 ms-4">
                            <i class="fas fa-building me-2"></i><?php echo htmlspecialchars($district ?? ''); ?>
                            <span class="badge bg-secondary ms-2"><?php echo count($districtProjects); ?> <?= __('cproj_projects', [], 'Projects') ?></span>
                        </h4>
                        
                        <div class="row ms-4">
                            <?php foreach ($districtProjects as $project): 
                                $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $project->site_name));
                                $district = strtolower($project->district ?? 'gorakhpur');
                                
                                // Dynamic image priority
                                $imgPath = '/assets/images/projects/placeholder/property.svg';
                                $isExternal = false;
                                if (!empty($project->image)) {
                                    if (strpos($project->image, 'http://') === 0 || strpos($project->image, 'https://') === 0) {
                                        $imgPath = $project->image;
                                        $isExternal = true;
                                    } else {
                                        $imgPath = '/' . ltrim($project->image, '/');
                                    }
                                }
                            ?>
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="card property-card h-100 shadow-sm border-0 overflow-hidden">
                                        <div class="card-img-wrapper position-relative">
                                            <img src="<?= $isExternal ? $imgPath : BASE_URL . $imgPath ?>" class="img-fluid card-img-top" alt="<?php echo htmlspecialchars($project->site_name); ?>" class="style-10068" onerror="this.src='<?= BASE_URL ?>/assets/images/projects/placeholder/property.svg'">
                                            <div class="position-absolute top-0 start-0 m-3">
                                                <span class="badge bg-<?php echo $project->status === 'active' ? 'success' : ($project->status === 'completed' ? 'primary' : 'warning'); ?> shadow-sm">
                                                    <?php echo ucfirst(str_replace('_', ' ', $project->status ?? 'Active')); ?>
                                                </span>
                                            </div>
                                            <div class="position-absolute top-0 end-0 m-3">
                                                <span class="badge bg-dark bg-opacity-75 shadow-sm">
                                                    <?php echo ucfirst($project->site_type ?? 'Residential'); ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-body aps-cp-card-body">
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
                                                <a href="<?php echo BASE_URL; ?>/projects/<?php echo e($slug); ?>" class="btn btn-primary btn-sm flex-grow-1">
                                                    <i class="fas fa-eye me-1"></i><?= __('cproj_view_details', [], 'View Details') ?>
                                                </a>
                                                <a href="https://wa.me/<?= $phoneRaw ?>?text=Hi, I'm interested in <?php echo urlencode($project->site_name); ?>" target="_blank" class="btn btn-success btn-sm">
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
                <h3 class="text-muted"><?= __('cproj_empty_title', [], 'No Projects Available') ?></h3>
                <p class="text-muted"><?= __('cproj_empty_desc', [], 'Check back soon for our upcoming projects!') ?></p>
                <a href="<?php echo BASE_URL; ?>/" class="btn btn-primary">
                    <i class="fas fa-home me-2"></i><?= __('cproj_homepage', [], 'Go to Homepage') ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>
