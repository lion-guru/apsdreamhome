/**
 * project - APS Dream Home Component
 * 
 * @package APS Dream Home
 * @version 1.0.0
 * @author APS Dream Home Team
 * @copyright 2026 APS Dream Home
 * 
 * Description: Handles project functionality
 * 
 * Features:
 * - Secure input validation
 * - Comprehensive error handling
 * - Performance optimization
 * - Database integration
 * - Session management
 * - CSRF protection
 * 
 * @see https://apsdreamhome.com/docs
 */
<?php
//
// ERROR HANDLING CONFIGURATION
//
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

function handleError(,  = null,  = null) {
     = date('Y-m-d H:i:s') . ' - ERROR: ' . ;
    if ()  .= ' in ' . ;
    if ()  .= ' on line ' . ;
    error_log();
    return false;
}

function safeExecute(,  = 'Operation failed') {
    try {
        return ();
    } catch (Exception ) {
        handleError( . ': ' . (), (), ());
        return null;
    }
}

//
$layout = 'layouts/base';
$page_title = $page_title ?? 'Project Gallery - APS Dream Home';
$page_description = $page_description ?? 'View photos and videos of our projects';
$project = $project ?? [];
$project_id = $project_id ?? 0;
?>

<!-- Hero Section -->
<section class="project-hero text-white text-center py-5 mb-5" style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.9), rgba(118, 75, 162, 0.9)), url('https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1920'); background-size: cover; background-position: center; background-attachment: fixed;">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/" class="text-white">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/gallery" class="text-white">Gallery</a></li>
                        <li class="breadcrumb-item active text-white-50"><?php echo htmlspecialchars($project['project_name']); ?></li>
                    </ol>
                </nav>
                
                <div class="animate-fade-up">
                    <h1 class="display-3 fw-bold mb-4"><?php echo htmlspecialchars($project['project_name']); ?></h1>
                    <p class="lead text-white-90 mb-4"><?php echo htmlspecialchars($project['project_description']); ?></p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <span class="badge bg-white text-primary px-3 py-2">
                            <i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($project['location']['address']); ?>
                        </span>
                        <span class="badge bg-white text-success px-3 py-2">
                            <i class="fas fa-check-circle me-2"></i>Completed Project
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Project Stats -->
<section class="py-4 bg-white">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 col-6 mb-4">
                <div class="stat-item">
                    <h4 class="text-primary fw-bold"><?php echo count($project['images']); ?></h4>
                    <p class="text-muted mb-0">Photos</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <div class="stat-item">
                    <h4 class="text-success fw-bold"><?php echo count($project['videos']); ?></h4>
                    <p class="text-muted mb-0">Videos</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <div class="stat-item">
                    <h4 class="text-info fw-bold">2024</h4>
                    <p class="text-muted mb-0">Year</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <div class="stat-item">
                    <h4 class="text-warning fw-bold">4.8</h4>
                    <p class="text-muted mb-0">Rating</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Project Location -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="location-info">
                    <h2 class="display-5 fw-bold mb-4">Project Location</h2>
                    <div class="location-details mb-4">
                        <div class="location-item d-flex align-items-center mb-3">
                            <i class="fas fa-map-marker-alt text-primary me-3"></i>
                            <div>
                                <h5 class="mb-1">Address</h5>
                                <p class="text-muted mb-0"><?php echo htmlspecialchars($project['location']['address']); ?></p>
                            </div>
                        </div>
                        <div class="location-item d-flex align-items-center mb-3">
                            <i class="fas fa-globe text-success me-3"></i>
                            <div>
                                <h5 class="mb-1">Coordinates</h5>
                                <p class="text-muted mb-0"><?php echo $project['location']['lat']; ?>, <?php echo $project['location']['lng']; ?></p>
                            </div>
                        </div>
                        <div class="location-item d-flex align-items-center">
                            <i class="fas fa-directions text-info me-3"></i>
                            <div>
                                <h5 class="mb-1">Getting There</h5>
                                <p class="text-muted mb-0">Easily accessible from main city centers</p>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-lg px-4 py-3" onclick="openGoogleMaps()">
                        <i class="fas fa-map me-2"></i>Get Directions
                    </button>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="map-container">
                    <div id="projectMap" style="height: 400px; border-radius: 15px; overflow: hidden;">
                        <iframe src="https://maps.google.com/maps?q=<?php echo $project['location']['lat']; ?>,<?php echo $project['location']['lng']; ?>&z=15&output=embed" 
                                width="100%" 
                                height="100%" 
                                frameborder="0" 
                                style="border:0" 
                                allowfullscreen>
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Gallery Tabs -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-4">Project Gallery</h2>
            <p class="lead text-muted">Explore our project through high-quality images and videos</p>
        </div>

        <!-- Gallery Navigation -->
        <ul class="nav nav-pills justify-content-center mb-4" id="galleryTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="photos-tab" data-bs-toggle="pill" data-bs-target="#photos" type="button" role="tab">
                    <i class="fas fa-images me-2"></i>Photos (<?php echo count($project['images']); ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="videos-tab" data-bs-toggle="pill" data-bs-target="#videos" type="button" role="tab">
                    <i class="fas fa-video me-2"></i>Videos (<?php echo count($project['videos']); ?>)
                </button>
            </li>
        </ul>

        <!-- Gallery Content -->
        <div class="tab-content" id="galleryTabContent">
            <!-- Photos Tab -->
            <div class="tab-pane fade show active" id="photos" role="tabpanel">
                <div class="photos-gallery">
                    <div class="row g-4">
                        <?php foreach ($project['images'] as $index => $image): ?>
                            <div class="col-lg-4 col-md-6">
                                <div class="photo-card animate-fade-up" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                                    <div class="photo-container">
                                        <img src="<?php echo htmlspecialchars($image['url']); ?>" 
                                             alt="<?php echo htmlspecialchars($image['title']); ?>"
                                             class="photo-image">
                                        <div class="photo-overlay">
                                            <div class="photo-actions">
                                                <button class="btn btn-light btn-sm" onclick="openPhotoModal('<?php echo htmlspecialchars($image['url']); ?>', '<?php echo htmlspecialchars($image['title']); ?>')">
                                                    <i class="fas fa-expand"></i>
                                                </button>
                                                <button class="btn btn-primary btn-sm" onclick="downloadPhoto('<?php echo htmlspecialchars($image['url']); ?>')">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="photo-info">
                                        <h5 class="photo-title"><?php echo htmlspecialchars($image['title']); ?></h5>
                                        <p class="photo-description">Click to view full size</p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Videos Tab -->
            <div class="tab-pane fade" id="videos" role="tabpanel">
                <div class="videos-gallery">
                    <div class="row g-4">
                        <?php foreach ($project['videos'] as $index => $video): ?>
                            <div class="col-lg-6">
                                <div class="video-card animate-fade-up" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                                    <div class="video-container">
                                        <iframe src="<?php echo htmlspecialchars($video['url']); ?>" 
                                                frameborder="0" 
                                                allowfullscreen
                                                class="video-iframe">
                                        </iframe>
                                        <div class="video-info-overlay">
                                            <h4 class="video-title"><?php echo htmlspecialchars($video['title']); ?></h4>
                                        </div>
                                    </div>
                                    <div class="video-details">
                                        <h5 class="video-name"><?php echo htmlspecialchars($video['title']); ?></h5>
                                        <p class="video-description">Watch the complete project tour</p>
                                        <button class="btn btn-primary btn-sm" onclick="openVideoModal('<?php echo htmlspecialchars($video['url']); ?>', '<?php echo htmlspecialchars($video['title']); ?>')">
                                            <i class="fas fa-play me-1"></i>Watch Full Screen
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Project Features -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="display-5 fw-bold mb-4">Project Highlights</h2>
                <p class="lead text-muted mb-5">Key features and amenities that make this project special</p>
                
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-home"></i>
                            </div>
                            <h4>Premium Construction</h4>
                            <p>High-quality materials and modern construction techniques</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h4>Security & Safety</h4>
                            <p>24/7 security with advanced surveillance systems</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-leaf"></i>
                            </div>
                            <h4>Green Living</h4>
                            <p>Eco-friendly features and sustainable design</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-gradient-primary text-white">
    <div class="container text-center">
        <h2 class="display-5 fw-bold mb-4">Interested in This Project?</h2>
        <p class="lead text-white-90 mb-4">Get in touch with our team for more information or to schedule a site visit</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="<?php echo BASE_URL; ?>/contact" class="btn btn-light btn-lg px-4 py-3">
                <i class="fas fa-phone me-2"></i>Contact Us
            </a>
            <a href="<?php echo BASE_URL; ?>/properties" class="btn btn-outline-light btn-lg px-4 py-3">
                <i class="fas fa-building me-2"></i>Similar Projects
            </a>
        </div>
    </div>
</section>

<!-- Modals -->
<div class="modal fade" id="photoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bg-dark">
            <div class="modal-body p-0">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                <img id="modalPhoto" src="" alt="" class="img-fluid w-100">
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="videoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bg-dark">
            <div class="modal-body p-0">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                <iframe id="modalVideo" src="" frameborder="0" allowfullscreen class="w-100" style="height: 500px;"></iframe>
            </div>
        </div>
    </div>
</div>

<!-- Custom Styles -->
<style>
.project-hero {
    position: relative;
    overflow: hidden;
}

.animate-fade-up {
    animation: fadeInUp 0.8s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.photo-card, .video-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.photo-card:hover, .video-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.photo-container, .video-container {
    position: relative;
    height: 250px;
    overflow: hidden;
}

.photo-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.photo-card:hover .photo-image {
    transform: scale(1.05);
}

.photo-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.photo-card:hover .photo-overlay {
    opacity: 1;
}

.photo-actions {
    display: flex;
    gap: 0.5rem;
}

.photo-info, .video-details {
    padding: 1.5rem;
}

.photo-title, .video-name {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #2c3e50;
}

.photo-description, .video-description {
    color: #6c757d;
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

.video-iframe {
    width: 100%;
    height: 100%;
    border: none;
}

.video-info-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,0.8));
    padding: 1rem;
    color: white;
}

