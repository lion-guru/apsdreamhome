<?php
// Fetch approved testimonials from database
$testimonials = [];
$error = '';

try {
    $conn = getDbConnection();
    $query = "SELECT 
                id,
                client_name as name,
                email,
                rating,
                testimonial,
                client_photo,
                created_at 
              FROM testimonials 
              WHERE status IN ('approved', 'active') 
              ORDER BY created_at DESC";
    $result = $conn->query($query);
    
    if ($result) {
        $testimonials = $result->fetch_all(MYSQLI_ASSOC);
        
        // Process testimonials to ensure they have all required fields
        foreach ($testimonials as &$testimonial) {
            // Set default rating if not set
            if (!isset($testimonial['rating']) || $testimonial['rating'] === null) {
                $testimonial['rating'] = 5;
            }
            
            // Generate avatar if no photo is available
            if (empty($testimonial['client_photo'])) {
                $initials = '';
                $nameParts = explode(' ', $testimonial['name']);
                foreach ($nameParts as $part) {
                    $initials .= strtoupper(substr($part, 0, 1));
                    if (strlen($initials) >= 2) break;
                }
                $testimonial['avatar'] = '<div class="avatar-circle bg-primary text-white d-flex align-items-center justify-content-center">' . $initials . '</div>';
            } else {
                $testimonial['avatar'] = '<img src="' . htmlspecialchars($testimonial['client_photo']) . '" alt="' . htmlspecialchars($testimonial['name']) . '" class="rounded-circle" width="60" height="60">';
            }
        }
        unset($testimonial); // Break the reference
    }
} catch (Exception $e) {
    $error = 'Error loading testimonials. Please try again later.';
    error_log('Testimonial fetch error: ' . $e->getMessage());
}
?>

<!-- Testimonials Section -->
<section class="testimonials-section py-7 bg-light position-relative overflow-hidden">
    <!-- Decorative Elements -->
    <div class="testimonial-shape-1 position-absolute"></div>
    <div class="testimonial-shape-2 position-absolute"></div>
    
    <div class="container position-relative">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center mb-5">
                <span class="subtitle text-primary">TESTIMONIALS</span>
                <h2 class="fw-bold mb-3">What Our Clients Say</h2>
                <div class="title-line mx-auto"></div>
                <p class="lead text-muted">Hear from our satisfied clients about their experience with APS Dream Home</p>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-warning"><?= htmlspecialchars($error) ?></div>
        <?php elseif (empty($testimonials)): ?>
            <div class="text-center py-5">
                <i class="fas fa-comment-slash fa-3x text-muted mb-3"></i>
                <p class="h5">No testimonials available yet. Be the first to share your experience!</p>
            </div>
        <?php else: ?>
            <div class="testimonial-slider-wrapper position-relative">
                <div class="swiper testimonial-slider">
                    <div class="swiper-wrapper">
                        <?php foreach (array_chunk($testimonials, 2) as $testimonialPair): ?>
                            <div class="swiper-slide">
                                <div class="row g-4">
                                    <?php foreach ($testimonialPair as $testimonial): ?>
                                        <div class="col-md-6">
                                            <div class="testimonial-card bg-white p-4 rounded-4 shadow-sm h-100">
                                                <div class="testimonial-content mb-4">
                                                    <div class="quote-icon mb-3">
                                                        <i class="fas fa-quote-left text-primary"></i>
                                                    </div>
                                                    <div class="rating mb-3">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <i class="fas fa-star<?= $i > $testimonial['rating'] ? '-o' : '' ?> text-warning"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                    <p class="mb-0 fst-italic">"<?= nl2br(htmlspecialchars($testimonial['testimonial'])) ?>"</p>
                                                </div>
                                                <div class="testimonial-author d-flex align-items-center mt-auto">
                                                    <div class="author-avatar me-3">
                                                        <div class="avatar-circle bg-primary text-white d-flex align-items-center justify-content-center">
                                                            <?= strtoupper(substr($testimonial['name'], 0, 1)) ?>
                                                        </div>
                                                    </div>
                                                    <div class="author-info">
                                                        <h5 class="mb-1"><?= htmlspecialchars($testimonial['name']) ?></h5>
                                                        <p class="text-muted mb-0">
                                                            <?= date('F j, Y', strtotime($testimonial['created_at'])) ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Navigation Buttons -->
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                    
                    <!-- Pagination -->
                    <div class="swiper-pagination"></div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Testimonial Stats -->
        <div class="row mt-5 pt-4">
            <div class="col-md-4 mb-4 mb-md-0">
                <div class="stat-card text-center p-4 rounded-3 bg-white shadow-sm h-100" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-icon mb-3">
                        <i class="fas fa-smile fa-2x text-primary"></i>
                    </div>
                    <h3 class="counter fw-bold mb-1" data-target="98">0</h3>
                    <p class="text-muted mb-0">Client Satisfaction</p>
                </div>
            </div>
            <div class="col-md-4 mb-4 mb-md-0">
                <div class="stat-card text-center p-4 rounded-3 bg-white shadow-sm h-100" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-icon mb-3">
                        <i class="fas fa-home fa-2x text-primary"></i>
                    </div>
                    <h3 class="counter fw-bold mb-1" data-target="1250">0</h3>
                    <p class="text-muted mb-0">Properties Sold</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card text-center p-4 rounded-3 bg-white shadow-sm h-100" data-aos="fade-up" data-aos-delay="300">
                    <div class="stat-icon mb-3">
                        <i class="fas fa-users fa-2x text-primary"></i>
                    </div>
                    <h3 class="counter fw-bold mb-1" data-target="15">0</h3>
                    <p class="text-muted mb-0">Years of Experience</p>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- End Testimonials Section -->
