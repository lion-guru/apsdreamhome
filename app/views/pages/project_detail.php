<?php
/**
 * Project Detail Page
 * Display individual project/site details
 */
$project = $project ?? null;
$baseUrl = defined('BASE_URL') ? BASE_URL : 'http://localhost/apsdreamhome';

if ($project) {
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $project->site_name));
    $district = strtolower($project->district ?? 'gorakhpur');
    $heroImg = '/assets/images/projects/' . $district . '/' . $slug . '.jpg';
    if (stripos($project->site_name, 'Suryoday') !== false) {
        $heroImg = '/assets/images/projects/gorakhpur/suryoday.jpg';
    } elseif (stripos($project->site_name, 'Raghunath') !== false) {
        $heroImg = '/assets/images/projects/gorakhpur/raghunath nagri motiram.JPG';
    } elseif (stripos($project->site_name, 'Braj') !== false || stripos($project->site_name, 'Radha') !== false) {
        $heroImg = '/assets/images/projects/gorakhpur/suryoday1.jpeg';
    }
}
?>

<?php if ($project): ?>
<!-- Project Hero -->
<section class="hero-section text-white py-5 position-relative" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('<?php echo $baseUrl . $heroImg; ?>'); background-size: cover; background-position: center;">
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
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge bg-<?php echo $project->site_type === 'residential' ? 'success' : ($project->site_type === 'commercial' ? 'primary' : 'warning'); ?> fs-6">
                        <?php echo ucfirst($project->site_type ?? 'Residential'); ?>
                    </span>
                    <span class="badge bg-<?php echo $project->status === 'active' ? 'success' : 'secondary'; ?> fs-6">
                        <?php echo $project->status === 'active' ? 'Available' : ucfirst($project->status ?? 'Active'); ?>
                    </span>
                    <?php if (!empty($project->total_area)): ?>
                    <span class="badge bg-info fs-6">
                        <i class="fas fa-expand me-1"></i><?php echo htmlspecialchars($project->total_area); ?> Acres
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Quick Contact Bar -->
<section class="bg-primary text-white py-3">
    <div class="container">
        <div class="row align-items-center text-center">
            <div class="col-md-4 mb-2 mb-md-0">
                <i class="fas fa-phone-alt me-2"></i>
                <a href="tel:+919277121112" class="text-white text-decoration-none">+91 92771 21112</a>
            </div>
            <div class="col-md-4 mb-2 mb-md-0">
                <i class="fas fa-envelope me-2"></i>
                <a href="mailto:info@apsdreamhome.com" class="text-white text-decoration-none">info@apsdreamhome.com</a>
            </div>
            <div class="col-md-4">
                <a href="https://wa.me/919277121112?text=Hi, I'm interested in <?php echo urlencode($project->site_name); ?>" target="_blank" class="btn btn-success">
                    <i class="fab fa-whatsapp me-2"></i>WhatsApp Now
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Project Overview -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <h2 class="mb-4"><i class="fas fa-info-circle text-primary me-2"></i>Project Overview</h2>
                <p class="lead">
                    <?php echo nl2br(htmlspecialchars($project->description ?? 'Premium residential plots with modern infrastructure and excellent amenities located in the heart of ' . ($project->district ?? 'Uttar Pradesh') . '.')); ?>
                </p>
                
                <!-- Key Highlights -->
                <h3 class="mt-5 mb-4"><i class="fas fa-star text-warning me-2"></i>Key Highlights</h3>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center p-3 bg-light rounded">
                            <i class="fas fa-road fa-2x text-primary me-3"></i>
                            <div>
                                <h6 class="mb-0">Wide Roads</h6>
                                <small class="text-muted">30-40 ft internal roads</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center p-3 bg-light rounded">
                            <i class="fas fa-shield-alt fa-2x text-success me-3"></i>
                            <div>
                                <h6 class="mb-0">Gated Community</h6>
                                <small class="text-muted">24/7 security with CCTV</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center p-3 bg-light rounded">
                            <i class="fas fa-bolt fa-2x text-warning me-3"></i>
                            <div>
                                <h6 class="mb-0">Underground Electricity</h6>
                                <small class="text-muted">Safe and reliable power</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center p-3 bg-light rounded">
                            <i class="fas fa-tree fa-2x text-info me-3"></i>
                            <div>
                                <h6 class="mb-0">Green Parks</h6>
                                <small class="text-muted">Open spaces and gardens</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Amenities -->
                <?php 
                $amenities = [];
                if (!empty($project->amenities)) {
                    $decoded = json_decode($project->amenities, true);
                    if (is_array($decoded)) {
                        $amenities = $decoded;
                    }
                }
                ?>
                <h3 class="mt-5 mb-4"><i class="fas fa-concierge-bell text-primary me-2"></i>Amenities</h3>
                <div class="row">
                    <?php if (!empty($amenities)): ?>
                        <?php foreach ($amenities as $amenity): ?>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-check-circle text-success me-3"></i>
                                    <span><?php echo htmlspecialchars($amenity); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-md-6 mb-3"><i class="fas fa-check-circle text-success me-3"></i>Wide Roads (30-40 ft)</div>
                        <div class="col-md-6 mb-3"><i class="fas fa-check-circle text-success me-3"></i>24/7 Water Supply</div>
                        <div class="col-md-6 mb-3"><i class="fas fa-check-circle text-success me-3"></i>Underground Electricity</div>
                        <div class="col-md-6 mb-3"><i class="fas fa-check-circle text-success me-3"></i>Green Parks & Gardens</div>
                        <div class="col-md-6 mb-3"><i class="fas fa-check-circle text-success me-3"></i>Gated Community</div>
                        <div class="col-md-6 mb-3"><i class="fas fa-check-circle text-success me-3"></i>CCTV Security</div>
                        <div class="col-md-6 mb-3"><i class="fas fa-check-circle text-success me-3"></i>Rain Water Drainage</div>
                        <div class="col-md-6 mb-3"><i class="fas fa-check-circle text-success me-3"></i>Street Lights</div>
                        <div class="col-md-6 mb-3"><i class="fas fa-check-circle text-success me-3"></i>Park & Playground</div>
                        <div class="col-md-6 mb-3"><i class="fas fa-check-circle text-success me-3"></i>Near Main Road</div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Contact Card -->
                <div class="card shadow-lg mb-4 sticky-top" style="top: 100px;">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-headset me-2"></i>Get In Touch</h4>
                    </div>
                    <div class="card-body">
                        <h5 class="text-primary mb-3">Interested in this project?</h5>
                        <div class="d-grid gap-2">
                            <a href="tel:+919277121112" class="btn btn-success btn-lg">
                                <i class="fas fa-phone me-2"></i>Call Now
                            </a>
                            <a href="https://wa.me/919277121112?text=Hi, I'm interested in <?php echo urlencode($project->site_name); ?>" target="_blank" class="btn btn-outline-success btn-lg">
                                <i class="fab fa-whatsapp me-2"></i>WhatsApp
                            </a>
                            <a href="<?php echo $baseUrl; ?>/contact" class="btn btn-primary btn-lg">
                                <i class="fas fa-envelope me-2"></i>Send Inquiry
                            </a>
                            <a href="<?php echo $baseUrl; ?>/register" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Register Now
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Project Details Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-building me-2"></i>Project Details</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td><strong>Project Name:</strong></td>
                                <td><?php echo htmlspecialchars($project->site_name); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Location:</strong></td>
                                <td><?php echo htmlspecialchars($project->city ?? ''); ?></td>
                            </tr>
                            <tr>
                                <td><strong>District:</strong></td>
                                <td><?php echo htmlspecialchars($project->district ?? ''); ?></td>
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
                                <td><span class="badge bg-secondary"><?php echo ucfirst($project->site_type ?? 'Residential'); ?></span></td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td><span class="badge bg-<?php echo $project->status === 'active' ? 'success' : 'info'; ?>"><?php echo $project->status === 'active' ? 'Available' : ucfirst($project->status ?? 'Active'); ?></span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Location Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h3 class="mb-4"><i class="fas fa-map-marked-alt text-primary me-2"></i>Location & Accessibility</h3>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Address</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong><?php echo htmlspecialchars($project->site_name); ?></strong></p>
                        <p class="mb-1"><?php echo htmlspecialchars($project->location ?? ''); ?></p>
                        <p class="mb-0">
                            <?php echo htmlspecialchars(($project->city ?? '') . ', ' . ($project->district ?? '')); ?>
                            <?php if (!empty($project->pincode)): ?> - <?php echo htmlspecialchars($project->pincode); endif; ?><br>
                            <?php echo htmlspecialchars($project->state ?? 'Uttar Pradesh'); ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-directions me-2"></i>Nearby Places</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <i class="fas fa-train text-muted me-2"></i>
                                <small>Railway Station</small>
                            </div>
                            <div class="col-6 mb-3">
                                <i class="fas fa-bus text-muted me-2"></i>
                                <small>Bus Stand</small>
                            </div>
                            <div class="col-6 mb-3">
                                <i class="fas fa-school text-muted me-2"></i>
                                <small>Schools</small>
                            </div>
                            <div class="col-6 mb-3">
                                <i class="fas fa-hospital text-muted me-2"></i>
                                <small>Hospitals</small>
                            </div>
                            <div class="col-6 mb-3">
                                <i class="fas fa-shopping-cart text-muted me-2"></i>
                                <small>Market</small>
                            </div>
                            <div class="col-6 mb-3">
                                <i class="fas fa-place-of-worship text-muted me-2"></i>
                                <small>Temple/Church</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Google Map -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-map-marked me-2"></i>Location on Map</h5>
                    </div>
                    <div class="card-body p-0">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3571.7828543256!2d83.37!3d26.76!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjbCsDQ1JzM2LjAiTiA4M8KwMjInMTIuMCJF!5e0!3m2!1sen!2sin!4v1234567890"
                            width="100%" 
                            height="350" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                    <div class="card-footer text-center">
                        <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode(($project->location ?? $project->city ?? '') . ', ' . ($project->district ?? 'Uttar Pradesh')); ?>" target="_blank" class="btn btn-outline-primary">
                            <i class="fas fa-external-link-alt me-2"></i>Open in Google Maps
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Other Projects -->
<section class="py-5">
    <div class="container">
        <h3 class="mb-4"><i class="fas fa-th-large text-primary me-2"></i>Other Projects You May Like</h3>
        <div class="row">
            <?php if (!empty($related_projects)): ?>
                <?php foreach (array_slice($related_projects, 0, 3) as $related): 
                    $relSlug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $related->site_name));
                    $relImg = '/assets/images/placeholder/property.svg';
                    if (stripos($related->site_name, 'Suryoday') !== false) {
                        $relImg = '/assets/images/projects/gorakhpur/suryoday.jpg';
                    } elseif (stripos($related->site_name, 'Raghunath') !== false) {
                        $relImg = '/assets/images/projects/gorakhpur/raghunath nagri motiram.JPG';
                    }
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <img src="<?php echo $baseUrl . $relImg; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($related->site_name); ?>" style="height: 150px; object-fit: cover;" onerror="this.src='<?php echo $baseUrl; ?>/assets/images/placeholder/property.svg'">
                        <div class="card-body">
                            <h6 class="card-title"><?php echo htmlspecialchars($related->site_name); ?></h6>
                            <p class="text-muted small mb-2"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($related->district ?? ''); ?></p>
                            <a href="<?php echo $baseUrl; ?>/projects/<?php echo $relSlug; ?>" class="btn btn-sm btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <img src="<?php echo $baseUrl; ?>/assets/images/projects/gorakhpur/suryoday.jpg" class="card-img-top" alt="Suryoday Colony" style="height: 150px; object-fit: cover;">
                        <div class="card-body">
                            <h6 class="card-title">Suryoday Colony</h6>
                            <p class="text-muted small mb-2"><i class="fas fa-map-marker-alt me-1"></i>Gorakhpur</p>
                            <a href="<?php echo $baseUrl; ?>/projects/suryoday-colony" class="btn btn-sm btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <img src="<?php echo $baseUrl; ?>/assets/images/projects/gorakhpur/raghunath nagri motiram.JPG" class="card-img-top" alt="Raghunath Nagri" style="height: 150px; object-fit: cover;">
                        <div class="card-body">
                            <h6 class="card-title">Raghunath Nagri</h6>
                            <p class="text-muted small mb-2"><i class="fas fa-map-marker-alt me-1"></i>Gorakhpur</p>
                            <a href="<?php echo $baseUrl; ?>/projects/raghunath-nagri" class="btn btn-sm btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <img src="<?php echo $baseUrl; ?>/assets/images/projects/gorakhpur/suryoday1.jpeg" class="card-img-top" alt="Braj Radha Nagri" style="height: 150px; object-fit: cover;">
                        <div class="card-body">
                            <h6 class="card-title">Braj Radha Nagri</h6>
                            <p class="text-muted small mb-2"><i class="fas fa-map-marker-alt me-1"></i>Gorakhpur</p>
                            <a href="<?php echo $baseUrl; ?>/projects/braj-radha-nagri" class="btn btn-sm btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="text-center mt-3">
            <a href="<?php echo $baseUrl; ?>/company/projects" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-building me-2"></i>View All Projects
            </a>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container text-center">
        <h2 class="mb-3">Interested in <?php echo htmlspecialchars($project->site_name); ?>?</h2>
        <p class="lead mb-4">Book your dream plot today and secure your future!</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="tel:+919277121112" class="btn btn-warning btn-lg">
                <i class="fas fa-phone me-2"></i>Call Now
            </a>
            <a href="https://wa.me/919277121112?text=Hi, I'm interested in <?php echo urlencode($project->site_name); ?>" target="_blank" class="btn btn-success btn-lg">
                <i class="fab fa-whatsapp me-2"></i>WhatsApp
            </a>
            <a href="<?php echo $baseUrl; ?>/register" class="btn btn-light btn-lg">
                <i class="fas fa-user-plus me-2"></i>Register Now
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