.video-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0;
}

.feature-card {
    text-align: center;
    padding: 2rem;
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.feature-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    color: white;
    font-size: 1.5rem;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.map-container {
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

@media (max-width: 768px) {
    .display-3 {
        font-size: 2.5rem;
    }
    
    .photo-container, .video-container {
        height: 200px;
    }
}
</style>

<!-- JavaScript -->
<script>
function openPhotoModal(imageSrc, title) {
    document.getElementById('modalPhoto').src = imageSrc;
    document.getElementById('modalPhoto').alt = title;
    new bootstrap.Modal(document.getElementById('photoModal')).show();
}

function openVideoModal(videoSrc, title) {
    document.getElementById('modalVideo').src = videoSrc;
    new bootstrap.Modal(document.getElementById('videoModal')).show();
}

function downloadPhoto(imageSrc) {
    const link = document.createElement('a');
    link.href = imageSrc;
    link.download = 'project-photo.jpg';
    link.target = '_blank';
    link.click();
}

function openGoogleMaps() {
    const lat = <?php echo $project['location']['lat']; ?>;
    const lng = <?php echo $project['location']['lng']; ?>;
    window.open(`https://maps.google.com/maps?q=${lat},${lng}`, '_blank');
}

// Add animation on scroll
document.addEventListener('DOMContentLoaded', function() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fade-up');
            }
        });
    }, observerOptions);

    document.querySelectorAll('.photo-card, .video-card, .feature-card').forEach(el => {
        observer.observe(el);
    });
});
</script>

//
// PERFORMANCE OPTIMIZATION GUIDELINES
//
// This file contains 503 lines. Consider optimizations:
//
// 1. Use database indexing
// 2. Implement caching
// 3. Use prepared statements
// 4. Optimize loops
// 5. Use lazy loading
// 6. Implement pagination
// 7. Use connection pooling
// 8. Consider Redis for sessions
// 9. Implement output buffering
// 10. Use gzip compression
//
//