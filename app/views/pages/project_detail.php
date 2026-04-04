<?php
/**
 * Project Detail Page
 * Display individual project/site details
 */
$project = $project ?? null;
$baseUrl = defined('BASE_URL') ? BASE_URL : 'http://localhost/apsdreamhome';
?>

<?php if ($project): ?>
<!-- Project Hero -->
<section class="hero-section text-white py-5" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('<?php echo $baseUrl; ?>/assets/images/projects/project-default.jpg'); background-size: cover;">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo $baseUrl; ?>/" class="text-white">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo $baseUrl; ?>/company/projects" class="text-white">Projects</a></li>
                        <li class="breadcrumb-item text-white active"><?php echo htmlspecialchars($project->site_name); ?></li>
                    </ol>
                </nav>
                <h1 class="display-4 fw-bold mb-3"><?php echo htmlspecialchars($project->site_name); ?></h1>
                <p class="lead mb-3">
                    <i class="fas fa-map-marker-alt me-2"></i>
                    <?php echo htmlspecialchars(($project->location ?? '') . ', ' . ($project->city ?? '')); ?>
                </p>
                <span class="badge bg-<?php echo $project->site_type === 'residential' ? 'success' : ($project->site_type === 'commercial' ? 'primary' : 'warning'); ?> fs-6">
                    <?php echo ucfirst($project->site_type ?? 'Residential'); ?>
                </span>
                <span class="badge bg-<?php echo $project->status === 'active' ? 'success' : 'secondary'; ?> ms-2 fs-6">
                    <?php echo ucfirst($project->status ?? 'Active'); ?>
                </span>
            </div>
        </div>
    </div>
</section>

<!-- Project Overview -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <h2 class="mb-4">Project Overview</h2>
                <p class="lead">
                    <?php echo nl2br(htmlspecialchars($project->description ?? 'Premium residential plots with modern infrastructure and excellent amenities.')); ?>
                </p>
                
                <?php if (!empty($project->amenities)): ?>
                <h3 class="mt-5 mb-4"><i class="fas fa-star text-warning me-2"></i>Amenities</h3>
                <div class="row">
                    <?php 
                    $amenities = json_decode($project->amenities, true) ?? [];
                    if (is_array($amenities) && count($amenities) > 0): 
                        foreach ($amenities as $amenity):
                    ?>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span><?php echo htmlspecialchars($amenity); ?></span>
                            </div>
                        </div>
                    <?php 
                        endforeach;
                    else:
                    ?>
                        <!-- Default amenities -->
                        <div class="col-md-6 mb-3"><i class="fas fa-check-circle text-success me-2"></i>Wide Roads (30-40 ft)</div>
                        <div class="col-md-6 mb-3"><i class="fas fa-check-circle text-success me-2"></i>24/7 Water Supply</div>
                        <div class="col-md-6 mb-3"><i class="fas fa-check-circle text-success me-2"></i>Underground Electricity</div>
                        <div class="col-md-6 mb-3"><i class="fas fa-check-circle text-success me-2"></i>Green Parks</div>
                        <div class="col-md-6 mb-3"><i class="fas fa-check-circle text-success me-2"></i>Gated Community</div>
                        <div class="col-md-6 mb-3"><i class="fas fa-check-circle text-success me-2"></i>CCTV Security</div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="col-lg-4">
                <div class="card shadow-lg sticky-top" style="top: 100px;">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-info-circle me-2"></i>Project Details</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Location:</strong></td>
                                <td><?php echo htmlspecialchars(($project->city ?? '') . ', ' . ($project->district ?? '')); ?></td>
                            </tr>
                            <tr>
                                <td><strong>State:</strong></td>
                                <td><?php echo htmlspecialchars($project->state ?? 'Uttar Pradesh'); ?></td>
                            </tr>
                            <?php if (!empty($project->pincode)): ?>
                            <tr>
                                <td><strong>Pincode:</strong></td>
                                <td><?php echo htmlspecialchars($project->pincode); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($project->total_area)): ?>
                            <tr>
                                <td><strong>Total Area:</strong></td>
                                <td><?php echo htmlspecialchars($project->total_area); ?> Acres</td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td><strong>Type:</strong></td>
                                <td><?php echo ucfirst($project->site_type ?? 'Residential'); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td><span class="badge bg-<?php echo $project->status === 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($project->status ?? 'Active'); ?></span></td>
                            </tr>
                        </table>
                        
                        <hr>
                        
                        <div class="d-grid gap-2">
                            <a href="<?php echo $baseUrl; ?>/contact" class="btn btn-primary btn-lg">
                                <i class="fas fa-phone me-2"></i>Contact Us
                            </a>
                            <a href="https://wa.me/919277121112?text=Hi, I'm interested in <?php echo urlencode($project->site_name); ?>" target="_blank" class="btn btn-success btn-lg">
                                <i class="fab fa-whatsapp me-2"></i>WhatsApp
                            </a>
                            <a href="<?php echo $baseUrl; ?>/register" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Register Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Location Map -->
<section class="py-5 bg-light">
    <div class="container">
        <h3 class="mb-4"><i class="fas fa-map-marked-alt text-primary me-2"></i>Location</h3>
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5><i class="fas fa-map-marker-alt text-danger me-2"></i>Address</h5>
                        <p class="mb-0">
                            <?php echo htmlspecialchars($project->site_name); ?><br>
                            <?php echo htmlspecialchars($project->location ?? ''); ?><br>
                            <?php echo htmlspecialchars(($project->city ?? '') . ', ' . ($project->district ?? '') . ' - ' . ($project->pincode ?? '')); ?><br>
                            <?php echo htmlspecialchars($project->state ?? 'Uttar Pradesh'); ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5><i class="fas fa-building text-info me-2"></i>Nearby Landmarks</h5>
                        <ul class="list-unstyled mb-0">
                            <li><i class="fas fa-school text-muted me-2"></i>Schools within 2 km</li>
                            <li><i class="fas fa-hospital text-muted me-2"></i>Hospitals nearby</li>
                            <li><i class="fas fa-shopping-cart text-muted me-2"></i>Market area close by</li>
                            <li><i class="fas fa-bus text-muted me-2"></i>Bus stand within 5 km</li>
                            <li><i class="fas fa-train text-muted me-2"></i>Railway station accessible</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-primary text-white text-center">
    <div class="container">
        <h2 class="mb-3">Interested in <?php echo htmlspecialchars($project->site_name); ?>?</h2>
        <p class="lead mb-4">Book your dream plot today and secure your future!</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="<?php echo $baseUrl; ?>/contact" class="btn btn-light btn-lg">
                <i class="fas fa-phone me-2"></i>Contact Us
            </a>
            <a href="<?php echo $baseUrl; ?>/register" class="btn btn-outline-light btn-lg">
                <i class="fas fa-user-plus me-2"></i>Register Now
            </a>
            <a href="https://wa.me/919277121112" target="_blank" class="btn btn-success btn-lg">
                <i class="fab fa-whatsapp me-2"></i>WhatsApp Now
            </a>
        </div>
    </div>
</section>

<?php else: ?>
<!-- Project Not Found -->
<section class="py-5 text-center">
    <div class="container">
        <div class="alert alert-info">
            <h2><i class="fas fa-info-circle me-2"></i>Project Not Found</h2>
            <p class="lead">The requested project could not be found.</p>
            <a href="<?php echo $baseUrl; ?>/company/projects" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i>Back to Projects
            </a>
        </div>
    </div>
</section>
<?php endif; ?>
