<?php
$project = $data['project'] ?? [];
$relatedProjects = $data['relatedProjects'] ?? [];
?>

<!-- Hero Section -->
<div class="project-hero" style="background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('<?php echo h($project['banner_image'] ?? $project['image_path'] ?? 'assets/images/project-banner.jpg'); ?>'); background-size: cover; background-position: center; color: white; padding: 150px 0 100px; margin-top: -20px; position: relative;">
    <div class="container" data-aos="fade-up">
        <span class="badge bg-primary mb-3"><?php echo h($project['status'] ?? 'Ongoing'); ?></span>
        <h1 class="display-4 fw-bold mb-3"><?php echo h($project['name']); ?></h1>
        <p class="lead mb-4"><i class="fas fa-map-marker-alt me-2"></i><?php echo h($project['location']); ?></p>
        <div class="d-flex gap-3">
            <button class="btn btn-primary btn-lg" onclick="document.getElementById('enquiryForm').scrollIntoView({behavior: 'smooth'})">
                <i class="fas fa-paper-plane me-2"></i>Enquire Now
            </button>
            <button class="btn btn-outline-light btn-lg" onclick="document.getElementById('gallery').scrollIntoView({behavior: 'smooth'})">
                <i class="fas fa-images me-2"></i>View Gallery
            </button>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container py-5">
    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Overview -->
            <div class="card shadow-sm mb-5" data-aos="fade-up">
                <div class="card-body p-4">
                    <h3 class="mb-4">Project Overview</h3>
                    <div class="row g-4 mb-4">
                        <div class="col-md-4 col-6">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-ruler-combined text-primary fa-2x"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <small class="text-muted d-block">Total Area</small>
                                    <strong><?php echo h($project['total_area'] ?? 'N/A'); ?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-layer-group text-primary fa-2x"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <small class="text-muted d-block">Total Plots</small>
                                    <strong><?php echo h($project['total_plots'] ?? 'N/A'); ?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-tag text-primary fa-2x"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <small class="text-muted d-block">Starting Price</small>
                                    <strong>₹<?php echo number_format($project['base_price'] ?? 0); ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="project-description mt-4">
                        <?php echo $project['description']; ?>
                    </div>
                </div>
            </div>

            <!-- Amenities -->
            <div class="mb-5" data-aos="fade-up">
                <h3 class="mb-4">Amenities</h3>
                <div class="row g-4">
                    <?php 
                    $amenities = isset($project['amenities']) ? json_decode($project['amenities'], true) : [];
                    if (empty($amenities)) {
                        $amenities = ['24/7 Security', 'Park', 'Wide Roads', 'Electricity', 'Water Supply', 'Drainage'];
                    }
                    foreach ($amenities as $amenity): 
                    ?>
                    <div class="col-md-4 col-6">
                        <div class="amenity-card bg-white shadow-sm h-100">
                            <i class="fas fa-check-circle text-success mb-3 fa-2x"></i>
                            <h5 class="mb-0"><?php echo h(is_array($amenity) ? $amenity['title'] : $amenity); ?></h5>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Gallery -->
            <div id="gallery" class="mb-5" data-aos="fade-up">
                <h3 class="mb-4">Project Gallery</h3>
                <div class="row g-3">
                    <?php 
                    $gallery = isset($project['gallery_images']) ? json_decode($project['gallery_images'], true) : [];
                    if (empty($gallery)) {
                        $gallery = [$project['image_path'] ?? 'assets/images/placeholder.jpg'];
                    }
                    foreach ($gallery as $index => $image): 
                    ?>
                    <div class="col-md-4 col-6">
                        <div class="gallery-item">
                            <img src="<?php echo h($image); ?>" alt="Project Image <?php echo $index + 1; ?>" class="img-fluid rounded" onclick="openLightbox(this.src)">
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Map/Location -->
             <?php if (!empty($project['map_embed_code'])): ?>
            <div class="mb-5" data-aos="fade-up">
                <h3 class="mb-4">Location</h3>
                <div class="ratio ratio-16x9 rounded overflow-hidden shadow-sm">
                    <?php echo $project['map_embed_code']; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Enquiry Form -->
            <div id="enquiryForm" class="card shadow-sm sticky-top" style="top: 100px; z-index: 10;" data-aos="fade-left">
                <div class="card-body p-4">
                    <h4 class="mb-4">Interested?</h4>
                    <p class="text-muted mb-4">Fill out the form below and our team will get back to you shortly.</p>
                    
                    <form action="/api/enquiry" method="POST" id="projectEnquiryForm">
                        <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                        <input type="hidden" name="source" value="project_details">
                        
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email (Optional)</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea name="message" class="form-control" rows="3" placeholder="I am interested in <?php echo h($project['name']); ?>..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Send Enquiry</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Related Projects -->
<?php if (!empty($relatedProjects)): ?>
<div class="bg-light py-5">
    <div class="container">
        <h3 class="mb-4 text-center">Similar Projects in <?php echo h($project['location']); ?></h3>
        <div class="row g-4">
            <?php foreach ($relatedProjects as $relProject): ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0 transition-hover">
                    <img src="<?php echo h($relProject['image_path'] ?? 'assets/images/placeholder.jpg'); ?>" class="card-img-top" alt="<?php echo h($relProject['name']); ?>" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo h($relProject['name']); ?></h5>
                        <p class="card-text text-muted"><i class="fas fa-map-marker-alt me-2"></i><?php echo h($relProject['location']); ?></p>
                        <a href="/projects/<?php echo $relProject['id']; ?>" class="btn btn-outline-primary w-100">View Details</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
.amenity-card {
    text-align: center;
    padding: 20px;
    border-radius: 10px;
    transition: transform 0.3s ease;
}
.amenity-card:hover {
    transform: translateY(-5px);
}
.gallery-item img {
    cursor: pointer;
    transition: transform 0.3s ease;
}
.gallery-item img:hover {
    transform: scale(1.02);
}
.transition-hover {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.transition-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}
</style>
